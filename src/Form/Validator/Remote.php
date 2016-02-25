<?php

namespace Waxis\Form\Form\Validator;

class Remote extends Ancestor {

	public $error = 'Field is incorrect.';

	public function isValid($value, $context, $name, $key = false){
        $class = $this->options['class'];
        $method = $this->options['method'];

        $result = $class::$method($value);

        if ($result === true) {
        	return true;
        } else {
        	$this->errors[] = $this->error;

        	return false;
        }
    }
}