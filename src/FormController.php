<?php

namespace Waxis\Form;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FormController extends Controller
{
	public function upload(Request $request)
	{
		$descriptor = unserialize(decode($request->formDescriptor));

		$form = new \Form($descriptor);

		$element = $form->getElementByName($request->element);

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
					return response('Már létezik fájl ezzel a névvel.', 500);
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
			$this->_forward('error', null, null, array('errorCode' => 901));
			return false;
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

	public function clone (Request $request) {
		$rp = $request->all();

		$error = false;
		$html = false;

		$form = new \Form(unserialize(decode($rp['formDescriptor'])), array(), true);

		if (!$form->isPermitted()) {
			$this->_forward('error', null, null, array('errorCode' => 901));
			return false;
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
			$this->_forward('error', null, null, array('errorCode' => 901));
			return false;
		}

		$element = $form->getElementByName($rp['element']);

		$items = $element->convertItemsToAutocompleteFormat($element->getItems($rp['query'], $element->getSourceParamsWithDynamicValues($rp)));

		return $items;
	}

	public function prefetch (Request $request) {
		$rp = $request->all();

		$form = new \Form(unserialize(decode($rp['descriptor'])), $rp);

		if (!$form->isPermitted()) {
			$this->_forward('error', null, null, array('errorCode' => 901));
			return false;
		}

		$element = $form->getElementByName($rp['element']);

		$items = $element->convertItemsToAutocompleteFormat($element->getItems());

		return $items;
	}

	public function loaddata (Request $request) {
		$rp = $request->all();

		$form = new \Form(unserialize(decode($rp['descriptor'])), $rp);

		if (!$form->isPermitted()) {
			$this->_forward('error', null, null, array('errorCode' => 901));
			return false;
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
			$this->_forward('error', null, null, array('errorCode' => 901));
			return false;
		}

		$element = $form->getElementByName($rp['element']);

		$valid = false;
		foreach ($element->validators as $validator => $options) {
			if ($validator == 'remote') {
				$key = $rp['key'] == '' ? null : $rp['key'];
				$value = $element->getRequestParam($rp['element'], $key);

				$validatorClass = new \Waxis\Form\Form\Validator($validator, $options, $element->getRequestParams(), $element);
				$valid = $validatorClass->isValid($value, $element->getName(false), $element->getValueKey());
			}
		}

		$return = ['valid' => $valid];

		return $return;
	}

	/*public function deletefileAction (Request $request) {
		$rp = $request->all();

		$file = UPLOAD_PATH . $rp['type'] . '/' . $rp['hash'] . '.' . $rp['ext'];
		$fileTemp = UPLOAD_TEMP_PATH . $rp['type'] . '/' . $rp['hash'] . '.' . $rp['ext'];

		if (file_exists($file)) {
			unlink($file);
		} else if (file_exists($fileTemp)) {
			unlink($fileTemp);
		}

		if (isset($rp['table']) || isset($rp['dbModel'])) {
			$modelParts = [];

			if (isset($rp['dbModel'])) {
				$modelParts = explode('_', $rp['dbModel']);
			}

			$table = isset($rp['table']) ? $rp['table'] : strtolower($modelParts[key($modelParts)]);
			$field = isset($rp['field']) ? $rp['field'] : $rp['dbField'];

			$db = Zend_Registry::get('db');
			$db->update($table, array($field => null), 'id = "'.$rp['id'].'"');
		}

		$response = WX_Ajax_Response_Json::success();
		die($response);
	}

	public function uploadfileAction (Request $request) {
		$rp = $request->all();

		if (isset($_FILES['upload-file'])) {
			$file = $_FILES['upload-file'];
			$fileinfo = pathinfo($file['name']);

			$name = $fileinfo['filename'];
			$ext = $fileinfo['extension'];
			$hash = MD5(rand(5, 15) . time() . $name);

			$directory = $rp['directoryPath'];

			if (!file_exists($directory)) {
				WX_Directory_Writer::getInstance()->makeDirectory($directory, 0777);
			}

			if (move_uploaded_file($file['tmp_name'], $directory . '/' . $hash . '.' . $ext)) {
				$type = $rp['type'];
				$file = $name . '.' .$ext;
				// ez koráttban UPLOAD_TEMP_URL volt
				$path = UPLOAD_URL . $type;
				$responseData = array(
					'file' 			=> $file,
					'name'			=> $name,
					//'downloadLink'=> $rp['downloadScript'] . '/' . $type . '/' . $hash . '/' . $name . '/' . $ext,
					'downloadLink'	=> $path . '/' . $hash . '.' . $ext,
					'hash' 			=> $hash,
					'ext'			=> $ext
				);

				$response = WX_Ajax_Response_Json::success($responseData);
			} else {
				$message = t('ERROR_UNEXPECTED');
				$response = WX_Ajax_Response_Json::error($message);
			}

			die($response);
		}
	}

	public function uploadtempimageAction() {
		$this->uploadimageAction(true);
	}

	public function uploadimageAction($isTemp = false, Request $request) {
		$rp = $request->all();

		$type = WX_Tools::getValue($rp, 'type');

		$response = WX_Ajax_Response_Json::error('-1');
		
		if (isset($_FILES['upload-image'])) {
			$file 	= $_FILES['upload-image'];
			if (WX_Tools::isValidImage($file['tmp_name'])) {
				$image 	= new WX_Image($type);

				if ($isTemp) {
					$image->uploadTemp($file);
				} else {
					$image->upload($file);
				}

				if ($image->hasErrors()) {
					$response = WX_Ajax_Response_Json::error($image->getErrors());
				} else {
					$feedbackSize = isset($rp['size']) ? $rp['size'] : false;
					$responseData = $image->get($feedbackSize);
					$response = WX_Ajax_Response_Json::success($responseData);
				}
			}

			die($response);
		}
	}

	public function uploadmultitempimageAction() {
		$this->uploadmultiimageAction(true);
	}

	public function uploadmultiimageAction($isTemp = false, Request $request) {
		$rp = $request->all();

		$type = $rp['type'];
		$feedbackSize = $rp['size'];

		$response = array();

		foreach ($_FILES as $file) {
			if (WX_Tools::isValidImage($file['tmp_name'])) {
				$image 	= new WX_Image($type);

				if ($isTemp) {
					$image->uploadTemp($file);
				} else {
					$image->upload($file);
				}

				if (!$image->hasErrors()) {
					$responseData = $image->get($feedbackSize);
					$response[] = $responseData;
				}
			}
		}

		if (count($response)) {
			$responseObj = WX_Ajax_Response_Json::success($response);
		} else {
			$responseObj = WX_Ajax_Response_Json::error();
		}

		die($responseObj);
	}

	public function deleteimageAction (Request $request) {
		$rp = $request->all();

		$response = WX_Ajax_Response_Json::success();

		if (WX_Tools::getValue($rp, 'type', false) === false) {
			$message = 'Missing "type" param';
			WX_Exception::create($message);

			return;
		}
		if (WX_Tools::getValue($rp, 'hash', false) === false) {
			$message = 'Missing "hash" param';
			WX_Exception::create($message);

			return;
		}
		$type = WX_Tools::getValue($rp, 'type');
		$hash = WX_Tools::getValue($rp, 'hash');
		$temp = WX_Tools::getValue($rp, 'temp', false);

		$image = WX_Image::getInstance($type);

		if ($temp === false) {
			$image->delete($hash);
		} else {
			$image->deleteTemp($hash);
		}

		$response = WX_Ajax_Response_Json::success();

		die($response);
	}*/
}
