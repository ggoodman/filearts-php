<?php

class UserRecord extends FARecord {

	public function setTableDefinition() {
	
		$this->setTableName('user');
		
		$this->hasColumn('id', array(
			'type' => 'int',
			'primary' => TRUE,
			'autoIncrement' => TRUE,
		));
		$this->hasColumn('username', array(
			'type' => 'string',
			'size' => 40,
		));
		$this->hasColumn('password', array(
			'type' => 'string',
			'size' => 32,
		));
	}
	
	public function verify($credentials = array()) {

		$credentials = array_merge(array(
			'user' => NULL,
			'pass' => NULL,
		), $credentials);
		
		$matched = $this->getJoinedRecords($this,
			$this->getSelectQuery()
				->where('User.username=? AND User.password=?', array(
					$credentials['username'],
					md5(SALT . $credentials['password']),
				))
		);
		
		if ($matched->valid()) return $matched->current();
	}
}

?>