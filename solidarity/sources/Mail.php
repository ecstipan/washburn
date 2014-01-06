<?php
    /*===========================================*
    * 											*
    *  @Title:	Mail Management Source API		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	boolean SendMail(string to, string subject, string body)
    * 		- detects whether using SMTP or PHP mail
    * 		- forms email
    * 		- sends via SMTP or PHP
    * 		- handles SSL authentication
    * 		- returns true on success/false on fail
    * 		- outputs error in debug
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
    function SendMail($to, $subject = 'No Subject', $body = ''){
    	global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
		
    	if(!isset($to)) return false;
		
		//do some cleaning
		$body = trim($body);
		$subject = stripslashes(nl2br(trim($subject)));
		
		$replyName = $coreSettings['preferences']['server_title'];
		$replyEmail = $coreSettings['preferences']['admin_email_addr'];
		
		//send our message out
		if ($coreSettings['preferences']['mail_smtp']===true){
    		
			//use pear's SMTP mail factory
			require_once("Mail.php");
			
    		$from = $replyName." <".$replyEmail.">";

			$host = $coreSettings['preferences']['mail_smtp_host'];
			$username = $coreSettings['preferences']['mail_smtp_user'];
			$password = $coreSettings['preferences']['mail_smtp_password'];
			 
			//establish authentication
			if($coreSettings['preferences']['mail_smtp_auth']===true){
				$auth = true;
			}else{
				$auth = false;
			}
			 
			 //are we using ssl?
			if($coreSettings['preferences']['mail_smtp_ssl']===true){
				$host = 'ssl://'.$coreSettings['preferences']['mail_smtp_host'];
				$port = "465";
			}else{
				$host = $coreSettings['preferences']['mail_smtp_host'];
				$port = $coreSettings['preferences']['mail_smtp_port'];
			}
			 
			$headers = array (	'From' => $from,
			   					'To' => $to,
			   					'Subject' => $subject);
			$smtp = Mail::factory('smtp',
				array (	'host' => $host,
						'auth' => $auth,
						'port' => $port,
						'username' => $username,
						'password' => $password));
			 
			$mail = $smtp->send($to, $headers, $body);
			 
			if (PEAR::isError($mail)) {
				_debug("Email Failed: ".$mail->getMessage());
				return false;
			}
			
			//everything worked
			return true;
		}else{
    		//use regular mail

			//add some headers
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From: ".$replyName." <".$replyEmail.">\r\n";
			$headers .= "To: <".$to.">\r\n";
			
			//send the damn thing
			return mail($to, $subject, $body, $headers);
    	}
    }
    
?>