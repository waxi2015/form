<?php

namespace Waxis\Form\Form;

class Filter {

	public $data = null;

	public function __construct ($data) {
		$this->data = $data;
	}

	public function striptags ($options = false) {
		$allowed = $options['allowed'];

		if (is_array($this->data)) {
			foreach ($this->data as $key => $value) {
				$this->data[$key] = strip_tags($value, $allowed);
			}
		} else {
			$this->data = strip_tags($this->data, $allowed);
		}
	}

	public function trim () {
		if (is_array($this->data)) {
			foreach ($this->data as $key => $value) {
				$this->data[$key] = trim($value);
			}
		} else {
			$this->data = trim($this->data);
		}
	}

	public function getData () {
		return $this->data;
	}
}