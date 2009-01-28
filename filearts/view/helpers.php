<?php

function limit($str, $length = 20, $suffix = '') {

	return substr($str, 0, $length - strlen($suffix)) . $suffix;
}

?>