<?php
    /*===========================================*
    * 											*
    *  @Title:	TEMPLATE: Registration Screen	*
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
<!-- Registration Screen Template -->
<script type="text/javascript">

function postAJAX_register() {
	
	$("input").removeClass('form_highlighted');
	
	var username=document.forms["register"]["username"].value;
	if (username==null || username=="")
	{
		$("#updatetext").html('Please enter a username.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	
	var password=document.forms["register"]["password"].value;
	if (password==null || password.length <6)
	{
		$("#updatetext").html('Please enter a valid password.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	var passwordb=document.forms["register"]["password_again"].value;
	if (passwordb==null || passwordb.length <6)
	{
		$("#updatetext").html('Please enter a valid repeat password.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	var passwordb=document.forms["register"]["password_again"].value;
	if (password!=passwordb)
	{
		$("#updatetext").html('The entered passwords did not match.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	
	var email=document.forms["register"]["email"].value;
	var atpos=email.indexOf("@");
	var dotpos=email.lastIndexOf(".");
	if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length)
	{
		$("#updatetext").html('Please enter a valid email.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	
	var first=document.forms["register"]["first"].value;
	if (first==null || first.length <2)
	{
		$("#updatetext").html('Please enter your first name.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	
	var phone=document.forms["register"]["phone"].value;
	if (phone==null || phone.length <2)
	{
		$("#updatetext").html('Please enter your phone number.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	
	var last=document.forms["register"]["last"].value;
	if (last==null || last.length <2)
	{
		$("#updatetext").html('Please enter your last name.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	
	var id=document.forms["register"]["idnumber"].value;
	if (id==null || id.length <6)
	{
		$("#updatetext").html('Please enter your id number.');
		$( "#oops_warning" ).dialog('open');
		return false;
	}
	
	<?php if ($pageData['use_ws'])
	{
		 echo 'var workstudy = $("#reg_form input[type=\'radio\']:checked").val();'; 
	}
	?>
	
	//so we passed that, let's send our sruff through ajax.
	$(".loading").show();
	
	//organize the data properly
    var regdata = 'username=' + username + '&password=' + password + '&password_again=' + passwordb + '&phone=' + phone + '&email=' + email + '&first=' + first + '&last=' + last + '&idnumber=' + id 
    <?php if ($pageData['use_ws']) echo " + '&workstudy=' + workstudy"; ?>
     + '&session_key=<?php echo $pageData['session_key']; ?>';

	//start the ajax
    $.ajax({
        type: "post",       
        data: regdata,     
        cache: false,
        dataType: "html",
        success: function (html) {              
            //if process.php returned 1/true (send mail success)
            if (html==1) {                  
                 window.location = "../activate/";
            } else if (html==2) {
            	//bad username/password
            	$("#updatetext").html('Some of the information that you provided is either incorect or already in use.');
				$( "#oops_warning" ).dialog('open');
            } else if (html==3) {
            	//account needs activation
            	$("#updatetext").html('Fuck');
				$( "#oops_warning" ).dialog('open');
            } else {
            	$("#updatetext").html('An unexpected error occured: ' + html);
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
	$("#reg_button").button();
	$("#reg_button").click(function() {
		postAJAX_register();
		return false; 
	});
	$("#workstudy").buttonset();
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
});
</script>

<div id="registration_bodybox">
	<div id="registration_banner"><?php echo $pageData['headline']; ?></div>
	<div id="registration_panel">
		<div id="<?php if ($pageData['use_ws']) echo 'registration_formbox'; else echo 'registration_formbox_small'; ?>">
			Register<br>
			<form name="register" method="POST" id="reg_form">
				<label class="reg_label">Username
				</label>
				<input type="text" name="username" id="username" tabindex="1"/><br>
				<label class="reg_label">Password
				</label>
				<input type="password" name="password" id="password" tabindex="2"/><br>
				<label class="reg_label">Password Again
				</label>
				<input type="password" name="password_again" id="password_again" tabindex="3"/><br>
				<label class="reg_label">Email
				</label>
				<input type="text" name="email" id="email" tabindex="4"/><br>
				<label class="reg_label">Phone
				</label>
				<input type="text" name="phone" id="phone" tabindex="4"/><br>
				<label class="reg_label">First Name
				</label>
				<input type="text" name="first" id="first" tabindex="5"/><br>
				<label class="reg_label">Last Name
				</label>
				<input type="text" name="last" id="last" tabindex="6"/><br>
				<label class="reg_label">ID Number
				</label>
				<input type="text" name="idnumber" id="idnumber" tabindex="7"/><br>
				
				<?php if ($pageData['use_ws']) {
					echo '<label class="reg_label">Work Study</label>
					<div id="workstudy">
						<input type="radio" id="radio1" name="workstudy_radio" value="1" /><label for="radio1">Yes</label>
						<input type="radio" id="radio2" name="workstudy_radio" value="0" checked="checked" /><label for="radio2">No</label>
					</div><br>';
				} ?>
				<input type="hidden" name="session_key" value="<?php echo $pageData['session_key']; ?>" />
				<button id="reg_button" tabindex="8">Register</button>
			</form>
		</div>
	</div>
</div>
<img class="loading" src="../images/loader.gif" alt="">
<div class="bdialog" id="oops_warning" title="Oops! Something's wrong.">
	<p id="updatetext">The server could not log you on.</p>
</div>
