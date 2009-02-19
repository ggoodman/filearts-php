<?php

class FATable {

	protected $entityClass;
	protected $tableName;
	
	protected $selectQuery;
	
	protected $autoIncrement;
	protected $primaryKey = array();
	
	protected $columns = array();
	protected $hasMany = array();
	protected $hasOne = array();
	protected $prefetch = array();
	
	public function __construct($entityClass) {
	
		$this->entityClass = $entityClass;
	}
	
	public function addSelectColumns(FAQuery $query, $prefix = '') {
		
		foreach ($this->columns as $name => $column) {
		
			$query->column($this->entityClass . '.' . $column['column'] . ' AS ' . $prefix . $name);
		}
	}
	
	public function buildSelectQuery() {
	
		// Create the select query
		$this->selectQuery = FAPersistence::getDatabase($this->entityClass)
			->select($this->tableName) // Create a select query for this table
			->setClass($this->entityClass); // Set the Entity class of the query builder
		
		// Add this table's columns to the query
		$this->addSelectColumns($this->selectQuery);
		
		// Iterate through prefetched entities and build the query
		foreach ($this->prefetch as $name => $options) {
		
			// Get the joined entity's table
			$table = FAPersistence::getTable($options['class']);
			
			// Add the joined entity's basic columns
			$table
				->addSelectColumns($this->selectQuery, '__' . $name . '__');
			
			// Create the join that will link the prefetched entity to the base entity
			$this->selectQuery->join($table->getTableName() . ' ' . $table->getEntityClass(),
				$table->getEntityClass() . '.' . $options['local'] . '=' . $this->getEntityClass() . '.' . $options['foreign']);
			
			// Call the prefetched entity's prepareSelect method on the query
			call_user_func(array($options['class'], 'prepareSelect'), $this->selectQuery);
		}
		
		return $this->selectQuery;
	}
	
	public function getAutoIncrement() {
		
		return $this->autoIncrement;
	}

	public function getColumns() {
	
		return $this->columns;
	}
	
	public function getEntityClass() {
	
		return $this->entityClass;
	}

	public function getPrimaryKey() {
	
		return $this->primaryKey;
	}
	
	public function getRelation($name) {
	
		if (isset($this->hasMany[$name])) return $this->hasMany[$name];
		if (isset($this->hasOne[$name])) return $this->hasOne[$name];
		if (isseT($this->prefetch[$name])) return $this->prefetch[$name];
	}
	
	public function getTableName() {
	
		return $this->tableName;
	}
	
	public function getSelectQuery() {
	
		return clone $this->selectQuery;
	}
	
	public function hasColumn($name, $options) {
	
		$options = array_merge(array(
			'column' => $name,
			'type' => '',
			'size' => '',
			'primary' => FALSE,
			'autoIncrement' => FALSE,
			'null' => FALSE,
		), $options);
		
		$this->columns[$name] = $options;
		
		if ($options['primary']) $this->primaryKey[$name] = $options['column'];
		if ($options['autoIncrement']) $this->autoIncrement = $name;
	}
	
	public function hasMany($name, $options) {
	
		$options = array_merge(array(
			'class' => ucfirst($name),
			'local' => 'id',
			'foreign' => $this->tableName . '_id',
			'through' => '',
		), $options);
		
		$this->hasMany[$name] = $options;
	}
	
	public function hasOne($name, $options) {
	
		$options = array_merge(array(
			'class' => ucfirst($name),
			'local' => 'id',
			'foreign' => $name . '_id',
			'prefetch' => FALSE,
		), $options);
		
		if ($options['prefetch']) $this->prefetch[$name] = $options;
		else $this->hasOne[$name] = $options;
	}
	
	public function isColumn($name) {
	
		return isset($this->columns[$name]);
	}
	
	public function isManyRelation($name) {
	
		return isset($this->hasMany[$name]);
	}
	
	public function isOneRelation($name) {
	
		return isset($this->hasOne[$name]);
	}
	
	public function setTableName($tableName) {
	
		$this->tableName = $tableName;
	}
}

?>