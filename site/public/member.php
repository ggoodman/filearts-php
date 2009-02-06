<?php

require_once '../lib/bootstrap.php';

function register_form($request) {

	return new FAForm(array(
		'action' => path('member.create'),
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
				'type' => 'text',
				'name' => 'name',
				'title' => 'Write your full name as you wish others to see',
				'label' => 'Full Name:',
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
				'type' => 'password',
				'name' => 'confirm',
				'title' => 'Confirm',
				'label' => 'Confirm Password:',
				'validators' => array(
					array('type' => 'regex', 'value' => '/[^\s]/')
				),
			), array(
				'type' => 'submit',
				'value' => 'Register',
			)
		),
		'validators' => array(
			array('type' => 'equal', 'value' => array('password', 'confirm')),
		),
	));
}

function create_action($registry, $request, $response) {
	
	$form = register_form($request);

	if ($form->isValid($request->post)) {
		
		$values = $form->getValues();
		$values['password'] = md5(SALT . $values['password']);
		
		$user = $registry->dba->User
			->setArray($values)
			->save();
				
		if ($user) {
			
			$request->session['user_id'] = $user->id;
			path('index.index')->redirectTo();
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
		if (isset($request->ref)) FAPath::redirect($request->ref);
		else path('index.index')->redirectTo();
	}
	
	throw new Exception("You must be logged in to access this page.");
}

function register_action($registry, $request, $response) {

	$response->register_form = register_form($request);
}

?>