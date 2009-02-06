<?php

class FAPersistence {

	static private $instance;
	
	static public function getInstance() {
	
		if (self::$instance == NULL)
			throw new Exception("You must initialize FAPersistence");
		
		return self::$instance;
	}
	
	static public function getDatabase() {
		
		return self::getInstance()->dba;
	}
	
	static public function connect($dba) {
	
		return (self::$instance = new FAPersistence($dba));
	}
	
	static public function init($url) {
	
		return self::connect(database($url));
	}
	
	protected $dba;
	
	protected $records = array();
	protected $tables = array();
	
	protected function __construct($dba) {
	
		$this->dba = $dba;
	}

	public function __call($alias, $key) {
	
		$entity = $this->__get($alias);
		$table = $entity->getTable();
		
		foreach ($table->getPrimaryKey() as $name => $column) {
			
			$entity->$name = array_shift($key);
		}
		
		return $entity->find();
	}
	
	public function __get($class) {
	
		return new $class;
	}
	
	public function findAll($alias) {
	
		$table = $this->__get($alias)->getTable();
		$query = $table->getSelectQuery();
		
		return new FARecordSet($table, $query);
	}
	
	public function findOrNew($alias, $key) {
	
		if (!is_array($key)) $key = array($key);
	
		try {
			return $this->__call($alias, $key);
			
		} catch (Exception $e) {
		
			return $this->__get($alias);
		}
	}
}

class FAEntity {

	protected $record;
	protected $table;
	
	protected $stored = FALSE;
	
	protected $cache = array();
	
	protected $tableDef = array();
	protected static $tableDefs = array();

	public function __construct($values = array()) {
	
		$class = get_class($this);
	
		if (!isset(self::$tableDefs[$class])) {
		
			self::$tableDefs[$class] = new FATable($this, $this->tableDef);
		}
		
		$this->table = self::$tableDefs[$class];
		$this->record = new FARecord($values);
	}

	public function __get($key) {
	
		if (isset($this->record[$key])) return $this->record[$key];
		
		if ($ret = $this->table->getJoin($key, $this->record)) {
			
			$this->$key = $ret;
			
			return $this->$key;
		}
	}
	
	public function __set($key, $value) {
	
		$columns = $this->table->getColumns();
		$primary = $this->table->getPrimaryKey();
		
		if (isset($primary[$key]) && !$value) {
			
			return;
		} else if (isset($columns[$key])) {

			$this->record[$key] = $value;
		} else {
			
			$this->$key = $value;
		}
	}
	
	public function prepareSelect(FAQuery $query) { return $query; }
	public function preSave() {}
	public function postSave() {}
	
	public function set($key, $value) {
	
		$this->__set($key, $value);
		
		return $this;
	}
	
	public function getRecord() {
		
		return $this->record;
	}
	
	public function getTable() {
	
		return $this->table;
	}
	
	public function find() {
	
		$query = $this->table->getSelectQuery();
		$id = $this->table->getTableAlias();
		
		foreach ($this->table->getPrimaryKey() as $name => $column) {
		
			if (!$this->record[$name]) throw new Exception("Record not found");
		
			$query->where($this->table->getTableAlias() . '.' . $column . '=?', $this->__get($name));
			$id .= '.' . $this->__get($name);
		}
		
		if ($record = &FARecord::getRecord($id)) {
		
			$this->setRecord($record);
			
			$this->stored = TRUE;
		} else {
		
			$result = $query->execute();
			
			if (!$result->valid()) throw new Exception("Record not found");
		
			$this->populate($result->current());
		}
		
		return $this;
	}
	
	public function isStored() {
		
		return $this->stored;
	}
	
	public function populate($rowData) {
	
		$columns = $this->table->getColumns();
		
		$values = array();
		$prefetch = array();
		$meta = array();
	
		foreach ($rowData as $key => $value) {
		
			if (isset($columns[$key])) {
				
				$values[$key] = $value;
			} else if (preg_match('/^_(?<join>[^_]+)_(?<name>.+)$/', $key, $matches)) {
			
				$prefetch[$matches['join']][$matches['name']] = $value;
			} else {
			
				$meta[$key] = $value;
			}
		}
		
		$id = array($this->table->getTableAlias()) + array_intersect_key($values, $this->table->getPrimaryKey());
		$id = implode('.', $id);
		
		$record = &FARecord::createRecord($id, $values);
		
		$record->setMeta($meta);
		$this->setRecord($record);
		
		foreach ($this->table->getPrefetch() as $name => $options) {
		
			$class = $options['record'];
			
			$this->$name = new $class;		
			$this->$name->populate($prefetch[$name]);
		}
		
		$this->stored = TRUE;
		
		return $this;
	}
	
	public function delete() {
	
		if ($this->isStored()) {
		
			$query = $this->table->getDeleteQuery();
			
			foreach ($this->table->getPrimaryKey() as $name => $column) {
			
				$query->where("$name=?", $this->record[$name]);
			}
			
			$query->execute();
			
			// Flush the dirty cache to clean
			$this->record = new FARecord;
			
		} else {
		
			$this->record = new FARecord;
		}
	}
	
	public function save() {
	
		$this->preSave();
		
		if ($this->isStored()) {
		
			$query = $this->table->getUpdateQuery()
				->setAll($this->record->getDirty());
			
			foreach ($this->table->getPrimaryKey() as $name => $column) {
			
				$query->where("$name=?", $this->record[$name]);
			}
			
			$query->execute();
			
			// Flush the dirty cache to clean
			$this->record->flush();
			
		} else {
		
			$this->table->getInsertQuery()
				->setAll($this->record->getDirty())
				->execute();
			
			// If the table has an auto-increment column, update the record to account for it
			// before fetching the tracked record
			if ($this->table->getAutoIncrement()) {
				
				$name = $this->table->getAutoIncrement();
				$this->__set($name, FAPersistence::getDatabase()->lastInsertId());
			}
			
			// Replace the current un-tracked record with one pulled from the database
			$this->find();
		}
		
		$this->postSave();
		
		return $this;
	}
	
	public function setArray($rowData) {
	
		foreach ($rowData as $key => $value) {
			
			$this->__set($key, $value);
		}
		
		return $this;
	}
	
	public function setRecord(&$record) {
	
		$this->record = &$record;
		
		return $this;
	}
	
	public function toArray() {
	
		return $this->record;
	}
}

/**
 * Class representing a database row data
 */
class FARecord implements ArrayAccess, IteratorAggregate {
	
	static private $records = array();

	static public function &createRecord($id, $rowData) {
	
		self::$records[$id] = new FARecord($rowData);
		
		return self::$records[$id];
	}

	static public function &getRecord($id) {
	
		if (isset(self::$records[$id])) return self::$records[$id];
		
		$ret = NULL;	
		return $ret;
	}

	protected $dirty = array();
	protected $values = array();
	protected $meta = array();

	public function __construct($values = array()) {
	
		$this->values = $values;
	}
	
	public function flush() {
	
		$this->values = array_merge($this->values, $this->dirty);
		$this->dirty = array();
	}
	
	public function getDirty() {
	
		return $this->dirty;
	}
	
	public function getIterator() {
	
		return new ArrayIterator(array_merge($this->values, $this->dirty));
	}
	
	public function offsetGet($key) {
	
		if (isset($this->dirty[$key])) return $this->dirty[$key];
		if (isset($this->values[$key])) return $this->values[$key];
		if (isset($this->meta[$key])) return $this->meta[$key];
	}
	
	public function offsetExists($key) {
	
		return (isset($this->dirty[$key])
			|| isset($this->values[$key])
			|| isset($this->meta[$key])
		);
	}
	
	public function offsetSet($key, $value) {
	
		return $this->dirty[$key] = $value;
	}
	
	public function offsetUnset($key) {
	
		if (isset($this->dirty[$key])) unset($this->dirty[$key]);
	}
	
	public function setMeta($key, $value = NULL) {
	
		if (NULL === $value && is_array($key)) $this->meta += $key;
		else $this->meta[$key] = $value;
	}
}

class FATable {

	protected $alias;
	protected $table;
	
	protected $primaryKey = array();
	protected $columns = array();
	protected $aliases = array();
	
	protected $autoIncrement = '';
	
	protected $many = array();
	protected $one = array();
	
	protected $prefetch = array();
	
	protected $query;
	
	public function __construct(FAEntity $entity, $tableDef = array()) {
	
		$this->alias = get_class($entity);
	
		$tableDef = array_merge(array(
			'table' => '',
			'columns' => array(),
			'hasOne' => array(),
			'hasMany' => array(),
		), $tableDef);
		
		$this->setTableName($tableDef['table']);
		
		foreach ($tableDef['columns'] as $name => $options) $this->hasColumn($name, $options);
		foreach ($tableDef['hasOne'] as $name => $options) $this->hasOne($name, $options);
		foreach ($tableDef['hasMany'] as $name => $options) $this->hasMany($name, $options);

		$this->query = FAPersistence::getDatabase()->select($this->table . ' ' . $this->getTableAlias());
		
		$this->addSelectColumns($this->query);
		$this->addPrefetchRecords($this->query);
		
		$this->query = $entity->prepareSelect($this->query);
	}
		
	public function getColumns() {
	
		return $this->columns;
	}
	
	public function getPrefetch() {
		
		return $this->prefetch;
	}
	
	public function getPrimaryKey() {
		
		return $this->primaryKey;
	}
	
	public function getTableAlias() {
	
		return $this->alias;
	}
	
	public function getTableName() {
	
		return $this->table;
	}

	public function setTableName($table) {
		
		$this->table = $table;
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
	
	public function getAutoIncrement() {
		
		return $this->autoIncrement;
	}
	
	protected function addPrefetchRecords(FAQuery $query) {
	
		foreach ($this->prefetch as $name => $options) {
		
			$class = $options['record'];
			$entity = new $class;
			$table = $entity->getTable();
			
			$this->query->join($table->getTableName() . " $class", $this->getTableAlias().'.'.$options['foreign']."=$class.".$options['local']);
			
			$table->addSelectColumns($this->query, "_{$name}_");
			$entity->prepareSelect($this->query);
		}
	}
	
	protected function addSelectColumns(FAQuery $query, $prefix = '') {

		foreach ($this->columns as $name => $options) {
		
			$query->column($this->getTableAlias() . '.' . $options['column'] . 
				(($prefix) ? ' ' . $prefix . $options['column'] : ''));
		}			
	}
	
	public function getJoin($name, FARecord $record) {
	
		if (isset($this->many[$name])) {
			
			$class = $this->many[$name]['record'];
			$entity = new $class;
			$table = $entity->getTable();
		
			$query = $table->getSelectQuery()
				->where(
					$table->getTableAlias() . '.' . $this->many[$name]['foreign'] . '=?',
					$record[$this->many[$name]['local']]);
					
			return new FARecordSet($table, $query);
			
		} else if (isset($this->one[$name])) {
		
			$key = $record[$this->one[$name]['foreign']];
			$alias = $this->one[$name]['record'];
			
			return FAPersistence::getInstance()->$alias($key);
		}
	}
	
	public function getSelectQuery() {

		if ($this->query == NULL) {
		
			$this->query = FAPersistence::getDatabase()->select($this->table . ' ' . $this->getTableAlias());
			
			$this->addSelectColumns($this->query);
			$this->addPrefetchRecords($this->query);
		}
		
		return clone $this->query;
	}
	
	public function getDeleteQuery() {
	
		return FAPersistence::getDatabase()->delete($this->table);
	}
	
	public function getUpdateQuery() {
	
		return FAPersistence::getDatabase()->update($this->table);
	}
	
	public function getInsertQuery() {
		return FAPersistence::getDatabase()->insert($this->table);
	}
}

?>