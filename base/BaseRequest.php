<?php
class BaseRequest {
	public function getQuery(){
		return $_SERVER['REQUEST_URI'];
	}
	
	public function getParams(){
		return $_REQUEST;
	}

	public function getCookies(){
		return $_COOKIE;
	}
	
	public function getParam($name, $default = null){
		return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
	}
	
	public function getCookie($name, $default = null){
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
	}
	
	public function getSession($name, $default = null){
		return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
	}
	
	public function setSession($name, $value){
		$_SESSION[$name] = $value;
		return $_SESSION[$name];
	}
	
	public function removeSession($name){
		unset($_SESSION[$name]);
		return true;
	}
	
	public function setCookie($name, $value, $duration){
		$expires = time() + ($duration * 24 * 60 * 60);
		setcookie($name,$value,$expires);
	}
	
	public function removeCookie($name){
		setcookie($name, "", time()-3600);
		unset($_COOKIE[$name]);		
	}
}