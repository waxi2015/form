<?php

namespace Waxis\Form\Form\Element;

class Radiogroup extends Element {

	public $type = 'radiogroup';

	public $items = array();

	public $emptyMessageType = 'emptyRadiogroup';

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($this->descriptor['source']) && !isset($this->descriptor['items'])) {
			$this->descriptor['items'] = $this->descriptor['source'];
		}
		
		if (isset($this->descriptor['items'])) {
			$items = $this->descriptor['items'];

			if (isset($items['class']) && isset($items['method'])) {
				$class = $items['class'];
				$method = $items['method'];
				$items = new $class;
				$items = $items->$method();
			}

			if (!is_array($items) && strstr($items, '::')){
				$items = explode('::',$items);
				$class = $items[0];
				$method = $items[1];
				$items = call_user_func($class . '::' . $method);
			}

			$this->items = $items;
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

	public function getChecked ($value, $allowDefault = false) {
		$requestValue = $this->getValue($allowDefault);

		if ($requestValue === null && $this->defaultValue === null) {
			return false;
		}
		
		if (($requestValue !== null && $value == $requestValue) || (!$this->isPost && $value == $this->defaultValue && $this->defaultValue !== null)) {
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