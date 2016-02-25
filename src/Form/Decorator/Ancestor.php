<?php

namespace Waxis\Form\Form\Decorator;

class Ancestor {

	public $config = null;

	public $options = null;

	public function __construct ($options) {
		$this->options = $options;
	}

	public function getOptions () {
		return $this->options;
	}

	public function setConfig (\Waxis\Form\Form\Config $config) {
		$this->config = $config;
	}

	public function getConfig () {
		if (!$this->config) {
			$config = new \Waxis\Form\Form\Config;
			$this->setConfig($config);
		}

		return $this->config;
	}

	public function getConfigVar ($var) {
		$config = $this->getConfig();

		return $config->$var;
	}
}