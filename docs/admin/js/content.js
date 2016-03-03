/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 /* globals */
var newSectionInstanceID = 1;
var tinyIDs = []; // holds the current tinyMCE editors
var mediaPickerTarget = '';
var curMediaPath = '../media/images/';
var contentChanged = false;
var nodePickerTreeInited = false;
var nodeTarget;

function showLogin() {
	$("#login-dialog").dialog({
		modal: true,
		dialogClass: "no-close",
		closeOnEscape: false,
		buttons: {
			"Log in": function() {
				$.ajax({
				  url: 'login_ajax.php',
				  data: 'action=login&' + $("form#login-form").serialize(),
				  dataType: 'json',
				  type: 'POST',
				  cache: false,
				  error: function(xhr, textStatus, errorThrown){
						alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
					},
				  success: function (j) {		  
					if (j.result == true) {
						$('#login-dialog').dialog("close");
						// reload the selected node (bug where if the tree is clicked when user is logged out we still show the curren
						// node but highlighting has changed
						var nodeID = $('#tree').jstree('get_selected').attr('id');  // TO DO review hack to avoid issues with multiselects. 
						var languageID = $('#language').val();			
						removeTabs();
						getTemplate(nodeID, languageID);
						loadContentNode(nodeID); 
					}
					else {
						var n = noty({
							text: '<b>Login details incorrect - please try again...<\/b>\n\n',
							animation: {
								open: {height: 'toggle'}, 
								close: {height: 'toggle'}, 
								easing: 'swing', 
								speed: 500 
							},
							timeout: 6000, 
							layout: 'bottom',
							type: 'error'
						});
					}
				  }
				});				
			}
		}
	});	
}		
	

function showLoadingDialog() {
	$("#loading-dialog").dialog({
		closeOnEscape: false,
		modal: true,
		draggable: false,
		resizable: false,
		open: function(event, ui) { $(".ui-dialog-titlebar-close").hide();} 
	});
}	
	
	
function hideLoadingDialog() {
	$("#loading-dialog").dialog('close');
}	


function showHelpDialog() {	
	// TO DO - help dialog removed?
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


function showPagePathsDialog() {	
	$("#pagepaths-dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		modal: true,
		// finally realised it wasn't this code playing up - it's because the dialog is being called from a href click
		// this redraws the screen and messes up the positioning. solution is to just return false (or more correctly trap the default event on click)
		//	position: {
		//		my: "center",
		//		at: "top-25%",
		//		of: $("#contentcontainer"),
		//		within: $("#contentcontainer")
		//	},
		resizable: true,
		width: 800,
		height: 650,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
}


function showMediaPickerDialog() {
	getMedia("");
	
	$("#mediapicker-dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		modal: true,
		resizable: true,
		width: 1000,
		height: 800,
		buttons: {
				Close: function() {
					$('#image-upload').hide();
					$('#progress').hide();
					$('.status').hide();
					$('#image-upload-help-text').hide();
					$( this ).dialog( "close" );
				}
		}
	});
}


function showLinkPickerDialog(editor) {
	//editor.insertContent('<atest');
	$("#linkpicker-dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		modal: true,
		resizable: true,
		width: 1000,
		height: 800,
		buttons: {
				Insert: function() {
					var title = $('#linkpicker-title').val() !== '' ? $('#linkpicker-title').val() : $('#linkpicker-title').val();
					var target = $('#linkpicker-target').val() == 'new'  ? '_blank' : '_self';
					editor.insertContent('<a href="' + $('#linkpicker-url').val() + '"' + ' title="' + title + '" target="' + target + '">' + $('#linkpicker-text').val() + '</a>');
					$( this ).dialog( "close" );
				},			
				Cancel: function() {
					$( this ).dialog( "close" );
				}
		}
	});
}


function showLinkPickerNodePickerDialog() {
	// check if we need to load the tree
	if(nodePickerTreeInited == false )
	{
		$('#nodepicker-tree')
		.jstree({
			'contextmenu' : {
				'select_node' : false
			},
			'core' : {
				'data' : {
					'url' : '?operation=get_node',
					'data' : function (node) {
						return { 'id' : node.id };
					}
				},
				'check_callback' : true,
				'themes' : {
					'responsive' : false
				}
			},
			'state' : { 'key' : 'nodepicker'},
			'plugins' : ['state','dnd','contextmenu','wholerow']
		})
		
		// after the state has loaded THEN we bind on the select o/w the state loading bones it.
		.on('state_ready.jstree', function (e, data) {
			nodePickerTreeInited = true;
			
			$('#nodepicker-tree').bind('select_node.jstree', function (e, data) {
				if(data && data.selected && data.selected.length) {		
					// to do this is a hack as when the tree first loads this is fired on select of the first node. TODO read the api doc and see what event I should hook on
					var nodePickedID = data.selected[0];  // TO DO review hack to avoid issues with multiselects. 
					var nodePickedName = data.node.text;
					
					// Get primary path (URL) for nodeID?
					$.ajax({
						url: 'content_ajax.php',
						data: { action: 'getPrimaryPagePath',
							nodeID: nodePickedID
						},
						dataType: 'json',
						type: 'POST',
						cache: false,
						error: function(xhr, textStatus, errorThrown){
							alert("Error - the server appears to be down - please check your connection and try again: " +textStatus);
						},
						success: function (j) {		 
							$('#linkpicker-url').val(j.path);
						} 
					}); 
					
					if($('#linkpicker-text').val() == '')
					{
						$('#linkpicker-text').val(nodePickedName);
					}
					if($('#linkpicker-title').val() == '')
					{
						$('#linkpicker-title').val(nodePickedName);
					}
						
					$("#nodepicker-dialog").dialog('close');
					
					
				}
			})
		})
	}
	else {
		// nothing to do
	}
	
	$("#nodepicker-dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		modal: true,
		resizable: true,
		width: 1000,
		height: 800,
		buttons: {
				Close: function() {
					$( this ).dialog( "close" );
				}
		}
	});
}
	


function getMedia(path) {
	// first empty the folders and files divs
	$('#mediafolders, #mediafiles').empty();
		
	// now add the folders and files 
	$.ajax({
		url: 'content_ajax.php',
		data: { action: 'getFilesAndFolders',
			path: path
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus);
		},
		success: function (j) {		 
			// handle not logged in
			if (typeof(j.NotLoggedIn) != "undefined" && j.NotLoggedIn !== null && j.NotLoggedIn == true){
				showLogin();
				return false;
			}
			if (typeof(j.result) != "undefined" && j.result !== null && j.result == true) {
				curMediaPath = j.folderCurrent;
				$('#folder-path').val(curMediaPath);
				
				var appendHTML = '<ul>';
				if(j.folderup != "") {
					appendHTML += '<li><img src="css/images/folderup.png"><span>' + j.folderup + '</span></div></li>';
				}
				if (typeof(j.folders) != "undefined" && j.folders !== null && j.folders.length > 0) {	
					
					// Create each folder
					$.each(j.folders, function(key, value) {
						appendHTML += ('<li><img src="css/images/folder.png"/><span>' + value + '</span></div></li>');
					}); 
				}
				appendHTML += '</ul>';
				$('#mediafolders').append(appendHTML);
				
				// Create each file
				$.each(j.files, function(key, value) {
					var extn = value.substring(value.lastIndexOf('.') + 1).toLowerCase();
					if(extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
						$('#mediafiles').append('<div class="media-file" data-url="' + value + '"><img src="' + value + '"/></div>');
					}  
					else 
					{
						$('#mediafiles').append('<div class="media-file" data-url="' + value + '"><img src="' + './css/images/document.png' + '"/><p>' + value.replace('../media/', '') + '</p></div>');
					}	
				}); 
			} 
			else 
			{
				alert('No template tabs available\n\n' + j.msg);			
			}
		}
	}); 
}


function createMediaFolder() {
	// get name of folder
	var folderName = $('#create-folder-name').val();
	
	if (folderName == '') {
		alert('You need to provide a folder name');
		return false;
	}
	// create
	$.ajax({
		url: 'content_ajax.php',
		data: { action: 'createFolder',
			folderPath: curMediaPath,
			folderName: folderName
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
		success: function (j) {		 
			// handle not logged in
			if (typeof(j.NotLoggedIn) != "undefined" && j.NotLoggedIn !== null && j.NotLoggedIn == true){
				showLogin();
				return false;
			}
			if (typeof(j.result) != "undefined" && j.result !== null && j.result == true) {
			 	getMedia(curMediaPath);
			} 
			else {
				alert('Error creating new folder \n\n' + j.msg);	
				getMedia(curMediaPath);				
			}
		}
	}); 
}


function showNodePickerDialog() {

	// check if we need to load the tree
	if(nodePickerTreeInited == false )
	{
		$('#nodepicker-tree')
		.jstree({
			'contextmenu' : {
				'select_node' : false
			},
			'core' : {
				'data' : {
					'url' : '?operation=get_node',
					'data' : function (node) {
						return { 'id' : node.id };
					}
				},
				'check_callback' : true,
				'themes' : {
					'responsive' : false
				}
			},
			'state' : { 'key' : 'nodepicker'},
			'plugins' : ['state','dnd','contextmenu','wholerow']
		})
		
		// after the state has loaded THEN we bind on the select o/w the state loading bones it.
		.on('state_ready.jstree', function (e, data) {
			nodePickerTreeInited= true;
			
			$('#nodepicker-tree').bind('select_node.jstree', function (e, data) {
				if(data && data.selected && data.selected.length) {		
					// to do this is a hack as when the tree first loads this is fired on select of the first node. TODO read the api doc and see what event I should hook on
					var nodePickedID = data.selected[0];  // TO DO review hack to avoid issues with multiselects. 
					var nodePickedName = data.node.text;
					var level = $(nodeTarget).data('level');
					var prevNode = $(nodeTarget).prev('div.np-node');
					
					var newNode ='<div class="np-node level-'+level+'" data-level="'+level+'" data-nodeid="' + nodePickedID + '"> \
							<span>' + nodePickedName + '</span> \
							<div class="np-node-buttons"> \
								<a href="#" class="ui-icon ui-icon-arrowthick-1-w np-node-make-adult">&lt; Adult</a>&nbsp;  \
								<a href="#" class="ui-icon ui-icon-arrowthick-1-e np-node-make-child">Child &gt; </a>&nbsp;  \
								<a href="#" class="ui-icon ui-icon-arrowthick-1-n np-node-move-up">Up ^ </a>&nbsp; \
								<a href="#" class="ui-icon ui-icon-arrowthick-1-s np-node-move-down">Down | </a>&nbsp;  \
								<a href="#" class="ui-icon ui-icon-trash np-node-delete">Del</a>&nbsp; \
							</div> \
						</div>';
						
					$("#nodepicker-dialog").dialog('close');
					
					if(prevNode.length !== 0) {
					//	$(newNode).insertAfter(prevNode);
						$(newNode).insertBefore(nodeTarget);
					}
					else 
					{
						$(newNode).insertBefore(nodeTarget);
					}

					resetNpNodeTreeButtons(nodeTarget.closest('.np-node-tree-root'));
					encodeNpNodeTree(nodeTarget); 
				}
			})
		})
	}
	else {
		// nothing to do
	}
	
	$("#nodepicker-dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		modal: true,
		resizable: true,
		width: 1000,
		height: 800,
		buttons: {
				Close: function() {
					$( this ).dialog( "close" );
				}
		}
	});
}


function getTemplatesList() {
	var action = 'getTemplatesList';
	
	$.ajax({
		url: 'content_ajax.php',
		data: { 
			action: action
		},
		dataType: 'json',
		type: 'POST',
		cache: false,
		error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
		success: function (j) {		  
			if (j.result == true) {
				$('#select-template-ID').empty();
				// now add all templates	
				$.each(j.results, function(key, value) {
					$('#select-template-ID').append($('<option>', { 
						value: value.templateID,
						text : value.name 
					}));
				});  
			}
			else {
				alert('Error getting template list\n\n' + j.msg);
			}
		}
	});				
}


function showSelectTemplateDialog(nodeID) {	
	var languageID = $('#language').val();
	$("#select-template-dialog").dialog({
		closeOnEscape: true,
		modal: true,
		draggable: true,
		resizable: true,
		width: 800,
		height: 500,
		buttons: {
			"Create": function() {
				var action = 'setTemplate';
				$.ajax({
					url: 'content_ajax.php',
					data: { action: action, 
						nodeID: nodeID,
						templateID: $("#select-template-ID").val(),
						languageID: languageID
					},
					dataType: 'json',
					type: 'POST',
					cache: false,
					error: function(xhr, textStatus, errorThrown){
							alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
					},
					success: function (j) {		  
						if (j.result == true) {
							$('#select-template-dialog').dialog("close");
							// now reload the node to get fields
							removeTabs();
							getTemplate(nodeID, languageID);
							loadContentNode(nodeID);
						}
						else {
							alert('Error creating template\n\n' + j.msg);
						}
					}
				});				
			}
		}
	});
}


function getTemplate(nodeID, languageID) {
	// we load the template and the content separately in case we've changed the entity's content type (e.g. there is unmapped data)
	// TO DO - create a "unused" tab and hide and show if this data exists. 
	$.ajaxq('queue', {
          url: 'content_ajax.php',
		  data: { action: 'getTemplate', 
					nodeID: nodeID,
					languageID: $('#language').val()
					},
          dataType: 'json',
          type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
                alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
            },
          success: function (j) {		 
			// handle not logged in
			if (typeof(j.NotLoggedIn) != "undefined" && j.NotLoggedIn !== null && j.NotLoggedIn == true){
				showLogin();
				return false;
			}
			
			
			if (typeof(j.result) != "undefined" && j.result !== null && j.result == true) {
				// 1. create the tab
				var tabContent = $( "#tab_content" ),
				  tabTemplate = "<li id='tab-li-#{id}'><a href='#{href}'>#{label}</a></li>";
				var tabs = $("#content_tabs_container");
				
				
				$.each(j.tabs, function(key, value) {
					if (!$('#tab-'+value['tabID']).length)
					{
						var label = value['tab_name'],
							id = "tab-" + value['tabID'],
							li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ).replace( /#\{id\}/g, value['tabID'] ) ),
							tabContentHtml = tabContent.val() || "";
					 
						  tabs.find( ".ui-tabs-nav" ).append( li );
						  tabs.append( "<div class='tab_content' id='" + id + "'><p>" + tabContentHtml + "</p></div>" );
						  tabs.tabs( "refresh" );
					}
				});
				
				// 2. create the sections if exist and there are entities within under each tab
				if (typeof(j.sections) != "undefined" && j.sections !== null) {
					// 2a create the section
					$.each(j.sections, function(key, value) {
						$sectionHasEntities = false;
						// check the entities to see if this section contains anything o/w we don't create
						$.each(j.results, function(entKey, entValue) {
							if(entValue['sectionID'] == value['sectionID'])
							{
								$sectionHasEntities = true;
							}
						});
						
						if($sectionHasEntities && value['sectionID'] !== null) {
							// first check if the section exists - if not create it
							if (!$('#section_'+value['sectionID']).length) {
								$("#tab-"+value['tabID']).append('<div class="section" id="section_' + value['sectionID'] + '">Section: ' + value['sectionID'] + '<br/><div class="section-instances"></div><div class="sectioninst-create"><a href="#" class="button green newSectionInst" id="sectioninst-create_' + value['sectionID'] + '">Create a new section item' + '</a></div>');
							}
						}
					});
					
					
					// 2b. create the section instances under each section (creating the section first if it doesn't already exist)
					if (typeof(j.sectionInstances) != "undefined" && j.sectionInstances !== null) {
						$.each(j.sectionInstances, function(key, value) {				
							
							if (!$('#sectioninst_'+value['sectionInstanceID']).length) {
								var sectionHTML = '<div class="sectioninst" id="sectioninst_' + value['sectionInstanceID'] + '"> \
										<input id="sectioninst-order_' + value['sectionInstanceID'] + '" type="hidden" value="' + (key + 1) + '"/> \
										<div class="sectioninst_fields sectioninst_fields_peek"></div>  \
										<div class="section-buttons"> \
											<a href="#" class="button green sectioninst_show">Show more..</a> \
											<a href="#" class="button black sectioninst_moveup">Move up</a> \
											<a href="#" class="button black sectioninst_movedown">Move down</a> \
											<a href="#" class="button red sectioninst_delete">Delete</a> \
										</div></div>';
								$("#section_"+value['sectionID'] + " div.section-instances").append(sectionHTML)
							}
						});
					}
				}
				
				
				// 3. Create each entity for the template in the relevant tab & sectioninstance (optional)
				if (typeof(j.results) != "undefined" && j.results !== null) {
					$.each(j.results, function(key, value) {
						// now add the field - either to the end of each Section Instance or directly to the tab
						// if we have a sectionID then it's part of a section so we need to consider add to each instance
						if (value['sectionID'] !== null) {
							$('#section_' + value['sectionID'] + ' div.sectioninst' ).each(function(i, elm) {
							//	console.log($(this).attr("id"));
								var sectionInstanceID = elm.id.replace('sectioninst_', '');
								var appendHTML = createEntityHTML(value['entity_type'], value['entityID'], value['entity_name'], value['entity_title'], value['entity_description'], sectionInstanceID);
								$(this).children('div.sectioninst_fields').append(appendHTML);
							});

						}
						else {
							var appendHTML = createEntityHTML(value['entity_type'], value['entityID'], value['entity_name'], value['entity_title'], value['entity_description'], null);
							$('#tab-'+value['tabID']).append(appendHTML);
						}
					}); 
				}
			}
			else {
				console.log('No template info available\n\n' + j.msg);
				// New node - show the select template dialog
				getTemplatesList();
				showSelectTemplateDialog(nodeID);				
			}
        }
    });  	 
}


function createEntityHTML(entityType, entityID, entity_name, entity_title, entity_description, sectionInstID) {
	var appendHTML ='';
	
	if (sectionInstID != null) {
		entityID = 'entitysecinst_' + entityID + '_' + sectionInstID;
		entityName = 'entitysecinst_' + entityID + '_' + sectionInstID;
	}
	else {
		entityID = 'entity_' + entityID;
		entityName = entity_name;
	}
	
	switch(entityType) {
		case "1":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title +'</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><input type="number" data-entityName="' + entityName + '" class="entity-number" id="' + entityID + '" name="' + entityID + '" /></div>';
			break;
		case "4":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><textarea class="entity-longtext" rows="4" cols="52" id="' + entityID + '" name="' + entityID + '"></textarea></div>';
			break;
		case "5":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><textarea class="entity-richtext" rows="4" cols="52" id="' + entityID + '" name="' + entityID + '"></textarea></div>';
			break;
		case "6":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><input class="entity-date" type="text" id="' + entityID + '" name="' + entityID + '" /></div>';
			break;
		case "7":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><input class="entity-datetime" type="text" id="' + entityID + '" name="' + entityID + '" /></div>';
			break;
		case "8":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><input id="' + entityID + '_checkbox" class="entity-checkbox" type="checkbox"/><input type="text" class="entity-bool visuallyhidden" id="' + entityID + '_bool" name="' + entityID + '"/></div>';
			break;
		case "10":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><input class="entity-nodepicker" type="text" id="' + entityID + '" name="' + entityID + '" /> \
				</div>';
			break;
		case "11":
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><input class="entity-shorttext" type="text" id="' + entityID + '" name="' + entityID + '" /> \
				<a href="#" class="button purple content-mediapicker" >Choose </a>\
				</div>';
			break;
		default:
				appendHTML = '<div class="entity-info"><h4 class="entity-title" title="'+ entityName + '">' + entity_title + '</h4><p class="entity-description">' + entity_description + '</p></div><div class="entity-data"><input class="entity-shorttext" type="text" id="' + entityID + '" name="' + entityID + '" /></div>';
			break;
	}
	
	appendHTML = '<div class="entity clearfix">' + appendHTML + '</div>';
	return appendHTML
}


function loadContentNode(nodeID) {
	contentChanged = false;
	// Otherwise you can save over content for other nodes.
	$("#content-id").val('');
				
	$.ajaxq('queue', {
		  url: 'content_ajax.php',
		  data: { action: 'loadContent', 
					nodeID: nodeID,
					languageID: $('#language').val()
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
			if (j.result == true) {
				// TO DO check if content is available?
				if (typeof(j.results['contentID']) != "undefined" && j.results['contentID'] !== null) {
					// set the title of the content node to be ID
					$("#content-node-title").html(j.results['contentID']);
				}
				else {				
					alert("No content found!");
					return false;
				}
				
				// TO DO - check that the relevant field exists for the data - if it doesn't then call a unused data function
				// this will check for an unused data 
				var entityID = 1;
				
				//	For each entity set the value
				if(typeof j.results['entities'] != 'undefined')			{	
					$.each(j.results['entities'], function(key, value) {
						// console.log('KEY: ' + key + ' VALUE:' + value);
						// TO DO - if a value is set to null in the content table (e.g. created date) then this falls over in a big steaming heap. TO FIX
						if(value['entityID'] != null) {
							if (value['value'] == null) 
							{
							//	alert('null found');
							}
							
							if (typeof(value['sectionInstanceID']) != "undefined" && value['sectionInstanceID'] !== null){
							//	console.log("#entitysecinst_" + value['entityID']+"-" + value['sectionInstanceID']);
								$("#entitysecinst_" + value['entityID']+"_" + value['sectionInstanceID']).val(value['value']);
								
								// check if we need to set the checkbox
								if(value['entity_type'] == 8) {
									console.log("SECTION: #entity_" + value['entityID'] + '_bool');
									// we also need to set the hidden text box to "true" or "false"
									if(value['value'] == '1') {
										$("#entitysecinst_" + value['entityID'] + "_" + value['sectionInstanceID'] + '_checkbox').prop('checked', 'checked');
										$("#entitysecinst_" + value['entityID'] + "_" + value['sectionInstanceID'] + '_bool').val("true");
									} 
									else {
										$("#entitysecinst_" + value['entityID'] + "_" + value['sectionInstanceID'] + '_bool').val("false");
									}
								}
							}
							else {
								$("#entity_" + value['entityID']).val(value['value']);
								
								// check if we need to set the checkbox
								if(value['entity_type'] == 8) {
									console.log("#entity_" + value['entityID'] + '_bool');
									if(value['value'] == '1') {
										$("#entity_" + value['entityID'] + '_checkbox').prop('checked', true);
										// we also need to set the hidden text box to "true"
										$("#entity_" + value['entityID'] + '_bool').val("true");
									} else {
										$("#entity_" + value['entityID'] + '_bool').val("false");
									}
								}
							}
						}
					}); 
				}
				
				// Generic node standard info
				$("#node-id").val(j.results['nodeID']);
				$("#content-id").val(j.results['contentID']);
				$("#template-id").val(j.results['templateID']);
				$("#content-created").val(j.results['created']);
				$("#content-createdBy").val(j.results['createdBy']);
				$("#content-lastUpdated").val(j.results['lastUpdated']);
				$("#content-lastUpdatedBy").val(j.results['lastUpdatedBy']);
				$("#content-notes").val(j.results['notes']);
				$("#content-nocache").val(j.results['noCache']);
				if(j.results['noCache'] == 1)
				{
					$("#content-nocache_checkbox").prop('checked', true);
				}
				
				
				// for the site setings root node we just hide the page path container
				if(j.results['nodeID'] == 1) 
				{
					$('#page-path-container').hide();
				}
				else 
				{
					// Page paths - there can be more than one
					var pagepaths = $("#content-pagepaths");
					$('#page-path-container').show();
					pagepaths.empty();
					$('#page-path').html('');
					$('#page-path-aliases').html('');
					
					if(j.results['pagepath'].length > 0) {
						$.each(j.results['pagepath'], function(key, value) {
							// alert(value.path);
							// if it's the primary populate that field 

							var pathType = '';
								switch (value['type']) {
									case '0':
										pathType = 'Primary';
										break;
									case '1':
										pathType = 'Alias';
										break;
									case '2':
										pathType = '301 Redirect';
										break;
									case '3':
										pathType = '302 Redirect';
										break;
								}
								
							if(value['type'] == '0') {
								$("#content-primarypagepath").val(value['path']);
								$("#content-primarypagepath-none").hide();
								// TO DO a total hack if there is no page path I put out a 0 to avoid the annoying js error of failure in absence... to fix

								if(key!='0') {
									$("#content-primarypagepathID").val(key);
								}
								if (value['path'] == '') 
								{
									// homepage
									$("#page-path").append('<a href="\\" target="_blank">Default homepage</a>');
								} else {
									$("#page-path").append('<a href="\\' + value['path'] + '" target="_blank">' + value['path'] + '</a>');
								}
							}
							else {
								// add to the dialog
								pagepaths.append('<li id="content-pagepath-' + key + '" name="content-pagepath-' + key + '"><a href="/' + value['path'] + '">' + value['path'] + '</a> - ' + pathType + '<a href="#" class="button red content-pagepath-delete" data-id="' + key + '">Delete </a></li>');
								// and the overview
								$('#page-path-aliases').append('<li>' + value['path'] + ' type: ' + pathType + '</li>');
							}
						});
					} 
					else 
					{
						$("#content-primarypagepath").val('');
						$("#content-primarypagepath-none").show();
						$("#page-path").append('<h4>Your page doesn\'t have a Primary Page path so cannot be seen by web visitors yet</h4><p>Set a page path (e.g. a web address) using the "Change" button below.</p>');
						
						// Get a suggested value to make it easier for editors.
						getSuggestedPrimaryPagePath();
					}
				}
			}
			else 
			{
				console.log('No content exists yet\n\n' + j.msg);
				$("#node-id").val(nodeID);
			}
        }
    }); 
}


function removeTabs() {
	var tabs = $("#content_tabs_container");
	
	// before we start kill TinyMCE
	destroyTinyMCE();
	// all tabs that start tab-li remove the tab header
	tabs.find("[id^=tab-li-]").remove();
	// and the content
 	tabs.find("[id^=tab-]").remove();
	tabs.tabs( "refresh" );
}
		

function saveContent() {
	// saves the content in the tabs to the node as a new version. 
	// TO DO - separate or add a variable for save and publish
	// get content from all fields starting with entity
	//var entityContent = $('*[id^=entity_]').serialize();  - done in contentData below

	var nodeID = $('#node-id').val(); 
	var contentID = $('#content-id').val();
	var languageID = $('#language').val();
	var notes = $('#content-notes').val();
	var noCache = $('#content-nocache').val();
	
	// trigger save on any tinymce 
	tinyMCE.triggerSave();
	
	// check if it's a new node and if a primary path is set (o/w warn the user it's not visible until you do)
	var pagePathCount = $('#page-path').children('a').size();
	
	if(pagePathCount == 0 && ($('#content-created').val() == $('#content-lastUpdated').val()))
	{
		var notifyNoPagePath = noty({
					text: 'Warning\n\n This content will not be viewable as a webpage unless a page path is set',
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 6000, 
					layout: 'topRight',
					type: 'alert'
				});
	}
	// OLD - get the form data - you can just add vars to the data but to serialize a whole form you need to do this below and push the params that aren't in the form. 
	// I was using serializeArray() and then .push to add the action etc but this is better?

	// create a container for the content Data
	var contentData = {};

	// build up a list of section instances (with their order and parent section)
	var sectionInstances = [];
	$('div[id^=sectioninst_]').each(function(i, obj) {
		// add to the array
		sectionInstances.push({
			name: obj.id,
			sectionID: $(obj).parents("div.section").attr("id"),
			sortOrder: i
		});
	
	});
	// add to array
	contentData['sectionInstances'] = sectionInstances;
	
	// serialize each entity field and add to the array 
	contentData['entityData'] = $('*[id^=entity_]').serializeArray();
	// and now each instance of a section has an entity - store this
	contentData['entitySectionContent'] = $('*[id^=entitysecinst_]').serializeArray();

	$.ajaxq('queue', {
        url: 'content_ajax.php',
		data: { action: 'saveContent', 
			nodeID: nodeID,
			contentID: contentID,
			languageID: languageID,
			notes: notes,
			noCache: noCache,
			contentData: JSON.stringify(contentData)   
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
					text: 'Content Save Result:\n\n' + j.msg,
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 3000, 
					layout: 'bottom',
					type: 'success'
				});
			}
			
			else {
			//	alert('Problem storing content\n\n' + j.msg);
				var n = noty({
					text: 'Problem storing content\n\n' + j.msg,
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 3000, 
					layout: 'bottom',
					type: 'error'
				});
			}
          }
        });  	 

}		


function addNewPagePath() {
	// TO DO - this will need to find the highest one and create with a dynamic id
	var action = 'addPagePath';
	var nodeID = $('#node-id').val();
	var pathType = $('#content-primarypagepath-new-type').val();
	var path = clean_path($("#content-primarypagepath-new").val());
	// set the cleaned path back
	$("#content-primarypagepath-new").val(path);
	var pathTypeTxt = $('#content-primarypagepath-new-type option:selected').text();
	
	path = path.trim().toLowerCase();
	
	if (path == '')
	{
		alert ("Path cannot be blank");
		return false;
	}
	
	$.ajaxq("queue", {
		  url: 'content_ajax.php',
//		 async: false, 
		  data: { action: action, 
			nodeID: nodeID,
			type: pathType,
			path: path
		  },
		  dataType: 'json',
		  type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
			},
		  success: function (j) {		  
			if (j.result !== false) {
							
				// add to the dialog
				$("#content-pagepaths").append('<li id="content-pagepath-"' + j.result + ' name="content-pagepath-' + j.result + '"><a href="/' + path + '">' + path + '</a> - ' + pathTypeTxt + '<a href="#" class="button red content-pagepath-delete" data-id="' + j.result.trim() + '">Delete </a></li>');
				
				// and the overview
				$('#page-path-aliases').append('<li>' + path + ' type: ' + pathTypeTxt + '</li>');
				
				
			}
			else {
				alert('Error adding page path\n\n' + j.msg);
				// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
			}
		  }
		});		

	$("#content-primarypagepath-new").val('')
}


function deletePagePath(ID) {
	var action = 'deletePagePath';
	
	$.ajaxq("queue", {
		  url: 'content_ajax.php',
//		 async: false, 
		  data: { action: action, 
			pathID: ID
		  },
		  dataType: 'json',
		  type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
			},
		  success: function (j) {		  
			if (j.result !== false) {
				return true;
			}
			else {
				alert('Error deleting page path\n\n' + j.msg);
				// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
			}
		  }
		});		
	return true;
}


function deleteNode(nodeID) {
	// on delete of node.
	var action = 'deleteNode';
	
	$.ajaxq("queue", {
		  url: 'content_ajax.php',
		  data: { action: action, 
			nodeID: nodeID
		  },
		  dataType: 'json',
		  type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
			},
		  success: function (j) {		  
			if (j.result !== false) {
				return true;
			}
			else {
				alert('Error deleting page\n\n' + j.msg);
			}
		  }
		});		
	return true;
}


function copyNodeTest() {
	
	// TO DO - this will need to find the highest one and create with a dynamic id
	var action = 'copyNodeTest';
	
	$.ajaxq("queue", {
		  url: 'content_ajax.php',
//		 async: false, 
		  data: { action: action
		  },
		  dataType: 'json',
		  type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
			},
		  success: function (j) {		  
			if (j.result == true) {
				// TO DO - reload the page?
			}
			else {
				// TO DO deal with duplicate warnings
				alert('Error creating test node\n\n' + j.msg);
				// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
			}
		  }
		});			
		return true;
}	


function updatePrimaryPagePath() {
	// TO DO - this will need to find the highest one and create with a dynamic id
	var action = 'updatePrimaryPagePath';
	var nodeID = $('#node-id').val();
	var path = clean_path($("#content-primarypagepath").val());
	// set the cleaned path back
	$("#content-primarypagepath").val(path);
	var languageID = $('#language').val();

	$.ajaxq("queue", {
		  url: 'content_ajax.php',
//		 async: false, 
		  data: { action: action, 
			nodeID: nodeID,
			path: path
		  },
		  dataType: 'json',
		  type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
			},
		  success: function (j) {		  
			if (j.result == true) {
				var notifyUpdatePrimaryPagePath = noty({
					text: 'Success\n\n The primary page path has been set',
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 6000, 
					layout: 'bottom',
					type: 'success'
				});
				// now reload the node to get fields - TO DO hacky
				var languageID = $('#language').val();
				removeTabs();
				getTemplate(nodeID, languageID);
				loadContentNode(nodeID);
			}
			else {
				// TO DO deal with duplicate warnings
				alert('Error updating primary page path\n\n' + j.msg);
				// $("#login-errors").html("<b>Login details incorrect - please try again...<\/b>").show().delay(5000).fadeOut('slow');
			}
		  }
		});			
		return true;
}	


function getSuggestedPrimaryPagePath() {
	var action = 'getSuggestedPrimaryPagePath';
	var nodeID = $('#node-id').val();
	var nodeName = $('.jstree-clicked').text();
	var languageID = $('#language').val();

	$.ajaxq("queue", {
		  url: 'content_ajax.php',
		  data: { action: action, 
			nodeID: nodeID,
			nodeName: nodeName
		  },
		  dataType: 'json',
		  type: 'POST',
		  cache: false,
		  error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
			},
		  success: function (j) {		  
			if (j.result == true) {
				// now reload the node to get fields - TO DO hacky
				$("#content-primarypagepath").val(j.path);
			}
			else {
				var notifyGetSuggestedPrimaryPagePath = noty({
					text: 'ERROR:\n\n The suggested primary page path cannot be set',
					animation: {
						open: {height: 'toggle'}, 
						close: {height: 'toggle'}, 
						easing: 'swing', 
						speed: 500 
					},
					timeout: 6000, 
					layout: 'bottom',
					type: 'error'
				});
			}
		  }
		});			
		return true;
}	


function clean_path(path) {
	var start = 0;
	var end = path.length;
	
	path = path.replace(/ /g,"-");
	if (path.length > 0) {
		if (path[0] == "/") {
			start = 1;
		}
		if (path[path.length - 1] == "/") {
			end = path.length -1;
		}
	}
	return path.substring(start, end);
}
	
	
function clearContentNodePanel() {
	$("#content-node-title").html('');
	
	// clear form - TO DO better way of doing this
	$("#node-id").val('');
	$("#content-id").val('');
	$("#content-guid").val('');
	$("#content-lastname").val('');
	$("#content-lastUpdated").val('');
	$("#content-created").val('');
	// $('#content-items-table tr:gt(0)').remove();
}	

		
function createNewSectionInstance(sectionID) {
	// this is a bit hacky.
	sectionInstanceID = newSectionInstanceID * -1; // negative to avoid clashes
	
	var sortOrder = $("#section_" + sectionID + " .sectioninst").length + 1;
										
	var sectionHTML = '<div class="sectioninst sectioninst_fields_peek" id="sectioninst_' + sectionInstanceID + '"> \
										<input id="sectioninst-order_' + sectionInstanceID + '" type="hidden" value="' + sortOrder + '"/> \
										<div class="sectioninst_fields"></div>  \
										<div class="section-buttons"> \
										<a href="#" class="button green sectioninst_show">Show more..</a> \
										<a href="#" class="button black sectioninst_moveup">Move up</a> \
										<a href="#" class="button black sectioninst_movedown">Move down</a> \
										<a href="#" class="button red sectioninst_delete">Delete</a> \
										</div></div>';

	// destroyTinyMCE();
	
	$("#section_"+sectionID + " div.section-instances").append(sectionHTML);
	
	// Now get the template and loop through all entities with a matching section ID and create 'em
	
	$.ajaxq('queue', {
	  url: 'content_ajax.php',
//	  async: false, 
	  data: { action: 'getTemplate', 
				nodeID: $('#node-id').val(),
				languageID: $('#language').val()
				},
	  dataType: 'json',
	  type: 'POST',
	  cache: false,
	  error: function(xhr, textStatus, errorThrown){
			alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
		},
	  success: function (j) {		 
		// handle not logged in
		if (typeof(j.NotLoggedIn) != "undefined" && j.NotLoggedIn !== null && j.NotLoggedIn == true){
			showLogin();
			return false;
		}
		
		
		if (typeof(j.result) != "undefined" && j.result !== null && j.result == true) {
						
			// 4. Create each entity for the template in the relevant tab & sectioninstance (optional)
			$.each(j.results, function(key, value) {
				// add the field
				// now add the field - either to the end of each Section Instance or directly to the tab
				
				// if we have a sectionID  and it matches add 
				if (value['sectionID'] !== null && value['sectionID'] == sectionID ) {
					//$('#section_' + value['sectionID'] + ' div.sectioninst' ).each(function(i, elm) {
						var appendHTML = createEntityHTML(value['entity_type'], value['entityID'], value['entity_name'], value['entity_title'], value['entity_description'], sectionInstanceID);
						$('#sectioninst_' + sectionInstanceID).children('div.sectioninst_fields').append(appendHTML);
				//	});

				}
			}); 
			// increment the global in case user creates multiple
			newSectionInstanceID++;
			
			//	setupTinyMCE();
			setupTinyMCE($('#sectioninst_' + sectionInstanceID).children('.sectioninst_fields').children('.entity').children('.entity-data'));
			setupNodePickers($('#sectioninst_' + sectionInstanceID).children('.sectioninst_fields').children('.entity').children('.entity-data'));
			
			$('html, body').animate({scrollTop:$('#sectioninst_' + sectionInstanceID).offset().top - 100}, 'slow');
		} 
		else {
			alert('No template tabs available\n\n' + j.msg);			
		}
	  }
	});  	 
}		


function setupTinyMCE(selector) {
	$(selector).children(".entity-richtext").each(function(){ 
		var height = 250;
		// if a section then smaller
		if ($(this).attr('id').indexOf("entitysecinst") == 0) {
			height = 200;
		}
		var entityID = $(this).attr('id');
		// add to array for destroy later
		tinyIDs.push(entityID); 
		var selectorStr = '#' + entityID;
		tinymce.init({
			height : height,
			menubar: false,
			relative_urls : false,
			selector   : selectorStr,
			plugins: "link,code,paste,linkpicker",
			paste_as_text: true,
			paste_remove_styles: true,
			toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | code | removeformat | linkpicker"
		});
	});
	var currentdate = new Date();
	$( ".entity-datetime" ).datetimepicker({
		dateFormat: 'yy-mm-dd' 
	});
	$( ".entity-date" ).datepicker();
	//$( ".entity-date" ).datetimepicker();
}


function destroyTinyMCE() {
	$.each(tinyIDs, function(key, value) {
		tinymce.remove('#' + value);
	});
	// empty the array
	tinyIDs.length = 0;
}


function setupNodePickers(selector) {
	$(selector).children(".entity-nodepicker").each(function(){ 
		var entityContainer = $(this).parent();
		
		if(entityContainer.children('.np-node-tree').length == 0)
		{
			var treeHtml = '<div class="np-node-tree"> \
				<h4>Select nodes:</h4> \
				<div class="np-node-tree-root"> \
				</div> ';
				treeHtml += '</div>';
			
			// add the empty tree
			$(treeHtml).insertBefore(this);
			var treeRoot = entityContainer.find('div.np-node-tree-root').first();
			var treeDataField = $(this);
			var treeDataJSObj;
			try {
				treeDataJSObj=JSON.parse(treeDataField.val());	
			}
			catch(e){
				// alert(e); //error in the JSON - probably an empty string?
			}
			
			treeRoot.html('');
			var treeHtml = '';
			
			$(treeDataJSObj).each(function(nodeKey, nodeValue) {
				treeHtml += decodeNpNodeData(nodeValue, 1);
			});
			
			// add the last add node button
			treeHtml += '<div class="np-node-button level-1" data-level="1"><a href="#" class="np-node-make-node"><span class="ui-icon ui-icon-plusthick"></span>Add Node</a></div>';
				
			treeRoot.html(treeHtml);
			resetNpNodeTreeButtons(treeRoot);
		}
	});
}	


function decodeNpNodeData(curNode, level) 
{
	var nodeHtml = "";
	
	// first get the data for the node itself.
	nodeHtml = "<h3>" + curNode.nodeID + ": " + curNode.name + " level: " + level + "</h3>";
	nodeHtml = '<div class="np-node level-' + level + '" data-level="' + level + '" data-nodeid="' + curNode.nodeID + '"> \
						<span>' + curNode.name + '</span> \
						<div class="np-node-buttons"> \
							<a href="#" class="ui-icon ui-icon-arrowthick-1-w np-node-make-adult">&lt; Adult</a>&nbsp;  \
							<a href="#" class="ui-icon ui-icon-arrowthick-1-e np-node-make-child">Child &gt; </a>&nbsp;  \
							<a href="#" class="ui-icon ui-icon-arrowthick-1-n np-node-move-up">Up ^ </a>&nbsp; \
							<a href="#" class="ui-icon ui-icon-arrowthick-1-s np-node-move-down">Down | </a>&nbsp;  \
							<a href="#" class="ui-icon ui-icon-trash np-node-delete">Del</a>&nbsp; \
						</div>\
					</div>';
	
	// then check for children
	if(curNode.children.length > 0) {
		$(curNode.children).each(function () {
			nodeHtml += decodeNpNodeData(this, level + 1);
		});	
		nodeHtml += '<div class="np-node-button level-' + (level + 1) + '" data-level="' + (level + 1) + '"><a href="#" class="np-node-make-node"><span class="ui-icon ui-icon-plusthick"></span>Add Node</a></div>';
	}
	
	return nodeHtml;
}
	
		
function resetNpNodeTreeButtons(treeRoot)
{
	// removes all disabled classes and then sets them back on the relevant one
	
	// remove
	treeRoot.find('div a.ui-state-disabled').removeClass('ui-state-disabled');
	
	// traverse tree and disable buttons
	treeRoot.find('div.np-node').each(function () {
		console.log($(this).children('span').html());
		// if we're at level one no adult
		var level = $(this).data('level');
		if(level == 1) {
			$(this).find('a.np-node-make-adult').first().addClass('ui-state-disabled');
		}
		
		var prevNode = $(this).prev('.np-node');
		// not quite got the logic of this right yet
	/*	if(prevNode.data('level') !== level)
		{
			$(this).find('a.np-node-make-child').first().addClass('ui-state-disabled');
		} */
		if((prevNode.length > 0 && level == 1) || (prevNode.data('level') == level))	
		{
			// enabled
		}
		else {
			$(this).find('a.np-node-make-child').first().addClass('ui-state-disabled');
		}
				
		// check what comes first a node at the same level or one above
		var prevLevel = level -1;
		var prevNodeAtLevelOrAbove = $(this).prevAll('.np-node.level-'+level + ', .np-node.level-'+prevLevel).first();
		if(prevNodeAtLevelOrAbove.length == 0 || prevNodeAtLevelOrAbove.data('level') != level)
		{
			$(this).find('a.np-node-move-up').first().addClass('ui-state-disabled');
		}
		
		// down is easy - if the add a node button is next then disable
		var nextNodeAtLevel = $(this).nextAll('.level-'+level).first();
		if(nextNodeAtLevel.hasClass('np-node-button'))
		{
			$(this).find('a.np-node-move-down').first().addClass('ui-state-disabled');
		}
	});
}


function encodeNpNodeTree(curNode) {
		var treeRoot = $(curNode).closest('div.np-node-tree-root');
		var treeContainer = $(curNode).closest('div.np-node-tree');
		var treeDataField = treeContainer.next('input.entity-nodepicker').first();
		var treeDataJSObj = [];	
		
		// traverse the tree and build up the js object.
		// we create the obj with both the node id and the name but the name is only for display
		// cms only stores the node ids as the names may change
		var curNode = {nodeID:"1234", name:"About Us", children:[]};
		
		var nodeIndex = 0;
		treeRoot.find('div.np-node.level-1').each(function () {
			console.log(nodeIndex + ' - ' + $(this).children('span').html());
			treeDataJSObj[nodeIndex] = encodeNpNodeData(this);
			nodeIndex++;
		});
		
		var dataJSON = JSON.stringify(treeDataJSObj);
		treeDataField.val(dataJSON); 
}


function encodeNpNodeData(curNode)
{
	var curNodeData = {nodeID:"", name:"", children:[]};
	// returns the jsobject for the node
	curNodeData.name = $(curNode).children('span').first().html();
	curNodeData.nodeID = $(curNode).data('nodeid');
	
	// console.log(curNodeData.nodeID + " : " + curNodeData.name);
	// check for children
	var level = $(curNode).data('level');
	var childLevel = level + 1;
	var childNodes = $(curNode).nextUntil('div.np-node.level-' + level + ', div.np-node-button.level-'+level).filter('div.np-node.level-' + childLevel);
	
	// foreach following div up to a div with the next lowest level or a add node button (end of children)
	if(childNodes.length !== 0)
	{
		var childCount = 0;
		childNodes.each(function () {
			curNodeData.children[childCount] = encodeNpNodeData(this);
			childCount++;
		});
	}
	return curNodeData;
}

	
// MAIN DOC READY JQUERY
$(document).ready(function() {  


	$(window).resize(function () {
		var h = Math.max($(window).height() - 0, 420);
		//$('#container, #data, #tree, #data .content').height(h).filter('.default').css('lineHeight', h + 'px');
		$('#tree').height(h).filter('.default').css('lineHeight', h + 'px');
	}).resize();
	
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
	
	// *********** START OF JSTREE ********************  ///
	$('#tree')
		.jstree({
			'contextmenu' : {
				'select_node' : false
			},
			'core' : {
				'data' : {
					'url' : '?operation=get_node',
					'data' : function (node) {
						return { 'id' : node.id };
					}
				},
				'check_callback' : true,
				'themes' : {
					'responsive' : true
				}
			},
			'plugins' : ['state','dnd','contextmenu','wholerow']
		})
		.on('delete_node.jstree', function (e, data) {
			var nodeId = data.node.id;
			$.get('?operation=delete_node', { 'id' : data.node.id })
				.success(function() {
					deleteNode(nodeId);
				})
				.fail(function () {
					data.instance.refresh();
				});
		})
		.on('create_node.jstree', function (e, data) {
			$.get('?operation=create_node', { 'id' : data.node.parent, 'position' : data.position, 'text' : data.node.text })
				.done(function (d) {
					data.instance.set_id(data.node, d.id);
				})
				.fail(function () {
					data.instance.refresh();
				});
		})
		.on('rename_node.jstree', function (e, data) {
			$.get('?operation=rename_node', { 'id' : data.node.id, 'text' : data.text })
				.fail(function () {
					data.instance.refresh();
				});
		})
		.on('move_node.jstree', function (e, data) {
			$.get('?operation=move_node', { 'id' : data.node.id, 'parent' : data.parent, 'position' : data.position })
				.fail(function () {
					data.instance.refresh();
				});
		})
		.on('copy_node.jstree', function (e, data) {
			$.get('?operation=copy_node', { 'id' : data.original.id, 'parent' : data.parent, 'position' : data.position })
				.always(function () {
					data.instance.refresh();
				});
		})
		.on('changed.jstree', function (e, data) {
			// check if some content has been changed and warn user
			if (contentChanged) {
				if(!window.confirm('You have unsaved changes - these will be lost if you change content nodes without first saving your changes - CONTINUE?')) {
					return false;
				}
			}
			
			if(data && data.selected && data.selected.length) {
				$.get('?operation=get_content&id=' + data.selected.join(':'), function (d) {
					$('#data .default').html(d.content).show();
				});
				
				var nodeID = data.selected[0];  // TO DO review hack to avoid issues with multiselects. 
				var languageID = $('#language').val();			
				removeTabs();
				getTemplate(nodeID, languageID);
				loadContentNode(nodeID); 
				// setup any node picker trees
				
			}
			else {
				$('#data .content').hide();
				$('#data .default').html('Select a file from the tree.').show();
			}
	});
				
	// *********** END OF JSTREE ********************  ///
	// *********** END OF JSTREE ********************  ///	

	$(window).on('beforeunload', function () {
		if (contentChanged) {
			return 'You haven\'t saved your changes.';
		}
	});
	
	// look for changes in content to warn user
	$('#contentcontainer').on('change keyup keydown', 'input, textarea, select', function (e) {
		contentChanged = true;
	});
	
	// if set then show the help
	if (showHelpOnLoad == true) {
		showHelpDialog();
	}

	var wasMobile = false;
	if ($(window).width() <= 1024) {
		wasMobile = true;
	}
	
	$(window).resize(function() {
		if ($(window).width() <= 1024 && !wasMobile) {
			$('#treecontainer').show();
			$('#edit-panel').hide();
			$('#tree-showhide').removeClass('left');
			$('#tree-showhide-bar').removeClass('left');
			wasMobile = true;
		}
		else 
		{
			if($(window).width() > 1024 && wasMobile)
			{
				$('#treecontainer').show();
				$('#edit-panel').show();
				wasMobile = false;
			}
		}
	});
	
	
	$("#tree-showhide").click(function() {
		if($(this).hasClass('left'))
		{
			$('#edit-panel').hide();
			$('#treecontainer').show( "slide", { direction: "left"  }, 1000 );
			$('#tree-showhide').toggleClass('left');
			$('#tree-showhide-bar').toggleClass('left');
		}
		else 
		{
			$('#edit-panel').show();
			$('#treecontainer').hide( "slide", { direction: "left"  }, 1000 );
			$('#tree-showhide').toggleClass('left');	
			$('#tree-showhide-bar').toggleClass('left');
		}
		
	});
	
	$("#menu-help").click(function() {
		showHelpDialog();
	});
		
	$("#save, #save-2").click(function() {
		saveContent();
		contentChanged = false;
	});
	
	$("#pagepaths").click(function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		if (contentChanged) {
			if(!window.confirm('You have unsaved changes - these may be lost if you modify settings in the pagepaths area without first saving your changes - CONTINUE?')) {
				return false;
			}
		}
		showPagePathsDialog();
	});
	
	$("#language").change(function() {
	  alert( "Language changed - please click on a node to reload!" );
	});
	
	// set checkbox 
	$("#contentcontainer").on('click', '#content-nocache_checkbox', function() {
		if($(this).is(':checked') ) {
			$(this).next('#content-nocache').val(1);
		} else {
			$(this).next('#content-nocache').val(0);
		}
	});
	
	
	$("#content-pagepath-add").click(function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		addNewPagePath();
	});
	
	$("#content-primarypagepath-update").click(function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		updatePrimaryPagePath();
	});
	
	$('#content-pagepaths').on('click', 'a.content-pagepath-delete', function(event) {
	    event.preventDefault ? event.preventDefault() : event.returnValue = false;
		pathID = $(this).data('id');
		if (deletePagePath(pathID)) {
			$(this).parent('li').remove();
		}
	});
	
	// When page loads...
	$("#content_tabs_container").tabs({ 
		 activate: function( event, ui ){
			var selector = $(this).children('.tab_content').children('.entity').children('.entity-data');
			setupTinyMCE(selector);
			setupNodePickers(selector);
		}, 
		disabled: []
	});
		
	// Media Picker
	// media picker chooser	
	$("#contentcontainer").on('click', '.content-mediapicker', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		// get the section ID & set the global var
		mediaPickerTarget = $(this).prev('.entity-shorttext').attr('id');
		showMediaPickerDialog();
	});
	
	// click on folder
	$("#mediapicker-dialog").on('click', '.media-folders ul li', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;

		var path = $(this).children('span:first').text();
		getMedia(path);
	});
	
	// click on file from the media picker
	$("#mediapicker-dialog").on('click', '.media-file', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;

		var filepath = $(this).data('url');
		if (filepath.substring(0, 2) == '..') {
			filepath = filepath.substring(2);
		}
		
		// set the entity value 
		$('#' + mediaPickerTarget).val(filepath);
		$("#mediapicker-dialog").dialog( "close" );
	});
	// EOF Media Picker
				
	// Add a section
	$("#contentcontainer").on('click', '.newSectionInst', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		// get the section ID
		var sectionID = $(this).attr("id").replace("sectioninst-create_", "");
		createNewSectionInstance(sectionID);
	});
	
	$("#contentcontainer").on('click', '.sectioninst_fields_peek', function() {
		$(this).removeClass('sectioninst_fields_peek');
		var fieldsDiv = $(this).children('.sectioninst_fields');
		
		$(this).closest('.sectioninst').children('.section-buttons').children('.sectioninst_show').hide();
		setupTinyMCE($(this).children('.entity').children('.entity-data'));
		setupNodePickers(fieldsDiv.children('.entity').children('.entity-data'));
		
		$('html, body').animate({scrollTop:$(this).closest('.sectioninst').offset().top - 100}, 'slow');
	});
	
	// Show section content
	$("#contentcontainer").on('click', '.sectioninst_show', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		var fieldsDiv = $(this).closest('.sectioninst').children('.sectioninst_fields');
		fieldsDiv.removeClass('sectioninst_fields_peek');
		$(this).hide();
		setupTinyMCE(fieldsDiv.children('.entity').children('.entity-data'));
		setupNodePickers(fieldsDiv.children('.entity').children('.entity-data'));
		
		$('html, body').animate({scrollTop:$(this).closest('.sectioninst').offset().top - 100}, 'slow');
	});
	
	// Move section up
	$("#contentcontainer").on('click', '.sectioninst_moveup', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		var curDiv = $(this).closest('.sectioninst');
		var prevDiv = curDiv.prev('.sectioninst');
		if (prevDiv.length !== 0) {
		//	destroyTinyMCE();
			$(curDiv).insertBefore(prevDiv); 
		//	setupTinyMCE();
		}
		else 
		{
			//	 alert('already at the top');
		}
		$('html, body').animate({scrollTop:$(curDiv).offset().top - 100}, 'slow');
	});
	
	// Move section down
	$("#contentcontainer").on('click', '.sectioninst_movedown', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		var curDiv = $(this).closest('.sectioninst');
		var nextDiv = curDiv.next('.sectioninst');
		if(nextDiv.length !== 0) {
		//	destroyTinyMCE();
			$(curDiv).insertAfter(nextDiv);
		//	setupTinyMCE();
		}
		else 
		{
			//	 alert('already at the top');
		}
		$('html, body').animate({scrollTop:$(curDiv).offset().top - 100}, 'slow');
	});

	// Delete section 
	$("#contentcontainer").on('click', '.sectioninst_delete', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		var curDiv = $(this).closest('.sectioninst');
		if (window.confirm("Are you sure - this cannot be undone!?")) {
			curDiv.hide('slow', function(){ curDiv.remove(); });
		}
	});
	
	// Link Picker - Media Picker  
	$("#linkpicker-select").click(function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		showLinkPickerNodePickerDialog();
	});	

	
	// set checkbox 
	$("#contentcontainer").on('click', '.entity-checkbox', function() {
	//	event.preventDefault ? event.preventDefault() : event.returnValue = false;
		if($(this).is(':checked') ) {
			$(this).next('.entity-bool').val('true');
		} else {
			$(this).next('.entity-bool').val('false');
		}
	});
	
        
/* variables */
  var preview = $('img.media-preview');
  var status = $('.status');
  var percent = $('.percent');
  var bar = $('.bar');
  var progress = $('.progress');

  /* only for image preview */
  $("#image").change(function(){
    preview.fadeOut();

    /* html FileRender Api */
    var oFReader = new FileReader();
    oFReader.readAsDataURL(document.getElementById("image").files[0]);
	var fileName = document.getElementById("image").files[0].name;
	var extn = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
	var showPreview = false;
	if(extn == "gif" || extn == "png" || extn == "jpg" || extn == "jpeg") {
		showPreview = true;
	}  

    oFReader.onload = function (oFREvent) {
		if(showPreview)
		{
			preview.attr('src', oFREvent.target.result).fadeIn();
		}
		else 
		{
			preview.attr('src', './css/images/document.png').fadeIn();
		}
    };
  });

	$('#pure-form').ajaxForm({
		/* set data type json */
		dataType:  'json',

		/* reset before submitting */
		beforeSend: function() {
			bar.show();
			progress.show();
			status.fadeOut();
			bar.width('0%');
			percent.html('0%');
		},
		
		/* progress bar call back*/
		uploadProgress: function(event, position, total, percentComplete) {
			var pVel = percentComplete + '%';
			bar.width(pVel);
			percent.html(pVel);
		},

		/* complete call back */
		complete: function(data) {
			preview.fadeOut(800);
			status.html(data.responseJSON.status).fadeIn();
			getMedia(curMediaPath);
			$('#image-upload').hide();
			$('#image-upload-help-text').hide();
			$('#progress').hide();
			bar.fadeOut();
		}

  });

	$('#image').change(function() {
		$('#progress').hide();
		$('.status').hide();		
		$('#image-upload-help-text').hide();
		$('#image-upload').show();
		$('#image-upload-help-text').show();
	});
	
	$('#create-folder').click(function() {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;		
		createMediaFolder();
	});
	
	/* Begin Node Picker  */ 
	$("#contentcontainer").on('click', '.np-node-make-adult', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
			
		var curNode = $(this).closest('div.np-node');
		var nextNode = curNode.next('div');
		var level = curNode.data('level');
		var newLevel = level - 1;
		
		if(newLevel > 0)
		{
			var prevNode = curNode.prev('div.np-node');
			var prevNodeLevel = prevNode.data('level');
		
			// first check if there immediately following it is a add new node button - if so delete it.
			if(nextNode.children('a').first().hasClass('np-node-make-node'))
			{
				curNode.next('div').remove();
			}
				
			// If prev node is at what the new level will be easy (e.g. first child node)
			// *
			//   *  // this one`
			//   *
			// *
			if(prevNodeLevel == newLevel)
			{
				curNode.data('level', newLevel);
				curNode.removeClass('level-' + level).addClass('level-' + newLevel);
			}
			else 
			{
				// if there is a node before that needs to stay at same level
				// *
				//   *
				//   *  // this one
				//   *
				// *
				prevNode.after('<div class="np-node-button level-'+level+'" data-level="'+level+'"><a href="#" class="np-node-make-node">Add Node</a></div>');
				curNode.data('level', newLevel);
				curNode.removeClass('level-' + level).addClass('level-' + newLevel);
			}
						
			// if the next node is NOT at the same level then create a add node button
		//	$('<div class="np-node-button level-'+newLevel+'" data-level="'+newLevel+'"><a href="#" class="button purple content-nodepicker np-node-make-node">Add Node</a></div>').insertAfter(curNode);
		}
		else 
		{
			//	 alert('really shouldn't be here as I should have hid the button`');
		}
		resetNpNodeTreeButtons(curNode.closest('.np-node-tree-root'));
		encodeNpNodeTree(curNode);
	});
	
	
	$("#contentcontainer").on('click', '.np-node-make-child', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		
		var curNode = $(this).closest('div.np-node');
		var level = curNode.data('level');
		var newLevel = level + 1;
		var prevNode = curNode.prev('div.np-node.level-' + level);
		
		// can't make a child of a node which doesn't have any nodes above it at the same level
		if(prevNode.length !== 0) {
			curNode.data('level', newLevel);
			curNode.removeClass('level-' + level).addClass('level-' + newLevel);
			
			// if the next node is NOT at the same level then create a add node button
			$('<div class="np-node-button level-'+newLevel+'" data-level="'+newLevel+'"><a href="#" class="np-node-make-node"><span class="ui-icon ui-icon-plusthick"></span>Add Node</a></div>').insertAfter(curNode);
		}
		else 
		{
			//	 alert('really shouldn't be here as I should have hid the button`');
		}
		resetNpNodeTreeButtons(curNode.closest('.np-node-tree-root'));
		encodeNpNodeTree(curNode);
	});
	
	
	$("#contentcontainer").on('click', '.np-node-make-node', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		// get the div where the node is to be added into a global var
		
		// set the nodeTarget as the buttons parent div (node is inserted before)
		nodeTarget = $(this).parent();
		showNodePickerDialog();
		
	});	
	
	
	$("#contentcontainer").on('click', '.np-node-delete', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		var tree = $(this).closest('div.np-node-tree-root');
		var curNode = $(this).parent().parent();
		var level = curNode.data('level');
		var prevNode = curNode.prev('div.np-node.level-'+level);
		var nextNode = curNode.next('div.np-node-button');
		
		
		if(level == 1){
			curNode.remove();
		}
		else 
		{
			if(level > 1 && prevNode.length == 0 && nextNode.length !== 0) 
			{
				// single child node
				curNode.remove();
				nextNode.remove();
			}
			else
			{
				curNode.remove();
			}
		}
		
		resetNpNodeTreeButtons(tree);
		encodeNpNodeTree(tree);
	});

	
	$("#contentcontainer").on('click', '.np-node-move-up', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		
		var curNode = $(this).closest('div.np-node');
		var level = curNode.data('level');
		var prevNode = curNode.prevAll('div.np-node.level-' + level).first();
		var childNodes = null;
			
		if(curNode.next('div.np-node-button').length == 0)
		{
			childNodes = curNode.nextUntil('div.np-node.level-' + level + ', div.np-node-button.level-'+level);
		}
	
		if(prevNode.length !== 0) {
			$(curNode).insertBefore(prevNode);
			$(childNodes).insertAfter(curNode);
		}
		else 
		{
			//	 alert('already at the top');
		}
		resetNpNodeTreeButtons(curNode.closest('.np-node-tree-root'));
		encodeNpNodeTree(curNode);
	});
	
	
	$("#contentcontainer").on('click', '.np-node-move-down', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		//var nextDiv = curDiv.next('li:not(.np-node-button)');  // to do where not .np-node-button
		var curNode = $(this).closest('div.np-node');
		var level = curNode.data('level');
		var nextNode = curNode.nextAll('div.np-node.level-' + level).first();
		var nextNextNode = nextNode.nextAll('div.np-node.level-' + level + ', .np-node-button.level-' + level).first();
		var childNodes = curNode.nextUntil('div.np-node.level-' + level);
		
		// it's a single node move - easy
		if(nextNode.length !== 0) {
			if(nextNextNode.length ==0)
			{
				nextNextNode = curNode.nextAll('div.np-node-button.level-1').first();
			}
			$(curNode).insertBefore(nextNextNode);
			$(childNodes).insertAfter(curNode);
		}
		else 
		{
			// alert('already at the bottom');
		}
		resetNpNodeTreeButtons(curNode.closest('.np-node-tree-root'));
		encodeNpNodeTree(curNode);
	});

	/* EOF Node Picker */
	
}); 

// select file function only for styling up input[type="file"]
function select_file(){
	document.getElementById('image').click();
	return false;
}