<?php

class BooksModel extends BaseModelSql
{
	public $id;
	public $title;
	public $isbn;
	public $created;
	public $modified;

	protected function propertiesList(){
		return array('primary' => 'id','title','isbn','created','modified');
	}

	protected function beforeSave(){
		if(!isset($this->created))
			$this->created = time();
	}
}