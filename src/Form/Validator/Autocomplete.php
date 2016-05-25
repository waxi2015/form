<?php

namespace Waxis\Form\Form\Validator;

class Autocomplete extends Ancestor {

	public $message = 'form.validators.autocomplete_msg';

   	public function isValid($value, $context, $name, $key = false){
        $valid = true;

        $element = $this->element;

        $valueArray = $element->getValueArray();
        
        if (!isset($valueArray['new']) || (isset($valueArray['new']) && ($valueArray['new'] == '' || $valueArray['new'] == 'true'))) {
            $valid = false;
        } else {
           // if ($element->getMode() == 'remote' && $element->getSourceType() == 'function') {
            // check if the selected value can actually be a value
            // eg. if there's a parametric source param which has been changed
            // after selecting the value the autocomplete will remember the old value
            // which would may not be an option if the suggestion search ran again
            // parametric source params can only occur at remote / functions
                if (!$element->isValueExist()) {
                    $valid = false;
                }
            //}
        }

        if ($valid) {
            return true;
        } else {
        	$this->errors[] = trans($this->message);

            return false;
        }
    }
}