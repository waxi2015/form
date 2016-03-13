<?php

namespace Waxis\Form;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FormController extends Controller
{
	public function __construct (Request $request) {
		if (isset($request->locale)) {
			\Lang::setLocale($request->locale);
		}
	}
	
	public function upload(Request $request)
	{
		$descriptor = unserialize(decode($request->formDescriptor));

		$form = new \Form($descriptor);

		$element = $form->getElementByName($request->element);

		if (!$form->isPermitted()) {
			return [];
		}

		switch ($request->type) {
			case 'image':
				$path = 'uploads/images/' . $element->getImageDescriptor();
				$file = $request->file('image');
				$filename = md5($file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
				break;

			case 'file':
				$path = $form->getConfig()->getUpload('file') . $element->getFolder();
				$file = $request->file('file');
				$filename = $file->getClientOriginalName();

				if (file_exists($path . $filename) && config('app.env') == 'production') {
					return response(trans('form.file_exits_with_name'), 500);
				}

				break;

			default:
				return false;
		}

		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}

		$file->move($path, $filename);

		return [
			'file' => $filename,
			'path' => '/' . $path
		];
	}

    public function validateform (Request $request) {
		$rp = $request->all();

		$descriptor = unserialize(decode($rp['formDescriptor']));

		$form = new \Form($descriptor, $rp);

		$idElement = $form->getIdElement();

		$id = isset($rp[$idElement]) && !empty($rp[$idElement]) && !is_int($rp[$idElement]) ? decode($rp[$idElement]) : false;

		if (!$form->isPermitted($id)) {
			return [];
		}

		$response = array(
			'valid' => false
		);
		// itt megadható akár más adat is, mint ami kezdetben az rp-ben ment át a WX_Form 2. paramétereként
		if ($form->isValid()) {
			$response['valid'] = true;

			$result = $form->runOnValid();

			if ($result === true || $result['valid'] == true){
				if ($form->isEmptyOnValid()) {
					$form->emptyFields();
				}
			} else {
				$response['valid'] = false;
			}
			
			if (isset($result['message'])) {
				$response['message'] = $result['message'];
			}
		}
		$response['html'] = $form->fetch();

		return $response;
	}

	public function cloning (Request $request) {
		$rp = $request->all();

		$error = false;
		$html = false;

		$form = new \Form(unserialize(decode($rp['formDescriptor'])), array(), true);

		if (!$form->isPermitted()) {
			return [];
		}

		$clone = $form->getClone($rp['nodeTree'], $rp['clones']);
		$clone->setId(null, true);

		// This is required for the clones to see default element values
		// when checking for conditional default values
		$nth = (int) $rp['clones'] + 1;
		foreach ($form->getElements() as $element) {
			$form->setRequestParam($element->getName(false), $element->getValue(), $nth);
		}

		if (!$clone) {
			$error = 'Cloning failed.';
		} else {
			$html .= $clone->fetch();
			$html .= $form->fetchJavascript('condition.phtml', array('elements' => $clone->getElements(), 'hideSelector' => '.wx-element'));
			$html .= $form->fetchJavascript('condition.phtml', array('elements' => $clone->getColumns(), 'hideSelector' => '.wx-column'));
			$html .= $form->fetchJavascript('condition.phtml', array('elements' => $clone->getRows(), 'hideSelector' => '.wx-row'));
			$html .= $form->fetchJavascript('condition.phtml', array('elements' => $clone->getBcolumns(), 'hideSelector' => '.wx-bcolumn'));
			$html .= $form->fetchJavascript('condition.phtml', array('elements' => $clone->getBrows(), 'hideSelector' => '.wx-brow'));
			$html .= $form->fetchJavascript('condition.phtml', array('elements' => $clone->getSections(), 'hideSelector' => '.wx-section'));
			$html .= $form->renderCapturedScript();
		}

		$response = array(
			'error' => $error,
			'html' => $html
		);

		return $response;
	}

	public function suggest (Request $request) {
		$rp = $request->all();

		$query = $rp['query'];

		$form = new \Form(unserialize(decode($rp['descriptor'])), $rp);

		if (!$form->isPermitted()) {
			return [];
		}

		$element = $form->getElementByName($rp['element']);

		$items = $element->convertItemsToAutocompleteFormat($element->getItems($rp['query'], $element->getSourceParamsWithDynamicValues($rp)));

		return $items;
	}

	public function prefetch (Request $request) {
		$rp = $request->all();

		$form = new \Form(unserialize(decode($rp['descriptor'])), $rp);

		if (!$form->isPermitted()) {
			return [];
		}

		$element = $form->getElementByName($rp['element']);

		$items = $element->convertItemsToAutocompleteFormat($element->getItems());

		return $items;
	}

	public function loaddata (Request $request) {
		$rp = $request->all();

		$form = new \Form(unserialize(decode($rp['descriptor'])), $rp);

		if (!$form->isPermitted()) {
			return [];
		}

		$element = $form->getElementByName($rp['element']);
		$element->setRequestParams($rp);

		$params = array();
		foreach ($element->getSourceParams() as $key => $param) {
			if (strpos($param, '%') !== false) {
				$params[$key] = $rp[str_replace('%', '', $param)];
			} else {
				$params[$key] = $param;
			}
		}

		$data = call_user_func_array(array($element->getSource('class'), $element->getSource('method')), $params);

		return $data;
	}

	public function validationremote (Request $request) {
		$rp = $request->all();

		$form = new \Form(unserialize(decode($rp['descriptor'])), $rp);

		if (!$form->isPermitted()) {
			return [];
		}

		$element = $form->getElementByName($rp['element']);

		$valid = false;
		foreach ($element->validators as $validator => $options) {
			if ($validator === 'remote') {
				$key = $rp['key'] == '' ? null : $rp['key'];
				$value = $element->getRequestParam($rp['element'], $key);

				$validatorClass = new \Waxis\Form\Form\Validator($validator, $options, $element->getRequestParams(), $element);
				$valid = $validatorClass->isValid($value, $element->getName(false), $element->getValueKey());
			}
		}

		$return = ['valid' => $valid];

		return $return;
	}
}
