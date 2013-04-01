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
	
	protected function beforeSave(){
		if(!isset($this->created))
			$this->created = time();
	}
}