<?php

Route::group(['middleware' => 'web'], function () {
	Route::post('/wx/form/validateform', 'Waxis\Form\FormController@validateform');
	Route::post('/wx/form/cloning', 'Waxis\Form\FormController@cloning');
	Route::post('/wx/form/suggest', 'Waxis\Form\FormController@suggest');
	Route::post('/wx/form/prefetch', 'Waxis\Form\FormController@prefetch');
	Route::post('/wx/form/loaddata', 'Waxis\Form\FormController@loaddata');
	Route::post('/wx/form/validationremote', 'Waxis\Form\FormController@validationremote');
	Route::post('/wx/form/upload', 'Waxis\Form\FormController@upload');
});