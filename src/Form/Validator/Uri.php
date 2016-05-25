<?php

namespace Waxis\Form\Form\Validator;

class Uri extends Ancestor {

	public $message = 'form.validators.uri_msg';

   	public function isValid($value, $context, $name, $key = false){
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        	$this->errors[] = trans($this->message);

            return false;
        } else {
            return true;
        }
    }
}