<?php

abstract class FADatabaseResultSet implements Iterator {
	
	protected $index = -1;
	protected $cache = array();
	protected $valid = TRUE;
	
	abstract public function size();
	abstract public function fetch();

	public function current() {
	
		return $this->cache[$this->index];
	}
		
	public function next() {
	
		$this->index++;
	
		if (isset($this->cache[$this->index])) {
		
			$this->valid = TRUE;
		} else {
			
			$current = $this->fetch();
			
			if ($current !== FALSE) {
			
				$this->cache[$this->index] = $current;
				$this->valid = TRUE;
			} else {
			
				$this->valid = FALSE;
			}
		}
	}
	
	public function key() {
		
		return $this->index;
	}
	
	public function rewind() {
	
		if (!empty($this->cache))
			$this->index = -1;
		
		$this->next();
	}
	
	public function valid() {
	
		return $this->valid;
	}
}

?>