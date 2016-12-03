<?php

namespace Waxis\Form\Form\Element;

class Hidden extends Element {

	public $type = 'hidden';

	public $secret = false;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['secret'])) {
			$this->secret = $this->descriptor['secret'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function isSecret () {
		return $this->secret;
	}
}