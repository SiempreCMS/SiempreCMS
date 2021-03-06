<?php
class ContactUsView
{
    private $model;
    private $controller;
 
    public function __construct($controller,$model) {
        $this->controller = $controller;
        $this->model = $model;
    }
 
    public function getResponse() {
        return $this->model->response['output'];
    }
	
	public function getResult() {
        return $this->model->response['result'];
    }
	
	public function getForm() {
        return $this->model->form;
    }
}
