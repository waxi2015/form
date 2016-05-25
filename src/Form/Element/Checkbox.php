<?php

namespace Waxis\Form\Form\Element;

class Checkbox extends Element {

	public $type = 'checkbox';

	public $checked = null;

	public $defaultValue = false;

	public $emptyMessageType = 'emptyCheckbox';

	public $value = 1;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['value'])) {
			$this->value = $this->descriptor['value'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function isEmpty ($language = null) {
		if (!$this->isChecked()) {
			$this->isEmpty = true;
		} else {
			$this->isEmpty = false;
		}

		return $this->isEmpty;
	}

	public function getValue ($allowDefault = false, $language = null) {
		return $this->value;
	}

	public function isChecked () {
		$nth = false;
		$checked = false;
		if ($this->checked === null) {
			if ($this->isPost || $this->isLoad) {
				if ($this->isCloneable) {
					$nth = $this->nthClone;
				} elseif ($this->isParentCloneable()) {
					$parent = $this->getClosestParent(array('clone' => true));
					$nth = $parent['nthClone'];
				}

				$value = $this->getRequestParam($this->getName(false), $nth);

				# && $value == $this->value added as trial
				if ($value !== null && $value !== false && $value == $this->value) {
					$checked = true;
				}
			} else {
				$checked = $this->defaultValue;
			}
		} else {
			$checked = $this->checked;
		}

		return $checked;
	}

	public function getChecked () {
		$return = '';

		if ($this->isChecked()) {
			$return = 'checked="checked"';
		}
		return $return;
	}

	public function setChecked ($checked) {
		$this->checked = $checked;
	}

	public function emptyField () {
		if ($this->defaultValue === true) {
			$this->setChecked(true);
		} else {
			$this->setChecked(false);
		}
	}

	public function getAdditionalAttributes ($value = null) {
		return  $this->getChecked() . ' ' . $this->getDisabled() . ' ' . $this->getRel();
	}

	public function fetchData () {
		$nth = $this->getNthInstance();
		$name = $this->getName(false);
		$value = $this->isChecked() ? true : false;

		if ($value !== null && !$this->disabled) {
			if ($nth === null) {
				$data[$name] = $value;
			} else {
				$data[$name][$nth] = $value;
			}
		} else {
			$data = array();
		}
	
		return $data;
	}
 }