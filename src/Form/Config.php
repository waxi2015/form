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
		'tags' => 'tags.phtml',
		
		'html' => 'html.phtml',
		'table' => 'table.phtml',
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
		'empty' => 'form.validators.empty.empty',
		'emptyAll' => 'form.validators.empty.emptyAll',
		'emptySelect' => 'form.validators.empty.emptySelect',
		'emptyMultiselect' => 'form.validators.empty.emptyMultiselect',
		'emptyCheckbox' => 'form.validators.empty.emptyCheckbox',
		'emptyCheckboxgroup' => 'form.validators.empty.emptyCheckboxgroup',
		'emptyRadio' => 'form.validators.empty.emptyRadio',
		'emptyRadiogroup' => 'form.validators.empty.emptyRadiogroup',
		'emptyTags' => 'form.validators.empty.emptyTags',
		
		'autocompleteNew' => 'form.validators.empty.autocompleteNew',
	);

	public $validators = array(
		'emailAddress' => '\Waxis\Form\Form\Validator\EmailAddress',
		'digits' => '\Waxis\Form\Form\Validator\Digits',
		'remote' => '\Waxis\Form\Form\Validator\Remote',
		'uri' => '\Waxis\Form\Form\Validator\Uri',
		'identical' => '\Waxis\Form\Form\Validator\Identical',
		'autocomplete' => '\Waxis\Form\Form\Validator\Autocomplete',
		'securePassword' => '\Waxis\Form\Form\Validator\SecurePassword',
		'numeric' => '\Waxis\Form\Form\Validator\Numeric',
		'vat' => '\Waxis\Form\Form\Validator\Vat',
		'regexp' => '\Waxis\Form\Form\Validator\Regexp',
	);

	public $decorators = array(
		'charlimit' => 'form.decorators.charlimit_text',
		'wordlimit' => 'form.decorators.wordlimit_text',
	);

	public $uploads = array(
		'file' => 'uploads/documents/',
	);

	public $defaultElementType = 'text';

	public function getTemplateDirectory () {
		return $this->templateDirectory;
	}

	public function getTemplate ($element) {
		return $this->elements[$element];
	}

	public function getMessages () {
		return $this->messages;
	}

	public function getMessage ($key) {
		return trans($this->messages[$key]);
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
		return trans($this->decorators[$key]);
	}

	public function getUploads () {
		return $this->uploads;
	}

	public function getUpload ($key) {
		return $this->uploads[$key];
	}
}