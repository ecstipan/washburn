<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: JSON API				*
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
    *  	
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		//see if we're logged in
		if(IsGuest()) header('Location: '.$coreSettings['preferences']['server_url']);
		
		if (isset($args[0]) && $args[0] == 'userlist') {
			if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) 
				header('Location: '.$coreSettings['preferences']['server_url']);
			
			
			
		} else if (isset($args[0]) && $args[0] == 'userinfo') {
			if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) 
				header('Location: '.$coreSettings['preferences']['server_url']);
			
			$info = array();
			$info['success']=true;
			$info['username']=$userInfo['username'];
			$info['first']=$userInfo['first_name'];
			$info['last']=$userInfo['last_name'];
			$info['id_number']=$userInfo['id_number'];
			$info['email']=$userInfo['email'];
			$info['phone']=$userInfo['phone'];
			
			if ($coreSettings['preferences']['registration_work_study'])
				$info['work_study']=(boolean)$userInfo['work_study'];
			
			$info['send_sms']=(boolean)$userInfo['send_sms'];
			$info['send_email']=(boolean)$userInfo['send_email'];
			$info['group']=$coreSettings['groups'][$userInfo['group']]['title'];
			
			$info['joined']=$userInfo['registered_date_pretty'];
			$info['total_lab_time']=secondsToTime($userInfo['total_time_in_lab']);
			$info['ip']=$_SERVER['REMOTE_ADDR'];
			
			echo json_encode($info);
		} else if (isset($args[0]) && $args[0] == 'serverinfo') {
			if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) 
				header('Location: '.$coreSettings['preferences']['server_url']);
			
			$info = array();
			$info['success']=true;
			$info['servername']=$coreSettings['preferences']['server_title'];
			$info['serverurl']=substr($coreSettings['preferences']['server_url'], 0, 45).'...';
			$info['email']=$coreSettings['preferences']['admin_email_addr'];
			
			//see if we exist
			$sql = "SELECT COUNT(*) as `count`
			FROM {{DB}}.`solidarity_users`;";
			
			$result = queryDB($sql);
			
			if (!$result || !isset($result[0]['count'])) echo 'failed';
			$info['usercount'] = $result[0]['count'];
			
			if(HasPermission($userInfo['user_id'], 'perm_admin_settings')) {
				$info['logs'] = UnreadLogCount();
				$info['liscence']=$coreSettings['serial_number'];
				$info['status']=$coreSettings['liscense_info'];
			}
			echo json_encode($info);
		} else if (isset($args[0]) && $args[0] == 'adminsettings') {
			if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) 
				header('Location: '.$coreSettings['preferences']['server_url']);		
			
			if(HasPermission($userInfo['user_id'], 'perm_admin_settings')) {
				//so we're able to be here
				$settings=array();
				$sql = "SELECT *
						FROM {{DB}}.`solidarity_settings`;";
						
				$result = queryDB($sql);
				
				if (!$result || !isset($result[0]['id'])) $settings['success'] = false;
				else {
					$settings['success'] = true;
					$settings['results'] = array();
					$i=0;
					while (isset($result[$i])) {
						$settings['results'][$i] = array();
						$settings['results'][$i]['name'] = $result[$i]['name'];
						$settings['results'][$i]['value'] = $result[$i]['value'];
						$settings['results'][$i]['type'] = $result[$i]['type'];
						$settings['results'][$i]['catagory'] = $result[$i]['catagory'];
						$i++;
					}
				}
				echo json_encode($settings);
			}else {
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
		} else if (isset($args[0]) && $args[0] == 'userlist') {
				if (HasPermission($userInfo['user_id'], 'perm_view_users')) {
					//we can be here
					//let's see is we can get more info or not
					
					//get our page
					if (!isset($args[1]) || $args[1] == "" || $args[1] != "page"){
						unset($args[0]);
						header('Location: '.$coreSettings['preferences']['server_url']."ajax/userlist/page/1/".implode('/', $args));
					} else {
						$page = intval(trim($args[2]));
						if ($page < 1) $page = 1;
						//count our users
						$sql = "SELECT COUNT(sid) AS `count`
						FROM {{DB}}.`solidarity_users` WHERE `activated` = '1' AND `disabled` = '0';";
						
						$result = queryDB($sql);
						if (!$result || !isset($result[0]['count'])) {
							$data['success'] = false;
							echo json_encode($aata); return;
						}
						if ($page > ceil($result[0]['count']/15)) $page = ceil($result[0]['count']/15);
					}
					$search = "";
					if (isset($args[3]) && $args[3]=="search") {
						//enumerate searches
						if (isset($args[4]) && $args[4] == 'first-asc') $search = "";
						if (isset($args[4]) && $args[4] == 'first-dec') $search = "";
						if (isset($args[4]) && $args[4] == 'last-asc') $search = "";
						if (isset($args[4]) && $args[4] == 'last-dec') $search = "";
						if (isset($args[4]) && $args[4] == 'join-asc') $search = "";
						if (isset($args[4]) && $args[4] == 'join-dec') $search = "";
						if (isset($args[4]) && $args[4] == 'email-asc') $search = "";
						if (isset($args[4]) && $args[4] == 'email-dec') $search = "";
						if (isset($args[4]) && $args[4] == 'ws-asc') $search = "";
						if (isset($args[4]) && $args[4] == 'ws-dec') $search = "";
						if (isset($args[4]) && $args[4] == 'time-asc') $search = "";
						if (isset($args[4]) && $args[4] == 'time-dec') $search = "";
						
						//admin-only searches
						if (HasPermission($userInfo['user_id'], 'perm_admin_users') && isset($args[4]) && $args[4] == 'disabled-asc') $search = "";
						if (HasPermission($userInfo['user_id'], 'perm_admin_users') && isset($args[4]) && $args[4] == 'disabled-dec') $search = "";
						if (HasPermission($userInfo['user_id'], 'perm_admin_users') && isset($args[4]) && $args[4] == 'activated-asc') $search = "";
						if (HasPermission($userInfo['user_id'], 'perm_admin_users') && isset($args[4]) && $args[4] == 'activated-dec') $search = "";
						
						
					}
					
					//get our search que
					
					$data = Array();
					if (HasPermission($userInfo['user_id'], 'perm_admin_users')) {
						//give more information
						//list all users, even those who are disabled
						$sql = "SELECT *
						FROM {{DB}}.`solidarity_users` LIMIT ".(($page-1)*15).",15;";
						
						$result = queryDB($sql);
						
						if (!$result || !isset($result[0]['sid'])) $data['success'] = false;
						else {
							$data['success'] = true;
							$data['results'] = array();
							$i=0;
							while (isset($result[$i])) {
								$data['results'][$i] = array();
								
								
								
								$i++;
							}
						}
					} else {
						//give them limited information
						
					}
					echo json_encode($data);
				} else header('Location: '.$coreSettings['preferences']['server_url']);
		} else {
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
		
		return true;
	}

?>