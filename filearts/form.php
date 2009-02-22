<?php

require_once 'form/control.php';
require_once 'form/validator.php';

abstract class FAFormElement {

	abstract public function __toString();
}

abstract class FAFormContainer extends FAFormElement {

	protected $children = array();
	
	public function __toString() {
	
		$ret = $this->getHeader();
		
		foreach ($this->children as $child) $ret .= $child;
		
		return $ret . $this->getFooter();
	}
	
	public function addChild(FAFormElement $child) {
	
		if ($this->isEmpty()) array_pop($this->children);
	
		$this->children[] = $child;
	}
	
	public function getHeader() {
		
		return '';
	}
	
	public function getFooter() {
	
		return '';
	}
	
	public function getChildren() {
	
		return $this->children;
	}
	
	public function getLastChild() {
	
		if (empty($this->children)) throw new FAException("Container has no children");
		
		return $this->children[count($this->children) - 1];
	}

	public function isEmpty() {
	
		if (empty($this->children)) return true;
	
		foreach ($this->children as $child)
			if ($child instanceof FAFormContainer && $child->isEmpty()) return true;
		
		return false;
	}
}

class FAFormPage extends FAFormContainer {

	protected $title;
	
	public function __construct($title = '') {
	
		$this->title = $title;
		
		$this->addChild(new FAFormSection);
	}
}

class FAFormSection extends FAFormContainer {

	protected $title;
	
	public function __construct($title = '') {
	
		$this->title = $title;
	}
	
	public function getHeader() {
	
		$ret = "<fieldset>\n";
		
		if ($this->title) $ret .= "<legend>{$this->title}</legend>\n";
		
		return $ret;
	}
	
	public function getFooter() {
		
		return "</fieldset>\n";
	}
}

class FAForm extends FAFormContainer {

	// CLASS METHODS //

	private static $forms = array();
	private static $controlTypes = array();
	
	private static $defaultFormValidator;
	
	public static function createControl($control, $options) {
	
		$control = strtolower($control);
	
		if (!isset(self::$controlTypes[$control])) throw new FAException("No such control: $control");
		
		$class = self::$controlTypes[$control];
		$control = new $class($options);
		
		if ($control->isRequired()) $control->addClass('fa-form-element-required');
		
		return $control;
	}

	public static function getForm($name) {
		
		if (!isset(self::$forms[$name])) {
			
			$class = $name;
			
			if (!class_exists($class)) throw new FAException("No such form: $name");
			
			self::$forms[$name] = new $class;
			self::$forms[$name]->setUp();
		}
		
		return self::$forms[$name];
	}
	
	public static function setControlClass($control, $class) {
	
		$control = strtolower($control);
	
		if (!class_exists($class)) throw new FAException("No such control class: $class");
		
		self::$controlTypes[$control] = $class;
	}
	
	private static function init() {
	
		self::$defaultFormValidator = new FAFormValidator;
	}
	
	// INSTANCE METHODS //
	
	protected $formValidators = array();
	
	protected $action = '';
	protected $method = 'post';
	
	protected $values = array();
	
	public function __construct() {
	
		if (!self::$defaultFormValidator) self::init();
	
		$this->addChild(new FAFormPage);
		$this->addValidator(self::$defaultFormValidator);
	}
	
	public function __call($control, $args) {
	
		if (empty($args)) throw new FAException("Missing control name");
		
		array_push($args, array());
		
		// Required fields for ALL controls
		$options = array_merge(array(
			'name' => array_shift($args),
			'type' => $control,
			'hint' => '',
			'title' => '',
			'class' => '',
			'required' => false,
			'validators' => array(),
			'filters' => array(),
		), array_shift($args));
		
		// Last page, last section
		$this->getLastChild()->getLastChild()
			->addChild(self::createControl($control, $options));
		
		return $this;
	}
	
	public function addValidator(FAFormValidator $validator) {
	
		$this->formValidators[] = $validator;
		
		return $this;
	}
	
	public function getCurrentPage() {
	
		// TODO: This assumes only ONE page
		return $this->getLastChild();
	}
	
	public function getErrors() {
	
		$errors = array();
		
		foreach ($this->formValidators as $validator)
			$errors += $validator->getErrors();
			
		return $errors;
	}
	
	public function getValues() {
	
		return $this->values;
	}
	
	public function getHeader() {
	
		return "<form action=\"{$this->action}\" class=\"fa-form\" method=\"{$this->method}\">\n";
	}
	
	public function getFooter() {
	
		return "</form>\n";
	}
	
	public function isValid($values) {
	
		$ret = true;
		$this->values = array();
	
		foreach ($this->formValidators as $validator) {
			
			$ret &= $validator->validateContainer($this->getCurrentPage(), $values);
		}
		
		$errors = $this->getErrors();
		
		foreach ($errors as $key => $errors) unset($values[$key]);
		
		$this->values = $values;
		
		return $ret;
	}

	public function action($path) {
	
		$this->action = $path;
		
		return $this;
	}
	
	public function method($method = 'POST') {
	
		$this->method = $method;
	
		return $this;
	}
	
	public function startPage($title = '') {
	
		$this->addChild(new FAFormPage($title));
		
		return $this;
	}
	
	public function startSection($title = '') {
	
		$this->getLastChild()->addChild(new FAFormSection($title));
		
		return $this;
	}
}

function form($name) {

	return FAForm::getForm(ucfirst($name) . 'Form');
}

?>