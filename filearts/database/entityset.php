<?php

// Inspired by Peter
class FALazyProperty {

	public function __get($property) {
	
		$method = "create{$property}Instance";
		
		if (method_exists($this, $method)) return ($this->$property = $this->$method());
	}
}

class FAEntitySet extends FALazyProperty implements OuterIterator {

	protected $entityClass = NULL;
	protected $query = NULL;
	protected $original = NULL;
	
	public function __construct($entityClass, FAQuery $query) {
		
		$this->entityClass = $entityClass;
		$this->original = $this->query = $query->setClass();
		
		$this->reset();
	}
	
	public function reset() {
	
		$this->query = clone $this->original;
		
		unset($this->iterator);
		
		return $this;
	}
	
	public function __call($method, $args) {
	
		call_user_func_array(array(&$this->query, $method), $args);
		
		return $this;
	}
	
	public function createIteratorInstance() {
	
		return $this->query->execute();
	}
	
	public function getFirst() {
	
		if ($this->valid()) return $this->current();
	}
	
	public function getInnerIterator() {
	
		return $this->iterator;
	}
	
	public function getQuery() {
	
		if (!isset($this->query)) throw new Exception("Cannot modify a result set that has settled.");
	
		return $this->query;
	}
	
	public function current() {
	
		$class = $this->entityClass;

		$entity = new $class($this->iterator->current());
		
		return $entity;
	}

	public function key() {
		
		return $this->iterator->key();
	}
	
	public function next() {
	
		return $this->iterator->next();
	}
	
	public function rewind() {
	
		return $this->iterator->rewind();
	}
	
	public function valid() {
	
		return $this->iterator->valid();
	}
}

?>