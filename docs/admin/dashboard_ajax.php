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

$content = new content();

// TO DO - security later - e.g. is this user allowed 
$userID = $_SESSION['userID'];

if (isset($_POST['action'])) {
	////////////////////////////////////////////////////
	// getTemplate Get template from the content BY ID 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'getTemplate' ) {
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])
			&&isset($_POST['languageID'])
			&&is_int((int)$_POST['languageID'])
			) {		
			$foundRecord = $content->getTemplate((int)$_POST['nodeID'],(int)$_POST['languageID']);
			
			if($foundRecord!==true) {
				echo(json_encode(array('result' => false, 'msg' => 'No template available in the database')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $foundRecord, 'msg' => 'Template found!', 'results' => $content->template, 'sectionInstances' => $content->sectionInstances, 'tabs' => $content->tabs, 'sections' => $content->sections,)));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	////////////////////////////////////////////////////
	// loadContent Content BY ID
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'loadContent' ) {
		if(isset($_POST['nodeID'])
		&& is_int((int)$_POST['nodeID'])
		&& isset($_POST['languageID'])
		&& is_int((int)$_POST['languageID'])
		) {		
			$foundRecord = $content->getContent((int)$_POST['nodeID'],(int)$_POST['languageID']);
			
			if($foundRecord!==true) {
				echo(json_encode(array('result' => false, 'msg' => 'Error obtaining content from the database')));
				exit();
			}
			else {
				// echo(json_encode(array('result' => $foundRecord, 'msg' => 'Content found', 'results' => $content->results)));
				echo(json_encode(array('result' => $foundRecord, 'msg' => 'Content found', 'results' => $content->results)));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	////////////////////////////////////////////////////
	// saveContent Content BY ID 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'saveContent' ) {
		
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])
			&&isset($_POST['languageID'])
			&&is_int((int)$_POST['languageID'])
			&&isset($_POST['notes'])			
			) {		
		
			// TO DO encode the entity value pairs into an array to pass into the saveContent node
			
			$entityValuesArray = array();
			
			foreach($_POST as $name => $value) {
				if(substr($name, 0, 7 )=== "entity-") {
					$entityID = substr($name,7);
					if (is_numeric($entityID)) {
						$entityValuesArray[$entityID] = $value;
					}
					// TO DO ELSE?  Content could go walkies otherwise!
				}
			}
			// DEBUG ONLY		error_log(print_r($entityValuesArray,1));
			
			// Testing
			// $saveSuccess = true;
			$saveSuccess = $content->saveContent((int)$_POST['nodeID'], (int)$_POST['languageID'], $_POST['notes'], $entityValuesArray);
			
			if($saveSuccess!==true) {
				echo(json_encode(array('result' => false, 'msg' => 'Error storing content to the database')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Content stored')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// setTemplate Set template - used for new nodes 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'setTemplate' ) {
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])
			&&isset($_POST['templateID'])
			&&is_int((int)$_POST['templateID'])
			&&isset($_POST['languageID'])
			&&is_int((int)$_POST['languageID'])
			) {		
			$saveSuccess = $content->setTemplate((int)$_POST['nodeID'], (int)$_POST['templateID'],(int)$_POST['languageID']);
			
			if($saveSuccess!==true) {
				echo(json_encode(array('result' => false, 'msg' => 'Error setting template in the database')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Template set!')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	////////////////////////////////////////////////////
	// addPagePath - adds a url to a page 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'addPagePath' ) {
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])
			&&isset($_POST['path'])
			&&(strlen($_POST['path'])>0)
			//&&isset($_POST['languageID'])
			//&&is_int((int)$_POST['languageID'])
			) {		
			$pagePathID = $content->addPagePath((int)$_POST['nodeID'], $_POST['path'] /*,(int)$_POST['languageID']*/);
			
			if($pagePathID == 0) {
				echo(json_encode(array('result' => false, 'msg' => 'Error adding page path to the database')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $pagePathID, 'msg' => 'Page Path added')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	////////////////////////////////////////////////////
	// updatePrimaryPagePath - adds or updates the primary url to a page 
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'updatePrimaryPagePath' ) {
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])
			&&isset($_POST['path'])
			&&(strlen($_POST['path'])>0)
			//&&isset($_POST['languageID'])
			//&&is_int((int)$_POST['languageID'])
			) {		
			$result = $content->setPrimaryPagePath((int)$_POST['nodeID'], $_POST['path'] /*,(int)$_POST['languageID']*/);
			
			if($result == 0) {
				echo(json_encode(array('result' => false, 'msg' => 'Error updating primary page path')));
				exit();
			}
			else {
				echo(json_encode(array('result' => true, 'msg' => 'Primary Page Path updated')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	////////////////////////////////////////////////////
	// testCopyNode - add test nodes3
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'copyNodeTest' ) {
			$result = $content->copyNodeTest(40,1);
			
			if($result == 0) {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating test node')));
				exit();
			}
			else {
				echo(json_encode(array('result' => true, 'msg' => 'Node created')));
				exit();
			}
	}
	
	
	////////////////////////////////////////////////////
	// getTemplateList - get list of templates
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'getTemplatesList' ) {
	
			$template = new template();
			$result = $template->getTemplatesList();
			
			if($result == 0) {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating test node')));
				exit();
			}
			else {
				echo(json_encode(array('result' => true, 'msg' => 'Templates found', 'results' => $template->results)));
				exit();
			}
	}
}  
	

	// shouldn't get here
	echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
	exit();

?>