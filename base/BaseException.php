<?php
class BaseException extends Exception
{
	protected $message;
	protected $function = 'defaultCallback';
	
	public function __construct($message, $function = null, $code = 0){
		$this->message = $message;
		$function 	   = $function . "Callback";
		if(method_exists($this, $function)){
			$this->function = $function;
		}
	}
	
	public function callBack(){
		$function = $this->function;
		$this->$function();
	}
	
	private function defaultCallback(){
		header("HTTP/1.0 500 Not Found");
		new ErrorsController('errors','index', array('error' => $this->message));
	}
	
	private function controllersCallback(){
		header("HTTP/1.0 404 Not Found");
		new ErrorsController('errors','index', array('error' => '404 This page was not found on this server!'));
	}
}