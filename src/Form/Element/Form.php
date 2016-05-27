<?php

namespace Waxis\Form\Form\Element;

class Form extends Ancestor {

	public $type = 'form';

	public $nodeLevel = 0;

	public $sections = array();

	public $action = null;
	
	public $method = 'AJAX';

	public $emptyOnValid = false;

	public $onValid = null;

	public $load = null;

	public $init = true;

	public $submit = null;

	public $dataId = null;

	public $table = null;

	public $converters = null;

	public $idElement = 'id';

	public $idField = 'id';

	public $before = null;

	public $after = null;

	public $save = null;

	public $reload = false;

	public $feedback = null;
	
	public $permission = null;

	public $ownerField = null;

	public $preparedData = null; # this is the final data, after converting, etc.

	public $steps = null;

	public function __construct ($descriptor) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($descriptor['id'])) {
			$this->registryNamespace = $descriptor['id'];
			$this->id = $descriptor['id'];
			$this->formIdentifier = $descriptor['id'];
		} else {
			throw new \Exception('Form ID must be defined.');
		}

		if (!isset($descriptor['sections']) && empty($this->sections)) {
			$this->_setDefaultSections();
		}

		if (isset($descriptor['table']) && $this->table === null) {
			$this->table = $descriptor['table'];
		}

		if (isset($descriptor['converters']) && $this->converters === null) {
			$this->converters = $descriptor['converters'];
		}

		if (isset($descriptor['reload'])) {
			$this->reload = $descriptor['reload'];
		}

		if (isset($descriptor['save'])) {
			$this->save = $descriptor['save'];
		}

		if (isset($descriptor['before'])) {
			$this->before = $descriptor['before'];
		}

		if (isset($descriptor['after'])) {
			$this->after = $descriptor['after'];
		}

		if (isset($descriptor['feedback'])) {
			$this->feedback = $descriptor['feedback'];
		}

		if (isset($descriptor['init'])) {
			$this->init = $descriptor['init'];
		}

		if (isset($descriptor['submit'])) {
			$this->submit = $descriptor['submit'];
		}

		if (isset($descriptor['action'])) {
			$this->action = $descriptor['action'];
		}

		if (isset($descriptor['method'])) {
			$this->method = $descriptor['method'];
		}

		if (isset($this->descriptor['class'])) {
			$this->class = $this->descriptor['class'];
		}

		if (isset($descriptor['templateDirectory'])) {
			$this->templateDirectory = $descriptor['templateDirectory'];
		}

		if (isset($descriptor['template'])) {
			$this->template = $descriptor['template'];
		}

		if (isset($descriptor['vars'])) {
			$this->vars = $descriptor['vars'];
		}

		if (isset($descriptor['emptyOnValid'])) {
			$this->emptyOnValid = $descriptor['emptyOnValid'];
		}

		if (isset($descriptor['onValid'])) {
			$this->onValid = $descriptor['onValid'];
		}

		if (isset($descriptor['permission'])) {
			$this->permission = $descriptor['permission'];
		}

		if (isset($descriptor['ownerField'])) {
			$this->ownerField = $descriptor['ownerField'];
		}

		if (isset($descriptor['onValid'])) {
			$this->onValid = $descriptor['onValid'];
		}

		if (isset($descriptor['filters'])) {
			$this->filters = array_merge($this->filters, $descriptor['filters']);
		}

		if (isset($descriptor['load']) && $this->load === null) {
			$this->load = $descriptor['load'];

			if (!empty($descriptor['load'])) {
				$this->load = true;
			}
		}

		if (isset($descriptor['steps'])) {
			$this->steps = $descriptor['steps'];
		}

		if (isset($descriptor['viewMode'])) {
			$this->viewMode = $descriptor['viewMode'];
		}

		$this->setSections();

		parent::__construct($descriptor);
	}

	public function renderLanguageSelectors () {
		if (!$this->isMultilingual()) {
			return;
		}

		$languages = config('locale.languages');

		return $this->renderTemplate('languages');
	}

	public function getLanguages () {
		return config('locale.languages');
	}

	public function getSteps () {
		return $this->steps;
	}

	public function renderSteps () {
		if ($this->getSteps() === null) {
			return;
		}

		return $this->renderTemplate('steps');
	}

	public function getPermission () {
		return $this->permission;
	}

	public function isPermitted ($recordId = false) {
		# @todo: megírni

		if ($this->getPermission() === null) {
			return true;
		}

		if (\Auth::guard($this->getPermission())->check()) {

			if ($this->getOwnerField() !== null && $recordId) {
				$result = collect(\DB::table($this->getTable())
					->where('id', $recordId)->first())->toArray();

				if (isset($result[$this->getOwnerField()]) && $result[$this->getOwnerField()] == \Auth::guard('admin')->user()->id) {
					return true;
				}

				return false;
			} else {
				return true;
			}
		}

		return false;
	}

	public function getOwnerField () {
		return $this->ownerField;
	}

	public function setDefaultValuesToRequestParams () {
		foreach ($this->getElements() as $element) {
			$element->setDefaultValueToRequestParam();
		}
	}

	public function loadData ($raw = array(), $reload = false) {
		if ($this->isCloneRequest) {
			return false;
		}

		$params = $data = array();
		if (empty($raw) && $this->dataId !== null) {
			$raw = $this->getDataFromDb();
		}

		if (empty($raw)) {
			$raw = array();
		}

		# (for translator) array of rows with the same connector
		$relatives = [];

		if ($this->isMultilingual() && isset($raw['connector'])) {
			$relatives = json_decode(json_encode((array) \DB::table($this->getTable())
				->where('connector', $raw['connector'])->get()), true);
		}

		foreach ($raw as $name => $value) {
			$element = $this->getElementByName($name);

			if ($this->isMultilingual() && $element->translate && isset($raw['connector']) && $name != $this->idField) {
				$value = [];

				foreach ($relatives as $relative) {
					$relativeValue = $relative[$name];

					if (!is_array($relativeValue) && isJson($relativeValue)) {
						$relativeValue = json_decode($relativeValue, true);
					}

					$translatedValues[$relative['language']] = $relativeValue;
				}

				$value = $translatedValues;
			}

			if ($name == $this->idField && $this->dataId !== null) {
				if (!empty($value) && !is_int($value)) {
					$value = encode($value);
				}
			}

			if (!is_array($value) && isJson($value)) {
				$decoded = json_decode($value, true);

				if (is_array($decoded)) {
					$decodedArrayTest = $decoded;

					reset($decodedArrayTest);
					$firstKey = key($decodedArrayTest);

					if (is_int($firstKey)) {
						$value = $decoded;
					}
				}
			}

			if (is_array($value)) {	
				$clones = count($value) - 1;

				if ($element->isCloneable) {
					$cloneName = $element->getCloneTree('-');

					if (!isset($params[$cloneName]) || (int) $params[$cloneName] < $clones) {
						$params[$cloneName] = $clones;
					}
				}
			}

			$data[$name] = $value;

			$this->isLoad = true;
		}
		if ($this->converters !== null && !empty($data)) {
			$data = $this->convertData($this->converters, $data);
		}

		$params = array_merge($params, $data);
		$this->setRequestParams($params);

		return $params;
	}

	public function getDataFromDb () {

		$id = $this->dataId;

		$results = collect(\DB::table($this->table)
					 ->whereRaw($this->idField . ' = "' . $id . '"')->first());

		return $results->toArray();
	}

	public function runOnValid () {
		# if we don't need job to be done
		if ($this->save === null && $this->after === null) {
			# if we have feedback for the result value
			if ($this->feedback !== null) {
				return $this->feedback['true'];
			} else {
				return array('valid' => true);
			}

		}

		$result = true;

		# run different data optimalizations before saving
		$this->preparedData = $this->prepareRawData();

		# if we should save
		if ($this->save !== null) {
			# if we should do something before saving
			if ($this->before !== null) {
				$this->runBefore();
			}

			# do multilingual converting
			if ($this->isMultilingual()) {
				$this->convertMultilingual('before');
			}
			

			$this->preparedData = $this->convertMultiDataToJson($this->preparedData);

			# save form data
			$result = $this->saveData();
		}
		# if it's a success
		if ($result == true) {
			# do multilingual converting
			if ($this->isMultilingual()) {
				$this->convertMultilingual('after');
			}

			# if we should do something after validation or saving
			if ($this->after !== null) {
				$after = $this->runAfter();

				# if after has return value
				if ($after !== null) {
					$result = $after;
				}
			}

		} else {
			# if could not be saved
			return [
				'valid' => false,
				'message' => trans('form.error_during_save')
			];
		}

		# if we need to reload data from db, after save / action
		if ($this->reload) {
			$this->reloadData();
		}

		# if we have feedback for the result value
		if ($this->feedback !== null) {
			if (is_bool($result)) {
				$result = trim(json_encode($result),'"');
			}
			return $this->feedback[$result];
		}
		
		return $result;
	}

	public function prepareRawData () {
		# get the form data
		$data = $this->fetchData();

		# convert if needed
		if (is_array($this->save) && isset($this->save['converters'])) {
			$data = $this->convertData($this->save['converters'], $data);
		}

		# do some more converting, required to work properly
		$data = $this->convertMultiDataToJson($data);
		$data = $this->replaceHtmlQuotesToOriginal($data);

		# decode the id
		if (isset($data[$this->idElement]) && !empty($data[$this->idElement])) {
			$data[$this->idElement] = decode($data[$this->idElement]);
		}

		return $data;
	}

	public function reloadData () {
		if ($this->dataId === null) {
			$this->dataId = \DB::getPdo()->lastInsertId();
		} elseif (!is_numeric($this->dataId)) {
			$this->dataId = decode($this->dataId);
		}

		$this->isPost = false;

		$this->loadData(array(), true);
	}

	public function saveData () {
		$data = $this->preparedData;

		$action = 'insert';

		$id = isset($data[$this->idField]) && !empty($data[$this->idField]) ? $data[$this->idField] : null;

		if ($id !== null) {
			$this->dataId = $id;
			$action = 'update';
		}

		# remove nosave data from data
		# so that we can pass variables from
		# before events to after events
		# without effecting the query
		$dbData = $data;
		unset($dbData['nosave']);

		try {
			switch ($action) {
				case 'insert':
					\DB::table($this->table)->insert($dbData);

					$id = \DB::getPdo()->lastInsertId();
					$data[$this->idField] = $id;
					$this->dataId = encode($id);
					$this->setRequestParam($this->idField, $this->dataId);
					break;

				case 'update':
					\DB::table($this->table)->where($this->idField, $id)->update($dbData);
					break;
			}

			$result = true;
		} catch (\Exception $e) {
			//DX($e->getMessage());
			$result = false;
		}

		// Run external saving, eg for connection tables
		// for example: tags
		if ($result == true) {
			foreach ($this->sections as $key => $one) {
				$one->saveExternalData();
			}
		}

		$this->preparedData = $data;

		return $result;
	}

	public function convertMultilingual ($method) {
		$obj = [
			'class' => '\Waxis\Form\Form\Converter\Multilingual',
			'method' => $method,
			'updateData' => true
		];

		$this->runMethod($obj);
	}

	public function runBefore () {
		if (empty($this->before)) {
			return;
		}

		$data = null;
		foreach ($this->before as $one) {
			$this->runMethod($one, $data);

			if ($one['updateData']) {
				$data = $this->preparedData;
			}
		}
	}

	public function runAfter () {
		if (empty($this->after)) {
			return;
		}

		$return = null;
		foreach ($this->after as $one) {
			$data = null;

			# this will pass exactly the data
			# that is received by insert/update queries
			# + nosave parameter so that before and after
			# can communicate
			if (isset($one['workWithSaveData']) && $one['workWithSaveData'] == true) {
				$data = $this->preparedData;
			}

			$return = $this->runMethod($one, $data);
		}

		return $return;
	}

	public function runMethod ($obj, $data = null) {
		if ($data === null) {
			$data = $this->prepareRawData();
		}

		if (is_array($obj)) {
			$class = $obj['class'];
			$method = $obj['method'];
			$static = getValue($obj, 'static');
			$instantiate = getValue($obj, 'instantiate');
			$converters = getValue($obj, 'converters');
			$params = getValue($obj, 'params', array());
			$updateData = getValue($obj, 'updateData', array());

			if (!empty($converters)) {
				$data = $this->convertData($converters, $data);
			}

			if ($static) {
				$result = $class::$method($data, $params, $this);
			} elseif ($instantiate) {
				$result = $class::getInstance()->$method($data, $params, $this);
			} else {
				$instance = new $class($data, $params, $this);
				$result = $instance->$method($data, $params, $this);
			}

			if ($updateData) {
				$this->preparedData = $result;
			}
		} else {
			$obj = explode('::', $obj);
			$result = $obj[0]::$obj[1]($data, array(), $this);
		}

		return $result;
	}

	public function getCloneTreeByName ($name) {
		if (!isset($this->descriptor['sections'])) {
			$this->_setDefaultSections();
		}

		$this->setSections();

		$element = $this->getElementByName($name);

		if (!$element->isCloneable) {
			$level = $element->getNodeLevel();
			$return = array();
			for ($i = $level; $i >= 0; $i--) {
				$parent = $element->getParents($i);

				if (!empty($parent)) {
					$return[$parent['nodeLevel']] = $parent['nth'];
				}
			}
			//$return[$element->getNodeLevel()] = $element->nth;

			$tree = implode('-', $return);
		} else {
			$tree = $element->getNodeTreeString('-');
		}

		return 'clone-' . $tree;
	}

	// must return with the right keys
	public function convertData ($converters, $data) {
		foreach ($converters as $converter) {
			$converter = explode('::', $converter);
			$converterClass = $converter[0];
			$converterMethod = $converter[1];

			$data = $converterClass::$converterMethod($data, $this);
		}

		return $data;
	}

	public function replaceHtmlQuotesToOriginal ($data) {
		$return = array();

		foreach ($data as $key => $value) {
			$return[$key] = null === $value ? $value : str_replace('&quot;','"',$value);
		}

		return $return;
	}

	public function convertMultiDataToJson ($data) {
		$return = array();

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$return[$key] = json_encode($value, JSON_FORCE_OBJECT);
			} else {
				$return[$key] = $value;
			}
		}

		return $return;
	}

	public function getAction () {
		return $this->action;
	}

	public function getElementByName ($name) {
		if (empty($this->sections)) {
			if (!isset($this->descriptor['sections'])) {
				$this->_setDefaultSections();
			}

			$this->setSections();
		}

		$element = false;

		foreach ($this->sections as $section) {
			foreach ($section->brows as $brow) {
				foreach ($brow->bcolumns as $bcolumn) {
					foreach ($bcolumn->rows as $row) {
						foreach ($row->columns as $column) {
							foreach ($column->elements as $element) {
								if ($element->name == $name) {
									return $element;
								}
							}
						}
					}
				}		
			}
		}

		return $element;
	}

	public function getClone ($nodeTree, $clones = 0) {
		$treeParts = explode('-', $nodeTree);
		$nodeLevel = count($treeParts) - 1;

		switch ($nodeLevel) {
			case 1:
				$clone = $this
						->sections[$treeParts[1]];
				break;

			case 2:
				$clone = $this
						->sections[$treeParts[1]]
						->brows[$treeParts[2]];
				break;

			case 3:
				$clone = $this
						->sections[$treeParts[1]]
						->brows[$treeParts[2]]
						->bcolumns[$treeParts[3]];
				break;

			case 4:
				$clone = $this
						->sections[$treeParts[1]]
						->brows[$treeParts[2]]
						->bcolumns[$treeParts[3]]
						->rows[$treeParts[4]];
				break;

			case 5:
				$clone = $this
						->sections[$treeParts[1]]
						->brows[$treeParts[2]]
						->bcolumns[$treeParts[3]]
						->rows[$treeParts[4]]
						->columns[$treeParts[5]];
				break;

			case 6:
				$clone = $this
						->sections[$treeParts[1]]
						->brows[$treeParts[2]]
						->bcolumns[$treeParts[3]]
						->rows[$treeParts[4]]
						->columns[$treeParts[5]]
						->elements[$treeParts[6]];
				break;

			default:
				$clone = false;
				break;
		}

		$clone->isClone = true;
		$clone->isRemovable = true;
		$clone->nthClone = $clones + 1;
		$clone->isCloneRequest = true;
		$clone->setChildren();
		$clone->setPropertiesToChildrenRecursive(array('isCloneRequest' => true), $clone);

		//D($clone->nthClone);

		return $clone;
	}

	public function setValues ($values = null) {
		foreach ($this->sections as $one) {
			$one->setValues($values);
		}
	}

	public function isValid ($values = null) {
		$this->setValues($values);
		$this->isPost = true;

		$valid = true;
		foreach ($this->sections as $one) {
			$one->isPost = true;
			$isValid = $one->isValid();
			if (!$isValid) {
				$valid = false;
			}
		}

		return $valid;
	}

	public function setSections () {
		$this->sections = array();

		foreach ($this->descriptor['sections'] as $nth => $one) {
			$this->addSection($one, $nth);
		}
	}

	/*public function setValues ($values) {
		foreach ($this->sections as $one) {
			$one->setValues($values);
		}
	}*/

	public function renderSections () {
		$this->renderObjects($this->sections);
	}

	public function _setDefaultSections () {

		$descriptor = $this->descriptor;

		$descriptor['sections'] = array();

		if (isset($descriptor['brows'])) {
			$descriptor['sections'][] = array(
				'brows' => $descriptor['brows']
			);
			unset($descriptor['brows']);
		} elseif (isset($descriptor['bcolumns'])) {
			$descriptor['sections'][] = array(
				'bcolumns' => $descriptor['bcolumns']
			);
			unset($descriptor['bcolumns']);
		} elseif (isset($descriptor['rows'])) {
			$descriptor['sections'][] = array(
				'rows' => $descriptor['rows']
			);
			unset($descriptor['rows']);
		} elseif (isset($descriptor['columns'])) {
			$descriptor['sections'][] = array(
				'columns' => $descriptor['columns']
			);
			unset($descriptor['columns']);
		} elseif (isset($descriptor['elements'])) {
			$descriptor['sections'][] = array(
				'elements' => $descriptor['elements']
			);
			unset($descriptor['elements']);
		} else {
			throw new \Exception('At least "Elements" should be specified at Form.');
		}

		$this->descriptor = $descriptor;
	}

	public function getMethod () {
		return $this->method;
	}

	public function createFormId () {
		return md5(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(1,10)));
	}

	public function addSection ($section, $nth = 0) {
		$sectionObj = new Section($section, $nth, [
			'isLoad' => $this->isLoad,
			'multilingual' => $this->multilingual,
		]);

		$sectionObj->filters = $this->filters;
		$sectionObj->formId = $this->formId;
		$sectionObj->formIdentifier = $this->formIdentifier;
		//$sectionObj->descriptorName = $this->getDescriptorName();
		$sectionObj->formDescriptor = $this->formDescriptor;
		$sectionObj->viewMode = $this->viewMode;
		$sectionObj->setTemplateDirectory($this->getTemplateDirectory());
		$sectionObj->registryNamespace = $this->registryNamespace;
		$sectionObj->setParent($this);
		$sectionObj->setBrows();
		$sectionObj->filters = $this->filters;

		$clones = $sectionObj->getDefaultClones();
		$clonesFromRequest = $sectionObj->getClonesCountFromRequest();

		if ($clonesFromRequest > $clones) {
			$clones = $clonesFromRequest;
		}

		$sectionObj->clones = $clones;
		$sectionObj->setId();

		$this->sections[] = $sectionObj;

		for ($c = 1; $c <= $sectionObj->clones; $c++) {

			$sectionCloneObj = clone $sectionObj;
			$sectionCloneObj->nthClone = $c;
			$sectionCloneObj->isClone = true;

			if ($c > $sectionObj->getDefaultClones()) {
				$sectionCloneObj->isRemovable = true;
			}

			$sectionCloneObj->setBrows();
			
			$this->sections[] = $sectionCloneObj;
		}

		return $this->sections;
	}

	public function isTest () {
		if (\Registry::isRegistered('test-env')) {
			return \Registry::isRegistered('test-env');
		}

		return false;
	}

	public function isEmptyOnValid () {
		return $this->emptyOnValid;
	}

	public function emptyFields () {
		foreach ($this->sections as $key => $section) {
			$section->clones = $section->getDefaultClones();
			if ($section->isRemovable) {
				unset($this->sections[$key]);
			} else {
				$section->emptyFields();	
			}
		}
	}

	public function fetchData () {
		$data = array();

		foreach ($this->sections as $key => $one) {
			$data = array_replace_recursive($data, $one->fetchData());
		}
		
		return $data;
	}

	public function isInit () {
		return $this->init;
	}

	public function getSubmit () {
		return $this->submit;
	}

	public function getDescriptor () {
		return $this->formDescriptor;
	}

	public function getIdField () {
		return $this->idField;
	}

	public function getIdElement () {
		return $this->idElement;
	}

	public function getTable () {
		return $this->table;
	}
}