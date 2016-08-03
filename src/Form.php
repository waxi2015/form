<?php

namespace Waxis\Form;

class Form extends Form\Element\Form {

	public function __construct($descriptor, $loadData = array(), $isCloneRequest = null) {
		$this->formId = $this->createFormId();

		if (is_string($descriptor)) {
			$descriptorClass = '\App\Descriptors\Form\\' . ucfirst($descriptor);
			$descriptorObj = new $descriptorClass();

			$descriptor = $descriptorObj->descriptor();
		}

		$this->descriptor = $this->formDescriptor = $descriptor;

		if (isset($descriptor['idElement'])) {
			$this->idElement = $descriptor['idElement'];
		}

		if (isset($descriptor['idField'])) {
			$this->idField = $descriptor['idField'];
		}

		if (isset($descriptor['converters'])) {
			$this->converters = $descriptor['converters'];
		}

		if ($isCloneRequest !== null) {
			$this->isCloneRequest = $isCloneRequest;
		}

		if (isset($descriptor['init'])) {
			$this->init = $descriptor['init'];
		}
		
		if (isset($descriptor['multilingual'])) {
			$this->multilingual = $descriptor['multilingual'];
		}

		if (!empty($loadData)) {
			$this->isLoad = true;

			if (isset($this->descriptor['table'])) {
				$this->table = $this->descriptor['table'];

				if (is_numeric($loadData)) {
					$this->dataId = $loadData;
					$loadData = array();
				}
			} elseif (is_numeric($loadData)) {
				$this->isLoad = false;
				$loadData = false;
			}
		}

		if ($loadData !== false) {
			$this->loadData($loadData);
		}
		
		parent::__construct($descriptor);
		// This is required for the clones to see default element values
		// when checking for conditional default values
		if (!$this->isLoad && !$this->isPost) {
			$this->setDefaultValuesToRequestParams();
		}
	}
}