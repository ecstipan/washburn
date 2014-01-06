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
<style>
	#link_box {
	position:fixed;
	top:50%;
	left:50%;
	display:block;
	padding:0px;
	width:<?php echo  $pageData['iconds_width'];?>px;
	height:540px;
	margin-top:-235px;
	z-index:0;
}

.link_box_small {
	margin-left:-<?php echo  $pageData['iconds_m_l_small'];?>px;
}

.link_box_bigl {
	margin-left:-<?php echo  $pageData['iconds_m_l_big'];?>px;
}	
</style>
<script type="text/javascript">
	var sidebaropen = true;
	
	function showloading(){
		$(".loading").show();
		$('#top_progressbar_bar').progressbar('value', 0);
		$( "#top_progressbar" ).fadeIn('fast');
	}
	
	function hideloading(){
		$('#top_progressbar_bar').progressbar('value', 0);
		$( "#top_progressbar" ).fadeOut('fast');
		$(".loading").fadeOut('fast');
	}
	
	function loadingvalue(val){
		$('#top_progressbar_bar').progressbar('value', val);
	}
	
	function loadingvalue_start(val){
		var currval = $('#mask_loadingbar').progressbar('value');
		if (val > currval) {  
			$('#mask_loadingbar').progressbar('value', currval + 1);
    		setTimeout(function () { loadingvalue_start(val);  }, 5);
		} else if (val < currval) {
			$('#mask_loadingbar').progressbar('value', val);
		}
		if (currval == 100) hideMainLoader();
	}
	
	function hideMainLoader() {
		setTimeout(function () { 
			$('#main_loading_mask').fadeOut('slow');  
			setTimeout(function () { 
				$( ".sidebar" ).toggleClass( "sidebarhidden", 1000 );
				$( "#main_window" ).toggleClass( "main_bodybox_big", 1000 );
				$( "#accountshadow" ).toggleClass( "main_bodybox_big", 1000 );
				$( "#link_box" ).toggleClass( "link_box_bigl", 1000 );
				$( "#accountsbox" ).toggleClass( "accoutbox_big", 1000 );
					setTimeout(function () { $( "#liscenseerror" ).dialog('open'); }, 2000);
			}, 500);
		}, 500);
	}
	
	function loadUserPanelAJAX() {
		$.ajax({
		    type: "POST",
		    dataType: "json",
		    data: 'session_key=<?php echo $pageData['session_key']; ?>',
		    beforeSend: function(x) {
		        if(x && x.overrideMimeType) {
		            x.overrideMimeType("application/json;charset=UTF-8");
		        }
		    },
		    url: './ajax/userinfo/',
		    success: function(data) {
		        // 'data' is a JSON object which we can access directly.
		        // Evaluate the data.success member and do something appropriate...
		        if (data.success == true){
		           $('#username').val(data.username);
		           $('#email').val(data.email);
		           $('#phone').val(data.phone);
		           $('#first').val(data.first);
		           $('#last').val(data.last);
		           $('#idnumber').val(data.id_number);
		           
		           
		           <?php if ($pageData['work_study'])
					{
						 echo '
		           if (data.work_study==1) {
		           		$("#radio1").attr("checked", true);
		           		$("#label1").addClass("ui-state-active");
		           } else {
		           		$("#radio2").attr("checked", true);
		           		$("#label2").addClass("ui-state-active");
		           }'; } ?>//<script >
		           if (data.send_sms==1) {
		           		$("#radio5").attr("checked", true);
		           		$("#label5").addClass("ui-state-active");
		           } else {
		           		$("#radio6").attr("checked", true);
		           		$("#label6").addClass("ui-state-active");
		           }
		           if (data.send_email==1) {
		           		$("#radio3").attr("checked", true);
		           		$("#label3").addClass("ui-state-active");
		           } else {
		           		$("#radio4").attr("checked", true);
		           		$("#label4").addClass("ui-state-active");
		           }
		           
		        } else{
		        	$("#updatetext").html('Failed to load your data.');
					$( "#oops_warning" ).dialog('open');
		        }
		    },
		    error: function(data) {
		    	$("#updatetext").html('Failed to load your data.');
				$( "#oops_warning" ).dialog('open');
		    }
		});
	}
	function saveUserPanelAJAX() {
		showloading();
		
		var password=document.forms["account"]["password"].value;
		if (password=="" || password==null || password.length <6)
		{
			$("#updatetext").html('Please enter a valid new password.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		var passwordb=document.forms["account"]["password_again"].value;
		if (passwordb=="" || passwordb==null || passwordb.length <6)
		{
			$("#updatetext").html('Please enter a valid repeat password.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		
		var passwordc=document.forms["account"]["password_current"].value;
		if (passwordc==null || passwordc.length <6)
		{
			$("#updatetext").html('Please enter your current password to change your settings.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		
		var passwordb=document.forms["account"]["password_again"].value;
		if (password!=passwordb)
		{
			$("#updatetext").html('The entered new passwords did not match.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		
		var email=document.forms["account"]["email"].value;
		var atpos=email.indexOf("@");
		var dotpos=email.lastIndexOf(".");
		if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length)
		{
			$("#updatetext").html('Please enter a valid email.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		
		var first=document.forms["account"]["first"].value;
		if (first==null || first.length <2)
		{
			$("#updatetext").html('Please enter your first name.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		
		var phone=document.forms["account"]["phone"].value;
		if (phone==null || phone.length <2)
		{
			$("#updatetext").html('Please enter your phone number.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		
		var last=document.forms["account"]["last"].value;
		if (last==null || last.length <2)
		{
			$("#updatetext").html('Please enter your last name.');
			$( "#oops_warning" ).dialog('open');
			hideloading();
			return false;
		}
		
		<?php if ($pageData['work_study'])
		{
			 echo 'var workstudy = $("#workstudy input[type=radio]:checked").val();'; 
		}
		?>//<script>
		
		var sendsms = $("#sendsms input[type=radio]:checked").val();
		var sendemail = $("#sendemails input[type=radio]:checked").val();
		
		loadingvalue(30);
		
		 var regdata = 'password=' + password + '&password_again=' + passwordb + '&phone=' + phone + '&email=' + email + '&first=' + first + '&last=' + last + '&sendsms=' + sendsms + '&sendemail=' + sendemail + '&current_password=' + passwordc 
	    <?php if ($pageData['work_study']) echo " + '&workstudy=' + workstudy"; ?>
	     + '&session_key=<?php echo $pageData['session_key']; ?>';
		
		//start the ajax
	    $.ajax({
	    	url: './users/saveaccount/',
	        type: "post",       
	        data: regdata,     
	        cache: false,
	        dataType: "html",
	        success: function (html) {         
	            //if process.php returned 1/true (send mail success)
	            if (html==1) {                
	            	//we have success
	            	loadingvalue(90);
	            	hideAccountBox();
	            	
	            	if (sidebaropen){
		            	$( ".sidebar" ).toggleClass( "sidebarhidden", 200 );
						$( "#main_window" ).toggleClass( "main_bodybox_big", 200 );
						$( "#accountshadow" ).toggleClass( "main_bodybox_big", 200 );
						$( "#link_box" ).toggleClass( "link_box_bigl", 200 );
						$( "#accountsbox" ).toggleClass( "accoutbox_big", 200 );
						
						loadUserAJAX();
						
						$( ".sidebar" ).toggleClass( "sidebarhidden", 200 );
						$( "#main_window" ).toggleClass( "main_bodybox_big", 200 );
						$( "#accountshadow" ).toggleClass( "main_bodybox_big", 200 );
						$( "#link_box" ).toggleClass( "link_box_bigl", 200 );
						$( "#accountsbox" ).toggleClass( "accoutbox_big", 200 );
	            	}
	            	
	            	loadingvalue(100);
	            	
	            	setTimeout(function () { 
	            		$( "#account_ok" ).dialog('open');
	            	}, 400);
	            } else if (html==2) {
	            	//bad username/password
	            	$("#updatetext").html('We could not update your account.');
					$( "#oops_warning" ).dialog('open');
				} else if (html==3) {
	            	//bad username/password
	            	$("#updatetext").html('You did not enter the correct password for your account.');
					$( "#oops_warning" ).dialog('open');
	            } else if (html==4) {
	            	//bad username/password
	            	$("#updatetext").html('An internal system error has occured.  We could not update your account');
					$( "#oops_warning" ).dialog('open');
	            } else {
	            	$("#updatetext").html('An unexpected error occured.');
					$( "#oops_warning" ).dialog('open');
	            }       
	        }     
	    });
	    
		hideloading();
		return false;
		
	}
	
	var accountsopen = false;
	function showAccountBox(){
		loadUserPanelAJAX();
		accountsopen = true;
		$('#acc_form input').removeAttr("disabled");  
		$('#username, #idnumber').attr("disabled", true);
		$("#save_button").attr("disabled", true).addClass("ui-state-disabled");
		setTimeout(function () { 
			$('#accountsbox').toggleClass('accountbox_show', 200);
		}, 100);
		setTimeout(function () { 
			$( "#accordion" ).accordion( "option", "active", 0 );
		}, 300);
	}
	function hideAccountBox(){
		$('#accountsbox').toggleClass('accountbox_show', 200);
		$('#acc_form input:not(input:radio)').val('').attr("disabled", true);
		$("#save_button").attr("disabled", true).addClass("ui-state-disabled");
		$( "#accordion" ).accordion( "option", "active", 1 );
		$('#acc_form input:radio').attr('checked', false);
		$('#acc_form label').removeClass("ui-state-active");
		accountsopen = false;
	}
	
	function loadUserAJAX() {
		$.ajax({
		    type: "POST",
		    dataType: "json",
		    data: 'session_key=<?php echo $pageData['session_key']; ?>',
		    beforeSend: function(x) {
		        if(x && x.overrideMimeType) {
		            x.overrideMimeType("application/json;charset=UTF-8");
		        }
		    },
		    url: './ajax/userinfo/',
		    success: function(data) {
		        // 'data' is a JSON object which we can access directly.
		        // Evaluate the data.success member and do something appropriate...
		        if (data.success == true){
		            $('#user_area').html('User Info<br>' +
		            	'<h1>' + data.first + ' ' + data.last + '</h1>'
		            	+'<h2>Username:</h2><h3>' + data.username + '</h3>'
		            	+'<h2>ID Numbber:</h2><h3>' + data.id_number + '</h3>'
		            	+'<h2>Email:</h2><h3>' + data.email + '</h3>'
		            	+'<h2>Group:</h2><h3>' + data.group + '</h3>'
		            	+'<h2>Joined On:</h2><h3>' + data.joined + '</h3>'
		            	+'<h2>Swipe Time:</h2><h3>' + data.total_lab_time + '</h3>'
		            	+'<h2>IP Address:</h2><h3>' + data.ip + '</h3>'
		            );
		            
		            <?php if ($pageData['work_study']) echo "
		            if (data.work_study) {
		            	 $('#user_area').html( $('#user_area').html()
		            	 	+'<h2>Work Study:</h2><h3>Yes</h3>'
		           		 );
		           } else {
		           		$('#user_area').html( $('#user_area').html()
		            	 	+'<h2>Work Study:</h2><h3>No</h3>'
		           		 );
		           }"; ?>
		            
		        }
		        else{
		            $('#user_area').html('Failed to load data!');
		        }
		    },
		    error: function(data) {
		    	 $('#user_area').html('Failed to load data!');
		    }
		});
	}
	function loadServerAJAX() {
		$.ajax({
		    type: "POST",
		    dataType: "json",
		    data: 'session_key=<?php echo $pageData['session_key']; ?>',
		    beforeSend: function(x) {
		        if(x && x.overrideMimeType) {
		            x.overrideMimeType("application/json;charset=UTF-8");
		        }
		    },
		    url: './ajax/serverinfo/',
		    success: function(data) {
		        // 'data' is a JSON object which we can access directly.
		        // Evaluate the data.success member and do something appropriate...
		        if (data.success == true){
		            $('#server_info').html('Server Info<br>' +
		            	'<center><h5>' + data.servername + '</h5></center>'
		            	+'<h4>' + data.serverurl + '</h4><br>'
		            	+'<h2>Users:</h2><h3>' + data.usercount + '</h3>'
		            	+'<h2>Admin Email:</h2><h3>' + data.email + '</h3>');
		            	
		            	<?php if(isset($pageData['mainicons']['admin']))  {
			            	echo "$('#server_info').html( $('#server_info').html()
			            	+'<h2>Unread Logs:</h2><h3>' + data.logs + '</h3>'
			            	+'<h2>Licence:</h2><h3>' + data.liscence + '</h3>'
			            	+'<h2>Status:</h2><h3>' + data.status + '</h3>');";
						} ?>
		            	
		        } else{
		            $('#server_info').html('Failed to load data!');
		        }
		    },
		    error: function(data) {
		    	 $('#server_info').html('Failed to load data!');
		    }
		});
	}
	
	//begin jqeuery stuff
	$(function() {
		//initialize our pretty loading cover
		$("#mask_loadingbar").progressbar({
			value: 0
		});

		//setup DOM elements
		$(".loading").hide();
		$("#top_progressbar").hide();
		$("#top_buttons").buttonset();
		$("#accordion").accordion();
		$("#top_progressbar_bar").progressbar({
			value: 0
		});
		$( "#side_hide_button" ).button({
            icons: {
                primary: "ui-icon-transferthick-e-w"
            },
            text: false
        });
		$( "#side_hide_button" ).click(function() {
			sidebaropen = !sidebaropen;
			$( ".sidebar" ).toggleClass( "sidebarhidden", 200 );
			$( "#main_window" ).toggleClass( "main_bodybox_big", 200 );
			$( "#accountshadow" ).toggleClass( "main_bodybox_big", 200 );
			$( "#link_box" ).toggleClass( "link_box_bigl", 200 );
			$( "#accountsbox" ).toggleClass( "accoutbox_big", 200 );
			return false;
		});
		$("#link_box div").hover(
		  function () {
		    $(this).fadeTo( 100,1);
		  }, 
		  function () {
		    $(this).fadeTo( 300, 0.5);
		  }
		);
		$( "#oops_warning" ).dialog({
			autoOpen: false,
			width:410,
			modal:false,
			show: 'fade',
			hide: 'fade',
			buttons: {
				"Ok": function() { 
						$(this).dialog("close");

					}
				}
		});
		$( "#account_ok" ).dialog({
			autoOpen: false,
			width:410,
			modal:false,
			show: 'fade',
			hide: 'fade',
			buttons: {
				"Ok": function() { 
						$(this).dialog("close"); 
					}
				}
		});
			
		//setup our form stuff
		
		$( "#accordion" ).accordion( "option", "active", 1 );
		$( "#cancel_button" ).button();
		$( "#cancel_button" ).click(function() {
			hideAccountBox();
			return false;
		});
		$("#save_button").button();
		$("#save_button").click(function() {
			saveUserPanelAJAX();
			return false;
		});
		$("#acc_form").find(":input").change(function() {
		   $("#save_button").removeAttr("disabled").removeClass('ui-state-disabled');
		});
		$("#acc_form").find(":input").keypress(function(event) {
			if ( event.which == 13 ) {
		    	event.preventDefault();
		    	saveUserPanelAJAX();
				return false;
		   	}
		  $("#save_button").removeAttr("disabled").removeClass('ui-state-disabled');
		});
		$("#workstudy").buttonset();
		$("#sendemails").buttonset();
		$("#sendsms").buttonset();
		$('#radio1').click(function(){
			$('#radio1').attr('checked', 'checked');
			$('#radio2').removeAttr('checked');
		});
		$('#radio2').click(function(){
			$('#radio2').attr('checked', 'checked');
			$('#radio1').removeAttr('checked');
		});
		
		$('#radio3').click(function(){
			$('#radio3').attr('checked', 'checked');
			$('#radio4').removeAttr('checked');
		});
		$('#radio4').click(function(){
			$('#radio4').attr('checked', 'checked');
			$('#radio3').removeAttr('checked');
		});
		
		$('#radio5').click(function(){
			$('#radio5').attr('checked', 'checked');
			$('#radio6').removeAttr('checked');
		});
		$('#radio6').click(function(){
			$('#radio6').attr('checked', 'checked');
			$('#radio5').removeAttr('checked');
		});
		
		loadingvalue_start(15);
		
		<?php
			if ($pageData['liscense_popup']) {
				echo "
				$.fx.speeds._default = 1000;
				$( \"#liscenseerror\" ).dialog({
				autoOpen: false,
				width:410,
				modal:true,
				show: \"fade\",
				hide: \"fade\",
				buttons: {
					\"Ok\": function() { 
							$(this).dialog(\"close\"); 
						}
					}
			});";
			}
		?>
		//<script type="text/javascript" >
		//begin our page setup
		$( ".sidebar" ).toggleClass( "sidebarhidden", 1 );
		$( "#main_window" ).toggleClass( "main_bodybox_big", 1 );
		$( "#accountshadow" ).toggleClass( "main_bodybox_big", 1 );
		$( "#link_box" ).toggleClass( "link_box_bigl", 1 );
		$( "#accountsbox" ).toggleClass( "accoutbox_big", 1 );
		$("#link_box div").fadeTo(100, 0.5);
		
		//button functions
		$("#top_button_home").click(function() {
			window.location = '<?php echo $pageData['header_url']; ?>';
		});
		$("#top_button-1").click(function() {
			
		});
		$("#top_button-2").click(function() {
			window.location = './logout/';
		});
		
		//icon funcitons
		$("#icons_account").click(function() {
			if(!accountsopen) showAccountBox();
		});
		$("#icons_admin").click(function() {
			window.location = './admin/';
		});
		$("#icons_users").click(function() {
			window.location = './users/';
		});
		$("#icons_schedule").click(function() {
			
		});
		
		$('#icons_projects').click(function() {
			window.location = 'https://sharepoint.wpi.edu/research/CNCLabs/_Layouts/listform.aspx?PageType=8&ListId=%7BCCC1C9D4-B945-4B65-A8FE-EE7B9FB8AB5F%7D';
		});
		
		$('#icons_schedule').click(function() {
			window.location = 'https://sharepoint.wpi.edu/research/CNCLabs/Lists/Machine%20Tool%20Schedule/calendar.aspx';
		});		
		loadingvalue_start(30);
		
		//start the AJAX
		loadUserAJAX();
		
		loadingvalue_start(50);
		
		loadServerAJAX();
		
		//finished laoading everything
		loadingvalue_start(100);
	});
</script>

<div id="main_loading_mask">
	<p>Loading Control Panel</p>
	<div id="mask_loadingbar"></div>
</div>

<div id="main_bodybox">
	<div id="main_top_bar">
		<div id="left_block"></div>
		<div id="topbar_g_l"></div>
		<a href="<?php echo $pageData['header_url']; ?>" alt="Home" id="main_title"><?php echo $pageData['header']; ?></a>
		<div id="topbar_g_r"></div>
		<div id="top_iconset">
			<span>
				<div id="top_buttons">
					<input type="checkbox" id="top_button_home" /><label for="top_button_home">Home</label>
					<input type="checkbox" id="top_button-2" /><label for="top_button-2">Logout</label>
				</div>
			</span>
		</div>
		<div id="top_progressbar">
			<font id="progress_text">Loading...</font>
			<div id="top_progressbar_bar"></div>
		</div>
	</div>
	<div id="sidebar" class="sidebar">
		<div id="sidebar_top"></div>
		<div id="user_area" class="shadow">
			
		</div>
		<div id="server_info" class="shadow">
			
		</div>
	</div>
	<div id="main_window" class="main_bodybox_small">
		<button id="side_hide_button"></button>
		<div id="accountshadow" class="main_bodybox_small"></div>
		<div id="accountsbox" class="shadowdark accountbox_hide accoutbox_small">
			<h1>My Account</h1>
			<form name="account" method="POST" id="acc_form">
				<div id="accordion">
					<h3><a href="#">Contact Information</a></h3>
    				<div>
						<label class="reg_label">Username
						</label>
						<input type="text" name="username" id="username" class="reg_width" disabled="disabled" tabindex="1"/><br>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">Email
						</label>
						<input type="text" name="email" id="email" class="reg_width" tabindex="4"/><br>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">First Name
						</label>
						<input type="text" name="first" id="first" class="reg_width" tabindex="5"/><br>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">Last Name
						</label>
						<input type="text" name="last" id="last" class="reg_width" tabindex="6"/><br>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">ID Number
						</label>
						<input type="text" name="idnumber" id="idnumber" class="reg_width" disabled="disabled" tabindex="7"/><br>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">Phone Number
						</label>
						<input type="text" name="phone" id="phone" class="reg_width" tabindex="8"/>
						<div class="ui-helper-clearfix"></div>
					</div>
					<h3><a href="#">Preferences</a></h3>
    				<div>
						<?php if ($pageData['work_study']) {
							echo '<label class="reg_label">Work Study</label>
							<div id="workstudy">
								<input type="radio" id="radio1" name="workstudy_radio" value="1" /><label id="label1" for="radio1">Yes</label>
								<input type="radio" id="radio2" name="workstudy_radio" value="0" /><label id="label2" for="radio2">No</label>
							</div><br>';
						} ?>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">Send Email</label>
						<div id="sendemails">
							<input type="radio" id="radio3" name="sendemail_radio" value="1" /><label id="label3" for="radio3">Yes</label>
							<input type="radio" id="radio4" name="sendemail_radio" value="0" /><label id="label4" for="radio4">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">Send SMS</label>
						<div id="sendsms">
							<input type="radio" id="radio5" name="sendsms_radio" value="1" /><label id="label5" for="radio5">Yes</label>
							<input type="radio" id="radio6" name="sendsms_radio" value="0" /><label id="label6" for="radio6">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label">New Password
						</label>
						<input type="password" name="password" id="password" class="reg_width" tabindex="10"/><br>
						<div class="ui-helper-clearfix"></div>
						<label class="reg_label">Again
						</label>
						<input type="password" name="password_again" id="password_again" class="reg_width" tabindex="11"/><br>
					</div>
				</div>
				<label class="reg_labelIlarge">Current Password
				</label>
				<input type="password" name="password_current" id="password_current" class="password_current" tabindex="12"/><br>
				<div class="ui-helper-clearfix"></div>
				<input type="hidden" name="session_key" value="<?php echo $pageData['session_key']; ?>" />
				
				<button id="cancel_button" tabindex="20">Cancel</button><button id="save_button" tabindex="15">Save</button>
			</form>
			
		</div>
		<div id="link_box" class="link_box_small">
		
			<?php
				if (isset($pageData['mainicons']['account'])) echo '
					<div id="icons_account" class="shadowdark">
						<img src="./images/icons/account.png" alt="My Account"/>
						<p>
							My Account
						</p>
					</div>';
				
				if (isset($pageData['mainicons']['admin'])) echo '
					<div id="icons_admin" class="shadowdark">
						<img src="./images/icons/settings.png" alt="System"/>
						<p>
							System
						</p>
					</div>';
				if (isset($pageData['mainicons']['users'])) echo '
					<div id="icons_users" class="shadowdark">
						<img src="./images/icons/users.png" alt="Users"/>
						<p>
							Users
						</p>
					</div>';
				if (isset($pageData['mainicons']['groups'])) echo '
					<div id="icons_groups" class="shadowdark">
						<img src="./images/icons/groups.png" alt="Groups"/>
						<p>
							Groups
						</p>
					</div>';
				if (isset($pageData['mainicons']['projects'])) echo '
					<div id="icons_projects" class="shadowdark">
						<img src="./images/icons/projects.png" alt="Projects"/>
						<p>
							Projects
						</p>
					</div>';
				if (isset($pageData['mainicons']['rooms'])) echo '
					<div id="icons_rooms" class="shadowdark">
						<img src="./images/icons/rooms.png" alt="Workspaces"/>
						<p>
							Workspaces
						</p>
					</div>';
				if (isset($pageData['mainicons']['logs'])) echo '
					<div id="icons_logs" class="shadowdark">
						<img src="./images/icons/logs.png" alt="Logs"/>
						<p>
							Logs
						</p>
					</div>';
				if (isset($pageData['mainicons']['rss'])) echo '
					<div id="icons_rss" class="shadowdark">
						<img src="./images/icons/rss.png" alt="RSS Feed"/>
						<p>
							RSS Feed
						</p>
					</div>';
				if (isset($pageData['mainicons']['floorplan'])) echo '
					<div id="icons_floorplan" class="shadowdark">
						<img src="./images/icons/map.png" alt="Map View"/>
						<p>
							Map View
						</p>
					</div>';
				if (isset($pageData['mainicons']['console'])) echo '
					<div id="icons_console" class="shadowdark">
						<img src="./images/icons/console.png" alt="Console"/>
						<p>
							Console
						</p>
					</div>';
				if (isset($pageData['mainicons']['swipe'])) echo '
					<div id="icons_swipe" class="shadowdark">
						<img src="./images/icons/swipe.png" alt="Swipe"/>
						<p>
							Swipe
						</p>
					</div>';
				
				
				if (isset($pageData['mainicons']['schedule'])) echo '
					<div id="icons_schedule" class="shadowdark">
						<img src="./images/icons/schedule.png" alt="Schedule"/>
						<p>
							Schedule
						</p>
					</div>';
			?>
		</div>
	</div>
</div>

<img class="loading" src="./images/loader.gif" alt="">

<div class="bdialog" id="oops_warning" title="Oops! Something's wrong.">
	<p id="updatetext">The server could not gather your information.</p>
</div>

<div class="bdialog" id="account_ok" title="Your account has been updated.">
	<p id="updatetext">Your changes have been saved.</p>
</div>

<?php
	if ($pageData['liscense_popup']){
		echo '
			<div class="bdialog" id="liscenseerror" title="Your Liscense Has Expired">
				<p>You are operating under an expired liscense.  Please renew your liscense for this product as soon as possible to continue its use.</p>
			</div>
		';
	}
?>
