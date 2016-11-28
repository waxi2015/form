<?php

namespace Waxis\Form\Form\Element;

class Element extends Ancestor {

	public $nodeLevel = 6;

	public $defaultValue = null;

	public $value = null;

	public $required = false;

	public $error = null;

	public $validators = array();

	public $decorators = array();

	public $label = null;

	public $isEmpty = null;

	public $emptyMessageType = 'empty';

	public $static = false;

	public $standalone = false;

	public $name = null;

	public $readonly = false;
	
	public $disabled = false;

	public $prefix = null;

	public $suffix = null;

	public $tooltip = null;

	public $info = null;

	public $placeholder = null;

	public $emptied = false;

	public $rel = null;
	
	public $translate = false;
	
	public $convert = null;
	
	public $load = true;

	public $descriptionClass = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if ($constructParams !== null) {
			foreach ($constructParams as $param => $value) {
				$this->$param = $value;
			}
		}

		$this->nth = $nth;
		
		if (isset($this->descriptor['default'])) {
			$this->setDefaultValue($this->descriptor['default']);
		}

		if (isset($this->descriptor['required'])) {
			$this->required = $this->descriptor['required'];
		}

		if (isset($this->descriptor['error'])) {
			$this->error = $this->descriptor['error'];
		}

		if (isset($this->descriptor['validators'])) {
			$validators = $this->descriptor['validators'];

			foreach ($validators as $key => $value) {
				if (is_array($value)) {
					$validator = $key;
				} else {
					$validator = $value;
				}

				$found = false;
				foreach ($this->validators as $vkey => $one) {
					if ((is_array($one) && $vkey == $validator) || $one == $validator) {
						$found = true;
					}
				}

				if (!$found) {
					$this->validators = $this->validators + array($key => $value);
				}

			}
		}

		if (isset($this->descriptor['filters'])) {
			$filters = $this->descriptor['filters'];

			foreach ($filters as $key => $value) {
				if (is_array($value)) {
					$filter = $value['filter'];
				} else {
					$filter = $value;
				}

				$found = false;
				foreach ($this->filters as $fkey => $one) {
					if ((is_array($one) && $one['filter'] == $filter) || $one == $filter) {
						$found = true;
						$foundKey = $fkey;
					}
				}

				if (!$found) {
					$this->filters[] = $value;
				} else {
					$this->filters[$foundKey] = $value;
				}
			}
		}

		if (isset($descriptor['decorators'])) {
			$this->decorators = $descriptor['decorators'];
		}

		if (isset($this->descriptor['label'])) {
			$this->label = $this->descriptor['label'];
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

		if (isset($descriptor['description'])) {
			$this->description = $descriptor['description'];
		}

		if (isset($descriptor['descriptionClass'])) {
			$this->descriptionClass = $descriptor['descriptionClass'];
		}
		
		if (isset($this->descriptor['standalone'])) {
			$this->standalone = $descriptor['standalone'];
		}
		
		if (isset($this->descriptor['name'])) {
			$this->name = $descriptor['name'];
		}
		
		if (isset($this->descriptor['readonly'])) {
			$this->readonly = $this->descriptor['readonly'];
		}
		
		if (isset($this->descriptor['disabled'])) {
			$this->disabled = $descriptor['disabled'];
		}
		
		if (isset($this->descriptor['prefix'])) {
			$this->prefix = $descriptor['prefix'];
		}
		
		if (isset($this->descriptor['suffix'])) {
			$this->suffix = $descriptor['suffix'];
		}
		
		if (isset($this->descriptor['tooltip'])) {
			$this->tooltip = $descriptor['tooltip'];
		}
		
		if (isset($this->descriptor['info'])) {
			$this->info = $descriptor['info'];
		}
		
		if (isset($this->descriptor['placeholder'])) {
			$this->placeholder = $descriptor['placeholder'];
		}
		
		if (isset($this->descriptor['rel'])) {
			$this->rel = $descriptor['rel'];
		}

		if (isset($this->descriptor['translate'])) {
			$this->translate = $descriptor['translate'];
		}

		if (isset($this->descriptor['convert'])) {
			$this->convert = $descriptor['convert'];
		}

		if (isset($this->descriptor['load'])) {
			$this->load = $descriptor['load'];
		}

		parent::__construct($descriptor, $nth);
	}

	public function getDescriptionClass () {
		return $this->descriptionClass;
	}

	public function getInstances () {
		if (!$this->isMultilingual() || !$this->translate) {
			return [null=>null];
		}

		$return = [];

		foreach (config('locale.languages') as $one) {
			$return[] = $one['iso2'];
		}

		return $return;
	}

	public function getLabelFor () {
		return $this->getId();
	}

	public function getPlaceholder () {
		if ($this->placeholder !== null) {
			return 'placeholder="' . trans($this->placeholder) . '"';
		}

		return false;
	}

	public function getLabel () {
		if ($this->label === null) {
			return null;
		}

		return trans($this->label);
	}

	public function getReadonly () {
		if ($this->readonly) {
			return 'readonly';
		}

		return false;
	}

	public function getRel () {
		if ($this->rel !== null) {
			return 'rel="' . $this->rel . '"';
		}

		return null;
	}

	public function getDisabled () {
		if ($this->disabled || $this->viewMode === true) {
			return 'disabled';
		} else {
			if ($this->hasCondition()) {
				foreach ($this->condition as $field => $value) {
					if (!$this->isConditionTrue($field, $value)) {
						return 'disabled';
					}
				}
				return false;
			} else {
				return false;
			}
		}
	}

	public function getItems () {
		return $this->items;
	}

	public function setValues ($values = null) {
		if ($values !== null) {
			$this->setRequestParams($values);
		}
	}

	public function getClass ($language = null) {
		$class = $this->class;

		if ($this->hasCondition()) {
			foreach ($this->condition as $field => $value) {
				if (!$this->isConditionTrue($field, $value)) {
					$class .= ' hidden';
				}
			}
		}

		if ($this->isMultilingual() && $this->translate) {
			$class .= ' wax-translatable';
		}

		return $class;
	}

	public function isValid () {
		$valid = true;
		$validate = true;
		$this->isPost = true;

		if (!is_null($this->condition)) {
			foreach ($this->condition as $field => $value) {
				if (!$this->isConditionTrue($field, $value)) {
					$validate = false;
				}
			}
		}

		if ($validate) {
			if ($this->required) {
				foreach ($this->getInstances() as $language) {
					if ($this->validateIfEmpty($language) !== true) {
						$valid = false;
					}
				}
			}
		}

		if ($valid) {
			if (($this->required && $validate) || !$this->isEmpty()) {
				foreach ($this->getInstances() as $language) {
					if ($this->checkValidators($this->validators, $language) !== true) {
						$valid = false;
					}
				}
			}
		}

		return $valid;
	}

	public function validateIfEmpty ($language = null) {
		if ($this->isEmpty($language)) {

			$error = $this->error === null ? $this->getConfig()->getMessage($this->emptyMessageType) : $this->error;
			$this->addError($error, $language);
			
			return false;
		}

		return true;
	}

	public function isEmpty ($language = null) {
		$value = $this->getValue(false, $language);

		if ($this->isMultilingual() && $this->translate && $language !== null) {
			if ($value === null || $value === '') {
				$this->isEmpty[$language] = true;
			} else {
				$this->isEmpty[$language] = false;
			}

			return $this->isEmpty[$language];
		} else {
			if ($value === null || $value === '') {
				$this->isEmpty = true;
			} else {
				$this->isEmpty = false;
			}

			return $this->isEmpty;
		}
	}

	public function checkValidators ($validators, $language = null) {
		$valid = true;

		if ($this->getDisabled() === false) {
			foreach ($validators as $key => $one) {
				if ((is_array($one) && !is_numeric($key)) || !is_array($one)) {
					if (is_array($one)) {
						$validatorName = $key;
						$options = $one;
					} else {
						$validatorName = $one;
						$options = array();
					}
					$validator = new \Waxis\Form\Form\Validator($validatorName, $options, $this->getRequestParams(), $this);

					if (!$validator->isValid($this->getValue(false, $language), $this->getName(false, $language), $this->getValueKey())) {
						$valid = false;

						$this->addError($validator->getErrors(), $language);
					}
				}
			}
		}

		// this would have been used to multi level validation but didn't work out easily
		/*if ($valid) {
			foreach ($validators as $key => $one) {
				if (is_array($one) && is_numeric($key)) {
					$valid = $this->checkValidators($one);
				}
			}
		}*/

		return $valid;
	}

	public function addError ($errors, $language = null) {
		if ($this->isMultilingual() && $this->translate && $language !== null) {
			if (is_array($errors)) {
				$this->errors[$language] = array_merge($this->errors[$language], $errors);
			} else {
				$this->errors[$language][] = $errors;
			}
		} else {
			if (is_array($errors)) {
				$this->errors = array_merge($this->errors, $errors);
			} else {
				$this->errors[] = $errors;
			}
		}
	}

	public function hasFrontValidators () {
		if (!$this->required && empty($this->validators)) {
			return false;
		}

		return true;
	}

	public function getFrontValidators () {
		$validators = array();

		if ($this->required) {
			$validators['notEmpty'] = array(
				'enabled' => true,
				'message' => trans($this->error !== null ? $this->error : $this->getConfigVar('messages')[$this->emptyMessageType])
			);
		}

		foreach ($this->validators as $key => $value) {
			$validator = is_array($value) ? $key : $value;
			$options = is_array($value) ? $value : array();

			$add = true;
			$newOptions = array();

			if (!isset($options['message'])) {
				$options['message'] = trans($this->getValidatorMessage($validator));
			}

			switch ($validator) {
				case 'remote':
					$newOptions['message'] = $options['message'];
					$newOptions['type'] = 'POST';
					$newOptions['url'] = '/wx/form/validationremote';
					$newOptions['data'] = [
						'id' => $this->getRequestParam('id'),
						'element' => $this->getName(false),
						'key' => $this->getValueKey(),
						'descriptor' => encode(serialize($this->formDescriptor)),
						'locale' => \Lang::getLocale(),
						'_token' => csrf_token()
					];
					unset($newOptions['class']);
					unset($newOptions['method']);
					$options = $newOptions;
					break;

				case 'identical':
					$key = $this->getValueKey();

					$field = $options['field'];

					$brackets = '';
					if ($key !== null) {
						$brackets = "[$key]";
					}

					$options['field'] = $field . $brackets;
					break;

				case 'autocomplete':
					$add = false;
					break;
			}

			if ($add) {
				$validators[$validator] = $options;
			}
		}

		return $validators;
	}

	public function getValidatorMessage ($validator) {
		$validatorClass = $this->getConfigVar('validators')[$validator];
		$validatorClass = new $validatorClass;
		return $validatorClass->message;

	}

	public function setValue ($language = null) {
		$value = null;

		if (!$this->isCloneRequest) {
			$nth = false;

			if ($this->isCloneable) {
				$nth = $this->nthClone;
			} elseif ($this->isParentCloneable()) {
				$parent = $this->getClosestParent(array('clone' => true));
				$nth = $parent['nthClone'];
			}

			$value = $this->getRequestParam($this->getName(false), $nth);

			if ($language !== null && $this->isMultilingual()) {
				$value = isset($value[$language]) ? $value[$language] : null;
			}
		}

		if ($value === null && !$this->isPost/* && !$this->isLoad*/) {
			$value = $this->defaultValue;
		}

		$this->value = $value;
	}

	public function setDefaultValue ($value) {
		$this->defaultValue = $value;
	}

	# $allowDefault will let getValue to return the default value
	# if it was otherwise false. This is required to return value
	# to the template but not to the save (/process) method.
	public function getValue ($allowDefault = false, $language = null) {
		/*if ($this->value === null && !$this->emptied) {
			$this->setValue();
		}*/
		# a WX_Form hívásakor minden példányosításra kerül és a value
		# értéket kap. Később, amikor a WX_Form példányosítása után
		# módosítunk a requestParams-on (pl. belekerül az id), akkor
		# nem fog eljutni a templatehez, mivel előzőleg már be lett
		# állítva a value értéke. Ezért kell mindig settelni.
		if (!$this->emptied) {
			$this->setValue($language);
		}

		if ($this->hasCondition() && ($this->isPost || $this->isLoad)) {
			$conditionsTrue = true;
			foreach ($this->condition as $field => $requiredValue) {
				if (!$this->isConditionTrue($field, $requiredValue)) {
					$conditionsTrue = false;
				}
			}

			if ($conditionsTrue) {
				return $this->value;
			} else {
				return $allowDefault ? $this->defaultValue : false;
			}

		} else {
			return $this->value;
		}
	}

	public function getFieldnameToValidate ($language = null) {
		return $this->getName(true, $language);
	}

	public function getName ($withSuffix = true, $language = null) {
		$name = $this->getParam('name');

		if ($language !== null && $this->translate && $this->isMultilingual()) {
			$name .= "[$language]";
		}

		$suffix = '';

		if ($withSuffix) {
			$valueKey = $this->getValueKey();
			$suffix = !is_null($valueKey) ? '['.$valueKey.']' : '';
		}



		return $name . $suffix;
	}

	public function getData ($language = null) {
		$data = $this->getParam('data');

		if (empty($data)) {
			$data = array();
		}

		if (!$this->standalone) {
			$data['tree'] = $this->getNodeTreeString('-', $language);
			$data['clone'] = trim(json_encode($this->isClone),'"');
			$data['clone-removable'] = $this->isClone && $this->isRemovable ? 'true' : 'false';
		}

		$return = '';
		foreach ($data as $key => $value) {
			$return .= ' data-' . $key . '="' . $value . '" ';
		}

		return $return;
	}

	public function getElementData ($language = null) {
		$data = array();

		if ($this->getDecorators() !== null) {
			foreach ($this->getDecorators() as $decorator) {
				$decorator = new \Waxis\Form\Form\Decorator($decorator['type'], $decorator);

				foreach ($decorator->getOptions() as $option => $value) {
					$data['decorator-' . $option] = trans($value);
				}
			}	
		}

		if ($this->isMultilingual() && $this->translate) {
			$data['language'] = $language;
		}

		$return = '';
		foreach ($data as $key => $value) {
			$return .= ' data-' . $key . '="' . $value . '" ';
		}

		return $return;
	}

	public function getPrefix () {
		if ($this->prefix === null && $this->suffix === null) {
			return false;
		}

		$return = '<div class="input-group">';

		if ($this->prefix !== null) {
			$return .= '<span class="input-group-addon">' . $this->prefix . '</span>';
		}

		return $return;
	}

	public function getSuffix () {
		if ($this->prefix === null && $this->suffix === null) {
			return false;
		}

		$return = '';

		if ($this->suffix !== null) {
			$return .= '<span class="input-group-addon">' . $this->suffix . '</span>';
		}

		$return .= '</div>';

		return $return;
	}

	public function getInfo () {
		if (empty($this->info)) {
			return false;
		}

		return trans($this->info);
	}

	public function getRequired () {
		if (!$this->required) {
			return false;
		}

		//return 'data-fv-notEmpty="true" data-fv-threshold="1"';
	}

	public function hasError ($language = null) {
		if ($this->isError($language)) {
			return 'has-feedback has-error';
		}

		return false;
	}

	public function getTooltip () {
		if ($this->tooltip === null) {
			return false;
		}

		return 'title="' . trans($this->tooltip) . '" data-toggle="tooltip" data-placement="bottom" data-trigger="focus"';
	}

	public function getAdditionalAttributes ($language = null) {
		return $this->getReadonly() . ' ' . $this->getDisabled() . ' ' . $this->getTooltip() . ' ' . $this->getRequired() . ' ' . $this->getRel() . ' ' . $this->getElementData($language);
	}

	public function emptyField () {
		$this->value = $this->defaultValue !== null ? $this->defaultValue : null;
		
		$this->emptied = true;
	}

	public function fetchData () {
		$nth = $this->getNthInstance();
		$name = $this->getName(false);
		$value = $this->getValue();

		if ($this->static) {
			$data = array();
		} elseif ($value !== null && !$this->disabled) {
			if ($nth === null) {
				$data[$name] = $this->convertData($this->filterData($value));
			} else {
				$data[$name][$nth] = $this->convertData($this->filterData($value));
			}
		} elseif ($this->disabled) {
			$data = array();
		} else {
			$data = array();
		}
		
		return $data;
	}

	public function saveExternalData ($data) {
		return true;
	}

	public function convertData ($data) {
		if ($this->convert === null) {
			return $data;
		}

		$converter = $this->convert;

		if (is_array($data)) {
			foreach ($data as $key => $one) {
				$return[$key] = $converter($one);
			}
		} else {
			$return = $converter($data);
		}


		return $return;
	}

	public function filterData ($data) {
		if (empty($data) || empty($this->filters)) {
			return $data;
		}

		$filter = new \Waxis\Form\Form\Filter($data);

		foreach ($this->filters as $key => $one) {
			if (is_array($one)) {
				$filterName = getValue($one, 'filter');
				$options = getValue($one, 'options');
			} else {
				$filterName = $one;
				$options = false;
			}

			$filter->$filterName($options);
		}

		return $filter->getData();
	}

	public function setDefaultValueToRequestParam () {
		$this->setRequestParam($this->getName(false), $this->defaultValue, $this->getNthInstance());
	}

	public function getDecorators () {
		return $this->decorators;
	}
}