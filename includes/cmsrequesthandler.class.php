<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
 
//  Purpose:- Handles the page request and directs to the cache and or to the page builder (CMSHelper) then storing back to cache 
// TO DO - merge the restUtils methods into this?

class CMSRequestHandler {
	private $time_start;
	private $requestData;
	private $pathArray;
	private $requestVars;
	private $pageRequested;
	
	function __construct() 
	{
		// for the page execution time in the debug. 
		$this->time_start = microtime(true);
		$this->requestData = RestUtils::processRequest(); 

		// get the path array - e.g. /home/about-us/welcome
		$this->pathArray = $this->requestData->getPathArray();
		$this->requestVars = $this->requestData->getRequestVars();
		$this->pageRequested = end($this->pathArray);

		// TO DO - merge in the REST UTILS? It's not rest anymore too!?

		/* 
		1.  First check if we have an entry for the page path 
		2.  If exists then check the cache - TODO
		3.  Otherwise build the page from the DB
		4.  Write this out and add to cache if cacheable - TODO

		Creating a page
		1.  Get the template 
		2.  Get all data for the page
		3.  Run it through the template engine
		*/ 
		
		// 1. Check cache
		if(CACHE_ENABLED) {
			$cache = new CMSCache($this->pathArray);
			
			if($cache->inCache)
			{
				$outputContent = $cache->cacheItem->content;
				$debug = false;		
				if(isset($this->requestVars['debug']) && DEBUG) {
					$debug = TRUE;
				}
						
				if($debug === TRUE)
				{ 
					// Get time for page creation execution time 
					$time_end = microtime(true);
					$timeTaken = $time_end - $this->time_start;

					$debugText = "	<div id='debug' style='background: #ffdd00; color: #333; font-size: 12px !important; line-height: 1.62em;'>
						<table>
						<tr><td><b>CACHE HIT:</b></td><td>".htmlentities(print_r($this->pathArray,1), ENT_QUOTES,'ISO-8859-1')."</td></tr>
						<tr><td><b>Cache created:</b></td><td>".$cache->cacheItem->created."</td></tr>
						<tr><td><b>Cache expires:</b></td><td>".$cache->cacheItem->expiry."</td></tr>
						<tr><td><b>Path Requested:</b></td><td>".htmlentities(print_r($this->pathArray,1), ENT_QUOTES,'ISO-8859-1')."</td></tr>
						<tr><td><b>Page Requested:</b></td><td>".htmlentities($this->pageRequested, ENT_QUOTES,'ISO-8859-1')."</td></tr>
						<tr><td><b>Request Variable:</b></td><td>".htmlentities(print_r($this->requestVars,1), ENT_QUOTES,'ISO-8859-1')."</td></tr>
						<tr><td><b>Time Taken:</b></td><td>$timeTaken</td></tr>
						</table>
					</div>";
					
					$outputContent = $this->addDebugToBody($outputContent, $debugText);
				}
				RestUtils::sendResponse(200, $outputContent, 'text/html');
			}
		}
		
		
		$cms = new CMSHelper();
		$cms->checkPage($this->pathArray);


		switch($cms->pageType)
		{
			case 'notfound':
				// Gets the 404 from a text file (not using a CMS node for added safety ? - Perhaps this needs changing)
				$fourohfour = file_get_contents('fourohfour.html', true); 	
				RestUtils::sendResponse(404, $fourohfour, 'text/html');
				break;
				
			case '301redirect':
				RestUtils::sendResponse(301, $cms->pagePath, 'text/html');
				break;
				
			case '302redirect':
				RestUtils::sendResponse(302, $cms->pagePath, 'text/html');
				break;
			
			case 'module':
				$module = new Module($cms->module);
				$output = $module->output;
				RestUtils::sendResponse(200, $output, 'text/html');
				break;
				
			case 'sitemap':
				// this might need to be done differently with different languages
				$sitemapBody = $cms->getSitemap();
				RestUtils::sendResponse(200, $sitemapBody, 'text/xml');
				break;
			
			//case 'cached':
			//	$replacedOutput = 
			
			case 'wildcard':
				// pretty much the same logic as the default normal pages but cached differently?
				
			// normal pages assumed!
			default:
				// get the data and build the page 
				// if getPage returns false it's a new page not yet published - 404 instead of error
				if(!$cms->getPage($this->pathArray, $this->requestVars))
				{
					// Gets the 404 from a text file (not using a CMS node for added safety ? - Perhaps this needs changing)
					$fourohfour = file_get_contents('fourohfour.html', true); 	
					RestUtils::sendResponse(404, $fourohfour, 'text/html');
				}
				
				$debug = false;		
				if(isset($this->requestVars['debug']) && DEBUG) {
					$debug = TRUE;
				}
				
				$replacedOutput = $cms->createPage();
				
				// store to cache// TODO - check if the page is allowed to be cacheable
				$pageCacheable = !$cms->page['noCache'];
				
				if(CACHE_ENABLED && $pageCacheable) 
				{
					$cache->writeCache($replacedOutput);
				}
				
				if($debug === TRUE)
				{ 
					// Get time for page creation execution time 
					$time_end = microtime(true);
					$timeTaken = $time_end - $this->time_start;

					$debugText = "	<div id='debug' style='background: #ffdd00; color: #333; font-size: 12px !important; line-height: 1.62em;'>
				<table>
				<tr><td><b>Path Requested:</b></td><td>".htmlentities(print_r($this->pathArray,1), ENT_QUOTES,'ISO-8859-1')."</td></tr>
				<tr><td><b>Page Requested:</b></td><td>".htmlentities($this->pageRequested, ENT_QUOTES,'ISO-8859-1')."</td></tr>
				<tr><td><b>Page Node:</b></td><td>".htmlentities($cms->nodeID, ENT_QUOTES,'ISO-8859-1')."</td></tr>
				<tr><td><b>Page Data:</b></td><td>".htmlentities(print_r($cms->page,1), ENT_QUOTES,'ISO-8859-1')."</td></tr>
				<tr><td><b>Request Variable:</b></td><td>".htmlentities(print_r($this->requestVars,1), ENT_QUOTES,'ISO-8859-1')."</td></tr>
				<tr><td><b>Fields in template:</b></td><td>".htmlentities(print_r($cms->fieldsFound,1), ENT_QUOTES,'ISO-8859-1')."</td></tr>
				<tr><td><b>Page Content:</b></td><td>".htmlentities(print_r($cms->page,1))."</td></tr>
				<tr><td><b>Related Pages:</b></td><td>".substr(htmlentities($cms->relatedNodeIDsStr, ENT_QUOTES,'ISO-8859-1'), 0, 1000)."</td></tr>
				<tr><td><b>Other Page Content:</b></td><td>".substr(htmlentities(print_r($cms->relatedContent,1), ENT_QUOTES,'ISO-8859-1'), 0, 1000)."</td></tr>
				<tr><td><b>Page Sections:</b></td><td>".htmlentities(substr(print_r($cms->sections,1), 0, 1000), ENT_QUOTES,'ISO-8859-1')."</td></tr>
				<tr><td><b>Time Taken:</b></td><td>$timeTaken</td></tr>
				</table>
			</div>";
			
					$replacedOutput = $this->addDebugToBody($replacedOutput, $debugText);
					
				}
					RestUtils::sendResponse(200, $replacedOutput, 'text/html');
			break;
		}
	}
	
	private function addDebugToBody($content, $debugText)
	{
		// find start of body tag
		$startBody = stripos($content, '<body');
		// now find closing tag
		$endBody = stripos($content, '>', $startBody);
		
		$content = substr($content, 0, $endBody + 1) . $debugText . substr($content, $endBody + 1);
		
		return $content;
		
	}
}