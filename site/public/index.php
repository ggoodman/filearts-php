<?php

require_once '../lib/bootstrap.php';

function page_init($registry, $request, $response) {
	
	$response->topnav['home']->class('current');
}

function index_action($registry, $request, $response) {

	$response->news = paginate($registry->dba->findAll('Article'));
}

?>