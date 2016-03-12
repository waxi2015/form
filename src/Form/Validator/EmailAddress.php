<?php

namespace Waxis\Form\Form\Validator;

class EmailAddress extends Ancestor {

	public $message = 'form.validators.email_msg';

   	public function isValid($value, $context, $name, $key = false){

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
        	$this->errors[] = trans($this->message);

            return false;
        }
    }
}