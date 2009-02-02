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

function comment_form($request) {

	return new FAForm(array(
		'action' => path('news.comment'),
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
				'name' => 'article_id',
				'validators' => array(
					array('type' => 'regex', 'value' => '/^[0-9]+$/')
				),
				'value' => $request->id,
			), array(
				'type' => 'hidden',
				'name' => 'user_id',
				'validators' => array(
					array('type' => 'regex', 'value' => '/^[0-9]+$/')
				),
				'value' => $request->visitor->id,
			), array(
				'type' => 'richedit',
				'name' => 'body',
				'title' => 'The body of the comment',
				'label' => 'Comment:',
				'validators' => array(
					array('type' => 'regex', 'value' => '/[^\s]/')
				),
			), array(
				'type' => 'submit',
				'value' => 'Comment',
			)
		),
	));
}

function comment_action($registry, $request, $response) {

	$response->article = (isset($request->article_id)) ? $registry->dba->Article($request->article_id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");
	
	$form = comment_form($request);
	
	if ($form->isValid($request->post)) {
		
		$comment = $registry->dba->Comment->findOrNew($form->id)
			->setArray($form->getValues())
			->save();
		
		path('news.view')
			->arg('id', $comment->article_id)
			->anchor("comment_" . $comment->id)
			->redirectTo();
	}
}

function delete_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->Article($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");

	if (isset($request->confirm)) {
	
		$registry->dba->Article($request->id)->delete();
		path('index.index')->redirectTo();
	}
}

function edit_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->Article($request->id) : NULL;
	
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
	
		$article = $registry->dba->Article->findOrNew($request->id)
			->setArray($form->getValues())
			->save();
		
		path('news.view')->arg('id', $article->id)->redirectTo();
	}
	
	$response->article_form = $form;
	$response->view = 'edit';
}

function view_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->Article($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");
	
	$response->comment_form = comment_form($request);
	
	paginate($response->article->comments);
}

function write_action($registry, $request, $response) {

	$response->article_form = article_form($request);
	$response->view = 'edit';
}

?>