<?php

namespace Waxis\Form\Form;

class Config {

	public $templateDirectory = '/Template/';

	public $elements = array(
		'form' => 'form.phtml',
		'section' => 'section.phtml',
		'brow' => 'brow.phtml',
		'bcolumn' => 'bcolumn.phtml',
		'row' => 'row.phtml',
		'column' => 'column.phtml',

		'label' => 'label.phtml',
		'info' => 'info.phtml',
		'description' => 'description.phtml',
		'html' => 'html.phtml',

		'hidden' => 'hidden.phtml',
		'text' => 'text.phtml',
		'email' => 'email.phtml',
		'password' => 'password.phtml',
		'textarea' => 'textarea.phtml',
		'select' => 'select.phtml',
		'multiselect' => 'multiselect.phtml',
		'checkbox' => 'checkbox.phtml',
		'checkboxgroup' => 'checkboxgroup.phtml',
		'radio' => 'radio.phtml',
		'radiogroup' => 'radiogroup.phtml',
		
		'image' => 'image.phtml',
		'multiimage' => 'multiimage.phtml',
		'file' => 'file.phtml',
		'multifile' => 'multifile.phtml',

		'swap' => 'swap.phtml',
		'slider' => 'slider.phtml',
		'autocomplete' => 'autocomplete.phtml',
		'multicheckbox' => 'multicheckbox.phtml',
		
		'editor' => 'editor.phtml',
		
		'anchor' => 'anchor.phtml',
		'button' => 'button.phtml',
		'submit' => 'submit.phtml',

		'clone' => 'clone.phtml',
		'error' => 'error.phtml',

		'condition' => 'condition.phtml',

		'tabs' => 'tabs.phtml',
		'steps' => 'steps.phtml',
		'languages' => 'languages.phtml',
	);

	public $messages = array(
		'empty' => 'Please fill in this field',
		'emptyAll' => 'Please fill all the fields',
		'emptySelect' => 'Please choose one',
		'emptyMultiselect' => 'Please select at least one',
		'emptyCheckbox' => 'You must check this field',
		'emptyCheckboxgroup' => 'Please select at least one',
		'emptyRadio' => 'You must check this field',
		'emptyRadiogroup' => 'Please select at least one',
		'emptyMulticheckbox' => 'Please select at least one',
		
		'autocompleteNew' => 'Please select one from the list',
	);

	public $validators = array(
		'emailAddress' => '\Waxis\Form\Form\Validator\EmailAddress',
		'digits' => '\Waxis\Form\Form\Validator\Digits',
		'remote' => '\Waxis\Form\Form\Validator\Remote',
		'uri' => '\Waxis\Form\Form\Validator\Uri',
		'identical' => '\Waxis\Form\Form\Validator\Identical',
		'autocomplete' => '\Waxis\Form\Form\Validator\Autocomplete',
	);

	public $decorators = array(
		'charlimit' => ' character(s) left',
		'wordlimit' => ' word(s) left',
	);

	public $uploads = array(
		'file' => 'uploads/docs/',
	);

	public $defaultElementType = 'text';

	public function getTemplateDirectory () {
		return __DIR__ . $this->templateDirectory;
	}

	public function getTemplate ($element) {
		return $this->elements[$element];
	}

	public function getMessages () {
		return $this->messages;
	}

	public function getMessage ($key) {
		return $this->messages[$key];
	}

	public function getValidators () {
		return $this->validators;
	}

	public function getValidator ($key) {
		return $this->validators[$key];
	}

	public function getDecorators () {
		return $this->decorators;
	}

	public function getDecorator ($key) {
		return $this->decorator[$key];
	}

	public function getUploads () {
		return $this->uploads;
	}

	public function getUpload ($key) {
		return $this->uploads[$key];
	}
}