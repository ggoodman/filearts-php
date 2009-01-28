<?php

function split_path($path) {
	
	return preg_split("~/~", $path, -1, PREG_SPLIT_NO_EMPTY);
}

function stripslashes_deep($value) {

	$value = is_array($value) ?
		array_map('stripslashes_deep', $value) :
		stripslashes($value);
	
	return $value;
}

?>