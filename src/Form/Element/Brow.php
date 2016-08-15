<?php

namespace Waxis\Form\Form\Element;

class Brow extends Structure {

	public $type = 'brow';

	public $nodeLevel = 2;

	public $bcolumns = array();

	public $tab = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		$this->nth = $nth;

		if (!isset($descriptor['bcolumns'])) {
			$this->_setDefaultBcolumns();
		}

		if (isset($descriptor['tab'])) {
			$this->tab = $descriptor['tab'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function setValues ($values = null) {
		foreach ($this->bcolumns as $one) {
			$one->setValues($values);
		}
	}

	public function isValid () {
		$valid = true;
		foreach ($this->bcolumns as $one) {
			$one->isPost = true;
			$isValid = $one->isValid();
			if (!$isValid) {
				$valid = false;
			}
		}

		return $valid;
	}

	public function setBcolumns () {
		$this->bcolumns = array();

		foreach ($this->descriptor['bcolumns'] as $nth => $one) {
			$this->addBcolumn($one, $nth);
		}
	}

	/*public function setValues ($values) {
		foreach ($this->bcolumns as $one) {
			$one->setValues($values);
		}
	}*/

	public function renderBcolumns () {
		$this->renderObjects($this->bcolumns);
	}

	public function _setDefaultBcolumns () {

		$descriptor = $this->descriptor;

		$descriptor['bcolumns'] = array();

		if (isset($descriptor['rows'])) {
			$descriptor['bcolumns'][] = array(
				'rows' => $descriptor['rows']
			);
			unset($descriptor['rows']);
		} elseif (isset($descriptor['columns'])) {
			$descriptor['bcolumns'][] = array(
				'columns' => $descriptor['columns']
			);
			unset($descriptor['columns']);
		} elseif (isset($descriptor['elements'])) {
			$descriptor['bcolumns'][] = array(
				'elements' => $descriptor['elements']
			);
			unset($descriptor['elements']);
		} else {
			throw new \Exception('At least "Elements" should be specified at Brow.');
		}
		
		$this->descriptor = $descriptor;
	}

	public function addBcolumn ($bcolumn, $nth) {
		$bcolumnObj = new Bcolumn($bcolumn, $nth, [
			'isLoad' => $this->isLoad,
			'multilingual' => $this->multilingual,
		]);

		if ($this->condition !== null) {
			$bcolumnObj->condition = $this->condition;
		}
		$bcolumnObj->filters = $this->filters;
		$bcolumnObj->formId = $this->formId;
		$bcolumnObj->formIdentifier = $this->formIdentifier;
		//$bcolumnObj->descriptorName = $this->getDescriptorName();
		$bcolumnObj->formDescriptor = $this->formDescriptor;
		$bcolumnObj->viewMode = $this->viewMode;
		$bcolumnObj->setTemplateDirectory($this->getTemplateDirectory());
		$bcolumnObj->registryNamespace = $this->registryNamespace;
		$bcolumnObj->setParent($this);
		$bcolumnObj->setRows();
		
		$clones = $bcolumnObj->getDefaultClones();
		$clonesFromRequest = $bcolumnObj->getClonesCountFromRequest();

		if ($clonesFromRequest > $clones) {
			$clones = $clonesFromRequest;
		}

		$bcolumnObj->clones = $clones;
		$bcolumnObj->setId();

		$this->bcolumns[] = $bcolumnObj;

		for ($c = 1; $c <= $bcolumnObj->clones; $c++) {
			$bcolumnCloneObj = clone $bcolumnObj;
			$bcolumnCloneObj->nthClone = $c;
			$bcolumnCloneObj->isClone = true;

			if ($c > $bcolumnObj->getDefaultClones()) {
				$bcolumnCloneObj->isRemovable = true;
			}

			$bcolumnCloneObj->setRows();
			
			$this->bcolumns[] = $bcolumnCloneObj;
		}

		return $this->bcolumns;
	}

	public function emptyFields () {
		foreach ($this->bcolumns as $key => $bcolumn) {
			$bcolumn->clones = $bcolumn->getDefaultClones();
			if ($bcolumn->isRemovable) {
				unset($this->bcolumns[$key]);
			} else {
				$bcolumn->emptyFields();	
			}
		}
	}

	public function fetchData () {
		$data = array();

		foreach ($this->bcolumns as $key => $one) {
			$data = array_replace_recursive($data, $one->fetchData());
		}

		return $data;
	}

	public function saveExternalData ($data) {
		foreach ($this->bcolumns as $key => $one) {
			$one->saveExternalData($data);
		}
	}

	public function getData () {
		$data = $this->getParam('data');

		if (empty($data)) {
			$data = array();
		}

		$data['tree'] = $this->getNodeTreeString('-');
		$data['clone'] = trim(json_encode($this->isClone),'"');
		$data['clone-removable'] = $this->isClone && $this->isRemovable ? 'true' : 'false';

		if ($this->tab !== null) {
			$data['tab'] = $this->tab;
		}

		$return = '';
		foreach ($data as $key => $value) {
			$return .= ' data-' . $key . '="' . $value . '" ';
		}

		return $return;
	}
}