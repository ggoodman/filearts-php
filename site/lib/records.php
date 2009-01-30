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
			'type' => 'varchar',
			'size' => 40,
		));
		$this->hasColumn('password', array(
			'type' => 'char',
			'size' => 32,
		));
		$this->hasColumn('name', array(
			'type' => 'varchar',
			'size' => 60,
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

class NewsRecord extends FARecord {

	public function setTableDefinition() {
	
		$this->setTableName('news');
		
		$this->hasColumn('id', array(
			'type' => 'int',
			'primary' => TRUE,
			'autoIncrement' => TRUE,
		));
		$this->hasColumn('user_id', array(
			'type' => 'int',
		));
		$this->hasColumn('published', array(
			'type' => 'date',
		));
		$this->hasColumn('title', array(
			'type' => 'varchar',
			'size' => 120,
		));
		$this->hasColumn('body', array(
			'type' => 'text',
		));
		
		$this->hasOne('user', array(
			'local' => 'id',
			'foreign' => 'user_id',
		));
	}
}

?>