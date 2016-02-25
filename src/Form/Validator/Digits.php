<?php

namespace Waxis\Form\Form\Validator;

class Digits extends Ancestor {

	public $message = 'Please fill only digits.';

   	public function isValid($value, $context, $name, $key = false){
        if (ctype_digit($value)) {
            return true;
        } else {
        	$this->errors[] = $this->message;

            return false;
        }
    }
}