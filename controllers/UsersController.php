<?php
class UsersController extends Controller
{
	public function indexAction(){
		if(App::get()->user->loggedIn){					
			echo 'Hello, ' . App::get()->user->username;
			
			 if(App::get()->user->isAdmin)
				echo '<br>WHOA! You\'r ADMIN!';
			else 
				echo '<br>And you\'re just a lowly douche'; 
			
		} else {
			echo 'No user logged in';
		}
	}
	
	public function loginAction(){		
		$parameters = $this->parseRequestUser();
		
		if(!$parameters){
			$this->sendError('Username or password is missing');
			App::get()->endApp();
		}					
		
		$admin      = App::get()->request->getParam('adm',false);
		App::get()->user->setUserOption('isAdmin', $admin);
		
		$rememberMe = App::get()->request->getParam('rememberme',false);
		App::get()->user->rememberMe = $rememberMe;
		
		if(App::get()->user->login($parameters['username'],$parameters['pswd']))
			App::get()->request->redirect('/users/');
		else
			echo 'failed to log in';
	}
	
	public function logoutAction(){
		App::get()->user->logout();
		
		App::get()->request->redirect('/users/');
	}


	public function GetUserAction() {
		$user = new UserModel();
		$parameters = $this->parseRequestBook();

	}
	public function NewUserAction() {
		$user = new UserModel();
		$parameters = $this->parseRequestBook();

	}
	public function UpdateUserAction() {
		$user = new UserModel();
		$parameters = $this->parseRequestBook();

	}
	public function RemoveUserAction() {
		$user = new UserModel();
		$parameters = $this->parseRequestBook();

	}
	
	public function parseRequestUser() {		
		$username   = App::get()->request->getParam('username',"");
		$password   = App::get()->request->getParam('pswd',"");		
		
		if(!$username || !$password){
			return false;
		}
		
		$request = array('username' => $username, 'pswd' => $password);
		
		return $request;
	}
}
