<?php

require_once '../../lib/bootstrap.php';

function page_init($registry, $request, $response) {
	
	$response->topnav['articles']->class('current');
}

function article_form($request) {

	return new FAForm(array(
		'action' => path('articles.save'),
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
				'type' => 'text',
				'name' => '_tags',
				'title' => 'Tags associated with this article',
				'label' => 'Tags:',
				'validators' => array(
				),
			), array(
				'type' => 'textarea',
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

function create_action($registry, $request, $response) {

	$response->article_form = article_form($request);
	$response->view = 'edit';
}

function delete_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->Article($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");

	if (isset($request->confirm)) {
	
		$registry->dba->Article($request->id)->delete();
		path('articles.index')->redirectTo();
	}
}

function edit_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->Article($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");

	$response->article_form = article_form($request);
	$response->article_form->isValid($response->article->toArray());
}

function index_action($registry, $request, $response) {

	$response->articles = $registry->dba->findAll('Article');
	
	paginate($response->articles);
}

function save_action($registry, $request, $response) {

	$form = article_form($request);
	
	if ($form->isValid($request->post)) {
	
		$article = $registry->dba->findOrNew('Article', $request->id)
			->setArray($form->getValues())
			->save();
		
		path('articles.view')->arg('id', $article->id)->redirectTo();
	}
	
	$response->article_form = $form;
	$response->view = 'edit';
}

function view_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->Article($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");
}

?>