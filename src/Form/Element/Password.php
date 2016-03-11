<?php

namespace Waxis\Form\Form\Element;

class Password extends Element {

	public $type = 'password';

	public function getLoadedValue () {
		if ($this->load !== false) {
			return $this->getValue();
		}

		return null;
	}
}