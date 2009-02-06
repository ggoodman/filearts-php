<?php

define('STARTUP', array_sum(explode(' ', microtime())));

error_reporting(E_ALL);
set_error_handler('handle_error');
set_exception_handler('handle_exception');
ob_start();

require_once 'functions.php';
require_once 'path.php';
require_once 'view.php';
require_once 'database.php';
require_once 'form.php';

set_include_path(dirname(__FILE__)
	. PATH_SEPARATOR . get_include_path()
);

class FARegistry extends stdClass {

	private function __construct() {
	}
	
	static private $instance;
	
	static public function instance() {
	
		if (!isset(self::$instance)) self::$instance = new FARegistry;
		
		return self::$instance;
	}
}

function get_registry() {

	return FARegistry::instance();
}


class FARequest extends stdClass {

	private function __construct() {
	
		$this->action = path()->getAction();
		$this->controller = path()->getController();
		$this->module = path()->getModule();
		$this->base_url = path()->getBase();
		
		$this->method = $_SERVER['REQUEST_METHOD'];
		
		$this->get = (get_magic_quotes_gpc()) ? stripslashes_deep($_GET) : $_GET;
		$this->post = (get_magic_quotes_gpc()) ? stripslashes_deep($_POST) : $_POST;
		$this->cookie = (get_magic_quotes_gpc()) ? stripslashes_deep($_COOKIE) : $_COOKIE;

		session_start();
		$this->session = &$_SESSION;
		
		$this->all = (get_magic_quotes_gpc()) ? stripslashes_deep($_REQUEST) : $_REQUEST;
		
		foreach ($this->all as $key => $value) $this->$key = $value;
	}
	
	static private $instance;
	
	static public function instance() {
	
		if (!isset(self::$instance)) self::$instance = new FARequest;
		
		return self::$instance;
	}
}

function get_request() {

	return FARequest::instance();
}

class FAResponse extends stdClass {

	private function __construct() {
	
		$this->view = path()->getAction();
		$this->base_url = path()->getBase();
	}
	
	static private $instance;
	
	static public function instance() {
	
		if (!isset(self::$instance)) self::$instance = new FAResponse;
		
		return self::$instance;
	}
}

function get_response() {

	return FAResponse::instance();
}

function load_module() {

	$path = split_path(path()->getModule());
	$base = SITE_DIR;
	
	array_unshift($path, '.'); // Allow the algorithm to search the base path
		
	while (!empty($path)) {
	
		$filename = $base . '/' . implode('/', $path) . '/module.init.php';
		
		if (file_exists($filename)) {
			
			require $filename;
			break;
		}
	
		array_pop($path);
	}
	
	if (!function_exists('module_init')) {
		
		function module_init() {}
	}
}

function load_site() {

	$base = SITE_DIR;
	$filename = $base . '/site.init.php';
	
	if (file_exists($filename)) require $filename;
	
	if (!function_exists('site_init')) {
		
		function site_init() {}
	}
}

function load_page() {

	if (!function_exists('page_init')) {
		
		function page_init() {}
	}
}

function handle_error($code, $message, $trace = NULL) {

	if ($code & error_reporting()) {
	
		if (!is_array($trace)) $trace = debug_backtrace();
	
		ob_clean();
		display_http_error('500', $message, $code, $trace);
	}
}

function handle_exception(Exception $e) {

	$code = ($e->getCode()) ? $e->getCode() : E_USER_ERROR;

	handle_error($code, $e->getMessage(), $e->getTrace());
}

function handle_request() {

	$registry = get_registry();
	$request = get_request();
	$response = get_response();

	load_site();
	load_module();
	load_page();
	
	site_init($registry, $request, $response);
	module_init($registry, $request, $response);
	page_init($registry, $request, $response);
	
	$handler = path()->getAction() . '_action';

	if (!function_exists($handler))
		display_http_error('404', "Page not found");
		
	$handler($registry, $request, $response);
	
	FAView::render(path()->a($response->view), get_object_vars($response));
	
	ob_end_flush();
}

?>