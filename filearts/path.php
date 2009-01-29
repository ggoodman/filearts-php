<?php

/**
 * A class representing a path
 */
class FAPath {

	private $base;
	private $module;
	private $controller;
	private $action;
	
	private $anchor;
	
	private $args = array();
	private $mask = array();
	
	static private $abs = FALSE;
	static private $based = FALSE;
	
	static private $_base;
	static private $_module;
	static private $_controller;
	static private $_action;
	static private $_args;
	
	/**
	 * Create the base path object and initialize it
	 *
	 * @access	private
	 */
	public function __construct() {
	
		if (!isset(self::$_base)) self::init();
	
		$this->base = self::$_base;
		$this->module = self::$_module;
		$this->controller = self::$_controller;
		$this->action = self::$_action;
		$this->args = self::$_args;
	}
	
	/**
	 * Create the path string
	 *
	 * @return	string	Returns the path as a string
	 */
	public function __toString() {
		$args = array();
		
		foreach ($this->mask as $allowed) {

			isset($this->args[$allowed]) and $args[$allowed] = $this->args[$allowed];
		}
		
		if (isset($args['a'])) unset($args['a']);
		
		$path = $this->module . '/';
		
		if ($this->controller != 'index' || $this->action != 'index') $path .= $this->controller . '.php';
		if ($this->action != 'index') $args['a'] = $this->action;
		if (!empty($args)) $path .= '?' . http_build_query($args, '');
		if ($this->anchor) $path .= '#' . $this->anchor;
		
		if (!self::$based) $path = '/' . $this->base . '/' . $path;

		$path = preg_replace('~^/|/+?(?=/)~', '', $path); //Strip excess '/'
		
		if (self::$abs) $path = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $path;
	
		return $path;
	}
	
	/**
	 * Tell FAPath whether to use absolute paths or not
	 *
	 * @static
	 * @param	bool	Use absolute paths
	 */
	static public function absolute($abs = FALSE) {
	
		self::$abs = $abs;
	}
	
	static public function based($based = FALSE) {
	
		self::$based = $based;
	}
	
	/**
	 * Initialize the path object and determine actual path
	 *
	 * @access	private
	 * @static
	 */
	private static function init() {
		$actual = preg_split("~/~", dirname($_SERVER['PHP_SELF']), -1, PREG_SPLIT_NO_EMPTY);
		$framework = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
		$base = '/';
		$module = '';
		
		while (!empty($actual)) {
			
			if (strcasecmp(end($framework), end($actual)) == 0) {
			
				while (strcasecmp(end($framework), end($actual)) == 0) {
					
					$base = '/' . array_pop($actual) . $base;
					array_pop($framework);
				}
				
				break;
			}
			
			$module =  '/' . array_pop($actual) . $module;
		}
		
		self::$_base = $base;
		self::$_module = $module;
		self::$_controller = basename($_SERVER['PHP_SELF'], '.php');
		self::$_action = (isset($_GET['a']) ? $_GET['a'] : 'index');
		
		self::$_args = $_GET;
	}
	
	/**
	 * Get the current action
	 *
	 * @return	string	The current action
	 */
	public function getAction() {
	
		return $this->action;
	}
	
	/**
	 * Get the current arguments
	 *
	 * @return	array	The current arguments
	 */
	public function getArgs() {
		
		return $this->args;
	}
	
	/**
	 * Get the current base url
	 *
	 * @return	string	The current base url
	 */
	public function getBase() {
	
		return $this->base;
	}
	
	/**
	 * Get the current controller
	 *
	 * @return	string	The current controller
	 */
	public function getController() {
	
		return $this->controller;
	}
	
	/**
	 * Get the current module
	 *
	 * @return	string	The current module
	 */
	public function getModule() {
	
		return $this->module;
	}
	
	/**
	 * Chainable function to set arguments
	 *
	 * @return	FAPath	The path object itself
	 */
	public function args($args) {
	
		$this->keep(array_keys($args));
		$this->args = array_merge($this->args, $args);
		
		return $this;
	}
	
	/**
	 * Chainable function to remove mask arguments
	 *
	 * Only arguments that are present in the mask will be kept in the final url.
	 *
	 * @return	FAPath	The path object itself
	 */
	public function discard($mask) {
	
		$mask = (is_array($mask)) ? $mask : func_get_args();
		
		$this->mask = array_diff($this->mask, $mask);
		
		return $this;
	}
	
	/**
	 * Chainable function to add mask arguments
	 *
	 * Only arguments that are present in the mask will be kept in the final url.
	 *
	 * @return	FAPath	The path object itself
	 */
	public function keep($mask) {
	
		$mask = (is_array($mask)) ? $mask : func_get_args();
		
		$this->mask = array_merge($this->mask, $mask);
		
		return $this;
	}
	
	/**
	 * Alias of FAPath::module()
	 *
	 * @see	module()
	 */
	public function m($module) {
	
		return $this->module($module);
	}
	
	/**
	 * Chainable function to set the module
	 *
	 * @return	FAPath	The path object itself
	 */
	public function module($module) {
	
		$this->module = $module;
		
		return $this;
	}
	
	/**
	 * Alias of FAPath::controller()
	 *
	 * @see	controller()
	 */
	public function c($controller) {
	
		return $this->controller($controller);
	}
	
	/**
	 * Chainable function to set the controller
	 *
	 * @return	FAPath	The path object itself
	 */
	public function controller($controller) {
	
		$this->controller = ($controller) ? $controller : 'index';
		
		return $this;
	}
	
	/**
	 * Alias of FAPath::action()
	 *
	 * @see	action()
	 */
	public function a($action) {
	
		return $this->action($action);
	}
	
	/**
	 * Chainable function to set the action
	 *
	 * @return	FAPath	The path object itself
	 */
	public function action($action) {
	
		$this->action = ($action) ? $action : 'index';
		
		return $this;
	}
	
	public function anchor($anchor) {
	
		$this->anchor = $anchor;
		
		return $this;
	}
}

class FAAnchor extends FAPath {

	protected $text;
	protected $title;
	protected $classes = array();
	protected $id = '';

	public function __toString() {
	
		$url = parent::__toString();
	
		isset($this->text) or $this->text = $url;
	
		$a = '<a href="' . $url .'"';
		
		isset($this->title) and $a .= ' title="' . $this->title . '"';
		isset($this->id) and $a .= ' id="' . $this->id . '"';
		empty($this->classes) or $a .= ' class="' . implode(' ', $this->classes) . '"';
		
		$a .= '>';
		$a .= $this->text;
		$a .= '</a>';
		
		return $a;
	}
	
	public function addClass($class) {
		
		$this->classes[] = $class;
		
		return $this;
	}
	
	public function id($id) {
	
		$this->id = $id;
		
		return $this;
	}
	
	public function text($text) {
	
		$this->text = $text;
		
		return $this;
	}
	
	public function title($title) {
	
		$this->title = $title;
		
		return $this;
	}
}

/**
 * Create and return a path object
 *
 * Pass in a path in the form of [module.][controller.]action
 * If you do not specify a module, controller or action, the current module, controller and/or
 * action is assumed.
 *
 * To obtain the path itself, you need to print the path object or explicitly call its __toString
 * method.
 *
 * <code>
 * print path('sub.module.controller.action');		// sub/module/controller.php?a=action
 * print path('module.controller.action');			// module/controller.php?a=action
 * print path('controller.action');					// controller.php?a=action
 * print path('action');							// current.php?a=action
 * print path();									// current.php
 * print path()
 *	->args(array('id' => 42));						// current.php?id=42
 * print path()
 *	->args(array('id' => 42, 's' => 'search for'));	// current.php?id=42&s=search+for
 * print path()
 *	->args(array('a' => 'A', 's' => 'search for'))
 *	->discard('b');									// current.php?id=42
 * </code>
 *
 * @return	FAPath	The chainable path object
 */
function path($parts = '', $path = NULL) {

	$path or $path = new FAPath;
	
	if (!$parts) return $path;
	
	$split = explode(':', $parts);
	$parts = explode('.', array_shift($split));
	
	empty($parts) or $path->a(array_pop($parts));
	empty($parts) or $path->c(array_pop($parts));
	empty($parts) or $path->m(implode('/', $parts));
	
	empty($split) or $path->keep(explode(',', array_shift($split)));
	
	return $path;
}

function anchor($parts = '', $text = NULL) {

	$path = path($parts, new FAAnchor);
	
	is_null($text) or $path->text($text);
	
	return $path;
}

?>