<?php

namespace Waxis\Form\Form\Validator;

class Remote extends Ancestor {

	public $message = 'form.validators.remote_msg';

	public function isValid($value, $context, $name, $key = false){
        $class = getValue($this->options, 'class');
        $method = getValue($this->options, 'method');
        $table = getValue($this->options, 'table');
        $field = getValue($this->options, 'field');
        $id = getValue($context, 'id');

        if (!empty($id) && !ctype_digit($id)) {
            $id = decode($id);
        }

        if ($table && $field) {
            $query = \DB::table($table)->where($field, $value);

            if (!empty($id)) {
                $query->where('id', '!=', $id);
            }

            $result = !(bool) $query->count();
        } else {
            $result = $class::$method($value, $id);
        }

        if ($result === true) {
        	return true;
        } else {
            $this->errors[] = trans($this->message);

        	return false;
        }
    }
}