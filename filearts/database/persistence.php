<?php

require_once 'entity.php';
require_once 'entityset.php';
require_once 'table.php';

class FAPersistence {

	private static $instance;

	public static function createEntity($table) {
	
		if ($table instanceof FATable) {
		
			$table = $table->getEntityClass();
		}
	
		if (!class_exists($table, FALSE)) throw new FAException("No such table: $table");
		
		$entity = new $table;
		
		if (!$entity instanceof FAEntity) throw new FAException("Class $table must extend FAEntity");
		
		return $entity;
	}
	
	public static function getDatabase($table) {
		
		return self::getInstance()->_getDatabase($table);
	}
	
	private function _getDatabase($table) {
	
		if ($table instanceof FAEntity) {
		
			$table = get_class($table);
		}
		
		if (isset($this->datasource[$table]))
			return $this->database[$this->datasource[$table]];
		
		return $this->defaultDatabase;
	}

	public static function getTable($table) {
		
		return self::getInstance()->_getTable($table);
	}
	
	private function _getTable($table) {
	
		if ($table instanceof FAEntity) {
		
			$table = get_class($table);
		}
		
		if (!isset($this->tables[$table])) {
		
			$this->tables[$table] = new FATable($table);
			
			call_user_func(array($table, 'setTableDefinition'), $this->tables[$table]);
			call_user_func(array($table, 'prepareSelect'), $this->tables[$table]->buildSelectQuery());
		}
		
		return $this->tables[$table];
	}
	
	static public function getInstance() {
	
		if (self::$instance == NULL)
			throw new Exception("You must initialize FAPersistence");
		
		return self::$instance;
	}
	
	static public function connect($dba) {
	
		self::$instance = new FAPersistence($dba);
		
		return self::getInstance();
	}
	
	static public function init($url) {
	
		return self::connect(database($url));
	}
	
	private $defaultDatabase;
	private $datasource = array();
	private $database = array();
	
	private $tables = array();
	
	public function __construct($dba) {
	
		$this->defaultDatabase = $dba;
	}

	public function __call($table, $key) {
	
		if (!$key) throw new FANotFoundException("Empty key");
		if (!is_array($key)) $key = array($key);
	
		$table = self::getTable($table);
		$entity = self::createEntity($table);
		
		foreach ($table->getPrimaryKey() as $name => $column) {
		
			if (empty($key)) throw new Exception("Missing primary key fields");
		
			$entity->$name = array_shift($key);
		}
		
		return $entity->find();
	}
	
	public function __get($table) {
	
		return self::createEntity($table);
	}
	
	public function findAll($table) {
	
		return self::getTable($table)->getSelectQuery()->execute();
	}
	
	public function findOrNew($table, $key) {
	
		try {
			return $this->__call($table, $key);
		
		} catch (FANotFoundException $e) {
		
			return $this->__get($table);
		}
	}
}

?>