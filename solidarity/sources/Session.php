<?php
    /*===========================================*
    * 											*
    *  @Title:	Session Verification API		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	void StartSession()
    * 		- begins the session and loads some information
    * 		- initializes our userInfo table and determines if they're a guest or not.
    * 		- the session itself only contains the user's id and a few other tidbits like a key.
    * 
    *  void KillSession()
    * 		- completely removes the current session
    * 
    * 	boolean SessionValid(string key)
    * 		- returns true if the session exists and they key matches the sesion's key
    * 
    * 	string GetSessionKey()
    * 		- returns the current session's key
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function StartSession(){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		_debug('Begening Session');
		session_set_cookie_params(3600 * 24 * 30);
		session_start();
		
		//either setup a new session or continue our old one.
		if (!isset($_SESSION['user_id'])){
			$_SESSION['user_id'] = -1;
			
			//generate a new key to prevent off-domain attacks
			$charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$length=10;
		    $str = '';
		    $count = strlen($charset);
		    while ($length--) {
		        $str .= $charset[mt_rand(0, $count-1)];
		    }
			$_SESSION['key'] = $str;
		}
		
    }
	
	function KillSession(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		session_unset();
		session_destroy();
	}
	
	function SessionValid($key){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		if (!isset($key) || $key === '') return false;
		
		if (trim($key)===$_SESSION['key']) return true;
		else return false;
	}
	
	function GetSessionKey(){
		return $_SESSION['key'];
	}
?>