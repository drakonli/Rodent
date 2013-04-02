<?php

class AuthorModel extends BaseModelSql
{
	public $id;
	public $name;

	protected function propertiesList(){
		return array('primary' => 'id','name');
	}
	
	protected function  idValidate($propValue){
		if(preg_match('/[^0-9]/i',$propValue, $match))
			return false;
		
		return true;
	}
	
	protected function nameValidate($propValue){
		if(preg_match('/[^a-z_\-\.]/i',$propValue, $match))
			return false;

		return true;
	}
}