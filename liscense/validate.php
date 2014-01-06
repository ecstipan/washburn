<?php
    /*===========================================*
    * 											*
    *  @Title:	Silidarity Liscensing Mid-Ware	*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This is the Licensing middleware file.
    * 	Installations will poll this file to determine if their liscence is valid.
    * 	This is more secure than letting remote clients tap into our DB.
    * 	It is also considerably faster.
    * 	
    * 	This file uses ./Sources/Subs-Liscence.php for all of its core functionality.
    * 	Unlike other files, the sources do not impliment other sources.
    * 	Because of this, the liscenceing system is self-contained and can be thought of
    * 	as a seperate project in a sense.
    * 
    * 	Security isn't a big issue, since we're cleaning everything, and no information 
    * 	is really being released on the page.
    */
   
   	global $liscence_conn;
	
	//establish our timezine
	date_default_timezone_set('America/New_York');
	
   	//add in our functionality
   	require_once('./Sources/Subs-Liscense.php');
   
    //This is an exception to any other connection array!
    //This does not have a driver as this does not go thorugh PDO!
    $connectionCredentials= array(
		'hostname'		=> 'localhost',
		'database'		=> 'solidarity_liscence',
		'username'		=> 'liscence_remote',
		'password'		=> 'xqPmtRdGDHmEbbx4',
	);
	
	//our output for later
	$ourput = "FAILED";
	
	//make sure we have our information
	//be aware these are MD5 encrypted
	if (!isset($_GET['s'])||$_GET['s']=="") ThrowError();
	if (!isset($_GET['p'])||$_GET['p']=="") ThrowError();
	
	//do some tiying up
	$get_serial 	= clean($_GET['s']);
	$get_productkey = clean($_GET['p']);
	
	//connect
	L_DBConnect($connectionCredentials);
	
	//validate our liscense
	$ourput = checkCredentials($get_serial, $get_productkey);
	
	//close our connection
	L_DBDisonnect();
	
	//display our plain text
	echo $ourput;
?>