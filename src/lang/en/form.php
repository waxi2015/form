<?php

return [

	'success_msg_title' => 'Message',	
	'error_msg_title' => 'Error',	
	'error_during_save' => 'Process failed, please try again.',	
	'file_exits_with_name' => 'There is already a file named like this.',	

	'button_file_upload' => 'Upload file',	
	'button_files_upload' => 'Upload files',	
	'button_image_upload' => 'Upload picture',	
	'button_image_delete' => 'Delete picture',	
	'button_images_upload' => 'Upload pictures',	
	'button_images_delete' => 'Delete pictures',	

	'autocomplete_no_results' => 'No results found',

	'validators' => [
		'autcomplete_msg' => 'Please choose one from the list',
		'digits_msg' => 'Please fill in only numbers',
		'numeric_msg' => 'Please enter a valid number',
		'email_msg' => 'Wrong email address',
		'identical_msg' => 'Fields do not match',
		'remote_msg' => 'It is already taken',
		'uri_msg' => 'Wrong URL',
		'vat_msg' => 'Wrong VAT number',
		'regexp_msg' => 'Wrong input',

		'empty' => [
			'empty' => 'Please fill in this field',
			'emptyAll' => 'Please fill in all the fields',
			'emptySelect' => 'Please choose one from the list',
			'emptyMultiselect' => 'Please choose at least one',
			'emptyCheckbox' => 'Please check this field',
			'emptyCheckboxgroup' => 'Please check at least one field',
			'emptyRadio' => 'Please select one',
			'emptyRadiogroup' => 'Please select one',
			'emptyTags' => 'Please select at least one from the list',
			'autocompleteNew' => 'Please select one from the list',
		]
	],

	'decorators' => [
		'charlimit_text' => ' characters left',
		'wordlimit_text' => ' words left',
	]
];