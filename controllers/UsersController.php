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
		$rememberMe = App::get()->request->getParam('rememberme',false);
		$username   = App::get()->request->getParam('username',"");
		$password   = App::get()->request->getParam('pswd',"");
		$admin      = App::get()->request->getParam('adm',false);
		
		App::get()->user->setUserOption('isAdmin', $admin);
		App::get()->user->rememberMe = $rememberMe;
		
		if(App::get()->user->login($username,$password))
			App::get()->request->redirect('/users/');
		else
			echo 'failed to log in';
	}
	
	public function logoutAction(){
		App::get()->user->logout();
		
		App::get()->request->redirect('/users/');
	}
}