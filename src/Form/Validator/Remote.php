<?php

namespace Waxis\Form\Form\Validator;

class Remote extends Ancestor {

	public $error = 'Field is incorrect.';

	public function isValid($value, $context, $name, $key = false){
        $class = getValue($this->options, 'class');
        $method = getValue($this->options, 'method');
        $table = getValue($this->options, 'table');
        $field = getValue($this->options, 'field');

        if ($table && $field) {
            $result = !(bool) \DB::table($table)->where($field, $value)->count();
        } else {
            $result = $class::$method($value);
        }

        if ($result === true) {
        	return true;
        } else {
        	$this->errors[] = $this->error;

        	return false;
        }
    }
}