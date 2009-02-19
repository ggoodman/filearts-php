<?php

interface FAPathBuilder {

	public function buildPath(FAPath $path);
}

interface FAPathRenderer {

	public function renderPath(FAPath $path);
}

/**
 * PathBuilder that creates paths to actual files, passing the action through a GET parameter
 */
class FADefaultPathBuilder implements FAPathBuilder {

	public function buildPath(FAPath $path) {
	
		$ret = $path->getBase();
		$args = $path->getArgs();
		
		if ($path->getModule() != FAPath::DEFAULT_MODULE) $ret .= str_replace('.', '/', $path->getModule()) . '/';
		if ($path->getAction() != FAPath::DEFAULT_ACTION) $args[FAPath::ACTION_VAR] = $path->getAction();
		
		$ret .= $path->getController() . '.php';
		
		if (!empty($args)) $ret .= '?' . http_build_query($args, '');
		if ($path->getAnchor()) $ret .= '#' . $path->getAnchor();
		
		return $ret;
	}
}

class FAFilenamePathBuilder implements FAPathBuilder {

	public function buildPath(FAPath $path) {
	
		$ret = $path->getRoot() . $path->getBase();
		
		if ($path->getModule() != FAPath::DEFAULT_MODULE) $ret .= str_replace('.', '/', $path->getModule()) . '/';
	
		$ret .= $path->getController() . '.php';
		
		return $ret;
	}
}

class FADefaultPathRenderer implements FAPathRenderer {

	public function renderPath(FAPath $path) {
	
		return $path->getPath();
	}
}

class FAPath {

	const ACTION_VAR = 'a';
	const BACKREF_VAR = 'ref';

	const DEFAULT_MODULE = 'index';
	const DEFAULT_CONTROLLER = 'index';
	const DEFAULT_ACTION = 'index';
	
	protected $base;
	protected $root;
		
	protected $module;
	protected $controller;
	protected $action;
	
	protected $args = array();
	protected $mask = array();
	protected $meta = array();
	
	protected $anchor;
	
	protected $pathBuilder;
	protected $pathRenderer;
	
	static private $_root;
	static private $_base;
	static private $_module;
	static private $_controller;
	static private $_action;
	static private $_args;
	
	static private $defaultBuilder;
	static private $defaultRenderer;
	
	public static function setDefaultPathBuilder(FAPathBuilder $builder) {
	
		self::$defaultBuilder = $builder;
	}
	
	public static function setDefaultPathRenderer(FAPathRenderer $renderer) {
	
		self::$defaultRenderer = $renderer;
	}
	
	public static function setRedirect($path) {
	
		header("Location: $path");
		exit();
	}

	/**
	 * Initialize the path object and determine actual path
	 *
	 * @static
	 */
	public static function init() {
	
		if (!defined('SITE_DIR')) {
		
			//trigger_error("SITE_DIR not defined, assuming document root.", E_USER_NOTICE);
			define ('SITE_DIR', dirname($_SERVER['SCRIPT_FILENAME']));
		}
		
		$root = preg_split("~/~", realpath($_SERVER['DOCUMENT_ROOT']), -1, PREG_SPLIT_NO_EMPTY);
		$actual = preg_split("~/~", realpath(dirname($_SERVER['SCRIPT_FILENAME'])), -1, PREG_SPLIT_NO_EMPTY);
		$site = preg_split("~/~", realpath(SITE_DIR), -1, PREG_SPLIT_NO_EMPTY);
		
		self::$_root = '/' . implode('/', $root);
		self::$_base = implode('/', array_diff($site, $root));
		self::$_module = implode('.', array_diff($actual, $site));
		self::$_controller = basename($_SERVER['SCRIPT_FILENAME'], '.php');
		self::$_action = (isset($_GET[self::ACTION_VAR]) ? $_GET[self::ACTION_VAR] : self::DEFAULT_ACTION);
		
		if (!self::$_module) self::$_module = self::DEFAULT_MODULE;
		if (!self::$_controller) self::$_controller = self::DEFAULT_CONTROLLER;
		
		self::$_base = (self::$_base) ? '/' . self::$_base . '/' : '/';
		
		self::$_args = $_GET;
		
		self::setDefaultPathBuilder(new FADefaultPathBuilder);
		self::setDefaultPathRenderer(new FADefaultPathRenderer);
	}

	public function __construct($route = '') {
	
		if (!isset(self::$_action)) self::init();
	
		$this->root = self::$_root;
		$this->base = self::$_base;
		$this->module = self::$_module;
		$this->controller = self::$_controller;
		$this->action = self::$_action;
		$this->args = self::$_args;
		
		$this->setBuilder(self::$defaultBuilder);
		$this->setRenderer(self::$defaultRenderer);
		
		if ($route) {
		
			$split = explode(':', $route);
			$route = array_shift($split);
			$parts = explode('.', $route);
			
			empty($parts) or $this->a(array_pop($parts));
			empty($parts) or $this->c(array_pop($parts));
			empty($parts) or $this->m(implode('.', $parts));
			
			empty($split) or $this->keep(explode(',', array_shift($split)));
		}
	}
	
	public function __call($meta, $args) {
	
		$this->meta[$meta] = array_shift($args);
		
		return $this;
	}
	
	public function __toString() {
	
		return $this->pathRenderer->renderPath($this);
	}
	
	public function setDefault() {
	
		self::$_root = $this->root;
		self::$_base = $this->base;
		self::$_module = $this->module;
		self::$_controller = $this->controller;
		self::$_action = $this->action;
		self::$_args = $this->args;
		
		return $this;
	}
	
	public function a($action = '') {
	
		return $this->action($action);
	}
	public function action($action = '') {
	
		$this->action = ($action) ? $action : self::DEFAULT_ACTION;
		
		return $this;
	}
	
	public function getAction() {
	
		return $this->action;
	}
	
	public function c($controller = '') {
	
		return $this->controller($controller);
	}
	public function controller($controller = '') {
	
		$this->controller = ($controller) ? $controller : self::DEFAULT_CONTROLLER;
		
		return $this;
	}
	
	public function getController() {
	
		return $this->controller;
	}
	
	public function m($module = '') {
	
		return $this->module($module);
	}
	public function module($module = '') {
	
		$this->module = ($module) ? $module : self::DEFAULT_MODULE;
		
		return $this;
	}
	
	public function anchor($anchor = '') {
	
		$this->anchor = $anchor;
		
		return $this;
	}
	
	public function backRef() {
	
		$this->arg(FAPath::BACKREF_VAR, path());
		
		return $this;
	}
	
	public function keep($key) {
		
		if (!is_array($key)) $key = func_get_args();
		
		foreach ($key as $keep) $this->mask[$keep] = $keep;
		
		return $this;
	}
	
	public function arg($key, $value = '') {
	
		$this->args[$key] = $value;
		$this->mask[$key] = $key;
		
		return $this;
	}
	
	public function args($args = array()) {
	
		foreach ($args as $key => $value) $this->arg($key, $value);
		
		return $this;
	}
	
	public function getBase() {
	
		return $this->base;
	}
	
	public function getArgs() {
	
		$args = array();
	
		foreach ($this->mask as $allowed) {
		
			if (isset($this->args[$allowed])) $args[$allowed] = $this->args[$allowed];
		}
		
		return $args;
	}
	
	public function getMeta($key = NULL) {
	
		if ($key !== NULL) {
		
			if (isset($this->meta[$key])) return $this->meta[$key];
		} else {
	
			return $this->meta;
		}
	}
	
	public function getModule() {
	
		return $this->module;
	}
	
	public function getAnchor() {
	
		return $this->anchor;
	}
	
	public function setMetaArray($meta) {
	
		$this->meta = array_merge($this->meta, $meta);
		
		return $this;
	}

	public function getPath() {
	
		return $this->pathBuilder->buildPath($this);
	}
	
	public function getRoot() {
		
		return $this->root;
	}
	
	public function getRoute() {
	
		return "{$this->module}.{$this->controller}.{$this->action}";
	}
	
	public function redirectTo() {
	
		self::setRedirect($this);
	}

	public function setBuilder(FAPathBuilder $builder) {
	
		$this->pathBuilder = $builder;
		
		return $this;
	}
	
	public function setRenderer(FAPathRenderer $renderer) {
	
		$this->pathRenderer = $renderer;
		
		return $this;
	}
}

class FAAnchorPathRenderer implements FAPathRenderer {

	public function renderPath(FAPath $path) {
	
		extract(array_merge(array(
			'title' => '',
			'text' => '',
			'class' => '',
			'id' => '',
			'href' => $path->getPath(),
		), array_filter($path->getMeta())));
		
		if (!$text) $text = $title;
		if (!$text) $text = $path->getRoute();
		
		return "<a class=\"$class\" href=\"$href\" id=\"$id\" title=\"$title\">$text</a>";
	}
}

interface FALinkable {

	public function getPath();
}

function path($route = '') {

	if ($route instanceof FALinkable) {
	
		return $route->getPath();
	}
	
	if ($route instanceof FAPath) {
	
		return $route;
	}
	
	return new FAPath($route);
}

function a($route, $text = NULL) {

	$path = path($route)->setRenderer(new FAAnchorPathRenderer);
	
	if ($text !== NULL) $path->text($text)->title($text);
	
	return $path;
}

function anchor($route, $text = NULL) { return a($route, $text); }

?>