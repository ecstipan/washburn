<?php
    /*===========================================*
    * 											*
    *  @Title:	HANDLER: HTTP Error	Simulator	*
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
    * 	This page generates a funny 404 error message response to simulate a 404 error.
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function cunstructor($args = array()){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
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
?>