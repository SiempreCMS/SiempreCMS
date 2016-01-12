/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
	
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


function protectScreen(form) {
	// disables all menus and that so the user concentrates on job in hand
	$('#edit-menu').block({ message: null }); 
	// Disable the user tabs
    $('#user_tabs_menu').block({ message: null }); 
	
	// Do do all the other tabs?
}	


function unprotectScreen() {
	// disables all menus and that so the user concentrates on job in hand
	$('#edit-menu').unblock({ message: null }); 
    $('#user_tabs_menu').unblock(); 
}	


function protectForm(form) {
	// protect the fields - a generic function in which you can pass the form id and it will do the job
	// used in conjunction with the protect screen to disable all menus et al when the user is editing a form and we don't want
	// to nav away until complete
	$(':input',form+'-form')
		 .not(':button, :submit, :reset, :hidden, .noedit')
		.attr('disabled', 'disabled');
					 
	$(form+'-edit').show();
	$(form+'-new-save').hide();
}


function unprotectForm(form) {
	// protect the fields
	$(':input',form+'-form')
		 .not(':button, :submit, :reset, :hidden, .noedit')
		 .removeAttr('disabled');
					 
	$(form+'-edit').hide();
	$(form+'-update').show();
	$(form+'-new-save').hide();
}
		

function userSearch() {
	// disable the search button(s)
	$(this).attr("disabled", "true");
	
	// clear last results
	$('#user-results-table tr:gt(0)').remove();

	// get page selection - if null then it's disabled so use 1.
	var pageNo = $('#user-results-page').val();
	if (pageNo === null)
		pageNo = 1;
	
	var ajaxData = $('form#user-search-form').serializeArray();
    ajaxData.push({ name: "action", value: "search" });
    ajaxData.push({ name: "user-search-perpage", value: $('#user-results-perpage').val() });
	ajaxData.push({ name: "user-search-page", value: pageNo });
  
  
	$.ajax({
		url: 'user_search_ajax.php',
		data: ajaxData,
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
			var lastBooking = ''
			$.each(j.results, function(key, value) { 
				if (value['lastBooking'] == null) {
					lastBooking = '~ n/a ~';
				} 
				else {
					lastBooking = value['lastBooking'];
				}
				$('#user-results-table').append('<tr id=\'userresult'+value['userID']+'\'><td>'+value['userID']+'<\/td><td>'+value['userName']+'<\/td><td>'+value['foreName']+'<\/td><td>'+value['lastName']+'<\/td><td>'+value['lastLogin']+'<\/td><td>'+value['created']+'<\/td><td>'+value['lastUpdated']+'<\/td><\/tr>');
			});  
	
			$(".stripes tr:odd").addClass("alt");
					
			$('#user-results-heading').text('Search Results - ' + j.totalrows + ' rows found');
						
			// Set the paging
			var nopages = Math.ceil(j.totalrows/$('#user-results-perpage').val());			
			var curpage = $('#user-results-page').val();
			// clear the select page
			$('#user-results-page')[0].options.length = 0;
			$('#user-results-page').attr('disabled','disabled');
		
			if (nopages>1) 
			{
				for (i=1;i<=nopages;i=i+1)
				{	
					if (curpage != i) {
						$('#user-results-page').append('<option value="'+i+'">'+i+'</option>');
					} else {
						$('#user-results-page').append('<option selected="selected" value="'+i+'">'+i+'</option>');
					}
				}		
				$('#user-results-page').removeAttr('disabled');
			}				
			// show the results tab
			$("#user_tabs_container").tabs("option", "active", 1);

			// disable the search button(s)
			$(this).removeAttr("disabled");			
			return false;
		}
		else {
			$("#user-search-errors").text("No results found...").show();
			
			setTimeout(function(){
				$("#user-search-errors").fadeOut("slow", function () {
					$("#user-search-errors").hide();
				});
			}, 4000);
		
			// disable the search button(s)
			$(this).removeAttr("disabled");	
			return false;
		}
	  }
	});

	return false;
}

function loadUser(userID) {
	$.ajax({
          url: 'user_ajax.php',
          data: 'action=loadUser&userID='+userID,
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
				// set the user panel
				$("#user-panel-header-title").html('User Panel:- (' + j.results['userID'] + ') ' + j.results['lastName'] + ', ' +j.results['foreName']);
				// now set the user fields
				$("#user-id").val(j.results['userID']);
				$("#user-username").val(j.results['userName']);
				$("#user-forename").val(j.results['foreName']);
				$("#user-lastname").val(j.results['lastName']);
				$("#user-email").val(j.results['email']);
				// show the user tab
				$("#user_tabs_container").tabs("option", "active", 2);
				$("#user-search-errors").text("").show();
				$("#user-record-form").show();
				$("#user-record-change-password").show();
				$("#user-record-form-password").hide();
			}
			else {
				var n = noty({
					text: 'Invalid user ID\n\n',
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
			
			// protect the fields
			protectForm('#user-record');
          }
        });
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
	
	//When page loads...
	$( "#user_tabs_container").tabs();
	
	
	////////////////////////////////
	////////////////////////////////
	//   User Search              //
	////////////////////////////////
	////////////////////////////////	
	
	// User Search - load user ID
	$('#user-id-button').click(function() {
		
		var userID = $('#user-search-id').val();
          
		loadUser(userID);
		return false;
	});
	
	
	// User Search
	$("#user-search-button").click(function() {
		userSearch();
		return false;
	});
		
	// paging changed
	$("#user-results-perpage").change(function() {
		$("#user-results-page").val(1);  // reset to one incase you're increasing the numbers of records per page. 
		userSearch();
	});
	
	$("#user-results-page").change(function() {
		userSearch();
	});
	
	// User search record selected - on instead of click to bind dynamically created
	$('#user-results').on('click', 'tr:gt(0)', function() {
		var userID = $(this).attr('id').replace('userresult', '');
		loadUser(userID);
	});
	
	// New User
	$("#user-record-new").click(function() {
		// show the user tab
		$("#user_tabs_container").tabs("option", "active", 2);
		$("#user-record-form").show();
		// clear user form
		$(':input','#user-record-form')
			 .not(':button, :submit, :reset, :hidden')
			 .val('')
			 .removeAttr('disabled')
		$('#user-record-edit').hide();
		$('#user-record-update').hide();
		$('#user-record-change-password').hide();
		$("#user-record-form-password").hide();
		$('#user-record-new-save').show();
		$('#user-id').val('tbc').attr('disabled', 'disabled');
		$('#user-username').removeAttr('readonly');
		$('#user-record-change-password').hide();
	});
	
	// New User Save
	$("#user-record-new-save").click(function() {
		$.ajax({
			url: 'user_ajax.php',
			data: 'action=new&'+$("#user-record-form").serialize(),
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
				//$('#user-id').val(j.user-id);
				userid = j.userid;
				$('#user-id').val(userid);
				var n = noty({
					text: 'User Record Saved!\n\n',
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
				var n2 = noty({
					text: 'Remember to set a password for the new user!\n\n',
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
				
				// unprotect the menus et al
				$('#user-username').attr('readonly', 'true');
				unprotectScreen();
				// protect the fields
				protectForm('#user-record');
				$("#user-record-change-password").show();
			}  
			else {
				if (j.msg != null){	
					var n = noty({
						text: j.msg + '\n\n',
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
				else {
					var n = noty({
						text: 'There seems to be a problem with the server...\n\n',
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
          }
        });
	});
	
	
	// Edit User
	$("#user-record-edit").click(function() {	
		protectScreen();
		unprotectForm('#user-record');
		$("#user-record-change-password").hide();
		
	});

	
	// Change User Password
	$("#user-record-change-password").click(function() {	
		$("#user-record-change-password").hide();
		$('#user-record-form-password').show();
		
	});	
	
	
	// Update User
	$("#user-record-update").click(function() {
		$.ajax({
			url: 'user_ajax.php',
			data: 'action=set&'+$("#user-record-form").serialize(),
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
				var n = noty({
						text: 'User Record Updated\n\n',
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
				
				// unprotect the menus et al
				$('#user-record-update').hide();
				$("#user-record-change-password").show();
				unprotectScreen();
				// protect the fields
				protectForm('#user-record');
			}  
			else {
				if (j.msg !== null){
					var n = noty({
						text: j.msg + '\n\n',
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
				else {
					var n = noty({
						text: 'There seems to be a problem with the server...\n\n',
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
          }
        });
	});
	
	
	// Update Password
	$("#user-record-update-password").click(function() {
		$.ajax({
			url: 'user_ajax.php',
			data: 'action=setPassword&user-id='+$("#user-id").val()+'&'+$("#user-record-form").serialize(),
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
				$("#user-record-form-password").hide();
				var n = noty({
						text: 'User Password Updated...\n\n',
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
				
				$("#user-record-change-password").show();
			}  
			else {
				if (j.msg != null){
					var n = noty({
						text: j.msg,
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
				else {
					var n = noty({
						text: '<b>There seems to be a problem with the server...<\/b>',
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
          }
        });
	});
}); 
	