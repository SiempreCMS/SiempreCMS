<?php

class HelloWorld {
	public $CMSContent;
	public $output;
	
	// A business logic class to keep the model skinny(ier)
	public function __construct(&$CMSContent) {
		$this->CMSContent = $CMSContent;
	}
	 
	 
	public function createHelloWorld() {
		$this->output .= '<h>Hello World!</h2>';
		return $this->output;
	}		
}
?>
