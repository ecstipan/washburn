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
<script type="text/javascript">	
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
	//begin jqeuery stuff
	
	function loadSettings(){
		$( "registration_default_group option").removeAttr('selected');
		showloading();
		loadingvalue(30);
		var hours=new Array(); 
		$.ajax({
		    type: "POST",
		    dataType: "json",
		    data: 'session_key=<?php echo $pageData['session_key']; ?>',
		    beforeSend: function(x) {
		        if(x && x.overrideMimeType) {
		            x.overrideMimeType("application/json;charset=UTF-8");
		        }
		    },
		    url: '../ajax/adminsettings/',
		    success: function(data) {
		        // 'data' is a JSON object which we can access directly.
		        // Evaluate the data.success member and do something appropriate...
		        
		        if (data.success == true){
		          	loadingvalue(50);
		          	var settings = data.results;
		           
		         	 for (var i = 0; i < settings.length; i++) {
		           		if (settings[i].type == 'check') {
		           			var value = settings[i].value;
		           			var name = settings[i].name;
		           			
		      				//update our information
		           			if (value == 1){
		           				$('#radio_'+ name + '_1').attr('checked', 'checked');
		           				$('#radio_'+ name + '_2').removeAttr('checked');
		           			} else {
		           				$('#radio_'+ name + '_2').attr('checked', 'checked');
		           				$('#radio_'+ name + '_1').removeAttr('checked');
		           			}
		           			
		           			//set this the first time too
		           			$( "#" + settings[i].name ).buttonset({create: function(event, ui) {
		           				if (value == 1){
			           				$('#radio_'+ name + '_1').attr('checked', 'checked');
			           				$('#radio_'+ name + '_2').removeAttr('checked');
			           			} else {
			           				$('#radio_'+ name + '_2').attr('checked', 'checked');
			           				$('#radio_'+ name + '_1').removeAttr('checked');
			           			}
		           			}});
		           		} else {
		           			if (settings[i].name=='registration_default_group') {
		           				$( "option").attr('selected', false);
		           				$('select [name=registration_default_group]').val(settings[i].value);
		           				$( "#def_group_" + settings[i].value ).attr('selected', true);
		           				$('.ui-combobox-input').val($('#combobox option:selected').html());
		           			} else {
		           				if (settings[i].catagory=='Hours') {
		           					hours[settings[i].name] = settings[i].value;
		           				} else {
		           					$( "#" + settings[i].name ).val(settings[i].value);
		           				}
		           			}
		           		}
		           		loadingvalue(50+i);
		           	}
		           	
		           	//set our values
					$( "#hourslider_monday").slider("values", 0, hours['hours_monday_start']).slider("values", 1, hours['hours_monday_end']);
		           	$( "#hourslider_monday").parent().find( ".hour_readout" ).html( "" + hours['hours_monday_start'] + ":00 to " + hours['hours_monday_end'] + ":00" );
		           	
					$( "#hourslider_tuesday").slider("values", 0, hours['hours_tuesday_start']).slider("values", 1, hours['hours_tuesday_end']);
		           	$( "#hourslider_tuesday").parent().find( ".hour_readout" ).html( "" + hours['hours_tuesday_start'] + ":00 to " + hours['hours_tuesday_end'] + ":00" );
		           	
		           	$( "#hourslider_wednesday").slider("values", 0, hours['hours_wednesday_start']).slider("values", 1, hours['hours_wednesday_end']);
		           	$( "#hourslider_wednesday").parent().find( ".hour_readout" ).html( "" + hours['hours_wednesday_start'] + ":00 to " + hours['hours_wednesday_end'] + ":00" );
		           	
		           	$( "#hourslider_thursday").slider("values", 0, hours['hours_thursday_start']).slider("values", 1, hours['hours_thursday_end']);
		           	$( "#hourslider_thursday").parent().find( ".hour_readout" ).html( "" + hours['hours_thursday_start'] + ":00 to " + hours['hours_thursday_end'] + ":00" );
		           	
		           	$( "#hourslider_friday").slider("values", 0, hours['hours_friday_start']).slider("values", 1, hours['hours_friday_end']);
		           	$( "#hourslider_friday").parent().find( ".hour_readout" ).html( "" + hours['hours_friday_start'] + ":00 to " + hours['hours_friday_end'] + ":00" );
		           	
		           	$( "#hourslider_saturday").slider("values", 0, hours['hours_saturday_start']).slider("values", 1, hours['hours_saturday_end']);
		           	$( "#hourslider_saturday").parent().find( ".hour_readout" ).html( "" + hours['hours_saturday_start'] + ":00 to " + hours['hours_saturday_end'] + ":00" );
		           	
		           	$( "#hourslider_sunday").slider("values", 0, hours['hours_sunday_start']).slider("values", 1, hours['hours_sunday_end']);
		           	$( "#hourslider_sunday").parent().find( ".hour_readout" ).html( "" + hours['hours_sunday_start'] + ":00 to " + hours['hours_sunday_end'] + ":00" );
		           	
		           	loadingvalue(100);
		           	loadingvalue_start(100);
					hideMainLoader()
		        } else{
		        	$("#updatetext").html('Failed to load system settings data.');
					$( "#oops_warning" ).dialog('open');
		        }
		    },
		    error: function(data) {
		    	$("#updatetext").html('Failed to load system settings data.');
				$( "#oops_warning" ).dialog('open');
		    }
		});
		$("#save_button").button('disable').button('refresh');
		$("#cancel_button").button('disable').button('refresh');
		$("#save_button").button( "widget" ).removeClass('ui-state-active');
		$("#save_button").button( "widget" ).removeClass('ui-state-hover');
		$("#cancel_button").button( "widget" ).removeClass('ui-state-active');
		$("#cancel_button").button( "widget" ).removeClass('ui-state-hover');
		
		
		setTimeout(function () {
			hideloading();
			setTimeout(function () {
				$("#top_button-1").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-1").button( "widget" ).removeClass('ui-state-hover');
				$("#top_button-1").button('enable');
			}, 200);
		}, 200);
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
		}, 500);
	}
	
	function saveSettings() {
		$("#cancel_button").button('disable');
		$("#save_button").button('disable');
		$("#top_button-2").button('disable');
		
		showloading();
		loadingvalue(10);
		var quit=false;
		var data = 'save=true&session_key=<?php echo $pageData['session_key']; ?>';
		//validate all of our standard inputs
		$("#accordion").find('input').each(function() {
			if ($('#'+this.id).attr('type') == "text") {
				$('#'+this.id).removeClass('errorhighlight');
				//validate our data
				if (($('#'+this.id).attr('type') == "text" || $('#'+this.id).attr('type') == "password") && $('#'+this.id).attr('required') == "required") {
					var sett=$('#'+this.id).val();
					if (sett=="" || sett ==null || sett.length < 1) {
						$('#'+this.id).addClass('errorhighlight');
						$("#updatetext").html('Please enter a valid value for setting: '+this.id+'.');
						$( "#oops_warning" ).dialog('open');
						hideloading();
						$("#top_button-2").button('enable');
						$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
						$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
						
						quit = true;
					} else {
						if ($('#'+this.id).attr('email') == "validate"){
							var atpos=sett.indexOf("@");
							var dotpos=sett.lastIndexOf(".");
							if (atpos<1 || dotpos<atpos+2 || dotpos+2>=sett.length)
							{
								$("#updatetext").html('Please enter a valid email for setting: '+this.id+'.');
								$( "#oops_warning" ).dialog('open');
								hideloading();
								$("#top_button-2").button('enable');
								$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
								$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
								
								quit = true;
							}
						}
						data = data + '&'+this.id+'='+sett;
					}
				}
			}
		});
		
		//checkboxes
		var radiodivs=new Array();
		$("#accordion").find('.radiodiv').each(function() {
			var name = this.id;
			var sett=$('#'+this.id+' input[type=radio]:checked').val();
			radiodivs[name] = sett;
			data = data + '&'+name+'='+sett;
		});
		//required values if the checkbox is checked
		if (radiodivs['mail_smtp']==1) {
			var sett=$('#mail_smtp_host').val();
			if (sett=="" || sett ==null || sett.length < 1) {
				$("#updatetext").html('Please enter a valid value for setting: mail_smtp_host.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				$('#mail_smtp_host').addClass('errorhighlight');
				quit = true;
			}
			data = data + '&mail_smtp_host'+'='+sett;
			var sett=$('#mail_smtp_port').val();
			if (sett=="" || sett ==null || sett.length < 1) {
				$("#updatetext").html('Please enter a valid value for setting: mail_smtp_port.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				$('#mail_smtp_port').addClass('errorhighlight');
				quit = true;
			}
			data = data + '&mail_smtp_port'+'='+sett;
		}
		if (radiodivs['mail_smtp_auth']==1) {
			var sett=$('#mail_smtp_user').val();
			if (sett=="" || sett ==null || sett.length < 1) {
				$("#updatetext").html('Please enter a valid value for setting: mail_smtp_user.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				$('#mail_smtp_user').addClass('errorhighlight');
				quit = true;
			}
			data = data + '&mail_smtp_user'+'='+sett;
			var sett=$('#mail_smtp_password').val();
			if (sett=="" || sett ==null || sett.length < 1) {
				$("#updatetext").html('Please enter a valid value for setting: mail_smtp_password.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				$('#mail_smtp_password').addClass('errorhighlight');
				quit = true;
			}
			data = data + '&mail_smtp_password'+'='+sett;
		}
		if (radiodivs['rss_use_password']==1) {
			var sett=$('#rss_password').val();
			if (sett=="" || sett ==null || sett.length < 1) {
				$("#updatetext").html('Please enter a valid value for setting: rss_password.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				$('#rss_password').addClass('errorhighlight');
				quit = true;
			}
			data = data + '&rss_password'+'='+sett;
		}
		if (radiodivs['registration_email_filter']==1) {
			var sett=$('#registration_email_domain').val();
			if (sett=="" || sett ==null || sett.length < 1) {
				$("#updatetext").html('Please enter a valid value for setting: registration_email_domain.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				$('#registration_email_domain').addClass('errorhighlight');
				quit = true;
			}
			data = data + '&registration_email_domain'+'='+sett;
		}
		
		//sliders
		data = data+'&hours_'+'monday'+'_start='+$( "#hourslider_"+"monday").slider("values", 0);
		data = data+'&hours_'+'monday'+'_end='+$( "#hourslider_"+"monday").slider("values", 1);
		
		data = data+'&hours_'+'tuesday'+'_start='+$( "#hourslider_"+"tuesday").slider("values", 0);
		data = data+'&hours_'+'tuesday'+'_end='+$( "#hourslider_"+"tuesday").slider("values", 1);
		
		data = data+'&hours_'+'wednesday'+'_start='+$( "#hourslider_"+"wednesday").slider("values", 0);
		data = data+'&hours_'+'wednesday'+'_end='+$( "#hourslider_"+"wednesday").slider("values", 1);
		
		data = data+'&hours_'+'thursday'+'_start='+$( "#hourslider_"+"thursday").slider("values", 0);
		data = data+'&hours_'+'thursday'+'_end='+$( "#hourslider_"+"thursday").slider("values", 1);
		
		data = data+'&hours_'+'friday'+'_start='+$( "#hourslider_"+"friday").slider("values", 0);
		data = data+'&hours_'+'friday'+'_end='+$( "#hourslider_"+"friday").slider("values", 1);
		
		data = data+'&hours_'+'saturday'+'_start='+$( "#hourslider_"+"saturday").slider("values", 0);
		data = data+'&hours_'+'saturday'+'_end='+$( "#hourslider_"+"saturday").slider("values", 1);
		
		data = data+'&hours_'+'sunday'+'_start='+$( "#hourslider_"+"sunday").slider("values", 0);
		data = data+'&hours_'+'sunday'+'_end='+$( "#hourslider_"+"sunday").slider("values", 1);
		
		//group select
		var defgroup = $('#combobox').val();
		if (defgroup == "" || defgroup<0) {
				$("#updatetext").html('Please enter a valid value for setting: registration_default_group.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				quit = true;
		}
		data = data+'&registration_default_group='+defgroup;
		
		if (quit) return false;
		//time for the big ajax call	
		$.ajax({
		    type: "POST",
		    dataType: "json",
		    data: data,
		    beforeSend: function(x) {
		        if(x && x.overrideMimeType) {
		            x.overrideMimeType("application/json;charset=UTF-8");
		        }
		    },
		    url: '../admin/',
		    success: function(data) {
		    	if (data.success){
		    		loadingvalue(100);
		    		$( "#save_ok" ).dialog('open');
					setTimeout(function () {
						hideloading();
						loadingvalue(0);
						setTimeout(function () {
							loadSettings();
							$("#top_button-2").button('enable');
							$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
							$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
						}, 200);
					}, 100);
		    	} else {
		    		$("#updatetext").html(data.message);
					$( "#oops_warning" ).dialog('open');
					hideloading();
					$("#top_button-2").button('enable');
					$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
					$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
					hideloading();
					loadingvalue(0);
		    	}
		    },
		    error: function(data) {
		    	$("#updatetext").html('Could not sync settings.');
				$( "#oops_warning" ).dialog('open');
				hideloading();
				$("#top_button-2").button('enable');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-active');
				$("#top_button-2").button( "widget" ).removeClass('ui-state-hover');
				hideloading();
				loadingvalue(0);
		    }
		});
	}
	
	(function( $ ) {
		$.widget( "ui.combobox", {
			_create: function() {
				var input,
					self = this,
					select = this.element.hide(),
					selected = select.children( ":selected" ),
					value = selected.val() ? selected.text() : "",
					wrapper = this.wrapper = $( "<span>" )
						.addClass( "ui-combobox" )
						.insertAfter( select );

				input = $( "<input>" )
					.appendTo( wrapper )
					.val( value )
					.addClass( "ui-combobox-input" )
					.removeClass('ui-autocomplete-input')
					.autocomplete({
						delay: 0,
						minLength: 0,
						source: function( request, response ) {
							var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
							response( select.children( "option" ).map(function() {
								var text = $( this ).text();
								if ( this.value && this.value != -1 && ( !request.term || matcher.test(text) ) )
									return {
										label: text.replace(
											new RegExp(
												"(?![^&;]+;)(?!<[^<>]*)(" +
												$.ui.autocomplete.escapeRegex(request.term) +
												")(?![^<>]*>)(?![^&;]+;)", "gi"
											), "<strong>$1</strong>" ),
										value: text,
										option: this
									};
							}) );
						},
						select: function( event, ui ) {
							ui.item.option.selected = true;
							self._trigger( "selected", event, {
								item: ui.item.option
							});
							$("#save_button").button('enable');
		 					$("#cancel_button").button('enable');
						},
						change: function( event, ui ) {
							$("#save_button").button('enable');
		 					$("#cancel_button").button('enable');
							if ( !ui.item ) {
								var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
									valid = false;
								select.children( "option" ).each(function() {
									if ( $( this ).text().match( matcher ) ) {
										this.selected = valid = true;
										return false;
									} else {
										this.selected = false;
									}
								});
								if ( !valid ) {
									// remove invalid value, as it didn't match anything
									$( this ).val( "" );
									select.val( "" );
									input.data( "autocomplete" ).term = "";
									return false;
								}
							}
						}
					});

				input.data( "autocomplete" )._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + item.label + "</a>" )
						.appendTo( ul );
				};
				
				$( "<a>" )
					.attr( "tabIndex", -1 )
					.attr( "title", "Show All Items" )
					.appendTo( wrapper )
					.button({
						icons: {
							primary: "ui-icon-triangle-1-s"
						},
						text: false
					})
					.addClass( "ui-corner-all ui-combobox-toggle" )
					.click(function() {
						// close if already visible
						if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
							input.autocomplete( "close" );
							return;
						}

						// work around a bug (likely same cause as #5265)
						$( this ).blur();

						// pass empty string as value to search for, displaying all results
						input.autocomplete( "search", "" );
						input.focus();
					});
			},

			destroy: function() {
				this.wrapper.remove();
				this.element.show();
				$.Widget.prototype.destroy.call( this );
			}
		});
	})( jQuery );
	
	$(function() {
		//setup DOM elements
		$(".loading").hide();
		$("#mask_loadingbar").progressbar({
			value: 0
		});
		$("#top_progressbar").hide();
		$("#top_buttons").buttonset();
		$("#accordion").accordion();
		$("#top_progressbar_bar").progressbar({
			value: 0
		});
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
		$( "#save_ok" ).dialog({
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
		$( "#help_dialog" ).dialog({
			autoOpen: false,
			modal:false,
			show: 'fade',
			hide: 'fade',
			buttons: {
				"Ok": function() { 
						$(this).dialog("close");
					}
			},
			close: function(event, ui) {
				$("#top_button-3").button( "widget" ).removeClass('ui-state-active ui-state-hover');
				$("#top_button-3").button('enable');
			}
		});
		
		$( "#cancel_button" ).button();
		$( "#cancel_button" ).click(function() {
			$("#top_button-1").button('disable');
			loadSettings();
			return false;
		});
		$("#save_button").button();
		$("#save_form").find(":input").change(function() {
		   $("#save_button").button('enable');
		   $("#cancel_button").button('enable');
		});
		$("#save_form").find(":input").keypress(function(event) {
			if ( event.which == 13 ) {
		    	event.preventDefault();
		    	saveSettings();
				return false;
		   	}
		  $("#save_button").button('enable');
		  $("#cancel_button").button('enable');
		});
		
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
		
		//button functions
		$("#top_button_home").click(function() {
			window.location = '<?php echo $pageData['header_url']; ?>';
			return false;
		});
		$("#top_button-1").click(function() {
			$("#top_button-1").button('disable');
			loadSettings();
		});
		$("#top_button-2").click(function() {
			saveSettings();
			return false;
		});
		$("#top_button-3").click(function() {
			$("#help_dialog").dialog('open');
			$("#top_button-3").button('disable');
		});
		$("#top_button-4").click(function() {
			window.location = '<?php echo $pageData['header_url']; ?>logout/';
			return false;
		});
		
		$(".tooltip_label").tooltip({ 
		    bodyHandler: function() { 
		        return $(this).attr("rel"); 
		    }, 
		    delay: 300,
		    track: true,
		    showURL: false 
		});
		loadSettings();
		
		$( "#combobox" ).combobox();
		
		//sliders
		$( "#hourslider_monday, #hourslider_tuesday, #hourslider_wednesday, #hourslider_thursday, #hourslider_friday, #hourslider_saturday, #hourslider_sunday" ).slider({
			range: true,
			min: 0,
			max: 24,
			step: 1,
			values: [ 9, 17 ],
			slide: function( event, ui ) {
				$("#save_button").button('enable');
		  		 $("#cancel_button").button('enable');
				$(this).parent().find( ".hour_readout" ).html( "" + ui.values[ 0 ] + ":00 to " + ui.values[ 1 ] + ":00" );
			}
		});
		$("#save_button").click(function() {
			saveSettings();
			$("#save_button").button('disable');
			return false;
		});
	});
</script>

<div id="main_loading_mask">
	<p>Syncing System Settings...</p>
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
					<button id="top_button_home" />Home</button>
					<button id="top_button-1" />Force Reload</button>
					<button id="top_button-2" />Force Save</button>
					<button id="top_button-3" />Help</button>
					<button id="top_button-4" />Logout</button>
				</div>
			</span>
		</div>
		<div id="top_progressbar">
			<font id="progress_text">Syncing...</font>
			<div id="top_progressbar_bar"></div>
		</div>
	</div>
	<div id="main_window" class="main_bodybox_big">
		<div id="savesbox" class="shadowdark">
			<form name="settings" method="POST" id="save_form">
				<div id="accordion">
					<h3><a href="#">Server Settings</a></h3>
    				<div>
						<label class="reg_label tooltip_label" rel="This is the name of the server and the title of the Control Panel.">Server Title:
						</label>
						<input type="text" name="server_title" id="server_title" class="special_input reg_width" required="required"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="The DNS or web address of the server.  This should point to the application directory.  Should lead with http:// and trail with a /.">Server URL:
						</label>
						<input type="text" name="server_url" id="server_url" class="special_input reg_width" required="required"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Lock the server into maintenance mode.  This disables the Control Panel for non-admins.">Maintenance:
						</label>
						<div id="maint_mode" class="radiodiv">
							<input type="radio" id="radio_maint_mode_1" name="maint_mode" value="1" /><label id="label_maint_mode_1" for="radio_maint_mode_1">Yes</label>
							<input type="radio" id="radio_maint_mode_2" name="maint_mode" value="0" /><label id="label_maint_mode_2" for="radio_maint_mode_2">No</label>
						</div><br>
						</br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Is the server in Daylight Savings Time?">DST:
						</label>
						<div id="daylight_savings_time" class="radiodiv">
							<input type="radio" id="radio_daylight_savings_time_1" name="daylight_savings_time" value="1" /><label id="label_daylight_savings_time_1" for="radio_daylight_savings_time_1">Yes</label>
							<input type="radio" id="radio_daylight_savings_time_2" name="daylight_savings_time" value="0" /><label id="label_daylight_savings_time_2" for="radio_daylight_savings_time_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="The standard GMT timezone offset.<Br><?php echo $pageData['time']; ?>">Timezone:
						</label>
						<input type="text" name="timezone_gmt" id="timezone_gmt" class="special_input reg_width" required="required"/><br>
						<div class="ui-helper-clearfix"></div>
					</div>
					<h3><a href="#">Mail</a></h3>
    				<div>
						<label class="reg_label tooltip_label" rel="The email address of the server's administrator.">Admin Email:
						</label>
						<input type="text" name="admin_email_addr" id="admin_email_addr" class="special_input reg_width" required="required" email="validate"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Should the server use SMTP Mail Factory instead of mail()?">Use SMTP:
						</label>
						<div id="mail_smtp" class="radiodiv">
							<input type="radio" id="radio_mail_smtp_1" name="mail_smtp" value="1" /><label id="label_mail_smtp_1" for="radio_mail_smtp_1">Yes</label>
							<input type="radio" id="radio_mail_smtp_2" name="mail_smtp" value="0" /><label id="label_mail_smtp_2" for="radio_mail_smtp_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="If using SMTP, the host of the SMTP server.">SMTP Host:
						</label>
						<input type="text" name="mail_smtp_host" id="mail_smtp_host" class="special_input reg_width"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="The port for the SMTP server.">SMTP Port:
						</label>
						<input type="text" name="mail_smtp_port" id="mail_smtp_port" class="special_input reg_width"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Does the SMTP server require authorization?">SMTP Auth:
						</label>
						<div id="mail_smtp_auth" class="radiodiv">
							<input type="radio" id="radio_mail_smtp_auth_1" name="mail_smtp_auth" value="1" /><label id="label_mail_smtp_auth_1" for="radio_mail_smtp_auth_1">Yes</label>
							<input type="radio" id="radio_mail_smtp_auth_2" name="mail_smtp_auth" value="0" /><label id="label_mail_smtp_auth_2" for="radio_mail_smtp_auth_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="The username for the SMTP account.">SMTP User:
						</label>
						<input type="text" name="mail_smtp_user" id="mail_smtp_user" class="special_input reg_width"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="The password for the SMTP account.">SMTP Pass:
						</label>
						<input type="password" name="mail_smtp_password" id="mail_smtp_password" class="special_input reg_width"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Use SSL encryption to send email?">Use SSL:
						</label>
						<div id="mail_smtp_ssl" class="radiodiv">
							<input type="radio" id="radio_mail_smtp_ssl_1" name="mail_smtp_ssl" value="1" /><label id="label_mail_smtp_ssl_1" for="radio_mail_smtp_ssl_1">Yes</label>
							<input type="radio" id="radio_mail_smtp_ssl_2" name="mail_smtp_ssl" value="0" /><label id="label_mail_smtp_ssl_2" for="radio_mail_smtp_ssl_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
					</div>
					<h3><a href="#">Notifications</a></h3>
    				<div>
    					<label class="reg_label tooltip_label" rel="Enable RSS functionality?">Enable RSS:
						</label>
						<div id="rss_enable" class="radiodiv">
							<input type="radio" id="radio_rss_enable_1" name="rss_enable" value="1" /><label id="label_rss_enable_1" for="radio_rss_enable_1">Yes</label>
							<input type="radio" id="radio_rss_enable_2" name="rss_enable" value="0" /><label id="label_rss_enable_2" for="radio_rss_enable_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Password-protect the RSS feed?">Use RSS Pass:
						</label>
						<div id="rss_use_password" class="radiodiv">
							<input type="radio" id="radio_rss_use_password_1" name="rss_use_password" value="1" /><label id="label_rss_use_password_1" for="radio_rss_use_password_1">Yes</label>
							<input type="radio" id="radio_rss_use_password_2" name="rss_use_password" value="0" /><label id="label_rss_use_password_2" for="radio_rss_use_password_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="The password for the RSS feed.">RSS Pass:
						</label>
						<input type="text" name="rss_password" id="rss_password" class="special_input reg_width"/><br>
						<div class="ui-helper-clearfix"></div>
    				</div>
    				<h3><a href="#">User Registration</a></h3>
    				<div>
    					<label class="reg_label tooltip_label" rel="Enable non-swipe terminal registrations?">Enabled:
						</label>
						<div id="registration_enabled" class="radiodiv">
							<input type="radio" id="radio_registration_enabled_1" name="registration_enabled" value="1" /><label id="label_registration_enabled_1" for="radio_registration_enabled_1">Yes</label>
							<input type="radio" id="radio_registration_enabled_2" name="registration_enabled" value="0" /><label id="label_registration_enabled_2" for="radio_registration_enabled_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Use the Work Study field in registration?">Work Study:
						</label>
						<div id="registration_work_study" class="radiodiv">
							<input type="radio" id="radio_registration_work_study_1" name="registration_work_study" value="1" /><label id="label_registration_work_study_1" for="radio_registration_work_study_1">Yes</label>
							<input type="radio" id="radio_registration_work_study_2" name="registration_work_study" value="0" /><label id="label_registration_work_study_2" for="radio_registration_work_study_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Only allow registrations from a specific email domain?">Email Filter:
						</label>
						<div id="registration_email_filter" class="radiodiv">
							<input type="radio" id="radio_registration_email_filter_1" name="registration_email_filter" value="1" /><label id="label_registration_email_filter_1" for="radio_registration_email_filter_1">Yes</label>
							<input type="radio" id="radio_registration_email_filter_2" name="registration_email_filter" value="0" /><label id="label_registration_email_filter_2" for="radio_registration_email_filter_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Only allow emails from this domain.">Domain Filter:
						</label>
						<input type="text" name="registration_email_domain" id="registration_email_domain" class="special_input reg_width"/><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="The default group to place all new registered members into.">Default Group:
						</label>
						<select class="reg_width" name="registration_default_group" id="combobox">
							<option value="-1">Select a Group...</option>
						<?php
							//load our groups
							$sql = "SELECT `id_group`,`title`
									FROM {{DB}}.`solidarity_groups`;";
									
							$result = queryDB($sql);
							
							if (!$result || !isset($result[0]['id_group'])) echo '<option selected="selected" value = "-1">Failed to load Groups</option>';
							else {
								$i=0;
								while (isset($result[$i])) {
									echo '<option id="def_group_'.$result[$i]['id_group'].'" value="'.$result[$i]['id_group'].'">'.$result[$i]['title'].'</option>
									';

									$i++;
								}
							}
						?>
						</select>
						<div class="ui-helper-clearfix"></div>
    				</div>
    				<h3><a href="#">Swipe Machines</a></h3>
    				<div>
    					<label class="reg_label tooltip_label" rel="Enable remote swipe terminals?  Note: This will only disable sign in events.  People will still be able to leave a workspace."> Enabled?:
						</label>
						<div id="swipe_enabled" class="radiodiv">
							<input type="radio" id="radio_swipe_enabled_1" name="swipe_enabled" value="1" /><label id="label_swipe_enabled_1" for="radio_swipe_enabled_1">Yes</label>
							<input type="radio" id="radio_swipe_enabled_2" name="swipe_enabled" value="0" /><label id="label_swipe_enabled_2" for="radio_swipe_enabled_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Attempt to use javascript and a webcam to take swipe event photos?"> Take Photos?:
						</label>
						<div id="swipe_photos_enabled" class="radiodiv">
							<input type="radio" id="radio_swipe_photos_enabled_1" name="swipe_photos_enabled" value="1" /><label id="label_swipe_photos_enabled_1" for="radio_swipe_photos_enabled_1">Yes</label>
							<input type="radio" id="radio_swipe_photos_enabled_2" name="swipe_photos_enabled" value="0" /><label id="label_swipe_photos_enabled_2" for="radio_swipe_photos_enabled_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
						
						<label class="reg_label tooltip_label" rel="Print badges on sign-in?"> Print Badges?:
						</label>
						<div id="swipe_print_sticker" class="radiodiv">
							<input type="radio" id="radio_swipe_print_sticker_1" name="swipe_print_sticker" value="1" /><label id="label_swipe_print_sticker_1" for="radio_swipe_print_sticker_1">Yes</label>
							<input type="radio" id="radio_swipe_print_sticker_2" name="swipe_print_sticker" value="0" /><label id="label_swipe_print_sticker_2" for="radio_swipe_print_sticker_2">No</label>
						</div><br>
						<div class="ui-helper-clearfix"></div>
    				</div>
    				<h3><a href="#">Usage Hours</a></h3>
    				<div>
    					<label class="reg_label tooltip_label extra_padding" rel="Allow any member to swipe in during these hours."> Monday:
    					</label>
    					<div id="swipe_hours_monday" class="swipe_hour_div">
    						<span class="hour_readout"></span>
    						<div id="hourslider_monday" class="thinslider"></div>
    					</div>
    					<div class="ui-helper-clearfix"></div>
    					
    					<label class="reg_label tooltip_label extra_padding" rel="Allow any member to swipe in during these hours."> Tuesday:
    					</label>
    					<div id="swipe_hours_tuesday" class="swipe_hour_div">
    						<span class="hour_readout"></span>
    						<div id="hourslider_tuesday" class="thinslider"></div>
    					</div>
    					<div class="ui-helper-clearfix"></div>
    					
    					<label class="reg_label tooltip_label extra_padding" rel="Allow any member to swipe in during these hours."> Wednesday:
    					</label>
    					<div id="swipe_hours_wednesday" class="swipe_hour_div">
    						<span class="hour_readout"></span>
    						<div id="hourslider_wednesday" class="thinslider"></div>
    					</div>
    					<div class="ui-helper-clearfix"></div>
    					
    					<label class="reg_label tooltip_label extra_padding" rel="Allow any member to swipe in during these hours."> Thursday:
    					</label>
    					<div id="swipe_hours_thursday" class="swipe_hour_div">
    						<span class="hour_readout"></span>
    						<div id="hourslider_thursday" class="thinslider"></div>
    					</div>
    					<div class="ui-helper-clearfix"></div>
    					
    					<label class="reg_label tooltip_label extra_padding" rel="Allow any member to swipe in during these hours."> Friday:
    					</label>
    					<div id="swipe_hours_friday" class="swipe_hour_div">
    						<span class="hour_readout"></span>
    						<div id="hourslider_friday" class="thinslider"></div>
    					</div>
    					<div class="ui-helper-clearfix"></div>
    					
    					<label class="reg_label tooltip_label extra_padding" rel="Allow any member to swipe in during these hours."> Saturday:
    					</label>
    					<div id="swipe_hours_saturday" class="swipe_hour_div">
    						<span class="hour_readout"></span>
    						<div id="hourslider_saturday" class="thinslider"></div>
    					</div>
    					<div class="ui-helper-clearfix"></div>
    					
    					<label class="reg_label tooltip_label extra_padding" rel="Allow any member to swipe in during these hours."> Sunday:
    					</label>
    					<div id="swipe_hours_sunday" class="swipe_hour_div">
    						<span class="hour_readout"></span>
    						<div id="hourslider_sunday" class="thinslider"></div>
    					</div>
    					<div class="ui-helper-clearfix"></div>
    				</div>
				</div>
				<div class="ui-helper-clearfix"></div>
				<input type="hidden" name="session_key" value="<?php echo $pageData['session_key']; ?>" />
				<br>
				<div class="ui-helper-clearfix"></div>
				<button id="cancel_button" tabindex="20">Cancel</button><button id="save_button" tabindex="15">Save</button>
			</form>
		</div>
	</div>
</div>

<img class="loading" src="../images/loader.gif" alt="">

<div class="bdialog" id="oops_warning" title="Oops! Something's wrong.">
	<p id="updatetext">The server could not gather your information.</p>
</div>

<div class="bdialog" id="save_ok" title="System Settings have been updated.">
	<p>Your changes have been saved.</p>
</div>

<div class="bdialog" id="help_dialog" title="Help">
	<p>
		Hover over the title of a setting to get more information about it.  Settings are organized into catagories.
	</p>
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
