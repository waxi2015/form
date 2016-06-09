<?php

namespace Waxis\Form\Form\Element;

class Textarea extends Element {

	public $type = 'textarea';

	public $translate = true;

	public $autogrow = true;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['autogrow'])) {
			$this->autogrow = $this->descriptor['autogrow'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function isAutogrow () {
		return $this->autogrow;
	}
}