<?php

namespace Waxis\Form\Form\Element;

class Description extends Element {

	public $type = 'description';

	public $description = null;

	public $static = true;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($descriptor['description'])) {
			$this->description = $descriptor['description'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getDescription () {
		return $this->description;
	}
}