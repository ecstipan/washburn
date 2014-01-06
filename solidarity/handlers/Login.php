<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: Login	*
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
    * 	int tryLogin() 
    * 		- reads POST data and attempts to log the user in
    * 		- returns various integers representing error codes
    * 	
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (isset($args[0])) {
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
			
			return true;
		}
		
		
		//this call is for a post call
		//even with the fancy URI mapper, we still need post for more secure calls
		if (isset($_POST['username'])) {
			//so somebody submitted the login form	
			echo tryLogin();
		} else {
			_debug('Echoing page headers');
			//okay, no more backend stuff
			//start the html
			HTML5_head('User Login');
			
			//see if we're logged in
			if(IsGuest()){
				//load the login template
				_debug('Displaying Login Screen');
				
				//get our page data for the presentation layer
				$pageData['headline'] = $coreSettings['preferences']['server_title'].' User Login';
				$pageData['login_text'] = "Control Panel Login";
				$pageData['Register'] = "Register";
				$pageData['username'] = "Username";
				$pageData['password'] = "Password";
				$pageData['login_button_text'] = "Log In";
				$pageData['session_key'] = GetSessionKey();
				$pageData['show_reg_button'] = $coreSettings['preferences']['registration_enabled'];
				if ($coreSettings['preferences']['maint_mode']) $pageData['show_popup'] = "true"; 
				else $pageData['show_popup'] = "false";
				
				//load our login form template
				loadTemplate('login');
				
				HTML5_close_page();
			} else {
				//we're already logged in
				_debug('Already Logged In! Redirecting');
				header('Location: ../');
			}
		}
		
		return true;
	}

	function tryLogin(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//let's check for everything
		if(!isset($_POST['username']) || $_POST['username'] == "") return 2;
		if(!isset($_POST['password']) || $_POST['password'] == "") return 2;
		if(!isset($_POST['session_key']) || $_POST['session_key'] == "") return 'HACKING ATTEMPT';
		
		//validate our session
		$key = trim($_POST['session_key']);
		if (!SessionValid($key)) return 'HACKING ATTEMPT';
		
		$user = mysql_escape_string(strtolower(trim(htmlspecialchars_decode($_POST['username']))));
		$pass = mysql_escape_string(trim(htmlspecialchars_decode($_POST['password'])));
		
		$password_md5 = MD5($user.":".$pass);
		
		//see if we exist
		$sql = "SELECT * 
		FROM {{DB}}.`solidarity_users` 
		WHERE `username` = '".$user."' 
		AND `password` = '".$password_md5."'
		;";
		
		$result = queryDB($sql);
		
		if (!$result || !isset($result[0]['id_group'])) return 2;
		
		//we need to grab our id
		if ($coreSettings['preferences']['maint_mode']) {
			if (!HasPermission($result[0]['sid'], 'perm_admin_settings')) return 4;
		}
		
		//check for disabled accounts
		if ($result[0]['disabled']==1) return 5;
		
		//make sure we're activated
		if ($result[0]['activated']==0) return 3;
		
		$data = Login($user, $pass);
		
		if (!$data) return -2;
		
		return 1;
	}

?>