<?php
abstract class BaseUser
{
	public $rememberMe = false;
	public $loggedIn   = false;
	protected $hashOptions = array();
	protected $name;
	protected $password;
	protected $delimiter = "&";
	
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
		$this->hashOptions[$optionName] = "$optionValue";
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
		/* --session */
		
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
			$parsedCookie = $this->parseUserCookie($userCookie);
			if(isset($parsedCookie['rodusrname']) &&
					$userCookie['name'] == App::get()->generateHash($parsedCookie['rodusrname'])){
				$this->name = $parsedCookie['rodusrname'];
				$this->username = $parsedCookie['rodusrname'];
				$hash = $parsedCookie['hash'];
				
				unset($parsedCookie['hash']);
				unset($parsedCookie['rodusrname']);
				
				foreach($parsedCookie as $key => $value)
						$this->hashOptions[$key] = $value;
				
				if($this->generateUserHash() == $hash){
					foreach($this->hashOptions as $key => $value)
						$this->$key = $value;
					
					$this->loggedIn = true;
					$this->rememberMe = true;
				}
			}
		}
		/* --cookie */
		
		return $this->loggedIn;
	}
	
	private function setStatus(){
		if($this->rememberMe)
			$this->loggedIn = $this->generateUserCookie();
		else 
			$this->loggedIn = $this->generateUserSession();
	}
	
	private function generateUserSession(){
		$this->hashOptions['username'] = $this->name;
		App::get()->request->removeCookie(App::get()->generateHash($this->name));
		App::get()->request->setSession('user', $this->hashOptions);
	
		return true;
	}
	
	private function generateUserCookie(){
		$delimiter = $this->delimiter;
		$params = "rodusrname=$this->name" . $delimiter;
		
		foreach($this->hashOptions as $optionName => $optionValue)
			$params .= $optionName . "=" . $optionValue . $delimiter;
		
		$cookieValue = $this->generateUserHash() . urlencode($delimiter . $params);
		$cookieName  = App::get()->generateHash($this->name);

		App::get()->request->removeSession('user');
		App::get()->request->setCookie($cookieName, $cookieValue, 30);

		return true;
	}
	
	private function generateUserHash(){
		$delimiter = $this->delimiter;
		$hashString = "username=" . $this->name . $delimiter;
		
		foreach($this->hashOptions as $optionName => $optionValue){
			$hashString .= $optionName . "=" . $optionValue . $delimiter;
		}

		return App::get()->generateHash($hashString);
	}
	
	private function parseUserCookie($userCookie){
		$cookieArray = array();
		$cookie = explode($this->delimiter,$userCookie['value']);
		$cookieArray['hash'] = $cookie[0];
		unset($cookie[0]);
		foreach($cookie as $key => $value){
			if(!empty($value)){
				list ($cKey, $cValue) = explode('=', $value, 2);
				$cookieArray[$cKey] = $cValue;
			}
		}
		
		return $cookieArray;
	}
	
	abstract protected function authenticate();
}