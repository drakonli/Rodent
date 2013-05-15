<?php 
class BaseController {
	protected $controller;
	protected $action;
	protected $layout;
	
	public function __construct($controller, $action, $customVariables = array()){
		$this->controller = $controller;
			
		if(!$action)
			$this->action = isset($this->action) ? $this->action : App::get()->getSetting('defaultAction');
		else
			$this->action = $action;
		
		$action = $this->action . 'Action';
		
		$this->layout = isset($this->layout) ? $this->layout : App::get()->getSetting('defaultLayout');
		
		if(!method_exists($this, $action)){
			throw new BaseException('Method ' . $action . '.php was not found in controller "' . get_class($this) . '"', 'controllers');
		}
		
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