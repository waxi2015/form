<?php

namespace Waxis\Form\Form\Element;

class Bcolumn extends Structure {

	public $type = 'bcolumn';

	public $nodeLevel = 3;

	public $rows = array();

	public $width = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		$this->nth = $nth;

		if (!isset($descriptor['rows'])) {
			$this->_setDefaultRows();
		}

		if (isset($descriptor['width'])) {
			$this->width = $descriptor['width'];
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
		foreach ($this->rows as $one) {
			$one->setValues($values);
		}
	}

	public function isValid () {
		$valid = true;
		foreach ($this->rows as $one) {
			$one->isPost = true;
			$isValid = $one->isValid();
			if (!$isValid) {
				$valid = false;
			}
		}

		return $valid;
	}

	public function setRows () {
		$this->rows = array();

		foreach ($this->descriptor['rows'] as $nth => $one) {
			$this->addRow($one, $nth);
		}
	}

	/*public function setValues ($values) {
		foreach ($this->rows as $one) {
			$one->setValues($values);
		}
	}*/

	public function renderRows () {
		$this->renderObjects($this->rows);
	}

	public function _setDefaultRows () {

		$descriptor = $this->descriptor;

		$descriptor['rows'] = array();
		
		if (isset($descriptor['columns'])) {
			$descriptor['rows'][] = array(
				'columns' => $descriptor['columns']
			);
			unset($descriptor['columns']);
		} elseif (isset($descriptor['elements'])) {
			$descriptor['rows'][] = array(
				'elements' => $descriptor['elements']
			);
			unset($descriptor['elements']);
		} else {
			throw new \Exception('At least "Elements" should be specified at Bcolumn.');
		}

		$this->descriptor = $descriptor;
	}

	public function addRow ($row, $nth) {
		$rowObj = new Row($row, $nth, [
			'isLoad' => $this->isLoad,
			'multilingual' => $this->multilingual,
		]);

		if ($this->condition !== null) {
			$rowObj->condition = $this->condition;
		}
		$rowObj->filters = $this->filters;
		$rowObj->formId = $this->formId;
		$rowObj->formIdentifier = $this->formIdentifier;
		//$rowObj->descriptorName = $this->getDescriptorName();
		$rowObj->formDescriptor = $this->formDescriptor;
		$rowObj->viewMode = $this->viewMode;
		$rowObj->setTemplateDirectory($this->getTemplateDirectory());
		$rowObj->registryNamespace = $this->registryNamespace;
		$rowObj->setParent($this);
		$rowObj->setColumns();
		
		$clones = $rowObj->getDefaultClones();
		$clonesFromRequest = $rowObj->getClonesCountFromRequest();

		if ($clonesFromRequest > $clones) {
			$clones = $clonesFromRequest;
		}

		$rowObj->clones = $clones;
		$rowObj->setId();

		$this->rows[] = $rowObj;

		for ($c = 1; $c <= $rowObj->clones; $c++) {
			$rowCloneObj = clone $rowObj;
			$rowCloneObj->nthClone = $c;
			$rowCloneObj->isClone = true;

			if ($c > $rowObj->getDefaultClones()) {
				$rowCloneObj->isRemovable = true;
			}

			$rowCloneObj->setColumns();
			
			$this->rows[] = $rowCloneObj;
		}

		return $this->rows;
	}

	public function emptyFields () {
		foreach ($this->rows as $key => $row) {
			$row->clones = $row->getDefaultClones();
			if ($row->isRemovable) {
				unset($this->rows[$key]);
			} else {
				$row->emptyFields();	
			}
		}
	}

	public function fetchData () {
		$data = array();

		foreach ($this->rows as $key => $one) {
			$data = array_replace_recursive($data, $one->fetchData());
		}

		return $data;
	}

	public function saveExternalData ($data) {
		foreach ($this->rows as $key => $one) {
			$one->saveExternalData($data);
		}
	}
}