<?php
    /*===========================================*
    * 											*
    *  @Title:	TEMPLATE: Badge print Page		*
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
<!DOCTYPE html>
<html>
	<head>
		<!-- Solidarity version 0.0.1 - Designed by Rayce Stipanovich -->
		<!-- Worcester Polytechnic Institute -->
		<title>Badge</title>
		<!-- Browser Support -->
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
	</head>
	<body>
		<style>

			@media print {
				#printme {
					position:absolute;
					top:0;
					left:0;
					width:640px;
					height:880px;
					overflow:hidden;
				}
			}

			html {
				width:640px;
				max-width:640px;
				height:880px;
				max-height:880px;
				margin:0px;
				padding:0px;
				margin-left:10px;
				overflow:scroll;
			}
			body {
				width:640px;
				max-width:640px;
				height:880px;
				max-height:880px;
				background-color:#fff;
				margin:0px;
				padding:0px;
				margin-left:10px;
				font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; 
			   	font-weight: 500;
			   	color: #000000;
				overflow:scroll;
			}
			.inverted {
				background:#000000;
				color:#ffffff;
			}
		</style>
		<div id="printme">
		<font style="color:#000000; font-size:100px; margin:0px;padding:0px; margin-top:0px;position:absolute;top:0px;left:0px;"><?php echo $pageData['first']; ?></font>
		<font style="color:#000000; font-size:100px; margin:0px;padding:0px; margin-top:0px;position:absolute;top:90px;left:0px;"><?php echo $pageData['last']; ?></font>
		<div class="inverted" style="width:300px;height:80px; font-size:60px; margin:0px;position:absolute;top:220px;left:0px;padding:20px; padding-top:0px;padding-bottom:0px;text-align:center;"><?php echo $pageData['date']; ?></div>
		<?php
			if ($pageData['work_study']) {
				echo '
				<div class="inverted" style="width:100px;height:80px; font-size:60px; margin:0px;position:absolute;top:220px;left:350px;padding:10px; padding-top:0px;padding-bottom:0px;text-align:center;">WS</div>
				';
			}
			if ($pageData['mon']) {
				echo '
				<div class="inverted" style="width:140px;height:80px; font-size:60px; margin:0px;position:absolute;top:220px;left:480px;padding:10px; padding-top:0px;padding-bottom:0px;text-align:center;">MON</div>
				';
			}
		?>
		<font style="color:#000000; font-size:80px; margin:0px;padding:0px; margin-top:0px;position:absolute;top:310px;left:0px;"><?php echo $pageData['group']; ?></font>
		<div class="inverted" style="width:620px;height:90px; font-size:70px; margin:0px;position:absolute;top:400px;left:0px;padding:10px; padding-top:0px;padding-bottom:0px;">TOOL USAGE:</div>
		<div style="width:610px;height:500px;"></div>
		<?php
		if ($pageData['guest']) {
			echo '<div class="inverted" style="width:620px;height:200px; font-size:160px; margin:0px;position:absolute;top:400px;left:0px;padding:10px; padding-top:0px;padding-bottom:0px;">VISITOR</div>';
		} else {
			$i=0;
			while ($i<count($pageData['machines'])) {
				echo '<div style="width:200px;
				height:70px;
				font-size:50px; 
				margin:0px;padding:0px;float:left;text-align:center;"';
				
				if (isset($pageData['machines'][$i]['perm']) && $pageData['machines'][$i]['perm']) {
					echo ' class="inverted"';
				}
				echo '>'.$pageData['machines'][$i]['abv'].'</div>';
				$i++;
			}
		}
		?>
		</div>
		<script type="text/javascript">
			window.onload=function(){
				self.print();
				self.close();
			} 
		</script>
	</body>
</html>

