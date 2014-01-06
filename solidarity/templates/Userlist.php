<?php
    /*===========================================*
    * 											*
    *  @Title:	TEMPLATE: User List/Management	*
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
	function postAJAX_activate() {
		var x=document.forms["activate"]["email"].value;
		var atpos=x.indexOf("@");
		var dotpos=x.lastIndexOf(".");
		if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
		{
			
			
			$("#updatetext").html('Please enter an email.');
			$( "#oops_warning" ).dialog('open');
			return false;
		}
		var password=document.forms["activate"]["activation_key"].value;
		if (password==null || password.length <20)
		{
			$("#updatetext").html('Please enter a valid activation key.');
			$( "#oops_warning" ).dialog('open');
			return false;
		}
		//so we passed that, let's send our sruff through ajax.
		$(".loading").show();
		//organize the data properly
        var edata = 'email=' + x + '&activation_key=' + password + '&session_key=<?php echo $pageData['session_key']; ?>';
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
                     window.location = "../login";
                } else if (html==2) {
                	//bad username/password
                	$("#updatetext").html('You have entered an incorrect key or your account is already activated.');
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
	
	function postAJAX_activate_resend() {
		var x=document.forms["activate"]["email"].value;
		var atpos=x.indexOf("@");
		var dotpos=x.lastIndexOf(".");
		if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
		{
			$("#updatetext").html('Please enter an email.');
			$( "#oops_warning" ).dialog('open');
			return false;
		}
		//so we passed that, let's send our sruff through ajax.
		$(".loading").show();
		//organize the data properly
        var edata = 'email=' + x + '&reset=1&session_key=<?php echo $pageData['session_key']; ?>';
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
                    $( "#resent" ).dialog('open');
                } else {
					$( "#resent_fail" ).dialog('open');
                }
                $(".loading").hide();           
            }     
        });
       return false;
	}
	
	//begin the document
	$(function() {
		$(".loading").hide();
		$("#activate_button").button();
		$("#activate_button").click(function() {
			postAJAX_activate();
			return false; 
		});
		$("#resend").button();
		$("#resend").click(function() {
			postAJAX_activate_resend();
			return false;
		});
		$( "#oops_warning" ).dialog({
			autoOpen: false,
			width:410,
			modal:true,
			buttons: {
				"Ok": function() { 
						$(this).dialog("close"); 
					}
				}
		});
		$( "#resent" ).dialog({
			autoOpen: <?php echo $pageData['show_resent']; ?>,
			width:410,
			modal:true,
			buttons: {
				"Ok": function() { 
						$(this).dialog("close"); 
					}
				}
		});
		$( "#resent_fail" ).dialog({
			autoOpen: <?php echo $pageData['show_resent_fail']; ?>,
			width:410,
			modal:true,
			buttons: {
				"Ok": function() { 
						$(this).dialog("close"); 
					}
				}
		});
	});
</script>


<div id="login_bodybox">
	<div id="login_banner"><?php echo $pageData['headline']; ?></div>
	<div id="login_panel">
		<div id="login_formbox">
			<?php echo $pageData['ac_text']; ?><br>
			<form name="activate" method="POST" id="login_form">
				<label><?php echo $pageData['email']; ?>
				</label>
				<input type="text" name="email" id="email" tabindex="1"/><br>
				<label><?php echo $pageData['ack']; ?>
				</label>
				<input type="text" name="activation_key" id="activation_key" tabindex="2"/><br>
				<input type="hidden" name="session_key" value="<?php echo $pageData['session_key']; ?>" />
					
				<button id="activate_button" tabindex="3"><?php echo $pageData['ac_button_text']; ?></button>
				<button id="resend" tabindex="4"><?php echo $pageData['resend'] ?></button>
			</form>
		</div>
	</div>
</div>

<img class="loading" src="../images/loader.gif" alt="">

<div class="bdialog" id="oops_warning" title="Oops! Something's wrong.">
	<p id="updatetext">The server could not log you on.</p>
</div>

<div class="bdialog" id="resent" title="Activation Resent">
	<p>Your activation code has been resent.</p>
</div>
<div class="bdialog" id="resent_fail" title="Activation Resend Failure">
	<p>Please check that the email address is the one that you registered with.</p>
</div>
