<?php

namespace Waxis\Form\Form\Element;

class Submit extends Button {
	
	public $type = 'submit';

	public $static = true;

	public $canLoad = false;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($this->descriptor['canLoad'])) {
			$this->canLoad = $this->descriptor['canLoad'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function canLoad () {
		return $this->canLoad;
	}

}