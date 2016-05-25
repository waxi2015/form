<?php

namespace Waxis\Form\Form;

class Decorator {

	public $type = null;

	public $options = null;

	public $class = null;

	public function __construct ($type, $options = null) {
		$this->type = $type;
		$this->options = $options;

		switch ($this->type) {
			case 'charlimit':
				$class = new Decorator\Charlimit($options);
				break;

			case 'wordlimit':
				$class = new Decorator\Wordlimit($options);
				break;
			
			default:
				$class = new Decorator\Wordimit($options);
				break;
		}

		$this->class = $class;
	}

	public function getOptions () {
		return $this->class->getOptions();
	}
}