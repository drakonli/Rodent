<?php
class User extends BaseUser
{	
	
	protected function authenticate(){		
		
		$user = new UserModel();	
		$user->username = $this->name;
		$user->password = App::get()->generateHash($this->password);
		
		$thisUser = $user->findOne();	
		
		
		if(empty($thisUser)){
			echo 'Username or password is invalid';
			App::get()->endApp();
		}		
		
		return true;
	}
	
}