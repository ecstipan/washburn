<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: Admin Settings			*
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
    * 	
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		//see if we're logged in
		if(IsGuest()) header('Location: '.$coreSettings['preferences']['server_url']);
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) header('Location: '.$coreSettings['preferences']['server_url']);
		
		if (isset($_POST['save'])){
			//AJAX resend request
			echo saveSettings();
		} else if (isset($args[0])) {
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
		} else {
			$pageData['header_url'] = $coreSettings['preferences']['server_url'];
			$pageData['header'] = 'System Administration';
			
			$pageData['session_key'] = GetSessionKey();
			$pageData['time'] = '<br>The server\'s local time is:<br>'.date('l jS \of F Y'). '<br>'.date('h:i:s A');
			if($coreSettings['liscense_info'] == 'EXPIRED' && HasPermission($userInfo['user_id'], 'perm_admin_settings'))
			$pageData['liscense_popup'] = true;
			else $pageData['liscense_popup'] = false;
			
			//display page
    		HTML5_head('System Administration');

			//load our login form template
			loadTemplate('admin');
			
			HTML5_close_page();
		}
		
		return true;
	}
	
	function saveSettings(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) 
			header('Location: '.$coreSettings['preferences']['server_url']);
		
		$response = array();
		$response['success'] = true;
		$response['message'] = 'Settings saved.';
		
		$settings= array();

		//start by validating all of our required values
		$sql = "SELECT name, type, value, email FROM {{DB}}.`solidarity_settings` WHERE `required` = '1';";
		$result=queryDB($sql);
		if (!$result) {
			$response['success'] = false;
			$response['message'] = 'Could Not Find Values.';
			return json_encode($response);
		}
		
		//load into settings
		foreach($result as $row){
			if (!isset($_POST[$row['name']]) || $_POST[$row['name']]=="") {
				$response['success'] = false;
				$response['message'] = 'Missing Required Value.';
				return json_encode($response);
			}
			if($row['email']==1) {
				$email 			= trim(mysql_escape_string(urldecode($_POST[$row['name']])));
				$emailarray		= array();
				$emailarray 	= explode("@", $email);
				if (count($emailarray) !=2) {
					$response['success'] = false;
					$response['message'] = 'Invalid Email.';
					return json_encode($response);
				}
				
				$dotarr			= array();
				$dotarr 		= explode('.', $emailarray[1]);
				if (count($dotarr) > 2) {
					$response['success'] = false;
					$response['message'] = 'Invalid Email.';
					return json_encode($response);
				}
				
				$domain 		= $dotarr[count($dotarr)-1];
				if ($domain!="com" && $domain!="net" && $domain!="org" && $domain!="edu") {
					$response['success'] = false;
					$response['message'] = 'Invalid Email.';
					return json_encode($response);
				}
			}
			$settings[$row['name']]=trim(mysql_escape_string(urldecode($_POST[$row['name']])));
		}

		//check for conditional required values
		if ($settings['mail_smtp']==1) {
			$topic = 'mail_smtp_host';
			if (!isset($_POST[$topic]) || $_POST[$topic]=="") {
				$response['success'] = false;
				$response['message'] = 'Missing Required Value: '.$topic;
				return json_encode($response);
			}
			$settings[$topic]=trim(mysql_escape_string(urldecode($_POST[$topic])));
			$topic = 'mail_smtp_port';
			if (!isset($_POST[$topic]) || $_POST[$topic]=="") {
				$response['success'] = false;
				$response['message'] = 'Missing Required Value: '.$topic;
				return json_encode($response);
			}
			$settings[$topic]=trim(mysql_escape_string(urldecode($_POST[$topic])));
		}
		if ($settings['mail_smtp_auth']==1) {
			$topic = 'mail_smtp_user';
			if (!isset($_POST[$topic]) || $_POST[$topic]=="") {
				$response['success'] = false;
				$response['message'] = 'Missing Required Value: '.$topic;
				return json_encode($response);
			}
			$settings[$topic]=trim(mysql_escape_string(urldecode($_POST[$topic])));
			$topic = 'mail_smtp_password';
			if (!isset($_POST[$topic]) || $_POST[$topic]=="") {
				$response['success'] = false;
				$response['message'] = 'Missing Required Value: '.$topic;
				return json_encode($response);
			}
			$settings[$topic]=trim(mysql_escape_string(urldecode($_POST[$topic])));
		}
		if ($settings['rss_use_password']==1) {
			$topic = 'rss_password';
			if (!isset($_POST[$topic]) || $_POST[$topic]=="") {
				$response['success'] = false;
				$response['message'] = 'Missing Required Value: '.$topic;
				return json_encode($response);
			}
			$settings[$topic]=trim(mysql_escape_string(urldecode($_POST[$topic])));
		}
		if ($settings['registration_email_filter']==1) {
			$topic = 'registration_email_domain';
			if (!isset($_POST[$topic]) || $_POST[$topic]=="") {
				$response['success'] = false;
				$response['message'] = 'Missing Required Value: '.$topic;
				return json_encode($response);
			}
			$settings[$topic]=trim(mysql_escape_string(urldecode($_POST[$topic])));
		}

		//now we get to update the settings
		foreach($settings as $name => $value) {
			if (!saveSetting($name, $value))  {
				$response['success'] = false;
				$response['message'] = 'Failed to save '.$name.'!';
				return json_encode($response);
			}
		}
		
		//success
		return json_encode($response);
	}
?>