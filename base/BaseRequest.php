<?php
class BaseRequest {
	public function getQuery(){
		return $_SERVER['REQUEST_URI'];
	}
}