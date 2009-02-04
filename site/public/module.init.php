<?php

function module_init($registry, $request, $response) {

	$response->topnav = array(
		'home' => anchor('index.index', "Home"),
		'news' => anchor('news.index', "News"),
	);
}

?>