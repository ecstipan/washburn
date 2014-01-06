<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: Activate	*
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
    *  	int activateRequest(int sid, string key)
    * 		- caled when AJAX posts data or when a URI is invoked
    * 		- atempts to activate the user
    * 		- returns numerical error codes that AJAX interprets into strings
    * 
    *	int resetRequest()
    * 		- pulls data from AJAX POST
    * 		- attempts to find a valid unactivated account
    * 		- emails the user
    * 		- returns numerical error codes that AJAX uses to display strings in the frontend
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		//see if we're logged in
		if(!IsGuest()) header('Location: '.$coreSettings['preferences']['server_url']);
			
		if (isset($_POST['activation_key'])){
			//validate the session
			if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) die('Hacking attempt...');
			
			//obtain our user id
			$email = trim(mysql_escape_string($_POST['email']));
			$sql = "SELECT sid 
			FROM {{DB}}.`solidarity_users` 
			WHERE `email` = '".$email."' 
			AND `activated` = '0'
			;";
			
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['sid'])) {
				echo 2;
				return;
			}
			
			//grab our sid
			$sid = $result[0]['sid'];
			
			//process our key
			$key = trim(mysql_escape_string($_POST['activation_key']));
			
			//AJAX activation
			echo activateRequest($sid, $key);
		} else if (isset($_POST['reset'])){
			//AJAX resend request
			echo resetRequest();
		} else if (isset($args[0]) && $args[0] > 0) {
			//link activation
			$sid = trim(mysql_escape_string($args[0]));
			$key = trim(mysql_escape_string($args[1]));
			
			//send the request
			$result = activateRequest($sid, $key);
			
			//redirect
			if ($result == 1){
				header('Location: '.$coreSettings['preferences']['server_url']);
			} else {
				header('Location: '.$coreSettings['preferences']['server_url'].'activate/');
			}
			
			echo $result;
		} else {
			//display page
			$pageData['show_resent'] = 'false';
			$pageData['show_resent_fail'] = 'false';
			
    		HTML5_head('Activate Account');
			
			$pageData['headline'] = 'Account Activation';
			$pageData['ac_text'] = "Activate Account";
			$pageData['resend'] = "Resend";
			$pageData['email'] = "Email";
			$pageData['ack'] = "Key";
			$pageData['ac_button_text'] = "Activate";
			$pageData['session_key'] = GetSessionKey();
			
			//load our login form template
			loadTemplate('activate');
			
			HTML5_close_page();
		}
		
		return true;
	}
	
	function activateRequest($sid, $key){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($sid) || $sid < 0) return 0;
		if (!isset($key) || $key == "") return 2;
		
		//activate them
		if (!ActivateUser($sid, $key)) return 2;
		
		//success code
		return 1;
	}
	
	function resetRequest(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($_POST['email'])) return 0;
		if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) return 0;
		$email = trim(mysql_escape_string($_POST['email']));
		
		//grab our sid
		$sql = "SELECT sid 
				FROM {{DB}}.`solidarity_users` 
				WHERE `email` = '".$email."' 
				AND `activated` = '0' 
				LIMIT 0, 1;";
			
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) return 0;
		$sid = $result[0]['sid'];
		
		//resend the activation email
		if (!SendActivationEmail($sid)) return 0;
		
		//success
		return 1;
	}
?>