<?php

require_once '../../lib/bootstrap.php';

function page_init($registry, $request, $response) {
	
	$response->topnav['articles']->class('current');
}

function create_action($registry, $request, $response) {

	$response->article_form = form('article');
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

	$response->article_form = form('article');
	$response->article_form->isValid($response->article->toArray());
}

function index_action($registry, $request, $response) {

	$response->articles = $registry->dba->findAll('Article');
	
	paginate($response->articles);
}

function save_action($registry, $request, $response) {

	$form = form('article');
	
	if ($form->isValid($request->post)) {
	
		$article = $registry->dba->findOrNew('Article', $request->id)
			->setArray($form->getValues())
			->save();
		
		path('articles.view')->arg('id', $article->id)->redirectTo();
	}
	
	$response->article_form_error = "Invalid article";
	
	$response->article_form = $form;
	$response->view = 'edit';
}

function view_action($registry, $request, $response) {

	$response->article = (isset($request->id)) ? $registry->dba->Article($request->id) : NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");
}

?>