<?php

namespace Waxis\Form\Form\Validator;

class Identical extends Ancestor {

	public $message = 'The fields are not identical.';

   	public function isValid($value, $context, $name, $key = false){
        if ((!$key && $value == $context[$this->options['field']]) || ($key !== false && $value == $context[$this->options['field']][$key])) {
            return true;
        } else {
        	$this->errors[] = $this->message;

            return false;
        }
    }
}