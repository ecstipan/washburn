<?php
   /*===========================================*
    * 											*
    *  @Title:	Database Interface and PDO		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	I know, I know... why not just directly use a PDO object or use something like DBAL?
    * 	- Because ORM or DBAL is slow and bulky, and just PDO doesn't give us neat methods.
    * 	- Don't worry, we still have our db object.
    * 
    * 	NOTE: No code should be interfacing with the PDO object directly.  Let's keep things tidy, shall we?
    * 
    * 	boolean connectDB()
    * 		- reads connection from coreSEttings
    * 		- attempts to connect to the DB by making a new instance of PDO
    * 		- provides other PDO functionality and debug support
    * 		- stored the PDO object in our $db object for later.
    * 		- returns true on successful connection
    * 
    * 	void disconnectDB()
    * 		- disconnects the datbase connection.
    * 		- unsets the global PDO $db object instance
    * 
    * 	mixed queryDB(string qstring)
    * 		- executes the query on the database
    * 		- replaces the {{DB}} string with the databse name of the srver
    * 		- returns result array on success, empty array on no result
    * 		- returns false on error
    * 
    * 	mixed lastInsertDB()
    * 		- returns the last autoinc ID after an insert statement.
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
	
	function connectDB(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//check if we had PDO enabled
		if (!defined('PDO::ATTR_DRIVER_NAME')) {
			_debug('Could not load PDO! Quitting...');
			_debug('Do you have PDO installed and enabled?');
			return false;
		}
		
		//start pulling our data from the config
		$host = $coreSettings['database']['host'];
		$dbname = $coreSettings['database']['database'];
		$user = $coreSettings['database']['username'];
		$password = $coreSettings['database']['password'];
		$driver = trim(strtolower($coreSettings['database']['driver']));
	
		//start our dsn string
		$dsn = $driver;
		
		//connection options array
		$options = array();
		
		//configure PDO for each driver
		if ($driver=='mysql'){												//MySQL
			$dsn .= ':host='.$host.';dbname='.$dbname;
		
			if (isset($coreSettings['database']['port'])) 
				$dsn .= ';port='.$coreSettings['database']['port'];
			
			if(isset($coreSettings['database']['charset']) && $coreSettings['database']['charset'] != ''){
				_debug('Setting charset to '.$coreSettings['database']['charset']);
				$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES '.$coreSettings['database']['charset'];
			}
		}else if ($driver=='mssql'){										//MS SQL Server
			$dsn .= ':host='.$host;
			
			if (isset($coreSettings['database']['port']))
				$dsn .= ','.$coreSettings['database']['port'];
			else return false;
			
			$dsn .= ';dbname='.$dbname;
		}else if ($driver=='oci'){											//oracle
			if (!isset($coreSettings['database']['oracle_config']))	{
				_debug('Missing Oracle Confoguration');
				return false;
			}
			$dsn .= ':dbname='.$coreSettings['database']['oracle_config'];
		}
		
		//finally we try and connect
		try {
			_debug('Connecting with DSN: '.$dsn);
			$db = new PDO($dsn, $user, $password, $options);
		} catch (PDOException $e) {
		    _debug('Connection failed: ' . $e->getMessage());
			return false;
		}
		
		//everything checnks out
		return true;
	}

	function disconnectDB(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//self explanitory
		if (isset($db) && $db)
		$db = null;
	}
	
	function queryDB($qstring){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//We can't wuery anyhting if we dont' have a PDO object
		if (!isset($db) || !$db) return false;
		if ( !$db->getAttribute(PDO::ATTR_CONNECTION_STATUS)) return false;
		
		//clean up our stuff
		if ($qstring==='') return false;
		$qstring = trim($qstring);
		$qstring = str_replace("{{DB}}", "`".$coreSettings['database']['database']."`", $qstring);
		_debug('Executing Query :"'.$qstring.'"');
		
		//prepare a cached statement
		$stmt = $db->prepare($qstring);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		
		//execute the query
		if (!$stmt->execute()) return false;
		
		_debug('Sucessfully Queried Database!');
		
		//grab our data
		$result = $stmt->fetchAll();
		
		//clear our resources
		$stmt->closeCursor();
		
		//output what we have
		if($result) return $result;
		return true;
	}
	
	function lastInsertDB(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//We can't wuery anyhting if we dont' have a PDO object
		if (!isset($db) || !$db) return false;
		if ( !$db->getAttribute(PDO::ATTR_CONNECTION_STATUS)) return false;
		
		//poll our object for our last id
		return $db->lastInsertId(); 
	}
?>