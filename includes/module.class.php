<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
class Module {
	// @mod - handles the communication with the custom modules. 
	public $moduleName;
	public $valid;
	public $output;
	public $result;
	public $content; // reference to the master content holder (the CMS helper) - should this be a static // singleton instead?
	
	function __construct($moduleName, &$content) {
		$this->content = &$content;
		$this->valid = true; 
		
		// validate module name
		if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $moduleName)) {
			$this->moduleName = $moduleName;
				
			$controllerPath = '../modules/' . strtolower($this->moduleName) . '/' . strtolower($this->moduleName) . '.controller.php';
			$modelPath = '../modules/' . strtolower($this->moduleName) . '/' . strtolower($this->moduleName) . '.view.php';
			$viewPath = '../modules/' . strtolower($this->moduleName) . '/' . strtolower($this->moduleName) . '.model.php';
			
			if (is_file($controllerPath))
			{
				require_once($controllerPath);
			} else {
				$this->valid = false;
				$this->output = "ERROR " . $this->moduleName . " Controller missing";
			}
			if (is_file($modelPath))
			{
				require_once($modelPath);
			} else {
				$this->valid = false;
				$this->output = "ERROR " . $this->moduleName . " Model missing";
			}
			if (is_file($viewPath))
			{
				require_once($viewPath);
			} else {
				$this->valid = false;
				$this->output = "ERROR " . $this->moduleName . " View missing";
			}
			
			if($this->valid) {
				// TODO create a special class which contains the CMS content and helper method to parse template content etc so modules can use
				// cms content
				$modelName = $this->moduleName . 'Model';
				$controllerName = $this->moduleName . 'Controller';
				$viewName = $this->moduleName . 'View';
				
				$model = new $modelName($this->content); // e.g. new HelloWorldModel()
				$controller = new $controllerName($model);
				$view = new $viewName($controller, $model);
				
				// validate and check the action - prefer POST over GET
				if (isset($_POST['action']) && !empty($_POST['action']) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', 
				 $_POST['action'])) {
					$action = trim($_POST['action']);
					if(method_exists($controller, $action)) {
						$controller->{$_POST['action']}();
					}
				}
				else if (isset($_GET['action']) && !empty($_GET['action']) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $_GET['action'])) {
					$action = trim($_GET['action']);
					if(method_exists($controller, $action)) {
						$controller->{$_GET['action']}();
					}
				}
				
				$this->result = $view->getResult();
		//		if($this->result) {
		//			$this->output = $view->getResponse();
		//		} else {
		//			$this->output = $view->getForm() . "<p class=\"error\">" . $view->getResponse() . "</p>";
		//		}
				$this->output = $view->getResponse();
				
			}
		} else { // invalid mod name
			$this->valid = false;
			$this->output = "ERROR Invalid module name";
		}
	}
}

?>