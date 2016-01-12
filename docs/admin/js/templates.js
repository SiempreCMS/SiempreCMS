/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
	
var curTabId = 0;
var contentChanged = false;
	
function showLogin() {
	$("#login-dialog").dialog({
		modal: true,
		dialogClass: "no-close",
		closeOnEscape: false,
		buttons: {
			"Log in": function() {
				$.ajaxq("queue", {
				  url: 'login_ajax.php',
//				  async: false, 
				  data: 'action=login&' + $("form#login-form").serialize(),
				  dataType: 'json',
				  type: 'POST',
				  cache: false,
				  error: function(xhr, textStatus, errorThrown){
						alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
					},
				  success: function (j) {		  
					if (j.result == true) {
						//alert('yup');
						$('#login-dialog').dialog("close");
					}
					else {
						$("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
					}
				  }
				});				
			}
		}
	});	
}		
	
	
function showHelpDialog() {	
	$("#booking-help-dialog").dialog({
		closeOnEscape: true,
		modal: true,
		draggable: true,
		resizable: true,
		width: 800,
		height: 500
	//	buttons: {
	//			Close: function() {
	//				$( this ).dialog( "close" );
	//			}
	//	},
	
	});
}


function getTemplatesList() {
	// TODO clear contents?
	$("#template-results-buttons").html('');
	
	// gets list of templates to load on the right hand side of the menu
		$.ajaxq('queue', {
          url: 'templates_ajax.php',
//		  async: false, 
          data: 'action=getTemplatesList',
          dataType: 'json',
          type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
                alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
            },
          success: function (j) {		 
			// handle not logged in
			if (j.NotLoggedIn == true) {
					showLogin();
					return false;
			}
			
			// TO DO check is not null - I've done this elsewhere... needs adding to all j.result
			if (j.result == true) {
				$.each(j.results, function(key, value) {					
					// add to a list on the right hand side
				//	$("#tab-"+value['tabID']).append(value['entityID'] + ': ' + value['entity_name'] + '<input type="text" id="entity-' + value['entityID'] + '" name="entity-' + value['entityID'] + '" /> <br/>');	
			//	$("#templatelist").append('<p>' + value['templateID'] + ' - ' + value['name'] + '</p>');
					
			// 	$('#template-results-table').append('<tr id=\'templateresult'+value['templateID']+'\'><td>'+value['name']+'<\/td></tr>');
				$('#template-results-buttons').append('<li><a href="#" class="ui-button" id=\'templateresult'+value['templateID']+'\'>'+value['name']+'</a></li>');
			});  
			
			// $('.stripes tr:even').addClass("alt");
			
			$( "input:submit, button, .ui-button").button();

						
			}
			
			else {
			//	$("#cust-search-errors").text("Invalid customer ID...").show().delay(5000).fadeOut('slow');
				alert('Problem obtaining content\n\n' + j.msg);
			}
          }
        });  	 
}

	
function loadTemplate(templateID) {
	// Load the template - get the content etc. 
	contentChanged = false;
	
	$.ajaxq('queue', {
          url: 'templates_ajax.php',
//		  async: false, 
          data: 'action=getTemplate&templateID='+templateID,
          dataType: 'json',
          type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
                alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
            },
          success: function (j) {		

			removeTabs();
			$("#add-seo").show();
			$("#add-sitemap").show();
			
			// handle not logged in
			if (j.NotLoggedIn == true) {
					showLogin();
					return false;
			}
			
			if (j.result == true) {
				var tabContent = $( "#tab_content" ),
				  tabTemplate = "<li id='tab-li-#{id}'><a href='#{href}'>#{label}</a></li>",
				  tabCounter = 2;
				var tabs = $("#template_tabs_container");
				
				$('#template-title').html(j.results['name']);
				$('#template-id').val(j.results['templateID']);
				$('#template-name').val(j.results['name']);
				$('#template-created').val(j.results['created']);
				$('#template-lastUpdated').val(j.results['lastUpdated']);
				$('#template-description').val(j.results['templateDescription']);
				$('#template-content').val(j.results['templateContent']);
				
				// template parent - hide if sitesettings
				if(j.results['templateID'] == 1) {
					$('#template-parent-section').hide();
				} else {
					$('#template-parent-section').show();
				}
				if(j.results['useParentTemplate'] == 1) {
					$('#template-parent').prop('checked', true);
				} else {
					$('#template-parent').prop('checked', false);
				}
				
				// Now create a tab for each one
				var lastTab ='';
				
				var tabContent = $( "#tab_content" ),
				  tabTemplate = "<li id='tab-li-#{id}'><a href='#{href}'>#{label}</a></li>",
				  tabCounter = 2;
				var tabs = $("#template_tabs_container");	
				
				if(j.tabs !== null) {
					$.each(j.tabs, function(key, value) {
						
						if(value['tab_name'] == "SEO") {
							$("#add-seo").hide();
						}
						if(value['tab_name'] == "Sitemap") {
							$("#add-sitemap").hide();
						}
						var label = value['tab_name'],
							id = "tab-" + value['tabID'],
							li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ).replace( /#\{id\}/g, value['tabID'] ) ),
							//	tabContentHtml = tabContent.val() || "Tab " + tabCounter + " content.";
							tabContentHtml = "";
							
						  tabs.find( ".ui-tabs-nav" ).append( li );
						  tabs.append( '<div class="tab_content" id="' + id + '"><p>' + tabContentHtml + '</p> \
								<div class="template_fields"></div> \
									<div class="template_buttons"> \
										<a class="button green" name="create-entity-'+value['tabID']+'" id="create-entity-' + value['tabID'] + '"> \
										<span class="icon-add"></span>Create New Entity</a> \
										<a class="button blue" name="create-section-'+value['tabID']+'" id="create-section-' + value['tabID'] + '"> \
										<span class="icon-add"></span>Create New Section</a> \
										<a class="button red" name="delete-tab-'+value['tabID']+'" id="delete-tab-' + value['tabID'] + '"> \
										<span class="icon-delete"></span>Delete tab</a> \
										<a class="button purple" name="moveleft-tab-'+value['tabID']+'" id="moveleft-tab-' + value['tabID'] + '"> \
										<span class="icon-left"></span>Move Tab Left</a> \
										<a class="button purple" name="moveright-tab-'+value['tabID']+'" id="moveright-tab-' + value['tabID'] + '"> \
										<span class="icon-right"></span>Move Tab Right</a> \
									</div> \
								</div>' );
						  tabs.tabs( "refresh" );
						  tabCounter++;

						lastTab = value['tabID']; 
					});
				}
				
				if (j.entities !== null) {
					$.each(j.entities, function(key, value) {
						// add the fields to each of the tabs	
						// section iD - to do think about a nicer way of doing this with validation
						var sectionVal = value['sectionID'];
						if (sectionVal == null ) {
							sectionVal = '';
						}
						var entityType = '';
						
						switch(value['entity_type']) {
							case "1":
								entityType = 'Number';
								break;
							case "2":
								entityType = 'Money';
								break;
							case "3":
								entityType = 'Text'
								break;
							case "4":
								entityType = 'Multi Line Text';
								break;
							case "5":
								entityType = 'Rich Text';
								break;
							case "6":
								entityType = 'Date';
								break;
							case "7":
								entityType = 'Date and time';
								break;
							case "8":
								entityType = 'Checkbox';
								break;								
							case "9":
								entityType = 'Drop Down';
								break;
							case "10":
								entityType = 'Node Picker';
								break;
							case "11":
								entityType = 'Media Picker';
								break;
							default:
								entityType = 'UNKNOWN';
								break;
						}
						
						
						var sectionDropDownHTML = ""; 
						// build up the section HTML - this could be made a lot more efficient by building these up once into an array and just outputting the relevant one.
						if(j.sections !== null) {
							$.each(j.sections, function(secKey, secValue) {
								if(secValue['template_tabID'] == value['tabID']) {
									if(secValue['sectionID'] == sectionVal) {
										sectionDropDownHTML = sectionDropDownHTML + '<option selected value="'+ secValue['sectionID'] +'">' + secValue['sectionID'] + '</option>';
									} else {
										sectionDropDownHTML = sectionDropDownHTML + '<option value="'+ secValue['sectionID'] +'">' + secValue['sectionID'] + '</option>';
									}
									
								}
							});
						}
						
						
						var sectionHTML = '<div class="entity clearfix" data-entityID="'+ value['entityID'] +'"> \
							<div class="entity-info"> \
								<h4 class="entity-title" title="' + value['entityID'] + '">' + value['entity_name'] + ' (' + entityType + ')</h4> \
							</div> \
							<div class="entity-data"> \
								<input type="hidden" id="entity-instance-' + value['entityID'] + '" value="' + value['entityID'] +'"/> \
								<label for="entity-' + value['entityID'] + '-sectionID">Section ID</label> \
								<select id="entity-' + value['entityID'] + '-sectionID"> \
									<option value="">n\a</option> \
									'+ sectionDropDownHTML + '\
								</select> \
								<br/>\
								<label for="entity-title' + value['entityID'] + '">Entity Title</label> \
								<input type="text" id="entity-title-' + value['entityID'] + '" value="' + value['entity_title'] + '"/> \
								<br/> \
								<label for="entity-description' + value['entityID'] + '">Entity Description</label> \
								<textarea rows="3" cols="52" id="entity-description-' + value['entityID'] + '">' + value['entity_description'] + '</textarea> \
							</div> \
							<div class="entity-buttons"> \
								<a href="#" class="button black entityinst_moveup">Move up</a> \
								<a href="#" class="button black entityinst_movedown">Move down</a> \
								<a href="#" class="button red entityinst_delete"><span class="icon-delete"></span>Delete entity</a> \
							</div> \
							</div>';
						
						$("#tab-" + value['tabID'] + " div.template_fields").append(sectionHTML);
							
					}); 
				}
				// select the last tab
				tabs.tabs({ active: curTabId });
				// end of new
			}
			else {
				alert('Problem obtaining template content\n\n' + j.msg);
			}
          }
        });  	 
}


function saveTemplate() {

	// check the same section ID is not across multiple tabs TO DO This will be taken away from the user and be invisible to avoid this issue in the future
	if(!checkSectionIDs()) {
		return false;
	}
	
	// get template content 
	//var entityDetails = $('*[id^=entity-]').serializeArray();
	var templateID = $('#template-id').val();
	var templateName = $('#template-name').val();
	var templateDescription = $('#template-description').val();
	var templateContent = $('#template-content').val();
	var useParentTemplate = 0;
	
	if($('#template-parent').is(':checked')) {
		useParentTemplate = 1;
	}
	// build up an array of entities with details  TO DO order 
	var entityDetails = [];
	$('input[id^=entity-instance-]').each(function(i, obj) {
		// add to the array
		var curID = obj.id.replace('entity-instance-','');
		
		entityDetails.push({
			entityID: curID,
			entityName: obj.id,
			sectionID: $('#entity-' + curID + '-sectionID').val(),
			title:  $('#entity-title-' + curID).val(),
			description: $('#entity-description-' + curID).val(),
			sortOrder: i + 1
		});
	});
	
	
	var action = 'saveTemplate';
	
	$.ajaxq('queue', {
          url: 'templates_ajax.php',
//		  async: false, 
//          data: 'action=saveTemplate&templateID='+templateID+'&templateName='+templateName+'&templateDescription='+templateDescription+'&templateContent='+templateContent,
		  data: { action: action, 
					templateID: templateID,
					templateName: templateName,
					templateDescription: templateDescription,
					templateContent: templateContent,
					entityDetails: entityDetails,
					useParentTemplate: useParentTemplate
					},
          dataType: 'json',
          type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
                alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
            },
          success: function (j) {		 
			// handle not logged in
			if (j.NotLoggedIn == true) {
					showLogin();
					return false;
			}
			
			// !not null
			// if (j.result == true && j.results !== null) {
			if (j.result == true) {
				var n = noty({
					text: 'Template Save Result:\n\n' + j.msg,
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 6000, 
					layout: 'topRight',
					type: 'success'
				});
				contentChanged = false;
			}
			
			else {
			//	$("#cust-search-errors").text("Invalid customer ID...").show().delay(5000).fadeOut('slow');
				alert('Problem storing content\n\n' + j.msg);
			}
          }
        });  	 
}		


function checkSectionIDs() {
	// checks that the same Section ID has not been used accross different tabs
	
	var sectionIDs = [];
	
	$('.entity-sectionID').each(function() {
		var sectionID = $(this).val();
		if (sectionID !== "") {
			var tabID = $(this).parents(".tab_content").attr('id');
		
			var sectionIDarr = [sectionID, tabID];
			var found = false;
			for (i = 0; i < sectionIDs.length; ++i) {
			//	console.log(sectionIDs[i][0] + " - " + sectionIDs[i][1] + " -VS- " + sectionID + " - " + tabID);
				if(sectionIDs[i][0] == sectionID && sectionIDs[i][1] != tabID ) {
					alert("Template NOT saved - sectionID  " + sectionID + " is used on multiple tabs - you MUST use unique section IDs on each tab.");
					return false;
				}
			}
			
			if (!found) {
				sectionIDs.push(sectionIDarr);
			}
		}
	
	});
	return true;
}


function newTemplate() {
	var action = 'newTemplate';
	//  alert(newTemplateName);
	
	$("#new-template-dialog").dialog({
		modal: true,
		buttons: {
			"Create": function() {
				// var newTemplateName = $("#new-template-name").val();
				
				$.ajaxq("queue", {
				  url: 'templates_ajax.php',
//				  async: false, 
				  data: { action: action, 
					templateName: $("#new-template-name").val()
				  },
				  dataType: 'json',
				  type: 'POST',
				  cache: false,
				  error: function(xhr, textStatus, errorThrown){
						alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
					},
				  success: function (j) {		  
					if (j.result == true) {
						$('#new-template-dialog').dialog("close");
						getTemplatesList();
						curTabId = 0;
						loadTemplate(j.newTemplateID);
					}
					else {
						alert('Error creating template\n\n' + j.msg);
						// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
					}
				  }
				});				
			}
		, "Cancel": function() {
			$('#new-template-dialog').dialog("close");
		}
		}
	});	
}		


function newTab() {
	var action = 'newTab';
	//  alert(newTemplateName);
	
	if ($("#template-id").val() == '') {
		alert('You need to select a template first');
		return false;
	}
	
	$("#new-tab-dialog").dialog({
		modal: true,
		buttons: {
			"Create": function() {
				// var newTemplateName = $("#new-template-name").val();
				
				$.ajaxq("queue", {
				  url: 'templates_ajax.php',
//				  async: false, 
				  data: { action: action, 
					templateID: $("#template-id").val(),
					tabName: $("#new-tab-name").val()
				  },
				  dataType: 'json',
				  type: 'POST',
				  cache: false,
				  error: function(xhr, textStatus, errorThrown){
						alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
					},
				  success: function (j) {		  
					if (j.result == true) {
						$('#new-tab-dialog').dialog("close");
						getTemplatesList();
						curTabId += 1;
						loadTemplate($("#template-id").val());
					}
					else {
						alert('Error creating template\n\n' + j.msg);
						// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
					}
				  }
				});				
			}
		, "Cancel": function() {
			$('#new-tab-dialog').dialog("close");
		}
		}
	});	
}		


function deleteTab(tabID) {
	var action = 'deleteTab';
	
	if (contentChanged) {
		if(!window.confirm('You have unsaved changes - these will be lost if you delete a tab without first saving your changes - CONTINUE?')) {
			return false;
		}
	}
	
	$.ajaxq("queue", {
		url: 'templates_ajax.php',
		data: { action: action, 
			tabID: tabID
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
		success: function (j) {		  
			if (j.result != false) {
				curTabId = 0;
				getTemplatesList();
				loadTemplate($("#template-id").val());;
			}
			else {
				alert('Error deleting tab\n\n' + j.msg);
			}
		}
	});		
}		


function updateTabOrder(templateID, sortOrder) {
	var action = 'updateTabOrder';
		
	$.ajaxq("queue", {
		url: 'templates_ajax.php',
		data: { action: action, 
			templateID: templateID,
			sortOrder: sortOrder
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
		success: function (j) {		  
			if (j.result != false) {
				curTabId = 0;
				getTemplatesList();
				loadTemplate($("#template-id").val());;
			}
			else {
				alert('Error updating tabs\n\n' + j.msg);
			}
		}
	});		
}		


function addSEOFields() {
	var action = 'addSEOFields';
	var templateID = 0;
	
	if ($("#template-id").val() == '') {
		alert('You need to select a template first');
		return false;
	} else {
		templateID = $("#template-id").val();
	}
	
	$.ajaxq("queue", {
		url: 'templates_ajax.php',
		data: { action: action, 
			templateID: templateID
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
		success: function (j) {		  
			if (j.result != false) {
				
				getTemplatesList();
				loadTemplate($("#template-id").val());;
			}
			else {
				alert('Error adding SEO fields\n\n' + j.msg);
			}
		}
	});	
}		


function addSitemapFields() {
	var action = 'addSitemapFields';
	var templateID = 0;
	
	if ($("#template-id").val() == '') {
		alert('You need to select a template first');
		return false;
	} else {
		templateID = $("#template-id").val();
	}
	
	$.ajaxq("queue", {
		url: 'templates_ajax.php',
		data: { action: action, 
			templateID: templateID
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
		success: function (j) {		  
			if (j.result != false) {
				
				getTemplatesList();
				loadTemplate($("#template-id").val());;
			}
			else {
				alert('Error adding Sitemap fields\n\n' + j.msg);
			}
		}
	});	
}		


function newEntity(tabID) {
	var action = 'newEntity';

	if (contentChanged) {
		if(!window.confirm('You have unsaved changes - these will be lost if you delete a tab without first saving your changes - CONTINUE?')) {
			return false;
		}
	}
	
	// TO DO - proper validation?
	if ($("#template-id").val() == '') {
		alert('You need to select a template first');
		return false;
	}
	
	$("#new-entity-dialog").dialog({
		modal: true,
		buttons: {
			"Create": function() {
				// var newTemplateName = $("#new-template-name").val();
				
				$.ajaxq("queue", {
				  url: 'templates_ajax.php',
//				  async: false, 
				  data: { action: action, 
					templateID: $("#template-id").val(),
					tabID: tabID,
					entityName: $("#new-entity-name").val(),
					entityType: $("#new-entity-type").val()
				  },
				  dataType: 'json',
				  type: 'POST',
				  cache: false,
				  error: function(xhr, textStatus, errorThrown){
						alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
					},
				  success: function (j) {		  
					if (j.result == true) {
						$('#new-entity-dialog').dialog("close");
						getTemplatesList();
						loadTemplate($("#template-id").val());
					}
					else {
						alert('Error creating template\n\n' + j.msg);
						// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
					}
				  }
				});				
			}
		, "Cancel": function() {
			$('#new-entity-dialog').dialog("close");
		}
		}
	});	
}		


function deleteEntity(entityID) {
	var action = 'deleteEntity';
	
	$.ajaxq("queue", {
		url: 'templates_ajax.php',
		data: { action: action, 
			entityID: entityID
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
		success: function (j) {		  
			if (j.result != false) {
				loadTemplate($("#template-id").val());;
			}
			else {
				alert('Error deleting entity\n\n' + j.msg);
			}
		}
	});		
}	


function newSection(tabID) {
	var action = 'newSection';
		
	// TO DO - proper validation?
	if ($("#template-id").val() == '') {
		alert('You need to select a template first');
		return false;
	}
	
		$.ajaxq("queue", {
			url: 'templates_ajax.php',
			//	async: false, 
			data: { action: action, 
			templateID: $("#template-id").val(),
			tabID: tabID
		  },
		  dataType: 'json',
		  type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
			},
		  success: function (j) {		  
			if (j.result != false) {
				alert("Section added: " + j.result);
				getTemplatesList();
				loadTemplate($("#template-id").val());
			}
			else {
				alert('Error creating section\n\n' + j.msg);
				// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
			}
		  }
		});				
}		


function removeTabs() {
	var tabs = $("#template_tabs_container");
	// all tabs that start tab-li remove the tab header
	tabs.find("[id^=tab-li-]").remove();
	// and the content
 	tabs.find("[id^=tab-]").remove();
	tabs.tabs( "refresh" );
}
		
		
		
// MAIN DOC READY JQUERY
$(document).ready(function() {  
	
	$("nav select").change(function() {
	  window.location = $(this).find("option:selected").val();
	});

	/* make the whole li button clickable */
	$('ul.menu-icons li').bind('click', function() {
		window.location = $(this).find('a').attr('href');
		return false;
	});

	/* stop bubble */
	$('ul.menu-icons li a').bind('click', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
	});
	
	$("#tree-showhide").click(function() {
		//$('#tree-container').toggle();
		if($(this).hasClass('left'))
		{
			$('#templatecontainer').hide();
			$('#templatelist').show( "slide", { direction: "left"  }, 1000 );
			$('#tree-showhide').toggleClass('left');
			$('#tree-showhide-bar').toggleClass('left');
		}
		else 
		{
			$('#templatecontainer').show();
			$('#templatelist').hide( "slide", { direction: "left"  }, 1000 );
			$('#tree-showhide').toggleClass('left');	
			$('#tree-showhide-bar').toggleClass('left');
		}
		
	});

	// look for changes in content to warn user before performing actions that cause a reload
	$('#contentcontainer').on('change keyup keydown', 'input, textarea, select', function (e) {
		contentChanged = true;
	});
	

	// if set then show the help
	if (showHelpOnLoad == true) {
		showHelpDialog();
	}
	
	// TO DO replace with screens
	$("#menu-settings").click(function() {
		alert('The Settings screen is not available in the online demo');
	});
	
	$("#menu-help").click(function() {
		showHelpDialog();
	});
	
	$("#new").click(function() {
		newTemplate();
	});
	
	$("#save").click(function() {
		saveTemplate();
	});
	
	$("#new-tab").click(function() {
		newTab();
	});
	
	$("#add-seo").click(function() {
		if (contentChanged) {
			if(!window.confirm('You have unsaved changes - these will be lost if you delete a tab without first saving your changes - CONTINUE?')) {
				return false;
			}
		}
	
		addSEOFields();
	});
	
	$("#add-sitemap").click(function() {
		if (contentChanged) {
			if(!window.confirm('You have unsaved changes - these will be lost if you delete a tab without first saving your changes - CONTINUE?')) {
				return false;
			}
		}
		addSitemapFields();
	});
		
	// took me ages to realise the obvious - the #template_tabs.. has to be a static element NOT created dynamically - your selector is in the on function. 
	$("#template_tabs_container").on( "click", '[id^="create-entity-"]', function() {
	//	alert( $( this ).attr('id') );
		var tabID = $(this ).attr('id').replace("create-entity-","");
		newEntity(tabID);
	});
	
	$("#template_tabs_container").on( "click", '[id^="create-section-"]', function() {
	//	alert( $( this ).attr('id') );
		var tabID = $(this ).attr('id').replace("create-section-","");
		newSection(tabID);
	});
	
	
	$("#template_tabs_container").on( "click", '[id^="delete-tab-"]', function() {
		var tabID = $(this ).attr('id').replace("delete-tab-","");
		deleteTab(tabID);
	});
	
	$("#template_tabs_container").on( "click", '[id^="moveleft-tab-"]', function() {
		if (contentChanged) {
			if(!window.confirm('You have unsaved changes - these may be lost if you modify settings in the pagepaths area without first saving your changes - CONTINUE?')) {
				return false;
			}
		}
		var tabIDtoMove = $(this ).attr('id').replace("moveleft-tab-","");
		var arrayEleToMove;
		
		var sortOrder = 1;
		var arr = [];
		$('#template_tabs_container .ui-tabs-nav a').each(function() {
			var id = $(this).attr('href').replace('#tab-', '');
			if(id != '#template-standard')
			{
				if(tabIDtoMove == id)
				{
					arrayEleToMove = sortOrder - 1;
				}
	//			console.log("adding " + id + " to element " + sortOrder);
				arr.push(id);
				sortOrder++;
			}	
		});
	//	console.log("Array: " + JSON.stringify(arr));
	//	console.log("Array MOVING : " + arrayEleToMove);
		if(arrayEleToMove != 0) {
			var temp = arr[arrayEleToMove - 1];
			arr[arrayEleToMove -1] = arr[arrayEleToMove];
			arr[arrayEleToMove] = temp;
		//	console.log("Array AFTER sort: " + JSON.stringify(arr));
			updateTabOrder($("#template-id").val(), arr);
		} 
		else
		{
			var n = noty({
					text: 'You can\'t move this tab any further left.\n\n',
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 6000, 
					layout: 'bottom',
					type: 'warning'
				});
		}
		
	});
	

	$("#template_tabs_container").on( "click", '[id^="moveright-tab-"]', function() {
		if (contentChanged) {
			if(!window.confirm('You have unsaved changes - these may be lost if you modify settings in the pagepaths area without first saving your changes - CONTINUE?')) {
				return false;
			}
		}
		var tabIDtoMove = $(this ).attr('id').replace("moveright-tab-","");
		var arrayEleToMove;
		
		var sortOrder = 1;
		var arr = [];
		$('#template_tabs_container .ui-tabs-nav a').each(function() {
			var id = $(this).attr('href').replace('#tab-', '');
			if(id != '#template-standard')
			{
				if(tabIDtoMove == id)
				{
					arrayEleToMove = sortOrder - 1;
				}
	//			console.log("adding " + id + " to element " + sortOrder);
				arr.push(id);
				sortOrder++;
			}	
		});
	//	console.log("Array: " + JSON.stringify(arr));
	//	console.log("Array MOVING : " + arrayEleToMove);
		if(arrayEleToMove != arr.length - 1) {
			var temp = arr[arrayEleToMove + 1];
			arr[arrayEleToMove +1] = arr[arrayEleToMove];
			arr[arrayEleToMove] = temp;
			//	console.log("Array AFTER sort: " + JSON.stringify(arr));
			updateTabOrder($("#template-id").val(), arr);
		}
				else
		{
			var n = noty({
					text: 'You can\'t move this tab any further right.\n\n',
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 6000, 
					layout: 'bottom',
					type: 'warning'
				});
		}
	});
	
	$("#contentcontainer").on('click', '.entityinst_moveup', function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		var curDiv = $(this).closest('.entity');
		var prevDiv = curDiv.prev('.entity');
		if(prevDiv.length !== 0) {
			$(curDiv).insertBefore(prevDiv); 
		} 
		else 
		{
		//	 alert('already at the top');
		}
	});
	
	$("#contentcontainer").on('click', '.entityinst_movedown', function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;	
		var curDiv = $(this).closest('.entity');
		var nextDiv = curDiv.next('.entity');
		if(nextDiv.length !== 0) {
			$(curDiv).insertAfter(nextDiv);
		} 
		else 
		{
		//	 alert('already at the top');
		}
	});
	
	$("#contentcontainer").on('click', '.entityinst_delete', function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		var curDiv = $(this).closest('.entity');
		var entityID = curDiv.data("entityid");

		if(!window.confirm('WARNING - this cannot be undone and any data stored in content nodes with this entity type will be LOST - ARE YOU SURE?')) {
			return false;
		}
		else {
			deleteEntity(entityID);
		}
	});
	
	
	//When page loads...
	$("#template_tabs_container").tabs({ 
		disabled: [],
	});
	
	// store cur tab - count number of preceding tabs - can't hook on click event in tab as rebuild of tabs fires this
	$("#template_tabs_container").on('click', 'ul.tabs li', function() {
		curTabId = $(this).prevAll('ul.tabs li').size();
	});
	
	// Template list - using .on instead of live to bind new ones - note selector is second function param
	$('#template-results-buttons').on('click', 'a', function() {
		if (contentChanged) {
			if(!window.confirm('You have unsaved changes - these may be lost if you change template without first saving your changes - CONTINUE?')) {
				return false;
			}
		}
		var id = $(this).attr('id').replace('templateresult','');
		// load the template data
		curTabId = 0;
		loadTemplate(id);
	});
	
	$( "input:submit, button, .ui-button").button();

	
	getTemplatesList();
	loadTemplate(1);
}); 