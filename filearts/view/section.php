<?php

class FASection {

	static private $stack = array();
	static private $sections = array();
	
	static public function get($section) {
	
		if (isset(self::$sections[$section])) return self::$sections[$section];
	}

	static public function start($var) {
		
		array_push(self::$stack, $var);
		ob_start();
		
		if (!isset(self::$sections[$var])) self::$sections[$var] = '';
	}
	
	static public function end() {
	
		$var = array_pop(self::$stack);
		
		self::$sections[$var] .= ob_get_contents();
		
		ob_end_clean();
	}
}

function section_start($var) {

	return FASection::start($var);
}

function section_end() {

	return FASection::end();
}

function section_get($section) {

	return FASection::get($section);
}

function section_print($section) {
	
	print FASection::get($section);
}

?>