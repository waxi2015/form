<?php

namespace Waxis\Form\Form\Element;

class Swap extends Checkbox {

	public $type = 'swap';

	public $size = 'normal';

	public $onText = 'ON';

	public $offText = 'OFF';

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['value'])) {
			$this->value = $this->descriptor['value'];
		}
		
		if (isset($this->descriptor['size'])) {
			$this->size = $this->descriptor['size'];
		}
		
		if (isset($this->descriptor['onText'])) {
			$this->onText = $this->descriptor['onText'];
		}
		
		if (isset($this->descriptor['offText'])) {
			$this->offText = $this->descriptor['offText'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getAdditionalAttributes ($value = null) {
		return  $this->getChecked() . ' ' . $this->getDisabled() . ' ' . $this->getRel();
	}

	public function getLabelFor () {
		return null;
	}
}