<?php
require_once('helloworld.class.php');

class HelloWorldModel
{
	public $response;
	public $CMSContent;
	
    public function __construct(&$CMSContent) {
		// $this->CMSContent = &$CMSContent;
		$helloWorld = new HelloWorld($CMSContent);
		
		$this->response['result'] = true;		
		
		$this->response['output'] = $helloWorld->createHelloWorld(); 
    } 
}
?>