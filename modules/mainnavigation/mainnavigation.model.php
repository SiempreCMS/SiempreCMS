<?php

require_once('mainnavigation.class.php');

class MainNavigationModel
{
	public $response;
	public $CMSContent;
	
    public function __construct(&$CMSContent) {
		// $this->CMSContent = &$CMSContent;
		$mainNavigation = new MainNavigation($CMSContent);
		
		$this->response['result'] = true;		
		
		$this->response['output'] = $mainNavigation->createMainNavigation(); 
    } 
}