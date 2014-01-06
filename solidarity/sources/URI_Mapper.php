<?php
   /*===========================================*
    * 											*
    *  @Title:	URL to URI Mapper/Parser		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This file turns the standard URL into data that PHP can use t dynamically generate pages.
    * 	This gives the effect of infinite directories, when there are actually only two files.
    * 	This also helps seperate backend and frontend, and reduces the need for lots of directory 
    * 	profiling.
    * 
    * 	array parseURL()
    * 		- parses the url loaded into the $_get URL param
    * 		- turns the URL into a handler call and sub-arguments
    * 		- cleans the arguments
    * 		- returns array of handler and call
    * 
    * 	boolean loadHandler(array handlerarray)
    * 		- attempts to load the handler given
    * 		- included a file in the handlers path with that name
    * 		- passes on the arguments to that function
    * 		- returns true if successful
    * 		- returns false if failure
    * 	void maintenanceRedirect()
    * 		- protects pages from being accessed by no nadmins when in maintinance mode
    * 		- redirects to maintinance mode handler
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
	
	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	
	function parseURL(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//get our url
		if (isset($_GET['url']) && (strlen($_GET['url']) > 0)) {
			//do some initial handling
			$raw = trim($_GET['url']);
			
			
			//remove estra "/"'s
			if (substr($raw, 0, 1)==="/") {
				$raw = substr($raw, 1);
			}
			if (substr($raw, -1, 1)==="/") {
				$raw = substr($raw, 0, strlen($raw) - 1);
			} else {
				header('Location: '.$coreSettings['preferences']['server_url'].$_GET['url'].'/');
				ob_end_flush();
				exit();
			}
			
			$urlArray = explode("/", $raw);
			
			//establish our handler
			$handler = strtolower(preclean($urlArray[0]));
			unset($urlArray[0]);
			
			//construct an argument array
			$Args = array();
			$i = 0;
			
			//clean up the rest of our array
			foreach ($urlArray as $id => $value) {
				
				//do some cleaning
				$parsed = trim(mysql_escape_string($value));
				
				//write to array
				$Args[$i] = $parsed;
				$i++;
			}
			
			_debug('Going to handler: '.$handler);
			
			//return our cleaned up arguments
			return array(
				'handler' => $handler,
				'args' => $Args
			);
			
		}else{
			_debug('no url found -> going to defualt handler ('.$coreSettings['default_handler'].' )');
			
			//go to our default handler established in our settings
			//only if nothing is passed
			return array('handler' =>  $coreSettings['default_handler'], array( ));
		}
	}
    
    function loadHandler($handlerarray){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//quick error checking
		if (!isset($handlerarray)&& !$handlerarray['handler']) return false;
		
		//load our things
		$handler = ucfirst(strtolower($handlerarray['handler']));
		if (isset($handlerarray['args']))
			$args = $handlerarray['args'];
		else $args = array();
		
		_debug("Checking for handler ".$handler);
		
		//check if our handler file exists.
		$handler_path = $coreSettings['handlers_path']."/".$handler.".php";
		_debug("Checking ".$handler_path);
		if (!file_exists($handler_path)) {
			$handler_path = $coreSettings['handlers_path']."/".ucfirst(strtolower($coreSettings['default_error_handler'])).".php";
			if (!file_exists($handler_path)) {
				return false;
			}
		}
		_debug("Found handler at ".$handler_path);
		
		//now we include the file with our absolute path to avoid inclusions to other places
		require_once($handler_path);
		
		//now we see if our fake constructor is there
		if (!function_exists('cunstructor')) return false;
		_debug("Found Constructor!");
		
		//run the constructor
		$successful_load = cunstructor($args);
		return $successful_load;
    }

	function maintenanceRedirect(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		$redirect=false;
		if ($coreSettings['preferences']['maint_mode']){
			if (!IsGuest() && !HasPermission($userInfo['user_id'], 'perm_admin_settings')) $redirect = true;
		}
		
		if ($redirect) header('Location: '.$coreSettings['preferences']['server_url'].'/maintenance/');
	}

?>