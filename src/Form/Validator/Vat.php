<?php

namespace Waxis\Form\Form\Validator;

class Vat extends Ancestor {

	public $message = 'form.validators.vat_msg';

   	public function isValid($value, $context, $name, $key = false){
        return true;
    }
}