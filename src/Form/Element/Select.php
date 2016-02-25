<?php

namespace Waxis\Form\Form\Element;

class Select extends Element {

	public $type = 'select';

	public $items = array();

	public $source = null;

	public $original = false;

	public $multiple = false;

	public $search = false;

	public $placeholder = null;

	public $emptyMessageType = 'emptySelect';

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['addEmpty']) && (empty($this->items) || $this->items[''] != '---')) {
			$this->items[''] = '---';
		}
		
		if (isset($this->descriptor['items']) && (empty($this->items) || (count($this->items) == 1 && $this->items[''] == '---'))) {
			$this->items = $this->items + $this->descriptor['items'];
		}
		
		if (isset($this->descriptor['source'])) {
			$this->source = $this->descriptor['source'];
		}
		
		if (isset($this->descriptor['original'])) {
			$this->original = $this->descriptor['original'];
		}
		
		if (isset($this->descriptor['multiple'])) {
			$this->multiple = $this->descriptor['multiple'];
		}
		
		if (isset($this->descriptor['search'])) {
			$this->search = $this->descriptor['search'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getPlaceholder () {
		if ($this->placeholder !== null) {
			return 'title="' . $this->placeholder . '"';
		}

		return false;
	}

	public function getSearch () {
		if ($this->search) {
			return 'data-live-search="true"';
		}

		return false;
	}

	public function getMultiple () {
		if ($this->multiple) {
			return 'multiple';
		}

		return false;
	}

	public function isOriginal () {
		return $this->original;
	}

	public function hasDynamicParams () {
		return (bool) $this->getDynamicParams();
	}

	public function getDynamicParams () {
		$params = isset($this->source['params']) ? $this->source['params'] : false;

		if (!$params) {
			return false;
		}

		$return = array();
		foreach ($params as $param) {
			if (strpos($param, '%') !== false) {
				$return[] = str_replace('%','',$param);
			}
		}

		return $return;
	}

	public function hasEmptyItem () {
		if (isset($this->items['']) && $this->items[''] == '---') {
			return true;
		}

		return false;
	}

	public function isItemsEmpty ($excludeFirstEmpty = true) {
		if ($excludeFirstEmpty) {
			if (empty($this->items) || (count($this->items) == 1 && $this->hasEmptyItem())) {
				return true;
			} else {
				return false;
			}
		} else {
			return empty($this->items);
		}
	}

	public function getItems () {
		if ($this->isItemsEmpty() && $this->source !== null) {
			$this->setItemsBySource();
		}

		return $this->items;
	}

	public function getSource ($param = false) {
		if ($param) {
			if (isset($this->source[$param])) {
				return $this->source[$param];
			} else {
				return false;
			}
		}

		return $this->source;
	}

	public function setItemsBySource () {
		$nthInstance = $this->getNthInstance();

		$items = @call_user_func_array(array($this->source['class'], $this->source['method']), $this->getSourceParams());

		if (empty($items)) {
			$items = array();
		}

		$value = $this->getValue();

		if ($value === null) {
		// if it has no value

			if (!$this->hasEmptyItem()) {
			// ... and it's not supposed to be blank
				reset($items);
				$this->setRequestParam($this->getName(false), key($items), $nthInstance);
			}

			// @toreview: csak beledobtam lent a "$value === false ||" -t
		} elseif (($value === false || !array_key_exists($value, $items)) && !$this->hasEmptyItem()) {

			// if it has value in request but maybe the option list changed
			// in this case if it has no empty item it should select the first item
			// which is selected anyway, but it has to be set to the request
			reset($items);
			$this->setRequestParam($this->getName(false), key($items), $nthInstance);

		} else {

			// in case it's value is the default (without having it in the request params)
			$this->setRequestParam($this->getName(false), $value, $nthInstance);
		}

		$this->items = $this->items + $items;
	}

	public function getSourceParams () {
		$params = array();

		if (!empty($this->source['params'])) {
		// if it has params
			foreach ($this->source['params'] as $key => $one) {
			// check out if any of the params is a variable

				if (strstr($one, '%')) {
					// if a param found with a wildcard 
					// it's value will be dynamic
					// which comes from an other field's value
					// through the request params
					//DX($this->getNthInstance());

					$params[] = $this->getRequestParam(getVarByPattern('/(%[a-zA-Z0-9]+)/', $one), $this->getNthInstance());
				} else {
					$params[] = $one;
				}
			}
		}

		return $params;
	}

	public function getSelected ($value, $allowDefault = false) {
		$requestValue = $this->getValue($allowDefault);

		if ($value == $requestValue) {
			return 'selected="selected"';
		}

		return false;
	}
 }

 /*

	More info: http://silviomoreto.github.io/bootstrap-select/

 */