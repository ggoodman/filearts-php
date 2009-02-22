<?php

class FANotFoundException extends Exception {}


/**
 * Class representing a row in the database
 */
abstract class FAEntity {

	protected $dirty = array();
	protected $saved = array();

	public function __construct($initial = array()) {
	
		$this->__set_state($initial);
	}
	
	public function __get_state() {
		
		return array_merge(get_class_vars($this), $this->saved, $this->dirty);
	}
	
	public function __set_state($values) {
	
		$this->saved = $this->dirty = array();
		
		$prefetch = array();
		$table = FAPersistence::getTable($this);
	
		foreach ($values as $key => $value) {
		
			if ($table->isColumn($key)) $this->saved[$key] = $value;
			elseif (preg_match('~^__(.+)__(.+)$~', $key, $matches)) $prefetch[$matches[1]][$matches[2]] = $value;
			else $this->$key = $value;
		}
		
		foreach ($prefetch as $name => $state) {
		
			$options = $table->getRelation($name);
			$class = $options['class'];
			
			$this->$name = new $class($state);
		}
	}

	public function __get($key) {
	
		if (isset($this->dirty[$key])) return $this->dirty[$key];
		if (isset($this->saved[$key])) return $this->saved[$key];
		
		$table = FAPersistence::getTable($this);
		
		if ($table->isManyRelation($key)) {
		
			$options = $table->getRelation($key);
			$joinTable = FAPersistence::getTable($options['class']);
			
			$this->$key = $joinTable->getSelectQuery()
				->where($joinTable->getEntityClass() . '.' . $options['foreign'] . '=?',
					$this->__get($options['local']))
				->execute();
				
			return $this->$key;
		}
		
		if ($table->isOneRelation($key)) {
		
			$options = $this->table->getRelation($key);
			$joinTable = FAPersistence::getTable($options['class']);
			
			$this->$key = $joinTable->getSelectQuery()
				->where($joinTable->getEntityClass() . '.' . $options['local'] . '=?',
					$this->__get($options['foreign']))
				->fetchOne();
				
			if (!$this->$key) throw new Exception("Missing related class: $key");
				
			return $this->$key;
		}
		
		if (method_exists($this, 'get' . $key)) {
		
			$this->$key = call_user_func(array($this, 'get' . $key));
			
			return $this->$key;
		}
	}
	
	public function __set($name, $value) {
	
		if (FAPersistence::getTable($this)->isColumn($name)) return $this->dirty[$name] = $value;
		
		return $this->$name = $value;
	}
	
	public function getIdentity() {
	
		$id = array();
	
		foreach (FAPersistence::getTable($this)->getPrimaryKey() as $key => $column) {
		
			$id[$key] = $this->__get($key);
		}
		
		return $id;
	}
	
	public function getValues($saved = FALSE) {
	
		if ($saved) return $this->saved;
		
		return array_merge($this->saved, $this->dirty);
	}
	
	public function delete() {
	
		FAPersistence::getDatabase($this)
			->delete(FAPersistence::getTable($this)->getTableName())
			->whereArray($this->getIdentity())
			->execute();
	}

	public function find() {
	
		$results = FAPersistence::getTable($this)->getSelectQuery()
			->whereArray($this->getValues(), get_class($this))
			->setClass()
			->execute();
			
		if (!$results->valid()) throw new FANotFoundException("Entity not found");
		
		$this->__set_state($results->current());
		
		return $this;
	}
	
	public function save() {
	
		$this->preSave();
		
		$table = FAPersistence::getTable($this);
	
		// This is an insert
		if (empty($this->saved)) {
		
			FAPersistence::getDatabase($this)
				->insert($table->getTableName())
				->setAll(array_filter($this->getValues()))
				->execute();
			
			// Figure out what the new id of this entity is
			if ($table->getAutoIncrement())
				$this->dirty[$table->getAutoIncrement()] 
					= FAPersistence::getDatabase($this)->lastInsertId();
				
		// This is an update
		} else {
			
			FAPersistence::getDatabase($this)
				->update($table->getTableName())
				->setAll($this->getValues())
				->whereArray($this->getIdentity())
				->execute();
		}
		
		$this->saved = array_merge($this->saved, $this->dirty);
		$this->dirty = array();
		
		$this->postSave();
		
		return $this;
	}
	
	public function set($key, $value) {
		
		$this->__set($key, $value);
		
		return $this;
	}
	
	public function setArray($array) {
	
		foreach ($array as $key => $value) $this->__set($key, $value);
		
		return $this;
	}
	
	public function toArray() {
	
		return array_merge(get_object_vars($this), $this->getValues());
	}
	
	public function preSave(){}
	public function postSave(){}
	
	public static function prepareSelect(FAQuery $query) {}
	abstract public static function setTableDefinition(FATable $table);
}

?>