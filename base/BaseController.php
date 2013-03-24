<?php 
class BaseController {
	protected $controller;
	protected $action;
	protected $layout;
	
	public function __construct($controller, $action){
		$this->controller = $controller;
		$this->action = $action;
		$action = $this->action.'Action';
		$this->layout = 'layout';
		
		$this->$action();
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