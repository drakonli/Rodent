<?php
class User extends BaseUser
{
	protected function authenticate(){
		$users = array("drakonli" => "123", "misha" => "soochka");
		
		if(isset($users[$this->name]) && $users[$this->name] == $this->password){
			return true;
		}
		
		return false;
	}
}