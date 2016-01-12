<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
require_once('../../admin-includes/admin.base.inc.php');
require_once('../../admin-includes/admin.security.inc.php');
require_once('../../admin-includes/admin.vercheck.inc.php');

$justLoggedIn = false;
// Show the help dialog if they've just logged into the demo system for the first time
if (isset($_SESSION['justLoggedIn']) && $_SESSION['justLoggedIn'] == true)
{
	$justLoggedIn = true;
	$_SESSION['justLoggedIn'] = false;
}

if(isset($_GET['operation'])) {
	$dbconn = 'mysqli://'.DB_USER.'@'.DB_SERVER.'/'.DB_NAME;
	$fs = new tree(db::get($dbconn), array('structure_table' => 'cms_tree_struct', 'data_table' => 'cms_tree_data', 'data' => array('nm')));
	try {
		$rslt = null;
		switch($_GET['operation']) {
			case 'get_node':
				$node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
				$temp = $fs->get_children($node);
				$rslt = array();
				foreach($temp as $v) {
					$rslt[] = array('id' => $v['id'], 'text' => $v['nm'], 'children' => ($v['rgt'] - $v['lft'] > 1));
				}
				break;
			case "get_content":
				$node = isset($_GET['id']) && $_GET['id'] !== '#' ? $_GET['id'] : 0;
				$node = explode(':', $node);
				if(count($node) > 1) {
					$rslt = array('content' => 'Multiple selected');
				}
				else {
					$temp = $fs->get_node((int)$node[0], array('with_path' => true));
					$rslt = array('content' => 'Selected: /' . implode('/',array_map(function ($v) { return $v['nm']; }, $temp['path'])). '/'.$temp['nm']);
				}
				break;
			case 'create_node':
				$node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
				$temp = $fs->mk($node, isset($_GET['position']) ? (int)$_GET['position'] : 0, array('nm' => isset($_GET['text']) ? $_GET['text'] : 'New node'));
				$rslt = array('id' => $temp);
				break;
			case 'rename_node':
				$node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
				$rslt = $fs->rn($node, array('nm' => isset($_GET['text']) ? $_GET['text'] : 'Renamed node'));
				break;
			case 'delete_node':
				$node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
				$rslt = $fs->rm($node);
				break;
			case 'move_node':
				$node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
				$parn = isset($_GET['parent']) && $_GET['parent'] !== '#' ? (int)$_GET['parent'] : 0;
				$rslt = $fs->mv($node, $parn, isset($_GET['position']) ? (int)$_GET['position'] : 0);
				break;
			case 'copy_node':
				$node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
				$parn = isset($_GET['parent']) && $_GET['parent'] !== '#' ? (int)$_GET['parent'] : 0;
				$rslt = $fs->cp($node, $parn, isset($_GET['position']) ? (int)$_GET['position'] : 0);
				break;
			default:
				throw new Exception('Unsupported operation: ' . $_GET['operation']);
				break;
		}
		header('Content-Type: application/json; charset=utf8');
		echo json_encode($rslt);
	}
	catch (Exception $e) {
		header($_SERVER["SERVER_PROTOCOL"] . ' 500 Server Error');
		header('Status:  500 Server Error');
		echo $e->getMessage();
	}
	die();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Siempre CMS - Content</title>
		<link rel="icon" type="image/png" href="images/favicon.ico" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="css/jquery-ui.min.css" />
		<link rel="stylesheet" href="css/jquery-ui.theme.min.css" />
		<link rel="stylesheet" href="css/jstree/style.min.css" />
		<link rel="stylesheet" href="css/main.css?ver=1.3.3" />
		<script type="text/javascript">
		<?php 
			// for the initial login help dialog
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
		$menu = 'content';
		require_once('../../admin-includes/admin.menu.php');
		?>
		
		<!-- Start of Login Dialog  -->  
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
		<div id="help-dialog" title="Siempre CMS Help - to close press the 'X' ->" style="display: none;" class="help"> 	
			<h2>Welcome to Siempre CMS ALPHA!</h2>
			<p>This is the alpha test version...</p>
		</div>  
		<!-- End of Help Dialog -->  
		
		<!-- Start of please wait div -->
		<div id="loading-dialog" title="Executing..." style="display: none;"> 
			<p><img src="images/ajax-loader.gif" alt="Please wait" /> Please Wait</p>
		</div> 
		
		<!-- Start of Select Template Dialog -->  
		<div id="select-template-dialog" title="Siempre CMS - Select a template type" style="display: none;" class="help"> 	
			<h2>Select a template!</h2>
			<p>You need to tell Siempre CMS what type of content you want to use here</p>
			<select id="select-template-ID" name="new-entity-type" style="display:block; float:left;">
				<option value="0" selected="selected">To load</option>			
			</select>	
		</div>  
		<!-- End of Select Template Help Dialog -->  
		
		<!-- Start of Media Picker Dialog -->  
		<div id="mediapicker-dialog" title="Siempre CMS - Select media" style="display: none;" class="help"> 	
			<h2>Select an image / document!</h2>
			<p>You need to tell Siempre CMS what type of content you want to use here</p>
			<div class="media-folders-container clearfix">
				<div id="mediafolders" class="media-folders"></div>
				<div class="media-folders">
					<a href="#" class="button green" id="create-folder" name="create-folder"><span class="icon-add"></span> Add folder</a>
					<input type="text" id="create-folder-name"/ placeholder="Folder name">
				</div>
			</div>
			<div class="fileupload">
				<!--status message will appear here-->
				<div class="status"></div>
				<!--image upload form-->
				<form id="pure-form" class="pure-form" action="file_upload.php" enctype="multipart/form-data" method="post">
					<input type="hidden" id="folder-path" name="folder-path"/>
					<div class="upload">
						<a onclick="select_file()" class="button">Choose a new file to upload...</a>
						<input class="button" id="image" type="file" name="image">
					</div>
					<!--image preview-->
					<img class="media-preview" src="" style="display:none">
					<br/>
					<input id="image-upload" class="button pure-button-primary" type="submit" value="Click to Upload!" style="display:none">
					<p id="image-upload-help-text" style="display:none">Your image / document is only uploaded once you click this</p>
				</form>
				<!--progress bar-->
				<div id="progress" class="progress" style="display:none">
					  <div class="bar"></div >
					  <div class="percent">0%</div >
				</div>
			</div>
			<div id="mediafiles" class="media-files"></div>
		</div>  
		<!-- End of Media Picker Dialog -->  
		
		<!-- Start of Node Picker Dialog -->  
		<div id="nodepicker-dialog" title="Siempre CMS - Select content node" style="display: none;" class="help"> 	
			<h2>Select a node!</h2>
			<p>You need to tell Siempre CMS which content item you want to use here</p>
			<div id="nodepicker-tree">
				<h1>Tree goes here</h1>
			</div>
		</div>  
		<!-- End of Node Picker Dialog -->  
		
		<!-- Start of Node Picker Dialog -->  
		<div id="linkpicker-dialog" class="linkpicker" title="Siempre CMS - Enter a link" style="display: none;"> 	
			<h2>Select a link!</h2>
			<p>EIther, manually enter a URL or select a content node, and provide the text for the link</p>
			<p>URL:</p>
			<input type="text" id="linkpicker-url" />
			<a href="#" class="button green" id="linkpicker-select"><span class="icon-action"></span> Select a content node</a>
			<p>Text to display:</p>
			<input type="text" id="linkpicker-text" />
			<p>Title:</p>
			<input type="text" id="linkpicker-title" />
			<p>Target:</p>
			<select id="linkpicker-target">
				<option value="none">None</option>
				<option value="new">New window</option>
			</select>
			
			</div>
		</div>  
		<!-- End of Node Picker Dialog --> 
		
		<!-- Start of Page Paths Dialog -->  
		<div id="pagepaths-dialog" title="Siempre CMS - Edit Page Paths" style="display: none;" class="help"> 	
			<h3>Page Paths</h3>
			<p>These are the URLs that this content is accessible from by end users (e.g. the web address)</p>
			<p>Changing this on a parent node will NOT change the paths for any content below this in the tree - think carefully before modifying!</p>
			Primary Page Path:<input type="text" id="content-primarypagepath" class="pagepath" name="content-primarypagepath"/>
			<input type="hidden" id="content-primarypagepathID" name="content-primarypagepathID"/>
			<p id="content-primarypagepath-none" class="warning">There is currently no page path / URL set for this page. This means it is not viewable on the website</p>
			<a href="#" class="button green" name="content-primarypagepath-update" id="content-primarypagepath-update"><span class="icon-publish"></span> Update primary path</a>
			<p class="note">For SEO reasons if you change this you should also create a redirect in the other page paths below for the old address</p>
			<hr/>
			<h4>Other Page Paths</h4>
			<div>
				<ul id="content-pagepaths" class="content-pagepaths"></ul>
			</div>
			<p class="note">Add a new path</p>
			<input type="text" id="content-primarypagepath-new" class="pagepath" name="content-primarypagepath-new"/>
			<select id="content-primarypagepath-new-type">
				<option value="2">301 Redirect</option>
				<option value="3">302 Redirect</option>
				<option value="1">Alias</option>
			</select>
			<a href="#" class="button green" id="content-pagepath-add" name="content-pagepath-add"><span class="icon-add"></span> Add page path</a>
		</div>  
		<!-- End of Page Paths Dialog -->  
		
		<div id="menuspacer" class="menuspacer">
		</div>
		<div class="main">
			<div id="tree-showhide-bar">
				<div id="tree-showhide">
					<span class="visually-hidden">Show tree</span>
				</div>
			</div>
			<div id="treecontainer" class="treecontainer">
				<div id="tree"></div>
			</div>
			<!-- Main Container -->
			<div id="edit-panel" class="edit-panel panel">
				<div id="edit-menu" class="edit-menu">
					
					<a href="#" class="button" id="save"><span class="icon-save"></span> Save and Publish</a>
					<!-- <a href="#" class="button green" id="publish"><span class="icon-publish"></span> Publish</a>
					<input type="button" class="button black" id="test" value="test">
					<input type="button" class="button purple" id="test2" value="test2">
					<input type="button" class="button red" id="test3" value="test3"> -->
					<select id="language" name="language" class="hidden">
						<option value="1" selected="selected">English (UK)</option>
						<option value="2">Français (FR)</option>
						<option value="3">Español (ES)</option>		
					</select> 
				</div>				
					<div>
						<div id="contentcontainer">
							<div class="edit-header" id="content-panel-header">
								<h3 id="content-panel-header-title">Content Admin Section - <span id="content-node-title"></span></h3>
							</div>
							<div id="content_tabs_container" class="accordion_content">	
								<div id="content_tabs_menu">
									<ul>
										<li><a href="#content-standard">Standard</a></li>
									</ul>
								</div> 
								<div id="content-standard" class="tab_content">
									<h2>Node Details</h2>
									<!-- this is to warn the user that someone else has made a change to the node in the mean while? -->
									<input type="hidden" id="content-lastupdated" name="content-lastupdated" />
									
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
											<h4 class="entity-title">Node ID</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<input class="node-details" type="text" id="node-id" name="node-id" readonly="readonly" />
										</div>
									</div>
									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Content ID</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<input class="node-details" type="text" id="content-id" name="content-id" readonly="readonly" />
										</div>
									</div>

									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Template ID</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<input class="node-details" type="text" id="template-id" name="template-id" readonly="readonly" />
										</div>
									</div>
									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Date Created</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<input class="node-details" type="text" id="content-created" name="content-created" readonly="readonly" />
										</div>
									</div>
									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Created By</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<input class="node-details" type="text" id="content-createdBy" name="content-createdBy" readonly="readonly" />
										</div>
									</div>
									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Last Updated</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<input class="node-details" type="text" id="content-lastUpdated" name="content-lastUpdated" readonly="readonly" />
										</div>
									</div>
									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Last Updated By</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<input class="node-details" type="text" id="content-lastUpdatedBy" name="content-lastUpdatedBy" readonly="readonly" />
										</div>
									</div>
									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Notes:</h4>
											<p class="entity-description">&nbsp;</p>
										</div>
										<div class="entity-data">
											<textarea class="node-notes" id="content-notes" name="content-notes" rows="6" cols="52"></textarea>
										</div>
									</div>
									<div class="entity clearfix">
										<div class="entity-info">
											<h4 class="entity-title">Do not cache:</h4>
											<p class="entity-description">This box should be checked for any pages that contain forms or content that can't be cached</p>
										</div>
										<div class="entity-data">
											<input id="content-nocache_checkbox" class="entity-checkbox" type="checkbox"/><input type="text" class="content-nocache-bool" id="content-nocache" name="content-nocache"/>
										</div>
									</div>
													
									<div id="page-path-container" class="page-path-container">
										<h3>Page Paths</h3>
										<div class="entity clearfix">
											<div class="entity-info"> 
												<h3>Primary Page Path: </h3>
											</div>
											<div class="entity-data">
												<span id="page-path"></span>
											</div>
										</div>
										<div class="entity clearfix">
											<div class="entity-info"> 
												<h3>Aliases / redirects</h3>
											</div>
											<div class="entity-data">
												<ul id="page-path-aliases">
													<li>none</li>
												</ul>
											</div>
										</div>
										<a href="#" class="button green" id="pagepaths"><span class="icon-publish"></span> Change</a>
									</div>
								</div>
							</div> <!-- EOF content_tabs_container -->
						</div>   <!-- end of content -->
					<br/>
				</div>
				<div id="bottom-menu" class="bottom-menu">
					
					<a href="#" class="button" id="save-2"><span class="icon-save"></span> Save and Publish</a>
					<!-- <a href="#" class="button green" id="publish"><span class="icon-publish"></span> Publish</a>
					<input type="button" class="button black" id="test" value="test">
					<input type="button" class="button purple" id="test2" value="test2">
					<input type="button" class="button red" id="test3" value="test3"> -->
					<select id="language-2" name="language" class="hidden">
						<option value="1" selected="selected">English (UK)</option>
						<option value="2">Français (FR)</option>
						<option value="3">Español (ES)</option>		
					</select> 
				</div>	
				
				<div style="clear: both;"></div>
				<div class="error" id="tree-errors">&nbsp;</div>
			</div>
			
			<div id="data">
				<div class="content code" style="display:none;"><textarea id="code" readonly="readonly"></textarea></div>
				<div class="content folder" style="display:none;"></div>
				<div class="content image" style="display:none; position:relative;">
					<img src="images/ajax-loader.gif" alt="" style="display:block; position:absolute; left:50%; top:50%; padding:0; max-height:5%; max-width:90%;" />
				</div>
				<div class="content default" style="text-align:center;">Select a node from the tree.</div>
			</div>
			
		</div> <!-- end of container -->
		<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui.min.js"></script> 
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>
		<script type="text/javascript" src="js/jstree/jstree.min.js"></script>
		<script type="text/javascript" src="js/jquery.form.min.js"></script>
		<script type="text/javascript" src="js/jquery.datetimepicker.js"></script>
 		<script type="text/javascript" src="js/jquery.ajaxq-sjm-0.0.7.js"></script> 
		<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript" src="js/noty/packaged/jquery.noty.packaged.min.js"></script>
		<script type="text/javascript" src="js/content.js?v=133"></script>
	</body>
</html>