<?php

namespace Waxis\Form\Form\Validator;

class Regexp extends Ancestor {

	public $message = 'form.validators.regexp_msg';

   	public function isValid($value, $context, $name, $key = false){
        if (preg_match("/".str_replace("\\\\", "\\", $this->options['regexp']."/"), $value)) {
            return true;
        } else {
        	$this->errors[] = trans($this->message);

            return false;
        }
    }
}