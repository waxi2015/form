<?php

namespace Waxis\Form\Form\Element;

class Button extends Element {

	public $type = 'button';

	public $static = true;

	public $text = false;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($descriptor['text'])) {
			$this->text = $descriptor['text'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getText () {
		return $this->text;
	}
}