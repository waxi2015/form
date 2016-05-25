<?php

namespace Waxis\Form\Form\Element;

class Email extends Element {

	public $type = 'email';

	public $validators = array('emailAddress');
}