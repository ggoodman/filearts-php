<?php

abstract class FATable {

	protected $table;
	
	protected $primaryKey = array();
	protected $columns = array();
	protected $aliases = array();
	
	protected $autoIncrement = '';
	
	protected $many = array();
	protected $one = array();
	
	protected $prefetch = array();

	abstract public function setTableDefinition();
	
	public function getTableName() {
	
		return $this->table;
	}

	public function setTableName($table) {
		
		$this->table = $table;
	}
	
	public function getAlias() {
	
		return substr(get_class($this), 0, -6);
		
		return $this->alias;
	}
	
	public function hasColumn($name, $options = array()) {
	
		$options = array_merge(array(
			'column' => $name,
			'primary' => FALSE,
			'type' => 'integer',
			'autoIncrement' => FALSE,
		), $options);
		
		if ($options['primary']) {
		
			$this->primaryKey[$name] = $options['column'];
		}
		
		if ($options['autoIncrement']) {
		
			$this->autoIncrement = $name;
		}
		
		$this->columns[$name] = $options;
		$this->aliases[$options['column']] = $name;
	}
	
	public function hasMany($name, $options) {
	
		$options = array_merge(array(
			'record' => $name,
			'local' => '',
			'foreign' => '',
		), $options);
	
		$this->many[$name] = $options;
	}
	
	public function hasOne($name, $options) {

		$options = array_merge(array(
			'record' => $name,
			'local' => '',
			'foreign' => '',
			'prefetch' => FALSE,
		), $options);
		
		if ($options['prefetch'])
			$this->prefetch[$name] = $options;
	
		$this->one[$name] = $options;
	}
	
	public function prepareSelect(FAQuery $query) {
	}
}

abstract class FARecord extends FATable {

	protected $dba;
	
	protected $query;
	
	protected $cache = array();
	protected $dirty = array();
	protected $values = array();
	
	protected $stored = FALSE;
	
	static protected $records = array();
	
	public function __construct($dba) {
	
		$this->dba = $dba;
	}
	
	public function __get($key) {
	
		if (isset($this->cache[$key])) return $this->cache[$key];
		if (isset($this->dirty[$key])) return $this->dirty[$key];
		if (isset($this->values[$key])) return $this->values[$key];
		
		if (isset($this->prefetch[$key])) {

			$table = $this->one[$key]['record'];
			$this->cache[$key] = $this->dba->$table;
			
			$values = array();
			
			foreach ($this->values as $alias => $value) {
			
				if (preg_match("/_{$table}_(?<key>.+)/", $alias, $matches))
					$values[$matches['key']] = $value;
			}
			
			$this->cache[$key]->populate($values);
			
			return $this->cache[$key];
		}
		
		if (isset($this->one[$key])) {

			$table = $this->one[$key]['record'];
			$this->cache[$key] = $this->dba->$table($this->__get($this->one[$key]['foreign']));
			
			return $this->cache[$key];
		}
		
		if (isset($this->many[$key])) {
		
			$table = $this->many[$key]['record'];
			$record = $this->dba->$table;
		
			$this->cache[$key] = $this->getJoinedRecords($record,
				$record->getSelectQuery()
					->where(
						$record->getAlias() . '.' . $this->many[$key]['foreign'] . '=?',
						$this->__get($this->many[$key]['local']))
			);
			
			return $this->cache[$key];
		}
		
		if (method_exists($this, "get$key")) {
			
			$this->cache[$key] = call_user_func(array(&$this, "get$key"), $key);
			return $this->cache[$key];
		}
	}
	
	public function __set($key, $value) {
	
		if (isset($this->columns[$key])) { return ($this->dirty[$key] = $value); }
		elseif (method_exists($this, "set$key")) {
			
			return (call_user_func(array(&$this, "set$key"), $value));
		} elseif (isset($this->data[$key])) {
			
			return ($this->data[$key] = $value);
		}
	}
	
	public function delete() {
	
		$query = $this->dba->delete($this->table);
	
		foreach ($this->primaryKey as $column) {
		
			$name = $this->aliases[$column];
			
			$query->where($column . '=?', $this->__get($name));
		}
		
		$query->execute();
	}
	
	public function getClass() {
	
		return substr(get_class($this), 0, -6);
	}
	
	protected function getJoinedRecords(FARecord $table, FAQuery $query) {
	
		return new FARecordSet($query, $table);
	}
	
	protected function addColumns(FAQuery $query, $prefix = '') {

		foreach ($this->columns as $name => $options) {
		
			$query->column($this->alias . '.' . $options['column'] . ' ' . $prefix . $options['column']);
		}			
	}
	
	public function getSelectQuery() {

		if ($this->query == NULL) {
		
			$this->query = $this->dba->select($this->table . ' ' . $this->alias);
			
			$this->addColumns($this->query);
			
			foreach ($this->prefetch as $name => $options) {
			
				$class = $options['record'];
				$record = $this->dba->$class;
							
				$this->query->join($record->getTableName() . " $class", $this->getAlias().'.'.$options['foreign']."=$name.".$options['local']);
				
				$record->addColumns($this->query, "_{$class}_");
				$record->prepareSelect($this->query);
			}
			
			$this->prepareSelect($this->query);
		}
		
		return clone $this->query;
	}

	public function find($key) {
	
		if (!$key || (is_array($key) && empty($key))) return NULL;
		if (!is_array($key)) $key = array($key);
		
		$query = $this->getSelectQuery();
		
		foreach ($this->primaryKey as $name => $column) {
			
			$query->where($this->alias . '.' . $column . '=?', array_shift($key));
		}
				
		$result = $query->execute();
		
		if ($result->valid()) {

			$record = $this->dba->__get($this->getClass());
			$record->populate($result->current());
			
			return $record;
		}
	}
	
	public function findAll() {
		
		return new FARecordSet($this->getSelectQuery(), clone $this);
	}
	
	public function findOrNew($key) {
	
		$record = $this->dba->__get($this->getClass());
		
		if (!$key || (is_array($key) && empty($key))) return $record;
		if (!is_array($key)) $key = array($key);
		
		$query = $this->getSelectQuery();
		$i = 0;
		
		foreach ($this->primaryKey as $name => $column) {
			
			$query->where($this->getAlias() . '.' . $column . '=?', $key[$i]);
			
			$record->$name = $key[$i++];
		}
		
		$result = $query->execute();

		if ($result->valid())
			$record->populate($result->current());
			
		return $record;
	}
	
	public function isDirty() {
	
		return !empty($this->dirty);
	}
	
	public function isStored() {
		
		return $this->stored;
	}
	
	public function populate($values = array()) {
	
		foreach ($values as $name => $value) {
		
			$this->values[$name] = $value;
			
			if (isset($this->dirty[$name])) unset($this->dirty[$name]);
		}
		
		$this->stored = TRUE;
	}
	
	public function save() {
	
		if (!$this->isDirty()) return $this;
		
		if ($this->isStored()) {
		
			$query = $this->dba->update($this->table)
				->setAll($this->dirty);
						
			foreach ($this->primaryKey as $alias => $column) {
				
				$query->where($column . '=?', $this->__get($alias));
			}
			
			$query->execute();
			
			$this->values = array_merge($this->values, $this->dirty);
			$this->dirty = array();
		} else {
		
			$query = $this->dba->insert($this->table);
			
			if (isset($this->dirty[$this->autoIncrement]))
				unset($this->dirty[$this->autoIncrement]);
				
			foreach ($this->dirty as $alias => $value) {
			
				$query->set($this->columns[$alias]['column'], $value);
			}
			
			try {
				$query->execute();
			} catch (FADatabaseException $e) {
			
				if ($e->getCode() == 1062) return FALSE;
				
				throw $e;
			}
			
			if ($this->autoIncrement)
				$this->dirty[$this->autoIncrement] = $this->dba->lastInsertId();
				
			$this->populate(array_merge(
				$this->values,
				$this->dirty
			));
		}
		
		return $this;
	}
	
	public function setArray($values) {
	
		foreach ($values as $key => $value)
			$this->__set($key, $value);
			
		return $this;
	}
	
	public function toArray() {
	
		return array_merge($this->values, $this->dirty);
	}
}

?>