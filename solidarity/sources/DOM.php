<?php
    /*===========================================*
    * 											*
    *  @Title:	DOM Creation and Management		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This file handles DOM element, the HTML5 architecture, and other commands.
    * 
    * 	void HTML5_head(string pagetitle, int backtraces)
    * 		- echo's the HTML5 doctype information and meta tags
    * 		- sets the page's title to the argument
    * 		- recursively moves up directory based on backtraces
    * 
    * 	void HTML5_close_page()
    * 		- called by handler to invoke solidarity to close th edom element upon script completion
    * 	
    * 	void GenerateFooter()
    * 		- generates an HTML5 complient document footer
    * 		- also generates XML and JSON style footers
    * 	
    * 	boolean loadTemplate(string source)
    * 		- loads and buffers a template for our presentation layer
    * 		- checks if the file exists before outputting
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function HTML5_head($pagetitle = 'Solidarity', $backtraces = 0){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		$dir = '';
		for ($i=0; $i<$backtraces; $i++){
			$dir .= '../';
		}
		
		//echo the header
    	_debug('Preparing to start DOM element.');
		
    	//precache our debug stuff
    	if ($coreSettings['enable_debug']){
    		$message = ob_get_clean();
			ob_start();
    	}
		
    	echo "<!DOCTYPE html>\n",
		"<html>\n",
		"\t<head>\n",
		"\t\t<!-- Solidarity version 0.0.1 - Designed by Rayce Stipanovich -->\n",
		"\t\t<!-- Worcester Polytechnic Institute -->\n",
		"\t\t<script type=\"text/javascript\" src=\"".$coreSettings['preferences']['server_url']."js/jquery-1.7.2.min.js\"></script>\n",
		"\t\t<script type=\"text/javascript\" src=\"".$coreSettings['preferences']['server_url']."js/jquery-ui-1.8.21.custom.min.js\"></script>\n",
		"\t\t<script type=\"text/javascript\" src=\"".$coreSettings['preferences']['server_url']."js/jquery.tooltip.min.js\"></script>\n",
		"\t\t<script type=\"text/javascript\" src=\"".$coreSettings['preferences']['server_url']."js/jquery.webcam.js\"></script>\n",
		"\t\t<script type=\"text/javascript\" src=\"".$coreSettings['preferences']['server_url']."js/jquery.timepicker.js\"></script>\n",
		"\t\t<script type=\"text/javascript\" src=\"".$coreSettings['preferences']['server_url']."js/jquery.tools.min.js\"></script>\n",
		"\t\t<link type=\"text/css\" href=\"".$coreSettings['preferences']['server_url']."css/custom-theme/jquery-ui-1.8.21.custom.css\" rel=\"stylesheet\" />\n",
		"\t\t<link type=\"text/css\" href=\"".$coreSettings['preferences']['server_url']."css/jquery.tooltip.css\" rel=\"stylesheet\" />\n",
		"\t\t<link type=\"text/css\" href=\"".$coreSettings['preferences']['server_url']."css/jquery.ui.timepicker.css\" rel=\"stylesheet\" />\n",
		"\t\t<link type=\"text/css\" href=\"".$coreSettings['preferences']['server_url']."css/main.css\" rel=\"stylesheet\" media=\"screen\"/>\n",
		"\t\t<title>$pagetitle</title>\n",
		"\t\t<!-- Browser Support -->\n",
		//"\t\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n",
		"\t\t<meta charset=\"UTF-8\" />\n",
		"\t\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=EmulateIE8\" />\n",
		"\t</head>\n",
		"\t<body>\n";
		
		//put back our fun stuff after the header
		if ($coreSettings['enable_debug']){
    		echo $message;
    	}
    }
	
	function HTML5_close_page(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		$handlers['closeDOM'] = true;
	}
	
	function GenerateFooter(){
		global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		if ($handlers['closeDOM']) {
			echo "\t</body>\n</html>";
		}
	}
	
	function loadTemplate($source){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
		//quick error checking
		if (!isset($source) || $source == "") return false;
		
		//load our things
		$source = ucfirst(strtolower($source));
		_debug("Checking for template ".$source);
		
		//check if our handler file exists.
		$source_path = $coreSettings['templates_path']."/".$source.".php";
		_debug("Checking ".$source_path);
		if (!file_exists($source_path)) {
			return false;
		}
		_debug("Found template at ".$source_path);
		
		//now we include the file with our absolute path to avoid inclusions to other places
		require_once($source_path);
		
		return true;
    }
?>