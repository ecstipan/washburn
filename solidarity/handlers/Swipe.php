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
		if(!IsGuest()) KillSession();
			
		if (isset($args[0]) && $args[0] >= 1) {
			$machineid = mysql_escape_string(trim(intval($args[0])));
			AuthenticateTerminal($machineid);
			
			//pull our room id from our machine id
			$sql = "SELECT `id_room`,`title` FROM {{DB}}.`solidarity_swipe_machines` 
					WHERE `id` = '".$machineid."';";
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['id_room'])) {
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
			
			$room = $result[0]['id_room'];
			
			$sql = "SELECT * FROM {{DB}}.`solidarity_rooms` 
					WHERE `id` = '".$room."';";
			$result = queryDB($sql);
			$room_title= "";
			$room_title = $result[0]['name'];
			
			_debug('Found room ID @'.$room);
			_debug('Current page generation now using key [#'.$machineid."@".$room."]");
			
			//we have our room information and are validated
			
			if (isset($args[1]) && $args[1] == 'swipein') {
				if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) 
					header('Location: '.$coreSettings['preferences']['server_url']);
			
				$info = array();
				$info['success']=false;
				
				$result = trySignIn($room);
				if ($result=='Success') $info['success']=true;
				else $info['merror']=$result;
			
				echo json_encode($info);
				exit();
			} else if (isset($args[1]) && $args[1] == 'swipeout') {
				if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) 
					header('Location: '.$coreSettings['preferences']['server_url']);
			
				$info = array();
				$info['success']=false;
				$info['merror']='Could not sign iout.';
				$info['closed']=false;
				
				$result = trySignOut($room);
				if ($result=='Success') $info['success']=true;
				if ($result=='Closed') {
					$info['success']=true;
					$info['closed']=true;
				}
				else $info['merror']=$result;
				echo json_encode($info);
			} else if (isset($args[1]) && $args[1] == 'listsignedin') {
				if (!isset($args[2]) || !SessionValid($args[2])) 
					header('Location: '.$coreSettings['preferences']['server_url']);
					
				$info = array();
				$info['success']=false;
				$info['merror']='Could not load data.';
				$info['result']=false;
				$info['result']=ListRoomMembers($room);
				
				if ($info['result'] != false) $info['success'] = true;
					
				echo json_encode($info);
			} else if (isset($args[1]) && $args[1] == 'germachinedata') {
				if (!isset($args[2]) || !SessionValid($args[2])) 
					header('Location: '.$coreSettings['preferences']['server_url']);
						
				$info = array();
				$info['success']=false;
				$info['error']='Could not load room data.';
				
				$sql = "SELECT *
						FROM {{DB}}.`solidarity_rooms` 
						WHERE `id` = '".$room."';";
						
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['id'])) {
					$info['error']='An unexpected error has occured.';
					echo json_encode($info);
					die();
				}
				
				$info['name'] = $result[0]['name'];
				$info['open'] = $result[0]['open'];
				
				$info['room_head_name'] = $result[0]['room_head_name'];
				$info['room_head_phone'] = $result[0]['room_head_phone'];
				
				$sql = "SELECT `disabled` FROM {{DB}}.`solidarity_swipe_machines` 
						WHERE `id` = '".$machineid."';";
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['disabled'])) {
					$info['error']='An unexpected error has occured.';
					echo json_encode($info);
					die();
				}
				
				$info['machine_disabled'] = $result[0]['disabled'];
				
				$info['success']=true;
				echo json_encode($info);
			} else if (isset($args[1]) && $args[1] == 'getlabmonitor') {
				if (!isset($args[2]) || !SessionValid($args[2])) 
					header('Location: '.$coreSettings['preferences']['server_url']);
				
				$info = array();
				$info['success']=false;
				$info['merror']='Could not load lab monitor data.';
				
				//grab all of our wonderful information
				$sql = "SELECT `solidarity_users`.`sid` AS `sid`, 
						`solidarity_users`.`first` AS `first`, 
						`solidarity_users`.`last` AS `last`, 
						`solidarity_users`.`id_group` AS `id_group`, 
						`solidarity_groups`.`title` AS `group`,
						`solidarity_last_lab_monitor`.`image` AS `image`, 
						`solidarity_last_lab_monitor`.`updated` AS `updated`
						
						FROM `solidarity_last_lab_monitor`, `solidarity_users`, `solidarity_groups`
						
						WHERE `solidarity_last_lab_monitor`.`sid` = `solidarity_users`.`sid`
						AND `solidarity_groups`.`id_group` = `solidarity_users`.`id_group`
						AND `solidarity_last_lab_monitor`.`id_room` = '".$room."';";
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['sid'])) {
					$info['merror']='An unexpected error has occured.';
					echo json_encode($info);
					die();
				}
				
				$info['name'] = ucfirst ($result[0]['first']).' '.ucfirst($result[0]['last']);
				$info['group'] = $result[0]['group'];
				$info['image'] = $result[0]['image'];
				$info['since'] = date('F j, Y, g:i a', strtotime($result[0]['updated']));
				$info['success'] = true;
				
				echo json_encode($info);
			} else if (isset($args[1]) && $args[1] == 'getuserinfoajax') {
				if (!isset($args[2]) || !SessionValid($args[2])) 
					header('Location: '.$coreSettings['preferences']['server_url']);
						
				$info = array();
				$info['success']=false;
				$info['error']='Could not load user data.';
				
				if (!isset($_POST['id_number']) || $_POST['id_number'] == "") {
					$info['error']='You did not provide a valid ID.';
					echo json_encode($info);
					die();
				}
				$id_number = mysql_escape_string(trim($_POST['id_number']));
				
				//check that we can get a valid ID
				$id_number = ProcessID($id_number);
				if(!$id_number) {
					$info['error']='The card reader could not read your ID.';
					echo json_encode($info);
					die();
				}
				
				//see if we exist
				$sql = "SELECT COUNT(`sid`) AS `count`
						FROM {{DB}}.`solidarity_users` 
						WHERE `id_number` = '".$id_number."';";
						
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['count'])) {
					$info['error']='An unexpected error has occured.';
					echo json_encode($info);
					die();
				}
				if ($result[0]['count'] <1) {
					$info['success']=true;
					$info['mustregister']=true;
					echo json_encode($info);
					die();
				}
				
				
				//now get their info
				$sql = "SELECT * 
						FROM {{DB}}.`solidarity_users` 
						WHERE `id_number` = '".$id_number."';";
						
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['sid'])) {
					$info['error']='An unexpected error has occured.';
					echo json_encode($info);
					die();
				}
				
				//safety checks
				if ($result[0]['disabled'] == 1) {
					$info['error']='Your account is disabled.';
					echo json_encode($info);
					die();
				}
				if ($result[0]['activated'] == 0) {
					$info['error']='Your account must be activated first.';
					echo json_encode($info);
					die();
				}
				
				$sql = "SELECT `sid` FROM {{DB}}.`solidarity_last_lab_monitor`
					WHERE `id_room` = '".$room."';";
				$resultz = queryDB($sql);
				if($resultz[0]['sid'] == $result[0]['sid']) {
					$info['reqconf'] = true;
				}
				
				//encode information
				$info['sid'] = $result[0]['sid'];
				$info['first'] = $result[0]['first'];
				$info['last'] = $result[0]['last'];
				$info['ws'] = $result[0]['work_study'];
				$info['fso'] = $result[0]['force_signed_out'];
				
				$info['fsodate'] = date('Y, n, j', strtotime($result[0]['fso_time_sign_in']));
				
				$info['sq'] = $result[0]['safety_quiz'];

				$info['inroom'] = IsSignedIn($result[0]['sid'], $room);

				$group = $result[0]['id_group'];
				
				//get lab monitor rights
				$open_close_p = false;
				loadGroup($group);
				//check for room permissions
				$sql = "SELECT count(`id_group`) AS `count`
						FROM {{DB}}.`solidarity_room_open_permissions` 
						WHERE `id_room` = '".$room."'
						AND `id_group` = '".$group."';";	
						
				$result = queryDB($sql);
				if (!isset($result[0]['count'])) {
					$info['error']='An unexpected error has occured.';
					echo json_encode($info);
					die();
				}
				if (GroupHasPermission($group, 'perm_open_close') && $result[0]['count'] >0) $open_close_p = true;
				$info['open_close'] = $open_close_p;
				
				//see if the lab is open
				$sql = "SELECT `open` 
						FROM {{DB}}.`solidarity_rooms` 
						WHERE `id` = '".$room."';";
						
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['open'])) {
					$info['error']='An unexpected error has occured.';
					echo json_encode($info);
					die();
				}
				$room_is_open = $result[0]['open'];
				if ($room_is_open==0 && !$open_close_p) {
					$info['error']='You cannot sign in when the room is closed.';
					echo json_encode($info);
					die();
				}
				
				$info['group'] = $coreSettings['groups'][$group]['title'];
				
				$info['success']=true;
				echo json_encode($info);
			} else if (isset($args[1]) && $args[1] == 'registeruser') {
				
				echo tryRegisterSwipe();
			} else if (isset($args[1]) && $args[1] == 'fso') {
				if (!isset($args[2]) || !SessionValid($args[2])) {
					echo '0'; 
					die();
				}
				
				if (!isset($_POST['id_number'])) {
					echo '0'; 
					die();
				}
					
				$id_number = ProcessID(mysql_escape_string(trim($_POST['id_number'])));
				
				$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `id_number` = '".$id_number."';";
				
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['sid'])) {
					echo '0'; 
					die();
				}
				
				$sid = $result[0]['sid'];
				
				//check if we're the last person in the lab
				$sql = "SELECT count(id) as `count` 
						FROM {{DB}}.`solidarity_signed_in_users` 
						WHERE `id_room` = '".$room."';";
				
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['count'])) {
					echo '0'; 
					die();
				}
				
				//we can't open it
				if ($result[0]['count'] > 1) {
					//there are others in the room
					//see if we're the room's lab monitor
					$sql = "SELECT `sid` FROM {{DB}}.`solidarity_last_lab_monitor`
							WHERE `id_room` = '".$room."';";
							
					$result = queryDB($sql);
					if (!$result || !isset($result[0]['sid'])) {
						echo '0'; 
						die();
					}
					if ($result[0]['sid'] == $sid) {
						echo '1'; die();
					}
					echo '0'; 
					die();
				}
			} else if (isset($args[1]) && $args[1] == 'uploadphoto') {
				if ($coreSettings['preferences']['swipe_photos_enabled']) {
					if (!isset($args[2]) || !SessionValid($args[2])) 
						header('Location: '.$coreSettings['preferences']['server_url']);
					
					$time = strtotime("now");
					
					//I don't think this really needs a function
					$str = file_get_contents("php://input");
					file_put_contents($coreSettings['application_path']."/uploads/swipe_".$room."_".$time.".jpg", pack("H*", $str));	
					
					$sql = "DELETE FROM {{DB}}.`solidarity_photo_stream` WHERE `id_room` = '".$room."';";
					$result = queryDB($sql);
					
					$sql = "INSERT INTO {{DB}}.`solidarity_photo_stream` (`id_room`, `path`) VALUES ('".$room."', 'swipe_".$room."_".$time.".jpg');";
					$result = queryDB($sql);
				} else die();
			} else if (isset($args[1]) && $args[1] == 'getlastphoto') {
				if ($coreSettings['preferences']['swipe_photos_enabled']) {
					if (!isset($args[2]) || !SessionValid($args[2])) 
						header('Location: '.$coreSettings['preferences']['server_url']);
					
					$sql = "SELECT `path` 
							FROM {{DB}}.`solidarity_photo_stream` 
							WHERE `id_room` = '".$room."';";
							
					$result = queryDB($sql);
					if (!$result || !isset($result[0]['path'])) {
						echo json_encode(Array('success' => false));
						return true;
					}
					
					$path = $result[0]['path'];
					echo json_encode(Array('success' => true, 'path' => $path));
				} else die();
			} else if (isset($args[1]) && $args[1] == 'printbadge') {
				if ($coreSettings['preferences']['swipe_print_sticker']) {
					//if (!isset($args[3]) || !SessionValid($args[3])) 
					//	header('Location: '.$coreSettings['preferences']['server_url']);
					
					if (!isset($args[2]) || $args[2] < 1) die();
					$id = intval(trim(mysql_escape_string($args[2])));
					printBadge($id, $room);
				}
			} else {
				//display our template
				$pageData['title'] = $room_title.' Sign In';
				$pageData['room'] = $room;
				$pageData['machine'] = $machineid;
				$pageData['camera'] = $coreSettings['preferences']['swipe_photos_enabled'];
				$pageData['use_ws'] = $coreSettings['preferences']['registration_work_study'];
				
				if ($coreSettings['preferences']['swipe_print_sticker']) $pageData['badge'] = 'true';
				else $pageData['badge'] = 'false';
				
				HTML5_head('Sign In');
				
				loadTemplate('swipe');
				
				HTML5_close_page();
			}
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

	function trySignIn($room) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($_POST['id_number']) || $_POST['id_number'] == "") return 'Invalid ID number.';
		
		$id_number = mysql_escape_string(trim($_POST['id_number']));
		
		//check that we can get a valid ID
		$id_number = ProcessID($id_number);
		if(!$id_number) return 'Failed to process ID';
		
		//so our id is is valid
		//grab out other info
		$sid = -1;
		
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `id_number` = '".$id_number."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) {
			//registering
			return 'You must register first.';
		}
		
		$group=$result[0]['id_group'];
		$sid=$result[0]['sid'];
		
		loadGroup($group);
		if (IsSignedIn($sid, $room)) return 'You are already signed into another workspace.';
		
		//get our photo stuff
		if(!isset($_POST['photourl'])) return 'Cannot find photo';
		$photourl = trim(mysql_escape_string($_POST['photourl']));
		
		
		//se we're able to sign in
		//check if the room is open
		$sql = "SELECT `open` 
				FROM {{DB}}.`solidarity_rooms` 
				WHERE `id` = '".$room."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['open'])) return 'Failed to gather room information.';
		
		$open = $result[0]['open'];
		if($open!=1) {
			//room is closed - check for open permissions
			//check for permissions
			if (!GroupHasPermission($group, 'perm_open_close')) return 'You cannot use this room when it is closed.';
			//see if they also have room-spepcific permissions
			
			$sql = "SELECT count(id_room) as `count` 
					FROM {{DB}}.`solidarity_room_open_permissions` 
					WHERE `id_room` = '".$room."' 
					AND `id_group` = '".$group."';";
			
			$result = queryDB($sql);
			if (!$result ||!isset($result[0]['count'])) return 'Could not find room permissions';
			
			//we can't open it
			if ($result[0]['count'] < 1) return 'You do not have permission to open this room.';
			
			//open the lab
			if (!OpenRoom($room, $sid, $photourl)) return 'Failed to open room.';
		}
		
		//sign them in
		//check for FSO
		$sql = "SELECT `force_signed_out`
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['force_signed_out'])) return 'Could not get FSO Information';
		$FSO = $result[0]['force_signed_out'];		
		if($FSO==1){
			//require signed out timestamp
			if (!isset($_POST['fso_timestamp'])) return 'Missing information.';
			$inputdate = trim(mysql_escape_string($_POST['fso_timestamp']));
			
			//update fso
			if(!FixLastForce($sid,$inputdate)) return 'Could not update your last session.';
		}
		
		//see if we're doing the safety quiz
		if (isset($_POST['quiz']) && $_POST['quiz']==1)
			PassSafetyQuiz($sid);
		
		//see if we have after hours
		$enter_permission = false;
		if (!IsBusinessHours()) {
			_debug('after hours');
			if (GroupHasPermission($group, 'perm_after_hours')) {
				$enter_permission = true;
			}
		} else {
			_debug('not after hours');
			$enter_permission = true;
		}
		
		if (!$enter_permission) {
			_debug('No Group Permission for after hours.');
			return 'You cannot sign in at this time.';
		}
		
		//sign them in
		if (!SignIn($id_number, $room, $photourl)) return 'Could not sign in. '.$id_number.' '.$room.' '.$photourl;
		
		//see if we are assuming the role of lab monitor
		//check for permisisons first
		$switchperm = false;
		$sql = "SELECT count(id_room) as `count` 
				FROM {{DB}}.`solidarity_room_open_permissions` 
				WHERE `id_room` = '".$room."' 
				AND `id_group` = '".$group."';";
		
		$result = queryDB($sql);
		if (!$result ||!isset($result[0]['count'])) return 'Could not find room permissions';
		
		if (GroupHasPermission($group, 'perm_open_close') && $result[0]['count'] >=1) {
			//see if we have our switch set
			if (isset($_POST['pass_mon'])) {
				//get their id
				$pass = intval(trim(mysql_escape_string($_POST['pass_mon'])));
					if($pass==1){
					
					//get gurrent lab monitor
					$old=0;
					$sql = "SELECT `sid` FROM {{DB}}.`solidarity_last_lab_monitor` 
						WHERE `id_room` = '".$room."';";
				
				$result = queryDB($sql);
				if (!$result ||!isset($result[0]['sid'])) return 'Could not assume lab monitor.';
					$old = $result[0]['sid'];
					if (!PassLabMonitor($old, $room, $sid, $photourl)) return "Could not assume lab monitor.";
				}
			}
		}
		
		//so we got through everything
		return 'Success';
	}

	function trySignOut($room) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		if (!isset($_POST['id_number']) || $_POST['id_number'] == "") return 'Invalid ID number.';
		$id_number = mysql_escape_string(trim($_POST['id_number']));
		
		//check that we can get a valid ID
		$id_number = ProcessID($id_number);
		if(!$id_number) return 'Failed to process ID';
		
		//so our id is is valid
		//grab out other info
		$sid = -1;
		$group=$coreSettings['preferences']['registration_default_group'];
		
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `id_number` = '".$id_number."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) return 'A Critical Error Occured!';
		
		$sid = $result[0]['sid'];
		
		loadGroup($group);
		if (!IsSignedIn($sid, $room)) return 'You are not signed in to this room.';
		
		//check if we're the last person in the lab
		$sql = "SELECT count(id) as `count` 
				FROM {{DB}}.`solidarity_signed_in_users` 
				WHERE `id_room` = '".$room."';";
		
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['count'])) return 'Could not poll room information.';
		
		//we can't open it
		if ($result[0]['count'] == 1) {
			//just sign out and close the room
			//sign out
			if (!SignOut($id_number, $room)) return 'Could not sign out.';
			
			//close room
			if (!CloseRoom($room, $sid)) return 'Could not close room.';
			
			return 'Closed';
		} else {
			//there are others in the room
			//see if we're the room's lab monitor
			$sql = "SELECT `sid` FROM {{DB}}.`solidarity_last_lab_monitor`
					WHERE `id_room` = '".$room."';";
	
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['sid'])) return 'Could not find lab monitor!';
			if($result[0]['sid'] == $sid) {
				//so we're the lab monitor and there are people here
				//check for FSO
				
				//force sign everyone out
				$sql = "SELECT `solidarity_users`.`id_number` AS `id_number`
						FROM {{DB}}.`solidarity_signed_in_users`, {{DB}}.`solidarity_users`  
						WHERE `solidarity_signed_in_users`.`id_room` = '".$room."'
						AND `solidarity_signed_in_users`.`sid` = `solidarity_users`.`sid`;";
				
				$result = queryDB($sql);
				if (!$result || !isset($result[0]['id_number'])) return 'Could not poll room information.';
				
				$i=0;
				$len = count($result);
				while($i < $len) {
					if ($result[$i]['id_number'] != $id_number)
						if (!SignOut($result[$i]['id_number'], $room, true)) return 'Could not sign out all users.';
					$i++;
				}
				
				if (!SignOut($id_number, $room)) return 'Could not sign out.';
				
				// close the room
				if (!CloseRoom($room, $sid)) return 'Could not close room.';
			} else {
				//just sign them out
				if (!SignOut($id_number, $room)) return 'Could not sign out.';
			}
		}
		
		//we should be signed out
		//and we got through everything
		return 'Success';
	}

	function printBadge($id, $room) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		if ($coreSettings['preferences']['swipe_print_sticker']) {
			
			if (!isset($id)) die();
			
			//get their information
			
			$sid = intval(mysql_escape_string(trim($id)));
			
			$sql = "SELECT `solidarity_users`.`first` AS `first`, 
					`solidarity_users`.`last` AS `last`,
					`solidarity_users`.`work_study` AS `work_study`,
					`solidarity_users`.`safety_quiz` AS `safety_quiz`,
					`solidarity_users`.`id_group` AS `gid`,
					`solidarity_groups`.`title` AS `group`
					FROM `solidarity_users`, `solidarity_groups`
					WHERE `solidarity_groups`.`id_group` = `solidarity_users`.`id_group`
					AND `solidarity_users`.`sid` = '".$sid."';";
					
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['work_study'])) exit();
			
			$pageData['room'] = $room;
			
			//load it into the page data
			$pageData['first'] = $result[0]['first'];
			$pageData['last'] = $result[0]['last'];
			$pageData['group'] = $result[0]['group'];
			$pageData['guest'] = $result[0]['safety_quiz'] == 0 ? true : false;
			$pageData['work_study'] = $result[0]['work_study'] == 1 ? true : false;
			$pageData['machines'] = Array();
			
			$pageData['date'] = date('m/d/y');
			
			$gid = $result[0]['gid'];
			
			$pageData['mon'] = false;
			$open_permission = false;
			$open_permission = GroupHasPermission($gid, 'perm_open_close');
			$sql = "SELECT count(id_room) as `count` 
					FROM {{DB}}.`solidarity_room_open_permissions` 
					WHERE `id_room` = '".$room."' 
					AND `id_group` = '".$gid."';";

			$result = queryDB($sql);
			if (!$result || !isset($result[0]['count'])) {
				exit();
			} else {
				if ($open_permission && $result[0]['count'] > 0) $pageData['mon'] = true;
			}
			
			//load our machine permissions
			$sql = "SELECT `id`, `abv` FROM `solidarity_machines`
					WHERE `id_room` = '".$room."';";
			
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['id'])) exit();
			
			$i=0;
			while ($i<count($result)) {
				$pageData['machines'][$i]=Array();
				$pageData['machines'][$i]['id'] = $result[$i]['id'];
				$pageData['machines'][$i]['abv'] = $result[$i]['abv'];
				
				//check our permissions
				$sql = "SELECT count(`id`) AS `count` FROM `solidarity_machine_permissions`
						WHERE `id_tool` = '".$pageData['machines'][$i]['id']."' AND `sid` = '".$sid."';";
						
				$resultz = queryDB($sql);
				if (!$resultz || !isset($resultz[0]['count'])) exit();
				
				if ($resultz[0]['count'] >= 1) $pageData['machines'][$i]['perm'] = true;
				
				$i++;
			}
			
			//load our badge template
			loadTemplate('badge');
		}
	}
	
	function tryRegisterSwipe() {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//let's check for everything
		if(!isset($_POST['username']) || $_POST['username'] == "") return 'Missing username';
		if(!isset($_POST['password']) || $_POST['password'] == "") return 'Missing password';
		if(!isset($_POST['password_again']) || $_POST['password_again'] == "") return 'Missing confirm password';
		if(!isset($_POST['email']) || $_POST['email'] == "") return 'Missing or invlid email';
		if(!isset($_POST['first']) || $_POST['first'] == "") return 'Missing first name';
		if(!isset($_POST['last']) || $_POST['last'] == "") return 'Missing last name';
		if(!isset($_POST['idnumber']) || $_POST['idnumber'] == "") return 'Missing ID number';
		
		if(!isset($_POST['phone']) || $_POST['phone'] == "") return 'Missing phone number';
		
		if($coreSettings['preferences']['registration_work_study']) {
			if(!isset($_POST['workstudy']) || $_POST['workstudy'] == "") return 'Missing work study flag';
		}
		
		if(!isset($_POST['session_key']) || $_POST['session_key'] == "") return 'HACKING ATTEMPT';
		
		//validate our session
		$key = trim($_POST['session_key']);
		if (!SessionValid($key)) return 'HACKING ATTEMPT';
		
		$userdata = array();
		$userdata['username'] = mysql_escape_string(strtolower(trim(htmlspecialchars_decode($_POST['username']))));
		
		$password_a = mysql_escape_string(trim(htmlspecialchars_decode($_POST['password'])));
		$password_b = mysql_escape_string(trim(htmlspecialchars_decode($_POST['password_again'])));
		if ($password_a != $password_b) return 'Passwords did not match';
		$userdata['password'] = $password_a;
		
		$userdata['email'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['email'])));
		$userdata['first'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['first'])));
		$userdata['last'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['last'])));
		$userdata['id_number'] = ProcessID(mysql_escape_string(trim(htmlspecialchars_decode($_POST['idnumber']))));
		$userdata['phone'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['phone'])));
		
		if($coreSettings['preferences']['registration_work_study']) {
			$userdata['work_study'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['workstudy'])));
		}
		
		//everything has been cleaned
		return RegisterUser($userdata, true);
	}
?>