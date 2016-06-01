<?php

namespace Waxis\Form\Form\Element;

class Slider extends Element {

	public $type = 'slider';

	public $isField = true;
	
	public $min = 0;
	
	public $max = 10;
	
	public $step = 1;
	
	public $orientation = 'horizontal'; // horizontal / vertical

	public $range = false;

	public $tooltip = true;

	public $selector = null;

	public $minSelector = null;

	public $maxSelector = null;
	
	public $format = false;
	
	public $prefix = false;

	public $suffix = false;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($this->descriptor['range'])) {
			$this->range = $this->descriptor['range'];
		}

		if (isset($this->descriptor['min'])) {
			$this->min = $this->descriptor['min'];
		}

		if (isset($this->descriptor['max'])) {
			$this->max = $this->descriptor['max'];
		}

		if (isset($this->descriptor['step'])) {
			$this->step = $this->descriptor['step'];
		}

		if (isset($this->descriptor['orientation'])) {
			$this->orientation = $this->descriptor['orientation'];
		}

		if (isset($this->descriptor['tooltip'])) {
			$this->tooltip = $this->descriptor['tooltip'];
		}

		if (isset($this->descriptor['selector'])) {
			$this->selector = $this->descriptor['selector'];
		}

		if (isset($this->descriptor['selector'])) {
			$this->minSelector = $this->descriptor['selector'];
		}

		if (isset($this->descriptor['minSelector'])) {
			$this->minSelector = $this->descriptor['minSelector'];
		}

		if (isset($this->descriptor['maxSelector'])) {
			$this->maxSelector = $this->descriptor['maxSelector'];
		}

		if (isset($this->descriptor['format'])) {
			$this->format = $this->descriptor['format'];
		}

		if (isset($this->descriptor['prefix'])) {
			$this->prefix = $this->descriptor['prefix'];
		}

		if (isset($this->descriptor['suffix'])) {
			$this->suffix = $this->descriptor['suffix'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getValue ($key = false, $allowDefault = false) {
		$value = parent::getValue($allowDefault);

		if (!is_array($value)) {
			$value = array($value);
		}

		if ($key !== false) {
			if (isset($value[$key])) {
				return $value[$key];
			} else {
				return false;
			}
		} else {
			return $value;
		}
	}

	public function getName ($withSuffix = true, $hideBrackets = false) {
		$name = $this->getParam('name');

		if ($hideBrackets) {
			$name = preg_replace('/\[.*\]/', '', $name);
		}

		$suffix = '';

		if ($withSuffix && $this->isRange()) {
			$valueKey = $this->getValueKey();
			$suffix = !is_null($valueKey) ? '['.$valueKey.'][]' : '[]';
		}

		return $name . $suffix;
	}

	public function getValueString () {
		$value = $this->getValue(false, true);

		if (count($value) > 1) {
			return '['.$value[0].','.$value[1].']';
		} else {
			return $value[0];
		}
	}

	public function isRange () {
		if ($this->range !== null) {
			return $this->range;
		}

		$value = $this->getValue(false, true);

		if (count($value) > 1) {
			$this->range = true;
		} else {
			$this->range = false;
		}

		return $this->range;
	}

	public function getSelector () {
		return replacePatternToClassAsset('/(%[a-zA-Z0-9]+\({0,1}\){0,1})/', $this->minSelector, $this);
	}

	public function getMinSelector () {
		return replacePatternToClassAsset('/(%[a-zA-Z0-9]+\({0,1}\){0,1})/', $this->minSelector, $this);
	}

	public function getMaxSelector () {
		return replacePatternToClassAsset('/(%[a-zA-Z0-9]+\({0,1}\){0,1})/', $this->maxSelector, $this);
	}

	public function isFormat () {
		return $this->format;
	}

	public function getPrefix () {
		return $this->prefix;
	}

	public function getSuffix () {
		return $this->suffix;
	}
}

// To extend the list of vars passed to slider check out:
// https://github.com/seiyria/bootstrap-slider#options
//
// Just set a class variable with the name required and add the following
// before the parent::__construct($descriptor, $nth); :
//
// if (isset($this->descriptor['VAR_NAME'])) {
//     $this->VAR_NAME = $descriptor['VAR_NAME'];
// }
//
// and then you can access it.