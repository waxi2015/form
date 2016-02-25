<?php

namespace Waxis\Form\Form\Validator;

class Uri extends Ancestor {

	public $message = 'Wrong url.';

   	public function isValid($value, $context, $name, $key = false){
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        	$this->errors[] = $this->message;

            return false;
        } else {
            return true;
        }
    }
}