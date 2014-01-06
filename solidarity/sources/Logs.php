<?php
    /*===========================================*
    * 											*
    *  @Title:	System Log Generator API		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	boolean AddLogEvent(string type, int memberid, [string title, [string/int $descriptionid, [string image, [int room]]]])
    * 		- attempts to insert a log event into the log table.
    * 		- obtains user's ip
    * 		- handles escaping and injection prevention
    * 		- returns success status 
    * 
    * 	boolean PruneLogs(int days)
    * 		- deletes all logs before "days" days ago.
    * 		- adds a log event to show who pruned the logs
    * 		- default is 90 days
    * 		- checks for permissions first
    * 
    * 	int UnreadLogCount()
    * 		- returns the number of unread logs
    * 
    * 	boolean MarkAllAsSeen()
    * 		- Marks all log events as seen
    * 
    * 	LOG EVENTS
    * 		1 - User activated by admin
    * 		2 - User Registered
    * 		3 -	Admin changed user's information
    * 		4 - User deleted
    * 		5 - User Disabled
    * 		6 - User Enabled
    * 		7 - group created
    * 		8 - group modified
    * 		9 - group deleted
    * 		10 - room created
    * 		11 - room modified
    * 		12 - room deleted
    * 		13 - swipe in
    * 		14 - swipe out
    */
    
	if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function AddLogEvent($type, $memberid, $title, $descriptionid = "", $image = "", $room = ""){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		_debug('Adding Log Event');
		
		//checking
		if (!isset($type)||$type=="") return false;
		if (!isset($memberid)||$memberid=="") return false;
		
		//cleaning
		$type = trim(mysql_escape_string($type));
		$memberid = mysql_escape_string(intval($memberid));
		
		//do some more processing
		if (!isset($title)||$title=="") $title = "NULL";
		$title = "'".trim(mysql_escape_string($title))."'";
		
		if (!isset($descriptionid)||$descriptionid=="") $descriptionid = "NULL";
		$descriptionid = "'".trim(mysql_escape_string($descriptionid))."'";
		
		if (!isset($image)||$image=="") $image = "NULL";
		$image = "'".trim(mysql_escape_string($image))."'";
		
		if (!isset($room)||$room=="") $room = "NULL";
		$room = "'".intval(trim(mysql_escape_string($room)))."'";
		
		//grab out ip for the logs
		$ip = $_SERVER['REMOTE_ADDR'];
		
		//begin our sql
		$sql = "INSERT INTO 
				{{DB}}.`solidarity_logs`
					(`log_type`, 
					`id_member`, 
					`title`, 
					`description`, 
					`event_image`, 
					`id_room`, 
					`ip`) 
				VALUES 
					('".$type."', 
					'".$memberid."', 
					".$title.", 
					".$descriptionid.", 
					".$image.", 
					".$room.", 
					'".$ip."');";
				
		//time to execute the query.
		$result = queryDB($sql);
		if (!$result) return false;
		
		_debug('Added Log Event!');
		return true;         
    }

	function PruneLogs($days = 90){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		_debug('Pruning Logs before '.$days.' days ago..');
		
		$days = intval($days);
		
		//check for permissions
		if (IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) return false;
		
		//remove the old junk
		if($coreSettings['database']['driver']=='mysql'){
			$sql = "DELETE FROM
	   	 			{{DB}}.`solidarity_logs` 
					WHERE 
	   				DATEDIFF(`solidarity_logs`.`time`, NOW()) < -".$days.";";
		}else{
			$sql = "DELETE FROM
	   	 			{{DB}}.`solidarity_logs` 
					WHERE 
	   				DATEDIFF(day, `solidarity_logs`.`time`, NOW()) < -".$days.";";
		}
		$result = queryDB($sql);
		if (!$result) return false;
		
		//insert a new log event
		AddLogEvent('maintenance', 
					$userInfo['user_id'], 
					$userInfo['first_name'].' '.$userInfo['last_name'].' pruned the logs.',
					'All logs were pruned to '.$days.' days');
		
		_debug('Done!');
		return true;
	}
	
	function UnreadLogCount(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//this is where the coutning happens
		$sql = "SELECT COUNT(id) AS 'count'
				FROM {{DB}}.`solidarity_logs`
				WHERE `seen`='0';";
				
		$result = queryDB($sql);
		if (!$result) return 0;
		
		return intval($result[0]['count']);
	}
	
	function MarkAllAsSeen(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		$sql = "UPDATE {{DB}}.`solidarity_logs`
				SET `seen` = '1'
				WHERE `seen`='0';";
		
		//check for permissions
		if (IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_settings')) return false;

		//execute the query
		$result = queryDB($sql);
		if (!$result) return false;
		
		return true;
	}
    
?>