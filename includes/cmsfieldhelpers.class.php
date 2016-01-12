<?php 

/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */



class FieldHelper {
/* TO DO TO DO - just copied and pasted the old generic FieldHelper */
/* class to validate and check fields.. TO DO have a good think about the structure of this 
   for example modifiers are handled in the foreach but this might be more generic and better placed here?
*/

	public $keyword;
	public $fieldTemplate;
	public $fieldContent;  // the parsed output content
	public $content; // reference to the master content holder (the CMS helper) - should this be a static // singleton instead?
	
	function __construct(&$content, $fieldTemplate) {
		$this->content = &$content;

		$this->fieldTemplate = $fieldTemplate;
		// keyword is up to first bracket
		$this->keyword = trim(substr($fieldTemplate, 0, strpos($fieldTemplate, '(')));
				
	//	$this->fieldContent = "[[CONTENT FROM FIELDHELPER]]";
		switch($this->keyword) {
			case '@ContentByNodeID':
				$this->fieldContent = "**TODO@ContentByNodeID**";
				
				$getNodeHelper = new GetNodeHelper($this->content, $fieldTemplate);
				if ($getNodeHelper->valid) {
					// ask the module helper for the content. 
					$this->fieldContent = $getNodeHelper->fieldContent;
				} else {
					if(DEBUG) {
						$this->fieldContent = "ERROR IN @ContentByNodeID :- " . $getNodeHelper->fieldContent;
					}
					else {
						// die quietly
						$this->fieldContent = "";
					}
				}
				break;
				
			case '@mod':
				$moduleHelper = new ModuleHelper($this->content, $fieldTemplate);
				if ($moduleHelper->valid) {
					// ask the module helper for the content. 
					$this->fieldContent = $moduleHelper->fieldContent;
				} else {
					if(DEBUG) {
						$this->fieldContent = "ERROR IN @mod :- " . $moduleHelper->fieldContent;
					}
					else {
						// die quietly
						$this->fieldContent = "";
					}
				}
				
				break;
			case '@foreach':
				$this->fieldContent = "**TODO@foreach**";
				$foreachHelper = new ForEachHelper($this->content, $fieldTemplate);
				if ($foreachHelper->valid) {
					// ask the module helper for the content. 
					$this->fieldContent = $foreachHelper->fieldContent;
				} else {
					if(DEBUG) {
						$this->fieldContent = "ERROR IN @mod :- " . $foreachHelper->fieldContent;
					}
					else {
						// die quietly
						$this->fieldContent = "";
					}
				}
				
				break;
			case "@if":
				$ifHelper = new IfHelper($this->content, $fieldTemplate);
				
				// TO DO validate field content against regexp
				if ($ifHelper->valid) {
					$this->fieldContent = $ifHelper->fieldContent;
				}
				else {
					$this->fieldContent = "";
				}
				break;
			default:
				if(DEBUG) {
					$this->fieldContent = "ERROR IN unknown function :- " . $fieldTemplate;
				}
				else {
					$this->fieldContent = "";
				}
		}
	}			
}


class GetNodeHelper {
	// Gets content using a node ID. 
	// TO DO have a good think about the structure of this 
	// EXAMPLE: {|@ContentByNodeID(27, 'Header1')|}
	public $valid;
	public $keyword;
	public $params;
	public $fieldContent;
	public $content; // reference to the master content holder (the CMS helper) - should this be a static // singleton instead?
	
	function __construct(&$content, $fieldTemplate) {
		$this->content = &$content;
	
		$this->fieldTemplate = $fieldTemplate;		
		// TO DO validate?
		$this->valid = true;
		
		// keyword is up to first bracket
		$this->keyword = substr($fieldTemplate, 0, strpos($fieldTemplate, '('));
		//error_log('Field Template Content = ' . $fieldTemplate);
		//error_log('Keyword = ' . $this->keyword);
		
		// Now get params
		// TO DO - gross simplification here as we might have NodesByIDs with {122,123}
		// TO DO write a function that can deal with this :(
		// might be as easy as just taking out the first param if it has { }
		// explodes the field content for the params. substring starting at the first ( and until ful str len minus keyword len and bracket
		// error_log('PARAMSTR:' . substr($fieldContent, strpos($fieldContent, '(') + 1, strlen($fieldContent) - strlen($this->keyword) - 2));
		$this->params = explode(',', substr($fieldTemplate, strpos($fieldTemplate, '(') + 1, strlen($fieldTemplate) - strlen($this->keyword) - 2));
		
		// TO DO - replace this total hack.
		// first trim
		// If the param has a start and end apostrophe then remove it (e.g. it's a string!)
		foreach ($this->params as $key => $value) {
			$value = trim($value);
			$strlen = strlen($value);
			// error_log('first : ' . substr($value, 0, 1) . ' last : '. substr($value,$strlen -1, 1));
			if(substr($value, 0, 1) == "'" && substr($value,$strlen -1, 1) == "'") {
				$value = substr($value, 1, $strlen - 2);
			}
			
			// store back to array - TO DO could use a pointer?
			// error_log('Adding : ' .  $value);
			$this->params[$key] = $value;
		}
		
		$outputContent = 'GETTING NODE INFO FOR: ' . $this->params[0] . ' Field: ' . $this->params[1];
		$relNodeID = $this->params[0];
		$relFieldName = $this->params[1];
		
		// if the content is not in the related content fields return an error
		//
		if (!isset($this->content->relatedContent[$relNodeID]['fields'][$relFieldName])) {
			error_log('Error getting ContentByNodeID - not in array nodeID: ' . $this->params[0] . ' field: ' . $this->params[1]);
			$this->valid = false;
			$outputContent = 'DATA MISSING **'.$relFieldName.'** RELATED FIELD - CHECK PARAMS IN ContentByNodeID :-' . $outputContent;
		}
		else {
			// output the content 
			$outputContent = $this->content->relatedContent[$relNodeID]['fields'][$relFieldName]['content'];
		} 
							
		 $this->fieldContent = $outputContent;
	}			
}


class ForEachHelper {
	// handles both the children key word and sections
	// NOTE Where is TODO
	//{|@foreach(item in @Page.Children.Where("!hidePage"))  {  ... }				
	//{|@foreach(section in @Page.Sections(1)) {  ... }

	public $keyword;
	public $fieldContent;
	public $valid;
	public $content; // reference to the master content holder (the CMS helper) - should this be a static // singleton instead?
	
	private $fieldFunction;
	public $varName;
	private $inAtPos;
	private $startLoopPos;
	public $loopContent;
	public $collectionContent;
	public $collectionType;
	public $collection;
	public $sectionID = 0;
	
	function __construct(&$content, $fieldContent) {
		$this->content = &$content;
		// TO DO validate valid?
		$this->valid = true;
		
	//	error_log('*********************************');
		$this->fieldContent = $fieldContent;
		// keyword is up to first bracket
		$this->keyword = trim(substr($fieldContent, 0, strpos($fieldContent, '(')));
	//	error_log('Field Content = ' . $fieldContent);
	//	error_log('Keyword = ' . $this->keyword);
		
		// Get content of function / field
		$this->fieldFunction = substr($fieldContent, strpos($fieldContent, '(') + 1, strlen($fieldContent) - strlen($this->keyword) - 2);
		
		// find in
		$this->inAtPos = strpos($this->fieldFunction, 'in @');
	//	error_log(' In keyword found at pos = ' . $this->inAtPos );
		
		// get the variable name for the loop
		$this->varName = trim(substr($this->fieldFunction, 0, $this->inAtPos));
	//	error_log('VarName ' . $this->varName);
		
		// get the collection text
		$this->collectionContent = trim(substr($this->fieldFunction, $this->inAtPos + 3, $this->findClosingBracket($this->fieldFunction) - ($this->inAtPos + 3)));
		//error_log('Collection Content =  ' . $this->collectionContent);
		
		$this->collection = $this->explodeCollection($this->collectionContent);
		
		// error_log(print_r($this->collection,1));

		$item = array_shift($this->collection);
		
	//	error_log("ITEM: " . $item['item']);
		if($item['item'] == "@Page") {
			$item = array_shift($this->collection);
	//		error_log("ITEM2: " . $item['item']);
			$this->collectionType = $item['item'];
			// get the section ID 
			if ($item['item'] == "Sections") {
				$this->sectionID = $item['options'];
			}
		}
		else {
			$this->collectionType = "UNKNOWN";
		}
		
		// Now get the content to loop output. e.g. between the first { and last }
		$this->startLoopPos = strpos($this->fieldFunction, '{');
		$this->loopContent = substr($this->fieldFunction, $this->startLoopPos + 1);	
	//	error_log('*********************************');
		
		/* Two options at the moment Children and Sections 							
			{|@foreach(item in @Page.Children.Where("!hidePage")) {  
					<li>{|@item.Name|}</li>
				}|}
		
		{|@foreach(section in @Page.Sections(1)) {
			<h2>{|@section.heading1|}</h2>
		}
		*/
							
		$loopOutput = '';
		
		switch($this->collectionType) {
			case "Children":
				// now count the children - TO DO this will be more complicated with filters and sort orders and limits (e.g. first 5 ) @item.SomeField.Where('someethingisTrue');
				$numChildren = 0;

				// for each piece of related content.. 
				//error_log("RELATED COUNT = " . count($this->content->relatedContent));
				
				// as per sections below - copy and paste - TODO refactor
				if (isset($content->variables[$this->varName])) {
					$this->valid = false;
					$this->fieldContent = "Variable name error - already exists";
				} else {
					$this->content->variables[$this->varName]['type'] = "child";
				//	$this->content->variables[$this->varName]['childID'] = $this->sectionID;
				}
				
				
				if (isset($this->content->relatedContent) && count($this->content->relatedContent) > 0) {
					$sortByPos = in_multiarray("SortBy", $this->collection, "item");
					$relatedContent = $this->content->relatedContent;
					// error_log(print_r($relatedContent,1));
//					error_log("FIRST: ". $this->content->relatedContent[0]['lastUpdatedDate']);
					
					if($sortByPos >= 0) {
						// sort!
						$sortStr = $this->collection[$sortByPos]["options"];
						// if there is " DESC" in the string then set descending sort to true and replace
						$descPos = strpos(strtoupper($sortStr), " DESC");
						$meta = array();
						
						// look for descending
						if($descPos){
							$meta['descending'] = true;
							$sortStr = substr($sortStr, 0, $descPos);
						} else {
							$meta['descending'] = false;
						}
						$meta['field']  = trim(str_replace('"', '', $sortStr));			

						// check if a field is an internal field and not a content field (e..g nodeID or createdDate
						$internalFields = array("nodeID", "nodeName", "level", "pageType", "URL", "createdDate", "createdById", "createdBy", "lastUpdatedDate", "lastUpdatedBy", "lastUpdatedBy", "lastUpdatedById", "sortOrder");
						if (in_array($meta['field'], $internalFields)) {
							$meta['internalField'] = true;	
						} else {
							$meta['internalField'] = false;
						}			
						usort($relatedContent, content_sorter($meta));
					}
					
					foreach($relatedContent as $child)
					{ 
						// TO DO more matching if we us .Where('something is true');
						// Check if it's a child
						if ($child['level'] > 0) {
							$numChildren++;
			
							// set the current child node ID 
							$this->content->variables[$this->varName]['childID'] = $child['nodeID'];
							
							// Now get the CMS helper to parse the loop content. 
							$loopOutput .= $this->content->parseTemplate($this->loopContent);
						}
					}
				}
				break;
			case "Sections":
				// first find the fields in the sections template section
				//	$fields = parent::findFields($this->loopContent);
				
				// add the field var name to the variables in the content of the main CMS helper
				// check if this is already set and throw an error if it is (to stop nested use of the same var name - probably valid in programming
				// but too hard to deal with here
				if (isset($content->variables[$this->varName])) {
					$this->valid = false;
					$this->fieldContent = "Variable name error - already exists";
				} else {
					$this->content->variables[$this->varName]['type'] = "Section";
					$this->content->variables[$this->varName]['sectionID'] = $this->sectionID;
				}
					
					
				foreach($this->content->sections[$this->sectionID]['sectionInstances'] as $section) {
					$childOutput = '';
					$lastChildPos = 0;
					
					// for each section Instance update the @section variable in the content variable with the current instance 
					$this->content->variables[$this->varName]['type'] = 'section';
					$this->content->variables[$this->varName]['sectionInstanceID'] = $section;

					// Now get the CMS helper to parse the loop content. 
					$loopOutput .= $this->content->parseTemplate($this->loopContent);
				}
				// finally kill the variable in case it's reused in a future loop
				unset($this->content->variables[$this->varName]);
				
				break;
			default:
				if (DEBUG) {
					$loopOutput = "ERROR - invalid collection";
				} else {
					$loopOutput = "";
				}
				break;
		}
		// finally add the loop output to the main output
		$this->fieldContent = $loopOutput;	
	}			
		
	
	function findClosingBracket($testString) {
		// finds the end of the field - just before the loop content should start
		// incoming str does NOT have a matching opening bracket - this might be changed 
		$openBrackets = 0;
		for ($i = 0; $i < strlen($testString); $i++) {
			if ($testString[$i] == '(') {
				$openBrackets++;
			}

			if ($testString[$i] == ')' && $openBrackets == 0) {
				return $i;
			} 
			
			if ($testString[$i] == ')' && $openBrackets != 0) { 
				$openBrackets--;
			}
		}
		return 0;
	}
	
	function explodeCollection($collectionStr) {
		// take a collection and explodes it with the item (Where, Sort etc) and any separate options ('sort ascending')
		// in the future it might need to explode the options as an array but for now only breaking it up - also options have the brackets which is probably
		// wrong for future reqs 
		$openBrackets = 0;
		$strMode = false;
		$collection = array();
		
		$collectionNameStr = ''; // holds the collection method name  e.g. Where or Sort
		$collectionOptionsStr = ''; // holds the options e.g. the bit in brackets Where('isVisible')

		for ($i = 0; $i < strlen($collectionStr); $i++) {
			
			if (!$strMode) {
				if ($collectionStr[$i] == '(') {
					$openBrackets++;
				}

				if ($collectionStr[$i] == ')') {
					$openBrackets--;
				} 
				
				// todo escape str?
				if ($collectionStr[$i] == '"') { 
					$strMode = !$strMode;
				}
				if ($collectionStr[$i] == '.') {
					// found a collection item - add to the array and blank out the outputstr
					array_push($collection, array("item" => $collectionNameStr, 
													"options" => $collectionOptionsStr));
					$collectionNameStr = '';
					$collectionOptionsStr = '';
					continue;
				}
			}
			if ($openBrackets > 0 || $collectionStr[$i] == ')')  {
				// dirty hack for now
				if ($collectionStr[$i] != "(" && $collectionStr[$i] != ")" && $collectionStr[$i] != "'" && $collectionStr[$i] != "'")
					$collectionOptionsStr .= $collectionStr[$i];
			} else {
				$collectionNameStr = $collectionNameStr . $collectionStr[$i];
			}
		}
		// push last one
		array_push($collection, array("item" => $collectionNameStr, 
													"options" => $collectionOptionsStr));
													
		return ($collection);
	}
}


class ModuleHelper {
	// @mod - gets output from custom code modules. 
	 // EXAMPLE: {|@mod('HelloWorld')|}

	public $keyword;
	public $moduleName;
	public $fieldContent;
	public $valid;
	public $content; // reference to the master content holder (the CMS helper) - should this be a static // singleton instead?
	
	function __construct(&$content, $fieldTemplate) {
		$this->content = &$content;
		// TO DO - validate the template better - at the moment regexp but nothing checking brackets and no nesting etc.
		$this->valid = true;
		
		// keyword is up to first bracket
		$this->keyword = substr($fieldTemplate, 0, strpos($fieldTemplate, '('));
	
		// get module name TODO could this be a variable rather than always a string?
		$this->moduleName = str_replace("'", "", trim(substr($fieldTemplate, strpos($fieldTemplate, '(') + 1, strpos($fieldTemplate, ')') - strpos($fieldTemplate, '(') - 1)));
		
		// validate module name
		$module = new Module($this->moduleName, $this->content);
		
		if($module->valid) {
			$this->fieldContent = $module->output;
		} else {
			$this->valid = false;
			$this->fieldContent = $module->output;
		}
	}			
}


class IfHelper {
	// @if = helper to determine if and if else conditionals 
	 // EXAMPLE: 
	 /* {|@if({|Page.ifVar|} == 4)
			{|if|}
				<h4>IF IS FOUND TO BE TRUE!</h4>
			{|/if|}
			{|else|}
				<h4>{|Page.pageTitle|}</h4>
			{|/else|}
		|} */ 
	public $conditional;
	public $fieldContent;
	public $valid;
	public $content; // reference to the master content holder (the CMS helper) - should this be a static // singleton instead?
	public $ifContent;
	public $elseContent;
	
	
	function __construct(&$content, $fieldTemplate) {
		$this->content = &$content;
		// TO DO - validate the template better - at the moment regexp but nothing checking brackets and no nesting etc.
		$this->valid = true;
		// error_log($fieldTemplate);

		// 1. get conditional
		$conditionalStartPos = 0;
		$conditionalEndPos = 0;
		findMatchingTags($fieldTemplate, '(', ')', $conditionalStartPos, $conditionalEndPos, $this->conditional); 
		
		//error_log("**". $this->conditional ."***");

		// 2. replace any fields with the content - ensuring strings are enclosed in ""
		// Now get the CMS helper to parse the conditional content - e.g. convert fields in the condition to values. 
		$this->conditional = $this->content->parseTemplate($this->conditional, true);
		// error_log("**". $this->conditional ."***");
		
		// 3. Evaluate the conditional statement (might be a recursive call to resolve complex conditionals
		$CurConditional = new Conditional($this->conditional);
		
		// 4. Get if content
		$ifStartPos = 0;
		$ifEndPos = 0;
		findMatchingTags($fieldTemplate,  STARTTOKEN.'if'.ENDTOKEN, STARTTOKEN.'/if'.ENDTOKEN, $ifStartPos, $ifEndPos, $this->ifContent); 
		
		$elseStartPos = 0;
		$elseEndPos = 0;
		// get content after the close of the if statement o/w nested ifs will cause probs
		$fieldTemplateAfterIf = substr($fieldTemplate, $ifEndPos);
		findMatchingTags($fieldTemplateAfterIf, STARTTOKEN.'else'.ENDTOKEN, STARTTOKEN.'/else'.ENDTOKEN, $ifStartPos, $ifEndPos, $this->elseContent); 
		
		
	//	$ifContentStartPos = strpos($this->fieldContent, STARTTOKEN) + strlen(STARTTOKEN);
	//	$ifContentEndPos = strpos($this->fieldContent, ENDTOKEN) - strlen(ENDTOKEN);
		//error_log("**IF**". $this->ifContent ."**/IF***");
		//error_log("**ELSE**". $this->elseContent ."**/ELSE***");
						
		// 5. output either if or else depending on the result of the conditional eval				
		if  ($CurConditional->result) {
		//		$this->fieldContent = $this->content->parseTemplate($this->ifContent);
			$this->fieldContent = rtrim(trim($this->content->parseTemplate($this->ifContent), " \n\r\0\x0B"), "\t");
		} else {
			$this->fieldContent = rtrim(trim($this->content->parseTemplate($this->elseContent), " \n\r\0\x0B"), "\t");
		}

	}			
}

// func for usort - takes a meta data with the sorting field / key and order
function content_sorter($meta)
{
	// TO DO check this work - looks like it's comparing the field rather than the value in teh field?
	
	$field = $meta['field'];

	// if it's an internal field then it's the field otherwiseh it's ['content']['field']
	if($meta['internalField']) {
		if($meta['descending']) {
			// return strnatcmp($b[$meta['field']], $a[$meta['field']]);
			return function ($a, $b) use ($field) {
				return strnatcmp($b[$field], $a[$field]);
			};
		}
		return function ($a, $b) use ($field) {
				return strnatcmp($a[$field], $b[$field]);
			};
	} else {
			if($meta['descending']) {
			return function ($a, $b) use ($field) {
				return strnatcmp($b['content'][$field], $a['content'][$field]);
			};
		}
		return function ($a, $b) use ($field) {
				return strnatcmp($a['content'][$field], $b['content'][$field]);
			};
	}
}
	
?>