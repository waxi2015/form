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
            if (empty($id)) {
                $query = \DB::table($table)->where($field, $value);
                $result = !(bool) $query->count();
            } else {
                $record = to_array(\DB::table($table)->find($id));
                if ($record[$field] == $value) {
                    $result = true;
                } else {
                    $query = \DB::table($table)
                                ->where($field, $value)
                                ->where('id', '!=', $id);
                    $result = !(bool) $query->count();
                }
            }

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