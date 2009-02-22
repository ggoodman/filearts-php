<?php

require_once '../lib/bootstrap.php';

function page_init($registry, $request, $response) {
	
	$response->topnav['news']->class('current');
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
		
		$comment = $registry->dba->findOrNew('Comment', $form->id)
			->setArray($form->getValues())
			->save();
		
		path('news.view')
			->arg('id', $comment->article_id)
			->anchor("comment_" . $comment->id)
			->redirectTo();
	}
}

function index_action($registry, $request, $response) {

	$response->news = $registry->dba->findAll('Article');
	
	paginate($response->news);
}

function view_year_action($registry, $request, $response) {

	$response->news = $registry->dba->findAll('Article')
		->where('YEAR(Article.published)=?', $request->year);
	
	paginate($response->news);
	
	$response->view = 'index';
}

function save_action($registry, $request, $response) {

	$form = article_form($request);
	
	if ($form->isValid($request->post)) {
	
		$article = $registry->dba->findOrNew('Article', $request->id)
			->setArray($form->getValues())
			->save();
		
		path('news.view')->arg('id', $article->id)->redirectTo();
	}
	
	$response->article_form = $form;
	$response->view = 'edit';
}

function view_action($registry, $request, $response) {

	if (isset($request->id)) {

		$response->article = $registry->dba->Article($request->id);
	} else {
	
		$response->article = $registry->dba->Article
			->set('title', str_replace('-', ' ', $request->title))
			->find();
	}
	
	if (!$response->article) display_http_error(404, "Article not found");
	
	$request->id = $response->article->id;
	
	$response->comment_form = comment_form($request);
	
	paginate($response->article->comments);
}


function view_title_action($registry, $request, $response) {

	$response->article = (isset($request->title)) ? 
		$registry->dba->findAll('Article')
			->where('Article.title=?', str_replace('-', ' ', $request->title))
			->getFirst() 
		: NULL;
	
	if (!$response->article) display_http_error(404, "Article not found");
	
	$request->id = $response->article->id;
	
	$response->comment_form = comment_form($request);
	
	paginate($response->article->comments);
	
	$response->view = 'view';
}

?>