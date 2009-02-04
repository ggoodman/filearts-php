<?php

class Visitor {

	private $defaults = array(
		'username' => 'Guest',
	);

	private $user;
	
	public function __get($var) {
	
		if ($this->isMember()) return $this->user->$var;
		elseif (isset($this->defaults[$var])) return $this->defaults[$var];
	}
	
	public function isMember() {
	
		return isset($this->user);
	}
	
	public function setUser(FAEntity $user) {
	
		$this->user = $user;
	}
}

?>