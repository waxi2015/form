<?php

namespace Waxis\Form\Form\Element;

class Row extends Structure {

	public $type = 'row';

	public $nodeLevel = 4;

	public $columns = array();

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		$this->nth = $nth;

		if (!isset($descriptor['columns'])) {
			$this->_setDefaultColumns();
		}

		if (isset($descriptor['renderStructure'])) {
			$this->renderStructure = $descriptor['renderStructure'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function setValues ($values = null) {
		foreach ($this->columns as $one) {
			$one->setValues($values);
		}
	}

	public function isValid () {
		$valid = true;
		foreach ($this->columns as $one) {
			$one->isPost = true;
			$isValid = $one->isValid();
			if (!$isValid) {
				$valid = false;
			}
		}

		return $valid;
	}

	public function setColumns () {
		$this->columns = array();

		foreach ($this->descriptor['columns'] as $nth => $one) {
			$this->addColumn($one, $nth);
		}
	}

	/*public function setValues ($values) {
		foreach ($this->columns as $one) {
			$one->setValues($values);
		}
	}*/

	public function renderColumns () {
		$this->renderObjects($this->columns);
	}

	public function _setDefaultColumns () {

		$descriptor = $this->descriptor;

		$descriptor['columns'] = array();

		if (isset($descriptor['elements'])) {
			$descriptor['columns'][] = array(
				'elements' => $descriptor['elements']
			);
		} else {
			throw new \Exception('At least "Elements" should be specified at Row.');
		}

		$this->descriptor = $descriptor;
	}

	public function addColumn ($column, $nth) {
		$columnObj = new Column($column, $nth, [
			'isLoad' => $this->isLoad,
			'multilingual' => $this->multilingual,
		]);

		if ($this->condition !== null) {
			$columnObj->condition = $this->condition;
		}

		if (getValue($column, 'renderStructure', null) === null) {
			$columnObj->renderStructure = $this->renderStructure;
		}
		
		$columnObj->filters = $this->filters;
		$columnObj->formId = $this->formId;
		$columnObj->formIdentifier = $this->formIdentifier;
		//$columnObj->descriptorName = $this->getDescriptorName();
		$columnObj->formDescriptor = $this->formDescriptor;
		$columnObj->viewMode = $this->viewMode;
		$columnObj->setTemplateDirectory($this->getTemplateDirectory());
		$columnObj->registryNamespace = $this->registryNamespace;
		$columnObj->setParent($this);
		$columnObj->isRemovable = false;
		$columnObj->setElements();

		$clones = $columnObj->getDefaultClones();
		$clonesFromRequest = $columnObj->getClonesCountFromRequest();

		if ($clonesFromRequest > $clones) {
			$clones = $clonesFromRequest;
		}

		$columnObj->clones = $clones;
		$columnObj->setId();

		$this->columns[] = $columnObj;

		for ($c = 1; $c <= $columnObj->clones; $c++) {
			$columnCloneObj = clone $columnObj;
			$columnCloneObj->nthClone = $c;
			$columnCloneObj->isClone = true;

			if ($c > $columnObj->getDefaultClones()) {
				$columnCloneObj->isRemovable = true;
			}

			$columnCloneObj->setElements();

			$this->columns[] = $columnCloneObj;
		}

		return $this->columns;
	}

	public function emptyFields () {
		foreach ($this->columns as $key => $column) {
			$column->clones = $column->getDefaultClones();
			if ($column->isRemovable) {
				unset($this->columns[$key]);
			} else {
				$column->emptyFields();	
			}
		}
	}

	public function fetchData () {
		$data = array();

		foreach ($this->columns as $key => $one) {
			$data = array_replace_recursive($data, $one->fetchData());
		}

		return $data;
	}

	public function saveExternalData ($data) {
		foreach ($this->columns as $key => $one) {
			$one->saveExternalData($data);
		}
	}
}