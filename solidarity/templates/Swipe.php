<?php
    /*===========================================*
    * 											*
    *  @Title:	TEMPLATE: Account Activatio		*
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
	var use_ws_global = <?php if ($pageData['use_ws']) echo 'true'; else echo 'false'; ?>;
	var currentpage = 'splashscreen';
	var photopath = "";
	var force_card_fucus = true;
	
	var badgeapi = <?php echo $pageData['badge']; ?>;
	var sendFSO = false;
	var sendRegister = false;
	var sendPhoto = false;
	var sendPassMonitor = false;
	var sendQuiz = false;
	
	var rawid = "";
	
	var sid = "";
	var first = "";
	var last = "";
	var ws = false;
	var openclose = false;
	var group = "";
	var sq = 0;
	var fso = 0;
	var pass=0;
	var cu_signedin=false;
	
	var OPEN = 0;
	
	var reg_user = '';
	var reg_first = '';
	var reg_last = '';
	var reg_passa = '';
	var reg_passb = '';
	var reg_email = '';
	var reg_phone = '';
	var reg_ws = '';
	var fsotime = 0;
	
	var CONNECTIVITY = true;
	
	function LoadPage(page) {
		$('#'+page).fadeIn('fast');
		$('#'+currentpage).fadeOut('fast');
		currentpage = page;
	}
	<?php if ($pageData['camera']) echo "
	sendPhoto = true;
	function SavePhoto() {
		webcam.capture();
		var url = '", 
		$coreSettings['preferences']['server_url'], 
		"swipe/", 
		$pageData['machine'], 
		"/uploadphoto/", 
		GetSessionKey(), "/';
		webcam.save(url);
		}
		";
	?>
	//<script>
	
	function ForceCardFocus() {
		if (force_card_fucus) {
			$('#card_input').removeAttr("disabled");
			$('#card_input').focus();
		} else {
			$('#card_input').attr("disabled", true);
		}
		setTimeout(function () {
			ForceCardFocus();
		}, 600);
	}
	
	function LoadWEFSO() {
		if (fso==true || fso == 1) {
			sendFSO = true;
			$('#fso_next').button('disable');
			LoadPage('colectfsoscreen');
			force_card_fucus = false;
		} else {
			LoadSafetyQuiz();
			force_card_fucus = false;
		}
	}
	
	function LoadSafetyQuiz() {
		if (sq==false || sq == 0) {
			sendQuiz = true;
			LoadPage('safetyquizscreen');
			force_card_fucus = false;
		} else {
			LoadPSScreen();
			force_card_fucus = false;
		}
	}
	
	function LoadPSScreen() {
		if ((openclose==true || openclose == 1) && (OPEN == 1 || OPEN == true)) {
			sendPassMonitor = true;
			LoadPage('passlabscreen');
			force_card_fucus = false;
		} else {
			LoadPhotoScreen();
			force_card_fucus = false;
		}
	}
	
	function LoadPhotoScreen() {
		if (sendPhoto) {
			LoadPage('photoscreen');
			setTimeout(function () {
				SavePhoto();
			}, 5000);
			force_card_fucus = false;
		} else {
			pushSignIn();
		}
	}
	
	function ResetClean() {
		sendFSO = false;
		sendRegister = false;
		sendPassMonitor = false;
		sendQuiz = false;
		
		rawid='';
		sid = "";
		first = "";
		last = "";
		ws = false;
		openclose = false;
		group = "";
		fso = 0;
		sq = 0;
		pass=0;
		fsotime = 0;
		cu_signedin=false;
		force_card_fucus = true;
		$('#card_input').val('');
		//$('#registerpanel input').val('');
		$('#registerpanel input[type=text]').val('');
		$('#registerpanel input[type=password]').val('');
		
		$('#fso_month').html('0000-00-00');
		$('#fso_hour').html('--:');
		$('#fso_min').html('--');
		
		$("#registerpanel input[type=radio]").attr('checked', false);
		$('#ws2').buttonset("refresh");
	}
	
	function pushSignIn(){
		if (OPEN == 1 || OPEN == true) {
			var url="";
			var data = "";
			var message = "";
			var badge = false;
			if (cu_signedin == true) {
				url = './swipeout/';
				data = 'session_key=<?php echo GetSessionKey(); ?>&id_number='+rawid;
				message = 'You have been signed out.';
			} else {
				url = './swipein/';
				badge=true;
				//gather more information
				var data = 'session_key=<?php echo GetSessionKey(); ?>&id_number='+rawid;
				if (sendPhoto) {
					data = data+'&photourl='+photopath;
				}
				if (sendFSO) {
					var fhours = $('#timepicker_hours').timepicker('getHour');
					if (fhours<10) fhours = '0'+fhours;
					var fmins = $('#timepicker_minutes').timepicker('getMinute');
					if (fmins<10) fmins = '0'+fmins;
					
					data = data+'&fso_timestamp='+fsotime+' '+fhours+':'+fmins+':00';
				}
				if (openclose && sendPassMonitor) {
					data = data+'&pass_mon='+pass;
				}
				if (sendQuiz) {
					data = data+'&quiz='+sq;
				}
				
				message = 'You have been signed in.';
			}
			url = url+'<?php echo GetSessionKey(); ?>/';
			$.ajax({
				url: url,
			    type: "POST",
			    dataType: "json",
			    data: data,
			  	beforeSend: function(x) {
			        if(x && x.overrideMimeType) {
			            x.overrideMimeType("application/json;charset=UTF-8");
			        }
			    },
			    //<script>
			    success: function(data) {
			    	CONNECTIVITY = true;
			    	if (data.success) {
			    		PrintBadge(sid);
			    		ShowMessage(message);
			    	} else {
			    		ShowMessage('You could not be signed in. - '+data.merror);
			    	}
			    },
			    error: function() {
			    	ShowMessage('You could not be signed in. - '+data.merror);
				   CONNECTIVITY = false;
			    }
			});
		} else {
			//gather more information
			var data = 'session_key=<?php echo GetSessionKey(); ?>&id_number='+rawid;
			if (sendPhoto) {
				data = data+'&photourl='+photopath;
			}
			if (sendFSO) {
				data = data+'&fso_timestamp='+fsotime+' '+$('#timepicker_hours').timepicker('getHour')+':'+$('#timepicker_minutes').timepicker('getMinute')+':00';
			}
			if (openclose && sendPassMonitor) {
				data = data+'&pass_mon='+pass;
			}
			if (sendQuiz) {
				data = data+'&quiz='+sq;
			}
			
			$.ajax({
				url: './swipein/',
			    type: "POST",
			    dataType: "json",
			    data: data,
			    //<script>
			    success: function(data) {
			    	if (data.success) {
			    		PrintBadge(sid);
			    		ShowMessage('The workspace has been opened.');
			    	} else {
			    		ShowMessage(data.merror);
			    	}
			    },
			    error: function() {
			    	ShowMessage('Failed to contact server.');
			    	CONNECTIVITY = false;
			    }
			});
		}
		
	}
	
	function pushSignOut(){
		if (OPEN == 1 || OPEN == true) {
			var url="";
			var data = "";
			var message = "";
			url = './swipeout/';
			data = 'session_key=<?php echo GetSessionKey(); ?>&id_number='+rawid;
			message = 'You have been signed out.';
			OPEN == 0;
			url = url+'<?php echo GetSessionKey(); ?>/';
			$.ajax({
				url: url,
			    type: "POST",
			    dataType: "json",
			    data: data,
			  	beforeSend: function(x) {
			        if(x && x.overrideMimeType) {
			            x.overrideMimeType("application/json;charset=UTF-8");
			        }
			    },
			    //<script>
			    success: function(data) {
			    	CONNECTIVITY = true;
			    	if (data.success) {
			    		
			    		OPEN = 0;
			    		if (data.closed) {
			    			ShowMessage('The workspace has been closed.');
			    		} else {
			    			ShowMessage(message);
			    		}
			    	} else {
			    		ShowMessage('You could not be signed out. - '+data.merror);
			    	}
			    },
			    error: function() {
			    	ShowMessage('You could not be signed out. - '+data.merror);
				    CONNECTIVITY = false;
			    }
			});
		} else {
			ShowMessage('The lab has just been closed.');
		}
		
	}
	
	function preSignOut(){
		var soconf = 0;
		var url = './fso/<?php echo GetSessionKey(); ?>/';
		var data = 'id_number='+rawid;
		$.ajax({
		    type: "POST",
		    dataType: "html",
		    data: data,
		    url: url,
		    success: function(html) {
		    	CONNECTIVITY = true;
		    	if (html == 1 || html == '1') {
					LoadPage('fsoscreen');
				} else {
					pushSignOut();
				}
		    },
		    error: function() {
		    	ShowMessage('Failed to get vital information.');
				CONNECTIVITY = false;
		    }
		});
	}
	
	function grabUserInfo() {
		sendFSO = false;
		sendRegister = false;
		sendPassMonitor = false;
		sendQuiz = false;
		
		sid = "";
		first = "";
		last = "";
		ws = false;
		openclose = false;
		group = "";
		fso = 0;
		sq = 0;
		pass= 0;
		cu_signedin=false;
		
		if(!CONNECTIVITY) {
			ShowMessage('Sorry, you cannot be signed in or out at this time.');
			ResetClean();
			force_card_fucus = true;
			return;
		}
		
		
		
		rawid = escape($('#card_input').val());
		var myrawid = 'id_number='+rawid;
		$('#card_input').val('');
		//ajax call
		$.ajax({
		    type: "POST",
		    dataType: "json",
		    data: myrawid,
		    beforeSend: function(x) {
		        if(x && x.overrideMimeType) {
		            x.overrideMimeType("application/json;charset=UTF-8");
		        }
		    },
		    url: './getuserinfoajax/<?php echo GetSessionKey(); ?>/',
		    //<script>
		    success: function(data) {
		    	if (data.success) {
		    		CONNECTIVITY = true;
		    		if (!data.inroom || data.inroom == false) {
			    		if (data.mustregister){
			    			LoadPage('registerscreen');
			    			sendRegister = true;
			    			force_card_fucus = false;
			    		} else {
			    			//load our stuff
			    			sid = data.sid;
			    			first = data.first;
			    			ws = data.ws;
			    			openclose = data.open_close;
			    			group = data.group;
			    			last = data.last;
							fso = data.fso;
							
							if (fso==1 || fso == true) {
								var start = new Date(data.fsodate);
		    					$( "#datepicker" ).datepicker( "option", "minDate", start );
							}
							
							sq = data.sq;
							
							cu_signedin=data.inroom;
							
			    			LoadWEFSO();
			    		}
		    		} else {
		    			//sign out
		    			preSignOut();
		    		}
		    	} else {
		    		force_card_fucus = true;
				    CONNECTIVITY = false;
		    		ShowMessage(data.error);
		    	}
		    },
		    error: function() {
		    	force_card_fucus = true;
				CONNECTIVITY = false;
		    	ShowMessage('Failed to get user info.');
		    }
		});
		force_card_fucus = false;
	}
	
	function GetLabMonitorInfo(){
		if (OPEN == 1 || OPEN == true) {
			$.ajax({
			    type: "POST",
			    dataType: "json",
			    data: '',
			    beforeSend: function(x) {
			        if(x && x.overrideMimeType) {
			            x.overrideMimeType("application/json;charset=UTF-8");
			        }
			    },
			    url: './getlabmonitor/<?php echo GetSessionKey(); ?>/',
			    success: function(data) {
			    	CONNECTIVITY = true;
			    	if (OPEN == 1 || OPEN == true) {
				    	if (data.success) {
				    		CONNECTIVITY = true;
				    		var imageurl = '<?php echo $coreSettings['preferences']['server_url']; ?>uploads/'+data.image;
				    		$('#labmonitorboximg').attr("src", imageurl);
				    		$('#m_name').html(data.name);
				    		$('#m_group').html(data.group);
				    		$('#m_since').html(data.since);
				    		if (OPEN == 1 || OPEN == true) {
				    			ListPeople();
				    		}
				    	} else {
				    		force_card_fucus = true;
				    		CONNECTIVITY = false;
				    	}
			    	}
			    },
			    error: function() {
			    	force_card_fucus = true;
				    CONNECTIVITY = false;
			    }
			});
		}

		setTimeout(function () {
			GetLabMonitorInfo();
		}, 500);
	}
	
	var usersinlist = 0;
	function ListPeople(){
		if (OPEN == 1 || OPEN == true) {
			$.ajax({
			    type: "POST",
			    dataType: "json",
			    data: '',
			    beforeSend: function(x) {
			        if(x && x.overrideMimeType) {
			            x.overrideMimeType("application/json;charset=UTF-8");
			        }
			    },
			    url: './listsignedin/<?php echo GetSessionKey(); ?>/',
			    success: function(data) {
			    	CONNECTIVITY = true;
			    	if (OPEN == 1 || OPEN == true) {
				    	if (data.success) {
				    		CONNECTIVITY = true;
				    		$('#userlist').html('');
				    		usersinlist = 0;
				    		var users = data.result;
				    		
				    		for (var i =0;i<users.length;i++) {
				    			usersinlist++;
				    			$('#userlist').html($('#userlist').html()+'<div class="swipe_person_div">'+
				    				'<p class="sp_1">'+users[i].first+' '+users[i].last+'</p>'+
				    				'<p class="sp_2">'+users[i].group+'</p>'+
				    				'<p class="sp_3">'+users[i].time+'</p>'+
				    				'<p class="sp_4">'+users[i].session+'</p><p></p>'+

				    			'</div>\n');
				    		}
				    	} else {
				    		force_card_fucus = true;
				    		CONNECTIVITY = false;
				    	}
			    	}
			    },
			    error: function() {
			    	force_card_fucus = true;
				    CONNECTIVITY = false;
			    }
			});
		}
	}
	
	function AddPerson(){
		
	}
	
	function DelPerson(){
		
	}
	
	function UpdatePerson(){
		
	}
	
	function UpdateSplash() {
		//poll the room information
		$.ajax({
		    type: "POST",
		    dataType: "json",
		    data: '',
		    //<script>
		    beforeSend: function(x) {
		        if(x && x.overrideMimeType) {
		            x.overrideMimeType("application/json;charset=UTF-8");
		        }
		    },
		    url: './germachinedata/<?php echo GetSessionKey(); ?>/',
		    //<script>
		    success: function(data) {
		    	if (data.success) {
		    		CONNECTIVITY = true;
		    		$('.lowerloading').show();
		    		$('#titlebox p').html(data.name+' Sign In');
		    		OPEN = data.open
		    		if (data.open==1 || data.open == true) {
		    			$('#openstatus').removeClass('sign_closed').addClass('sign_open').html('Open');
		    			$('#labmonitorbox').fadeIn();
		    			$('#userlist').fadeIn();
		    		} else {
		    			$('#openstatus').removeClass('sign_open').addClass('sign_closed').html('Closed');
		    			$('#labmonitorbox').fadeOut();
		    			$('#userlist').fadeOut();
		    		}
		    		$('#r_name').html('Room Contact: '+data.room_head_name);
		    		$('#r_phone').html('Contact Phone: '+data.room_head_phone);
		    	
		    		if (data.machine_disabled == 1) {
		    			window.location = '<?php echo $coreSettings['preferences']['server_url']; ?>';
		    		}
		    		
		    	} else {
		    		force_card_fucus = true;
		    		CONNECTIVITY = false;
		    		$('.lowerloading').hide();
		    	}
		    },
		    error: function() {
		    	force_card_fucus = true;
		    	CONNECTIVITY = false;
		    	$('.lowerloading').hide();
		    }
		});
		setTimeout(function () {
			UpdateSplash();
		}, 1000);
	}
	
	function ShowMessage(message) {
		force_card_fucus = false;
		$('#messagescreen p').html(message);
		LoadPage('messagescreen');
		ResetClean();
		setTimeout(function () {
			LoadPage('splashscreen');
		}, 3000);
	}

    function PrintBadge(vsid){
        var mywindow = window.open("<?php echo $coreSettings['preferences']['server_url']; ?>/swipe/<?php echo $pageData['room']; ?>/printbadge/"+vsid+"/<?php echo GetSessionKey(); ?>/", 'BADGE', 'height=900,width=620,resizable=0', false);
       setTimeout(function () {
			mywindow.close();
		}, 500);
        return true;
    }
	
	function pushRegister() {
		
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
		
		var workstudy = 0;
		
		<?php if ($pageData['use_ws'])
		{
			 echo '
			 if($("#registerpanel input[type=radio]:checked").val())
			 	workstudy = $("#registerpanel input[type=radio]:checked").val();
			 '; 
		}
		?>
		 var regdata = 'username=' + username + '&password=' + password + '&password_again=' + passwordb + '&phone=' + phone + '&email=' + email + '&first=' + first + '&last=' + last + '&idnumber=' + rawid 
	    <?php if ($pageData['use_ws']) echo " + '&workstudy=' + workstudy"; ?>
	     + '&session_key=<?php echo GetSessionKey(); ?>';
		
		//alert(regdata);
		
		$('#registerpanel input').val('');
		
		//start the ajax
	    $.ajax({
	    	url: "./registeruser/",
	        type: "post",       
	        data: regdata,     
	        cache: false,
	        dataType: "html",
	        success: function (html) {
	        	CONNECTIVITY = true;
	            //if process.php returned 1/true (send mail success)
	            if (html==1) {                  
	                 ShowMessage('You are now registered!');
	            } else if (html==2) {
	            	//bad username/password
	            	$("#updatetext").html('Some of the information that you provided is either incorect or already in use.');
					$( "#oops_warning" ).dialog('open');
	            } else if (html==3) {
	            	//account needs activation
	            	$("#updatetext").html('Error');
					$( "#oops_warning" ).dialog('open');
				} else if (html==4) {
	            	//account needs activation
	            	$("#updatetext").html('Missing Informatin!');
					$( "#oops_warning" ).dialog('open');
	            } else {
	            	$("#updatetext").html('An error occured: ' + html);
					$( "#oops_warning" ).dialog('open');
	            }          
	        },
	        error: function () {
	        	CONNECTIVITY = false;
	        	ShowMessage('Failed to register! - CODE '+html);
	        }   
	    });
	}
	
	$(function() {
		<?php if ($pageData['camera']) echo "
		$(\"#camera\").webcam({
			width: 640,
			height: 480,
			mode: \"save\",
			swffile: '",$coreSettings['preferences']['server_url'],"swf/jscam.swf',
			onSave: function(data) {
				$.ajax({
				    type: \"POST\",
				    dataType: \"json\",
				    data: 'session_key=", GetSessionKey(),"',
				    //<script>
				    beforeSend: function(x) {
				        if(x && x.overrideMimeType) {
				            x.overrideMimeType(\"application/json;charset=UTF-8\");
				        }
				    },
				    url: './getlastphoto/",GetSessionKey(),"/',
				    success: function(data) {
				    	photopath = data.path;
				    	pushSignIn();
				    },
				    error: function() {
				    	ShowMessage('Failed to upload photo.');
				    	ResetClean();
				    }
				});
			}
		});";?>//<script>

		$('#fso_next').button();
		$('#fso_next').click(function(){
			if($('#fso_month').html()=='0000-00-00' || $('#fso_hour').html()=='--:' || $('#fso_min').html()=='--') {
				ShowMessage('You have entered an invalid date.');
				return false;
			}

			LoadSafetyQuiz();
			return false;
		});
		$('#reg_c').button();
		$('#reg_c').click(function(){
			ShowMessage('You have cancled your registration.');
			return false;
		});
		$('#reg_next').button();
		$('#reg_next').click(function(){
			pushRegister();
			return false;
		});
		
		$('#s_y').click(function(){
			sq=1;
			LoadPSScreen();
			return false;
		});
		$('#s_n').click(function(){
			sq=0;
			LoadPSScreen();
			return false;
		});
		
		$('#p_y').click(function(){
			pass=1;
			LoadPhotoScreen();
			return false;
		});
		$('#p_n').click(function(){
			pass=0;
			LoadPhotoScreen();
			return false;
		});
		$('#f_y').click(function(){
			pushSignOut();
			return false;
		});
		$('#f_n').click(function(){
			ShowMessage('You have not been signed out.');
			return false;
		});
		
		$('#splashscreen').show('fast');
		$('#colectfsoscreen').hide('fast');
		$('#registerscreen').hide('fast');
		$('#passlabscreen').hide('fast');
		$('#safetyquizscreen').hide('fast');
		$('#fsoscreen').hide('fast');
		<?php if ($pageData['camera']) echo "$('#photoscreen').hide('fast');
		"; ?>
		$('#messagescreen').hide('fast');
		
		
		$('#card_input').focus();
		$('#card_input').keypress(function(event) {
			if ( event.which == 13 ) {
		    	event.preventDefault();
		    	grabUserInfo();
				return false;
		   	}
		});
		$( "#datepicker" ).datepicker({ minDate: -20, maxDate: "+0D", dateFormat: "yy-mm-dd", onSelect: function(dateText, inst) { 
		      fsotime = dateText;
		      $('#fso_month').html(dateText);
		      $('#fso_next').button('enable');
		   }
		 });
		$('#timepicker_hours').timepicker({
		    showMinutes: false,
		    showPeriod: true,
		    showLeadingZero: false,
		    onSelect: function(time, inst) { 
		      var h = $('#timepicker_hours').timepicker('getHour');
		      if (h <= 9) h = '0'+h;
		      $('#fso_hour').html(h+':');
		      $('#fso_next').button('enable');
		   }
		});
		$('#timepicker_minutes').timepicker({
		    showHours: false,
		    onSelect: function(time, inst) {
		      var k = $('#timepicker_minutes').timepicker('getMinute');
		      if (k <= 9) k = '0'+k;
		      $('#fso_min').html(k);
		      $('#fso_next').button('enable');
		   }
		});
		
		$("#ws2").buttonset();
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
			
		ForceCardFocus();
		UpdateSplash();
		GetLabMonitorInfo();
	});

</script>
<div id="blackscreen">
	<div id="splashscreen">
		<div id="titlebox" class="shadowdark">
			<p><?php echo $pageData['title']; ?></p>
		</div>
		<div id="openstatus" class="shadowdark"></div>
		<div id="currentlabinfo" class="shadowdark">
			<p id="rc">General Information</p>
			<p id="r_name">Loading...</p>
			<p id="r_phone">Loading...</p>
		</div>
		<div id="labmonitorbox" class="shadowdark">
			<p id="rc">Current Lab Monitor</p>
			<img src="" alt="" id="labmonitorboximg" />
			<p id="m_name">Loading...</p>
			<p id="m_group">Loading...</p>
			<p id="m_since">Loading...</p>
		</div>
		<div id="userlist_wrapper" class="shadowdark">
			<p id="rcz">Current Users</p>
			<div id="userlist">
				Loading...
			</div>
		</div>
		<div id="inpoutbox">
			<input type="text" id="card_input" />
		</div>
	</div>
	<div id="colectfsoscreen">
		<p class="bigtext">You did not sign out after your last session.</p>
		<p class="bigtext">When did you leave the wokspace?</p>
		<div id="datepicker"></div>
		<div id="timepicker_hours"></div>
		<div id="timepicker_minutes"></div>
		<p id="fso_month">0000-00-00</p>		
		<p id="fso_hour">--:</p>
		<p id="fso_min">--</p>
		<button class="cs_button" id="fso_next">Next</button>
	</div>
	<div id="registerscreen">
		<p class="bigtext">You must register your ID to swipe in.</p>
		<div id="registerpanel">
			<form name="register">
			<label class="reg_label">Username
			</label>
			<input type="text" name="username" id="username" tabindex="1"/><br>
			<label class="reg_label">Unique Password
			</label>
			<input type="password" name="password" id="password" tabindex="2"/><br>
			<label class="reg_label">Password Again
			</label>
			<input type="password" name="password_again" id="password_again" tabindex="3"/><br>
			<label class="reg_label">WPI Email
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
			<?php if ($pageData['use_ws']) {
				echo '<label class="reg_label">Work Study</label>
				<div id="ws2">
					<input type="radio" id="radio1" name="workstudy_radio" value="1" /><label for="radio1">Yes</label>
					<input type="radio" id="radio2" name="workstudy_radio" value="0" /><label for="radio2">No</label>
				</div><br>';
			} ?>
			</form>
		</div>
		<button class="cs_button_r" id="reg_next" >Register</button>
		<button class="c_button" id="reg_c" >Cancel</button>
	</div>
	<div id="safetyquizscreen">
		<p class="bigtext">Have you passed the Washburn Safety Quiz?</p>
		<div id="s_y" class="shadowdark"><p>Yes, I have.</p></div>
		<div id="s_n" class="shadowdark"><p>No, I have not.</p></div>
	</div>
	<div id="passlabscreen">
		<p class="bigtext">Do you want to become the new Lab Monitor?</p>
		<div id="p_y"><p>Make me the Lab Monitor.</p></div>
		<div id="p_n"><p>Keep current Lab Monitor.</p></div>
	</div>
	<div id="fsoscreen">
		<p class="bigtext">There are users in the workspace!</p>
		<div id="f_y"><p>Close the workspace anyway.</p></div>
		<div id="f_n"><p>Stay and get a replacement.</p></div>
	</div>
	<?php if ($pageData['camera']) echo '
	<div id="photoscreen">
		<p class="bigtext">One Moment... </p>
		<div id="camera"></div>
	</div>
	'; ?>
	<div id="messagescreen">
		<p>This text should change.</p>
	</div>
</div>

<img class="lowerloading" src="../../images/loader.gif" alt="">

<div class="bdialog" id="oops_warning" title="Oops! Something's wrong.">
	<p id="updatetext">The server could not log you on.</p>
</div>
