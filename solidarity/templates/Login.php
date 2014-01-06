<?php
    /*===========================================*
    * 											*
    *  @Title:	TEMPLATE: Login Screen			*
    *  @Author: Rayce Stipanovich				*
    *  @Rev: 	0.0.1							*
    *  @URL:	solidarity.wpi.edu				*
    * 											*
    *===========================================*/
   
   /*	This is the login screen template.  This will read from the pageData array and 
    */
    
    if (!defined('SOLIDARITY')) die('Hacking attempt...');
    
    global $coreSettings, $userInfo, $pageData, $handlers, $db, $actions;
    
?>
<!-- Login Screen Template -->
<script type="text/javascript">
	function postAJAX() {
		var username=document.forms["login"]["username"].value;
		if (username==null || username=="")
		{
			$("#updatetext").html('Please enter a username.');
			$( "#oops_warning" ).dialog('open');
			return false;
		}
		var password=document.forms["login"]["password"].value;
		if (password==null || password.length <6)
		{
			$("#updatetext").html('Please enter a valid password.');
			$( "#oops_warning" ).dialog('open');
			return false;
		}
		//so we passed that, let's send our sruff through ajax.
		$(".loading").show();
		//organize the data properly
        var edata = 'username=' + username + '&password=' + password + '&session_key=<?php echo $pageData['session_key']; ?>';
		//start the ajax
        $.ajax({
            url: "./", 
            type: "post",       
            data: edata,     
            cache: false,
            dataType: "html",
            success: function (html) {              
                //if process.php returned 1/true (send mail success)
                if (html==1) {                  
                     window.location = "../";
                } else if (html==2) {
                	//bad username/password
                	$("#updatetext").html('You have a bad username and password combination.');
					$( "#oops_warning" ).dialog('open');
                } else if (html==3) {
                	//account needs activation
                	$("#updatetext").html('You must activate your account before you can log in.  <br><a href="../activate/" alt="Activate Account">Activate Now</a>');
					$( "#oops_warning" ).dialog('open');
                } else if (html==5) {
                	//account is disable
                	$("#updatetext").html('Your account has been disabled.');
					$( "#oops_warning" ).dialog('open');
                } else if (html==4) {
                	//maintinance and not authorize
                	$("#updatetext").html('Sorry, while the server is in maintenance mode, you cannot access the control panel.');
					$( "#oops_warning" ).dialog('open');
                } else {
                	$("#updatetext").html('An unexpected error occured.');
					$( "#oops_warning" ).dialog('open');
                }
                $(".loading").hide();           
            }     
        });
       return false;
	}

	//begin the document
	$(function() {
		$(".loading").hide();
		$("#login_button").button();
		$("#login_button").click(function() {
			postAJAX();
			return false; 
		});
		$("#login_register").button();
		$("#login_register").click(function() {
			window.location = '../register/';
			return false; 
		});
		$( "#maint_warning" ).dialog({
			autoOpen: <?php echo $pageData['show_popup']; ?>,
			width:410,
			modal:true,
			buttons: { 
				"Ok": function() { 
						$(this).dialog("close"); 
					  }
					 }
		});
		$( "#oops_warning" ).dialog({
			autoOpen: false,
			width:410,
			modal:true,
			buttons: {
				"Ok": function() { 
						$(this).dialog("close"); 
					},
				"Reset Password": function() {
						window.location = "../users/forgot/";
						return false;
					 }
				}
		});
	});
</script>


<div id="login_bodybox">
	<div id="login_banner"><?php echo $pageData['headline']; ?></div>
	<div id="login_panel">
		<div id="login_formbox">
			<?php echo $pageData['login_text']; ?><br>
			<form name="login" method="POST" id="login_form">
				<label><?php echo $pageData['username']; ?>
				</label>
				<input type="text" name="username" id="username" tabindex="1"/><br>
				<label><?php echo $pageData['password']; ?>
				</label>
				<input type="password" name="password" id="password" tabindex="2"/><br>
				<input type="hidden" name="session_key" value="<?php echo $pageData['session_key']; ?>" />
					
				<button id="login_button" tabindex="3"><?php echo $pageData['login_button_text']; ?></button>
				<?php
					if ($pageData['show_reg_button'])
						echo '<button id="login_register" tabindex="4">'.$pageData['Register'].'</button>';
				?>
			</form>
		</div>
	</div>
</div>

<img class="loading" src="../images/loader.gif" alt="">

<img id="dev" src="../images/dev.png" alt="">

<div class="bdialog" id="maint_warning" title="Maintenance Mode Warning">
	<p>The server is currently under maintenance.  Only authorized users may log in.  Sorry for the inconvenience.</p>
</div>

<div class="bdialog" id="oops_warning" title="Oops! Something's wrong.">
	<p id="updatetext">The server could not log you on.</p>
</div>
