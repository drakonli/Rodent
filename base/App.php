<?php
// instance - экземпляр(в данном случае класса)
// тут мы делаем синглтон - чтоб у нас был 1 объект этого класса. ну помнишь? работало, просто пхп так делает.
// типа сам отвественнен если юзаешь функции без объявление статик как статик. вот и все. суть в том,
// чтоб у тебя был один объект через вызов функции getInstance.
// магическии функции, которыми в теории можно создать объект. мы их делаем приватными.
// да без разнцы, я реально не знаю сам. главное, чтоб закрыть и все.
// мы их привытными делаем. тобишь нельзя сделать new App(); потому что констракт приватный.
// поал? констракт вызывается определении нового объекта класса, а по скольку он приватный его
// публично вызывать нельзя
// new App() нельзя сделать - выдаст ошибку. скажет, что констракт приватный. ибо он каждый раз вызывается при new App();
// констракт используется для того, чтоб типа мол при каждом создании объекта чо-то делать.
// вот это и есть синглтон. когда мы можем создать только 1 объект. и то через функцию. и постоянно вызывать эту функцию
// чтоб работать с методами класса. тогда вся информация будет хранится в этом объекте.
// мы присвоили этот объект глобальному свойству класса App - $_instance. понял не? и если мы раз ему присвоили
// то опять присваивать уже не будет, ибо оно не будет "нуль". просто будет возвращать.
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
    	$router = new BaseRouter();
    	$controllerAction = $router->getControllerAction();
    	$controllerName = $controllerAction['controller'];
    	$controllerAction['controller'][0] = strtoupper($controllerAction['controller'][0]);
    	$controllerAction['controller'] = $controllerAction['controller'].'Controller';
		
		$controller = new $controllerAction['controller']($controllerName, $controllerAction['action']);
    }
    
    public static function autoload($class_name){
	    $folder = 'components'; // папка с компонентами
	    if(preg_match('/Controller$/', $class_name))
	    	$folder = 'controllers'; //
	    if(preg_match('/^Base/', $class_name))
	    	$folder = 'base';
	    	
	    include_once($folder . '/' . $class_name . '.php');
    }
    
    public function getSetting($setting){
    	global $config;
    	$settingValue = null;
    	if(isset($config[$setting]))
    		$settingValue = $config[$setting];
    	
    	return $settingValue;
    }

}

spl_autoload_register(array('App','autoload'));
// это то, что делает функция spl_autoload_register - мы регестрируем какую-то функцию(в данном случае в классе App)
// как __autoload(); тобишь когда класс не будет находится будет вызываться не __autoload(), а App::autoload()
// понял? это самописная функция. смотри. в том, что она находится не в глобальном пространстве хуй знает где. а в нашем классе
// который отвечает за все приложение. app - application. разделение логики. потому что в индексе нету записей
// типа длинных, которые там не должны быть.
// функция, которая запускается вместо __ аутолоад - вот эта функция внутри апп. и да - она единственная будет.