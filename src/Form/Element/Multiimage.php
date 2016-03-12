<?php

namespace Waxis\Form\Form\Element;

class Multiimage extends Element {

	public $type = 'multiimage';

	public $imageDescriptor = null;

	public $fields = null;

	public $preview = 'normal';

	public $thumbnail = 'thumbnail';

	public $buttonLabel = 'form.button_images_upload';

	public $maxFileSize = '25'; # in Mb

	public $acceptedFiles = 'image/*';

	public $limit = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		$this->imageDescriptor = $descriptor['imageDescriptor'];

		if (isset($descriptor['fields'])) {
			$this->fields = $descriptor['fields'];
		}

		if (isset($descriptor['thumbnail'])) {
			$this->thumbnail = $descriptor['thumbnail'];
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

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function hasFields () {
		return !empty($this->fields);
	}

	public function getFields () {
		return $this->fields;
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

	public function getPreviewSize () {
		return $this->preview;
	}

	public function getButtonLabel () {
		return trans($this->buttonLabel);
	}

	public function getThumbnailUrlBase () {
		return '/image/' . $this->getImageDescriptor() . '/' . $this->getThumbnailSize() . '/';
	}

	public function getPreviewUrlBase () {
		return '/image/' . $this->getImageDescriptor() . '/' . $this->getPreviewSize() . '/';
	}

	public function getThumbnailUrl ($image) {
		return $this->getThumbnailUrlBase() . $image;
	}

	public function getPreviewUrl ($image) {
		return $this->getPreviewUrlBase() . $image;
	}

	public function hasLimit () {
		return !is_null($this->limit);
	}

	public function getLimit () {
		$images = $this->getValue();
		$existingImages = !empty($images) ? count($images) : 0;

		return $this->limit - $existingImages;
	}

	public function setId ($id = null, $regenerateDefault = false, $language = null) {
		if ($id !== null) {
			$this->id = $id;
		} elseif ($this->id === null || $regenerateDefault) {

			if ($this->nodeLevel == 6) {
				$id = $this->getParam('name');

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
}