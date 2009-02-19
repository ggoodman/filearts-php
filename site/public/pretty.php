<?php

if (!defined('SITE_DIR')) define ('SITE_DIR', realpath(dirname(__FILE__) . '/../public'));

require_once dirname(__FILE__) . '/../../filearts/filearts.php';

define ('FA_PRETTY', TRUE);

$routes = array(
	'/blog/:year/:month/:title/' => array(
		'controller' => 'blog',
		'action' => 'view',
	),
);

FARouter::init($routes);
FARouter::dispatch($_SERVER['REQUEST_URI']);

?>