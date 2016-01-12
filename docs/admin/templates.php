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

$justLoggedIn = false;
// Show the help dialog if they've just logged into the demo system 
if (isset($_SESSION['justLoggedIn']) && $_SESSION['justLoggedIn'] == true)
{
	$justLoggedIn = true;
	$_SESSION['justLoggedIn'] = false;
}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Siempre CMS - Templates</title>
		<link rel="icon" type="image/png" href="images/favicon.ico" />
		<meta name="viewport" content="width=device-width" />		
		<link rel="stylesheet" href="css/jquery-ui.min.css" />
		<link rel="stylesheet" href="css/jquery-ui.theme.min.css" />
		<link rel="stylesheet" href="css/jstree/style.min.css" />
		<link rel="stylesheet" href="css/main.css?ver=1.3.3" />
		
		<script type="text/javascript">
		<?php 
			// for the initial log in help dialog
			if ($justLoggedIn)
			{
				echo "var showHelpOnLoad = true; ";
			}
			else {
				echo "var showHelpOnLoad = false; ";
			}
		?>
		</script>
	</head>
	<body>
	
	<?php
		$menu = 'templates';
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
		
		<!-- Start of Help Dialog -->  
		<div id="Siempre-CMS-help-dialog" title="Siempre CMS Help - to close press the 'X' ->" style="display: none;" class="help"> 	
			<h2>Welcome to Siempre CMS Beta!</h2>
			<p>This is in Beta...</p>
		</div>  
		<!-- End of Help Dialog -->  
		
		<!-- Start of please wait div -->
		<div id="loading-dialog" title="Executing..." style="display: none;"> 
			<p><img src="images/ajax-loader.gif" /> Please Wait</p>
		</div> 
		<!-- End of please wait div --> 
		
		<!-- Start of New Template Dialog -->  
		<div id="new-template-dialog" title="Siempre CMS Help - create a new template" style="display: none;" class="help"> 	
			<h2>Create a new template</h2>
			<p>Enter a name for your new template...</p>
			<input type="text" id="new-template-name"></input>
		</div>  
		<!-- End of New Template Dialog -->  
		
		<!-- Start of New Tab Dialog -->  
		<div id="new-tab-dialog" title="Siempre CMS Help - create a new tab in the template" style="display: none;" class="help"> 	
			<h2>Create a new tab</h2>
			<p>Enter a name for your new tab...</p>
			<input type="text" id="new-tab-name"></input>
		</div>  
		<!-- End of New Template Dialog -->  
		
		<!-- Start of New Entity Dialog -->  
		<div id="new-entity-dialog" title="Siempre CMS Help - create a new entity in the tab" style="display: none;" class="help"> 	
			<h2>Create a new entity</h2>
			<p>Enter a name for your new entity...</p>
			<input type="text" id="new-entity-name"></input>
			<p>And choose the type...</p>
			<select id="new-entity-type" name="new-entity-type" style="display:block; float:left;">
				<option value="1">1 - A Number</option>
				<option value="2">2 - Money</option>
				<option value="3">3 - Short Text</option>
				<option value="4" selected="selected">4 - Long Text</option>
				<option value="5">5 - Rich Text</option>				
				<option value="6">6 - Date</option>
				<option value="7">7 - Date and time</option>
				<option value="8">8 - Checkbox</option>							
			<!--	<option value="9">9 - Drop Down</option> -->
				<option value="10">10 - Node Picker</option> 
				<option value="11">11 - Media Picker</option>	
			</select>			
		</div>  
		<!-- End of New Template Dialog -->  
		
		
		<div id="menuspacer" class="menuspacer">
		</div>
		<div class="main">	
			<div id="tree-showhide-bar">
				<div id="tree-showhide">
					<span class="visually-hidden">Show tree</span>
				</div>
			</div>
		
		
			<div id="templatelist" class="treecontainer">	
				<div>
					<div class="templatelisthead">
						<h3>Available Templates:</h3>
					</div>
					<ul class="template-list" id="template-results-buttons">
						<li>&nbsp;</li>
					</ul>
				</div>
				<a href="#" class="button green" id="new"><span class="icon-publish"></span> New Template</a>
			</div>
			
			<!-- Right hand side container -->
			<!-- Main Container -->
			<div id="templatecontainer" class="edit-panel panel">
		
				<div id="submenucontainer" class="edit-menu">		
					<a href="#" class="button" id="save"><span class="icon-save"></span> Save</a>
					<a href="#" class="button purple" id="new-tab"><span class="icon-add"></span> Add Tab</a>
				</div>
			
				<div>
					<div id="contentcontainer">
						<div class="edit-header" id="content-panel-header">
							<h3 id="content-panel-header-title"><span id="template-title">&nbsp;</span></h3>
						</div>
						<div id="template_tabs_container_expand" style="display:none;" class="accordion_expand">
							<p><i>Click on the header above to expand..</i></p>
						</div> 
						<div id="template_tabs_container" class="accordion_content">	
							<div id="template_tabs_menu">
								<ul class="tabs">
									<li><a href="#template-standard">Standard</a></li>
								</ul>
							</div> 
							<div id="template-standard" class="tab_content">
								<h3>Template Details</h3>
								<div class="entity clearfix">
									<div class="entity-info">
										<h4 class="entity-title"></h4>
										<p class="entity-description">&nbsp;</p>
									</div>
									<div class="entity-data">
									
									</div>
								</div>
								
								<div class="entity clearfix">
									<div class="entity-info">
										<h4 class="entity-title">Template ID:</h4>
										<p class="entity-description">&nbsp;</p>
									</div>
									<div class="entity-data">
										<input type="text" id="template-id" name="template-id" readonly="true" />
									</div>
								</div>
								<div class="entity clearfix">
									<div class="entity-info">
										<h4 class="entity-title">Template Name:</h4>
										<p class="entity-description">&nbsp;</p>
									</div>
									<div class="entity-data">
										<input type="text" id="template-name" name="template-id" readonly="true" />
									</div>
								</div>
								<div class="entity clearfix">
									<div class="entity-info">
										<h4 class="entity-title">Created</h4>
										<p class="entity-description">&nbsp;</p>
									</div>
									<div class="entity-data">
										<input type="text" id="template-created" name="template-created" readonly="true" />
									</div>
								</div>
								<div class="entity clearfix">
									<div class="entity-info">
										<h4 class="entity-title">Last Updated</h4>
										<p class="entity-description">&nbsp;</p>
									</div>
									<div class="entity-data">
										<input type="text" id="template-lastUpdated" name="template-lastUpdated" readonly="true" />
									</div>
								</div>
								<div class="entity clearfix">
									<div class="entity-info">
										<h4 class="entity-title">Description:</h4>
										<p class="entity-description">&nbsp;</p>
									</div>
									<div class="entity-data">
										<textarea id="template-description" name="template-description" rows="2" cols="80"></textarea>
									</div>
								</div>
								<div class="entity clearfix">
									<div class="entity-info">
										<h4 class="entity-title">Content</h4>
										<p class="entity-description">&nbsp;</p>
									</div>
									<div class="entity-data">
										<textarea id="template-content" name="template-content" rows="30" cols="80"></textarea>
									</div>
								</div>
								<div class="entity clearfix" id="template-parent-section">
									<div class="entity-info">
										<h4 class="entity-title">Use Site Template as Parent?</h4>
										<p class="entity-description">Check this box to use the parent template.</p>
									</div>
									<div class="entity-data">
										<input type="checkbox" id="template-parent" name="template-parent" value="template-parent">Use parent
									</div>
								</div>
								<div class="entity clearfix" id="template-parent-section">
									<div class="entity-info">
										<h4 class="entity-title">Add standard SEO META fields?</h4>
										<p class="entity-description">Use this to add the standard META keywords to the template - you still need to output these on .</p>
									</div>
									<div class="entity-data">
										<a href="#" class="button purple" id="add-seo"><span class="icon-add"></span> Add SEO Fields</a>
									</div>
								</div>
								<div class="entity clearfix" id="template-parent-section">
									<div class="entity-info">
										<h4 class="entity-title">Add standard Sitemap fields?</h4>
										<p class="entity-description">Use this to add the standard Sitemap fields to the template - if these are set these are automatically used in the sitemap creation.</p>
									</div>
									<div class="entity-data">
										<a href="#" class="button purple" id="add-sitemap"><span class="icon-add"></span> Add Sitemap Fields</a>
									</div>
								</div>
							</div>
						</div> <!-- EOF template_tabs_container -->
					</div>
				</div>   <!-- end of content -->
				<br/>
			</div>
			<div style="clear: both;"></div>
			<div class="error" id="tree-errors">&nbsp;</div>
		</div><!-- end of wrapper -->
		<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui.min.js"></script> 
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>
		<script type="text/javascript" src="js/jstree/jstree.min.js"></script>
 		<script type="text/javascript" src="js/jquery.ajaxq-sjm-0.0.7.js"></script> 
		<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript" src="js/noty/packaged/jquery.noty.packaged.min.js"></script>
		<script type="text/javascript" src="js/templates.js?v=133"></script>
	</body>
</html>