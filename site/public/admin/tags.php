<?php

require_once '../../lib/bootstrap.php';

function index_action($registry, $request, $response) {

	foreach ($registry->dba->getDatabase('tags')->select('article_tag')->column('DISTINCT tag_id')->where('tag_id LIKE ?', $request->q . '%') as $tag) {
		echo $tag['tag_id']."\n";
	}
	exit();
}

?>