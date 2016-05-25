<?php

namespace Waxis\Form\Form\Element;

class Structure extends Ancestor {

	public $class = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = false) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if ($constructParams !== null) {
			foreach ($constructParams as $param => $value) {
				$this->$param = $value;
			}
		}

		if (isset($descriptor['class'])) {
			$this->class = $descriptor['class'];
		}

		if (isset($descriptor['templateDirectory'])) {
			$this->templateDirectory = $descriptor['templateDirectory'];
		}

		if (isset($descriptor['template'])) {
			$this->template = $descriptor['template'];
		}

		if (isset($descriptor['vars'])) {
			$this->vars = $descriptor['vars'];
		}

		if (isset($descriptor['description'])) {
			$this->description = $descriptor['description'];
		}

		if (isset($descriptor['filters'])) {
			$this->filters = array_merge($this->filters, $descriptor['filters']);
		}

		parent::__construct($descriptor, $nth);
	}
}