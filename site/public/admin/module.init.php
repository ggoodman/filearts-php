<?php

function module_init($registry, $request, $response) {

	$response->topnav = array(
		'home' => anchor('index.index', "Admin"),
		'articles' => anchor('articles.index', "Articles"),
	);

	if (!$request->visitor->isMember()) display_http_error("403", "Access Denied");
}

?>