<?php

namespace Waxis\Form\Form\Element;

class Column extends Structure {

	public $type = 'column';

	public $nodeLevel = 5;

	public $elements = array();

	public $width = null;

	public $required = false;

	public $error = null;

	public $condition = null;

	public $emptyMessageType = 'emptyAll';

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		$this->nth = $nth;

		if (!isset($descriptor['elements'])) {
			throw new \Exception('"Elements" must be specified at Column.');
		}

		if (isset($descriptor['width'])) {
			$this->width = $descriptor['width'];
		}

		if (isset($this->descriptor['required'])) {
			$this->required = $this->descriptor['required'];
		}

		if (isset($this->descriptor['error'])) {
			$this->error = $this->descriptor['error'];
		}

		if (isset($this->descriptor['condition'])) {
			$this->condition = $this->descriptor['condition'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getWidthString () {
		if (is_null($this->width)) {
			return 'col-lg-12';
		}

		$return = '';
		if (is_array($this->width)) {
			foreach ($this->width as $size => $width) {
				$return .= "col-$size-$width ";
			}
		} else {
			$return = 'col-xs-' . $this->width;
		}

		return $return;
	}

	public function setValues ($values = null) {
		foreach ($this->elements as $one) {
			$one->setValues($values);
		}
	}

	public function isValid () {
		$valid = true;
		$validate = true;

		if (!is_null($this->condition)) {
			foreach ($this->condition as $field => $value) {
				if (!$this->isConditionTrue($field, $value)) {
					$validate = false;
				}
			}
		}
		
		foreach ($this->elements as $one) {
			if (!$one->static) {
				$one->isPost = true;
				$isValid = $one->isValid();

				if (!$isValid) {
					$valid = false;
				}
			}
		}

		if ($this->required && $validate) {
			$empty = false;

			foreach ($this->elements as $one) {
				if (!$one->static && $one->isEmpty()) {
					$empty = true;
				}
			}

			if ($empty) {
				$valid = false;

				$error = $this->error === null ? $this->getConfig()->getMessage($this->emptyMessageType) : $this->error;
				$this->addError($error);
			}
		}

		return $valid;
	}

	public function setElements () {
		$this->elements = array();

		foreach ($this->descriptor['elements'] as $nth => $one) {
			$this->addElement($one, $nth);
		}
	}

	public function renderElements () {
		$this->renderObjects($this->elements);
	}

	public function addElement ($element, $nth) {
		if (!isset($element['type'])) {
			$element['type'] = $this->getConfigVar('defaultElementType');
		}

		$className = '\Waxis\Form\Form\Element\\' . ucfirst($element['type']);

		$elementObj = new $className($element, $nth, [
			'isLoad' => $this->isLoad,
			'multilingual' => $this->multilingual,
		]);

		if ($this->condition !== null) {
			$elementObj->condition = $this->condition;
		}
		$elementObj->filters = array_merge($elementObj->filters, $this->filters);
		$elementObj->formId = $this->formId;
		$elementObj->formIdentifier = $this->formIdentifier;
		//$elementObj->descriptorName = $this->getDescriptorName();
		$elementObj->formDescriptor = $this->formDescriptor;
		$elementObj->viewMode = $this->viewMode;
		$elementObj->setTemplateDirectory($this->getTemplateDirectory());
		$elementObj->registryNamespace = $this->registryNamespace;
		$elementObj->setParent($this);
		$elementObj->isRemovable = false;

		$clones = (int) $elementObj->getDefaultClones();
		$clonesFromRequest = (int) $elementObj->getClonesCountFromRequest();

		if ($clonesFromRequest > $clones) {
			$clones = $clonesFromRequest;
		}
		
		$elementObj->clones = $clones;
		$elementObj->setId();
		$elementObj->__construct($element, $nth, [
			'isLoad' => $this->isLoad,
			'multilingual' => $this->multilingual,
		]);

		$this->elements[] = $elementObj;

		for ($c = 1; $c <= $elementObj->clones; $c++) {
			$elementCloneObj = clone $elementObj;
			$elementCloneObj->nthClone = $c;
			$elementCloneObj->isClone = true;
			$elementCloneObj->setId(null, true);
			$elementCloneObj->__construct($element, $nth, [
				'isLoad' => $this->isLoad,
				'multilingual' => $this->multilingual,
			]);

			if ($c > $elementObj->getDefaultClones()) {
				$elementCloneObj->isRemovable = true;
			}

			$this->elements[] = $elementCloneObj;
		}
	}

	public function emptyFields () {
		foreach ($this->elements as $key => $element) {
			$element->clones = $element->getDefaultClones();
			if ($element->isRemovable) {
				unset($this->elements[$key]);
			} else {
				$element->emptyField();	
			}
		}
	}

	public function fetchData () {
		$data = array();

		foreach ($this->elements as $key => $one) {
			$data = array_replace_recursive($data, $one->fetchData());
		}

		return $data;
	}

	public function saveExternalData () {
		foreach ($this->elements as $key => $one) {
			$one->saveExternalData();
		}
	}
}
