<?php

namespace Waxis\Form\Form\Element;

class Anchor extends Element {

	public $type = 'anchor';

	public $static = true;

	public $text = false;
	
	public $href = false;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($descriptor['text'])) {
			$this->text = $descriptor['text'];
		}

		if (isset($descriptor['href'])) {
			$this->href = $descriptor['href'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getText () {
		return $this->text;
	}

	public function getHref () {
		return $this->href;
	}
}