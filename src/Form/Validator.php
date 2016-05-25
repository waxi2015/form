<?php

namespace Waxis\Form\Form;

class Validator {

	public $validator = null;

	public $options = array();

	public $message = null;

	public $errors = array();

	public $context = array();

	public $config = null;

	public $element = null;

	public function __construct ($validator, $options, $context, $element) {
		$this->context = $context;
		$this->element = $element;
		$this->validator = $validator;
		$this->options = $options;

		if (isset($options['message'])){
			$this->message = $options['message'];
		}
	}

	public function isValid ($value, $name, $key = false) {
		$validatorName = $this->getConfig()->getValidator($this->validator);

		$descriptor = array(
			'options' => $this->options
		);

		if (!is_null($this->message)) {
			$descriptor['message'] = $this->message;
		}

		$validator = new $validatorName($descriptor, $this->element);

		$valid = $validator->isValid($value, $this->context, $name, $key);

		if (!$valid) {
			$this->errors = $validator->getErrors();

			return false;
		}

		return true;
	}

	public function getErrors () {
		return $this->errors;
	}

	public function setConfig (\Waxis\Form\Form\Config $config) {
		$this->config = $config;
	}

	public function getConfig () {
		if (!$this->config) {
			if (file_exists(app_path() . '/Configs/Form.php')) {
				$config = new \App\Configs\Form;
			} else {
				$config = new Config;
			}
			$this->config = $config;
		}

		return $this->config;
	}

	public function getConfigVar ($var) {
		$config = $this->getConfig();

		return $config->$var;
	}
}