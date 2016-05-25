<?php

namespace App\Descriptors\Form;

class Example {
	
	public function descriptor () {
		return array(
			'id' => 'example',
			'elements' => [
				[
					'name' => 'text',
					'label' => 'Text',
				]
			]
		);
	}
}