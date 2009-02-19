<?php

require_once 'path.php';

class FARouteException extends Exception {}

class FARouter implements FAPathBuilder {

	private static $instance;

	public static function getInstance() {
	
		if (!self::$instance) self::$instance = new FARouter;
		
		return self::$instance;
	}
	
	public static function addRoute($route, $options) {
	
		self::getInstance()->_addRoute($route, $options);
	}
	
	public static function init($routes = array()) {
	
		foreach ($routes as $route => $options) self::addRoute($route, $options);

		FAPath::init();
		FAPath::setDefaultPathBuilder(self::$instance);
	}
	
	public static function dispatch($request_uri) {
	
		$request_uri = str_replace(substr(path()->getBase(), 0, -1), '', $request_uri);
		
		$path = self::getInstance()->match($request_uri)
			->setBuilder(new FAFilenamePathBuilder)
			->setDefault();
			
		FARequest::instance()->setArray($path->getMeta());
		
		require_once $path->getPath();
	}
	
	protected $routes = array();
	protected $options = array();
	
	protected function _addRoute($route, $options) {

		$options = array_merge(array(
			'module' => FAPath::DEFAULT_MODULE,
			'controller' => FAPath::DEFAULT_CONTROLLER,
			'action' => FAPath::DEFAULT_ACTION,
		), $options);
		
		$options['regex'] = preg_replace('~/:([a-z]+)(\([^\)]+\))(?=/)~', '/(?<\1>\2)', $route);
		$options['regex'] = '~' . preg_replace('~/:([a-z]+)(?=/)~', '/(?<\1>.+?)', $options['regex']) . '~';
		$options['mask'] = preg_replace('~/:([a-z]+)(\([^\)]+\))?(?=/)~', '/${\1}', $route);
		
		$options['path'] = $options['module'] . '.' . $options['controller'] . '.' . $options['action'];
		$options['route'] = $route;
		
		$this->options[$options['path']] = $options;		
		$this->routes[$route] = $options;
	}
	
	public function buildPath(FAPath $path) {
	
		if (isset($this->options[$path->getRoute()])) {
		
			$mask = $this->options[$path->getRoute()]['mask'];
			$ret = substr($path->getBase(), 0, -1);
			
			$ret .= call_user_func(
				create_function('$o', "extract(\$o); return \"{$mask}\";"),
				$path->getMeta());
				
			if (!preg_match($this->options[$path->getRoute()]['regex'], $ret))
				trigger_error("Missing or invalid route parameters");
			
			return $ret;
		}
		
		$parts = array();
		
		if ($path->getModule() != FAPath::DEFAULT_MODULE) $parts = explode('.', $path->getModule());
		if ($path->getController() != FAPath::DEFAULT_CONTROLLER) $parts[] = $path->getController();
		if ($path->getAction() != FAPath::DEFAULT_ACTION) $parts[] = $path->getAction();
		
		$parts = (empty($parts)) ? '' : implode('/', $parts) . '/';
		
		return $path->getBase() . $parts;
	}
	
	public function match($path) {
	
		foreach ($this->routes as $route => $options) {
		
			if (preg_match($options['regex'], $path, $matches)) {
			
				unset($matches[0]);
				
				$options = array_merge($options, array_unique($matches));
				
				$path = $options['module'] . '.' . $options['controller'] . '.' . $options['action'];
				
				return path($path)
					->route($route)
					->setMetaArray(array_merge($options, $matches));
			}
		}
		
		$parts = preg_split('~/~', $path, -1, PREG_SPLIT_NO_EMPTY);
		
		if (count($parts) == 1) return path($parts[0] . '.');
		else return path(implode('.', $parts));
	}
}

?>