<?php

require_once '../lib/bootstrap.php';

function article_form($request) {

	return new FAForm(array(
		'action' => path('news.save'),
		'method' => 'post',
		'elements' => array(
			array(
				'type' => 'hidden',
				'name' => 'id',
				'validators' => array(
					array('type' => 'regex', 'value' => '/^[0-9]*$/')
				),
			), array(
				'type' => 'hidden',
				'name' => 'user_id',
				'validators' => array(
					array('type' => 'regex', 'value' => '/^[0-9]+$/')
				),
				'value' => $request->visitor->id,
			), array(
				'type' => 'text',
				'name' => 'title',
				'title' => 'The title of the article',
				'label' => 'Title:',
				'validators' => array(
					array('type' => 'regex', 'value' => '/[^\s]/')
				),
			), array(
				'type' => 'richedit',
				'name' => 'body',
				'title' => 'The body of the article',
				'label' => 'Body:',
				'validators' => array(
					array('type' => 'regex', 'value' => '/[^\s]/')
				),
			), array(
				'type' => 'submit',
				'value' => 'Publish',
			)
		),
	));
}

function delete_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->News($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");

	if (isset($request->confirm)) {
	
		$registry->dba->News($request->id)->delete();
		path('index.index')->redirectTo();
	}
}

function edit_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->News($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");

	$response->article_form = article_form($request);
	$response->article_form->isValid($response->article->toArray());
}

function index_action($registry, $request, $response) {

	path('index.index')->redirectTo();
}

function save_action($registry, $request, $response) {

	$form = article_form($request);
	
	if ($form->isValid($request->post)) {
	
		$article = $registry->dba->News->findOrNew($request->id)
			->setArray($form->getValues())
			->save();
		
		path('news.view')->arg('id', $article->id)->redirectTo();
	}
	
	$response->article_form = $form;
	$response->view = 'edit';
}

function view_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->News($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");
}

function write_action($registry, $request, $response) {

	$response->article_form = article_form($request);
	$response->view = 'edit';
}

?>