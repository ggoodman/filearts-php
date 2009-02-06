<?php

require_once '../../lib/bootstrap.php';

function page_init($registry, $request, $response) {
	
	$response->topnav['home']->addClass('current');
}

function index_action($registry, $request, $response) {

}

?>