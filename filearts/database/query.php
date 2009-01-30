<?php

abstract class FAQuery {

	protected $dba;
	protected $table;
	
	public function __construct($dba) {
		
		$this->dba = $dba;
	}
	
	abstract public function getSql();
	
	public function execute() {
	
		return $this->dba->query($this->getSql());
	}
	
	public function __toString() {
	
		return $this->getSql();
	}
	
	public function table($table) {
	
		$this->table = $table;
		
		return $this;
	}
}

class FASelectQuery extends FAQuery implements IteratorAggregate {

	private $columns = array();
	private $joins = array();
	private $where = array();
	private $group = array();
	private $having = array();
	private $order = array();
	
	private $limit;
	private $offset;
	
	public function getIterator() {
	
		return $this->execute();
	}
	
	public function clearJoins($type = '') {
	
		$this->joins = array_filter(
			$this->joins,
			create_function('$join', "return (\$join['type'] != '$type');")
		);
		
		return $this;
	}
	
	public function clearColumns() {
	
		$this->columns = array();
		
		return $this;
	}
	
	public function clearGroups() {
	
		$this->group = array();
		
		return $this;
	}
	
	public function clearOrder() {
	
		$this->order = array();
		
		return $this;
	}
	
	public function fetchOne() {
	
		$result = $this->execute();
		
		if ($result->valid())
			return $result->current();
			
		throw new Exception("No record found");
	}
	
	public function fetchValue() {

		$result = $this->execute();
		
		if ($result->valid()) {
			
			$current = $result->current();
		
			return current($current);
		}
			
		throw new Exception("No record found");
	}

	public function column($column) {
	
		$this->columns[] = $column;
		
		return $this;
	}
	
	public function groupBy($column) {
	
		$this->group[] = $column;
		
		return $this;
	}
	
	public function having($fragment, $args = NULL) {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		$this->having[] = $fragment;
		
		return $this;
	}
	
	public function join($table, $fragment = '', $args = NULL, $type = 'INNER') {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		list($table, $alias) = explode(' ', $table) + array('');
		
		$this->joins[$alias] = array('table' => $table, 'type' => $type, 'on' => $fragment);
		
		return $this;
	}
	
	public function leftJoin($table, $fragment = '', $args = NULL) {
	
		$this->join($table, $fragment, $args, 'LEFT');
		
		return $this;
	}
	
	public function limit($limit = NULL) {
	
		$this->limit = $limit;
		
		return $this;
	}
	
	public function offset($offset = NULL) {
	
		$this->offset = $offset;
		
		return $this;
	}
	
	public function orderBy($column) {
	
		$this->order[] = $column;
		
		return $this->order;
	}
	
	public function where($fragment, $args = NULL) {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		$this->where[] = $fragment;
		
		return $this;
	}
	
	public function getSql() {

		$sql = "SELECT " . implode(', ', $this->columns);
		$sql .= " FROM " . $this->table;
		
		foreach ($this->joins as $alias => $join) {
		
			$sql .= " {$join['type']} JOIN {$join['table']} $alias ON ({$join['on']})";
		}
		
		empty($this->where) or $sql .= " WHERE " . implode(" AND ", $this->where);
		empty($this->group) or $sql .= " GROUP BY " . implode(', ', $this->group);
		empty($this->having) or $sql .= " HAVING " . implode(' AND ', $this->having);
		empty($this->order) or $sql .= " ORDER BY " . implode(', ', $this->order);
		
		is_null($this->limit) or $sql .= " LIMIT ". $this->limit;
		is_null($this->offset) or $sql .= " OFFSET " . $this->offset;
		
		return $sql;
	}
}

class FAInsertQuery extends FAQuery {

	protected $table;
	
	protected $joins = array();
	protected $where = array();
	protected $group = array();
	protected $order = array();
	protected $set = array();
	
	protected $ignore;
	protected $select;

	public function getSql() {
	
		$sql = "INSERT";
		$this->ignore and $sql .= " IGNORE";
		$sql .= " INTO " . $this->table;
		
		foreach ($this->joins as $alias => $join) {
		
			$sql .= " {$join['type']} JOIN {$join['table']} $alias ON ({$join['on']})";
		}
		
		empty($this->set) or $sql .= " SET " . implode(', ', $this->set);
		is_null($this->select) or $sql .= " (" . $this->select->getSql() . ")";
		
		empty($this->where) or $sql .= " WHERE " . implode(" AND ", $this->where);
		empty($this->group) or $sql .= " GROUP BY " . implode(', ', $this->group);
		empty($this->order) or $sql .= " ORDER BY " . implode(', ', $this->order);
		
		return $sql;
	}
	
	public function groupBy($column) {
	
		$this->group[] = $column;
		
		return $this;
	}
	
	public function ignore($ignore = TRUE) {
	
		$this->ignore = $ignore;
		
		return $this;
	}
	
	public function join($table, $fragment = '', $args = NULL, $type = 'INNER') {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		list($table, $alias) = explode(' ', $table) + array('');
		
		$this->joins[$alias] = array('table' => $table, 'type' => $type, 'on' => $fragment);
		
		return $this;
	}
	
	public function leftJoin($table, $fragment = '', $args = NULL) {
	
		$this->join($table, $fragment, $args, 'LEFT');
		
		return $this;
	}
	
	public function orderBy($column) {
	
		$this->order[] = $column;
		
		return $this->order;
	}
	
	public function select(FASelectQuery $query) {
	
		$this->select = $query;
	}
	
	public function set($key, $value) {
	
		$this->set[] = $this->dba->mergeArguments("$key=?", $value);
			
		return $this;
	}
	
	public function where($fragment, $args = NULL) {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		$this->where[] = $fragment;
		
		return $this;
	}
}

class FAUpdateQuery extends FAQuery {

	protected $joins = array();
	protected $where = array();
	protected $group = array();
	protected $order = array();
	protected $set = array();

	public function getSql() {

		$sql = "UPDATE " . $this->table;
		
		foreach ($this->joins as $alias => $join) {
		
			$sql .= " {$join['type']} JOIN {$join['table']} $alias ON ({$join['on']})";
		}
		
		empty($this->set) or $sql .= " SET " . implode(', ', $this->set);
		
		empty($this->where) or $sql .= " WHERE " . implode(" AND ", $this->where);
		empty($this->group) or $sql .= " GROUP BY " . implode(', ', $this->group);
		empty($this->order) or $sql .= " ORDER BY " . implode(', ', $this->order);
		
		return $sql;
	}
	
	public function groupBy($column) {
	
		$this->group[] = $column;
		
		return $this;
	}
	
	public function join($table, $fragment = '', $args = NULL, $type = 'INNER') {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		list($table, $alias) = explode(' ', $table) + array('');
		
		$this->joins[$alias] = array('table' => $table, 'type' => $type, 'on' => $fragment);
		
		return $this;
	}
	
	public function leftJoin($table, $fragment = '', $args = NULL) {
	
		$this->join($table, $fragment, $args, 'LEFT');
		
		return $this;
	}
	
	public function orderBy($column) {
	
		$this->order[] = $column;
		
		return $this->order;
	}
	
	public function set($key, $value) {
	
		$this->set[] = $this->dba->mergeArguments("$key=?", $value);
			
		return $this;
	}
	
	public function setAll($array) {
	
		foreach ($array as $key => $value)
			$this->set($key, $value);
			
		return $this;
	}
	
	public function where($fragment, $args = NULL) {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		$this->where[] = $fragment;
		
		return $this;
	}
}

class FADeleteQuery extends FAQuery {

	private $joins = array();
	private $where = array();
	private $group = array();
	private $having = array();
	private $order = array();
	
	private $limit;
	private $offset;
	
	public function groupBy($column) {
	
		$this->group[] = $column;
		
		return $this;
	}
	
	public function having($fragment, $args = NULL) {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		$this->having[] = $fragment;
		
		return $this;
	}
	
	public function join($table, $fragment = '', $args = NULL, $type = 'INNER') {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		list($table, $alias) = explode(' ', $table) + array('');
		
		$this->joins[$alias] = array('table' => $table, 'type' => $type, 'on' => $fragment);
		
		return $this;
	}
	
	public function leftJoin($table, $fragment = '', $args = NULL) {
	
		$this->join($table, $fragment, $args, 'LEFT');
		
		return $this;
	}
	
	public function limit($limit = NULL) {
	
		$this->limit = $limit;
		
		return $this;
	}
	
	public function offset($offset = NULL) {
	
		$this->offset = $offset;
		
		return $this;
	}
	
	public function orderBy($column) {
	
		$this->order[] = $column;
		
		return $this->order;
	}
	
	public function where($fragment, $args = NULL) {
	
		is_null($args) or $fragment = $this->dba->mergeArguments($fragment, $args);
		
		$this->where[] = $fragment;
		
		return $this;
	}
	
	public function getSql() {

		$sql = "DELETE FROM " . $this->table;
		
		foreach ($this->joins as $alias => $join) {
		
			$sql .= " {$join['type']} JOIN {$join['table']} $alias ON ({$join['on']})";
		}
		
		empty($this->where) or $sql .= " WHERE " . implode(" AND ", $this->where);
		empty($this->group) or $sql .= " GROUP BY " . implode(', ', $this->group);
		empty($this->having) or $sql .= " HAVING " . implode(' AND ', $this->having);
		empty($this->order) or $sql .= " ORDER BY " . implode(', ', $this->order);
		
		is_null($this->limit) or $sql .= " LIMIT ". $this->limit;
		is_null($this->offset) or $sql .= " OFFSET " . $this->offset;
		
		return $sql;
	}
}

?>