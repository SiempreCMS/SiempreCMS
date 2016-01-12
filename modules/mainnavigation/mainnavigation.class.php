<?php

class MainNavigation {
	public $CMSContent;
	public $output;
	
	// A business logic class to keep the model skinny(ier)
	public function __construct(&$CMSContent) {
		$this->CMSContent = $CMSContent;
	}
	 
	 
	public function createMainNavigation() {
		// get the JSON from the site settings for the main nav		
		$mainNavObj = json_decode($this->CMSContent->getFieldValue('MainNavigation', 'Site'), true);
		
		if(count($mainNavObj) > 0) {
			
			$this->output .= '<nav> 
								<div class="mobile-nav vifddsible-sm-* clearfix">
								<h3>Menu</h3>
									<button id="nav-menu-button" type="button" class="nav-button collapsed open">
										<span class="sr-only">Toggle navigation</span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
									</button>
								</div>
						<ul id="nav-menu" class="nav-menu nav-open" style="display: block;">';
			foreach($mainNavObj as $key => $node)
			{
				$this->createNode($node, 1);
			}
			$this->output .= '</ul> 
						</nav>';
		}
		return $this->output;
	}	
	
	
	private function createNode($node, $level)
	{
		$curNode = false;	// is this the current page
		$isChild = false;  // if the node is a child of this node (e.g. home > News > some article), we'd want to show News as selected / active with a special class when at "some article"
		if($this->CMSContent->nodeID == $node['nodeID'])
		{
			$curNode = true;
		}
		else {
			$parentNodesArray = explode(',', $this->CMSContent->getFieldValue('parentIDs'));
			if(in_array($node['nodeID'], $parentNodesArray))
			{
				$isChild = true;
			}
		}
		
		$classStr = '';
		// work out if we need classes
		if($curNode)
		{
			$classStr = ' class="current"';
		}
		if($isChild)
		{
			$classStr = ' class="active"';
		}
		
		
		if(count($node['children']) == 0) 
		{
			$this->output .= '<li'.$classStr.'>' . $this->createNodeHtml($node['nodeID']) . '</li>';
		}
		else 
		{
			$this->output .= '<li'.$classStr.'>';
			$this->output .= $this->createNodeHtml($node['nodeID']);
			$this->output .= '<ul>';
			foreach($node['children'] as $key => $childNode)
			{
				$this->createNode($childNode, $level + 1);
			}
			$this->output .= '</ul>';
			$this->output .= '</li>';
		}
	}
	
	
	private function createNodeHtml($nodeID)
	{
		$nodeOutput = '';
		
		$errorFound = false;
		$pageTitle = $this->CMSContent->getRelatedNodeFieldValue($nodeID, 'PageTitle', $errorFound);
		if($errorFound || $pageTitle == '')
		{
			$pageTitle = $this->CMSContent->getRelatedNodeFieldValue($nodeID, 'nodeName', $errorFound);
		}
		$url = $this->CMSContent->getRelatedNodeFieldValue($nodeID, 'URL', $errorFound);
		if($errorFound)
		{
			$url = '#';
		}
		else 
		{
			$url = '/' . $url;
		}
		
		$nodeOutput .= '<a href="' . $url .'">' . $pageTitle . '</a>';
		
		return $nodeOutput;
	}
}
?>
