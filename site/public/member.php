<?php

require_once '../lib/bootstrap.php';

function create_action($registry, $request, $response) {
	
	$form = form('register');

	if ($form->isValid($request->post)) {
		
		$values = $form->getValues();
		$values['password'] = md5(SALT . $values['password']);
		
		$user = $registry->dba->User
			->setArray($values)
			->save();
				
		if ($user) {
			
			$request->session['user_id'] = $user->id;
			if (isset($request->ref)) FAPath::setRedirect($request->ref);
			else path('index.index')->redirectTo();
		}
		
		$response->register_error = "That username is taken, please choose another.";
	} else {
		
		$response->register_error = "Please complete all fields.";
	}
	
	$response->register_form = $form;
	$response->view = 'register';
}

function logout_action($registry, $request, $response) {

	if ($request->visitor->isMember()) {
	
		unset($request->session['user_id']);
		if (isset($request->ref)) FAPath::setRedirect($request->ref);
		else path('index.index')->redirectTo();
	}
	
	throw new Exception("You must be logged in to access this page.");
}

function register_action($registry, $request, $response) {

}

?>