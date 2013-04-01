<?php 
class BaseRouter extends BaseRequest
{
	public function getControllerAction(){
		$controller = App::get()->getSetting('defaultController');
		$queryArray = $this->parseQuery();
		$action 	= $queryArray['action'];
		
		if($queryArray['controller'])
			$controller = $queryArray['controller'];
		
    	$controllerName = ucfirst($controller) . 'Controller';
		
		$controller = new $controllerName($controller, $action);
	}
	
	private function parseQuery(){
		$query = parent::getQuery();
		
		preg_match('/^\/([^\/\#?]*)\/?([^\/\?#]*)/', $query, $routeMatches);

		$controller = (isset($routeMatches[1]))? $routeMatches[1] : null;
		$action = (isset($routeMatches[2]))? $routeMatches[2] : null;
		
		return array('controller' => $controller, 'action' => $action);
	}
}