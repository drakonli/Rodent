<?php
class BaseRequest {
	public function getQuery(){
		return $_SERVER['REQUEST_URI'];
	}
	
	public function getAllParams(){
		return $_REQUEST;
	}

	public function getParam($name, $default = null){
		return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
	}
}