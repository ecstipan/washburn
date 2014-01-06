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
	function postAJAX_reset() {
		var username=document.forms["reset"]["username"].value;
		if (username==null || username=="")
		{
			$("#updatetext").html('Please enter a username.');
			$( "#oops_warning" ).dialog('open');
			return false;
		}
		var idnumber=document.forms["reset"]["idnumber"].value;
		if (idnumber==null || idnumber.length =="")
		{
			$("#updatetext").html('Please enter a valid password.');
			$( "#oops_warning" ).dialog('open');
			return false;
		}
		//so we passed that, let's send our sruff through ajax.
		$(".loading").show();
		//organize the data properly
        var edata = 'username=' + username + '&idnumber=' + idnumber + '&reset=1&session_key=<?php echo $pageData['session_key']; ?>';
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
                	$("#updatetext").html('You have a bad username and id combination.');
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
		$("#reset_button").button();
		$("#reset_button").click(function() {
			postAJAX_reset
			();
			return false; 
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

<div id="psreset_bodybox">
	<div id="psreset_banner">Reset Password</div>
	<div id="psreset_panel">
		<div id="psreset_formbox">
			Reset Password<br>
			<form name="reset" method="POST" id="reset_form">
				<label>Username
				</label>
				<input type="text" name="username" id="username" tabindex="1"/><br>
				<label>ID Number
				</label>
				<input type="text" name="idnumber" id="idnumber" tabindex="2"/><br>
				<input type="hidden" name="session_key" value="<?php echo $pageData['session_key']; ?>" />
					
				<button id="reset_button" tabindex="3">Reset</button>
			</form>
		</div>
	</div>
</div>

<img class="loading" src="../../images/loader.gif" alt="">

<div class="bdialog" id="oops_warning" title="Oops! Something's wrong.">
	<p id="updatetext">The server could not reset your password.</p>
</div>
