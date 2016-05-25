<?php

namespace Waxis\Form\Form\Validator;

class Digits extends Ancestor {

	public $message = 'form.validators.digits_msg';

   	public function isValid($value, $context, $name, $key = false){
        if (ctype_digit($value)) {
            return true;
        } else {
        	$this->errors[] = trans($this->message);

            return false;
        }
    }
}