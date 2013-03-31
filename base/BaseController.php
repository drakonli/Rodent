<?php 
class BaseController {
	protected $controller;
	protected $action;
	protected $layout;
	
	public function __construct($controller, $action, $customVariables = array()){
		$this->controller = $controller;
		$this->action = $action;
		$action = ucfirst($this->action) . 'Action';
		$this->layout = 'layout';
		
		$this->$action($customVariables);
	}
	
	protected function renderTemplate($template,$variables = array()){
		foreach($variables as $key => $value){
			$$key = $value;
		}
		include_once(App::get()->rootDir . '/templates/' . $this->controller . '/' . $template . '.php');
	}
	
	protected function render($template,$variables = array()){
		include_once(App::get()->rootDir . '/templates/' . $this->layout . '.php');
	}
	
	protected function getName(){
		return $this->controller;
	}
}