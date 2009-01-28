<?php

class FAForm {
	
	private $action;
	private $method;
	private $elements = array();
	private $values = array();
	private $invalid = array();
	
	public function __construct($form = array()) {
		
		$defaults = array(
			'action' => '',
			'method' => 'post',
			'elements' => array(),
		);
		
		$form = array_merge($defaults, $form);
		
		$this->action = $form['action'];
		$this->method = $form['method'];
		
		$this->addElements($form['elements']);
	}
	
	protected function addElements($elements) {
		
		foreach ($elements as $element) {
			
			$defaults = array(
				'type' => 'hidden',
				'name' => '',
				'title' => '',
				'label' => '',
				'value' => '',
				'validators' => array(),
				'options' => array(),
			);
			
			$element = array_merge($defaults, $element);
			
			if ($element['name'] && $element['type'] != 'checkbox') {
				
				$this->values[$element['name']] = $element['value'];
			}
			
			if ($element['type'] == 'date_range') {
			
				$this->addElements(array(
					array(
						'type' => 'plugin',
						'name' => $element['options']['start'],
						'validators' => $element['validators'],
					), array(
						'type' => 'plugin',
						'name' => $element['options']['end'],
						'validators' => $element['validators'],
					),
				));
				
				$element['validators'] = array();
			}
			
			$this->elements[] = $element;			
		}
	}
	
	protected function validateAgainst($value, $validators) {
		
		foreach ($validators as $validator) {
			
			switch ($validator['type']) {
				case 'regex':
					if (!preg_match($validator['value'], $value)) return false;
					break;
			}
		}
		
		return true;
	}
	
	public function buildHtml() {
		
		$html = '';
		
		$html .= "<form action=\"{$this->action}\" method=\"{$this->method}\">\n";
		$html .= "<dl>\n";
	
		foreach ($this->elements as $i => $element) {
		
			$wrap = true;
			$flip = false;
			$label = '';
			$control = '';
			$class = '';
			
			if ($element['label'])
				$label = "<label for=\"element_$i\" title=\"{$element['title']}\">{$element['label']}</label>\n";
				
			$name = $element['name'];
			
			if (in_array($name, $this->invalid)) $class = 'invalid ';

			switch ($element['type']) {
				case 'password':
					$control = "<input class=\"{$class}\" type=\"{$element['type']}\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\" value=\"{$this->getValue($name)}\" />\n";
					break;
				case 'text':
					$control = "<input class=\"{$class}watermark\" type=\"{$element['type']}\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\" value=\"{$this->getValue($name)}\" />\n";
					break;
				case 'textarea':
					$control = "<textarea class=\"{$class}\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\">{$this->getValue($name)}</textarea>\n";
					break;
				case 'richedit':
					$control = "<textarea class=\"{$class}richedit\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\">{$this->getValue($name)}</textarea>\n";
					break;
				case 'date_range':
					$start = $element['options']['start'];
					$end = $element['options']['end'];
					$class = (in_array($start, $this->invalid)) ? "invalid " : '';
					$control = "<input class=\"{$class}watermark date_range date_range_start\" type=\"text\" title=\"{$element['title']}\" name=\"{$start}\" id=\"date_range_{$i}\" value=\"{$this->getValue($start)}\" />\n";
					$class = (in_array($end, $this->invalid)) ? "invalid " : '';
					$control .= "<input class=\"{$class}watermark date_range date_range_end\" type=\"text\" title=\"{$element['title']}\" name=\"{$end}\" id=\"date_range_{$i}_end\" value=\"{$this->getValue($end)}\" />\n";
					break;
				case 'hidden':
					$wrap = false;
					$control = "<input class=\"{$class}\" type=\"{$element['type']}\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\" value=\"{$this->getValue($name)}\" />\n";
					break;
				case 'checkbox';
					$flip = true;
					$wrap = false;
					if ($this->getValue($name) == $element['value']) $checked = ' checked="yes"';
					else $checked = '';
					$control = "<input$checked class=\"{$class}\" type=\"{$element['type']}\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\" value=\"{$element['value']}\" />\n";
					break;
				case 'radio':
					$flip = true;
					$wrap = false;
					if ($this->getValue($name) == $element['value']) $checked = ' checked="yes"';
					else $checked = '';
					$control = "<input$checked class=\"{$class}\" type=\"{$element['type']}\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\" value=\"{$element['value']}\" />\n";
					break;
				case 'button':
				case 'submit':
				case 'reset':
					$class = "button";
					$wrap = false;
					$control = "<input class=\"{$class}\" type=\"{$element['type']}\" title=\"{$element['title']}\" name=\"{$name}\" id=\"element_$i\" value=\"{$element['value']}\" />\n";
					break;
				case 'cancel':
					$class = "button";
					$wrap = false;
					$control = "<input class=\"{$class}\" type=\"button\" title=\"{$element['title']}\" name=\"{$element['name']}\" id=\"element_$i\" value=\"{$element['value']}\" onClick=\"history.back();\" />\n";
					break;
				default:
					continue;
			}
			
			if ($wrap) {
				$label = "<dt>\n$label</dt>\n";
				$control = "<dd>\n$control</dd>\n";
			}
			
			if ($flip)
				$html .= $control . $label;
			else
				$html .= $label . $control;
		}
		
		$html .= "</dl>\n";
		$html .= "</form>\n";
		
		return $html;
	}
	
	public function getValue($name) {
		
		return (isset($this->values[$name])) ? $this->values[$name] : '';
	}
	
	public function getValues() {
	
		return $this->values;
	}
	
	public function setValue($name, $value) {
	
		foreach ($this->elements as $element) {
			
			if ($element['name'] != $name) continue;
			
			if ($this->validateAgainst($value, $element['validators'])) {
			
				$this->values[$name] = $value;
			}
		}
	}
	
	public function isValid($values) {
	
		$this->invalid = array();
		
		foreach ($this->elements as $i => $element) {
		
			if ($element['name']) {
			
				$name = $element['name'];
				$value = (isset($values[$name])) ? $values[$name] : $this->getValue($name);
				
				if (!$this->validateAgainst($value, $element['validators'])) {
					$this->invalid[] = $name;
					$this->values[$name] = '';
				} else {
					$this->values[$name] = $value;
				}
			}
		}
		
		return empty($this->invalid);
	}
	
	public function populate($values) {
	
		$this->isValid($values);
	}
	
	public function __toString() {
		
		return $this->buildHtml();
	}
	
	public function __get($name) {
	
		if (isset($this->values[$name]))
			return $this->values[$name];
	}
	
	public function __isset($name) {
		
		return isset($this->values[$name]);
	}
	
	public function __set($name, $value) {
		
		$this->setValue($name, $value);
	}
}

?>