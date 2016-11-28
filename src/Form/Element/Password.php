<?php

namespace Waxis\Form\Form\Element;

class Password extends Element {

	public $type = 'password';

	public $canShow = false;

	public $showIcon = 'fa fa-eye';

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['canShow'])) {
			$this->canShow = $this->descriptor['canShow'];
		}
		
		if (isset($this->descriptor['showIcon'])) {
			$this->showIcon = $this->descriptor['showIcon'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function canShowPassword () {
		return $this->canShow;
	}

	public function getShowPasswordIcon () {
		return $this->showIcon;
	}

	public function getLoadedValue () {
		if ($this->load !== false) {
			return $this->getValue();
		}

		return null;
	}
}