<?php

class AuthorModel extends BaseModelSql
{
	public $id;
	public $name;

	protected function propertiesList(){
		return array('primary' => 'id','name');
	}
}