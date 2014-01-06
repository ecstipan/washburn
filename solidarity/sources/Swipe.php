<?php
    /*===========================================*
    * 											*
    *  @Title:	Swipe and sign-in functions		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	string ProcessID(string rawID)
    * 		- 
    * 	
    * 	boolean IsBusinessHours()
    * 		- 
    * 
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	
	function ProcessID($rawID) {
		if (!isset($rawID)) return '';
		//%46595434901?;46595434901?+46595434901?
		
		$ids = explode('?', $rawID);
		$id=false;
		if(isset($ids[0]) && strlen($ids[0])>4) $id = $ids[0];
		if(isset($ids[1]) && strlen($ids[1])>4) $id = $ids[1];
		if(isset($ids[2]) && strlen($ids[2])>4) $id = $ids[2];
		
		if (!$id) return '';
		
		
		if (substr($id, 0, 1) == '+' || substr($id, 0, 1) == ';' || substr($id, 0, 1) == '%' ) {
			$id = substr($step2, 1);
		}
		
		if (strlen($id)>9) $id = substr($id, 0, strlen($id)-2);
				
		if (strpos($id, "e") > 0) return false;
		
		return trim($id);
	}
	
	function IsBusinessHours(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		$hour = intval(date('G'));
		$variable_start = 'hours_'.strtolower(date('l')).'_start';
		$variable_end = 'hours_'.strtolower(date('l')).'_end';
		
		
		_debug('Curent hour: '.$hour);
		_debug($variable_start.' '.$variable_end);
		
		$start = intval($coreSettings['preferences'][$variable_start]);
		$end = intval($coreSettings['preferences'][$variable_end]);
		
		if ($hour >= $start && $hour < $end) return true;
		return false;
	}
	
	function LoadCurrentLabMonitor($room){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		if (!isset($room)) return false;
		$mon = Array();
		
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_last_lab_monitor` 
				WHERE `id_room` = '".trim($room)."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) return false;
		
		$id = $result[0]['sid'];
		$image = $result[0]['image'];
		
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) return false;
		
		$first = $result[0]['first'];
		$last = $result[0]['last'];
		$group_id = $result[0]['id_group'];
		
		loadGroup($group_id);
		$group_name = $coreSettings['groups'][$group_id]['title'];
		
		$mon['first'] = $first;
		$mon['last'] = $last;
		$mon['image'] = $image;
		$mon['group_name'] = $group_name;
		
		return $mon;
	}
	
	function SignIn($idnumber, $room, $photourl, $projectid = null){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($idnumber)) return false;
		if (!isset($room)) return false;
		if ($coreSettings['preferences']['swipe_photos_enabled']) {
			if (!isset($photourl)) return false;
		}
		_debug('We have all of our information');
		//get their id number
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `id_number` = '".$idnumber."' AND `activated` = '1' AND `disabled` = '0';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) {
			_debug('Could not find user.');
			return false;
		}
		$groupid = $result[0]['id_group'];
		$sid = $result[0]['sid'];
		$username = $result[0]['username'];
		
		loadGroup($groupid);
		
		$enter_permission = false;
		
		//see if we have after hours
		if (!IsBusinessHours()) {
			_debug('after hours');
			if (GroupHasPermission($groupid, 'perm_after_hours')) {
				$enter_permission = true;
			}
		} else {
			_debug('not after hours');
			$enter_permission = true;
		}
		
		if (!$enter_permission) {
			_debug('No Group Permission for after hours.');
			return false;
		}
		
		if (IsSignedIn($sid, $room)) {
			_debug('signed in already.');
			return false;
		}
		
		//get our room information
		$sql = "SELECT `name` 
				FROM {{DB}}.`solidarity_rooms` 
				WHERE `id` = '".$room."';";
				
		$results = queryDB($sql);
		if (!$results || !isset($results[0]['name'])) {
			_debug('Could not find room. '.$room.' '.$results[0]['name']);
			return false;
		}
		
		$roomname = $results[0]['name'];
		
		//so now that we have permission, let's sign in
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `fso_time_sign_in` = NOW(),
				`force_signed_out` = '0',
				`fso_last_room` = '".$room."'
				WHERE `sid` = '".$sid."';";
		
		$result = queryDB($sql);
		
		_debug('Found room!');
		
		//add us to the in lab table
		$sql = "INSERT INTO {{DB}}.`solidarity_signed_in_users` 
				(`sid`, `id_room`, `signed_in_time`) VALUES ('".$sid."', '".$room."', NOW());";
		
		$result = queryDB($sql);
		
		if (!$coreSettings['preferences']['swipe_photos_enabled']) $photourl = "/blank.jpg";
		
		//log it
		if (!AddLogEvent(13, $sid, $username.' signed in to '.$roomname.'.', $sid, $photourl, $room))  {
			_debug('Failed to log.');
		}
		
		return true;
	}
	
	function SignOut($idnumber, $room, $forced = false){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;

		if (!isset($idnumber)) return false;
		if (!isset($room)) return false;
		_debug('Signing out');
		//get their id number
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `id_number` = '".$idnumber."' AND `activated` = '1' AND `disabled` = '0';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) {
			_debug('Could not find user in users table');
			return false;
		}
		
		loadGroup($result[0]['id_group']);
		
		$sid = $result[0]['sid'];
		$username = $result[0]['username'];
		
		if (!IsSignedIn($sid, $room)) return false;
		
		//get our room information
		$sql = "SELECT `name` 
				FROM {{DB}}.`solidarity_rooms` 
				WHERE `id` = '".$room."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['name']))  {
			_debug('Could not find room in rooms table');
			return false;
		}
		
		$roomname = $result[0]['name'];
		
		//calculate their signed in time
		$totallt = 0;
		$sql = "SELECT `signed_in_time`
				FROM {{DB}}.`solidarity_signed_in_users` 
				WHERE `id_room` = '".$room."'
				AND `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['signed_in_time']))  {
			_debug('Could not find room time');
			return false;
		}
		$signed_in_time = $result[0]['signed_in_time'];
		_debug($signed_in_time.' '.strtotime($signed_in_time));
		
		$totallt = time() - strtotime($signed_in_time);
		
		_debug('Adding '.$totallt.' to lab time');
		
		//let's sign out
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `force_signed_out` = '".intval($forced)."',
				`total_time_in_lab` = `total_time_in_lab` + ".$totallt."
				WHERE `sid` = '".$sid."';";
		
		$result = queryDB($sql);
		
		//remove us to the in lab table
		$sql = "DELETE FROM {{DB}}.`solidarity_signed_in_users` 
				WHERE `sid` = '".$sid."' 
				AND `id_room` = '".$room."';";
		
		$result = queryDB($sql);
		if (!$result)  {
			_debug('Could not delete room time');
			return false;
		}
		//log it
		if ($forced) if (!AddLogEvent(14, $sid, $username.' forgot to sign out of '.$roomname.'.', $sid, 'NULL', $room)) return false;
		
		if (!AddLogEvent(14, $sid, $username.' signed out of '.$roomname.'.', $sid, 'NULL', $room)) return false;
		
		return true;
	}
	
	function FixLastForce($sid, $inputdate) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		_debug('Updating sign ourt information');
		if (!isset($sid)) return false;
		//get our project and last sign in
		$sql = "SELECT `fso_time_sign_in`, `fso_last_project`
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['fso_time_sign_in'])) return false;
		
		$signed_in_time = $result[0]['fso_time_sign_in'];
		$totallt = strtotime($inputdate) - strtotime( $signed_in_time );
		
		//$project = $result[0]['fso_last_project'];
		
		//check if we're subtracting time
		if ($totallt <0) return false;
		
		//let's sign out
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `force_signed_out` = '0',
				`total_time_in_lab` = `total_time_in_lab` + '".$totallt."',
				WHERE `sid` = '".$sid."';";
		
		$result = queryDB($sql);
		
		//update our project tiem once we have projects
		
		
		return true;
	}
	
	function OpenRoom($room, $sid, $img) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($sid)) return false;
		if (!isset($room)) return false;
		if (!isset($img)) return false;
		
		//get their id number
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."' AND `activated` = '1' AND `disabled` = '0';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) {
			_debug('Could not find id!');
			return false;
		}
		$groupid = $result[0]['id_group'];
		$username = $result[0]['username'];
		
		loadGroup($groupid);
		
		$open_permission = false;
		
		$open_permission = GroupHasPermission($groupid, 'perm_open_close');
		
		if (!$open_permission) {
			_debug('No Global Open Permission!');
			return false;
		}
		
		$sql = "SELECT count(id_room) as `count` 
				FROM {{DB}}.`solidarity_room_open_permissions` 
				WHERE `id_room` = '".$room."' 
				AND `id_group` = '".$groupid."';";
		
		$result = queryDB($sql);
		if (!$result ||!isset($result[0]['count'])) {
			_debug('SQL ERROR');
			return false;
		}
		
		//we can't open it
		if ($result[0]['count'] < 1) {
			_debug('No Room Open Permission!');
			return false;
		}
		//we can open it
		//open the room
		$sql = "UPDATE {{DB}}.`solidarity_rooms` 
				SET `open` = '1'
				WHERE `id` = '".$room."';";
				
		$result = queryDB($sql);
		
		//get our room information
		$sql = "SELECT `name` 
				FROM {{DB}}.`solidarity_rooms` 
				WHERE `id` = '".$room."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['name'])) {
			_debug('Could not find room');
			return false;
		}
		
		$roomname = $result[0]['name'];
		
		//setup our last lab monitor
		$sql = "DELETE FROM {{DB}}.`solidarity_last_lab_monitor` 
				WHERE `id_room` = '".$room."';";
		$result = queryDB($sql);
		
		$sql = "INSERT INTO {{DB}}.`solidarity_last_lab_monitor` 
				(`sid`, `id_room`, `image`) VALUES ('".$sid."', '".$room."', '".$img."');";
		$result = queryDB($sql);
		
		//log it
		_debug('Logging actions');
		if (!AddLogEvent(15, $sid, $username.' opened room '.$roomname.'.', $sid, 'NULL', $room)) {
			_debug('Failed to log event!');
			return false;
		}
		
		//send mass email
		
		//send sms
		
		//update rss
		
		
		_debug('OPENED ROOM: '.$roomname);
		return true;
	}
	
	function CloseRoom($room, $sid) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($room)) return false;
		if (!isset($sid)) return false;
		
		//get their id number
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."' AND `activated` = '1' AND `disabled` = '0';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) {
			_debug('Failed to find user!');
			return false;
		}
		
		$sid = $result[0]['sid'];
		$username = $result[0]['username'];
	
		//close the room
		$sql = "UPDATE {{DB}}.`solidarity_rooms` 
				SET `open` = '0'
				WHERE `id` = '".$room."';";
				
		$result = queryDB($sql);
		
		//get our room information
		$sql = "SELECT `name` 
				FROM {{DB}}.`solidarity_rooms` 
				WHERE `id` = '".$room."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['name'])) {
			_debug('Failed to find room!');
			return false;
		}
		$roomname = $result[0]['name'];
		
		//log it
		if (!AddLogEvent(16, $sid, $username.' closed room '.$roomname.'.', $sid, 'NULL', $room))  {
			_debug('Failed to log event!');
			return false;
		}
		
		//send mass email
		
		//send sms
		
		//update rss
		
		_debug('CLOSED ROOM '.$roomname);
			
		return true;
	}
	
	function IsSignedIn($userid, $room) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($userid)) return false;
		
		$sql = "SELECT count(`sid`) AS `count`
				FROM {{DB}}.`solidarity_signed_in_users` 
				WHERE `sid` = '".$userid."'";
		if (isset($room)) $sql .= " AND `id_room` = '".$room."'";
		$sql .= ";";
			
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['count'])) return false;
		
		if ($result[0]['count'] > 0) return true;
		
		return false;
	}
	
	function PassLabMonitor($userid, $room, $passto, $newimagepath) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		//see if we all exist and are signed in
		_debug('SWITCHING LAB MONITOR');
		$sql = "SELECT count(`sid`) AS `count`
				FROM {{DB}}.`solidarity_signed_in_users` 
				WHERE `sid` = '".$userid."'
				AND `id_room` = '".$room."';";
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['count'])) {
			_debug('SQL ERROR');
			return false;
		}
		if ($result[0]['count'] <1) {
			_debug('user 1 not in lab');
			return false;
		}
		$sql = "SELECT count(`sid`) AS `count`
				FROM {{DB}}.`solidarity_signed_in_users` 
				WHERE `sid` = '".$passto."'
				AND `id_room` = '".$room."';";
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['count'])) {
			_debug('SQL ERROR');
			return false;
		}
		if ($result[0]['count'] <1) {
			_debug('user 2 not in lab');
			return false;
		}
		//so everything exists and we're both signed in
		//check for open_close_status on both parties
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$userid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid']))  {
			_debug('SQL ERROR');
			return false;
		}
		$group1 = $result[0]['id_group'];
		$username = $result[0]['username'];
		
		$sql = "SELECT * 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$passto."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid']))  {
			_debug('SQL ERROR');
			return false;
		}
		$group2 = $result[0]['id_group'];
		$paddname = $result[0]['username'];
		
		loadGroup($group1);
		if (!GroupHasPermission($group1, 'perm_open_close'))  {
			_debug('user 1 no global perm');
			return false;
		}
		
		loadGroup($group2);
		if (!GroupHasPermission($group2, 'perm_open_close'))  {
			_debug('user 2 no global perm');
			return false;
		}
		//check for room permissions
		$sql = "SELECT count(`id_group`) AS `count`
				FROM {{DB}}.`solidarity_room_open_permissions` 
				WHERE `id_room` = '".$room."'
				AND `id_group` = '".$group1."';";	
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['count']))  {
			_debug('SQL PERM ERROR');
			return false;
		}
		if ($result[0]['count'] <=0)  {
			_debug('user 1 no room perm');
			return false;
		}
		
		$sql = "SELECT count(`id_group`) AS `count`
				FROM {{DB}}.`solidarity_room_open_permissions` 
				WHERE `id_room` = '".$room."'
				AND `id_group` = '".$group2."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['count']))  {
			_debug('SQL PERM ERROR');
			return false;
		}
		if ($result[0]['count'] <=0)  {
			_debug('user 2 no room perm');
			return false;
		}
		//so we now have permissions to do this
		//time to update the lab monitor
		$sql = "UPDATE {{DB}}.`solidarity_last_lab_monitor` 
				SET `sid` = '".$passto."',
				`image` = '".$newimagepath."' 
				WHERE `id_room` = '".$room."' 
				AND `sid` = '".$userid."';";
				
		$result = queryDB($sql);
		if (!$result)  {
			_debug('SQL FAILED');
			return false;
		}
		
		//log it
		if (!AddLogEvent(16, $userid, $username.' passed monitor to '.$paddname.'.', $passto, 'NULL', $room))  {
			_debug('Failed to log event!');
			return false;
		}
		
		//send email
		
		//send sms
		
		//rss
		
		
		return true;
	}
	
	function PassSafetyQuiz($sid) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($sid)) return false;
		
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `safety_quiz` = '1' 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		
		$sql = "SELECT `username` 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['username'])) return false;
		$username = $result[0]['username'];
		
		//log it
		if (!AddLogEvent(17, $sid, $username.' passed the safety quiz.', $sid)) return false;
	}
	
	function HasPassedSafetyQuiz($sid) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($sid)) return false;
		
		$sql = "SELECT `safety_quiz` 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['safety_quiz'])) return false;
		if ($result[0]['safety_quiz']==1) return true;
		return false;
	}
	
	function ListRoomMembers($room) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		$data = Array();
		
		$sql = "SELECT 
					`solidarity_signed_in_users`.`sid` AS `sid`,  
					`solidarity_signed_in_users`.`signed_in_time` AS `signed_in_time`, 
					`solidarity_users`.`first` AS `first`, 
					`solidarity_users`.`last` AS `last`, 
					`solidarity_users`.`id_group` AS `group`
				FROM 
					`solidarity_signed_in_users`, 
					`solidarity_users` 
				WHERE 
					`solidarity_signed_in_users`.`sid` = `solidarity_users`.`sid` 
				AND 
					`solidarity_signed_in_users`.`id_room` = '".$room."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) return false;
		
		$rows = count($result);
		$i=0;
		while ($i < $rows) {
			
			$sid = $result[$i]['sid'];
			$first = ucfirst($result[$i]['first']);
			$last = ucfirst($result[$i]['last']);
			$stime = date('M jS - g:i a', strtotime($result[$i]['signed_in_time']));
			$currentsession = secondsToTime(time() - strtotime($result[$i]['signed_in_time']));
			
			$gid = $result[$i]['group'];
			loadGroup($gid);
			$groupname = $coreSettings['groups'][$gid]['title'];
			
			$data[$i] = Array("sid" => $sid,
							"time" => $stime,
							"first" => $first,
							"last" => $last,
							"group" => $groupname,
							"session" => $currentsession
						);
			$i++;
		}
		
		return $data;
	}
	
	function AuthenticateTerminal($terminal_id) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		_debug('Chcking Terminal ID #'.$terminal_id);
		if (!isset($terminal_id)) return false;
		
		$ip = $_SERVER["REMOTE_ADDR"];
		
		_debug('Useing ADDR '.$ip);
		
		$valid = false;
		
		$sql = "SELECT COUNT(`id`) AS `number`
				FROM {{DB}}.`solidarity_swipe_machines` 
				WHERE `id` = '".$terminal_id."'
				AND `ip` = '".$ip."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['number'])) $valid = false;
		else if ($result[0]['number'] >0) $valid = true;
		
		_debug('Retuned count of '.$result[0]['number']);
		
		if(!$valid) {
			header('Location: '.$coreSettings['preferences']['server_url']);
			die();
		}
	}
?>