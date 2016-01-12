/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 $(document).ready(function() {  
	
	// Login
	$("#login-submit").click(function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
		
		$.ajax({
			url: 'login_ajax.php',
			data: { action: "login", 
				username: $("#username").val(),
				password: $("#password").val()
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			error: function(xhr, textStatus, errorThrown){
				alert("Error - the server appears to be down - please check your connection and try again: " +textStatus)
        },
        success: function (j) {
			if (j.result == true) {
				var loc = $("#loc").val();
				if (loc.length == 0 || loc.toLowerCase() == "admin") {
					window.location.href = 'dashboard.php';
				}
				else {
					window.location.href = loc;
				}
				return false;  // block the form submit
			}
			else {
				switch(j.msg) {
					case 'Incorrect_login':
						$("#login-response-dialog").text("Incorrect User name or password - please check and try again.");
						break;
					case 'IP_locked':
						$("#login-response-dialog").text("You have had too many unsuccessful log in attempts - to protect your account we\'ve locked your access.  Try again in 30 minutes");
						break;
					default:
						$("#login-response-dialog").text("Unknown login error - please contact customer support");
				}
				
				$("#login-response-dialog").dialog({
					modal: true,
					buttons: {
						Ok: function() {
							$( this ).dialog( "close" );
						}
					}
				});			
			}
          }
        });
	});
}); 