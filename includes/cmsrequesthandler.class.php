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
				// TO DO load this from a text file (not using the db for added safety)
				$fourohfour = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">  
					<html>  
					  <head>  
						<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>  
						<title>404 - Page not found</title>  
						<style>
							body { background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAxMC8yOS8xMiKqq3kAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzVxteM2AAABHklEQVRIib2Vyw6EIAxFW5idr///Qx9sfG3pLEyJ3tAwi5EmBqRo7vHawiEEERHS6x7MTMxMVv6+z3tPMUYSkfTM/R0fEaG2bbMv+Gc4nZzn+dN4HAcREa3r+hi3bcuu68jLskhVIlW073tWaYlQ9+F9IpqmSfq+fwskhdO/AwmUTJXrOuaRQNeRkOd5lq7rXmS5InmERKoER/QMvUAPlZDHcZRhGN4CSeGY+aHMqgcks5RrHv/eeh455x5KrMq2yHQdibDO6ncG/KZWL7M8xDyS1/MIO0NJqdULLS81X6/X6aR0nqBSJcPeZnlZrzN477NKURn2Nus8sjzmEII0TfMiyxUuxphVWjpJkbx0btUnshRihVv70Bv8ItXq6Asoi/ZiCbU6YgAAAABJRU5ErkJggg==);}
							.error-template {padding: 40px 15px;text-align: center;}
							.error-actions {margin-top:15px;margin-bottom:15px;}
							.error-actions .btn { margin-right:10px; }
							.homelink {
								width: 150px;
								margin: 0 auto;
							}
							.homelink p{
								position: relative;
								top: -41px;
								left: 26px;
							}
							.homelink a {
								font-weight: 600;
								font-size: 16px;
								text-decoration: none;
							}
							.home {
								font-size: 9px;
								height: 1em;
								width: 0.5em;
								margin-top: 1em;
								margin-left: -1em;
								border-bottom: none;
								border-right: 1.5em solid #2C2C2C;
								border-left: 1.5em solid #2C2C2C;
								border-top: 1.4em solid #2C2C2C;
								position: relative;
								}

								.home::before {
								border-left: 2.4em solid transparent;
								position: absolute;
								content: "";
								top: -2.8em;
								right: -2.1em;
								width: 0em;
								height: 0em;
								border-right: 2.4em solid transparent;
								border-bottom: 1.5em solid #2C2C2C;
							}
						</style>
					  </head>  
					  <body>  
						<div class="container">
							<div class="row">
								<div class="col-md-12">
									<div class="error-template">
										<h1>
											Oops!</h1>
										<h2>
											404 Not Found</h2>
										<div class="error-details">
											Sorry, an error has occured, Requested page not found!
										</div>
										<div class="error-actions">
										<br />
											<div class="homelink">
												<a href="/"><div class="home"></div>
												<p>Take Me Home</p></a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					  </body>  
					</html>'; 
					
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
				$cms->getPage($this->pathArray);
				
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