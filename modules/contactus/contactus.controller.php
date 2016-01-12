<?php
class ContactUsController
{
    private $model;
 
    public function __construct($model){
        $this->model = $model;
    }
 
    public function clicked() {
        $this->model->response['output'] = "<p>Updated Data, thanks to MVC and PHP!</p>";	
    }
	
	public function handleSubmit() {
	
		// do something - perhaps email this?
		$this->model->handleSubmit();
	}
}