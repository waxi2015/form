<?php

namespace Waxis\Form\Form\Element;

class File extends Element {

	public $type = 'file';

	public $buttonLabel = 'Fájl feltöltése';

	public $maxFileSize = '25'; # in Mb

	public $acceptedFiles = '.pdf,.docx,.doc,.xls,.xlsx,.ppt,.pptx,.txt';

	public $folder = null;

	public $translate = true;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {

		if (isset($descriptor['buttonLabel'])) {
			$this->buttonLabel = $descriptor['buttonLabel'];
		}

		if (isset($descriptor['maxFileSize'])) {
			$this->maxFileSize = $descriptor['maxFileSize'];
		}

		if (isset($descriptor['acceptedFiles'])) {
			$this->acceptedFiles = $descriptor['acceptedFiles'];
		}

		if (isset($descriptor['folder'])) {
			$this->folder = $descriptor['folder'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getMaxFileSize () {
		return $this->maxFileSize;
	}

	public function getAcceptedFiles () {
		return $this->acceptedFiles;
	}

	public function getButtonLabel () {
		return $this->buttonLabel;
	}

	public function getFileUrl ($language = null) {
		return '/' . $this->getConfig()->getUpload('file') . $this->getFolder() . $this->getValue(false, $language);
	}

	public function getFolder () {
		$folder = $this->folder;

		if (!empty($folder) && substr($folder, -1, 1) != '/') {
			$folder .= '/';
		}

		return $folder;	
	}
}