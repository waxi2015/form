<?php

namespace Waxis\Form\Form\Element;

class Checkboxgroup extends Element {

	public $type = 'checkboxgroup';

	public $items = array();

	public $emptyMessageType = 'emptyCheckboxgroup';

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['items'])) {
			$this->items = $this->descriptor['items'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function isEmpty ($language = null) {
		$empty = true;

		foreach ($this->items as $value => $label) {
			if ($this->getChecked($value)) {
				$empty = false;
			}
		}

		return $empty;
	}

	public function getName ($withSuffix = true, $hideBrackets = false) {
		$name = $this->getParam('name');

		if ($hideBrackets) {
			$name = preg_replace('/\[.*\]/', '', $name);
		}

		$suffix = '';

		if ($withSuffix) {
			$valueKey = $this->getValueKey();
			$suffix = !is_null($valueKey) ? '['.$valueKey.'][]' : '[]';
		}

		return $name . $suffix;
	}

	public function getChecked ($value, $allowDefault = false) {
		$requestValue = $this->getValue($allowDefault);

		if (empty($requestValue) && $this->defaultValue === null) {
			return false;
		}

		if ((
				$requestValue !== null &&
				(
					(is_array($requestValue) && in_array($value, $requestValue)) ||
					$value == $requestValue
				)
			) ||
			(
				!$this->isPost && !$this->isLoad &&
				(
					(is_array($this->defaultValue) && in_array($value, $this->defaultValue)) ||
					$value == $this->defaultValue
				)
			)
			
		) {
			return 'checked="checked"';
		}

		return false;
	}

	public function getAdditionalAttributes ($value = null) {
		return  $this->getChecked($value, true) . ' ' . $this->getDisabled() . ' ' . $this->getRel();
	}

	public function fetchData () {
		$nth = $this->getNthInstance();
		$name = $this->getName(false);
		$value = $this->getValue();

		if (!$this->disabled) {
			if ($nth === null) {
				$data[$name] = $value !== null ? $value : array();
			} else {
				$data[$name][$nth] = $value !== null ? $value : array();
			}
		} else {
			$data = array();
 		}
		
		return $data;
	}
 }