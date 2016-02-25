<?php

namespace Waxis\Form\Form\Validator;

abstract class Ancestor {

    public $options = array();

    public $errors = array();

    public $element = null;

    public function __construct($descriptor = array(), $element = null){
        $this->element = $element;
        
        if (isset($descriptor['message'])){
            $this->message = $descriptor['message'];
        }

        if (isset($descriptor['options'])){
            $this->options = $descriptor['options'];
        }
    }

   	abstract public function isValid ($value, $context, $name, $key = false);

    public function getErrors(){
        return $this->errors;
    }
}