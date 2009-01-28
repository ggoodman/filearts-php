<?php

require_once 'path.php';

require_once 'view/helpers.php';
require_once 'view/section.php';

class FAView {

	static private $dirs = array('.');
	
	static public function render(FAPath $path, $context = array()) {
	
		$filename = $path->getController() . '.' . $path->getAction() . '.php';
		
		foreach (self::$dirs as $dir) {
		
			if (file_exists($dir . '/' . $filename)) {
			
				$filename = $dir . '/' . $filename;
				break;
			}
		}
	
		if (!file_exists($filename))
			throw new Exception("View not found");
			
		defined('DEBUG') and $handler = set_error_handler(array('FAView', 'filterError'));
			
		call_user_func_array(
			create_function('$f__,$a__', '$e__ = error_reporting(0);extract($a__, EXTR_SKIP);require($f__);error_reporting($e__);'),
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

function display_http_error($http_code, $http_message, $php_code = 0, $php_message = '') {

	$context = array(
		'http_code' => $http_code,
		'http_message' => $http_message,
		'debug' => (defined('DEBUG')) ? 'debug' : 'production',
		'php_code' => $php_code,
		'php_message' => $php_message,
		'php_backtrace' => debug_backtrace(),
	);
	
	header("HTTP/1.0 $http_code $http_message");
	
	try {
	
		FAView::addPath('filearts/view');
		FAView::render(path("error.$http_code"), $context);
	} catch (Exception $e) {
	
		// Last resort catch-all;
		echo "<pre>$e</pre>";
	}

	exit();
}

?>