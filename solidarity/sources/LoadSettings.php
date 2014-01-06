<?php
   /*===========================================*
    * 											*
    *  @Title:	Settings Parser, Loader, Eval	*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This file parses all of our settings and makes sure they are set, and validates a few of them.
    * 
    * 	void _debug(string input)
    * 		- outputs a pretty message of the argument if $coreSettings['enable_debug'] is enabled.
    * 		- doesnt affect anything if debug is off.
    * 
    * 	void LoadSettings()
    * 		- sets up the preferences array in coresettings
    * 
    * 	boolean saveSetting(string name, mixed value)
    * 		- checks if the setting exists
    * 		- cleans the value
    * 		- updates the setting via SQL
    * 		- updates the $coreSetting['preferences'] information
    * 		- returns boolean for success or failure
    * 
    * 	string secondsToTime(int seconds)
    * 		- outputs a pretty time conversions form seconds to real time
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
	
	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	
	//this isn't in a function because it just runs right away
	if (!isset($coreSettings['serial_number'])||$coreSettings['serial_number']==="") die('Failed to load CoreSettings.');
    if (!isset($coreSettings['product_key'])||$coreSettings['serial_number']==="") die('Failed to load CoreSettings.');
    if (!isset($coreSettings['enable_debug'])) die('Failed to load CoreSettings.');
    if (!isset($coreSettings['database'])) die('Failed to load CoreSettings.');
    if (!isset($coreSettings['database']['driver'])||$coreSettings['database']['driver']==="") die('Failed to load CoreSettings.');
	if (!isset($coreSettings['database']['host'])||$coreSettings['database']['host']==="") die('Failed to load CoreSettings.');
	if (!isset($coreSettings['database']['database'])||$coreSettings['database']['database']==="") die('Failed to load CoreSettings.');
	if (!isset($coreSettings['database']['username'])||$coreSettings['database']['username']==="") die('Failed to load CoreSettings.');
	if (!isset($coreSettings['database']['password'])||$coreSettings['database']['password']==="") die('Failed to load CoreSettings.');

	if(!isset($coreSettings['default_handler'])) $coreSettings['default_handler'] = 'home';

    function _debug($input = ''){
    	global $coreSettings;
    	
    	if ($coreSettings['enable_debug']) {
    		echo "\t\t<font size=\"1\" face=\"arial\" color=\"red\"><b>DEBUG:&nbsp;</b>",
    		trim($input),
    		"</font><br>\n";
    	}
    }

	function LoadSettings(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//setup our stuff
		if(!isset($coreSettings['preferences'])) $coreSettings['preferences'] = array();
		
		//our sql statement
		$sql = "SELECT name, type, value FROM {{DB}}.`solidarity_settings`;";
		$result=queryDB($sql);
		if (!$result) return false;
		
		//load into settings
		foreach($result as $row){
			if ($row['type']==='number'){
				$coreSettings['preferences'][$row['name']] = intval($row['value']);
			} else if ($row['type']==='string'){
				$coreSettings['preferences'][$row['name']] = (string)$row['value'];
			} else if ($row['type']==='check'){
				$coreSettings['preferences'][$row['name']] = (boolean)$row['value'];
			}
			_debug($row['name']." has been set");
		}
		_debug('Sucessfully loaded preferences!');
		
		//set our timezone
		_debug('Setting timezone...');
		
		//check to see if we already have one
		if (!ini_get('date.timezone') || ini_get('date.timezone')=='') {
			//check our settings
			if (!isset($coreSettings['preferences']['timezone_gmt']) || $coreSettings['preferences']['timezone_gmt'] === '')
				$coreSettings['preferences']['timezone_gmt'] = 0;
			if (!isset($coreSettings['preferences']['daylight_savings_time']) || $coreSettings['preferences']['daylight_savings_time'] === '')
				$coreSettings['preferences']['daylight_savings_time'] = 0;
			
			//calculate our timezone settings
			$offset = $coreSettings['preferences']['timezone_gmt'] + $coreSettings['preferences']['daylight_savings_time'];
			
			//add sign to our string
			if ($offset>0) $offset = "+".$offset;
			else if ($offset>0) $offset = "+".$offset;
			
			//set our timezone
			date_default_timezone_set('Etc/GMT'.$offset);
			
			_debug('Set Timezone to Etc/GMT'.$offset);
		}else date_default_timezone_set(ini_get('date.timezone'));
		
		_debug('The server\'s local time is '.date('l jS \of F Y h:i:s A'));
	}
	function saveSetting($name, $value) {
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		if (!isset($name) || $name == "") return false;
		if (!isset($value) || $value == "") return false;
		
		//clean some stuff
		$name = strtolower(trim(mysql_escape_string($name)));
		$value = trim(mysql_escape_string($value));
		
		//see if we even use that variable
		$sql = "SELECT `value` FROM {{DB}}.`solidarity_settings` WHERE `name` = '".$name."';";
		$result=queryDB($sql);
		if (!$result || !isset($result[0]['value'])) return false;
		
		//don't update it if we don't need to
		if ( $value == $result[0]['value']) return true;
		
		//okay, so it needs changed
		$sql = "UPDATE {{DB}}.`solidarity_settings` SET `value` = '".$value."' WHERE `name` = '".$name."';";
		$result=queryDB($sql);
		if (!$result) return false;
		
		//update local stuff
		$coreSettings['preferences'][$name] = $value;
		
		return true;
	}

	function secondsToTime($seconds) {
	    // extract hours
	    $hours = floor($seconds / (60 * 60));
	 
	    // extract minutes
	    $divisor_for_minutes = $seconds % (60 * 60);
	    $minutes = floor($divisor_for_minutes / 60);
	 
	    // extract the remaining seconds
	    $divisor_for_seconds = $divisor_for_minutes % 60;
	    $seconds = ceil($divisor_for_seconds);
		
		if($hours<10) $hours = '0'.$hours;
		if($minutes<10) $minutes = '0'.$minutes;
	 	if($seconds<10) $seconds = '0'.$seconds;
	    // return the final array
	    $obj = $hours.':'.$minutes.':'.$seconds;
	    return $obj;
	}
?>