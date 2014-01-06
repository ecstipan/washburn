<?php
	
	if (!defined('SOLIDARITY')) die('Hacking attempt...');
	global $coreSettings;
	
	$coreSettings['serial_number']					= '';
	$coreSettings['product_key']					= '';
	
	$coreSettings['enable_debug']					= false;
	
	//bare minimum connection credentials
	$coreSettings['database']['driver'] 			= 'mysql'; 					//mysql, mssql, oci
	$coreSettings['database']['host'] 				= 'localhost';
	$coreSettings['database']['database'] 			= '';
	$coreSettings['database']['username'] 			= '';
	$coreSettings['database']['password'] 			= '';
	
	//other things your driver may need
	$coreSettings['database']['port'] 				= '3306';
	$coreSettings['database']['charset'] 			= 'utf8';
	
	//in case your driver needs one
	$coreSettings['database']['socket'] 			= '';
	
	//oracle needs more information
	$coreSettings['database']['oracle_config'] 		= "
	(DESCRIPTION =
    	(ADDRESS_LIST =
      		(ADDRESS = (PROTOCOL = TCP)(HOST = localhost)(PORT = 1521))
    	)
    	(CONNECT_DATA =
      		(SERVICE_NAME = orcl)
    	)
  	)";
	
	$coreSettings['default_handler']			= 'home';
	$coreSettings['default_error_handler']		= 'error';

?>