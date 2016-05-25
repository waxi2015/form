<?php

namespace Waxis\Form\Form\Element;

class Html extends Element {

	public $type = 'html';

	public $content = null;

	public $plain = false;

	public $static = true;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($descriptor['content'])) {
			$this->content = $descriptor['content'];
		}

		if (isset($descriptor['plain'])) {
			$this->plain = $descriptor['plain'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getContent () {
		return replacePatternToClassAsset('/(%[a-zA-Z0-9]+\({0,1}\){0,1})/', $this->content, $this);
	}

	public function isPlain () {
		return $this->plain;
	}
}