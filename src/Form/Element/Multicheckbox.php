<?php

namespace Waxis\Form\Form\Element;

class Multicheckbox extends Multiselect {

	public $type = 'multicheckbox';

	public $items = array();

	public $source = null;

	public $emptyMessageType = 'emptyMulticheckbox';
 }

 /*
 	More about multicheckbox:
 	http://davidstutz.github.io/bootstrap-multiselect
 */
