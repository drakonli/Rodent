<?php
class UpdateController extends Controller
{
protected $action = 'book'; // default action for this controller

	public function bookAction()
	{
		$parameters = $this->parseRequestBook();
		$modified   = false;
		
		if(!$parameters){
			$this->sendError('Wrong parameters');
			App::get()->endApp();
		}
		
		$book = new BookModel();
		$book->id = $parameters['id'];
		$book = $book->findOne();
		if(empty($book)){
			$this->sendError('This ' . $this->action . ' doesn\'t exist', $parameters['type']);
			App::get()->endApp();
		}
		
		if(isset($parameters['author'])){
			$author = new AuthorModel();
			$author->id = $parameters['author'];
			$author = $author->findOne();
			
			if(empty($author)){
				$this->sendError('Author was not found', $parameters['type']);
				App::get()->endApp();
			}
			
			$modified 	  = true;
			$book->author = $author;
		}
		
		if(isset($parameters['title'])){
			$modified     = true;
			$book->title  = $parameters['title'];
		}
		
		if(isset($parameters['isbn'])){
			$modified 	  = true;
			$book->isbn   = $parameters['isbn'];
		}
		
		if($modified)
			$book->modified = time();
		
		if(!$book->save()){
			$this->sendError('Database error', $parameters['type']);
			App::get()->endApp();
		} 
		
		$data[$this->action][0] = $book->toArray();
		$data[$this->action][0]['addedSuccess'] = true;
		
		$data = $this->formatData($data, $parameters['type']);

		$this->render($this->action, array('data' => $data)); 
	}

	public function authorAction()
	{
		$parameters = $this->parseRequestAuthor();
		
		if(!$parameters){
			$this->sendError('Wrong parameters');
			App::get()->endApp();
		}
		
		
		$author = new AuthorModel();
		$author->id = $parameters['id'];
		$author = $author->findOne();
			
		if(empty($author)){
			$this->sendError('This ' . $this->action . ' was not found', $parameters['type']);
			App::get()->endApp();
		}
		
		if(isset($parameters['name'])){
			$author->name = $parameters['name'];
		}

		if(!$author->save()){
			$this->sendError('Database error', $parameters['type']);
			App::get()->endApp();
		}
		
		$data[$this->action][0] = $author->toArray();
		$data[$this->action][0]['addedSuccess'] = true;
		
		$data = $this->formatData($data, $parameters['type']);
		
		$this->render($this->action, array('data' => $data));	
	}
	
	public function userAction()
	{	
		$parameters = $this->parseRequestUser();
		$modified   = false;
	
		if(!$parameters){
			$this->sendError('Specify data that you need to update');
			App::get()->endApp();
		}	
		
		
		$user = new UserModel();
		$user->id  = $parameters['id'];
				
		$user = $user->findOne();		
		
		if(empty($user)){
			$this->sendError('This username was not found');
			App::get()->endApp();
		}
		
		if(isset($parameters['username'])){
			$modified     = true;
			$user->username  = $parameters['username'];				
		}
		
		if(isset($parameters['pswd'])){
			$modified     = true;
			$user->password = App::get()->generateHash($parameters['pswd']);
		}
		
		if($modified)
			$user->modified = time();		
		
		if(!$user->save()){
			$this->sendError('Database error', $parameters['type']);
			App::get()->endApp();
		}
		
		$data = array ("message" => "Your profile has been updated successfully.
				You'll be redirected to the main page in 5 seconds");
	
		$this->render($this->action, $data);		

	}
	
	private function parseRequestBook(){
		$type   = App::get()->request->getParam('type','json');
		$id     = App::get()->request->getParam('id', null);
		$isbn   = App::get()->request->getParam('isbn', null);
		$author = App::get()->request->getParam('author_id', null);
		$title  = App::get()->request->getParam('title', null);
	
		if($type != 'json' && $type != 'xml'){
			return false;
		}
		
		$this->type = $type;
		
		if(!$id){
			return false;
		}
	
		$request = array('type' => $type, 'isbn' => $isbn, 'author' => $author, 'title' => $title, 'id' => $id);
	
		return $request;
	}
	
	private function parseRequestAuthor(){
		$type   = App::get()->request->getParam('type','json');
		$id     = App::get()->request->getParam('id', null);
		$name   = App::get()->request->getParam('name', null);
	
		if($type != 'json' && $type != 'xml'){
			return false;
		}
	
		$this->type = $type;
		
		if(!$id){
			return false;
		}
	
		$request = array('type' => $type, 'id' => $id, 'name' => $name );
	
		return $request;
	}
	
	public function parseRequestUser() {
		$id    	    = App::get()->request->getParam('id', null);
		$username   = App::get()->request->getParam('username',"");
		$password   = App::get()->request->getParam('pswd',"");
	
		if(!$id){
			return false;
		}
	
		$request = array('id' => $id, 'username' => $username, 'pswd' => $password);
	
		return $request;
	}
}