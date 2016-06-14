<?php

namespace Waxis\Form\Form\Element;

class File extends Element {

	public $type = 'file';

	public $buttonLabel = 'form.button_file_upload';

	public $maxFileSize = '25'; # in Mb

	public $acceptedFiles = '.pdf,.docx,.doc,.xls,.xlsx,.ppt,.pptx,.txt';

	public $previewUrl = null;

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

		if (isset($descriptor['previewUrl'])) {
			$this->previewUrl = $descriptor['previewUrl'];
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
		return trans($this->buttonLabel);
	}

	public function getFileUrl ($language = null) {
		$url = $this->getPreviewUrl();

		if ($url === null) {
			$url = '/' . $this->getConfig()->getUpload('file');
		}

		$url .= $this->getFolder();

		return $url . $this->getValue(false, $language);
	}

	public function getFolder () {
		$folder = $this->folder;

		if (!empty($folder) && substr($folder, -1, 1) != '/') {
			$folder .= '/';
		}

		return $folder;	
	}

	public function getPreviewUrl () {
		return $this->previewUrl;
	}
}