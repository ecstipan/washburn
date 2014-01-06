<?php
    /*===========================================*
    * 											*
    *  @Title:	User Auth and Management System	*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	mixed RegisterUser(array userdata, boolean activated[false])
    * 		- parses, cleans, and prepares the data for SQL
    * 		- validates username and email, etc
    * 		- encrypts password
    * 		- handles duplicate accounts
    * 		- assigns default groups and other things from preferences
    * 		- sends an activation email if normal registration
 	*		- returns true on success
    * 		- returns string regarding status on failure
    * 	
    * 	boolean SendActivationEmail(int sid)
    * 		- if user is activated, does nothing
    * 		- generates activation code
    * 		- updates it to user's account
    * 		- sends email containing code and link to handler
    * 
    * 	boolean ActivateUser(int sid, string activationkey)
    * 		- if local user is admin, simply activates the uder
    * 		- verify's user's existance
    * 		- code required if non-admin
    * 		- sends welcome email
    * 
    * 	void LoadUserData()
    * 		- attempts to load the user data in the session
    * 		- sets up some items for guests
    * 		- loads user's information into the $userInfo table
    * 
    * 	boolean UpdateUser(int sid, array userdata)
    * 		- checks if current user is updating themselves
    * 		- allows for admin override with admin_user permissions
    * 		- sanatizes the user data
    * 		- validates userdata
    * 		- updates user's account via AQL
    * 		- removes current information
    * 		- recaches new userdata
    * 
    * 	boolean DeleteUser(int sid)
    * 		- checks for permissions to admin users
    * 		- checks if user exists
    * 		- deletes the account
    * 
    * 	boolean DisableUser(int sid)
    * 		- checks for permissions to admin users
    * 		- checks if user exists
    * 		- disables the account
    * 
    * 	boolean EnableUser(int sid)
    * 		- checks for permissions to admin users
    * 		- checks if user exists
    * 		- enables the account
    * 
    * 	boolean ResetPassword(int sid, [string username, [string id_number]])
    * 		- Checks if that user exists
    * 		- if we're a guest, check for our credentials
    * 		- allows for admin reset override
    * 		- allows for self-resets
    * 
    * 	boolean HasPermission(int sid, string permission)
    * 		- REturns true if the user has said permission
    * 		- This is only true if the user's group has that permission
    * 		- Precaches group information
    * 		- uses cahced user information if local user profile
    * 		- runs external query for polling other users' privelages'
    * 
    * 	int GetGroupID()
    * 		- returns the user's group id they're in
    * 
    * 	boolean IsGuest()
    * 		- returns false if no user is logged on in this session
    * 
    * 	boolean Login(string username, string password)
    * 		- logs the user in via password authentication
    * 		- encrypts the password
    * 		- establishes the session
    * 	
    * 	void Logout()
    * 		- terminates the session
    * 		- logs the user out
    * 		- redirects back to the defautl ahndler
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function RegisterUser($userdata, $activated=false){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//let's make sure that we have our arguments'
		if (!isset($userdata)) return false;
		
		//check our arguments
		if (!isset($userdata['username'])) 		return false;
		if (!isset($userdata['password'])) 		return false;
		if (!isset($userdata['email'])) 		return false;
		if (!isset($userdata['first'])) 		return false;
		if (!isset($userdata['last'])) 			return false;
		if (!isset($userdata['id_number'])) 	return false;
		if (!isset($userdata['phone'])) 		return false;
		
		if($coreSettings['preferences']['registration_work_study']) {
			if (!isset($userdata['work_study'])) 	return false;
		}
		//see if we have a group override
		if (!isset($userdata['group']) || $userdata['group'] == -1) $userdata['group'] = $coreSettings['preferences']['registration_default_group'];
		
		//check if the group exists
		//now we can do some fancy sql
		$sql = "SELECT * FROM {{DB}}.`solidarity_groups` WHERE `id_group` = '".$userdata['group']."';";
		$result = queryDB($sql);
		if(!$result) return 'Invalid Group Assignment';
		
		//regarding activation...
		if ($activated) {
			$userdata['activated'] = 1;
		} else {
			$userdata['activated'] = 0;
		}

		//check if the username exists
		$userdata['username'] = strtolower(trim(mysql_escape_string($userdata['username'])));
		
		$sql = "SELECT sid 
				FROM {{DB}}.`solidarity_users` 
				WHERE `username` = '".$userdata['username']."';";
				
		$result = queryDB($sql);
		if (!$result || isset($result[0]['sid'])) return 'Username Taken';
		
		//validate the password
		if (strlen($userdata['password']) < 6) return 'Password too Short';
		
		//validate the email
		//since we may want supports for bots, we won't do full regulat expressions here'
		// //validate the email.
		$email 			= trim($userdata['email']);
		$emailarray		= array();
		$emailarray 	= explode("@", $email);
		if (count($emailarray) !=2) return 'Invalid Email';
		
		$dotarr			= array();
		$dotarr 		= explode('.', $emailarray[1]);
		if (count($dotarr) > 2) return 'Invalid Email';
		
		$domain 		= $dotarr[count($dotarr)-1];
		if ($domain!="com" && $domain!="net" && $domain!="org" && $domain!="edu") return 'Invalid Email';
		
		//check this email for a specific address if we need to
		if($coreSettings['preferences']['registration_email_filter']) {
			if(isset($coreSettings['preferences']['registration_email_domain']) && $coreSettings['preferences']['registration_email_domain'] != "") {
				if (!strstr($email, $coreSettings['preferences']['registration_email_domain'])) return 'Invalid Email';
			}
		}
		
		//check if that email exists
		$sql = "SELECT sid 
				FROM {{DB}}.`solidarity_users` 
				WHERE `email` = '".trim($userdata['email'])."';";
				
		$result = queryDB($sql);
		if (!$result || isset($result[0]['sid'])) return 'Email Taken';
		
		//clean all other strings
		$userdata['email'] = trim($userdata['email']);
		$userdata['first'] = strtolower(trim(mysql_escape_string($userdata['first'])));
		$userdata['last'] = strtolower(trim(mysql_escape_string($userdata['last'])));
		$userdata['id_number'] = trim(mysql_escape_string($userdata['id_number']));
		$userdata['phone'] = trim(mysql_escape_string($userdata['phone']));
		
		if($coreSettings['preferences']['registration_work_study']) {
			$userdata['work_study'] = intval($userdata['work_study']);
		} else $userdata['work_study'] = 'NULL';
		
		//process the password
		$userdata['password'] = MD5($userdata['username'].":".trim(mysql_escape_string($userdata['password'])));
		
		//generate the query
		$sql = "INSERT INTO {{DB}}.`solidarity_users`
				(`username`,
				`password`,
				`first`,
				`last`,
				`email`,
				`id_number`,
				`id_group`,
				`work_study`,
				`phone`,
				`activated`) 
				VALUES 
				('".$userdata['username']."',
				'".$userdata['password']."',
				'".$userdata['first']."',
				'".$userdata['last']."',
				'".$userdata['email']."',
				'".$userdata['id_number']."',
				'".$userdata['group']."',
				'".$userdata['work_study']."',
				'".$userdata['phone']."',
				'".$userdata['activated']."'
				);";
				
		$result = queryDB($sql);	
		
		//Grab our last id
		$lastID = lastInsertDB();
		if (!$lastID) return 'Failed to insert new user!';
		
		if (!$activated) {
			//send an email
			if (!SendActivationEmail($lastID)) return 'Could Not Send email';
		}
		
		//Log it
		if (!AddLogEvent(2, $lastID, $userdata['username'].' registered.')) return 'Failed to Log Event!';
		
		return 1;
    }
	
	function SendActivationEmail($sid){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//let's check if we exist first?
		if (isset($userInfo['user_id']) && $userInfo['user_id'] != $sid){//don't run the query if we're logged in
		
			if ($userInfo['activated']) return true;
		
			$sql = "SELECT id_group 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['id_group'])) return false;
		}
		
		//so we exist.  let's generate our activation code'
		$charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$length=20;
	    $str = '';
	    $count = strlen($charset);
	    while ($length--) {
	        $str .= $charset[mt_rand(0, $count-1)];
	    }
		
		//let's run it through SQL'
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `activation_code` = '".$str."'
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if ($result===false) return false;
		
		//obtain an email address
		if (!IsGuest() && $userInfo['user_id']==$sid)
			$to = $userInfo['email'];
		else {
			//we need to grab it from SQL
			$sql = "SELECT email, username 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['email'])) return false;
	
			$to = $result[0]['email'];
			$username = $result[0]['username'];
		}
		
		//generate our body
		$body = "Hello, ".$username."!

Your account still needs to be activated.  Your activation code is:
		
".$str."
		
You can cctivate your account at the following url:
		
".$coreSettings['preferences']['server_url']."activate/".$sid."/".$str."/
		
Thanks!";
		
		//sent the sucker
		return SendMail($to, 'Welcome to '.$coreSettings['preferences']['server_title'], $body);
		
	}
	
	function ActivateUser($sid, $activationkey){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//validation
		if (!isset($sid) || $sid == "") return false;
		$sid = mysql_escape_string(intval($sid));
		
		// only allow us and admins
		if (!IsGuest() && HasPermission($userInfo['user_id'], 'perm_admin_user')){
			//we don't need a key check'
			//log it
			AddLogEvent(1, $userInfo['user_id'], $userInfo['username'].' activated a user.', $sid);
			
		} else {
			if (!isset($activationkey) || $activationkey == "") return false;
		
			//we need to match the keys.
			// let's pull ours from SQL
			$sql = "SELECT activation_code 
					FROM {{DB}}.`solidarity_users` 
					WHERE `sid` = '".$sid."'
					AND `activated` = '0';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['activation_code'])) return false;
			$act_key = $result[0]['activation_code'];
			
			//compare keys
			if ($act_key != trim($activationkey)) return false;
		}
		
		//activate the account
		//super SQL fun time
		$sql = "UPDATE {{DB}}.`solidarity_users` 
		SET `activated` = '1'
		WHERE `sid` = '".$sid."' 
		AND `activated` = '0';";
		
		$result = queryDB($sql);
		if ($result===false) return false;
		
		return true;
	}
	
	function LoadUserData(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		_debug('Loading User Data');
		
		if ($_SESSION['user_id'] === -1) {
			//guest
			$userInfo['user_id'] 	= -1;
			$userInfo['is_guest'] 	= true;
			$userInfo['username'] 	= 'Guest';
			$userInfo['disabled'] 	= true;
			$userInfo['activated'] 	= false;
		}else{
			//valid user
			$userInfo['user_id'] 	= $_SESSION['user_id'];
			$userInfo['is_guest'] 	= false;
			
			//poll the rest of our data
			$sql = "SELECT * FROM {{DB}}.`solidarity_users` WHERE `sid` = '".$userInfo['user_id']."';";
			$result = queryDB($sql);
			
			//an eror occured
			if (!$result){
				$userInfo['user_id'] 	= -1;
				$userInfo['is_guest'] 	= true;
				$userInfo['username'] 	= 'Guest';
				$userInfo['disabled'] 	= true;
				$userInfo['activated'] 	= false;
				return;
			}
			
			//that id doesn't exist
			if (!isset($result[0]['username'])){
				$userInfo['user_id']	= -1;
				$userInfo['is_guest'] 	= true;
				$userInfo['username'] 	= 'Guest';
				$userInfo['disabled'] 	= true;
				$userInfo['activated'] 	= false;
				return;
			}
			//locad that information into our table
			$userInfo['username'] 				= $result[0]['username'];
			$userInfo['group'] 					= intval($result[0]['id_group']);
			$userInfo['first_name'] 			= $result[0]['first'];
			$userInfo['last_name'] 				= $result[0]['last'];
			$userInfo['id_number'] 				= $result[0]['id_number'];
			$userInfo['email'] 					= $result[0]['email'];
			$userInfo['phone'] 					= $result[0]['phone'];
			
			$userInfo['send_email'] 			= $result[0]['send_email'];
			$userInfo['send_sms'] 				= $result[0]['send_sms'];
			
			$userInfo['registered_date'] 		= strtotime($result[0]['registered_date']);
			$userInfo['registered_date_pretty'] = date("F j, Y" ,strtotime($result[0]['registered_date']));
			
			$userInfo['disabled'] 				= $result[0]['disabled'];
			
			//log out if something weird happens
			if($userInfo['disabled']==1) Logout();
			
			$userInfo['total_time_in_lab'] 		= $result[0]['total_time_in_lab'];
			
			if($coreSettings['preferences']['registration_work_study']) {
				$userInfo['work_study'] 			= $result[0]['work_study'];
			}
			
			$userInfo['activated'] 				= $result[0]['activated'];
			
			//load our group information
			loadGroup($userInfo['group']);
			
			_debug('Successfully loaded '.$userInfo['username'].'\'s profile');
		}
	}

	function UpdateUser($sid, $userdata){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//validating... again
		if (!isset($sid) || $sid == "") return false;
		$sid = mysql_escape_string(intval($sid));
		
		if (!isset($userdata)) 						return false;
		if (!isset($userdata['username'])) 			return false;
		if (!isset($userdata['email'])) 			return false;
		if (isset($userdata['password']) && strlen($userdata['password']) < 6) 			return false;
		if (!isset($userdata['group'])) 			return false;
		if (!isset($userdata['first'])) 			return false;
		if (!isset($userdata['last'])) 				return false;
		if (!isset($userdata['id_number'])) 		return false;
		if (!isset($userdata['send_email'])) 		return false;
		if (!isset($userdata['send_sms'])) 			return false;
		if (!isset($userdata['phone'])) 			return false;
		if($coreSettings['preferences']['registration_work_study']) {
			if (!isset($userdata['work_study'])) 		return false;
		}
		if (!isset($userdata['total_time_in_lab'])) return false;
		
		//let's check our permissions
		if (IsGuest()) return false;
		
		//allow users to update themselves
		if ($userInfo['user_id'] != $sid){
			//give admins with access override
			if (!HasPermission($userInfo['user_id'], 'perm_admin_user')) return false;
		}
		
		//validate email, password, and other data
		$userdata['username'] 	= strtolower($userdata['username']);
		$userdata['first'] 		= trim($userdata['first']);
		$userdata['last'] 		= trim($userdata['last']);
		$userdata['id_number'] 	= trim($userdata['id_number']);
		$userdata['phone'] 		= trim($userdata['phone']);
		
		if (isset($userdata['password'])) {
			$userdata['password'] = trim($userdata['password']);
			$userdata['password'] = md5($userdata['username'].":".$userdata['password']);
		}
		
		$userdata['total_time_in_lab'] 		= intval(trim($userdata['total_time_in_lab']));
		
		//since we may want supports for bots, we won't do full regulat expressions here'
		// //validate the email.
		$email 			= trim($userdata['email']);
		$emailarray		= array();
		$emailarray 	= explode("@", $email);
		if (count($emailarray) !=2) return false;
		
		$dotarr			= array();
		$dotarr 		= explode('.', $emailarray[1]);
		if (count($dotarr) > 2) return false;
		
		$domain 		= $dotarr[count($dotarr)-1];
		if ($domain!="com" && $domain!="net" && $domain!="org" && $domain!="edu") return false;
		
		$userdata['send_email'] = (boolean) $userdata['send_email'];
		if ($userdata['send_email']) $userdata['send_email'] =1;
		else $userdata['send_email'] = 0;
		
		$userdata['send_sms'] = (boolean) $userdata['send_sms'];
		if ($userdata['send_sms']) $userdata['send_sms'] =1;
		else $userdata['send_sms'] = 0;
		
		if($coreSettings['preferences']['registration_work_study']) {
			$userdata['work_study'] = (boolean) $userdata['work_study'];
			if ($userdata['work_study']) $userdata['work_study'] =1;
			else $userdata['work_study'] = 0;
		}
		
		//let's check if we exist first?
		if ($userInfo['user_id'] != $sid){//don't run the query if we're logged in
			$sql = "SELECT id_group 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['id_group'])) return false;
		}
		
		//values are in the proper format
		//now we can update our stats
		//time for our big-ass query
		$sql = "UPDATE {{DB}}.`solidarity_users`
		
				SET `username` 				= '".$userdata['username']."',
				`email` 				= '".$userdata['email']."',
				`id_group` 				= '".$userdata['group']."',
				`id_number` 			= '".$userdata['id_number']."',
				`first` 				= '".$userdata['first']."',
				`last` 					= '".$userdata['last']."',
				`phone` 				= '".$userdata['phone']."',
				`send_email` 			= '".$userdata['send_email']."',
				`send_sms` 				= '".$userdata['send_sms']."',
				`total_time_in_lab` 	= '".$userdata['total_time_in_lab']."'";
				
				if($coreSettings['preferences']['registration_work_study']) {
					$sql .= ", `work_study` = '".$userdata['work_study']."'";
				}
				
				if (isset($userdata['password'])) {
					$sql .= ", `password` = '".$userdata['password']."'";
				}
				
				$sql .= " WHERE `sid` = '".$sid."';
		";
		
		$result = queryDB($sql);
		if (!$result) return false;
		
		//unset our current userdata if it's us
		if ($userInfo['user_id'] == $sid){
			$userInfo = NULL;
			$userInfo = array();
			
			LoadUserData();
		} else {
			//log it
			if (!AddLogEvent(3, $userInfo['user_id'], $userInfo['username'].' updated '.$userdata['username'].' \'s profile.', $sid)) return false;
		}
		
		return true;
	}

	function DeleteUser($sid){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//validate our argument
		if (!isset($sid) || $sid == "") return false;
		$sid = mysql_escape_string(intval($sid));
		
		//make sure we have permissions to do this
		if (IsGuest()) return false;
		if($userInfo['user_id'] != $sid) {
			if (!HasPermission($userInfo['user_id'], 'perm_admin_user')) return false;
		}
		
		//make sure that we exist
		$sql = "SELECT id_group 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['id_group'])) return false;
		
		//okay, let's do it'
		$sql = "DELETE * FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if ($result===false) return false;
		
		//log it
		AddLogEvent(4, $userInfo['user_id'], $userInfo['username'].' deleted a user.', $sid);
	}

	function DisableUser($sid){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//validate our argument
		if (!isset($sid) || $sid == "") return false;
		$sid = mysql_escape_string(intval($sid));
		
		//make sure we have permissions to do this
		if (IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_user')) return false;
		
		//make sure that we exist
		$sql = "SELECT id_group 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['id_group'])) return false;
		
		//okay, let's do it'
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `disabled` = '1'
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if ($result===false) return false;
		
		//log it
		AddLogEvent(5, $userInfo['user_id'], $userInfo['username'].' disabled a user.', $sid);
	}
	
	function EnableUser($sid){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//validate our argument
		if (!isset($sid) || $sid == "") return false;
		$sid = mysql_escape_string(intval($sid));
		
		//make sure we have permissions to do this
		if (IsGuest()) return false;
		if (!HasPermission($userInfo['user_id'], 'perm_admin_user')) return false;
		
		//make sure that we exist
		$sql = "SELECT id_group 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['id_group'])) return false;
		
		//okay, let's do it'
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `disabled` = '0'
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if ($result===false) return false;
		
		//log it
		AddLogEvent(6, $userInfo['user_id'], $userInfo['username'].' a user.', $sid);
	}

	function ResetPassword($sid, $username = "", $id_number = ""){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		$resetpermission = false;
		
		if (!IsGuest() && $userInfo['user_id']==$sid) $resetpermission = true; //we're already logged in and are resettin gour own password'
		else {
			//check if this id even exists
			$sql = "SELECT id_group 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['id_group'])) return false;

			//okay, so we exist
			if (!IsGuest() && $userInfo['user_id']!=$sid && HasPermission($userInfo['user_id'], 'perm_admin_users')) 
				$resetpermission = true; //we're an admin'
				
			else if (IsGuest()) {
				//check if we have our other arguments to validate
				if (isset($username) && isset($id_number) && $id_number != "" && $username != "") {
					//do some cleaning on our inputs
					$username = strtolower(trim(mysql_escape_string($username)));
					$id_number = trim(mysql_escape_string($id_number));
					
					//see if our credentials are valid
					$sql = "SELECT id_group 
					FROM {{DB}}.`solidarity_users` 
					WHERE `sid` = '".$sid."'
					AND `username` = '".$username."'
					AND `id_number` = '".$id_number."'
					;";
					
					$result = queryDB($sql);
					if ($result && isset($result[0]['id_group'])) $resetpermission = true;
				}
			}
		}
	
		//see if we've made it this far
		//this also make it easy to see how it catches all cases
		if (!$resetpermission) return false;
	
		//the user exists and we're alowed to do this
		//let's make a password
		//generate a new key to prevent off-domain attacks
		$charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$length=15;
	    $str = '';
	    $count = strlen($charset);
	    while ($length--) {
	        $str .= $charset[mt_rand(0, $count-1)];
	    }
		
		//we need their userename to generate a new hash
		if (!isset($username)) {
			//query time
			$sql = "SELECT username 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['username'])) return false;
		
			$username = $result[0]['username'];
		}
		//make our hash
		$password_md5 = MD5($username.":".$str);
		
		//update their new password
		$sql = "UPDATE {{DB}}.`solidarity_users` 
				SET `password` = '".$password_md5."'
				WHERE `sid` = '".$sid."';";
		
		$result = queryDB($sql);
			
		//make sure that our password is reset
		$sql = "SELECT password 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['password'])) return false;
	
		$testpassword = $result[0]['password'];
		if ($testpassword != $password_md5) return false;
		
		//obtain an email address
		if (!IsGuest() && $userInfo['user_id']==$sid)
			$to = $userInfo['email'];
		else {
			//we need to grab it from SQL
			$sql = "SELECT email 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['email'])) return false;
	
			$to = $result[0]['email'];
		}
		
		//generate our body
		$body = "Hello, ".$username."!
		
		Your password has been reset.  Your temporary password is:
		
		".$str."	
		
		You can now log into your account with this new password and change it.";
		
		//sent the sucker
		return SendMail($to, 'Your New Password', $body);
	}

	function HasPermission($sid, $permission){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//error checking
		if (!isset($sid) || !isset($permission)) return false;
		
		if (isset($userInfo['group'])) {
			//load our group if we're not loaded'
			if (!isset($coreSettings['groups'][intval($userInfo['group'])])) loadGroup($userInfo['group']);
		}
		//see if we're checking our id or somebody else's
		if(!IsGuest() && $userInfo['user_id'] == intval($sid)){
			//load our group if it's not precached'
			if(!isset($coreSettings['groups'][intval($userInfo['group'])])) loadGroup($userInfo['group']);
			
			//assign our group id
			$groupid = $userInfo['group'];
		}else{
			//time to load somebody else's
			$groupid = -1;
			
			//query tim
			$sql = "SELECT id_group 
				FROM {{DB}}.`solidarity_users` 
				WHERE `sid` = '".$sid."';";
				
			$result = queryDB($sql);
			if (!$result || !isset($result[0]['id_group'])) return false;
		
			//we exist!
			$groupid = intval($result[0]['id_group']);
		}
		
		//return if our group that we're in has that permission
		return GroupHasPermission($groupid, $permission);
	}
	
	function GetGroupID(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		return $userInfo['group'];
	}
	
	function IsGuest(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		return (boolean) $userInfo['is_guest'];
	}
	
	function Login($username, $password){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//prevent doubly logging on for some reason
		if (!IsGuest()) return false;
		
		//do some cleaning
		$username = strtolower(trim(mysql_escape_string($username)));
		$password = MD5($username.":".trim(mysql_escape_string($password)));
		
		_debug('Logging in '.$username.' with hash '.$username.":".trim(mysql_escape_string($password)).'('.MD5($username.":".trim(mysql_escape_string($password))).')');
		
		//see if we exist
		$sql = "SELECT sid  
				FROM {{DB}}.`solidarity_users` 
				WHERE `username` = '".$username."'
				AND `password` = '".$password."'
				AND `disabled` = '0'
				;";
		$result = queryDB($sql);
		if (!$result || !isset($result[0]['sid'])) return false;
		
		//we exist!
		$sid = intval($result[0]['sid']);
		
		//SendMail('stip.rayce@gmail.com', 'trying to log in', 'l '.$sid);
		
		$_SESSION['user_id'] = $sid;
		
		return true;
	}

	function Logout(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//log us out
		KillSession();
		
		//why send extra stuff if we're redirecting
		ob_clean();
		
		//redirect
		header('Location: ../');
		ob_end_flush();
		exit();
	}
    
?>