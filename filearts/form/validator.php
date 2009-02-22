<?php

class FAFormValidator {

	protected $errors = array();
	
	protected function addError(FAFormControl $control, $message) {
	
		$control->addClass('fa-form-error');
		$control->setValue('');
		
		$this->errors[$control->getName()][] = $message;
	}
	
	public function getErrors($name = NULL) {
	
		if ($name !== NULL) return (isset($this->errors[$name])) ? $this->errors[$name] : array();
	
		return $this->errors;
	}
	
	public function validate($values) {
	}

	public function validateContainer(FAFormContainer $container, $values) {
	
		foreach ($container->getChildren() as $child) {
		
			// Recurse for containers
			if ($child instanceof FAFormContainer) {
			
				$this->validateContainer($child, $values);
			
			} else if ($child instanceof FAFormControl) {
			
				$this->validateControl($child, $values);
			} 
		}
		
		return empty($this->errors);
	}
	
	public function validateControl(FAFormControl $control, $values) {
	
		$options = $control->getOptions();
		$name = $control->getName();
		
		if (isset($values[$name]))
			$control->setValue($values[$name]);
		
		if ($options['required']) {
		
			if (!isset($values[$name]) || !$values[$name]) {
			
				$this->addError(
					$control,
					ucfirst(str_replace('_', ' ', $name)) . " is a required field."
				);
			}
		}
	}
}

?>