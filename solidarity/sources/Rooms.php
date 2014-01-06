<?php
    /*===========================================*
    * 											*
    *  @Title:	Rooms Management System			*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	mixed AddRoom(string name, string head_name, string head_phone, string description, string location)
    * 		- checks that all of the information is there
    * 		- cleans strings and preps for sql
    * 		- adds to the DB
    * 		- logs event
    * 
    * 	mixed UpdateRoom(int id, string name, string head_name, string head_phone, string description, string location)
    * 		- cleans arguments
    * 		- checks that room exists
    * 		- updated the DB
    * 		- logs event
    * 
    * 	boolean DeleteRoom(int id)
    * 		- deletes the room from the db
    * 		- removes any group open/close permissions for the room
    * 		- logs the event
    * 
    * 	boolean SetRoomOpenPermission(int room, int group [, boolean open])
    * 		- sets the group open/close permission
    * 		- updates the database
    * 		- returns true on success
    * 
    * 	boolean HasOpenPermision(int room, int group)
    * 		- returns boolean if group has open/close permissions for the room
    */
   
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	
	function AddRoom($name, $head_name, $head_phone, $description, $locaiton){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return -1;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) return -1;
		
		if (!isset($name) || $name == "") return -1;
		if (!isset($head_name) || $head_name == "") return -1;
		if (!isset($head_phone) || $head_phone == "") return -1;
		if (!isset($description) || $description == "") return -1;
		if (!isset($locaiton) || $locaiton == "") return -1;
		
		//check for previously existing titles.
		$name			= trim(mysql_escape_string($name));
		$head_name 		= trim(mysql_escape_string($head_name));
		$head_phone 	= trim(mysql_escape_string($head_phone));
		$description 	= trim(mysql_escape_string($description));
		$locaiton 		= trim(mysql_escape_string($locaiton));
		
		//see if we exist
		$sql = "SELECT * FROM {{DB}}.`solidarity_rooms` WHERE `name` = '".$name."';";
		$result = queryDB($sql);
		if ($result && $result['id']) return -1;
		
		 $sql = "INSERT INTO {{DB}}.`solidarity_rooms`
				(`name`,
				`description`,
				`room_head_name`,
				`room_head_phone`,
				`location`) 
				VALUES 
				('".$name."',
				'".$description."',
				'".$head_name."',
				'".$head_phone."',
				'".$locaiton."');";
				
		$result = queryDB($sql);	
		
		//Grab our last id
		$lastID = lastInsertDB();
		if (!$lastID) return -1;
		
		//log it
		if (!AddLogEvent(10, $userInfo['user_id'], $userInfo['username'].' created rom '.$name)) return -1;
		
		return $lastID;
	}
	
	function UpdateRoom($id, $name, $head_name, $head_phone, $description, $locaiton){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) return false;
		
		if (!isset($id) || $id == "") return -1;
		if (!isset($name) || $name == "") return -1;
		if (!isset($head_name) || $head_name == "") return -1;
		if (!isset($head_phone) || $head_phone == "") return -1;
		if (!isset($description) || $description == "") return -1;
		if (!isset($locaiton) || $locaiton == "") return -1;
		
		//check for previously existing titles.
		$name			= trim(mysql_escape_string($name));
		$head_name 		= trim(mysql_escape_string($head_name));
		$head_phone 	= trim(mysql_escape_string($head_phone));
		$description 	= trim(mysql_escape_string($description));
		$locaiton 		= trim(mysql_escape_string($locaiton));
		
		//see if we exist
		$sql = "SELECT * FROM {{DB}}.`solidarity_rooms` WHERE `id` = '".$id."';";
		$result = queryDB($sql);
		if (!$result || !$result['id']) return false;
		
		$sql = "UPDATE {{DB}}.`solidarity_rooms`
		
				SET `name` 				= '".$name."',
				SET `description` 		= '".$description."',
				SET `room_head_name`	= '".$head_name."',
				SET `room_head_phone` 	= '".$head_phone."',
				SET `location` 			= '".$locaiton."'
				
				WHERE `id` = '".$id."';";
		
		$result = queryDB($sql);
		if (!$result) return false;
		
		if (!AddLogEvent(11, $userInfo['user_id'], $userInfo['username'].' modified room '.$name)) return false;
		
		return true;
	}
	
	function DeleteRoom($id){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) return false;
		
		if (!isset($id) || $id == "") return false;
		
		//see if we exist
		$sql = "DELETE FROM {{DB}}.`solidarity_rooms` WHERE `id` = '".$id."';";
		$result = queryDB($sql);
		if (!$result) return false;
		
		$sql = "DELETE FROM {{DB}}.`solidarity_room_open_permissions` WHERE `id_room` = '".$id."';";
		$result = queryDB($sql);
		if (!$result) return false;
		
		if (!AddLogEvent(12, $userInfo['user_id'], $userInfo['username'].' deleted a room #'.$id.'.')) return false;
		
		return true;
	}
	
	function SetRoomOpenPermission($room, $group, $open = true) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) return false;
		
		if (!isset($room) || $room == "") return false;
		if (!isset($group) || $group == "") return false;
		
		if($open) {
			$sql = "INSERT INTO {{DB}}.`solidarity_room_open_permissions`
					(`id_room`,
					`id_group`) 
					VALUES 
					('".$room."',
					'".$group."');";
					
			$result = queryDB($sql);
			
			//Grab our last id
			$lastID = lastInsertDB();
			if (!$lastID) return false;
		} else {
			$sql = "DELETE FROM {{DB}}.`solidarity_room_open_permissions` WHERE `id_room` = '".$room."' AND `id_group` = '".$group."';";
			$result = queryDB($sql);
			if (!$result) return false;
		}
		return true;
	}
	
	function HasOpenPermision($room, $group) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) return false;
		
		if (!isset($room) || $room == "") return false;
		if (!isset($group) || $group == "") return false;
		
		$sql = "SELECT COUNT(`id_group`) AS `match`
				FROM {{DB}}.`solidarity_room_open_permissions` 
				WHERE `id_group` = '".$group."' AND `id_room` = '".$room.";";
		
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['match'])) return false;
		if (intval($result[0]['match']) <1) return false;
		return true;
	}
?>