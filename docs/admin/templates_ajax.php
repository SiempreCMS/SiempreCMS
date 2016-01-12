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
require_once('../../admin-includes/admin.security_ajax.inc.php');

$template = new template();


// TO DO - security later - e.g. is this user allowed in templates section?
$userID = $_SESSION['userID'];

if (isset($_POST['action'])) {

	////////////////////////////////////////////////////
	// getTemplatesList 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'getTemplatesList' ) {
		$foundRecord = $template->getTemplatesList();
		
		if($foundRecord!==true) {
			echo(json_encode(array('result' => false, 'msg' => 'Error obtaining content from the database')));
			exit();
		}
		else {
			// echo(json_encode(array('result' => $foundRecord, 'msg' => 'Content found', 'results' => $content->results)));
			echo(json_encode(array('result' => $foundRecord, 'msg' => 'Templates found', 'results' => $template->results)));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// getTemplate 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'getTemplate' ) {
		
		if(isset($_POST['templateID'])&&is_int((int)$_POST['templateID'])) {		
			$templateID = $_POST['templateID'];
			$foundRecord = $template->getTemplate($templateID);
			// $foundRecord = true;
			
			// $results = array(	'ID' => 1, 
									// 'name' => 'Neil Young',
									// 'content' => 'BOB BOB',
									// 'lastUpdated' => '2013',
									// 'created' => '2013'
									// );
			if($foundRecord!==true) {
				echo(json_encode(array('result' => false, 'msg' => 'Error obtaining content from the database')));
				exit();
			}
			else {
				// echo(json_encode(array('result' => $foundRecord, 'msg' => 'Content found', 'results' => $content->results)));
				echo(json_encode(array('result' => $foundRecord, 'msg' => 'Templates found', 'tabs' => $template->tabs, 'entities' => $template->entities, 'sections' => $template->sections, 'results' => $template->results)));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed - missing template ID')));
			exit();
		}
		
	}
	
	
	////////////////////////////////////////////////////
	// saveTemplate 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'saveTemplate' ) {
		if(isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			&&isset($_POST['templateName'])
			&&isset($_POST['templateDescription'])
			&&isset($_POST['templateContent'])
		//	&&isset($_POST['entityDetails'])
			&&isset($_POST['useParentTemplate'])
			) {		
				
			// Testing
			//$saveSuccess = true;
			if(isset($_POST['entityDetails'])) {			
				$saveSuccess = $template->saveTemplate((int)$_POST['templateID'], $_POST['templateName'], $_POST['templateDescription'], $_POST['templateContent'], $_POST['entityDetails'], $_POST['useParentTemplate']);
			} else {
				$emptyArray =[];
				$saveSuccess = $template->saveTemplate((int)$_POST['templateID'], $_POST['templateName'], $_POST['templateDescription'], $_POST['templateContent'], $emptyArray, $_POST['useParentTemplate']);
			}
			
			if($saveSuccess!==true) {
				echo(json_encode(array('result' => false, 'msg' => 'Error storing template to the database')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Template updated')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// newTemplate 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'newTemplate' ) {
		if(isset($_POST['templateName'])) {		
		
			// TO DO validate content - str len etc
			
			// Testing
			//$saveSuccess = true;
			$saveSuccess = $template->newTemplate($_POST['templateName']);
			
			if($saveSuccess!==true) {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating template in the database')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Template created', 'newTemplateID' => $template->newTemplateID)));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// newTab  for Template 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'newTab' ) {
		if(isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			&&isset($_POST['tabName'])
			) {		
		
			// TO DO validate content - str len etc
			
			// Testing
			//$saveSuccess = true;
			$saveSuccess = $template->newTab($_POST['templateID'], $_POST['tabName']);
			
			if($saveSuccess!==false) {
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Template tab created')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating tab for template in the database')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// deleteTab  for Template 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'deleteTab' ) {
		if(isset($_POST['tabID'])
			&&is_int((int)$_POST['tabID'])
			) {		
			$deleteSuccess = $template->deleteTab($_POST['tabID']);
			
			if($deleteSuccess !== false) {
				echo(json_encode(array('result' => $deleteSuccess, 'msg' => 'Template tab deleted successfully')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error deleting tab from template in the database - you cannot delete a tab that has entities.')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// updateTabOrder  for Template 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'updateTabOrder' ) {
		if(isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			&&isset($_POST['sortOrder'])
			// &&is_int((int)$_POST['templateID'])
			) {		
			// Testing
			$updateSuccess = true;
			$updateSuccess = $template->updateTabOrder($_POST['templateID'], $_POST['sortOrder']);
			
			if($updateSuccess !== false) {
				echo(json_encode(array('result' => $updateSuccess, 'msg' => 'Template tabs ordering updated successfully')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error updating tab ordering.')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// newEntity  for Template Tab
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'newEntity' ) {
		if(isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			&&isset($_POST['tabID'])
			&&is_int((int)$_POST['tabID'])
			&&isset($_POST['entityName'])
			&&isset($_POST['entityType'])
			&&is_int((int)$_POST['entityType'])
			) {		
		
			// TO DO validate content - str len etc
			
			// Testing
			//$saveSuccess = true;
			$saveSuccess = $template->newEntity($_POST['templateID'], $_POST['tabID'], $_POST['entityName'], $_POST['entityType']);
			
			if($saveSuccess!==false) {
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Template entity created')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating new entity for template in the database')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// deleteEntity  for Template 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'deleteEntity' ) {
		if(isset($_POST['entityID'])
			&&is_int((int)$_POST['entityID'])
			) {		
			// Testing
		//	$deleteSuccess = true;
			$deleteSuccess = $template->deleteEntity((int)$_POST['entityID']);
			
			if($deleteSuccess !== false) {
				echo(json_encode(array('result' => $deleteSuccess, 'msg' => 'Entity deleted successfully')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error deleting entity from template in the database.')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// newSection  for Template Tab
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'newSection' ) {
		if(isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			&&isset($_POST['tabID'])
			&&is_int((int)$_POST['tabID'])
			) {		
		
			// Testing
			//$saveSuccess = true;
			$saveSuccess = $template->newSection($_POST['templateID'], $_POST['tabID']);
			
			if($saveSuccess!==false) {
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Template section created')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating new section for template in the database')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// addSEOFields to add SEO fields to the template
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'addSEOFields' ) {
		if(isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			) {		
		
			// Testing
			//$createSuccess = true;
			$createSuccess = $template->addSEOFields((int)$_POST['templateID']);
			
			if($createSuccess!==false) {
				echo(json_encode(array('result' => $createSuccess, 'msg' => 'Template section created')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating new section for template in the database')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// addSitemapFields to add sitemap fields to the template
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'addSitemapFields' ) {
		if(isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			) {		
		
			// Testing
			//$createSuccess = true;
			$createSuccess = $template->addSitemapFields((int)$_POST['templateID']);
			
			if($createSuccess!==false) {
				echo(json_encode(array('result' => $createSuccess, 'msg' => 'Template section created')));
				exit();
			}
			else {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating new section for template in the database')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
}  

	// shouldn't get here
	echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
	exit();

?>