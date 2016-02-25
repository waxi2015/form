<?php

namespace Waxis\Form\Form\Validator;

class EmailAddress extends Ancestor {

	public $message = 'The email address is not valid';

   	public function isValid($value, $context, $name, $key = false){

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
        	$this->errors[] = $this->message;

            return false;
        }
    }
}