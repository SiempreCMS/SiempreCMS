<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
// includes and security
require_once('../../admin-includes/admin.base.inc.php');
require_once('../../admin-includes/admin.security.inc.php');
require_once('../../admin-includes/admin.vercheck.inc.php');

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Siempre CMS - Users</title>
		<link rel="icon" type="image/png" href="images/favicon.ico" />
		<meta name="viewport" content="width=device-width" />	
		<link rel="stylesheet" href="css/jquery-ui.min.css" />
		<link rel="stylesheet" href="css/jquery-ui.theme.min.css" />
		<link rel="stylesheet" href="css/jstree/style.min.css" />
		<link rel="stylesheet" href="css/main.css?ver=1.3.3" />
	</head>
	<body>
		<?php
		$menu = 'user';
		require_once('../../admin-includes/admin.menu.php');
		?>
		
		<!-- Start of Login Dialog -->  
		<div id="login-dialog" class="login-form" title="Please login" style="display: none;"> 
			<p>Your session has timed out - please re-enter your password to continue.</p> 
			<form id="login-form"  action="#">
				<div class="form-row clearfix">
					<label>User:</label>
					<input type="text" name="username" id="username" value="<?php echo $username; ?>" readonly="readonly"/>
				</div>
				<div class="form-row clearfix">
					<label>Password: </label>
				<input type="password" name="password" id="password" />
				</div>
				<div class="form-row clearfix">
					<span class="error" id="login-errors">&nbsp;</span>
				</div>
			</form>
		</div>  
		<!-- End of Login Dialog -->  
		
		<!-- Start of please wait div -->
		<div id="loading-dialog" title="Executing..." style="display: none;"> 
			<p><img src="images/ajax-loader.gif" /> Please Wait</p>
		</div> 
		
		<div id="menuspacer" class="menuspacer">
		</div>		
		<!-- Main Wrapper -->
		<div class="main">
			<div id="edit-panel" class="edit-panel user-panel panel">
				<div id="edit-menu" class="edit-menu">	
					<a href="#" class="button green" id="user-record-new"><span class="icon-add"></span> Create a new user</a>
				</div>
			
				<div id="user">
					<div class="edit-header" id="user-panel-header">
						<h3 id="user-panel-header-title">User Panel - [please select a user]</h3>
					</div>
					<div id="user_tabs_container">	
						<div id="user_tabs_menu">
							<ul>
								<li><a href="#user-search">Search</a></li>
								<li><a href="#user-results">Results</a></li>
								<li><a href="#user-record">User</a></li>
							</ul>
						</div>
						<div id="user-search" class="tab_content">	
							<div id="user-search-panel">
								<p><b>Search for User</b></p>
								<form id="user-search-form" action="#">
									<div class="twocol-contentA">
										<div class="twocol-row">
											<div class="twocol-left">User Name:</div>
											<div class="twocol-right"><input type="text" id="user-search-username" name="user-search-username" /></div>
											<div class="twocol-clear"></div>
										</div>
										<div class="twocol-row">
											<div class="twocol-left">Name:</div>
											<div class="twocol-right"><input type="text" id="user-search-name" name="user-search-name" /></div>
											<div class="twocol-clear"></div>
										</div>
									</div>
									<br/>
									<a href="#" class="button" id="user-search-button"><span class="icon-search"></span> Search</a>
								</form>
							</div>					
							<div id="user-search-id">
								<p><b>Load User by ID</b></p>
								<form id="user-id-form" action="#">
									<div class="twocol-content">
										<div class="twocol-row">
											<div class="twocol-left">ID:</div>
											<div class="twocol-right"><input type="text" id="user-search-id" name="user-search-id" /></div>
											<div class="twocol-clear"></div>
										</div>
										<br/>
										<a href="#" class="button" id="user-id-button"><span class="icon-action"></span> Load</a>
									</div>
								</form>
							</div>
							<div style="clear: both;"></div>
							<div id="user-search-errors" class="error">&nbsp;</div>
						</div>
						<div id="user-results" class="tab_content">
							<h3 id="user-results-heading">Search Results</h3>
							<form id="user-results-settings" action="#">
								Show:
								<select id="user-results-perpage" name="user-results-perpage">
										<option value="20">20</option>
										<option value="50">50</option>
										<option value="100">100</option>
								</select> <label for="user-results-perpage"> Results Per Page</label>
								<label>Page: </label>
								<select id="user-results-page">
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
								</select>
							</form>
							<table id="user-results-table" class="stripes">
								<tbody>
								<tr>
									<th id="user-search-id-head">ID</th>
									<th id="user-search-username-head">User Name</th>
									<th id="user-search-forename-head">Fore Name</th>
									<th id="user-search-lastname-head">Last Name</th>
									<th id="user-search-id-lastbook">Last Login</th>
									<th id="user-search-created-head">Created</th>
									<th id="user-search-lastUpdated-head">Last Updated</th>
								</tr>
								</tbody>
							</table>
						</div>
						
						<div id="user-record" class="tab_content">
							<form id="user-record-form" action="#" style="display: none;">
								<div style="float:left; margin-right:20px; width:400px; border-right: .15em dotted #9669FE;">
									<div class="twocol-contentA">
										<div class="twocol-row">
											<div class="twocol-left">ID</div>
											<div class="twocol-right"><input size="6" name="user-id" id="user-id" readonly="true" type="text" class="text, noedit" /></div>
											<div class="twocol-clear"></div>
										</div>
										<div class="twocol-row">
											<div class="twocol-left">UserName</div>
											<div class="twocol-right"><input name="user-username" id="user-username" readonly="true" type="text" class="text" disabled="disabled"/></div>
											<div class="twocol-clear"></div>
										</div>
										<div class="twocol-row">
											<div class="twocol-left">First name</div>
											<div class="twocol-right"><input name="user-forename" id="user-forename" type="text" class="text" disabled="disabled"/></div>
											<div class="twocol-clear"></div>
										</div>
										<div class="twocol-row">
											<div class="twocol-left">Last name</div>
											<div class="twocol-right"><input name="user-lastname" id="user-lastname" type="text" class="text" disabled="disabled"/></div>
											<div class="twocol-clear"></div>
										</div>
										<div class="twocol-row">
											<div class="twocol-left">Email</div>
											<div class="twocol-right"><input id="user-email" name="user-email" type="text" class="text" disabled="disabled"/></div>
											<div class="twocol-clear"></div>
										</div>
									</div>
									<br/>
									<a href="#" class="button red" id="user-record-new-save"><span class="icon-edit"></span> Save New User</a>
									
									<a href="#" class="button red" id="user-record-edit"><span class="icon-edit"></span> Edit</a>
									<a href="#" class="button purple" id="user-record-change-password"><span class="icon-password"></span> Change Password</a>
									<a href="#" style="display: none" class="button green" id="user-record-update"><span class="icon-save"></span> Update</a>
								</div>					
								
							<div style="float:left; margin-right:20px;">
								<div id="user-record-form-password" style="display: none">
									<div class="twocol-content">
										<div class="twocol-row">
											<div class="twocol-left">New Password</div>
											<div class="twocol-right"><input id="user-password1" name="user-password1" type="password" class="text"/></div>
											<div class="twocol-clear"></div>
										</div>
										<div class="twocol-row">
											<div class="twocol-left">Confirm New Password</div>
											<div class="twocol-right"><input id="user-password2" name="user-password2" type="password" class="text"/></div>
											<div class="twocol-clear"></div>
										</div>
									</div>  
									<br/>
									<a href="#" class="button" id="user-record-update-password"><span class="icon-publish"></span> Update Password</a>
									<div id="user-record-password-errors" class="error">&nbsp;</div>
								</div>
							</div>
							<div style="clear: both;"></div>
							</form>
						
							<div id="user-record-errors" class="error">&nbsp;</div>
						</div> <!-- /user record container -->
					</div>	<!-- /user tab container -->
				</div>  <!-- /user -->
			</div>  <!-- /panels -->
		</div> <!-- end of wrapper -->
		<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui.min.js"></script> 
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>
		<script type="text/javascript" src="js/jstree/jstree.min.js"></script>
		<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript" src="js/noty/packaged/jquery.noty.packaged.min.js"></script>
		<script type="text/javascript" src="js/user.js?v=133"></script>
	</body>
</html>