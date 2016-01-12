<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 
//  Purpose:-      A temporary - does everything CMS helper class

// TO DO
// Replace the DB class with a safe CMS user one
// Eventually split this all logically

class CMSHelper {
	// defo keep
	private static $db;    
	
	// to review
	public $page;
	public $pagePath;
	public $nodeID;
	public $fields;
	public $relatedContent = array();  // TO DO - will these be objects of a page?
	public $fieldsFound = array();
	private $siteSettingsContentID;
	public $template;
	public $languageID;
	public $languageMatch;
	public $relatedNodes;
	public $relatedNodeIDsStr;
	public $sections = array();
	
	
	public $variables = array();
	
	// new stuff
	public $pageType;
	
	function __construct() 
	{
		// Init variables
		self::$db = DBCxn::Get();	
	}
  
	
	function createPage() {
		// pass the page template into the template "parser"
		return ($this->parseTemplate($this->template));
	}
	
	
	function parseTemplate($templateInput, $fieldsAsStrings = false)
	{
		// we're translating / converting rather than purely parsing but you get the gist.
		// Find fields in the template input content - some fields may be nested inside other fields but each field is parsed recursively to cover that. 
		$replacedOutput = '';
		
		// 1 Find fields
		$this->fieldsFound = $this->findFields($templateInput);

		$lastPos = 0;
		
		foreach($this->fieldsFound as $field) {	
			$dataType = "";
			
			// get the field template content (might just be the field itself or might be a conditional or mod or similar
			//$fieldTemplate = substr($templateInput, $lastPos, $field['startPos'] - $lastPos);
			$fieldTemplate = $field['name'];
			
			// If there is an @ symbol it's not a simple field but something that needs more work - e.g. content from other pages, a loop through children or content picker stuff 
			if (substr($fieldTemplate, 0,1) == "@"){
				// parse the content - if no further fields are found then the content is returned
				$fieldHelper = new FieldHelper($this, $fieldTemplate);
				$fieldContent = $fieldHelper->fieldContent;
			
			}			
			// a basic field so we need the content data from the current page.
			else {
				// Get field's source (e.g. Site.SiteTitle Page.Heading1 Section.SectionHeading MyVar.Somepage Child.ChildHeading
				$fieldSource = trim(substr($fieldTemplate, 0, strpos($fieldTemplate, ".")));
				$fieldName = trim(substr($fieldTemplate, strpos($fieldTemplate, ".")+1));
			//	error_log($fieldTemplate . " FieldSource = " . $fieldSource . " FieldName = " . $fieldName);
				$errorFound = false;
				
				if($fieldSource == '' || $fieldName == '') {
					$fieldContent = 'INVALID FIELD NAME:-' .$fieldTemplate;
					$errorFound = true;
				}
				else {	
					
					$fieldContent = $this->getFieldValue($fieldName, $fieldSource, $errorFound);
					
					if ($errorFound == true && !DEBUG) {
						// hide ugly errors from end users 
						$fieldContent =  '';
					}
					
					// if the flag to output fields as strings is set then add strings (this is to stop fields used in conditionals 
					// from having their contents validated!
					if($fieldsAsStrings && $dataType != "int") {
						$fieldContent = '"' . $fieldContent . '"';
					}
				}
			}

			// Update the output with the content from the template up to the start of the field and the content returned 
			$replacedOutput = $replacedOutput . substr($templateInput, $lastPos, $field['startPos'] - $lastPos). $fieldContent;
			
			// for the next field set the lastPos based on the end pos of the field
			$lastPos = $field['endPos'];
		}
		
		// Now add the rest of the template after the last field
		$replacedOutput = $replacedOutput . substr($templateInput, $lastPos);
		
		return $replacedOutput;
	}
	
	
	function getFieldValue($fieldName, $fieldSource = "Page", &$errorFound = false)
	{
		$fieldContent = '';
		$specialFields = array("nodeName", "URL", "createdDate", "createdBy", "createdByID", "lastUpdatedBy", "lastUpdatedByID", 'lastUpdatedDate', 'noCache', 'parentIDs');
							
		switch($fieldSource) {
			case "Page":
				// if is a special field
				if (in_array($fieldName, $specialFields)) {
					$fieldContent = $this->page[$fieldName];
				} else {
					// check if the content exists
					if (isset($this->fields[$fieldName])) 
					{
						$fieldContent = $this->fields[$fieldName]['content'];
						$dataType = $this->fields[$fieldName]['dataType'];
					} else {
						$fieldContent =  'DATA MISSING:-' . $fieldSource . '-' . $fieldName;
						$errorFound = true;
					}
				}
				break;
			case "Site":
				// if is a special field
				if (in_array($fieldName, $specialFields)) {
					$fieldContent = $this->relatedContent[1][$fieldName];
				} else {
					// check if the content exists
					if (isset($this->relatedContent[1]['fields'][$fieldName])) 
					{
						$fieldContent = $this->relatedContent[1]['fields'][$fieldName]['content'];
						$dataType = $this->relatedContent[1]['fields'][$fieldName]['dataType'];
					} else {
						$fieldContent =  'DATA MISSING:-' . $fieldName . ' - ' . $fieldSource;
						$errorFound = true;
					}
				}
				break;
			case "PageOrSite":
				// weird special case is the special fields as you would always have them at the page level so just use that 
				// if is a special field
				if (in_array($fieldName, $specialFields)) {
					$fieldContent = $this->$fieldName;
				} 
				else {
					// page data overrides sitewide data
					if (isset($this->fields[$fieldName])) {
						$fieldContent = $this->fields[$fieldName]['content'];
						$dataType = $this->fields[$fieldName]['dataType'];
					} elseif (isset($this->relatedContent[1]['fields'][$fieldName])){
						$fieldContent = $this->relatedContent[1]['fields'][$fieldName]['content'];
					}else {
						if (isset($this->siteContent[$fieldName])) 
						{
							$fieldContent = $this->siteContent['fields'][$fieldName]['content'];
							$dataType = $this->siteContent['fields'][$fieldName]['dataType'];
						} else {
							$fieldContent =  'DATA MISSING:-' .$fieldTemplate;
							$errorFound = true;
						}
					}
				}
				break;
			default:
				// TO DO created date etc in child variables
				// these could be variables (e.g. child nodes, sections or get content by ID?
				if(isset($this->variables[$fieldSource])) {
					switch ($this->variables[$fieldSource]['type']) {
						case 'section':
							$childFieldName = $fieldName . "_" . $this->variables[$fieldSource]['sectionID'] . "_" . $this->variables[$fieldSource]['sectionInstanceID'];
						
							if (isset($this->fields[$childFieldName])) {
								$fieldContent = $this->fields[$childFieldName]['content'];
							}
							else {
								$fieldContent =  'INVALID FIELD NAME:-' .$childFieldName;
								$errorFound = true;
							}
							break;
						
						case 'child':
							//	error_log(print_r($this->variables[$fieldSource]['childID'],1));
							// first check if it's a special field 
							if (in_array($fieldName, $specialFields)) {
								$fieldContent = "SPECIAL";
								// this info has it's own variable in the relatedNode and is not in the fields array
								$fieldContent = $this->relatedNodes[$this->variables[$fieldSource]['childID']][$fieldName];
							} else {
								if (isset($this->relatedContent[$this->variables[$fieldSource]['childID']]['fields'][$fieldName]['content'])) {	
									// $relatedContent = $this->relatedContent[$this->variables[$fieldSource]['childID']];
									$fieldContent = $this->relatedContent[$this->variables[$fieldSource]['childID']]['fields'][$fieldName]['content'];
									$dataType = $this->relatedContent[$this->variables[$fieldSource]['childID']]['fields'][$fieldName]['dataType'];
								}
								else {
									$fieldContent =  'INVALID FIELD NAME:-' .$fieldName;
									$errorFound = true;
								}
							}
							break;
					}
				}
				else {
					$fieldContent =  'INVALID FIELD NAME:-' .$fieldName;
					$errorFound = true;
				}
		}
		return $fieldContent;
	}
	
	function getRelatedNodeFieldValue($relNodeID, $relFieldName, &$errorFound = false)
	{
		$output = '';
		
		$specialFields = array("nodeName", "URL", "createdDate", "createdBy", "createdByID", "lastUpdatedBy", "lastUpdatedByID", 'lastUpdatedDate', 'noCache', 'parentIDs');
							
		// check the page is in the related content nodes
		if(isset($this->relatedContent[$relNodeID])) {
			// if is a special field
			if (in_array($relFieldName, $specialFields)) {
				$output = $this->relatedContent[$relNodeID][$relFieldName];
			}
			else {
				if (isset($this->relatedContent[$relNodeID]['fields'][$relFieldName])) 
				{
					$output = $this->relatedContent[$relNodeID]['fields'][$relFieldName]['content'];
				//	$dataType = $this->fields[$fieldName]['dataType'];
				} else {
					if (DEBUG) {
						$output =  'DATA MISSING:-' . $relNodeID . ' - ' . $relFieldName;
					}
					$errorFound = true;
				}
			}	
		}
		else 
		{
			if (DEBUG) {
				$output =  'NODE NOT IN RELATED:-' . $relNodeID . ' - ' . $relFieldName;
			}
		}
		return $output;
	}
	
	
	function findFields($inputStr) 
	{
		$fieldsFound = array();
		$lastPos = 0;
		$endPos = 0;
		$startPos = 0;
		$fieldCount = 0;
		
		// find the first instance of the opening tag
		$startPos = strpos($inputStr, STARTTOKEN, $lastPos);
		while ($startPos!==FALSE ) {					
			// find the end tag 
			$endPos = strpos($inputStr, ENDTOKEN, $startPos);
			
			// whilst the number of start tags <> end tags keep searching  
			while ($endPos != FALSE && substr_count($inputStr, STARTTOKEN, $startPos, $endPos + strlen(ENDTOKEN) - $startPos) != substr_count($inputStr, ENDTOKEN, $startPos, $endPos + strlen(ENDTOKEN) - $startPos)) {
				// find next end tag
				$endPos = strpos($inputStr, ENDTOKEN, $endPos + strlen(ENDTOKEN));
			}
			 
			// if no end tag then a tag isn't closed
			if ($endPos === FALSE) {
				// TO DO replace with a try catch block and handle better.
				// TO DO add this to the debug messages or whatever I implement.
				error_log('Template missing a closing tag');
				break;
			}
			$fieldCount++;			
			// add on the length of the end token (if you do this above in one statement it will never be false!
			$endPos += strlen(ENDTOKEN);
			
			// TO DO {|literal|} - allow comments
			// TO DO {|for  while  if 
			$name =  substr($inputStr,  $startPos + strlen(STARTTOKEN), $endPos - $startPos - strlen(STARTTOKEN) - strlen(ENDTOKEN));
			$fieldInfo = array('name'=> $name, 'startPos' => $startPos, 'endPos' => $endPos);
									
			$fieldsFound[$fieldCount] = $fieldInfo;				
			$lastPos=$endPos;			
			
			// next one
			$startPos = strpos($inputStr, STARTTOKEN, $lastPos);
		 }
		 return $fieldsFound;
	}
	
	
	function checkPage($pathArray)
	{
		$getPrimary = false;
		// first check if a language is the first part of the page path. 
		// TO DO this could cause issues with genuine paths  -e.g. /en/en/home ?
		// TO DO should only check this if multiple languages are enabled?
		// perhaps I set a language match flag - if this is set we search for both the path with and without the lang (a simple OR in the SQL) a nice bodge?
		
		// first check the cache
	//	$cmsCache = new cmsCache($pathArray);
		
	//	if($cmsCache->inCache)
	//	{
	//		$this->pageType = 'cached';
			
	//	}
		
		$db =  DBCxn::Get();	

		// Get the Page Node ID from the path Array 
		$pagePath = implode("/", $pathArray);

		$db =  DBCxn::Get();
		// TO DO Validate inputs
		
		try 
		{		
			$sql = "SELECT nodeID, type, path, module
						FROM cms_page_path AS p
						WHERE p.path = :pagePath
						;";	
			$query = $db->prepare($sql);

			$query->bindParam(':pagePath', $pagePath, PDO::PARAM_STR);
			$query->execute();
	
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$this->nodeID = $result['nodeID'];
				$this->pagePath = $result['path'];
				
				switch ($result['type']) 
				{
					case 0:
					case 1:
						$this->pageType = 'normal';
					break;
					
					case 2: 
						$this->pageType = '301redirect';
						$getPrimary = true;
					break;
					
					case 3: 
						$this->pageType = '302redirect';
						$getPrimary = true;
					break;
					
					case 4:
						$this->pageType = 'module';
						$this->module = $result['module'];
					break;
						
					case 10;
						$this->pageType = "sitemap";
					break;
					
					default:
						$this->pageType = 'unknowntype';
					break;
				}
				
				// we need to redirect to the primary path
				if ($getPrimary) {
					$sql = "SELECT nodeID, type, path
						FROM cms_page_path AS p
						WHERE p.nodeID = :nodeID
						;";	
						
					$query = $db->prepare($sql);

					$query->bindParam(':nodeID', $this->nodeID, PDO::PARAM_STR);
					$query->execute();
					
					// If we have a row
					if($query->rowCount() != 0) {
						$result = $query->fetch();
						$this->pagePath = $result['path'];
					} else {
						error_log($pagePath . " has a redirect but the node referenced has no primary path ");
						$this->pagePath = "/";
					}
				}
				
				return $this->pageType;
			}
			else {
				
				// now try wildcards
				// these are used for pagination and mvc style pages ... e.g.
				// news/page-1
				// news/2015/01
				$sql = "SELECT path, nodeID
						FROM cms_page_path AS p
						WHERE p.type = 100
						ORDER BY CHAR_LENGTH(path) DESC
						;";	
						$query = $db->prepare($sql);

				$query->execute();
		
				// If we have a row(s) check them
				if($query->rowCount() != 0) {
					foreach ($query as $key => $result) {
						$curPath = $result['path'];
						if($curPath == substr($pagePath, 0, strlen($curPath)))
						{
							$this->nodeID = $result['nodeID'];
							$this->pagePath = $result['path'];
							return 'wildcard';
						}
					}
				}
	
				// throw an exception
				//throw new Exception('No matching page in the database with path: ' .$pagePath); 
				//return false;
				$this->pageType = 'notfound';
				return $this->pageType;		
			}
		}
		catch (PDOException $e)
		{
			error_log("Error finding page ID .\n");
			error_log("Requested page:".$pagePath."\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		return true;	
	}  
	
	
	function getPage($pathArray) 
  	{
		// Get the Page 
		// TO DO this will be if the cache is non-existent / dirty
		$db =  DBCxn::Get();
		// TO DO Validate inputs
		
	//	error_log('LANG = ' . $pathArray[0]);
		/* TO DO - this was removed from here - I think it should be in teh checkPage func call when reinstated 
		switch($pathArray[0]) {
			case 'en':
				$this->languageID = 1;
				$this->languageMatch = TRUE;
				break;
			case 'fr':
				$this->languageID = 2;
				$this->languageMatch = TRUE;
				break;
			case 'es':
				$this->languageID = 3;
				$this->languageMatch = TRUE;
				break;
			default: 
				$this->languageID = 1;
				$this->languageMatch = FALSE;
		}
		
		if($this->languageMatch) {
			$language = array_shift($pathArray);
		} 
		
		 TO AVOID issues the defaults are set below - not sure they are used.
		*/
		$this->languageID = 1;
		$this->languageMatch = FALSE;
			
		// Get page node ID
	//	$this->nodeID = $this->getPageNodeID($pathArray);
		
		// Get latest, published content ID (and corresponding template ID
		$contentID = $this->getContentID($this->nodeID);

		// get template
		// TO DO get the page template type when I load the node ID?
		$this->getTemplate($this->templateID);
		
		// TO DO check cache?
		// TO DO - separate getPageContent - this will be especially important when I want to use sub "pages"
		
		// get page info
		try 
		{		
			// TO DO for now we have a simple content field .  this will 
			// eventually be more complex so this call will just get the page node ID and something else will build it up.
			$sql = "SELECT DISTINCT p.ID, p.path, p.nodeID AS nodeID, t.nm as nodeName, c.created, c.createdBy, uc.foreName AS c_forename, uc.lastName AS c_lastname, c.lastUpdated, c.lastUpdatedBy, uu.foreName AS u_forename, uu.lastName AS u_lastname, c.notes, c.noCache, c.parentIDs
						FROM cms_page_path AS p
						INNER JOIN cms_tree_data AS t
						ON p.nodeID = t.id
						INNER JOIN cms_content AS c
						ON p.nodeID = c.nodeID
						LEFT OUTER JOIN cms_user AS uc 
						ON uc.ID = c.createdBy
						LEFT OUTER JOIN cms_user AS uu 
						ON uu.ID = c.lastUpdatedBy
						WHERE p.nodeID = :nodeID AND c.published = 1
					 ORDER BY p.type
					 LIMIT 1;";	
			$query = $db->prepare($sql);
			
			$query->bindParam(':nodeID', $this->nodeID, PDO::PARAM_INT);
			$query->execute();
	
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$this->page['ID'] = $result['nodeID'];
				$this->page['nodeName'] = $result['nodeName'];
				$this->page['URL'] = $result['path'];
				$this->page['notes'] = $result['notes'];
				$this->page['createdDate'] = $result['created'];
				$this->page['createdByID'] = $result['createdBy'];
				$this->page['createdBy'] = $result['c_forename'] . " " . $result['c_lastname'];
				$this->page['lastUpdatedDate'] = $result['lastUpdated'];
				$this->page['lastUpdatedBy'] = $result['u_forename'] . " " . $result['u_lastname'];
				$this->page['lastUpdatedByID'] = $result['lastUpdatedBy'];
				$this->page['noCache'] = $result['noCache'];
				$this->page['parentIDs'] = $result['parentIDs'];
			}
			else {
				// throw an exception
				// keep a count - trim the end off and try again.. loop until all '/'s are gone
				// e.g. "home/history/thispage"
				// then  "home/history"
				// then "home"
				// then TO DO - 404?
				throw new Exception('No matching page in the database for node ID: ' . $this->nodeID . " - from page path : " . print_r($pathArray, true)); 
				return false;
				
			}
		}
		catch (PDOException $e)
		{
			error_log("Error getting page.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		catch (Exception $e)
		{
			error_log("Error getting page.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
	
		// NOW the new get content
		// 3. Now get the data
  		$this->getContent($contentID, "page");
		
		// Get number of section instances?
		// 4. Get section instances for each section
  		try {
			$db =  self::$db;
			
			$sql = "	SELECT ID AS sectionInstanceID, sectionID, sort_order 
							FROM `cms_section_instance` 
							WHERE contentID = :contentID
							ORDER BY sectionID, sort_order
						;"; 
			
			$query = $db->prepare($sql);
			$query->bindParam(':contentID', $contentID);
			
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
					$sectionID = $result['sectionID'];
					if(!isset($this->sections[$sectionID]['sectionInstances']))
						$this->sections[$sectionID]['sectionInstances'] = array();
					array_push($this->sections[$sectionID]['sectionInstances'], $result['sectionInstanceID']);
				}
			}
		}
		catch (PDOException $e) {
			error_log('Caught PDO exception in the getContent sections');
			error_log('Content ID - ' . $this->contentID . ' has thrown exception in the get content sections getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}		
		catch (Exception $e) {
			error_log('Caught general exception in the getContent sections');
			error_log('Content ID - ' . $this->contentID . ' has thrown exception in the get content sections  getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}
		
		// 5.  Now get the related content data (e.g. the other pages /nodes that this page needs from either content "picked", site wide content such as menus or where it's a parent pages that loops through it's children (e.g. a news summary page).

		// 5a. Get related Node IDs		 
		$this->relatedNodes = $this->getRelatedNodes($this->nodeID);
		
		// error_log(print_r($this->relatedNodes,1));
		// 5b. get content for each node -- guess it might be nice to just send an array to function or to use a generic function which you pass a pointer so we only have to change the function once?
		if (count($this->relatedNodes)) {
		//	error_log(print_r($this->relatedNodes, 1));
			$this->getRelatedContent($this->relatedNodes);
		}
	
		return true;	
	}  
	
	
	function getTemplate($templateID) 
	{
		$parentTemplate = '';
		$template = '';
		$embedParent = false;
		
		// Get HTML for the template 
		$db =  DBCxn::Get();
		// TO DO Validate inputs
		try 
		{		
			// TO DO parent template won't always be 1 - lookup
			$parentTemplateID = 1;
			$useParentTemplate = false;
			
			$sql = "SELECT t.ID, t.content, t.useParentTemplate
						FROM cms_template AS t
						WHERE t.ID = :templateID OR t.ID = :parentTemplateID
						ORDER BY ID;
					 ;";	
			$query = $db->prepare($sql);
			
			$query->bindParam(':templateID', $templateID, PDO::PARAM_INT);
			$query->bindParam(':parentTemplateID', $parentTemplateID, PDO::PARAM_INT);
			$query->execute();
	
			// If we have a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
					if($result['ID'] == $parentTemplateID) {
							$parentTemplate = $result['content'];
					} else {
						$useParentTemplate = $result['useParentTemplate'];
						$template = $result['content'];
					}
				}
			}
			else {
				// throw an exception
				throw new Exception('No matching page template in the database'); 
				return false;
			}
			
			if($useParentTemplate) {
				$this->template = str_replace(STARTTOKEN . "@childContent". ENDTOKEN, $template, $parentTemplate);
			} else {
				$this->template = $template;
			}
			
		}
		catch (PDOException $e)
		{
			error_log("Error getting page template.\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		
	return true;	
	
	}
	
	
	/* THINK this has been removed... was duplicated in the checkPage call. Removed. 
	function getPageNodeID($pathArray) 
  	{
		// Get the Page Node ID from the path Array 
		
		$pagePath = implode("/", $pathArray);

		$db =  DBCxn::Get();
		// TO DO Validate inputs
		
		try 
		{		
			$sql = "SELECT nodeID
						FROM cms_page_path AS p
						WHERE p.path = :pagePath
						;";	
			$query = $db->prepare($sql);

			$query->bindParam(':pagePath', $pagePath, PDO::PARAM_STR);
			$query->execute();
	
			// If we have a row
			if($query->rowCount() != 0) {
				$result = $query->fetch();
				$pageID = $result['nodeID'];
				return $pageID;	
			}
			else {
				
				// now try wildcards
				// these are used for pagination and mvc style pages ... e.g.
				// news/page-1
				// news/2015/01
				$sql = "SELECT path, nodeID
						FROM cms_page_path AS p
						WHERE p.type = 100
						ORDER BY CHAR_LENGTH(path) DESC
						;";	
						$query = $db->prepare($sql);

				//	$query->bindParam(':pagePath', $pagePath, PDO::PARAM_STR);
				$query->execute();
		
				// If we have a row(s) check them
				if($query->rowCount() != 0) {
					foreach ($query as $key => $result) {
						error_log($result['path']);
						$curPath = $result['path'];
						if($curPath == substr($pagePath, 0, str_len($curPath)))
						{
							$pageID = $result['nodeID'];
							return $pageID;
						}
					}
				}
						
										
				
				// throw an exception
				// OR should we loop up through the array and find the next parent page up?  Would have to tell the user we've 
				// redirected? 
				// keep a count - trim the end off and try again.. loop until all '/'s are gone
				// e.g. "home/history/thispage"
				// then  "home/history"
				// then "home"
				// then TO DO - 404?
				throw new Exception('No matching page in the database with path: ' .$pagePath); 
				return false;
				
			}
		}
		catch (PDOException $e)
		{
			error_log("Error finding page ID .\n");
			error_log("Requested page:".$pagePath."\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		return true;	
	}  
   */
	
	function getContentID($nodeID) 
  	{
		// Get the Content ID using the node ID
		// TO DO can this be removed as we now get as a list
		
		$db =  DBCxn::Get();
		// TO DO Validate inputs
		
		try 
		{		
			$sql = "SELECT MAX(ID) AS contentID, templateID
						FROM cms_content AS c
						WHERE c.nodeID = :nodeID  
						  AND c.languageID = :languageID
						AND c.published = TRUE 
						;";	
			$query = $db->prepare($sql);

			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
			$query->bindParam(':languageID', $this->languageID, PDO::PARAM_INT);
			$query->execute();
	
			// If we have a row
			if($query->rowCount() != 0) {
				// TO DO - only a single result so no need for a foreach
				foreach ($query as $key => $result) {
					$this->contentID = $result['contentID'];
					$this->templateID = $result['templateID'];
					return $this->contentID;
				}
			}
			else {
				// throw an exception
				throw new Exception('No matching content in the database with nodeID: ' .$nodeID); 
				return false;
				
			}
		}
		catch (PDOException $e)
		{
			error_log("Error finding content for node ID .\n");
			error_log("Requested page:".$nodeID."\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		catch (Exception $e)
		{
			error_log("Error finding content for node ID .\n");
			error_log("Requested page:".$nodeID."\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		return true;	
	}  
  	
	
	function getContent($contentIDList, $target) {
		// target is a pointer to either the page content 
		// error_log("CONTENTIDLIST: ".$contentIDList);
		try {
			$db =  self::$db;
			
			//error_log("Content:" . $contentIDList . ": target:" . $target);
			$sql = "	-- int
						(SELECT e.ID AS entityID, e.name, entity_type, value, sectionID, sort_order, section_instanceID, nodeID, 'int' AS dataType
						FROM `cms_entity` AS e
						INNER JOIN cms_entity_value_int eint ON eint.entityID = e.ID
						INNER JOIN cms_content AS c ON eint.contentID = c.ID
						WHERE e.entity_type = 1 AND eint.contentID IN ( " . $contentIDList . ")  )
						UNION
						-- money
						(SELECT e.ID AS entityID, e.name, entity_type, value, sectionID, sort_order, section_instanceID, nodeID, 'money' AS dataType
						FROM `cms_entity` AS e
						INNER JOIN cms_entity_value_money em ON em.entityID = e.ID
						INNER JOIN cms_content AS c ON em.contentID = c.ID
						WHERE e.entity_type = 2  AND em.contentID IN ( " . $contentIDList . ") )
						UNION
						-- short text && bool (8)
						(SELECT e.ID AS entityID, e.name, entity_type, value, sectionID, sort_order, section_instanceID, nodeID, 'text' AS dataType
						FROM `cms_entity` AS e
						INNER JOIN cms_entity_value_shorttext est ON est.entityID = e.ID
						INNER JOIN cms_content AS c ON est.contentID = c.ID
						WHERE (e.entity_type = 3 OR e.entity_type = 8) AND est.contentID IN ( " . $contentIDList . ") )
						UNION
						-- long text (3) && rich text (4) && nodepicker (10) && images / media (11)
						(SELECT e.ID AS entityID, e.name, entity_type, value, sectionID, sort_order, section_instanceID, nodeID , 'text' AS dataType
						FROM `cms_entity` AS e
						INNER JOIN cms_entity_value_longtext elt ON elt.entityID = e.ID
						INNER JOIN cms_content AS c ON elt.contentID = c.ID
						WHERE (e.entity_type = 4 OR e.entity_type = 5 OR e.entity_type = 10 OR e.entity_type = 11) AND elt.contentID IN ( " . $contentIDList . ") ) 
						UNION
						-- date (6) and date time (7) 
						(SELECT e.ID AS entityID, e.name, entity_type, value, sectionID, sort_order, section_instanceID, nodeID, 'date' AS dataType
						FROM `cms_entity` AS e
						INNER JOIN cms_entity_value_date elt ON elt.entityID = e.ID
						INNER JOIN cms_content AS c ON elt.contentID = c.ID
						WHERE (e.entity_type = 6 OR e.entity_type = 7) AND elt.contentID IN ( " . $contentIDList . ") ) 
						ORDER BY sectionID, sort_order
						;"; 
		
			$query = $db->prepare($sql);
		//	$query->bindParam(':contentID', $contentID);
			
			//print $sql;
			$query->execute();
		
			// If we have a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
					// difference here between this and the admin version is we use the name of the field as the key
					// if the section ID is not null then it's a section instance bit of content - we append the sectionID_sectioninstanceID to the field name
					$sectionID = $result['sectionID'];
					if ($sectionID == "null" || $sectionID == 0) 
						$fieldName = $result['name'];
					else 
						$fieldName = $result['name'] . '_' . $result['sectionID'] . '_' . $result['section_instanceID'] ;
					if ($target == "page") {
						$this->fields[$fieldName]['fieldName'] = $result['name'];
						$this->fields[$fieldName]['content'] = $result['value'];
						$this->fields[$fieldName]['sortOrder'] = $result['sort_order'];
						$this->fields[$fieldName]['sectionID'] = $result['sectionID'];
						$this->fields[$fieldName]['dataType'] = $result['dataType'];
					} else {
						$fieldName = $result['name'];					
						// add the content to the related page
					//	$this->relatedContent[$result['nodeID']]['content'][$fieldName] = $result['value'];	
						$this->relatedContent[$result['nodeID']]['fields'][$fieldName]['fieldName'] = $result['name'];
						$this->relatedContent[$result['nodeID']]['fields'][$fieldName]['content'] = $result['value'];
						$this->relatedContent[$result['nodeID']]['fields'][$fieldName]['sortOrder'] = $result['sort_order'];
						$this->relatedContent[$result['nodeID']]['fields'][$fieldName]['sectionID'] = $result['sectionID'];
						$this->relatedContent[$result['nodeID']]['fields'][$fieldName]['dataType'] = $result['dataType'];							
					}
				}
			} else {
				// thrown an exception
				error_log('No content for contentID:' . $contentIDList . ' from database');
			}
		}
		catch (PDOException $e) {
			error_log('Caught PDO exception in the getContent');
			error_log('Content ID - ' . $this->contentID . ' has thrown exception in the get content details getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}		
		catch (Exception $e) {
			error_log('Caught general exception in the getContent');
			error_log('Content ID - ' . $this->contentID . ' has thrown exception in the get content details getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}
		
	}
	
	
	function getContentIDList($relatedNodesStr) 
  	{
		// Get the Content IDs using the node ID string
		
		$db =  DBCxn::Get();
		// TO DO Validate inputs
		
		//error_log($relatedNodesStr);
		try 
		{		
			$sql = "SELECT ID AS contentID, templateID, nodeID
						FROM cms_content AS c
						WHERE c.nodeID IN (" . $relatedNodesStr . ") 
						  AND c.languageID = :languageID
						AND c.published = TRUE 
						;";	
			$query = $db->prepare($sql);

		//	$query->bindParam(':nodeIDList', $relatedNodesStr, PDO::PARAM_STR);
			$query->bindParam(':languageID', $this->languageID, PDO::PARAM_INT);
			$query->execute();
			
			$contentIDList = '';
			// If we have a row
			if($query->rowCount() != 0) {
				// for each row append to the string
				
				foreach ($query as $key => $result) {
					if ($contentIDList !== '') {
						$contentIDList .= ',' . $result['contentID'];
					} else {
						$contentIDList = $result['contentID'];
					}
				}
			}
			else {
				// report an error
				// TO DO error logging
				error_log('No matching content in the database with nodeIDs: ' . $relatedNodesStr); 
			}
			return $contentIDList;
		}
		catch (PDOException $e)
		{
			error_log("Error finding content for node ID .\n");
		//	error_log("Requested page:".$nodeID."\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		catch (Exception $e)
		{
			error_log("Error finding content for node ID .\n");
		//	error_log("Requested page:".$nodeID."\n");
			error_log("getCode: ". $e->getCode() . "\n");
			error_log("getMessage: ". $e->getMessage() . "\n");
			return false;
		}
		return false;	
	}  
	
	
	function getRelatedContent($relatedNodes)
	{
		// seems strange to have both relatedNodes and relatedContent. Main diff is that relatedContent's index is the nodeID. 
		// TODO - perhaps this could be consolidated into a single struc for efficiency. 
		
	//	$relatedNodesStr = '';
	//	foreach($relatedNodes as $node) {
	//		$relatedNodesStr .= ',' . $node['nodeID'];
	//	}
		// now delete the first comma
	//	$relatedNodesStr = substr($relatedNodesStr, 1, strlen($relatedNodesStr));
	//	$this->relatedNodeIDsStr = $relatedNodesStr; // used for page debug
		$this->relatedNodeIDsStr = implode(',', array_keys($relatedNodes));
		
		
		// Get latest, published content ID (and corresponding template ID for all in one sql efficient swoop
		$contentIDList = $this->getContentIDList($this->relatedNodeIDsStr);
		
		// for each related node add it to the contentID string for a single get data call to mysql (used to do a single one for each but page loads were > 2 secs		
		foreach($relatedNodes as $node) {
			$nodeID = $node['nodeID'];			
			// create the object - data added later
			// build up the related page
			$relatedPage = array('nodeID' => $nodeID,
									'nodeName' => $node['nodeName'], 
									'level' => $node['level'], 
									'pageType' => $node['pageType'], 
									'URL' => $node['URL'], 
									'createdDate' => $node['createdDate'],
									'createdByID' => $node['createdByID'],
									'createdBy' => $node['createdBy'],
									'lastUpdatedDate' => $node['lastUpdatedDate'],
									'lastUpdatedBy' => $node['lastUpdatedBy'],
									'lastUpdatedByID' => $node['lastUpdatedBy'],
									'sortOrder' => $node['sortOrder'],
									'parentIDs' => $node['parentIDs'],
									'content' => array());
					
			$this->relatedContent[$nodeID] = $relatedPage;
		}	
		
		// Get content data for all related pages
		if($contentIDList != "") {
			$this->getContent($contentIDList, "related");
		}
	}

	
	function getRelatedNodes($nodeID) {
		// get the nodes that the content node relies on - e.g. site settings root node, any children or GetByContentID if statically set in the template
		// can also get related content on the fly from dynamic queries (e.g. variables) - might be worth refactoring the parser to do a first
		// pass to get list of all data required and do one hit on DB?
	
		$relatedNodes = array();
		
		$db =  DBCxn::Get();
		
		try 
		{		
			// For site settings level = -1 and there is no pagepath hence or is null
			$sql = "SELECT nd.`ID` , nd.`nodeID`, td.`nm` AS nodeName, nd.`subnodeID` , nd.`level` , t.`name` AS pageType, cc.`ID` AS contentID, pp.`path` AS path, cc.created, cc.createdBy, uc.foreName AS c_forename, uc.lastName AS c_lastname, cc.lastUpdated, cc.lastUpdatedBy, uu.foreName AS u_forename, uu.lastName AS u_lastname, ts.pos AS sortOrder, cc.parentIDs
				FROM `cms_node_dependency` AS nd
				INNER JOIN cms_tree_data AS td
					ON nd.subnodeID = td.id
				INNER JOIN cms_content AS c 
					ON c.nodeID = nd.nodeID
				INNER JOIN cms_content AS cc 
					ON nd.subnodeID = cc.nodeID
				INNER JOIN cms_template AS t 
					ON c.templateID = t.ID
				INNER JOIN cms_tree_struct AS ts
					ON ts.id = nd.subnodeID
				LEFT OUTER JOIN cms_user AS uc 
					ON uc.ID = cc.createdBy
				LEFT OUTER JOIN cms_user AS uu 
					ON uu.ID = cc.lastUpdatedBy
				LEFT OUTER JOIN cms_page_path AS pp ON nd.subnodeID = pp.nodeID
				WHERE (nd.nodeID = :nodeID or nd.nodeID = 1) AND (`level` <> 0 OR nd.level IS NULL) AND c.published = 1 AND cc.published = 1 AND (pp.type = 0 OR pp.type IS NULL)
				;";	
			$query = $db->prepare($sql);

			$query->bindParam(':nodeID', $nodeID, PDO::PARAM_INT);
		//	$query->bindParam(':languageID', $this->languageID, PDO::PARAM_INT);
			$query->execute();

			// TO DO - if ever there are multiple sites then need to look this ID up?
			$relatedNode = array();
			$relatedNode['nodeID'] = 1;
			$relatedNode['nodeName'] = '';
			$relatedNode['level'] = -1;  // -1 denotes site settings
			$relatedNode['pageType'] = "Site Settings";
			$relatedNode['URL'] = null;
			$relatedNode['createdDate'] = null;
			$relatedNode['createdByID'] = null;
			$relatedNode['createdBy'] = null;
			$relatedNode['lastUpdatedDate'] = null;
			$relatedNode['lastUpdatedBy'] = null;
			$relatedNode['lastUpdatedByID'] = null;
			$relatedNode['sortOrder'] = null;
			$relatedNode['parentIDs'] = '';
			$relatedNode['fields'] = array();
			$relatedNodes[1] = $relatedNode;
			
			// If we have a row
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
					$relatedNode = array();
					$relatedNode['nodeID'] = $result['subnodeID'];
					$relatedNode['nodeName'] = $result['nodeName'];
					$relatedNode['level'] = $result['level'];
					$relatedNode['pageType'] = $result['pageType'];
					$relatedNode['URL'] = $result['path'];
					$relatedNode['createdDate'] = $result['created'];
					$relatedNode['createdByID'] = $result['createdBy'];
					$relatedNode['createdBy'] = $result['c_forename'] . " " . $result['c_lastname'];
					$relatedNode['lastUpdatedDate'] = $result['lastUpdated'];
					$relatedNode['lastUpdatedBy'] = $result['u_forename'] . " " . $result['u_lastname'];
					$relatedNode['lastUpdatedByID'] = $result['lastUpdatedBy'];
					$relatedNode['sortOrder'] = $result['sortOrder'];
					$relatedNode['parentIDs'] = $result['parentIDs'];
					
					// content is added later
					$relatedNode['fields'] = array();
					// stick the Node ID on the array					
					$relatedNodes[$result['subnodeID']] = $relatedNode;
				}
			}
		}
		catch (PDOException $e) {
			error_log('Caught PDO exception in the getRelatedNodes function');
			error_log('Node ID - ' . $nodeID . ' has thrown exception in the get content details getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}		
		catch (Exception $e) {
			error_log('Caught general exception in the getRelatedNodes function');
			error_log('Node ID - ' . $this->nodeID . ' has thrown an exception in the get content details getCode: ' . $e->getCode() . "\n" . $e->getMessage());

			return false;
		}
		//error_log(print_r($relatedNodes, 1));
		return ($relatedNodes);
	}
	
	
	function getSitemap(){
		$xmlBody = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
</urlset> ';
		if(!$xml = simplexml_load_string($xmlBody)) 
		{ 
			error_log('Unable to load XML string'); 
			return false;
		} 

		$db =  DBCxn::Get();
		
		try 
		{		
			//  TO DO - language is NOT considered
			$sql = 'SELECT pp.ID AS pathID, pp.path, pp.nodeID, c.ID AS contentID, td.nm AS nodeName, c.templateID,
c.lastUpdated AS lastModified,
ev1.value AS PageTitle,
ev2.value AS SitemapChangeFreq,
ev3.value AS SitemapPriority,
ev4.value AS SitemapHide


FROM `cms_page_path` AS pp 
	INNER JOIN cms_tree_data AS td ON td.ID = pp.nodeID 
	INNER JOIN cms_content AS c ON c.nodeID = pp.nodeID
	-- PageTitle
	LEFT OUTER JOIN 
		(SELECT ID, templateID, name FROM cms_entity WHERE name = "PageTitle") AS e1 
		ON e1.templateID = c.templateID
	LEFT OUTER JOIN 
		(SELECT ID, entityID, contentID, value FROM cms_entity_value_shorttext) AS ev1 
		ON ev1.entityID = e1.ID AND c.ID = ev1.contentID
	-- SitemapChangeFreq 
	LEFT OUTER JOIN 
		(SELECT ID, templateID, name FROM cms_entity WHERE name = "SitemapChangeFreq") AS e2 
		ON e2.templateID = c.templateID
	LEFT OUTER JOIN 
		(SELECT ID, entityID, contentID, value FROM cms_entity_value_shorttext) AS ev2 
		ON ev2.entityID = e2.ID AND c.ID = ev2.contentID
	-- SitemapPriority
	LEFT OUTER JOIN 
		(SELECT ID, templateID, name FROM cms_entity WHERE name = "SitemapPriority") AS e3
		ON e3.templateID = c.templateID
	LEFT OUTER JOIN 
		(SELECT ID, entityID, contentID, value FROM cms_entity_value_int) AS ev3
		ON ev3.entityID = e3.ID  AND c.ID = ev3.contentID
	-- SitemapHide
	LEFT OUTER JOIN 
		(SELECT ID, templateID, name FROM cms_entity WHERE name = "SitemapHide") AS e4
		ON e4.templateID = c.templateID
	LEFT OUTER JOIN 
		(SELECT ID, entityID, contentID, value FROM cms_entity_value_shorttext) AS ev4
		ON ev4.entityID = e4.ID AND c.ID = ev4.contentID
WHERE (pp.type = 0 OR pp.type = 1) AND c.published = 1 AND c.languageID = 1 
				;';	
			$query = $db->prepare($sql);

		//	$query->bindParam(':languageID', $this->languageID, PDO::PARAM_INT);
			$query->execute();

			// TO DO - if ever there are multiple sites then need to look this ID up?
			
			// If we have any rows
			if($query->rowCount() != 0) {
				foreach ($query as $key => $result) {
					$urlXml = $xml->addChild("url", "");
					$urlXml->addChild("loc", DOMAIN . '/'. $result['path']);
					$urlXml->addChild("lastmod", date(DATE_ATOM, strtotime($result['lastModified'])));
					if(isset($result['SitemapChangeFreq']) && $result['SitemapChangeFreq'] !== null) {
						$ChangeFreq = strtolower(trim($result['SitemapChangeFreq']));
						$ChangeFreqArray = array("always", "hourly", "daily", "weekly", "monthly", "yearly", "never");
						if(in_array($ChangeFreq, $ChangeFreqArray)) {
							$urlXml->addChild("changefreq", $ChangeFreq);
						}
					}
					if(isset($result['SitemapPriority']) && $result['SitemapPriority'] !== null) {
						$priority = number_format($result['SitemapPriority'] / 10, 1, '.', '');
						if ($result['SitemapPriority'] > 10) {
							$priority = "1.0";
						} elseif($result['SitemapPriority'] < 1) {
							$priority = "0.5";
						}
						$urlXml->addChild("priority", $priority);
					}
				}
			}
		}
		catch (PDOException $e) {
			error_log('Caught PDO exception in the getSitemap function');
			error_log('Exception in the getSitemap getCode: ' . $e->getCode() . "\n" . $e->getMessage());
			return false;
		}		
		catch (Exception $e) {
			error_log('Caught unknown exception in the getSitemap function');
			error_log('Exception in the getSitemap getCode: ' . $e->getCode() . "\n" . $e->getMessage());
			return false;
		}	
	
		$sitemapBody = $xml->asXML();

		return $sitemapBody;
	}
} 
?>