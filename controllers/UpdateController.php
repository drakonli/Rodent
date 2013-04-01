<?php
class UpdateController extends Controller
{
	protected $action = 'books'; // default action for this controller

	public function booksAction()
	{
		$this->parseRequest();
		//$xml = Array2XML::createXML('books', $books);
		//echo $xml->saveXML();

	}

	public function authorsAction()
	{

	}

	private function parseRequest(){
		$type = App::get()->request->getParam('type','json');
		$id   = App::get()->request->getParam('id');

		if($type != 'json' && $type != 'xml'){
			return false;
		}

		if($id){
			$ids = explode(',',$id);
			if(!count($ids)){
				return false;
			}
		}

		$request = array('type' => $type, 'id' => $id);

		return $request;
	}
}