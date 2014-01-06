<?php
    /*===========================================*
    * 											*
    *  @Title:	User Groups Management System	*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	boolean GroupHasPermission(int groupid, string permission)
    * 		- returns true of that group has the indicated permission
    * 	
    * 	void loadGroup(int groupid)
    * 		- loads group's information and permissions into $coreSettings['groups' table]
    * 		- reads from the table of previously cached
    * 		- initializes the table if need be
    *
    * 	int AddGroup(string title, string description, array permissions)
    * 		- checks for permissions
    * 		- generates a group with the selected information
    * 		- prevents duplicate names
    * 		- returns -1 on failure
    * 		- returns group's ID on success
    * 
    * 	boolean UpdateGroup(int gid, string title, string description, array permissions)
    * 		- checks for permissions
    * 		- checks if group id exists
    * 		- updates group's information
    * 		- unless perm_admin_settings, will not let user modify their own group or groups with perm_admin_settings
    * 
    * 	boolean DeleteGroup(int gid)
    * 		- checks for permissions
    * 		- doesn't let user delete their own group
    * 		- checks that group exists
    * 		- group must be empty and not the default.
    */
   
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
	function GroupHasPermission($groupid, $permission){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//load our group if it's not precached's
		if(!isset($coreSettings['groups'][intval($groupid)])) loadGroup($groupid);
		
		//only return true if our cached copy has th epermission
		if (!isset($coreSettings['groups'][$groupid][$permission])) return false;
		if ($coreSettings['groups'][$groupid][$permission] == 1) return true;
		else return false;
	}
	
    function loadGroup($groupid){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    	
		_debug('Loading Group: '.$groupid);
		
		//check for errors
		if(!isset($groupid) || $groupid=='') return;
		$groupid=intval(trim($groupid));
		
		//initialize our cache
		if(!isset($coreSettings['groups'][intval($groupid)])) $coreSettings['groups'] = array();
		
		//don't reload our group if we haven't done anything to edit it.
		if(isset($coreSettings['groups'][intval($groupid)])) return;
		
		//now we can do some fancy sql
		$sql = "SELECT * FROM {{DB}}.`solidarity_groups` WHERE `id_group` = '".$groupid."';";
		$result = queryDB($sql);
		if(!$result) return;
		
		$coreSettings['groups'][intval($groupid)] = $result[0];
	}
	
	function AddGroup($title, $description, $permissions){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return -1;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_groups')) return -1;
		
		if (!isset($title) || $title == "") return -1;
		if (!isset($description) || $description == "") return -1;
		if (!isset($permissions) || $permissions == Array()) return -1;
		
		if (!isset($permissions['perm_admin_settings']) || $permissions['perm_admin_settings'] == "") return -1;
		if (!isset($permissions['perm_open_close']) || $permissions['perm_open_close'] == "") return -1;
		if (!isset($permissions['perm_notify_sms']) || $permissions['perm_notify_sms'] == "") return -1;
		if (!isset($permissions['perm_notify_email']) || $permissions['perm_notify_email'] == "") return -1;
		if (!isset($permissions['perm_logs']) || $permissions['perm_logs'] == "") return -1;
		if (!isset($permissions['perm_admin_groups']) || $permissions['perm_admin_groups'] == "") return -1;
		if (!isset($permissions['perm_admin_users']) || $permissions['perm_admin_users'] == "") return -1;
		if (!isset($permissions['perm_after_hours']) || $permissions['perm_after_hours'] == "") return -1;
		if (!isset($permissions['perm_view_users']) || $permissions['perm_view_users'] == "") return -1;
		if (!isset($permissions['perm_view_projects']) || $permissions['perm_view_projects'] == "") return -1;
		if (!isset($permissions['perm_admin_projects']) || $permissions['perm_admin_projects'] == "") return -1;
		
		//data validation and sanitization
		$permissions['perm_admin_settings'] 	= intval(mysql_escape_string($permissions['perm_admin_settings']));
		$permissions['perm_open_close'] 		= intval(mysql_escape_string($permissions['perm_open_close']));
		$permissions['perm_notify_sms'] 		= intval(mysql_escape_string($permissions['perm_notify_sms']));
		$permissions['perm_notify_email'] 		= intval(mysql_escape_string($permissions['perm_notify_email']));
		$permissions['perm_logs'] 				= intval(mysql_escape_string($permissions['perm_logs']));
		$permissions['perm_admin_groups'] 		= intval(mysql_escape_string($permissions['perm_admin_groups']));
		$permissions['perm_admin_users'] 		= intval(mysql_escape_string($permissions['perm_admin_users']));
		$permissions['perm_after_hours'] 		= intval(mysql_escape_string($permissions['perm_after_hours']));
		$permissions['perm_view_users'] 		= intval(mysql_escape_string($permissions['perm_view_users']));
		$permissions['perm_view_projects'] 		= intval(mysql_escape_string($permissions['perm_view_projects']));
		$permissions['perm_admin_projects'] 	= intval(mysql_escape_string($permissions['perm_admin_projects']));
		
		//check for previously existing titles.
		$title			= trim(mysql_escape_string($title));
		$description 	= trim(mysql_escape_string($description));
		
		//see if we exist
		$sql = "SELECT * FROM {{DB}}.`solidarity_groups` WHERE `title` = '".$title."';";
		$result = queryDB($sql);
		if ($result && $result['id_group']) return -1;
		
		 $sql = "INSERT INTO {{DB}}.`solidarity_groups`
				(`title`,
				`description`,
				`perm_admin_settings`,
				`perm_open_close`,
				`perm_notify_sms`,
				`perm_notify_email`,
				`perm_logs`,
				`perm_admin_groups`,
				`perm_admin_users`,
				`perm_after_hours`,
				`perm_view_users`,
				`perm_view_projects`,
				`perm_admin_projects`) 
				VALUES 
				('".$title."',
				'".$description."',
				'".$permissions['perm_admin_settings']."',
				'".$permissions['perm_open_close']."',
				'".$permissions['perm_notify_sms']."',
				'".$permissions['perm_notify_email']."',
				'".$permissions['perm_logs']."',
				'".$permissions['perm_admin_groups']."',
				'".$permissions['perm_admin_users']."',
				'".$permissions['perm_after_hours']."',
				'".$permissions['perm_view_users']."',
				'".$permissions['perm_view_projects']."',
				'".$permissions['perm_admin_projects']."'
				);";
				
		$result = queryDB($sql);	
		
		//Grab our last id
		$lastID = lastInsertDB();
		if (!$lastID) return -1;
		
		//log it
		if (!AddLogEvent(7, $userInfo['user_id'], $userInfo['username'].' created group '.$title)) return -1;
		
		return $lastID;
	}
	
	function UpdateGroup($gid, $title, $description, $permissions){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_groups')) return false;
		
		if (!isset($gid) || $gid == "") return -1;
		if (!isset($title) || $title == "") return -1;
		if (!isset($description) || $description == "") return -1;
		if (!isset($permissions) || $permissions == Array()) return -1;
		
		if (!isset($title) || $title == "") return -1;
		if (!isset($description) || $description == "") return -1;
		if (!isset($permissions) || $permissions == Array()) return -1;
		
		if (!isset($permissions['perm_admin_settings']) || $permissions['perm_admin_settings'] == "") return -1;
		if (!isset($permissions['perm_open_close']) || $permissions['perm_open_close'] == "") return -1;
		if (!isset($permissions['perm_notify_sms']) || $permissions['perm_notify_sms'] == "") return -1;
		if (!isset($permissions['perm_notify_email']) || $permissions['perm_notify_email'] == "") return -1;
		if (!isset($permissions['perm_logs']) || $permissions['perm_logs'] == "") return -1;
		if (!isset($permissions['perm_admin_groups']) || $permissions['perm_admin_groups'] == "") return -1;
		if (!isset($permissions['perm_admin_users']) || $permissions['perm_admin_users'] == "") return -1;
		if (!isset($permissions['perm_after_hours']) || $permissions['perm_after_hours'] == "") return -1;
		if (!isset($permissions['perm_view_users']) || $permissions['perm_view_users'] == "") return -1;
		if (!isset($permissions['perm_view_projects']) || $permissions['perm_view_projects'] == "") return -1;
		if (!isset($permissions['perm_admin_projects']) || $permissions['perm_admin_projects'] == "") return -1;
		
		//data validation and sanitization
		$permissions['perm_admin_settings'] 	= intval(mysql_escape_string($permissions['perm_admin_settings']));
		$permissions['perm_open_close'] 		= intval(mysql_escape_string($permissions['perm_open_close']));
		$permissions['perm_notify_sms'] 		= intval(mysql_escape_string($permissions['perm_notify_sms']));
		$permissions['perm_notify_email'] 		= intval(mysql_escape_string($permissions['perm_notify_email']));
		$permissions['perm_logs'] 				= intval(mysql_escape_string($permissions['perm_logs']));
		$permissions['perm_admin_groups'] 		= intval(mysql_escape_string($permissions['perm_admin_groups']));
		$permissions['perm_admin_users'] 		= intval(mysql_escape_string($permissions['perm_admin_users']));
		$permissions['perm_after_hours'] 		= intval(mysql_escape_string($permissions['perm_after_hours']));
		$permissions['perm_view_users'] 		= intval(mysql_escape_string($permissions['perm_view_users']));
		$permissions['perm_view_projects'] 		= intval(mysql_escape_string($permissions['perm_view_projects']));
		$permissions['perm_admin_projects'] 	= intval(mysql_escape_string($permissions['perm_admin_projects']));
		
		//check for previously existing titles.
		$gid			= intval(mysql_escape_string($gid));
		$title			= trim(mysql_escape_string($title));
		$description 	= trim(mysql_escape_string($description));
		
		//see if we exist
		$sql = "SELECT * FROM {{DB}}.`solidarity_groups` WHERE `id_group` = '".$gid."';";
		$result = queryDB($sql);
		if (!$result || !$result['id_group']) return false;
		
		$sql = "UPDATE {{DB}}.`solidarity_groups`
		
				SET `title` 				= '".$title."',
				SET `description` 			= '".$description."',
				SET `perm_admin_settings`	= '".$permissions['perm_admin_settings']."',
				SET `perm_open_close` 		= '".$permissions['perm_open_close']."',
				SET `perm_notify_sms` 		= '".$permissions['perm_notify_sms']."',
				SET `perm_notify_email` 	= '".$permissions['perm_notify_email']."',
				SET `perm_logs` 			= '".$permissions['perm_logs']."',
				SET `perm_admin_groups` 	= '".$permissions['perm_admin_groups']."',
				SET `perm_admin_users` 		= '".$permissions['perm_admin_users']."',
				SET `perm_after_hours` 		= '".$permissions['perm_after_hours']."',
				SET `perm_view_users` 		= '".$permissions['perm_view_users']."',
				SET `perm_view_projects` 	= '".$permissions['perm_view_projects']."',
				SET `perm_admin_projects` 	= '".$permissions['perm_admin_projects']."' 
				
				WHERE `id_group` = '".$gid."';";
		
		$result = queryDB($sql);
		if (!$result) return false;
		
		if (!AddLogEvent(8, $userInfo['user_id'], $userInfo['username'].' modified group '.$title)) return false;
		
		return true;
	}
	
	function DeleteGroup($gid){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if(IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_groups')) return false;
		
		if (!isset($gid) || $gid == "") return false;
		
		if ($coreSettings['preferences']['registration_default_group']==$sid) return false;
		if ($userInfo['group']==$sid) return false;
		
		//check that it's empty
		$sql = "SELECT COUNT(`sid`) AS `match`
				FROM {{DB}}.`solidarity_users` 
				WHERE `id_group` = '".$gid."';";
		
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['match'])) return false;
		if (intval($result[0]['match']) >0) return false;
		
		//see if we exist
		$sql = "DELETE FROM {{DB}}.`solidarity_groups` WHERE `id_group` = '".$gid."';";
		$result = queryDB($sql);
		if (!$result) return false;
		
		$sql = "DELETE FROM {{DB}}.`solidarity_room_open_permissions` WHERE `id_group` = '".$gid."';";
		$result = queryDB($sql);
		if (!$result) return false;
		
		if (!AddLogEvent(9, $userInfo['user_id'], $userInfo['username'].' deleted a group #'.$gid.'.')) return false;
		
		return true;
	}
    
?>