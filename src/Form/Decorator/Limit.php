<?php

namespace Waxis\Form\Form\Decorator;

class Limit extends Ancestor {

	public $type = null;

	public function __construct ($options) {
		if ($this->type === null) {
			throw new Exception('Decorator Limit cannot be called directly.', 1);
		}

		$options['message'] = $this->getConfigVar('decorators')[$this->type];

		parent::__construct($options);
	}

}