<?php

namespace Waxis\Form\Form\Element;

class Multifile extends Element {

	public $type = 'multifile';

	public $fields = null;

	public $buttonLabel = 'form.button_files_upload';

	public $maxFileSize = '25'; # in Mb

	public $acceptedFiles = '.pdf,.docx,.doc,.xls,.xlsx,.ppt,.pptx,.txt';

	public $folder = null;

	public $limit = null;

	public $translate = true;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {

		if (isset($descriptor['fields'])) {
			$this->fields = $descriptor['fields'];
		}

		if (isset($descriptor['buttonLabel'])) {
			$this->buttonLabel = $descriptor['buttonLabel'];
		}

		if (isset($descriptor['maxFileSize'])) {
			$this->maxFileSize = $descriptor['maxFileSize'];
		}

		if (isset($descriptor['acceptedFiles'])) {
			$this->acceptedFiles = $descriptor['acceptedFiles'];
		}

		if (isset($descriptor['limit'])) {
			$this->limit = $descriptor['limit'];
		}

		if (isset($descriptor['folder'])) {
			$this->folder = $descriptor['folder'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function hasFields () {
		return !empty($this->fields);
	}

	public function getFields () {
		return $this->fields;
	}

	public function getMaxFileSize () {
		return $this->maxFileSize;
	}

	public function getAcceptedFiles () {
		return $this->acceptedFiles;
	}

	public function getButtonLabel () {
		return trans($this->buttonLabel);
	}

	public function hasLimit () {
		return !is_null($this->limit);
	}

	public function getLimit () {
		$images = $this->getValue();
		$existingImages = !empty($images) ? count($images) : 0;

		return $this->limit - $existingImages;
	}

	public function getFileUrl ($file = null, $language = null) {
		if ($file === null) {
			$file = $this->getValue(false, $language);
		}

		return $this->getFileUrlBase() . $file;
	}

	public function getFileUrlBase () {
		return '/' . $this->getConfig()->getUpload('file') . $this->getFolder();
	}

	public function getFolder () {
		$folder = $this->folder;

		if (!empty($folder) && substr($folder, -1, 1) != '/') {
			$folder .= '/';
		}

		return $folder;	
	}

	public function setId ($id = null, $regenerateDefault = false, $language = null) {
		if ($id !== null) {
			$this->id = $id;
		} elseif ($this->id === null || $regenerateDefault || $language !== null) {

			if ($this->nodeLevel == 6) {
				$id = $this->getParam('name');

				if ($language !== null) {
					$id .= '-' . $language;
				}

				if ($this->isMultiple()) {
					$id .= '-' . $this->getValueKey();
				}
			} else {
				$id = $this->getType(false);

				$id .= '-' . $this->getNodeTreeString('-');

				$nth = $this->getNthInstance();
				if ($nth !== null) {
					$id .= '-' . $nth;
				}
			}

			$id = lcfirst(str_replace(' ', '',ucwords(str_replace('-', '', $id))));

			$this->id = $id;
		}
	}

	public function fetchData () {
		$nth = $this->getNthInstance();
		$name = $this->getName(false);
		$value = $this->getValue();

		if ($this->static) {
			$data = array();
		} elseif ($value !== null && !$this->disabled) {
			if ($nth === null) {
				$data[$name] = $this->filterData($value);
			} else {
				$data[$name][$nth] = $this->filterData($value);
			}
		} elseif ($value == null && !$this->disabled) {
			if ($nth === null) {
				$data[$name] = null;
			} else {
				$data[$name][$nth] = null;
			}
		} elseif ($this->disabled) {
			$data = array();
		} else {
			$data = array();
		}

		return $data;
	}

	public function renderLanguageData ($language = null) {
		if ($language !== null && $this->isMultilingual() && $this->translate) {
			return 'data-language="' . $language . '"';
		}
	}

	public function getAdditionalAttributes ($language = null) {
		return $this->getReadonly() . ' ' . $this->getDisabled() . ' ' . $this->getTooltip() . ' ' . $this->getRequired() . ' ' . $this->getRel() . ' ' . $this->getElementData();
	}
}