<?php
class App {
    protected static $_instance; 
    public $rootDir;
    
    private function __construct() {
    	$this->rootDir = $_SERVER['DOCUMENT_ROOT'];
    }
    
    private function __clone() {}
    private function __wakeup() {}
    
    public static function get() {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
 
        return self::$_instance;
    }
    
    public function application_start(){   	   		
    	try {
    		$this->loadComponents($this->getSetting('components'));
    		$this->router->getControllerAction();
		} catch (BaseException $e) {
		    $e->invokeCallback();
		}
    }
    
    public static function autoload($class_name){
	    $folder = 'components'; // папка с компонентами
	    
	    if(preg_match('/.+Controller$/', $class_name))
	    	$folder = 'controllers'; 
	    
	    if(preg_match('/^Base/', $class_name))
	    	$folder = 'base';
	    
		if(preg_match('/Model$/', $class_name))
	    	$folder = 'model'; 
	   	
	    if(!file_exists($folder . '/' . $class_name . '.php')){
	    	throw new BaseException('File ' . $class_name . '.php was not found in folder "' . $folder . '"', $folder);
	    }
	    
		include_once($folder . '/' . $class_name . '.php');
    }
    
    public function getSetting($setting){
    	global $config;
    	$settingValue = null;
    	if(isset($config[$setting]))
    		$settingValue = $config[$setting];
    	
    	return $settingValue;
    }
    
    public function loadComponents($components, $path = false){
    	if(is_array($components)){
    		foreach($components as $name => $className){
    			$this->$name = new $className;
    		}
    		return true;
    	}
  		return false;
    }
    
    public function endApp($message = null){
    	exit($message);
    }

}

spl_autoload_register(array('App','autoload'));