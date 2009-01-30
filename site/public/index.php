<?php

require_once '../lib/bootstrap.php';

function page_init($registry, $request, $response) {

	if (isset($request->session['user_id'])) {
	
		$request->user = $registry->dba->User($request->session['user_id']);
	}
}

function index_action($registry, $request, $response) {

	$response->user = (isset($request->user)) ? $request->user->username : 'Guest';
	$response->news = $registry->dba->News->findAll();
	
	paginate($response->news);
}

?>