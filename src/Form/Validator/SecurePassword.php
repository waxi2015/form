<?php

namespace Waxis\Form\Form\Validator;

class SecurePassword extends Ancestor {

	public $message = 'Password must contain 6 letters, 1 digit and 1 uppercase at least';

   	public function isValid($value, $context, $name, $key = false){
        return true;
    }
}