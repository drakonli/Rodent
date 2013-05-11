<?php
class BaseUser 
{
	public $rememberMe = false;
	public $loggedIn   = false;
	protected $options = array();
	protected $name;
	protected $password;
	
	public function __construct(){
		$this->getStatus();
	}
	
	public function login($name, $password){
		$this->name     = $name;
		$this->password = $password;
		
		if($this->authenticate()){
			$this->setStatus();
		} else {
			$this->name     = null;
			$this->password = null;
		}
		
		return $this->loggedIn;
	}

	public function logout(){
		App::get()->request->removeCookie(App::get()->generateHash($this->name));
		App::get()->request->removeSession('user');
	}
	
	public function setUserOption($optionName,$optionValue){
		$this->options[$optionName] = $optionValue;
	}
	
	public function getStatus(){
		/* session */
		
		$userSession = App::get()->request->getSession('user', false);
		if($userSession){
			foreach($userSession as $key => $value){
				$this->$key = $value;
			}
			$this->loggedIn = true;
			return $this->loggedIn;
		}
		/* cookie */
		$cookies = App::get()->request->getCookies();
		$userCookie = array();	
		foreach($cookies as $key => $value){
			if(strpos($value, 'rodusrname')){
				$userCookie['name'] = $key;
				$userCookie['value'] = urldecode($value);
				break;
			}
		}
	
		if(!empty($userCookie)){
			$cookieArray = explode('|',$userCookie['value']);
			var_dump($cookieArray);
		}
		
		return $this->loggedIn;
	}
	
	private function setStatus(){
		if($this->rememberMe)
			$this->loggedIn = $this->generateUserCookie();
		else 
			$this->loggedIn = $this->generateUserSession();
	}
	
	private function generateUserCookie(){
		$delimiter = "&";
		$params = "rodusrname=$this->name" . $delimiter;
		$hashString = $this->name . sha1($this->password);
		
		foreach($this->options as $optionName => $optionValue){
			$params .= $optionName . "=" . $optionValue . $delimiter;
			$hashString .= $optionName . "=" . $optionValue . $delimiter;
		}
		
		$cookieValue = App::get()->generateHash($hashString) . urlencode($delimiter . $params);
		$cookieName  = App::get()->generateHash($this->name);

		App::get()->request->removeSession('user');
		App::get()->request->setCookie($cookieName, $cookieValue, 30);

		return true;
	}
	
	private function generateUserSession(){
		$this->options['username'] = $this->name;
		App::get()->request->removeCookie(App::get()->generateHash($this->name));
		App::get()->request->setSession('user', $this->options);
		
		return true;
	}
	
	protected function authenticate(){
		return true;
	}
}