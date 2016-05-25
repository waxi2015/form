<?php

namespace Waxis\Form\Form\Element;

class Section extends Structure {

	public $type = 'section';

	public $nodeLevel = 1;

	public $brows = array();

	public $title = null;
	
	public $tabs = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		$this->nth = $nth;

		if (!isset($descriptor['brows'])) {
			$this->_setDefaultBrows();
		}

		if (isset($descriptor['title'])) {
			$this->title = $descriptor['title'];
		}

		if (isset($descriptor['tabs'])) {
			$this->tabs = $descriptor['tabs'];
		}
		
		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getTitle () {
		return $this->title;
	}

	public function setValues ($values = null) {
		foreach ($this->brows as $one) {
			$one->setValues($values);
		}
	}

	public function isValid () {
		$valid = true;
		foreach ($this->brows as $one) {
			$one->isPost = true;
			$isValid = $one->isValid();
			if (!$isValid) {
				$valid = false;
			}
		}

		return $valid;
	}

	public function setBrows () {
		$this->brows = array();

		foreach ($this->descriptor['brows'] as $nth => $one) {
			$this->addBrow($one, $nth);
		}
	}

	public function renderBrows () {
		$this->renderObjects($this->brows);
	}

	public function _setDefaultBrows () {

		$descriptor = $this->descriptor;

		$descriptor['brows'] = array();

		if (isset($descriptor['bcolumns'])) {
			$descriptor['brows'][] = array(
				'bcolumns' => $descriptor['bcolumns']
			);
			unset($descriptor['bcolumns']);
		} elseif (isset($descriptor['rows'])) {
			$descriptor['brows'][] = array(
				'rows' => $descriptor['rows']
			);
			unset($descriptor['rows']);
		} elseif (isset($descriptor['columns'])) {
			$descriptor['brows'][] = array(
				'columns' => $descriptor['columns']
			);
			unset($descriptor['columns']);
		} elseif (isset($descriptor['elements'])) {
			$descriptor['brows'][] = array(
				'elements' => $descriptor['elements']
			);
			unset($descriptor['elements']);
		} else {
			throw new \Exception('At least "Elements" should be specified at Brow.');
		}

		$this->descriptor = $descriptor;
	}

	public function addBrow ($brow, $nth) {
		$browObj = new Brow($brow, $nth, [
			'isLoad' => $this->isLoad,
			'multilingual' => $this->multilingual,
		]);

		if ($this->condition !== null) {
			$browObj->condition = $this->condition;
		}
		$browObj->filters = $this->filters;
		$browObj->formId = $this->formId;
		$browObj->formIdentifier = $this->formIdentifier;
		//$browObj->descriptorName = $this->getDescriptorName();
		$browObj->formDescriptor = $this->formDescriptor;
		$browObj->viewMode = $this->viewMode;
		$browObj->setTemplateDirectory($this->getTemplateDirectory());
		$browObj->registryNamespace = $this->registryNamespace;
		$browObj->setParent($this);
		$browObj->setBcolumns();
		$browObj->isLoad = $this->isLoad;
		
		$clones = $browObj->getDefaultClones();
		$clonesFromRequest = $browObj->getClonesCountFromRequest();

		if ($clonesFromRequest > $clones) {
			$clones = $clonesFromRequest;
		}

		$browObj->clones = $clones;
		$browObj->setId();

		$this->brows[] = $browObj;

		for ($c = 1; $c <= $browObj->clones; $c++) {
			$browCloneObj = clone $browObj;
			$browCloneObj->nthClone = $c;
			$browCloneObj->isClone = true;

			if ($c > $browObj->getDefaultClones()) {
				$browCloneObj->isRemovable = true;
			}

			$browCloneObj->setBcolumns();
			
			$this->brows[] = $browCloneObj;
		}

		return $this->brows;
	}

	public function emptyFields () {
		foreach ($this->brows as $key => $brow) {
			$brow->clones = $brow->getDefaultClones();
			if ($brow->isRemovable) {
				unset($this->brows[$key]);
			} else {
				$brow->emptyFields();	
			}
		}
	}

	public function fetchData () {
		$data = array();

		foreach ($this->brows as $key => $one) {
			$data = array_replace_recursive($data, $one->fetchData());
		}

		return $data;
	}

	public function saveExternalData () {
		foreach ($this->brows as $key => $one) {
			$one->saveExternalData();
		}
	}

	public function getTabs () {
		return $this->tabs;
	}

	public function renderTabs () {
		if ($this->getTabs() === null) {
			return;
		}

		return $this->renderTemplate('tabs');
	}
}