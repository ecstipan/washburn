<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: User Management Func	*
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
    * 	int tryUpdateAccount()
    * 		- parses POST data
    * 		- validates and cleans information
    * 		- attempts to update the account
    * 		- returns integer status value
    * 
    * 	int tryReset()
    * 		- reads POST session variables
    * 		- verifies credentials
    * 		- resets password of the individual
    * 		- returns numerical status code
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		//see if we're logged in
		if (!isset($args[0])) {
			if(IsGuest()) header('Location: '.$coreSettings['preferences']['server_url']);
			if (!HasPermission($userInfo['user_id'], 'perm_view_users')) header('Location: '.$coreSettings['preferences']['server_url']);
			
			echo 'main user list';
			
		} else if (isset($args[0]) && $args[0] == 'forgot'){
			
			if (isset($_POST['reset'])) {
				//AJAX request
				echo tryReset();
				
			} else {
				//show our page
				HTML5_head( 'Password Reset');
				$pageData['session_key'] = GetSessionKey();
				loadTemplate('reset');
				HTML5_close_page();
			}
			
		} else if (isset($args[0]) && $args[0] == 'saveaccount'){
			
			
			//validate the session
			if (!isset($_POST['session_key']) || !SessionValid($_POST['session_key'])) die('Hacking attempt...');
			
			echo tryUpdateAccount();
			
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

function tryUpdateAccount(){
	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	
	$userdata = array();
	
	//validate our data
	if (!isset($_POST['phone']) || $_POST['phone'] == "") return 2;
	if (!isset($_POST['email']) || $_POST['email'] == "") return 2;
	if (!isset($_POST['first']) || $_POST['first'] == "") return 2;
	if (!isset($_POST['last']) || $_POST['last'] == "") return 2;
	
	if (!isset($_POST['sendsms']) || $_POST['sendsms'] == "") return 2;
	if (!isset($_POST['sendemail']) || $_POST['sendemail'] == "") return 2;
	
	if ($coreSettings['preferences']['registration_work_study'])
		if (!isset($_POST['workstudy']) || $_POST['workstudy'] == "") return 2;
	
	if(isset($_POST['password']) && strlen($_POST['password']) > 0) {
		if (strlen($_POST['password']) < 6) return 2;
		if (!isset($_POST['password_again']) || strlen($_POST['password_again']) < 6) return 2;
		if ($_POST['password'] != $_POST['password_again']) return 2;
		$userdata['password'] = trim(mysql_escape_string($_POST['password']));
	}
	
	//check their password
	if (!isset($_POST['current_password']) || strlen($_POST['current_password']) < 6) return 2;
	$pass= trim(mysql_escape_string($_POST['current_password']));
	$hash = md5($userInfo['username'].':'.$pass);
	
	$sql = "SELECT COUNT(`sid`) AS `match`
		FROM {{DB}}.`solidarity_users` 
		WHERE `password` = '".$hash."';";
		
	$result = queryDB($sql);
	if (!$result || !isset($result[0]['match'])) return 3;
	if (intval($result[0]['match']) < 1) return 3;
	
	//clean up our strings
	$userdata['username'] = $userInfo['username'];
	$userdata['id_number'] = $userInfo['id_number'];
	$userdata['total_time_in_lab'] = $userInfo['total_time_in_lab'];
	$userdata['group'] = $userInfo['group'];			
	
	$userdata['phone'] = trim(mysql_escape_string($_POST['phone']));
	$userdata['first'] = trim(mysql_escape_string($_POST['first']));
	$userdata['last'] = trim(mysql_escape_string($_POST['last']));
	$userdata['email'] = trim(mysql_escape_string($_POST['email']));
	
	if ($coreSettings['preferences']['registration_work_study'])
		$userdata['work_study'] = intval(trim(mysql_escape_string($_POST['workstudy'])));
	
	$userdata['send_sms'] = intval(trim(mysql_escape_string($_POST['sendsms'])));
	$userdata['send_email'] = intval(trim(mysql_escape_string($_POST['sendemail'])));
	
	if (!UpdateUser($userInfo['user_id'], $userdata)) return 4;
	
	return 1;
}

function tryReset(){
	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	if(!isset($_POST['username']) || $_POST['username'] == '') return 2;
	if(!isset($_POST['idnumber']) || $_POST['idnumber'] == '') return 2;
	
	$username = strtolower(trim(mysql_escape_string($_POST['username'])));
	$id_number = trim(mysql_escape_string($_POST['idnumber']));
	
	$sql = "SELECT sid 
			FROM {{DB}}.`solidarity_users` 
			WHERE `username` = '".$username."'
			AND `id_number` = '".$id_number."'
			;";
			
	$result = queryDB($sql);
	if ($result && isset($result[0]['sid'])) $sid = $result[0]['sid'];
	else return 2;
	
	//calm down, our function does cleaning
	if (!ResetPassword($sid, $username, $id_number)) return 2;
	
	return 1;
}
?>