<?php

namespace Waxis\Form\Form\Element;

class Text extends Element {

	public $type = 'text';

	public $translate = true;

	public function getValue ($allowDefault = false, $language = null) {
		if (!$this->emptied) {
			$this->setValue($language);
		}

		if ($this->hasCondition()) {
			$conditionsTrue = true;
			foreach ($this->condition as $field => $requiredValue) {
				if (!$this->isConditionTrue($field, $requiredValue)) {
					$conditionsTrue = false;
				}
			}

			if ($conditionsTrue) {
				return str_replace('"','&quot;',$this->value);		
			} else {
				return false;
			}

		} else {
			return str_replace('"','&quot;',$this->value);
		}
	}
}