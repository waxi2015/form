<?php

namespace Waxis\Form\Form\Validator;

class Identical extends Ancestor {

	public $message = 'form.validators.identical_msg';

   	public function isValid($value, $context, $name, $key = false){
        if ((!$key && $value == $context[$this->options['field']]) || ($key !== false && $key !== null && $value == $context[$this->options['field']][$key])) {
            return true;
        } else {
        	$this->errors[] = trans($this->message);

            return false;
        }
    }
}