<?php
    /*===========================================*
    * 											*
    *  @Title:	TEMPLATE: 404 Error Page		*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This is the error screen template.  Simple, huh?
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
?>
<div id="error_bodybox">
	<div id="error_fontbox">
		<font id="error_text"><?php echo $pageData['title']; ?></font>
		<br>
		<font id="error_text_small"><?php echo $pageData['warning']; ?></font>
	</div>
</div>