<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 
//  Purpose:-      The main user content class, 


class content {

	//public definitions
	private static $db;   
    public $template;
	public $results;
	public $parentIDs;
	
	// TO DO template stuff - consider a different class
	public $entities;
	public $entityValues;
	public $sectionInstances;
	public $tabs;
	public $sections;
	public $templateID;
   
	function __construct() 
	{
		// Init variables
		self::$db = DBCxn::Get();	
		
		$this->tabs = [];
		$this->entities =[];
	}
  
  
	function getTemplate($nodeID, $languageID) 
  	{
		// TO DO - this should be a get contentID function - this is messy.
		// Gets the content node details from the DB
 		$this->nodeID = $nodeID;
		
		// 1. get the latest content ID for this node and language 
		// TO DO have a think about the content ID use here - for example if you have published version with 4 sec instances but a later version with 5 which should it load? depends on the content you want - e.g. I think the section instances should be built from the content not the getTemplate method.
  		try {
			$db =  self::$db;
			
			$sql = " SELECT c.ID, c.templateID 
						FROM cms_content AS c 
						WHERE c.nodeID = :nodeID AND languageID = :languageID 
						ORDER BY c.ID DESC;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);			
			$query->execute();

			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$contentID = $result['ID']; 
				$this->templateID = $result['templateID'];
			} else {
				// TO DO -- handle this better?
				error_log('Error getting content ID for: nodeID = ' . $nodeID);
				
				// TODO improve error handling
				return false;
			}
			
			// 2. Get the tabs for this template
			$sql = "SELECT tt.ID AS tabID, tt.name AS tab_name
					FROM `cms_template` AS t 
					INNER JOIN `cms_template_tab` AS tt 
					  ON tt.templateID = t.ID
					WHERE t.ID = :templateID 
					ORDER BY tt.order, tt.ID;";
							
			$query = $db->prepare($sql);
			$query->bindParam(':templateID', $this->templateID, PDO::PARAM_INT);
			$query->execute();
			
			if($query->rowCount() !== 0) {	
				foreach ($query as $key => $result) {	
					$tab['tabID'] = $result['tabID'];
					$tab['tab_name'] = $result['tab_name'];
					$this->tabs[] = $tab;
				}
			}
			
			// 3. Get the sections for each tab
			$sql = "SELECT s.ID AS sectionID, s.template_tabID 
						FROM `cms_section` AS s
						INNER JOIN cms_template_tab AS tt
						ON tt.ID = s.template_tabID 
						WHERE tt.templateID = :templateID;";
							
			$query = $db->prepare($sql);
			$query->bindParam(':templateID', $this->templateID, PDO::PARAM_INT);
			$query->execute();
			
			if($query->rowCount() !== 0) {	
				foreach ($query as $key => $result) {
					$this->sections[$result['sectionID']]['sectionID'] = $result['sectionID'];				
					$this->sections[$result['sectionID']]['tabID'] = $result['template_tabID'];
				}
			}
			
			// 3. get the entities for this content ID
			$sql = "	 
				SELECT e.ID as entityID, e.name AS entity_name, e.title AS entity_title, e.description AS entity_description, e.entity_type, tt.ID AS tabID, tt.name AS tab_name, e.sectionID
					FROM `cms_template` AS t 
					INNER JOIN `cms_content` AS c
					  ON c.templateID = t.ID
					INNER JOIN `cms_entity` AS e
					  ON e.templateID = t.ID
					INNER JOIN `cms_template_tab` AS tt 
					  ON tt.ID = e.template_tabID
					WHERE c.ID = :contentID 
					ORDER BY tt.ID, e.sort_order;";
					
	
			$query = $db->prepare($sql);
			$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
		//	$query->bindParam(':version', $version);
			//print $sql;
			$query->execute();
		
			$sectionIDStr = '';
			
			// If we have results
			if($query->rowCount() != 0) {	
				foreach ($query as $key => $result) {
				//	$ID = $result['ID'];
					$entity['entityID'] = $result['entityID'];
					$entity['entity_name'] = $result['entity_name'];
					$entity['entity_title'] = $result['entity_title'];
					$entity['entity_description'] = $result['entity_description'];
					$entity['entity_type'] = $result['entity_type'];
					$entity['tabID'] = $result['tabID'];
					$entity['tab_name'] = $result['tab_name'];
					$entity['sectionID'] = $result['sectionID'];
					
					// push array on to array
					$this->template[] = $entity;
									
					// build up a string list of section IDs so we can get the instances of these repeating sections for them later
					// to do only add if not in the string already?
					if($result['sectionID'] !== null) {
						$sectionIDStr .= ', ' . $result['sectionID'];
					}
				}
				// remove the first ', '  actually dont - it stops the next query working if there are no entries!
				// $sectionIDStr = substr($sectionIDStr, 2);
				
				// 4. Get section Instances - now we have all the entities associated with this content ID we can get the details of the section
				// instances (avoiding doing too many joins but you could have got this in the first query). 
				// if the sectionIDstr is not empty then we have sections - get instance IDs to build up the screen	
				if($sectionIDStr !== '') {
					// error_log('Get section instances for content ID ' . $contentID. ' - sectionIDs:' . $sectionIDStr);
					// remove the first  ", "
					$sectionIDStr = substr($sectionIDStr, 2);
					$sql = "SELECT s.ID AS sectionID, si.ID AS section_instanceID, si.sort_order 
								FROM cms_section AS s 
								INNER JOIN cms_section_instance AS si ON si.sectionID = s.ID 
								WHERE s.ID IN (" . $sectionIDStr . ") AND si.contentID = :contentID
								ORDER BY si.sort_order;";
							
					//error_log($sql);
					$query = $db->prepare($sql);
					$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
					$query->execute();
					
					if($query->rowCount() !== 0) {	
						foreach ($query as $key => $result) {						
							$this->sectionInstances[$key]['sectionInstanceID'] = $result['section_instanceID'];
							$this->sectionInstances[$key]['sectionID'] = $result['sectionID'];
							$this->sectionInstances[$key]['sortOrder'] = $result['sort_order'];
						}
					//	error_log(print_r($this->sectionInstances,1));
					}
				}
			} // else {
				// throw an exception
			//	throw new Exception('Error getting template ' + $contentID + 'from database'); 
			//}
		} catch (Exception $e) {
			error_log('Throwing exception in the getTemplate');
			error_log('Content ID - ' . $contentID . ' has thrown exception in the get template details ' . $e->getMessage());
			return false;
		}
		// close the conn
		$db = null;
		
		return true;
	}
	
  
    function getContent($nodeID, $languageID) 
  	{
  		// Gets the content node details from the DB
		//	$this->contentID = $nodeID;
		
		// 1. Get the latest content ID and last updated etc data
		// TO DO - think about if this should be the published version or last saved?
		try {
			$db =  self::$db;
			
			$sql = "SELECT c.`ID`, c.`nodeID`, c.`created`, CONCAT(u1.foreName, ' ',u1.lastName, ' (', u1.ID, ')') AS createdBy, c.`lastUpdated`, CONCAT(u2.foreName, ' ',u2.lastName, ' (', u2.ID, ')') AS lastUpdatedBy, c.`templateID`, IFNULL(c.`notes`, 'No notes') AS notes, c.`version`, c.noCache AS noCache   
					FROM cms_content AS c
					LEFT OUTER JOIN cms_user AS u1 
						ON c.createdBy = u1.ID
					LEFT OUTER JOIN cms_user AS u2
						ON c.lastUpdatedBy = u2.ID
					WHERE nodeID = :nodeID 
					  AND languageID = :languageID 
					ORDER BY ID DESC
					LIMIT 1
					;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$this->results['nodeID'] = $result['nodeID'];
				$this->results['contentID'] = $result['ID'];
				$this->results['templateID'] = $result['templateID'];
				$this->results['notes'] = $result['notes'];
				$contentID = $result['ID'];
				$this->results['lastUpdated'] = $result['lastUpdated'];
				$this->results['lastUpdatedBy'] = $result['lastUpdatedBy'];
				$this->results['created'] = $result['created']; 
				$this->results['createdBy'] = $result['createdBy']; 
				$this->results['noCache'] = $result['noCache']; 
				
				// error_log('Getting content info for node :' . $nodeID . ' and content :' . $contentID . ' from database'); 
			} else {
				// throw an exception
				throw new Exception('Error getting standard content info for node :' . $nodeID . ' and from database'); 
			}
		} catch (Exception $e) {
			error_log('Throwing exception in the getContent for getting standard content');
			error_log('Node ID - ' . $nodeID . ' has thrown exception in part of the get content details ' . $e->getMessage());
			return false;
		}

		// 2, Get the page paths for the node  -- TO DO will language be an issue here?
		$this->getPagePaths($nodeID, $languageID);
			
		// 3. Now get the data
  		try {
			$db =  self::$db;
			
			$sql = "	-- int
						SELECT e.ID AS entityID, e.name, entity_type, value, si.sectionID, section_instanceID
						FROM `cms_entity` AS e
						RIGHT OUTER JOIN cms_entity_value_int eint ON eint.entityID = e.ID
						LEFT OUTER JOIN cms_section_instance si ON si.ID = eint.section_instanceID
						WHERE e.entity_type = 1  AND eint.contentID = :contentID 
						UNION
						-- money
						SELECT e.ID AS entityID, e.name, entity_type, value, si.sectionID, section_instanceID 
						FROM `cms_entity` AS e
						RIGHT OUTER JOIN cms_entity_value_money em ON em.entityID = e.ID
						LEFT OUTER JOIN cms_section_instance si ON si.ID = em.section_instanceID
						WHERE e.entity_type = 2  AND em.contentID = :contentID
						UNION
						-- short text
						SELECT e.ID AS entityID, e.name, entity_type, value, si.sectionID, section_instanceID 
						FROM `cms_entity` AS e
						RIGHT OUTER JOIN cms_entity_value_shorttext est ON est.entityID = e.ID
						LEFT OUTER JOIN cms_section_instance si ON si.ID = est.section_instanceID
						WHERE (e.entity_type = 3 OR e.entity_type = 8) AND est.contentID = :contentID
						UNION
						-- long text (3) && rich text (4) && nodespicker (10) && images / media (11)
						SELECT e.ID AS entityID, e.name, entity_type, value, si.sectionID, section_instanceID 
						FROM `cms_entity` AS e
						RIGHT OUTER JOIN cms_entity_value_longtext elt ON elt.entityID = e.ID
						LEFT OUTER JOIN cms_section_instance si ON si.ID = elt.section_instanceID
						WHERE (e.entity_type = 4 OR e.entity_type = 5 OR e.entity_type = 10 OR e.entity_type = 11) AND elt.contentID = :contentID
						UNION
						-- date
						SELECT e.ID AS entityID, e.name, entity_type, value, si.sectionID, section_instanceID 
						FROM `cms_entity` AS e
						RIGHT OUTER JOIN cms_entity_value_date elt ON elt.entityID = e.ID
						LEFT OUTER JOIN cms_section_instance si ON si.ID = elt.section_instanceID
						WHERE (e.entity_type = 6 OR e.entity_type = 7) AND elt.contentID = :contentID
						;"; 
			
			$query = $db->prepare($sql);
			$query->bindParam(':contentID', $contentID);
			
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
 			
				foreach ($query as $key => $result) {
				// TO DO change this to entity values
				//	$ID = $result['ID'];
					$this->results['entities'][$key]['entityID'] = $result['entityID'];
					$this->results['entities'][$key]['name'] = $result['name'];
					$this->results['entities'][$key]['entity_type'] = $result['entity_type']; // to do is this needed?
					$this->results['entities'][$key]['value'] = $result['value'];
					$this->results['entities'][$key]['sectionID'] = $result['sectionID'];
					$this->results['entities'][$key]['sectionInstanceID'] = $result['section_instanceID'];
				}
			} else {
				// thrown an exception
				error_log('No content for contentID:' . $contentID . ' from database');
			//	throw new Exception('Error getting content ' + $contentID + 'from database'); 
			}
			//error_log('User ID - ' . $_SESSION['customerID']);
		}
		catch (PDOException $e) {
		error_log('Caught PDO exception in the getContent');
			error_log('Node ID - ' . $nodeID . ' has thrown exception in part 3 of the get content details getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}		
		catch (Exception $e) {
			error_log('Caught general exception in the getContent');
			error_log('Node ID - ' . $nodeID . ' has thrown exception in part 3 the get content details getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}
		
		// close the conn
		$db = null;
		
		return true;
	}
	
	
	function getPagePaths($nodeID, $languageID)
	{
		try {
			$db =  self::$db;
			
			$sql = "SELECT `ID`, `path`, `nodeID`, `type`
					FROM cms_page_path
						WHERE nodeID = :nodeID 
						ORDER BY ID DESC
					;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
		//	$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);
			$query->execute();
		
			// If we have a row
			$pagepath = [];
			$this->results['pagepath'] = $pagepath;
			
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
				//	$ID = $result['ID'];
					$pagepath = [];
					$pagepath['pagepathID'] = $result['ID'];
					$pagepath['path'] = $result['path'];
					$pagepath['type'] = $result['type'];
					
					$this->results['pagepath'][] = $pagepath;
				}	
			} 
		}
		catch (Exception $e) 
		{
			error_log('Throwing exception in the getContent for getting page paths');
			error_log('Node ID - ' . $nodeID . ' has thrown exception in part 2 of the get content details ' . $e->getMessage());
			return false;
		}
		
		return true;
	}


	
	function saveContent($nodeID, $languageID, $notes, $noCache, $entityArray, $sectionInstances) 
  	{
		// Returned by the insert - TODO remove?
		$contentID = 0;
		$createdBy = 0;
		$created = 0;
		$userID = $_SESSION['userID'];
				
		// Stores the content to the DB
		// TO DO store the old version to the version tables. 
		
		$db =  DBCxn::Get();
	
		// TO DO Validate that the user has access to this node? 
		try 
		{	
			$db->beginTransaction();
			
			// 1A. get the last versionID and template (perhaps this is changeable?)
			$sql = "SELECT version AS versionID, created, createdBy  
							FROM `cms_content` AS c 
							WHERE c.nodeID = :nodeID
							AND c.languageID = :languageID
							ORDER BY ID DESC		
							LIMIT 1							
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$version = $result['versionID'] + 1;
				$created = $result['created'];
				$createdBy = $result['createdBy'];
			} else {
				// new
				$version = 1;
				$createdBy = $userID;
			}
			
			// 1B. get the templateID (perhaps this is changeable?)
			$sql = "SELECT templateID  
							FROM `cms_content` AS c 
							WHERE c.nodeID = :nodeID
							ORDER BY ID DESC		
							LIMIT 1							
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$templateID = $result['templateID']; // TO DO actually get this from the admin interface?! Is it possible to change this ever?
			} else {
				// TO DO -- I'm guessing this would be new content. Check that this is right? TemplateID would then be passed in by the admin interface?
				// error_log('getting here: nodeID = ' . $nodeID);
			}
				
			// 2a - set the published flag to false on all other content entries
			// TO DO - eventually the publish and save functions will be separate so this will need to be moved to the publish event
			$query = $db->prepare("UPDATE cms_content 
									SET published = 0 
									WHERE nodeID = :nodeID AND languageID = :languageID
									;");
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);
			
			$query->execute();
			
			// 2b. - get the parent node IDs to reduce load on page loads we store a comma separated list against the content of what the page path in node IDs are (for main nav etc to look up the tree)
			$this->parentIDs = implode(', ', $this->getParentNodes($nodeID));  

			
			// 2c. - store the record in the content table to get a contentID for later use
			// TO DO published is always true ATM
			$query = $db->prepare("INSERT INTO cms_content (nodeID, version, created, createdBy, lastUpdated, lastUpdatedBy, notes, templateID, published, languageID, noCache, parentIDs) 
									VALUES (:nodeID, :version, :created, :createdBy, NOW(), :lastUpdatedBy, :notes, :templateID, TRUE, :languageID, :noCache, :parentIDs)
									;");
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':version', $version, PDO::PARAM_INT);
			$query->bindParam(':created', $created, PDO::PARAM_STR);
			$query->bindParam(':createdBy', $createdBy, PDO::PARAM_INT);
			$query->bindParam(':lastUpdatedBy', $userID, PDO::PARAM_INT);
			$query->bindParam(':notes', $notes, PDO::PARAM_STR);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);
			$query->bindParam(':noCache', $noCache, PDO::PARAM_INT);
			$query->bindParam(':parentIDs', $this->parentIDs, PDO::PARAM_STR);
			
			$query->execute();
				
			$contentID = $db->lastInsertId() ;
			
			// 3. get the template - so we know where to store the content to
			self::getTemplate($nodeID, $languageID);
			
			// 4 Store the content
			// 4a - Loop through the section instances (with sort order) and store these to the table with the content ID and get hte new Section Instance ID so we know where to store this too later. 
			foreach ($sectionInstances as $key => $value) {
				// For each row.. insert and get last insert id and add this to the array
				$query = $db->prepare("INSERT INTO `cms_section_instance`
											(`contentID`, `sectionID`, `sort_order`) 			
											VALUES (:contentID, :sectionID, :sort_order)
									;");
									
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':sectionID', $value['sectionID'], PDO::PARAM_INT);
				$query->bindParam(':sort_order', $value['sortOrder'], PDO::PARAM_INT);
		//		error_log("INSERTING: contentID: " . $contentID. " sectionID: " . $value['sectionID'] . " sort: " . $value['sortOrder']);
				
				$query->execute();
				
				$newSectionInstanceID = $db->lastInsertId() ;
				$sectionInstances[$key]['newSectionInstanceID'] = $newSectionInstanceID;
			}
					
			// 4b. match each entity to a type
			$intArray = array();
			$moneyArray = array();
			$shortTextArray = array();
			$longTextArray = array();
			$richTextArray = array();
			$dateArray = array();
			$nodeArray = array();
			$mediaArray = array();
			
			// used to store any nodes that will need to be pulled back in the content (e.g. refed in the content pickers)
			$nodeDependencyArray = array();
			
			foreach ($entityArray as $key => $value) {
				// TO DO - make this generic so that we can map multiple types in some config setting somewhere (or perhaps make that config always 
				// choose the raw underlying data type.. probably that. 
				
				$sectionInstanceID = null;
				// look up the new section ID
				if ($value['sectionInstanceID'] !== null) {
					// find the new ID
					foreach($sectionInstances as $siKey => $siValue) {
						if ($siValue['sectionInstanceID'] === $value['sectionInstanceID']) 
						{
						//	error_log("Found - " . $siValue['newSectionInstanceID'] . " for : " . $siValue['sectionInstanceID'] . " : " . $value['sectionInstanceID']);
							$sectionInstanceID = $siValue['newSectionInstanceID'];
							// break;
						}
					}
					// TO DO handle!
				} 
				
				// Get the entity type by looking it up from the template
				$entityType = false;
				foreach($this->template as $tkey => $tvalue) {
					if ($tvalue['entityID'] == $value['entityID']) {
						$entityType = $tvalue['entity_type'];
						break;
					}
				}
				
				if ($entityType === false) {
					// Throw exception
					error_log('Entity ' . $tvalue['entityID'] . ' NOT found in the template');
					throw new Exception('Entity ' . $tvalue['entityID'] . ' NOT found in the template');
				}
				
				
				switch ($entityType) {
					case 1:
						$intArray[] = array(		'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
						break;
					case 2:
						$moneyArray[] = array(		'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
						break;
					case 3:
						$shortTextArray[] = array(	'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
						break;
					case 4:
						$longTextArray[] = array(	'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
						break;
					case 5:
						$richTextArray[] = array(	'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
						break;
					case 6:
						$dateArray[] = array(	'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
					case 7:
						$dateArray[] = array(	'entityID' => $value['entityID'],
													'value' => $value['value'], 
													'sectionInstanceID' => $sectionInstanceID);
						break;
					case 8:
						// bug with empty newly created fields store " " instead of false
						$boolValue = 'false';
						if ($value['value'] == 'true' || $value['value'] == 'TRUE' ) {
							$boolValue = true;
						}
						$shortTextArray[] = array(	'entityID' => $value['entityID'],
													'value' => $boolValue,
													'sectionInstanceID' => $sectionInstanceID);
						break;
					case 10:
						$nodeArray[] = array(	'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
						// decode the JSON and store all nodes to the dependencies 
						// (so that the content is available when the page is built)							
						$jsonObj = json_decode($value['value'], true);
		
						foreach($jsonObj as $key => $node)
						{
							$nodeDependencyArray[$node['nodeID']] = $node['nodeID'];
							if(count($node['children']) > 0)
							{
								$this->getChildNodeDependencies($node['children'], $nodeDependencyArray);
							}
						}		
						break;
					case 11:
						$mediaArray[] = array(	'entityID' => $value['entityID'],
													'value' => $value['value'],
													'sectionInstanceID' => $sectionInstanceID);
						break;
					default:
						//  add it to the blob...  TO DO 
						// FOr now just going into long text
				}
			/* for debugging
				error_log("INT:");
				error_log(print_r($intArray,1));
				error_log("MONEY:");
				error_log(print_r($moneyArray,1));
				error_log("SHORTTEXT:");
				error_log(print_r($shortTextArray,1));
				error_log("LONGTEXT:");
				error_log(print_r($longTextArray,1)); */
			}
					

			// 4. store each bit of content data into the relevant table  
			
			// 4.1 // entity_value_int 
			// TO DO review duplicate key update here
			foreach ($intArray as $key => $value) {
				$intValue = intval($value['value']);
				
			//	error_log("key = " . $value['entityID'] . " - " . $value['sectionInstanceID'] . " - Value=: " . $intValue);
				$query = $db->prepare("INSERT INTO cms_entity_value_int 
											(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $intValue, PDO::PARAM_INT);		
				$query->execute();
			} 
			
			// 4.2 // entity_value_money 		
			foreach ($moneyArray as $key => $value) {
				//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_money 
										(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);		
				$query->execute();
			} 
			
			// 4.3 // entity_value_shorttext 
			foreach ($shortTextArray as $key => $value) {
				//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_shorttext 
											(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR, 255);		
				$query->execute();
			} 
			
			// 4.4 //	entity_value_longtext 
			foreach ($longTextArray as $key => $value) {
				//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_longtext 
											(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);		
				$query->execute();
			} 
			
			// 4.5 //	entity_value_richtext stored in longtext
			foreach ($richTextArray as $key => $value) {
				//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_longtext 
											(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);		
				$query->execute();
			} 
			
			// 4.6  & 4.7 //	entity_value_date & entity_value_date_time
			foreach ($dateArray as $key => $value) {
				//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_date 
											(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);
			// not sure what to do with blank dates?
			//	if($value['value'] == "0000-00-00 00:00:00" || $value['value'] == "0000-00-00") {
			//		$query->bindValue(':value', null, PDO::PARAM_INT);	
			//	} else {
			//		$query->bindParam(':value', $value['value'], PDO::PARAM_STR);	
			//	}
				
					
				$query->execute();
			}
			
			// 4.10 //	entity_value_nodepicker JSON
			foreach ($nodeArray as $key => $value) {
			//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_longtext
											(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);		
				$query->execute();
			}
			
			// 4.11 //	entity_value_media
			foreach ($mediaArray as $key => $value) {
			//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_longtext
											(entityID, contentID, section_instanceID, value) 
										VALUES (:entityID, :contentID, :section_instanceID, :value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':section_instanceID', $value['sectionInstanceID'], PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);		
				$query->execute();
			}
					
			$db->commit();
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Saving Content failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		// close the conn
		$db = null;
		
		// update the dependencies table
		// TO DO updateDependencies($nodeID)
		$this->updateDependencies($nodeID, $nodeDependencyArray);

		// clear all cache files on publish - quick and dirty
		$cache = new CMSCache();
		$cache->dropAllCache();
		
		return true;	
	}
	
	function getChildNodeDependencies($childNodes, &$nodeDependencyArray)
	{
		foreach($childNodes as $key => $childNode)
		{
			$nodeDependencyArray[$childNode['nodeID']] = $childNode['nodeID'];
			if($childNode['children'] > 0)
			{
				$this->getChildNodeDependencies($childNode['children'], $nodeDependencyArray);
			}
		}
	}
	
	
	function copyNodeTest($nodeID, $languageID) 
  	{
		// TO DO think about where this node goes $targetParent node or some such

		$contentID = 0;
		$newNodeID = 0;
		
		// HACK TO DO 
		$languageID = 1;
		
		// Copies a node - HACK version TO DO as I just needed to bulk the CMS up loads to test slow parent pages for now but this is a required function
		
		$db =  DBCxn::Get();
	
		// TO DO Validate that the user has access to this node? 
		try 
		{	
			$db->beginTransaction();
			
			// 1A. get the last versionID and template (perhaps this is changeable?)
			$sql = "SELECT version AS versionID  
							FROM `cms_content` AS c 
							WHERE c.nodeID = :nodeID
							AND c.languageID = :languageID
							ORDER BY ID DESC		
							LIMIT 1							
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$version = $result['versionID'] + 1;
			} else {
				// new
				$version = 1;
			}
			
			// 1B. get the templateID (perhaps this is changeable?)
			$sql = "SELECT templateID  
							FROM `cms_content` AS c 
							WHERE c.nodeID = :nodeID
							ORDER BY ID DESC		
							LIMIT 1							
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$templateID = $result['templateID']; 
			} else {
				// TO DO -- I'm guessing this would be new content. Check that this is right? TemplateID would then be passed in by the admin interface?
				error_log('getting here: nodeID = ' . $nodeID);
			}
				
			// 2a. Create a new node 
			$parentID = 26; // TO DO get this for the source node
			$right = 37; // not sure about this
			$level = 3;
			$title = "TN" . md5(uniqid(rand(), TRUE)); // TO DO TO DO TO DO
			
			// TO DO - this is hardcoded to get the values from the last news node..
			$parentNodeID = 26;  // hardcoded TO DO
			
			$sql = "SELECT `ID`, `pid`, `pos`, `lft`, `rgt`, `lvl`
							FROM `cms_tree_struct` AS t 
							WHERE t.pid = :parentNodeID
							ORDER BY ID DESC		
							LIMIT 1							
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':parentNodeID', $parentNodeID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$position = $result['pos'] + 1;
				$left = $result['ID'];
			} else {
				// new
				$version = 1;
			}
			
			// TO DO - JSTree upgrade - you still need to do seperate inserts for name and type :)
			$query = $db->prepare("INSERT INTO cms_tree_struct (`pid`, `pos`, `lft`, `rgt`, `lvl`, `title`, `type`) 
									VALUES (:parent_ID, :position, :left, :right, :level, :title,  'default')
									;");
			$query->bindParam(':parent_ID', $parentID, PDO::PARAM_INT);
			$query->bindParam(':position', $position, PDO::PARAM_INT);
			$query->bindParam(':left', $left, PDO::PARAM_INT);
			$query->bindParam(':right', $right, PDO::PARAM_INT);
			$query->bindParam(':level', $level, PDO::PARAM_INT);
			$query->bindParam(':title', $title, PDO::PARAM_STR);
			
			$query->execute();
				
			$newNodeID = $db->lastInsertId() ;
			
			
			// 2b. - store the record in the content table to get a contentID for later use
			// TO DO published is always true ATM
			$version = 1;
			$notes = 'TN' . md5(uniqid(rand(), TRUE));
			$query = $db->prepare("INSERT INTO cms_content (nodeID, version, created, lastUpdated, notes, templateID, published, languageID) 
									VALUES (:nodeID, :version, NOW(), NOW(), :notes, :templateID, TRUE, :languageID)
									;");
			$query->bindParam(':nodeID', $newNodeID, PDO::PARAM_INT);
			$query->bindParam(':version', $version, PDO::PARAM_INT);
			$query->bindParam(':notes', $notes, PDO::PARAM_STR);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $languageID, PDO::PARAM_INT);
			
			$query->execute();
				
			$newContentID = $db->lastInsertId() ;
			
			// 4. Copy and store each bit of content data into the relevant table  
			
			// Headline 1
			$entityID = 108;
			$value = md5(uniqid(rand(), TRUE));
			$query = $db->prepare("INSERT INTO cms_entity_value_shorttext (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $entityID, PDO::PARAM_INT);
				$query->bindParam(':contentID', $newContentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value, PDO::PARAM_STR, 255);		
				$query->execute();
			
			// Headline 2
			$entityID = 109;
			$value = md5(uniqid(rand(), TRUE));
			$query = $db->prepare("INSERT INTO cms_entity_value_shorttext (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $entityID, PDO::PARAM_INT);
				$query->bindParam(':contentID', $newContentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value, PDO::PARAM_STR, 255);		
				$query->execute();


			// Headline 3
			$entityID = 111;
			$value = md5(uniqid(rand(), TRUE));
			$query = $db->prepare("INSERT INTO cms_entity_value_shorttext (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $entityID, PDO::PARAM_INT);
				$query->bindParam(':contentID', $newContentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value, PDO::PARAM_STR, 255);		
				$query->execute();
			
			// Content
			$entityID = 110;
			$value = 'Here is a bunch of dummy text to do what you like with. Nick Hall ‏@NickXHall  2h
must be an ABC sampling week or something MT @suttonnick: Monday\'s Sun front page - "Exclusive - Buy a killer pit bull for £50"';
			$query = $db->prepare("INSERT INTO cms_entity_value_longtext (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $entityID, PDO::PARAM_INT);
				$query->bindParam(':contentID', $newContentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value, PDO::PARAM_STR);		
				$query->execute();
		
		
			// Page Path
			$query = $db->prepare("INSERT INTO cms_page_path (path, nodeID, type) 
										VALUES (:path,:nodeID,0)
										;");
				$query->bindParam(':nodeID', $newNodeID, PDO::PARAM_INT);
				$query->bindParam(':path', $title, PDO::PARAM_STR);		
				$query->execute();
				
				
	/*		// 4.1 // entity_value_int 
			foreach ($intArray as $key => $value) {
			//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_int (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_INT);		
				$query->execute();
			} 
			
			// 4.2 // entity_value_money 		
			foreach ($moneyArray as $key => $value) {
			//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_money (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);		
				$query->execute();
			} 
			
			// 4.3 // entity_value_shorttext 
			foreach ($shortTextArray as $key => $value) {
			//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_shorttext (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR, 255);		
				$query->execute();
			} 
			
			// 4.4 //	entity_value_longtext 
			foreach ($longTextArray as $key => $value) {
			//	error_log("key = " . $key . " - " . $value);
				$query = $db->prepare("INSERT INTO cms_entity_value_longtext (entityID, contentID, value) 
										VALUES (:entityID,:contentID,:value)
										ON DUPLICATE KEY UPDATE value=:value;");
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':contentID', $contentID, PDO::PARAM_INT);
				$query->bindParam(':value', $value['value'], PDO::PARAM_STR);		
				$query->execute();
			} 
		
		*/
			$db->commit();
/// $db->rollBack();
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Copy Node in copy Content failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		// close the conn
		$db = null;
		
		// update the dependencies table
		// TO DO updateDependencies($nodeID)
	//	$this->updateDependencies($newNodeID, $nodeDependencyArray);
		return true;	
	}
	
	
	function setTemplate($nodeID, $templateID, $languageID)
	{
		$userID = $_SESSION['userID'];
		
		// Used to set the initial (e.g. new nodes template set for nodes) TODO - changes?
		// TO DO - the change template should be for all languages. If -1 is sent
		$db =  DBCxn::Get();
		
		$query = $db->prepare("INSERT INTO cms_content (nodeID, version, created, createdBy, lastUpdated, lastUpdatedBy, notes, templateID, published, languageID) 
										VALUES (:nodeID, 0, NOW(), :userID, NOW(), :userID, '', :templateID, 0, :languageID)
										;");
		$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
		$query->bindParam(':templateID', $templateID, PDO::PARAM_INT); 
		$query->bindParam(':languageID', $languageID, PDO::PARAM_INT); 
		$query->bindParam(':userID', $userID, PDO::PARAM_INT); 
		$query->execute();
		
		// close the conn
		$db = null;
		
		return true;	
	}
	
	
	function getSuggestedPrimaryPagePath($nodeID, $nodeName) 
	{
		$db =  DBCxn::Get();

		$sql = "SELECT pid, lvl
						FROM `cms_tree_struct` AS t
						WHERE t.ID = :nodeID				
						;";
		$query = $db->prepare($sql);
		$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
		$query->execute();
		
		// If we have a row	
		if($query->rowCount() != 0) {
			$result = $query->fetch();
			$parentNodeID = $result['pid']; 
			$sql = "SELECT path
						FROM `cms_page_path` AS pp
						WHERE pp.nodeID = :nodeID AND type = 0				
						;";
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $parentNodeID, PDO::PARAM_INT);
			$query->execute();
			
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				return strtolower($result['path'] . '/' . $nodeName);
			}
			else 
			{
				// this is a root level node so just return the node name?
				return strtolower('/' . $nodeName);
			}
		} 
		
		return '';
	}
	
	
	function setPrimaryPagePath($nodeID, $path /*, $languageID */)
	{
		// Used to change or for the initial primary page path
		// TO DO - primaruy for all languages and check uniqueness.
		
		$db =  DBCxn::Get();
		
		// 1.1 start a transaction 
		$db->beginTransaction();
		
		// 2. Update the paths to set any that are primary to redirects (e.g. type 0 to type 1)
		// TO DO 
		
		
		// 3. Check if a row exists AND check if it's been changed otherwise we'll end up with duplicates
		$sql = "SELECT ID, path, nodeID, type
					FROM cms_page_path
					WHERE (nodeID = :nodeID AND type = 0) 
							;";
		$query = $db->prepare($sql);
		$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
		// $query->bindParam(':path', $path, PDO::PARAM_STR);
		$query->execute();
			
		// If we have at least a row
		if($query->rowCount() != 0) {
			$result = $query->fetch();
			if ($result['path'] == $path && $result['type'] == 0 ) 
			{
				// it's already the same and primary (case sensitive?) so do nothing
				return true;
			}
			else 
			{
				// DO UPDATE - update the existing rows
				// First if there is a row with the same path that isn't a primary then delete it
				$query = $db->prepare("DELETE FROM cms_page_path 
									WHERE (nodeID = :nodeID AND type != 0 AND path = :path);");
				$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
				$query->bindParam(':path', $path, PDO::PARAM_STR);
				$query->execute();
				
				$query = $db->prepare("UPDATE cms_page_path 
									SET path = :path, type = 0
									WHERE (nodeID = :nodeID AND type = 0) OR (nodeID = :nodeID AND path = :path);");
				$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
				$query->bindParam(':path', $path, PDO::PARAM_STR);
				$query->execute();
				
				
			}
			// $result = $query->fetch();
			//$relatedNode['nodeID'] = $result['nodeID']; 
			
			// DO update
			
		} else {
			// DO insert!
			$query = $db->prepare("INSERT INTO cms_page_path (path, nodeID, type)
									VALUES (:path, :nodeID, 0);");
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':path', $path, PDO::PARAM_STR);
			$query->execute();
		
		}
	 
		// 4. Finalise the transaction
		$db->commit();
		
		// close the conn
		$db = null;
		
		return true;	
	}
	
	
	function checkPagePath($nodeID, $path) 
	{
		// Create the page path doesn't already exist in the DB
		$path = strtolower(trim(($path)));
		$db =  DBCxn::Get();
		try 
		{			
			$query = $db->prepare("SELECT p.nodeID AS nodeID, n.nm AS nodeName
									FROM `cms_page_path` AS p
									LEFT OUTER JOIN cms_tree_data n ON n.id = p.nodeID
									WHERE p.nodeID != :nodeID AND path = :path;");
									
			$query->bindParam(':path', $path, PDO::PARAM_STR);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			
			$query->execute(); 
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$nodeID = $result['nodeID'];
				$nodeName = $result['nodeName'];
			//	error_log($nodeName . ' - ' . $nodeID);
				return $nodeName . ' - ' . $nodeID;
			} else {
				return false;
			}
			
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			error_log("Check page path failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			$db = null;
			return false;
		}
	
		return false;
	}
		
	function addPagePath($nodeID, $type, $path) 
	{
		// Create the template in the DB
		// TO DO store the old version to the version tables. 
		$path = strtolower(trim(($path)));
		
		$db =  DBCxn::Get();
		
		if ($type !== 1 && $type !== 2 && $type !== 3)
		{
			$type = 1;
		}
		// TO DO Validate that the user has access to this node? 
		try 
		{			
			$query = $db->prepare("INSERT INTO cms_page_path (`path`, `nodeID`, `type`)
									VALUES (:path, :nodeID, :type);");
			$query->bindParam(':path', $path, PDO::PARAM_STR);
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':type', $type, PDO::PARAM_INT);
			$query->execute(); 
			$pagePathID = $db->lastInsertId(); 
			
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			error_log("Insert page path failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			$db = null;
			return false;
		}
	
		return $pagePathID;
	}
	
	
	function deleteNode($nodeID) 
	{
		// This is a quick and dirty workaround to delete a node. 
		// We don't delete the content stored - this is for a future undelete function? 
		// But NOT deleting the page paths causes issues if the user tries to create a page path in the future with the same
		// path as a deleted path. 
			
		$db =  DBCxn::Get();
		
		// TO DO Validate that the user has access to this node? 
		try 
		{			
			$query = $db->prepare("DELETE FROM cms_page_path 
									WHERE nodeID = :nodeID;");
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->execute(); 
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			error_log("Deletion of page path(s) failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			$db = null;
			return false;
		}
		return true;
	}
	
	
	function deletePagePath($pathID) 
	{
		// deletes path
		$pathID = strtolower(trim(($pathID)));
		
		$db =  DBCxn::Get();
		
		// TO DO Validate that the user has access to this node? 
		try 
		{			
			$query = $db->prepare("DELETE FROM cms_page_path 
									WHERE ID = :pathID;");
			$query->bindParam(':pathID', $pathID, PDO::PARAM_INT);
			$query->execute(); 
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			error_log("Delete page path failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			$db = null;
			return false;
		}
	
		return true;
	}
	
	function updateDependencies($nodeID, $npDependencies) 
	{
		// updates the dependencies tables for other nodes that need to know about this one and creates a list of the nodes this node needs to know about.
		// TO DO - if we ever have different templates for different languages this might get more complicated
		
		$db =  DBCxn::Get();
	//	$thisNodeDeps = array(); // nodes that this node is interested in
		$thisNodeDeps = $npDependencies;  // copy the np node dependencies (from the content)
		$otherNodeDeps = array();      // nodes that need to ref this node. 
		$insertChildDependency = false;  // TO DO for now if children are used in the template we just store a all levels child dependecy record if it doesn't already exist. Bit of a hack as I hope to add level specifics and types. 
		
		// Two jobs.. update all dependencies that this node needs to be concerned about and check all parent nodes of those above it.
		
		// TO DO
		
		// 	1	Search for all nodes that have a level reference (e.g. a news parent that references all sub levels) that might need this node
		
		// 1a. Create a list of parents in structure  NODE -> levelsup  
		// NEWS   -  level 3 
		      //JUNE - level 2
			       //1/6/2014 level 1
				       // THISNODE   
		$nodeParents = $this->getParentNodes($nodeID);
		
		
		// 1b. now check for a dependences of either all levels (=0) or specific for the parents
		// Now for each parent node look for an entry where the nodeID and subnodeID are the same... this means that it's interested in all nodes at the level indicated (0 means all children). 
		// So if there is an entry then we need to loop through and get the children for them
		// TO DO is there a way to do this in a single statement? Perhaps a prepared statement?
				
		$nodeList = implode(', ', $nodeParents);
		
	//	error_log('Parents for NODE: ' . $nodeID . ' : ' . $nodeList);
		
		// TO DO how do I safely add a IN PDO bindParam - e.g. a list of numbers but not as a string?
		/* $sql = "SELECT nodeID, level 
					FROM cms_node_dependency
					WHERE nodeID IN (". $nodeList . ") AND nodeID = subnodeID			
							;";
			error_log($sql);
			$query = $db->prepare($sql);
			$query->bindParam(':nodeList', $nodeList, PDO::PARAM_STR);
			$query->execute();
			
			// If we have at least a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
					$result = $query->fetch();
					$parentNodeID = $result['parent_ID']; 
					$level = $result['level']; 
					$curNodeID = $parentNodeID;
				}
			} else {
				// TO DO -- handle this better?
				error_log('Error getting parent for: nodeID = ' . $nodeID);
			}  */
	
		// Now for each parent check if either the specific level is needed or if all levels are of interest and add them to the depenency list
		foreach($nodeParents as $key => $value) {
			$sql = "SELECT nodeID, level 
					FROM cms_node_dependency
					WHERE (nodeID = :nodeID AND level = :level) OR (nodeID = :nodeID AND level = 0) 
							;";
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $value, PDO::PARAM_INT);
			$query->bindParam(':level', $key, PDO::PARAM_INT);
			$query->execute();
			
			// If we have at least a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$relatedNode['nodeID'] = $result['nodeID']; 
				$relatedNode['level'] = $result['level']; 
				
				array_push($otherNodeDeps, $relatedNode);
			} else {
				// TO DO -- handle this better?
			//	error_log('Error getting parent for: nodeID = ' . $nodeID);
			}
		} 
	//	error_log('OTHER NODES TO INSERT: ' . print_r($otherNodeDeps, 1));
		
		
		// 2. Now search template for hardcoded references and child references and add them
		// 2a hard coded references
		// get template
		$templateCode = $this->getTemplateCode($nodeID);
		
		$startPos  = 0;

		do
		{
			$startofstr = strpos($templateCode, '{|@ContentByNodeID(', $startPos); 
			if($startofstr !== false) {
				$bracketpos =  strpos($templateCode, '(', $startofstr);
				$endofstr = strpos($templateCode, ',', $startofstr);
				// error_log('STRING: ' . $startofstr. ' : ' . $endofstr . ' : ' . substr( $templateCode, $bracketpos+1 , $endofstr - $bracketpos -1));
				$nodeFound = trim(substr( $templateCode, $bracketpos+1 , $endofstr - $bracketpos -1));
				// add to the array
				$thisNodeDeps[$nodeFound] = $nodeFound;
				$startPos = $endofstr;
			}
		}
		while ($startofstr !== false);
		
		// 2b Child
		// TO DO - need to think about .Level(2)  or if this is absent then all?
		// @ContentByNodeID(27, 'NoExist')
		$startPos  = 0;

		//$startofstr = strpos($templateCode, '{|@foreach(var item in @Page.Children', $startPos); 
		// TO DO - check for the actual field rather than just the string.
		$startofstr = strpos($templateCode, '@Page.Children', $startPos); 
		if($startofstr !== false) {
			// TO DO check the structure of this loop and amend accordingly
			$bracketpos =  strpos($templateCode, '(', $startofstr);
			$endofstr = strpos($templateCode, ')', $startofstr);

			// TO DO remove this as it's a hardcode - if we find @Page.Children we just enter an all levels dependecy record for now
			$insertChildDependency = true;
		}

		
		// 3. Found all dependencies - insert
		// 3.a  Start trans
		$db->beginTransaction();
		
		// 3.b  Delete all dependencies where this node is the primary (e.g. we recreate this list in case some have been removed by template change or picker changes?)
		// TO DO - this would also mean we can remoce the duplicate check in 3.c
		
		// 3.c  Insert all new dependencies for this node.
		foreach($thisNodeDeps as $key => $value) 
		{
			$query = $db->prepare("SELECT nodeID, subnodeID, level
									FROM cms_node_dependency 
									WHERE nodeID =:nodeID AND subnodeID = :subnodeID
									;");
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':subnodeID', $value, PDO::PARAM_INT);  
			$query->execute();
			
			// if we don't have a row insert one!
			if($query->rowCount() == 0) {
				$query = $db->prepare("INSERT INTO cms_node_dependency (nodeID, subnodeID, level) 
										VALUES (:nodeID, :subnodeID, NULL)
										;");
				$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
				$query->bindParam(':subnodeID', $value, PDO::PARAM_INT);  // the subnode is the nodeID from the array 
				$query->execute();
			}
		}
		
		// 3.d Insert all new dependecies of other nodes concerned with this node!
		foreach($otherNodeDeps as $key => $value) 
		{
			$query = $db->prepare("SELECT nodeID, subnodeID, level
									FROM cms_node_dependency 
									WHERE nodeID =:nodeID AND subnodeID = :subnodeID
									;");
			$query->bindParam(':nodeID', $value['nodeID'], PDO::PARAM_INT);
			$query->bindParam(':subnodeID', $nodeID, PDO::PARAM_INT);  // the subnode is the nodeID of this node.. confusing!
		//	$query->bindParam(':level', $value['level'], PDO::PARAM_INT);
			$query->execute();
			
			// if we don't have a row insert one!
			if($query->rowCount() == 0) {
				$query = $db->prepare("INSERT INTO cms_node_dependency (nodeID, subnodeID, level) 
										VALUES (:nodeID, :subnodeID, :level)
										;");
				$query->bindParam(':nodeID', $value['nodeID'], PDO::PARAM_INT);
				$query->bindParam(':subnodeID', $nodeID, PDO::PARAM_INT);  // the subnode is the nodeID of this node.. confusing!
				$level = $key +1;
				$query->bindParam(':level', $level, PDO::PARAM_INT);
				$query->execute();
			}
		}
		
		// 3.e Insert child level dependences
		// TO DO - at the moment this is just an all levels if the @Page.Children is used at all. HACK!
		if ($insertChildDependency) 
		{
			$query = $db->prepare("SELECT nodeID, subnodeID, level
									FROM cms_node_dependency 
									WHERE nodeID =:nodeID AND subnodeID = :subnodeID AND level = 0
									;");
			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':subnodeID', $nodeID, PDO::PARAM_INT);  
			$query->execute();
			
			// if we don't have a row insert one!
			if($query->rowCount() == 0) {
				$query = $db->prepare("INSERT INTO cms_node_dependency (nodeID, subnodeID, level) 
										VALUES (:nodeID, :subnodeID, 0)
										;");
				$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
				$query->bindParam(':subnodeID', $nodeID, PDO::PARAM_INT); 
				$query->execute();
			}
		}
		
		// 3.d  Complete the trans
		$db->commit();
		
		// DIRTY CACHE - this node and any other one that references it! 
		
		// close the conn
		$db = null;
	}
	
	
	function TODOdeleteDependencies ($nodeID) 
	{
		// When you delete a node delete the references to it
		// Both for dependences of this node and where it is a dependency
		return true;
	}
	
	
	function getParentNodes($nodeID) 
	{
		$db =  DBCxn::Get();
		$nodeParents = array();
		$curNodeID = $nodeID;
		
		// i is used for a level count and a watchdog for crazy loops or something
		for ($i = 1; $i < 100; $i++) {
			// Get parent node
			// 1B. get the templateID (perhaps this is changeable?)
			$sql = "SELECT pid, lvl
							FROM `cms_tree_struct` AS t
							WHERE t.ID = :nodeID				
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':nodeID', $curNodeID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row
			// ********************
			// TO DO - here is your example of a single fecking row you've been looking for
			// ********************
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$parentNodeID = $result['pid']; 
				$level = $result['lvl']; 
				$curNodeID = $parentNodeID;
			} else {
				// TO DO -- handle this better?
				error_log('Error getting parent for: nodeID = ' . $nodeID);
			}
			
			if ($level == 0) {
			//	error_log(print_r($nodeParents,1));
				break;
			}	
			$nodeParents[$i] =  $curNodeID;
			if ($i === 99){
			// something seriously wrong with the tree!
				error_log('Error getting parents for nodeID: ' . $nodeID .' check tree');
			}
		}
		// close the conn  TODO review if I should be doing this - as it's a static?!
		//$db = null;
		
		$nodeParents = array_reverse ( $nodeParents, true );
		return $nodeParents;
	}
	
	
	function TODOgetChildNodes($nodeID) 
	{
		$nodeChildren = array();
		
		// TO DO
	
		return $nodeChildren;
	}
	
	
	function getTemplateCode($nodeID)
	{
		$db =  DBCxn::Get();

		// Get template code
		$sql = "SELECT content 
					FROM cms_template AS t 
					INNER JOIN cms_content AS c 
						ON c.templateID = t.ID
					WHERE c.nodeID = :nodeID AND c.published = 1
						;";
		
		$query = $db->prepare($sql);
		$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
		$query->execute();
		
		// If we have a row
		if($query->rowCount() != 0) {
			$result = $query->fetch();
			$templateCode = $result['content']; 
		} else {
			// TO DO -- handle this better?
			error_log('Error getting template content for: nodeID = ' . $nodeID);
		}
		
		// close the conn
		$db = null;
		
		return $templateCode;
	}
	
 } 
?>
