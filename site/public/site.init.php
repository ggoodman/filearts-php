<?php

require_once '../../filearts/filearts.php';

function site_init($registry, $request, $response) {

	$registry->dba = FAPersistence::connect(database('mysql://root@localhost/filearts'));
	
	$request->visitor = new Visitor;
	
	if (isset($request->session['user_id'])) {
	
		$user = $registry->dba->User($request->session['user_id']);
		
		if ($user) $request->visitor->setUser($user);
	}
	
	$response->visitor = $request->visitor;
}

?>