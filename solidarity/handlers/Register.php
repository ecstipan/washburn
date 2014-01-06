<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: Registration 			*
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
    * 	int tryRegister() 
    * 		- reads POST data sent by AJAX and attempts to register the user
    * 		- returns various integers representing error codes
    * 	
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//check if we're able to even register
		if (!$coreSettings['preferences']['registration_enabled']) header('Location: ../');
		
		//don't allow people logged in to register
		if (!IsGuest()) {
			header('Location: ../');
		}
		
		//this call is for a post call
		//even with the fancy URI mapper, we still need post for more secure calls
		if (isset($_POST['password'])) {
			//so somebody submitted the login form	
			echo tryRegister();
			
		} else {
			_debug('Echoing page headers');
			//okay, no more backend stuff
			//start the html
			HTML5_head('User Reistration');
			
			//see if we're logged in
			if(IsGuest()){
				//load the login template
				_debug('Displaying Registration Screen');
				
				//get our page data for the presentation layer
				$pageData['headline'] = $coreSettings['preferences']['server_title'].' User Registration';
				
				$pageData['session_key'] = GetSessionKey();
				$pageData['use_ws'] = $coreSettings['preferences']['registration_work_study'];
				
				//load our login form template
				loadTemplate('register');
				
				HTML5_close_page();
			} else {
				//we're already logged in
				_debug('Already Logged In! Redirecting');
				header('Location: ../');
			}
		}
		
		return true;
	}

	function tryRegister(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//let's check for everything
		if(!isset($_POST['username']) || $_POST['username'] == "") return 2;
		if(!isset($_POST['password']) || $_POST['password'] == "") return 2;
		if(!isset($_POST['password_again']) || $_POST['password_again'] == "") return 2;
		if(!isset($_POST['email']) || $_POST['email'] == "") return 2;
		if(!isset($_POST['first']) || $_POST['first'] == "") return 2;
		if(!isset($_POST['last']) || $_POST['last'] == "") return 2;
		if(!isset($_POST['idnumber']) || $_POST['idnumber'] == "") return 2;
		
		if(!isset($_POST['phone']) || $_POST['phone'] == "") return 2;
		
		if($coreSettings['preferences']['registration_work_study']) {
			if(!isset($_POST['workstudy']) || $_POST['workstudy'] == "") return 2;
		}
		
		if(!isset($_POST['session_key']) || $_POST['session_key'] == "") return 'HACKING ATTEMPT';
		
		//validate our session
		$key = trim($_POST['session_key']);
		if (!SessionValid($key)) return 'HACKING ATTEMPT';
		
		$userdata = array();
		$userdata['username'] = mysql_escape_string(strtolower(trim(htmlspecialchars_decode($_POST['username']))));
		
		$password_a = mysql_escape_string(trim(htmlspecialchars_decode($_POST['password'])));
		$password_b = mysql_escape_string(trim(htmlspecialchars_decode($_POST['password_again'])));
		if ($password_a != $password_b) return 2;
		$userdata['password'] = $password_a;
		
		$userdata['email'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['email'])));
		$userdata['first'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['first'])));
		$userdata['last'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['last'])));
		$userdata['id_number'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['idnumber'])));
		
		$userdata['phone'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['phone'])));
		
		if($coreSettings['preferences']['registration_work_study']) {
			$userdata['work_study'] = mysql_escape_string(trim(htmlspecialchars_decode($_POST['workstudy'])));
		}
		
		//everything has been cleaned
		return RegisterUser($userdata);
	}

?>