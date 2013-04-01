<?php
class NewController extends Controller
{
	protected $action = 'book'; // default action for this controller

	public function bookAction()
	{
		$parameters = $this->parseRequestBook();
		
		if(!$parameters){
			$this->sendError('Wrong parameters', $parameters['type']);
			App::get()->endApp();
		}
		
		$author = new AuthorModel();
		$author->id = $parameters['author'];
		$author = $author->findOne();
		
		if(!isset($author)){
			$this->sendError('Author was not found', $parameters['type']);
			App::get()->endApp();
		}
		
		$book = new BookModel();
		$book->author = $author;
		$book->title  = $parameters['title'];
		$book->isbn   = $parameters['isbn'];
		
		$thisBook = $book->find();
		if(!empty($thisBook)){
			$this->sendError('This ' . $this->action . ' already exists', $parameters['type']);
			App::get()->endApp();
		}
		
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
			$this->sendError('Wrong parameters', $parameters['type']);
			App::get()->endApp();
		}
		
		$author = new AuthorModel();
		$author->name = $parameters['name'];
		$thisAuthor = $author->find();
		
		if(!empty($thisAuthor)){
			$this->sendError('This ' . $this->action . ' already exists', $parameters['type']);
			App::get()->endApp();
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
	
	private function parseRequestBook(){
		$type   = App::get()->request->getParam('type','json');
		$isbn   = App::get()->request->getParam('isbn', null);
		$author = App::get()->request->getParam('author_id', null);
		$title  = App::get()->request->getParam('title', null);
	
		if($type != 'json' && $type != 'xml'){
			return false;
		}
	
		if(!$author || !$isbn || !$title){
			return false;
		}
	
		$request = array('type' => $type, 'isbn' => $isbn, 'author' => $author, 'title' => $title);
	
		return $request;
	}
	
	private function parseRequestAuthor(){
		$type   = App::get()->request->getParam('type','json');
		$name  = App::get()->request->getParam('name');

		if($type != 'json' && $type != 'xml'){
			return false;
		}

		if(!$name){
			return false;	
		}
		
		$request = array('type' => $type, 'name' => $name);

		return $request;
	}
}