<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: Home	*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This type of file is called a handler
    * 	Every handler has a function called the constructor.
    * 	This must exst in the file for any code to be loaded.
    * 	In a way, these work like simplified classes
    * 	These can have other functions in them as well, and these 
    * 	can build off of the sources directory
    * 
    * 	Handlers can also decide to load templat html files or not to build off of.
    * 
    * 	boolean constructor(array args)
    * 		- sets everything up for us
    * 		- process any data and runs queries
    * 		- returns false if there is an error
    * 	
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
    	_debug('Welcome to the Home Page Handler.  This is set as our default.');
		
		if (isset($args[0])) {
			$craparray= array();
			$craparray[0] = 'Aww Jeez!';
			$craparray[1] = 'Oh Snap!';
			$craparray[2] = 'Oops!';
			$craparray[3] = 'Sorry!';
			$craparray[4] = 'Uh Oh!';
			$craparray[5] = 'Hmmmm...';
			
			$text = $craparray[array_rand($craparray)];
					
			$pageData['title'] = $text;
			$pageData['warning'] = 'We can\'t seem to locate this.  The requested page could not be found.  Please make sure that you have entered a correct URL.
			If this does not solve the problem, please contact '.$coreSettings['preferences']['admin_email_addr'].' for assistance.';
			
			HTML5_head( '404 Error');
			
			loadTemplate('404');
			
			HTML5_close_page();
		}
		
		if (IsGuest()) {
			_debug('Loading Login Screen');
			header('Location: ./login/');
		}else{
			_debug('Loading Dashboard');
			HTML5_head($coreSettings['preferences']['server_title'].' | Control Panel');
			
			$pageData['header'] = $coreSettings['preferences']['server_title'];
			$pageData['header_url'] = $coreSettings['preferences']['server_url'];
			$pageData['session_key'] = GetSessionKey();
			$pageData['work_study'] = $coreSettings['preferences']['registration_work_study'];
			
			//only display certain things
			$pageData['mainicons'] = array();
			$pageData['mainicons']['account'] = true;
			$pageData['mainicons']['schedule'] = true;
			
			//get the rest of our permisisons
			if (HasPermission($userInfo['user_id'], 'perm_admin_settings')) {
				$pageData['mainicons']['admin'] = true;
				$pageData['mainicons']['console'] = true;
				$pageData['mainicons']['floorplan'] = true;
				$pageData['mainicons']['rooms'] = true;
				$pageData['mainicons']['swipe'] = true;
			}
			
			//rss
			if ($coreSettings['preferences']['rss_enable']){
				$pageData['mainicons']['rss'] = true;
				
				//get the rss url
				$rss_url = $coreSettings['preferences']['server_url'].'rss/';
				if ($coreSettings['preferences']['rss_use_password']) $rss_url .= $coreSettings['preferences']['rss_password'].'/';
				
				$pageData['rss_url'] = $rss_url;
			}
			
			//other permissions
			if (HasPermission($userInfo['user_id'], 'perm_view_users')) $pageData['mainicons']['users'] = true;
			if (HasPermission($userInfo['user_id'], 'perm_admin_groups')) $pageData['mainicons']['groups'] = true;
			if (HasPermission($userInfo['user_id'], 'perm_view_projects')) $pageData['mainicons']['projects'] = true;
			if (HasPermission($userInfo['user_id'], 'perm_logs')) $pageData['mainicons']['logs'] = true;
	 
	 		//calculate the best width for our icon grid
	 		$numbericons = count($pageData['mainicons']);
			if ($numbericons == 2 || $numbericons == 4)	 $width = 360;
			if ($numbericons == 7 || $numbericons == 8 || $numbericons == 10 || $numbericons == 11 || $numbericons == 12)	 $width = 720;
			else $width = 540;
	 
	 		$pageData['iconds_width'] = $width;
			$pageData['iconds_m_l_small'] = ($width/2) - 125;
			$pageData['iconds_m_l_big'] = $width/2;
	 		
			//check for liscense info
			if($coreSettings['liscense_info'] == 'EXPIRED' && HasPermission($userInfo['user_id'], 'perm_admin_settings'))
			$pageData['liscense_popup'] = true;
			else $pageData['liscense_popup'] = false;
			
			loadTemplate('dashboard');
			
			HTML5_close_page();
		}
		
		return true;
	}
?>