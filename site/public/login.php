<?php

require_once '../lib/bootstrap.php';

function login_action($registry, $request, $response) {

	if ($request->method == 'POST') {
	
		if (form('login')->isValid($request->post)) {
		
			$request->session['user_id'] = form('login')->getUser()->id;
			
			if (isset($request->ref)) FAPath::setRedirect($request->ref);
			else path('..')->redirectTo();
		}
	}
}

?>