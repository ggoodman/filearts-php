<?php

class FAPagerPage {

	protected $page;
	
	public function __construct($page) {
	
		$this->page = $page;
	}
	
	public function getLink() {
	
		return anchor()->set('p', $this->getPage());
	}
	
	public function getPage() {
		
		return $this->page;
	}
}

class FAPageIterator extends ArrayIterator {

	public function current() {
		
		return new FAPagerPage(parent::current());
	}
}

class FAPager {

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
	
	public function getNumPages() {

		return ceil($this->total / $this->size);
	}
	
	public function getPages() {
		
		return new FAPageIterator(range(1, $this->getNumPages()));
	}
	
	public function firstPage() {
		
		return get_path_args(array('p' => 1));
	}
	
	public function prevPage() {
		
		return get_path_args(array('p' => $this->page - 1));
	}
	
	public function nextPage() {

		return get_path_args(array('p' => $this->page + 1));
	}
	
	public function lastPage() {

		return get_path_args(array('p' => $this->getNumPages()));
	}

	public function isFirstPage() {
	
		return ($this->page == 1);
	}
	
	public function isLastPage() {
	
		return ($this->page >= $this->total / $this->size);
	}
	
	static public function paginate(FARecordSet $results, $size = 10) {
	
		$request = get_request();
		
		if (!isset($request->p)) $request->p = 1;
		if (!isset($request->s)) $request->s = $size;

		$limit = $request->s;
		$offset = $request->s * ($request->p - 1);

		$results->getQuery()->limit($limit)->offset($offset);

		$query = clone $results->getQuery();
		
		$result = $query
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
		
		get_response()->pager = new FAPager($total, $request->p, $request->s);
		
		return $results;
	}
}

function paginate(FARecordSet $results, $size = 10) {
	
	return FAPager::paginate($results, $size);
}

?>