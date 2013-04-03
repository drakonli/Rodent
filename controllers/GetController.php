<?php 

class GetController extends Controller
{
	protected $action = 'book'; // default action for this controller

	public function bookAction()
	{
		$parameters = $this->parseRequest();
		
		if(!$parameters){
			$this->sendError('Wrong parameters!');
			App::get()->endApp();
		}
		
		$books = new BookModel();
		$data = array();
		
		if($parameters['id']){
			foreach($parameters['id'] as $id => $value){
				$books->id = $id;
				$currentObject = $books->findOne();
				if(!empty($currentObject)){
					$data[$this->action][] = $currentObject->toArray();
				}
			}
		}
		
		if(!$parameters['id']){
			$objects = $books->findAll();
			foreach($objects as $currentObject){
				$data[$this->action][] = $currentObject->toArray();
			}
		}
		
		$data = $this->formatData($data, $parameters['type']);

		$this->render($this->action, array('data' => $data));
	}
	
	public function authorAction(){
		$parameters = $this->parseRequest();
		
		if(!$parameters){
			$this->sendError('Wrong parameters!');
			App::get()->endApp();
		}
		
		$authors = new AuthorModel();
		$data = array();
		
		if($parameters['id']){
			foreach($parameters['id'] as $key => $id){
				$authors->id = $key;
				$currentObject = $authors->findOne();
				if(!empty($currentObject)){
					$data[$this->action][] = $currentObject->toArray();
				}
			}
		}
		
		if(!$parameters['id']){
			$objects = $authors->findAll();
			foreach($objects as $currentObject){
				$data[$this->action][] = $currentObject->toArray();
			}
		}
		
		$data = $this->formatData($data, $parameters['type']);

		$this->render($this->action, array('data' => $data));
	}
	
	private function parseRequest(){
		$type = App::get()->request->getParam('type', $this->types['default']);
		$id   = App::get()->request->getParam('id');
		$ids  = null;
		
		if(!in_array($type, $this->types)){
			return false;
		}
		
		$this->type = $type;
		
		if($id){
			$id = explode(',',$id);

			if(!count($id)){
				return false;
			}
			
			$ids = array();
			
			foreach($id as $value){
				$ids[intval($value)] = 0;
			}
		}
	
		$request = array('type' => $type, 'id' => $ids);
		
		return $request;
	}
}