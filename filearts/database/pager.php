<?php

class FAPager {

	const DEFAULT_PAGE_SIZE = 20;

	const PAGE_VAR = 'p';
	const PAGE_SIZE_VAR = 's';

	protected $total;
	protected $page;
	protected $size;

	protected function __construct($total, $page, $size) {
	
		$this->total = $total;
		$this->page = $page;
		$this->size = $size;
	}
	
	public function getPage() {
	
		return $this->page;
	}
	
	public function getPath() {
	
		$path = path();
		
		if ($path->getArg(self::PAGE_SIZE_VAR) !== self::DEFAULT_PAGE_SIZE)
			$path->keep(self::PAGE_SIZE_VAR);
		
		return $path;
	}
	
	public function getNumPages() {

		return ceil($this->total / $this->size);
	}
	
	public function getPages() {
	
		$pages = array();
		
		foreach (range(1, $this->getNumPages()) as $i) $pages[$i] = $this->getPath()->arg(self::PAGE_VAR, $i);
		
		return $pages;
	}
	
	public function getFirstPage() {
	
		return $this->getPath()->arg(self::PAGE_VAR, 1);
	}
	
	public function getPrevPage() {
	
		return $this->getPath()->arg(self::PAGE_VAR, $this->page - 1);
	}
	
	public function getNextPage() {
	
		return $this->getPath()->arg(self::PAGE_VAR, $this->page + 1);
	}
	
	public function getLastPage() {
	
		return $this->getPath()->arg(self::PAGE_VAR, $this->getNumPages());
	}

	public function isFirstPage() {
	
		return ($this->page == 1);
	}
	
	public function isLastPage() {
	
		return ($this->page >= $this->total / $this->size);
	}
	
	static public function paginate(FAEntitySet $results, $size = self::DEFAULT_PAGE_SIZE) {
	
		$request = FARequest::instance();
		
		$p = self::PAGE_VAR;
		$s = self::PAGE_SIZE_VAR;
		
		if (!isset($request->$p)) $request->$p = 1;
		if (!isset($request->$s)) $request->$s = $size;

		$limit = $request->$s;
		$offset = $request->$s * ($request->$p - 1);

		$results->getQuery()->limit($limit)->offset($offset);

		$query = clone $results->getQuery();
		
		$result = $query
			->setClass()
			->clearColumns()
			->clearOrder()
			->clearGroups()
			->clearJoins("LEFT")
			->column("COUNT(*)")
			->limit()
			->offset();
			
		$total = $result->fetchValue();
		
		unset($query);
		unset($result);
		
		$results->pager = new FAPager($total, $request->$p, $request->$s);
		
		return $results;
	}
}

function paginate(FAEntitySet $results, $size = FAPager::DEFAULT_PAGE_SIZE) {
	
	return FAPager::paginate($results, $size);
}

?>