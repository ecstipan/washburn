<?php
   /*===========================================*
    * 											*
    *  @Title:	Liscencing Engine				*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This file validates our serial number and product key.
    * 	After it is finished doing local checks, it "phones home" to
    * 	determine if the liscence is valid.  There is one important function in this file.
    * 	The file then loads the liscence result into $coreSettings['liscence_info'] 
    * 	for later use.
    * 
    * 	void validateLiscence(string server = 'http://localhost/validate.php')
    * 		- checks our liscence credentials for proper variable types.
    * 		- establishes a CURL connection to query the liscence server
    * 		- outputs that request in a string in $coreSettings['liscence_info']
    * 	
    * 	string preclean(string dirtyString)
    * 		- cleans up the strings for encryption/
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
	
	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	
	function validateLiscense($server = 'http://localhost/validate.php'){
		global $coreSettings;
		
		_debug('Begining Liscence Valiation');
		
		//clean things up
		$serial = preclean($coreSettings['serial_number']);
		$key 	= preclean($coreSettings['product_key']);
		
		//basic validation
		if ( strlen(preclean($coreSettings['serial_number'])) < 16) return 'ERROR';
		if ( strlen(preclean($coreSettings['product_key'])) < 20) return 'ERROR';
		_debug('Local Valiation Success');
		
		//setup our CURL query
		//encrypt the transaction
		$Query = $server."?s=".md5($serial)."&p=".md5($key);
		_debug($Query);
		
		//setup CURL
        $crl = curl_init();
        $timeout = 5;
		
		_debug('Attempting Remote Validation');
		//make sure we get a response
        curl_setopt ($crl, CURLOPT_URL, trim($Query));
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        
		_debug(htmlspecialchars($ret));
		
		//make it an enum
		if($ret!='OK'&&$ret!='EXPIRED'&&$ret!='ERROR'&&$ret!='HIDDEN') $ret = 'ERROR';
		
		//clean up
		if (isset($coreSettings['liscense_info'])) unset($coreSettings['liscense_info']);
		
		//output our answer
		$coreSettings['liscense_info'] = $ret;
	}
    
    function preclean($dirtyString){
		//sorry for the inlines, but it cleans the string
		//1. removes any bad characters
		//2. trims whitespace

		return ereg_replace("[^A-Za-z0-9-]", "", trim($dirtyString));
	}
    
?>