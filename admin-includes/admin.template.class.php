<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 
//  Purpose:-      The main template class, 

class template {

	//public definitions
	private static $db;   
	public $results;
	public $tabs;
	public $entities;
	public $newTemplateID;
	public $sections;
	private $lastTabID;

	function __construct() 
	{
		// Init variables
		self::$db = DBCxn::Get();	
	}
  
  
  function getTemplatesList() 
  	{
  		// Gets the list of template from the DB
	
  		try {
			$db =  self::$db;
			 
			$sql = "	-- get list of templates from DB
						SELECT  `ID` ,  `name` ,  `version` ,  `created` ,  `lastUpdated` ,  `content` ,  `description` 
							FROM  `cms_template` AS t
							WHERE 1 ;";
			
			$query = $db->prepare($sql);
			//$query->bindParam(':nodeID', $this->contentID, PDO::PARAM_INT);
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
 			
				foreach ($query as $key => $result) {
				//	$ID = $result['ID'];
					$this->results[$result['ID']]['templateID'] = $result['ID'];
					$this->results[$result['ID']]['name'] = $result['name'];
				}
			} else {
				// thrown an exception
				throw new Exception('Error getting template List from database'); 
			}
			
			// close the connection
			$db = null;
			
		} catch (Exception $e) {
			error_log('Throwing exception in the getTemplateList');
			error_log($e->getMessage());
			return false;
		}
		return true;
	}
	

  function getTemplate($templateID) 
  	{
  		// Gets the details of the template from the DB
	
		// 1. Get the top level details of the template
  		try {
			$db =  self::$db;
			 
			$sql = "	-- get list of templates from DB
						SELECT  `ID` ,  `name` ,  `version` ,  `created` ,  `lastUpdated` , `description`, `content` AS templateContent, `useParentTemplate`
							FROM  `cms_template` AS t
							WHERE ID = :templateID;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			//print $sql;
			$query->execute();
		
			// If we have a row - TO DO only one row!
			if($query->rowCount() != 0) {
 			
				foreach ($query as $key => $result) {
				//	$ID = $result['ID'];
					$this->results['templateID'] = $result['ID'];
					$this->results['name'] = $result['name'];
					$this->results['version'] = $result['version'];
					$this->results['created'] = $result['created'];
					$this->results['lastUpdated'] = $result['lastUpdated'];
					
					// TO DO - this mess is where I had the json_encode utf8 issue of null content. Set this on the DB connection string for now but might need to fiddle later
					//		$this->results['templateContent'] = substr(str_replace(">", "&&&", str_replace("<", "$$$", $result['templateContent'])), 0, 7977); 
					// $this->results['templateContent'] = substr($result['templateContent'], 0, 1000);
					// $this->results['content'] = 'HERE';
					$this->results['templateDescription'] = $result['description'];
					$this->results['templateContent'] = $result['templateContent'];
					$this->results['useParentTemplate'] = $result['useParentTemplate'];
					
					// error_log($this->results['templateContent']);
					$this->results['description'] = $result['description'];
				}
			} else {
				// thrown an exception
				throw new Exception('Error getting template details from database'); 
			}
		} catch (Exception $e) {
			error_log('Throwing exception in the getTemplate function');
			error_log($e->getMessage());
			return false;
		}
		
		// 2. First get the tabs that belong to template and their names
		try {
			// $db =  self::$db;
			
			$sql = "	SELECT tt.ID AS tabID, tt.name AS tab_name
							FROM `cms_template` AS t 
							INNER JOIN `cms_template_tab` AS tt 
							  ON tt.templateID = t.ID
							WHERE t.ID = :templateID
							ORDER BY tt.order, tt.ID;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
				//	$ID = $result['ID'];
					$tab['tabID'] = $result['tabID'];
					$tab['tab_name'] = $result['tab_name'];
					$this->tabs[] = $tab;
				}
			} else {
				// thrown an exception
				// throw new Exception('Error getting template ' + $templateID + 'from database'); 
			//	error_log('Template ID - ' . $templateID . ' no tabs found ');
			//	return false;
			}
		} catch (Exception $e) {
			error_log('Throwing exception in the getTemplate details');
			error_log('Template ID - ' . $templateID . ' has thrown exception in the get template details ' . $e->getMessage());
			return false;
		}
		
		// 3. Now get the entity details
		try {
			// $db =  self::$db;
			
			$sql = "SELECT e.ID AS entityID, e.name AS entity_name, e.title AS entity_title, e.description AS entity_description, e.entity_type, tt.ID AS tabID, 	tt.name AS tab_name, e.sectionID
				FROM  `cms_template` AS t
				INNER JOIN  `cms_entity` AS e ON e.templateID = t.ID
				INNER JOIN  `cms_template_tab` AS tt ON tt.ID = e.template_tabID
				WHERE t.ID = :templateID
				ORDER BY tt.ID, e.sort_order;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
				//	$ID = $result['ID'];
					$entity['entityID'] = $result['entityID'];
					$entity['entity_name'] = $result['entity_name'];
					$entity['entity_type'] = $result['entity_type'];
					$entity['entity_title'] = $result['entity_title'];
					$entity['entity_description'] = $result['entity_description'];
					$entity['sectionID'] = $result['sectionID'];
					$entity['tabID'] = $result['tabID'];
					$entity['tab_name'] = $result['tab_name'];
					
					// push onto array keeping sort order
					$this->entities[] = $entity;
				}
			} else {
				// throw an exception
				// throw new Exception('Error getting template ' + $templateID + 'from database'); 
			//	error_log('Template ID - ' . $templateID . ' no details found ');
			//	return false;
			}
		} catch (Exception $e) {
			error_log('Throwing exception in the getTemplate details');
			error_log('Template ID - ' . $templateID . ' has thrown exception in the get template details ' . $e->getMessage());
			return false;
		} 
		
		
		// 4. Get the sections for each tabID
		try {
			// $db =  self::$db;
			
			$sql = "SELECT s.ID AS sectionID, s.template_tabID
				FROM  `cms_section` AS s
				INNER JOIN cms_template_tab tt ON tt.ID = s.template_tabID
				WHERE tt.templateID = :templateID;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
				//	$ID = $result['ID'];
					$this->sections[$result['sectionID']]['template_tabID'] = $result['template_tabID'];
					$this->sections[$result['sectionID']]['sectionID'] = $result['sectionID'];
				}
			} 
		} catch (Exception $e) {
			error_log('Throwing exception in the getTemplate details');
			error_log('Template ID - ' . $templateID . ' has thrown exception in the get template details ' . $e->getMessage());
			return false;
		} 
		
		// close the conn
		$db = null;
		
		return true;
	}
	
	function saveTemplate($templateID, $templateName, $templateDescription, $templateContent, $entityDetails, $useParentTemplate) 
  	{
		// Updates the templateContent in the DB
		// TO DO store the old version to the version tables. 
		// TO DO - call the checkDependencies  - this is if you add lower / upper level references or hardcoded references to other nodes. 
		
		$db =  DBCxn::Get();
	
		// TO DO Validate that the user has access to this node? 
		try 
		{	
			// TO DO - might not need a transaction here as I'm doing a simple update at the moment. TODO versioning?
			$db->beginTransaction();
			
			// 1.1 // UPDATE template 
			
			$query = $db->prepare("UPDATE cms_template 
									SET `name` = :name, 
									`lastUpdated` = NOW(), 
									`description` = :description, 
									`content` = :content,
									`useParentTemplate` = :useParentTemplate
									WHERE ID = :templateID;");
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			$query->bindParam(':name', $templateName, PDO::PARAM_STR);
			$query->bindParam(':description', $templateDescription, PDO::PARAM_STR);	
			$query->bindParam(':content', $templateContent, PDO::PARAM_STR);
			$query->bindParam(':useParentTemplate', $useParentTemplate, PDO::PARAM_INT);
			$query->execute(); 
			
			// loop through each entity and build up struc for saving the details
			foreach ($entityDetails as $key => $value) {
				//error_log("EntityID: " . $value['entityID'] . ' - sectionID = ' . $value['sectionID'] . ' - title = ' . $value['title']. ' - description = ' . $value['description']);
				
				// to do - if entities are ever allowed to be shared on multiple templates this needs to change
				$query = $db->prepare("UPDATE cms_entity
									SET `title` = :title, 
									`description` = :description,
									`sort_order` = :sortOrder, 
									`sectionID` = :sectionID
									WHERE ID =:entityID;");
				
				$query->bindParam(':title', $value['title'], PDO::PARAM_STR);
				$query->bindParam(':description', $value['description'], PDO::PARAM_STR);	
				$query->bindParam(':entityID', $value['entityID'], PDO::PARAM_INT);
				$query->bindParam(':sortOrder', $value['sortOrder'], PDO::PARAM_INT);
				if ($value['sectionID'] == "null" || $value['sectionID'] == '' || $value['sectionID'] == '0') {
					$sectionID = null;
					$query->bindParam(':sectionID',  $sectionID, PDO::PARAM_NULL);	
				} else {
					$query->bindParam(':sectionID',  $value['sectionID'], PDO::PARAM_INT);	
				}
				
				$query->execute(); 

			}	
			
			$db->commit();	
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Saving Template failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		// close the connection
		$db = null;
		
		return true;	
	} 
	
	
	function newTemplate($templateName) 
  	{
		// Create the template in the DB
		// TO DO store the old version to the version tables. 
		
		$db =  DBCxn::Get();
	
		// TO DO Validate that the user has access to this node? 
		try 
		{	
			// TO DO - might not need a transaction here as I'm doing a simple update at the moment. TODO versioning?
			$db->beginTransaction();
			
			// 1.1 // UPDATE template 
			
			$query = $db->prepare("INSERT INTO cms_template (`name`, `lastUpdated`, `created`)
									VALUES (:name, NOW(), NOW());");
			$query->bindParam(':name', $templateName, PDO::PARAM_STR);
			$query->execute(); 
			$this->newTemplateID = $db->lastInsertId(); 
			
			$db->commit();
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Saving Template failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		return true;	
	} 
	
	
	function newTab($templateID, $tabName) 
  	{
		// Returned by the insert - TODO remove?
		$order = 0;
		
		// Stores the new tab to the DB		
		$db =  DBCxn::Get();
	
		// TO DO Validate input 
		try 
		{	
			$db->beginTransaction();
			
			// 1. get the last order number for the template
			$sql = "SELECT MAX(`order`) AS `order`
							FROM `cms_template_tab` AS tt
							WHERE tt.templateID = :templateID
							ORDER BY ID DESC		
						--	LIMIT 1							
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$order = $result['order'] + 1;
			} else {
				$order = 1;
			}
			
			// 2 - store the new tab into the db 
			$query = $db->prepare("INSERT INTO cms_template_tab (`name`, `order`, `templateID`) 
									VALUES (:name, :order, :templateID)
									;");
			$query->bindParam(':name', $tabName, PDO::PARAM_STR);
			$query->bindParam(':order', $order, PDO::PARAM_STR);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
								
			$query->execute();
				
			$this->lastTabID = $db->lastInsertId() ;
			
			
			$db->commit();
			
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Saving New Tab failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		return true;	
	}
	
	
	function deleteTab($tabID) 
  	{
		// Deletes tab 	
		$db =  DBCxn::Get();
	
		// TO DO Validate input 
		try 
		{	
			$db->beginTransaction();
			
			// 1. check there are no entities in the tab
			$sql = "SELECT ID 
							FROM `cms_entity` AS e
							WHERE e.template_tabID = :tabID						
							;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':tabID', $tabID, PDO::PARAM_INT);
			$query->execute();
			
			// If we have a row then return false and don't delete
			if($query->rowCount() != 0) {
				$result = false;
				return false;
			} 
			
			// 2 - delete the tab 
			$query = $db->prepare("DELETE FROM cms_template_tab
									WHERE ID = :tabID
									;");
			
			$query->bindParam(':tabID', $tabID, PDO::PARAM_INT);
								
			$query->execute();
			
			$db->commit();
			
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Deleting Tab failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		return true;	
	}
	
	
	function updateTabOrder($templateID, $sortOrder) 
  	{
		// Deletes tab 	
		$db =  DBCxn::Get();
	
		// TO DO Validate input 
	
		try 
		{	
			$db->beginTransaction();
			
			foreach ($sortOrder as $curSortOrder => $tabID) {
			
				// 1 - update the tab sort order
				$query = $db->prepare("UPDATE cms_template_tab
										SET `order` = :sortOrder
										WHERE ID = :tabID AND templateID = :templateID
										;");
				
				$query->bindParam(':sortOrder', $curSortOrder, PDO::PARAM_INT);
				$query->bindParam(':tabID', $tabID, PDO::PARAM_INT);
				$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
									
				$query->execute();
			}
			
			$db->commit();
			
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Updating Tab Sort Order failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		 
		return true;	
	}
	
	
	function newEntity($templateID, $tabID, $entityName, $entityType, $description = "") 
  	{
		// Stores the new entity to the DB		
		$db =  DBCxn::Get();
	
		// TO DO Validate input 
		// TO DO - the idea was to reuse entities as there is a mapping table... 
		$entityName = str_replace(" ", "-", trim($entityName));
		
		try 
		{	
			$db->beginTransaction();
			
			// 1. insert into the entity table. 
			$sql = "INSERT INTO cms_entity (`name`, `title`, `entity_type`, `templateID`, `template_tabID`, `description`) 
									VALUES (:name, :name, :entityType, :templateID, :template_tabID, :description)
									;";
			
			$query = $db->prepare($sql);
			$query->bindParam(':name', $entityName, PDO::PARAM_STR);
			$query->bindParam(':entityType', $entityType, PDO::PARAM_INT);
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);					
			$query->bindParam(':template_tabID', $tabID, PDO::PARAM_INT);
			$query->bindParam(':entityID', $entityID, PDO::PARAM_INT);
			$query->bindParam(':description', $description, PDO::PARAM_STR);
			
			$query->execute();			
			
			$db->commit();
			
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Adding New Entity failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		return true;	
	}
	
	
	function deleteEntity($entityID) 
  	{
		// Stores the new tab to the DB		
		$db =  DBCxn::Get();
	
		// TO DO Validate input 
		try 
		{	
			$db->beginTransaction();
			
			// 1 - delete the entity 
			$query = $db->prepare("DELETE FROM cms_entity
									WHERE ID = :entityID
									;");
			
			$query->bindParam(':entityID', $entityID, PDO::PARAM_INT);
								
			$query->execute();
			
			$db->commit();
			
			// close the connection
			$db = null;
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Deleting Entity " . $entityID . " failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		return true;	
	}
	
	
	function newSection($templateID, $tabID) 
  	{
		// Stores the new section to the DB		
		$db =  DBCxn::Get();
	
		// TO DO Validate input 
		try 
		{	
			$db->beginTransaction();
			
			// 1. insert into the entity table. 
			$sql = "INSERT INTO cms_section (`template_tabID`) 
									VALUES (:template_tabID)
									;";
			
			$query = $db->prepare($sql);				
			$query->bindParam(':template_tabID', $tabID, PDO::PARAM_INT);
			
			$query->execute();			
			$sectionID = $db->lastInsertId(); 
			$db->commit();
			
			// close the connection
			$db = null;
			return $sectionID;
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			error_log("Saving New Section failed.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
		return false;	
	}
	
	
	function addSEOFields($templateID) 
  	{
		// TO DO Validate input 

		// 1. Add SEO tab
		$this->newTab($templateID, "SEO");
		
		// 2. Add fields
		$this->newEntity($templateID, $this->lastTabID, "PageTitle", 3, "Page title is shown in the browser title");
		$this->newEntity($templateID, $this->lastTabID, "MetaDescription", 4, "For SEO it is best to keep meta descriptions between 150 and 160");
		$this->newEntity($templateID, $this->lastTabID, "MetaTags", 4, "Meta tags are a bit old hat and ignored by Google but here by popular demand");
		$this->newEntity($templateID, $this->lastTabID, "NoIndex", 8, "Asks Google / Bing et al not to add this page to the index");
	
		return true;	
	}
	
	function addSitemapFields($templateID)
	{
		// 1. Add Sitemap tab
		$this->newTab($templateID, "Sitemap");
		
		// 2. Add fields
		$this->newEntity($templateID, $this->lastTabID, "SitemapChangeFreq", 3, "Used in the sitemap for SEO purposes. You can leave this blank, set to Never for archived pages.  Valid options: always, hourly, daily, weekly, monthly, yearly, never");
		$this->newEntity($templateID, $this->lastTabID, "SitemapPriority", 1, "Set a number between 1 - 10 (output is divided by 10) default is 5 (0.5) ");
		$this->newEntity($templateID, $this->lastTabID, "SitemapHide", 8, "Hide from sitemap?");
		return true;
	}
}
?>
