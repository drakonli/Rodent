<?php

class BookModel extends BaseModelSql
{
	public $id;
	public $title;
	public $author;
	public $isbn;
	public $created;
	public $modified;

	protected function propertiesList(){
		return array('primary' => 'id','author','title','isbn','created','modified');
	}
	
	protected function setRelations(){
		return array('author' => 'AuthorModel');
	}
	
	protected function idValidate($propValue){
		if(preg_match('/[^0-9]/i',$propValue, $match))
			return false;
	
		return true;
	}
	
	protected function beforeSave(){
		if(!isset($this->created))
			$this->created = time();
	}
}