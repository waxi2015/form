<?php

namespace Waxis\Form\Form\Element;

class Tags extends Multiselect {

	public $type = 'tags';

	public $items = array();

	public $source = null;

	public $emptyMessageType = 'emptyTags';
 }

 /*
 	More about tags:
 	http://getfuelux.com/javascript.html#pillbox
 */
