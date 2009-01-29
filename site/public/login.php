<?php

require_once '../lib/bootstrap.php';

function login_form($request) {

	return new FAForm(array(
		'action' => path('login.login'),
		'method' => 'post',
		'elements' => array(
			array(
				'type' => 'text',
				'name' => 'username',
				'title' => 'Username',
				'label' => 'Username:',
				'validators' => array(
					array('type' => 'regex', 'value' => '/[^\s]/')
				),
			), array(
				'type' => 'password',
				'name' => 'password',
				'title' => 'Password',
				'label' => 'Password:',
				'validators' => array(
					array('type' => 'regex', 'value' => '/[^\s]/')
				),
			), array(
				'type' => 'submit',
				'value' => 'Login',
			)
		),
	));
}

function login_action($registry, $request, $response) {

	$response->login_form = login_form($request);
	
	if ($request->method = 'POST') {
	
		if ($response->login_form->isValid($request->post)) {
			
			$user = $registry->dba->User->verify($response->login_form->getValues());
			
			if ($user) {
				
				$request->session['user_id'] = $user->id;
				redirect_to('index.index');
			}
			
			$response->login_error = "Invalid username/password";
		} else {
		
			$response->login_error = "Please enter a valid username and password";
		}
	}
}

?>