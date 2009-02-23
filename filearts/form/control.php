<?php

abstract class FAFormControl extends FAFormElement {

	private static $id = 0;
	
	public static function generateId() {
		
		return 'fa_control_' . self::$id++;
	}
	
	private $options = array();
	
	public function __construct($options) {
	
		$this->options = array_merge($this->getDefaultOptions(), $options);
	}
	
	public function addClass($class) {
	
		$this->options['class'] .= ' ' . $class;
	}
	
	public function getDefaultOptions() {
	
		return array();
	}
	
	public function getName() {
	
		return $this->options['name'];
	}
	
	public function getOptions() {
	
		return $this->options;
	}
	
	public function getValue() {
	
		return $this->options['value'];
	}
	
	public function isRequired() {
	
		return $this->options['required'];
	}
	
	public function setValue($value) {
	
		$this->options['value'] = $value;
	}
}

class FAGenericBox extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'id' => self::generateId(),
			'name' => '',
			'label' => '',
			'values' => array(),
		);
	}
	
	public function __toString() {
		
		extract($this->getOptions());
		
		$braces = ($type == 'checkbox') ? '[]' : '';
		
		$ret = "<div class=\"fa-form-element fa-form-element-stacked $class\">\n";
		
		if (empty($values)) {
		
			$checked = ($this->getValue()) ? 'checked="checked" ' : '';
			
			$ret .= "<label>\n";
			$ret .= "<input type=\"$type\" name=\"$name\" value=\"true\" $checked/>\n";
			$ret .= "$label\n";
			$ret .= "</label>\n";
		} elseif ($label) {
		
			$ret .= "<label>$label</label>\n";
		}
		
		foreach ($values as $value => $label) {
			
			$ret .= "<label>\n";
			$ret .= "<input type=\"$type\" name=\"$name$braces\" value=\"$value\" />\n";
			$ret .= "$label\n";
			$ret .= "</label>\n";
		}
		
		return $ret . "</div>\n";
	}
}

class FAGenericButton extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'name' => '',
			'value' => '',
		);
	}
	
	public function __toString() {
	
		$options = $this->getOptions();
		
		if (!$options['value']) $options['value'] = $options['name'];
		
		extract($options);
		
		$ret = "<div class=\"fa-form-element fa-form-element-inline $class\">\n";
		$ret .= "<input type=\"$type\" value=\"$value\" />\n";
		$ret .= "</div>\n";
		
		return $ret;
	}
}


class FACancelButton extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'name' => '',
			'value' => '',
		);
	}
	
	public function __toString() {
	
		$options = $this->getOptions();
		
		if (!$options['value']) $options['value'] = $options['name'];
		
		extract($options);
		
		$ret = "<div class=\"fa-form-element fa-form-element-inline $class\">\n";
		$ret .= "<input type=\"button\" value=\"$value\" onclick=\"history.go(-1)\" />\n";
		$ret .= "</div>\n";
		
		return $ret;
	}
}

class FAGenericInput extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'id' => self::generateId(),
			'label' => '',
			'name' => '',
			'value' => '',
		);
	}
	
	public function __toString() {
		
		extract($this->getOptions());
		
		$ret = "<div class=\"fa-form-element $class\">\n";
		
		if ($label) $ret .= "<label for=\"$id\">$label</label>\n";
		
		$ret .= "<input type=\"$type\" name=\"$name\" id=\"$id\" value=\"$value\" />\n";
		$ret .= "<span class=\"fa-form-hint\">$hint</span>\n";
		$ret .= "</div>\n";
		
		return $ret;
	}
}

class FATextArea extends FAGenericInput {

	public function __toString() {
		
		extract($this->getOptions());
		
		$ret = "<div class=\"fa-form-element $class\">\n";
		
		if ($label) $ret .= "<label for=\"$id\">$label</label>\n";
		
		$ret .= "<textarea name=\"$name\" id=\"$id\">$value</textarea>\n";
		$ret .= "<span class=\"fa-form-hint\">$hint</span>\n";
		$ret .= "</div>\n";
		
		return $ret;
	}
}

class FAHiddenInput extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'name' => '',
			'value' => '',
		);
	}
	
	public function __toString() {
		
		extract($this->getOptions());
		
		return "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
	}
}

FAForm::setControlClass('text', 'FAGenericInput');
FAForm::setControlClass('password', 'FAGenericInput');
FAForm::setControlClass('checkbox', 'FAGenericBox');
FAForm::setControlClass('radio', 'FAGenericBox');
FAForm::setControlClass('submit', 'FAGenericButton');
FAForm::setControlClass('cancel', 'FACancelButton');
FAForm::setControlClass('hidden', 'FAHiddenInput');
FAForm::setControlClass('textarea', 'FATextArea');

?>