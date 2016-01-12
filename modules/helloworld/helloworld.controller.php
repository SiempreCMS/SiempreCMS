<?php
class HelloWorldController
{
    private $model;
 
    public function __construct($model){
        $this->model = $model;
    }
}