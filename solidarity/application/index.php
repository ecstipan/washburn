<?php
   /*===========================================*
    * 											*
    *  @Title:	Solidarity						*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This is where the magic happens.
    * 
    * 	Everything passes through this file for secutity... EVERYTHING
    * 	This file works like a URI mapper.
    * 	Combiled with the .htaccess file, appache sends the rest of the url to the $url
    * 	variable via S_GET.  This is then parsed.
    * 	After Cleaning, the first namespace is parsed and a matching handler is called, 
    * 	and the rest of the sub-namespaces are paseed along as arguments to the handler call
    * 	so the handler can decide what to do with them.
    * 
    * 	This is based on the somewhat archaic architexture of php generating a lot of code.
    * 	However, this is very low traffic, so it really doesn't matter.  Some page reuests 
    * 	are handled with JSON, while others are renedered out.
    */
    
    //get the ball rolling to prevent hacking attempts
	define('SOLIDARITY', TRUE);
	
	// Using an pre-PHP 5.1 version?
	if (@version_compare(PHP_VERSION, '5.1') == -1)
		die("Please upgrade your PHP version");
	
	// Do some cleaning, just in case.
	foreach (array('db_character_set', 'cachedir') as $variable)
		if (isset($GLOBALS[$variable]))
			unset($GLOBALS[$variable], $GLOBALS[$variable]);
	
	//establish some nifty globals
	global $coreSettings, $userInfo, $pageData, $handlers, $db;
    
	//establish our settings global array
	$coreSettings=array();
	
    //we need to add to our global variables and handle some of them.
    $coreSettings['application_path'] 	= realpath(dirname(__FILE__));
	$coreSettings['sources_path'] 		= realpath($coreSettings['application_path']."/../sources");
	$coreSettings['handlers_path'] 		= realpath($coreSettings['application_path']."/../handlers");
	$coreSettings['templates_path'] 	= realpath($coreSettings['application_path']."/../templates");
	
	//Check if we have our CoreSettings.php
   	if (!file_exists(realpath($coreSettings['application_path']."/CoreSettings.php"))) {
    	header('Location: ./install.php');
		die();
	}else require_once($coreSettings['application_path']."/CoreSettings.php");
	
	//begin the buffered output  
    ob_start();
	
	//include our other functionality
	require_once($coreSettings['sources_path']."/LoadSettings.php");
	require_once($coreSettings['sources_path']."/Liscense.php");
	require_once($coreSettings['sources_path']."/URI_Mapper.php");
	require_once($coreSettings['sources_path']."/Database.php");
	require_once($coreSettings['sources_path']."/Session.php");
	require_once($coreSettings['sources_path']."/Mail.php");
	require_once($coreSettings['sources_path']."/Logs.php");
	require_once($coreSettings['sources_path']."/Groups.php");
	require_once($coreSettings['sources_path']."/Users.php");
	require_once($coreSettings['sources_path']."/DOM.php");
	require_once($coreSettings['sources_path']."/Rooms.php");
	require_once($coreSettings['sources_path']."/Swipe.php");
	//we loaded everything
	_debug('Finished Loading Sources');
	
	//check our liscence
	_debug('Validating Liscence:');
	
	validateLiscense('http://collablab.wpi.edu/washburn/liscense/validate.php');
	_debug('Liscence: Loaded into $coreSettings[\'liscense_info\']: '.$coreSettings['liscense_info']);
	
	//establish our sql connection
	_debug('Attempting to connect to Database.');
	$connection_success = connectDB();
	if ($connection_success) _debug('Connection established!');
	else {
		_debug('Connection failed!');
		die('Failed to connect to database!');
	}
	
	//load our other settings
	LoadSettings();
	
	//get what we're trying to dislay/do
	$handler_data = parseURL();
	
	//establish our session
	StartSession();
	
	//load our user data
	LoadUserData();
	
	//prevent unauthorized access
	maintenanceRedirect();
	
	/*
	$coreSettings['enable_debug'] = true;
	SignIn(736872432,1,'fish');
	GenerateFooter();
	ob_end_flush();
	exit();
	*/
	
	//display something
	$loadedpage = loadHandler($handler_data);
	if (!$loadedpage) _debug("Failed to find or load handler!");
	else _debug('Successfully loaded everything!');
	
	//send out buffer and erase any extra gabage.
	GenerateFooter();
	ob_end_flush();
?>