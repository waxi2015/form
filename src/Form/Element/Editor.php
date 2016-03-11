<?php

namespace Waxis\Form\Form\Element;

class Editor extends Textarea {

	public $type = 'editor';

	public $translate = true;

	public $css = 'content';

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {

		if (isset($descriptor['css'])) {
			$this->css = $descriptor['css'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getCss()
	{
		return $this->css;
	}
}