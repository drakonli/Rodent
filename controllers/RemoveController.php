<?php 
class RemoveController extends Controller
{
	protected $action = 'book'; // default action for this controller
	
	public function bookAction() 
	{
		$parameters = $this->parseRequest();
		
		if(!$parameters){
			$this->sendError('Wrong parameters!', $parameters['type']);
			App::get()->endApp();
		}
		
		$books = new BookModel();
		$data = array();
		if($parameters['id']){
			foreach($parameters['id'] as $key => $id){
				$books->id = $key;
				$currentObject = $books->findOne();
				if(!empty($currentObject)){
					$data[$this->action][$key] = $currentObject->toArray();
					if($currentObject->remove()){
						$data[$this->action][$key]['removeSuccess'] = true;
					}					
				}
			}
		}
		$data = $this->formatData($data, $parameters['type']);

		$this->render($this->action, array('data' => $data));
	}
	
	public function authorAction(){
		$parameters = $this->parseRequest();
		
		if(!$parameters){
			$this->sendError('Wrong parameters!', $parameters['type']);
			App::get()->endApp();
		}
		
		$authors = new AuthorModel();
		$data = array();
		if($parameters['id']){
			foreach($parameters['id'] as $key => $id){
				$authors->id = $key;
				$currentObject = $authors->findOne();
				if(!empty($currentObject)){
					$data[$this->action][$key] = $currentObject->toArray();
					if($currentObject->remove()){
						$data[$this->action][$key]['removeSuccess'] = true;
					}
				}
			}
		}
		$data = $this->formatData($data, $parameters['type']);

		$this->render($this->action, array('data' => $data));
	}
	
	public function userAction(){
		$parameters = $this->parseRequest();
	
		if(!$parameters){
			$this->sendError('Wrong parameters!', $parameters['type']);
			App::get()->endApp();
		}
	
		$user = new UserModel();
		$data = array();
		if($parameters['id']){
			foreach($parameters['id'] as $key => $id){
				$user->id = $key;
				$currentObject = $user->findOne();
				if(!empty($currentObject)){
					$data[$this->action][$key] = $currentObject->toArray();
					if($currentObject->remove()){
						$data[$this->action][$key]['removeSuccess'] = true;
					}
				}
			}
		}
		
		$data = array ("message" => "Your profile has been deleted successfully.
				You'll be redirected to the main page in 5 seconds");
	
		$this->render($this->action, $data);		
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