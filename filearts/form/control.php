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
		
		return <<<EOF
<div class="fa-form-element fa-form-element-inline $class">
<input type="$type" value="$value" />
</div>

EOF;
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
		
		return <<<EOF
<div class="fa-form-element fa-form-element-inline $class">
<input type="button" value="$value" onclick="history.go(-1)" />
</div>

EOF;
	}
}

class FAGenericInput extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'id' => self::generateId(),
			'name' => '',
			'value' => '',
		);
	}
	
	public function __toString() {
		
		extract($this->getOptions());
		
		return <<<EOF
<div class="fa-form-element $class">
<label for="$id">$label</label>
<input type="$type" name="$name" id="$id" value="$value" />
<span class="fa-form-hint">$hint</span>
</div>

EOF;
	}
}

class FATextArea extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'id' => self::generateId(),
			'name' => '',
			'value' => '',
		);
	}
	
	public function __toString() {
		
		extract($this->getOptions());
		
		return <<<EOF
<div class="fa-form-element $class">
<label for="$id">$label</label>
<textarea name="$name" id="$id">$value</textarea>
</div>

EOF;
	}
}

class FAHiddenInput extends FAFormControl {

	public function getDefaultOptions() {
	
		return array(
			'id' => self::generateId(),
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