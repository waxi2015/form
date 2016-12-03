<?php

namespace Waxis\Form\Form\Element;

class Image extends Element {

	public $type = 'image';

	public $imageDescriptor = 'image';

	public $thumbnail = 'thumbnail';

	public $buttonLabel = 'form.button_image_upload';

	public $buttonDeleteLabel = 'form.button_image_delete';

	public $maxFileSize = '25'; # in Mb

	public $acceptedFiles = 'image/*';

	public $onSuccess = null;

	public $onRemove = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		$this->imageDescriptor = $descriptor['imageDescriptor'];

		if (isset($descriptor['thumbnail'])) {
			$this->thumbnail = $descriptor['thumbnail'];
		}

		if (isset($descriptor['buttonLabel'])) {
			$this->buttonLabel = $descriptor['buttonLabel'];
		}

		if (isset($descriptor['buttonDeleteLabel'])) {
			$this->buttonDeleteLabel = $descriptor['buttonDeleteLabel'];
		}

		if (isset($descriptor['maxFileSize'])) {
			$this->maxFileSize = $descriptor['maxFileSize'];
		}

		if (isset($descriptor['acceptedFiles'])) {
			$this->acceptedFiles = $descriptor['acceptedFiles'];
		}

		if (isset($descriptor['onSuccess'])) {
			$this->onSuccess = $descriptor['onSuccess'];
		}

		if (isset($descriptor['onRemove'])) {
			$this->onRemove = $descriptor['onRemove'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getOnSuccess () {
		return $this->onSuccess;
	}

	public function getOnRemove () {
		return $this->onRemove;
	}

	public function getImageDescriptor () {
		return $this->imageDescriptor;
	}

	public function getMaxFileSize () {
		return $this->maxFileSize;
	}

	public function getAcceptedFiles () {
		return $this->acceptedFiles;
	}

	public function getThumbnailSize () {
		return $this->thumbnail;
	}

	public function getButtonLabel () {
		return trans($this->buttonLabel);
	}

	public function getButtonDeleteLabel () {
		return trans($this->buttonDeleteLabel);
	}

	public function getImageUrlBase () {
		return '/image/' . $this->getImageDescriptor() . '/' . $this->getThumbnailSize() . '/';
	}

	public function getImageUrl () {
		return '/image/' . $this->getImageDescriptor() . '/' . $this->getThumbnailSize() . '/' . $this->getValue();
	}

	public function getDefaultImageUrl () {
		return '/image/' . $this->getImageDescriptor() . '/' . $this->getThumbnailSize() . '/default';
	}

	public function renderDefaultHidden () {
		return !empty($this->getValue()) ? ' hidden' : '';
	}

	public function renderRemoveHidden () {
		return empty($this->getValue()) ? ' hidden' : '';
	}
}