<?php

namespace Waxis\Form\Form\Element;

class Editor extends Textarea {

	public $type = 'editor';

	public $translate = true;

	public $css = '/css/editor/editor.css';

	public $toolbar = [
		['Format'],
		['Bold', 'Italic', 'Underline', 'Strike'],
		['BulletedList','NumberedList','Outdent','Indent'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Image','Youtube',',Table','Link','Unlink'],
		['Undo','Redo'],
		['Source']
	];

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {

		if (isset($descriptor['css'])) {
			$this->css = $descriptor['css'];
		}

		if (isset($descriptor['toolbar'])) {
			$this->toolbar = $descriptor['toolbar'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getCss()
	{
		return $this->css;
	}

	public function getToolbar()
	{
		return $this->toolbar;
	}
}