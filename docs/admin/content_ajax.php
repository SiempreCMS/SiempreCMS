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
			&&isset($_POST['contentData'])
			&&isset($_POST['noCache'])
			&&is_int((int)$_POST['noCache'])
			) {		
		
			// encode the entity value pairs into an array to pass into the saveContent node
			// DEBUG ONLY		error_log(print_r($entityValuesArray,1));
			try {
				$entityValuesArrayNEW = array();
				$sectionInstancesArray = array();
				
				$contentData = json_decode($_POST['contentData'],true);
				$entityData = $contentData['entityData'];
				$entitySectionContent = $contentData['entitySectionContent'];
				$sectionInstances = $contentData['sectionInstances'];
				
				// go through normal entity data (e.g. not in a section) first and clean up the IDs
				$i = 1;
				foreach($entityData as $key => $value)
				{
					$i++;
					if(substr($value['name'], 0, 7 )=== "entity_") {
						$entityID = substr($value['name'], 7);
						if (is_numeric($entityID)) {
							$entityValuesArrayNEW[$i]['entityID'] = $entityID;
							$entityValuesArrayNEW[$i]['value'] = $value['value'];
							$entityValuesArrayNEW[$i]['sectionInstanceID'] = null;
							
					//		error_log("Added entity: ". $entityID . " value: " .  $value['value']);
						}
					}
				}
				
				// then the data entities in section instances
				foreach($entitySectionContent as $key => $value)
				{
					$i++;
					if(substr($value['name'], 0, 14) === "entitysecinst_") {
						$entityDividerPos = strpos($value['name'], '_', 15);
						$entityID = substr($value['name'],14, $entityDividerPos - 14);
						$sectionInstanceID = substr($value['name'], $entityDividerPos+1);
					//	error_log("Name: " . $value['name'] . " pos: " . $entityDividerPos . " . entityID: " . $entityID . " sectInstID: " . $sectionID); 
						if (is_numeric($entityID)) {
							$entityValuesArrayNEW[$i]['entityID'] = $entityID;
							$entityValuesArrayNEW[$i]['value'] = $value['value'];
							$entityValuesArrayNEW[$i]['sectionInstanceID'] = $sectionInstanceID;			
						//	error_log("Added section inst entity: ". $entityID . " value: " .  $value['value'] . " section " . $sectionInstanceID);	
						}
					}
				}
				
				// then the sectionInstances - note negative numbers are allowed as they are new section instances		
				foreach($sectionInstances as $key => $value)
				{
					if(substr($value['name'], 0, 12 )=== "sectioninst_") {
						$sectionInstanceID = substr($value['name'], 12);
						$sectionID = substr($value['sectionID'], 8); // section_XX
						if (is_numeric($sectionInstanceID)) {
							$sectionInstancesArray[$key]['sectionInstanceID'] = $sectionInstanceID;
							$sectionInstancesArray[$key]['sectionID'] = $sectionID;
							$sectionInstancesArray[$key]['sortOrder'] = $value['sortOrder'];		
				//			error_log("Added instance: ". $sectionInstanceID . " sectionID: " .  $sectionID . " sortorder:" . $value['sortOrder']);
						}
					}
				}
			} catch (Exception $e) {
				error_log('Throwing exception in the save Content AJAX call ' . $e->getMessage());
				echo(json_encode(array('result' => $saveSuccess, 'msg' => 'Content stored')));
				exit();
			}
			
						
			// Testing
			// $saveSuccess = $content->saveContent((int)$_REQUEST['nodeID'], (int)$_REQUEST['languageID'], $_REQUEST['notes'], $entityValuesArray);
			$saveSuccess = $content->saveContent((int)$_POST['nodeID'], (int)$_POST['languageID'], $_POST['notes'], (int)$_POST['noCache'], $entityValuesArrayNEW, $sectionInstancesArray);
			
	//		$saveSuccess = true;
			
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
			&&isset($_POST['type'])
			&&is_int((int)$_POST['type'])
			&&isset($_POST['path'])
			&&(strlen($_POST['path'])>0)
			//&&isset($_POST['languageID'])
			//&&is_int((int)$_POST['languageID'])
			) {		
			
			$exists = $content->checkPagePath((int)$_POST['nodeID'], $_POST['path']);
			if ($exists == false) {
			
				$pagePathID = $content->addPagePath((int)$_POST['nodeID'], (int)$_POST['type'], $_POST['path'] /*,(int)$_POST['languageID']*/);
				
				if($pagePathID == 0) {
					echo(json_encode(array('result' => false, 'msg' => 'Error adding page path to the database')));
					exit();
				}
				else {
					echo(json_encode(array('result' => $pagePathID, 'msg' => 'Page Path added')));
					exit();
				}
			} else {
				echo(json_encode(array('result' => false, 'msg' => 'Page Path already exists - ' . $exists)));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// deleteNOde - deletes a node
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'deleteNode' ) {
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])) {		
			
			$result = $content->deleteNode((int)$_POST['nodeID']);
			if($result == 0) {
				echo(json_encode(array('result' => false, 'msg' => 'Error deleting page')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $result, 'msg' => 'Page deleted')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	
	////////////////////////////////////////////////////
	// deletePagePath - deletes a page path
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'deletePagePath' ) {
		if(isset($_POST['pathID'])
			&&is_int((int)$_POST['pathID'])) {		
			
			$result = $content->deletePagePath((int)$_POST['pathID']);
			if($result == 0) {
				echo(json_encode(array('result' => false, 'msg' => 'Error deleting page path to the database')));
				exit();
			}
			else {
				echo(json_encode(array('result' => $result, 'msg' => 'Page Path deleted')));
				exit();
			}
			
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	////////////////////////////////////////////////////
	// getSuggestedPrimaryPagePath - gets a suggested primary url for a page - based on the parent
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'getSuggestedPrimaryPagePath' ) {
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])
			&&isset($_POST['nodeName'])
			) {		
			$path = $content->getSuggestedPrimaryPagePath((int)$_POST['nodeID'], str_replace(' ', '-', trim($_POST['nodeName'])));
			
			if($path == '') {
				echo(json_encode(array('result' => false, 'msg' => 'Error getting suggested primary page path')));
				exit();
			}
			else {
				echo(json_encode(array('result' => true, 'msg' => 'Primary Page Path sent', 'path' => $path)));
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
		//	&&(strlen($_POST['path'])>0)
			//&&isset($_POST['languageID'])
			//&&is_int((int)$_POST['languageID'])
			) 
			{		
			
			// first check we're not about to create a duplicate
			$result = $content->checkPagePath((int)$_POST['nodeID'], $_POST['path']);
			
			if($result === false) {
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
			else 
			{
				echo(json_encode(array('result' => $result, 'msg' => 'Primary Page Path cannot be updated - there is already a node with this page path')));
				exit();
			}
		}
		else {
			echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
			exit();
		}
	}
	
	////////////////////////////////////////////////////
	// getPrimaryPagePath - gets the primary url to a page from nodeID - used in pickers
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'getPrimaryPagePath' ) 
	{
		if(isset($_POST['nodeID'])
			&&is_int((int)$_POST['nodeID'])
			//&&isset($_POST['languageID'])
			//&&is_int((int)$_POST['languageID'])
			) 
		{		
		
			// first check we're not about to create a duplicate
			$content->getPagePaths((int)$_POST['nodeID'], 1);
			
			$paths = $content->results['pagepath'];
			
			$pathOut = "ERROR - no page path set";
			foreach($paths as $path)
			{
				if($path['type'] == 0)
				{
					$pathOut = '/' . $path['path'];
				}
			}

			echo(json_encode(array('result' => true, 'path' => $pathOut)));
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
	
	////////////////////////////////////////////////////
	// getFoldersFiles - get list of files and folders
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'getFilesAndFolders' ) {
		$folders = array();
		$files = array();
		$folderUp = '';
	
		if(isset($_POST['path']) && isset($_POST['path']) != ''){
			// check nothing naughty is going on
			$requestedPath = strtolower(trim($_POST['path']));
			if(substr($requestedPath, 0, 9) == '../media/') {
				$path = $requestedPath; 

				if(substr($requestedPath, 0, 9) == '../media/' && strlen($requestedPath) > 9) {
					$folderUp = dirname($path). '/';
				}
			} else {
				error_log("Illegal media path requested - " . $_POST['path']);
				$path = '../media/';
				$folderUp = '';
			}
		} else {
			$path = '../media/'; 
			$folderUp = '';
		}

		$results = scandir($path);

		if($results) {
			foreach ($results as $result) {
				if ($result === '.' or $result === '..') continue;

				if (is_dir($path . '/' . $result)) {
					//code to use if directory
					$folders[] = $path . $result . '/';
				} else {
					$files[] = $path . $result;
				}
			}
		}
		
		echo(json_encode(array('result' => true, 'msg' => 'Files found', 'files' => $files, 'folders' => $folders, 'folderup' => $folderUp, 'folderCurrent' => $path)));
		exit();
		
	}
	/* if ($_POST['action'] == 'getFilesAndFolders' ) {
		$folders = array();
		$files = array();
		$folderUp = '';
	
		if(isset($_POST['path']) && isset($_POST['path']) != ''){
			// check nothing naughty is going on
			$requestedPath = strtolower(trim($_POST['path']));
			if(substr($requestedPath, 0, 16) == '../media/images/') {
				$path = $requestedPath; 

				if(substr($requestedPath, 0, 16) == '../media/images/' && strlen($requestedPath) > 16) {
					$folderUp = dirname($path). '/';
				}
			} else {
				error_log("Illegal media path requested - " . $_POST['path']);
				$path = '../media/images/';
				$folderUp = '';
			}
		} else {
			$path = '../media/images/'; 
			$folderUp = '';
		}

		$results = scandir($path);

		if($results) {
			foreach ($results as $result) {
				if ($result === '.' or $result === '..') continue;

				if (is_dir($path . '/' . $result)) {
					//code to use if directory
					$folders[] = $path . $result . '/';
				} else {
					$files[] = $path . $result;
				}
			}
		}
		
		echo(json_encode(array('result' => true, 'msg' => 'Files found', 'files' => $files, 'folders' => $folders, 'folderup' => $folderUp, 'folderCurrent' => $path)));
		exit();
		
	}  */
	
	
	////////////////////////////////////////////////////
	// createFolder - createFolder
	////////////////////////////////////////////////////		
	if ($_POST['action'] == 'createFolder' ) {
			$folderPath = "";
			$folderName = "";
			
			// TO DO check it's in the right place
			if(isset($_POST['folderPath']) && isset($_POST['folderPath']) != ''){
				$folderPath = $_POST['folderPath'];
			} else {
				echo(json_encode(array('result' => false, 'msg' => 'Error - folder path is not valid')));
				exit();
			}
			
			// TO DO check it's a valid folder name
			if(isset($_POST['folderName']) && isset($_POST['folderName']) != ''){
				$folderName = $_POST['folderName'];
			} else {
				echo(json_encode(array('result' => false, 'msg' => 'Error - folder path is not valid')));
				exit();
			}
			
			// TO DO check folder name is valid, check it doesn't exist. Try catch wrap
			try {
				error_log("Creating - " . $folderPath . $folderName);
				if(mkdir($folderPath . $folderName))
				{
					$result = true;
				}
				else {
					$result = false; 
					error_log("Cannot create " . $folderPath . $folderName . " - Check the permissions on your media folder - " . $folderPath . $folderName);
				}
			}
			catch (Exception $e) {
					error_log("Cannot create " . $folderPath . $folderName . " - Check the permissions on your media folder");
					error_log("getCode: ". $e->getCode() . "\n");
					error_log("getMessage: ". $e->getMessage() . "\n");
					$result = false;
			}
			
			if($result == 0) {
				echo(json_encode(array('result' => false, 'msg' => 'Error creating new folder')));
				exit();
			}
			else {
				echo(json_encode(array('result' => true, 'msg' => 'Folder Created')));
				exit();
			}
	}
	
	
}  
	
	// shouldn't get here
	echo(json_encode(array('result' => false, 'msg' => 'AJAX Validation failed')));
	exit();

?>