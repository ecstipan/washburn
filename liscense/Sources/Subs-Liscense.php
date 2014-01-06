<?php
   /*===========================================*
    * 											*
    *  @Title:	Solidarity - Liscensing Include	*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This file is not setup like the others.  It is standalone (+ the upper wrapper).  This is made to compile fast.
    * 	We need the fast compile times so other sites can use this while it remains lightweight.
    * 	This is also the only file using these credentials (for lookup at least).
    * 	
    * 	This file captures the serial key, and product key through GET and queried the DB.
    * 	If the combination is a match, it simply writes out "OK" in text/plain.
    * 	If the account has expired, it will display "EXPIRED" in text/plain.
    * 	If the account doesn't exist, it will display "HIDDEN" in test/plain.
    * 	If there is an error, it displays "ERROR" in text/plain.
    * 	Remote pages will periodically check to see if their liscence is valid.
    * 
    * 	Since all of these pages are encrypted with IronCube, we don't need to worry about 
    * 	storing our read credentials in this file and having people read them.  This file
    * 	will never be seen by the public anyways.
    * 
    * 	void L_DBConnect(array $creds)
    * 		- DOES NOT WARP PDO!!!!!!!!
    * 		- polls data from the input array.
    * 		- establishes MySQL Connection.
    * 		- selects the Liscence Database.
    * 		- sets the connection object to $liscence_conn.
    * 		- handles errors.
    * 
    * 	void L_DBDisonnect()
    * 		- closes our connection object $liscence_conn
    * 
    * 	string clean(string dirtyString)
    * 		- cleans up the strings for SQL.
    * 		- prevents SQL injections.
    * 
    * 	string checkCredentials(string serial, string productkey)
    * 		- checks the database for a combination of both.
    * 		- returns "OK" if credentials match.
    * 		- returns "EXPIRED" if the liscence has expired.
    * 		- returns "HIDDEN" if the account doesnt exist.
    * 
    * 	bool expired(string timestamp)
    * 		- returns true if timestamp is later than the current UTF timestamp.
    * 		- else returns false.
    * 
    * 	void ThrowError()
    * 		- forces "ERROR" to be displayed.
    * 		- stops all script execution
    */

    //bring in our global connection object
   	global $liscence_conn;

	//setup the object
	$liscence_conn;

	function L_DBConnect($creds){
		global $liscence_conn;
		
   		//let's validate our arguments
   		if (!isset($creds)) ThrowError();
		if (!isset($creds['hostname']) || $creds['hostname'] == "") ThrowError();
		if (!isset($creds['database']) || $creds['database'] == "") ThrowError();
		if (!isset($creds['username']) || $creds['username'] == "") ThrowError();
		if (!isset($creds['password']) || $creds['password'] == "") ThrowError();
		
		//so we have valid data, lets directly use MySql
		//calm down, were only avoiding our framework once!
		//it's faster this way.
		$liscence_conn = mysql_connect(	trim($creds['hostname']),
										trim($creds['username']),
										trim($creds['password']));
		//see if we can connect
		if (!$liscence_conn) ThrowError();
		
		//try to select our database
		if ( !mysql_select_db(trim($creds['database']), $liscence_conn) ) ThrowError();
		
		//make sure we're in the right charset
		if ( !mysql_set_charset('utf8', $liscence_conn) ) ThrowError();
	}
	
	function L_DBDisonnect(){
		global $liscence_conn;
		
		//we can't do anything if we dont' have an object
		if (!isset($liscence_conn)) return false;
		
		//close the conneciton
		mysql_close($liscence_conn);
		
		//clean up
		unset($liscence_conn);
	}
	
	function clean($dirtyString){
		//sorry for the inlines, but it cleans the string
		//1. removes any bad characters
		//2. trims whitespace
		//3. forces anything that may be missed into utf8
		
		return mysql_escape_string(ereg_replace("[^A-Za-z0-9-]", "", trim($dirtyString)));
	}
	
	function checkCredentials($serial, $productkey){
		global $liscence_conn;
		
		//strings should have already been cleaned at this point
		//be aware these are MD5 encrypted
		
		//setup our query
		$sql = "SELECT `enabled`,`liscence_expires`
				FROM `accounts` 
				WHERE md5(`serial_number`)	=	'".$serial."' 
				AND md5(`product_key`)		=	'".$productkey."'
				;";
		
		//actually query our stuff
		$result = mysql_query($sql,$liscence_conn);
		
		//something went wrong?
		if (!$result) ThrowError();
		
		//see if our liscence even exists
		if (mysql_num_rows($result) < 1) return "HIDDEN";
		
		//load our information
		$liscence = mysql_fetch_array($result, MYSQL_ASSOC);
		
		//see if our liscence has been disabled
		//this will typecast the string into an int
		if ($liscence['enabled']!=1) return "EXPIRED";
		
		//see if our liscence is expired
		if (expired($liscence['liscence_expires'])) return "EXPIRED";
		
		//if we can, clear up memory
		unset($result);
		unset($liscence);
		
		//we've passed the gauntlet
		return "OK";
	}
	
	function expired($timestamp){
		//get our current timestamp as of the DB
		$result_timestamp =  mysql_query('SELECT CURRENT_TIMESTAMP AS time');
		$currentTime = strtotime(mysql_result($result_timestamp,0,"time"));
		unset($result_timestamp);
		
		//generate an integer value of time
		$expDate = strtotime (trim($timestamp));
		
		//compare them
		return ($currentTime >= $expDate ? true : false);
	}
	
	function ThrowError(){
		global $liscence_conn;
		
		//disconnect
		L_DBDisonnect();
		
		//stop execution
		die('ERROR');
	}

?>