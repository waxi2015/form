<?php

namespace Waxis\Form\Form\Validator;

class Numeric extends Ancestor {

	public $message = 'form.validators.numeric_msg';

   	public function isValid($value, $context, $name, $key = false){
   		$decimal = $this->options['decimalSeparator'];

   		if ($decimal == ',') {
   			$reqex = "[\-\+]?[0-9]*(\,[0-9]+)?";
   		} else {
   			$reqex = "[\-\+]?[0-9]*(\.[0-9]+)?";
   		}

   		if (preg_match($regex, $value)) {
   			return true;
   		} else {
        	$this->errors[] = trans($this->message);

            return false;
        }
    }
}