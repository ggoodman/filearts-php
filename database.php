<?php

require_once 'database/query.php';
require_once 'database/resultset.php';
require_once 'database/record.php';
require_once 'database/recordset.php';

abstract class FADatabaseConnection {

	protected $tables = array();

	abstract public function quote($str);
	abstract public function query($sql, $args = array());
	abstract public function lastInsertId();
	
	public function mergeArguments($sql, $args = NULL) {
	
		if ($args == NULL) return $sql;
		elseif (!is_array($args)) $args = array($args);
		
		$parts = preg_split('~\?~', $sql);
		$sql = array_shift($parts);
		
		while (!empty($args) && !empty($parts)) {
			
			$sql .= $this->quoteVal(array_shift($args)) . array_shift($parts);
		}
		
		return $sql;
	}
	
	protected function quoteVal($value) {
		
		while (true) {
			switch (gettype($value)) {
				case 'boolean': return ($value) ? 'TRUE' : 'FALSE';
				case 'integer':
				case 'double': return $value;
				case 'null': return 'NULL';
				case 'array':
					return '(' . implode(',', array_map(array($this, 'quoteVal'), $value)) . ')';
				default:
					if (ctype_digit($value)) {
						$value = (int)$value;
						continue;
					} elseif (is_numeric($value)) {
						$value = (double)$value;
						continue;
					}
					return "'" . $this->quote($value) . "'";
			}
		}
	}
	
	public function deleteFrom($table) {
	
		$query = new FADeleteQuery($this);
		
		return $query->from($table);
	}
	
	public function insertInto($table) {
		
		$query = new FAInsertQuery($this);
		
		return $query->from($table);
	}
	
	public function select($table) {
	
		$query = new FASelectQuery($this);
		
		$query->table($table);
		
		return $query;
	}
	
	public function update($table) {
	
		$query = new FAUpdateQuery($this);
		
		return $query->from($table);
	}
	
	public function __call($table, $key) {
	
		return $this->createRecord($table)->find($key);
	}
	
	public function __get($table) {
	
		return $this->createRecord($table);
	}

	protected function createRecord($table) {
	
		if (!isset($this->tables[$table])) {
	
			$class = $table . 'Record';
			
			if ($class == 'executeRecord') throw new Exception();
			
			$this->tables[$table] = new $class($this);
			$this->tables[$table]->setTableDefinition();
		}
		
		return clone $this->tables[$table];
	}
}

?>