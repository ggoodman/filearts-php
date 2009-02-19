<?php

require_once 'path.php';

require_once 'view/helpers.php';
require_once 'view/section.php';

class FAView {

	static private $dirs = array();
	
	static public function render(FAPath $path, $context = array()) {
	
		$filename = $path->getController() . '.' . $path->getAction() . '.php';
		
		foreach (self::$dirs as $dir) {
		
			if (file_exists($dir . '/' . $filename)) {
			
				$filename = $dir . '/' . $filename;
				break;
			}
		}
		
		if (!file_exists($filename))
			throw new Exception("View not found: $filename");
			
		defined('DEBUG') and $handler = set_error_handler(array('FAView', 'filterError'));
			
		call_user_func_array(
			create_function('$f__,$a__', '$e__ = error_reporting(E_ALL);extract($a__, EXTR_SKIP);require($f__);error_reporting($e__);'),
			array($filename, $context)
		);
		
		defined('DEBUG') and set_error_handler($handler);
	}
	
	static public function filterError($code, $message) {
		
		if ($code & E_NOTICE && preg_match('/^Undefined variable: (?<var>.+)$/', $message, $matches)) {
		
			echo "\${$matches['var']}";
		}
	}
	
	static public function addPath($dir) {
	
		self::$dirs[] = $dir;
	}
}

function render_view($filename, $context = array()) {

	FAView::render($filename, $context);
}

function display_http_error($http_code, $php_message, $php_code = 0, $trace = array()) {

	$messages = array(
		403 => "Access denied",
		404 => "Not found",
		500 => "Internal server error",
	);

	$context = array(
		'http_code' => $http_code,
		'debug' => defined('DEBUG'),
		'php_code' => $php_code,
		'php_message' => $php_message,
		'php_backtrace' => $trace,
	);
	
	header("HTTP/1.0 $http_code {$messages[$http_code]}");
	
	try {
	
		FAView::addPath(dirname(__FILE__) . '/view');
		FAView::render(path("error.$http_code"), $context);
	} catch (Exception $e) {
	
		// Last resort catch-all;
		echo "<pre>$e</pre>";
	}

	exit();
}

function render($filename, $context = array()) {

	if (!file_exists($filename)) {
	
		$filename = '../' . $filename;
	}
	
	if (file_exists($filename)) {
	
		extract($context);
		require($filename);
	}
}

?>