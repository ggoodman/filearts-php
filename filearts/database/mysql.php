<?php

require_once 'database.php';

class FAMysqlResultset extends FADatabaseResultSet {

	private $result;
	
	public function __construct($result) {
	
		$this->result = $result;
		$this->size = mysql_num_rows($result);
		
		$this->rewind();
	}
	
	public function fetch() {
	
		return mysql_fetch_array($this->result);
	}

	public function size() {
		
		return $this->size;
	}
}

class FAMysqlConnection extends FADatabaseConnection {

	private $link;

	public function __construct($host, $user, $pass, $db) {
		
		$this->link = mysql_pconnect($host, $user, $pass);
		
		mysql_select_db($db, $this->link);
	}
	
	public function quote($str) {
	
		return mysql_real_escape_string($str, $this->link);
	}
	
	public function query($sql, $args = array()) {
		
		$sql = $this->mergeArguments($sql, $args);
		
		$before = array_sum(explode(' ', microtime()));
		$ret = mysql_query($sql, $this->link);
		$time = array_sum(explode(' ', microtime())) - $before;
		
		section_start('debug');
		echo "<script type=\"text/javascript\">if (typeof(console) != 'undefined') console.log('".addslashes($sql)."; $time');</script>\n";
		section_end();
		
		//echo "SQL: $sql<br />\n";
		if (is_resource($ret)) return new FAMysqlResultset($ret);
		
		if ($ret == FALSE) {echo "SQL: $sql<br />\n"; throw new Exception("Mysql error: " . mysql_error($this->link));}
		
		return mysql_affected_rows($this->link);
	}
	
	public function lastInsertId() {
	
		return mysql_insert_id($this->link);
	}
}

?>