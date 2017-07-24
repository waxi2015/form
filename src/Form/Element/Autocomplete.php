<?php

namespace Waxis\Form\Form\Element;

class Autocomplete extends Element {

	public $type = 'autocomplete';

	public $highlight = 'true';

	public $hint = 'true';

	public $minLength = 1;

	public $emptyHtml = 'empty.phtml';

	public $suggestionHtml = 'suggestion.phtml';

	public $allowNew = false;

	// store dynamic params for speed optimalization
	public $dynamicParams = null;

	public $keyAsValue = true;

	public $source = [
		'items' => null,
		'display' => 'value',
		'limit' => 5,
		'defaults' => false,
		'mode' => 'local',
	];

	public $items = null;

	public $onSelect = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = false) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($this->descriptor['highlight'])) {
			$this->highlight = var_export($this->descriptor['highlight'],true);
		}
		
		if (isset($this->descriptor['hint'])) {
			$this->hint = var_export($this->descriptor['hint'],true);
		}
		
		if (isset($this->descriptor['minLength'])) {
			$this->minLength = $this->descriptor['minLength'];
		}
		
		if (isset($this->descriptor['emptyHtml'])) {
			$this->emptyHtml = $this->descriptor['emptyHtml'];
		}
		
		if (isset($this->descriptor['suggestionHtml'])) {
			$this->suggestionHtml = $this->descriptor['suggestionHtml'];
		}
		
		if (isset($this->descriptor['keyAsValue'])) {
			$this->keyAsValue = $this->descriptor['keyAsValue'];
		}

		if (isset($this->descriptor['allowNew'])) {
			$this->allowNew = $this->descriptor['allowNew'];
		}

		if (!$this->allowNew) {
			$this->validators['autocomplete'] = array(
				'message' => $this->getConfig()->getMessage('autocompleteNew')
			);
		}
		
		if (isset($this->descriptor['source'])) {
			$source = $this->descriptor['source'];

			$this->source = array_merge($this->source, $source);

			// if you need defaults then the minLength must be 0 otherwise it will do nothing at 0 chars
			if (isset($source['defaults']) && $source['defaults']) {
				$this->minLength = 0;
			}

			// if the source is function or db the default mode should be prefetch
			if (!isset($source['mode']) && (isset($source['table']) || isset($source['class']))) {
				$this->source['mode'] = 'prefetch';
			}

			if (isset($source['searchIn']) && !is_array($source['searchIn'])) {
				$this->source['searchIn'] = array($this->descriptor['source']['searchIn']);
			}

			if (!isset($source['searchIn']) && isset($source['display']) && (isset($source['table']) || (isset($source['mode']) && $source['mode'] == 'remote'))) {
				$this->source['searchIn'] = array($this->source['display']);
			}
		}
		
		if (isset($this->descriptor['onSelect'])) {
			$this->onSelect = $this->descriptor['onSelect'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function getFieldnameToValidate ($language = null) {
		return $this->getName() . '[display]';
	}

	// $params: source params, filled with conditional param values
	public function getItems ($query = false, $params = array(), $forceRefresh = false) {
		if ($this->items !== null && !$forceRefresh) {
			return $this->items;
		}

		switch ($this->getMode()) {
			case 'local':
				$items = $this->getSource('items');
				break;

			case 'prefetch':
			case 'remote':
				switch ($this->getSourceType()) {
					case 'function':
						$items = $this->getItemsFromFunction($query, $params);
						break;

					case 'db':
						$items = $this->getItemsFromDb($query);
						break;
				}
				break;

		}

		$temp = [];
		foreach ($items as $key => $one) {
			$temp[$key] = is_object($one) ? (array) $one : $one;
		}
		$items = $temp;

		$this->items = $items;

		return $this->items;
	}

	public function isValueExist () {
		$params = $this->getSourceParams();

		$temp = array();
		foreach ($params as $key => $param) {
			if (!is_array($param) && strstr($param, '%')) {
				// if the autocomplete is cloneable the conditional field must be too
				$temp[$key] = $this->getRequestParam(getVarByPattern('/(%[a-zA-Z0-9]+)/', $param), $this->getNthInstance());
			} else {
				$temp[$key] = $param;
			}
		}
		$params = $temp;

		$value = strtolower($this->getValue());
		$items = $this->getItems(false, $params, true);

		$found = false;

		if (empty($items)) {
			$items = array();
		}

		foreach ($items as $key => $one) {
			if ($this->isKeyAsValue()) {
				if ($value == $key) {
					$found = true;
				}
			} else {
				if ($value == strtolower($one[$this->getSource('display')])) {
					$found = true;
				}
			}
		}

		return $found;
	}

	public function getItemsFromFunction ($query, $params) {
		$class = $this->getSource('class');
		$method = $this->getSource('method');
		$params = array_merge($params, array($query));

		if ($params) {
			$items = call_user_func_array(array($class, $method), $params);
		} else {
			$items = call_user_func(array($class, $method));
		}

		if ($this->getMode() == 'remote') {
			$items = $this->filterFunctionItems($items, $query);
		}

		return $items;
	}

	public function filterFunctionItems ($items, $query = false) {
		if (!$query) {
			return $items;
		}

		$temp = array();

		if (empty($items)) {
			$items = array();
		}

		foreach ($items as $key => $value) {
			if (is_array($value)) {
				$match = false;

				foreach ($value as $vkey => $vvalue) {
					if (in_array($vkey, $this->getSearchIn())) {
						if (strpos(strtolower($vvalue), strtolower($query)) !== false) {
							$match = true;
						}
					}
				}

				if ($match) {
					$temp[$key] = $value;
				}
			} else {
				if (strpos(strtolower($value), strtolower($query)) !== false) {
					$temp[$key] = $value;
				}
			}	
		}
		$items = $temp;

		return $items;
	}

	public function getSourceParamsWithDynamicValues ($request) {
		$params = array();

		if (!empty($this->getSourceParams())) {
		// if it has params
			foreach ($this->getSourceParams() as $key => $one) {
			// check out if any of the params is a variable

				if (!is_array($one) && strstr($one, '%')) {
					// if a param found with a wildcard 
					// it's value will be dynamic
					// which comes from an other field's value
					// through the request params
					$params[] = $request[getVarByPattern('/(%[a-zA-Z0-9]+)/', $one)];
				} else {
					$params[] = $one;
				}
			}
		} else {
			return array();
		}

		return $params;
	}

	public function getItemsFromDb ($query) {
		$table = $this->getSource('table');
		$order = $this->getSource('order');
		$fields = $this->getSource('fields') ? array_merge(array('id'),$this->getSource('fields')) : array('*');
		$where = $this->getSource('where');

		$dbQuery = \DB::table($table)->select($fields);

		if ($where) {
			$dbQuery->whereRaw($where);
		}

		if ($order) {
			$dbQuery->orderByRaw($order);
		}

		if ($this->getMode() == 'remote') {
			$searchIn = '';
			foreach ($this->getSearchIn() as $key => $field) {
				$searchIn .= "`$field` like '%$query%'" . (($key + 1) < count($this->getSearchIn()) ? ' || ' : '');
			}
			$dbQuery->whereRaw($searchIn);
		}

		$items = $dbQuery->get();

		foreach ($items as $one) {
			$one = (array) $one;
			$temp[$one['id']] = $one;
		}
		$items = $temp;

		return $items;
	}

	public function convertItemsToAutocompleteFormat ($items) {
		$i = 0;
		$temp = array();
		foreach ($items as $key => $value) {
			$autocompletekey = $key;

			if ($this->getSourceType() == 'db') {
				$autocompletekey = $value['id'];
			}

			$temp[$i] = array(
				'autocompletekey' => $autocompletekey
			);

			if (is_array($value) || is_object($value)) {
				foreach ($value as $vkey => $vvalue) {
					$temp[$i][$vkey] = $vvalue;
				}
			} else {
				$temp[$i]['value'] = $value;
			}

			$i++;
		}

		$items = $temp;

		return $items;
	}

	public function hasDynamicParams () {
		return (bool) $this->getDynamicParams();
	}

	public function getDynamicParams () {
		if ($this->dynamicParams !== null) {
			return $this->dynamicParams;
		}

		$params = $this->getSourceParams();
		
		if (empty($params)) {
			return false;
		}

		$return = array();
		foreach ($params as $param) {
			if (!is_array($param) && strpos($param, '%') !== false) {
				$selector = $name = str_replace('%','',$param);

				$element = $this->getSiblingElement($name);

				if ($element->isMultiple()) {
					$nth = 0;

					if ($this->isMultiple()) {
						$nth = $this->getNthInstance();
					}

					$selector .= '[' . $nth . ']';
				}

				$return[] = array(
					'name' => $name,
					'selector' => $selector
				);
			}
		}

		$this->dynamicParams = $return;
		
		return $return;
	}

	public function getSource ($param = false) {
		if ($param) {
			if (isset($this->source[$param])) {
				return $this->source[$param];
			} else {
				return false;
			}
		}

		return $this->source;
	}

	public function getSourceParams () {
		$params = $this->getSource('params');

		if (!$params) {
			return array();
		}

		return $params;
	}

	public function isKeyAsValue () {
		return $this->keyAsValue;
	}

	public function getDisplay () {
		return $this->getSource('display');
	}

	public function getLimit () {
		return $this->getSource('limit');
	}

	public function isDefaults () {
		return $this->getSource('defaults');
	}

	public function getMode () {
		return $this->getSource('mode');
	}

	public function getSourceType () {
		$type = 'items';

		if ($this->getSource('class')) {
			$type = 'function';
		} elseif ($this->getSource('table')) {
			$type = 'db';
		}

		return $type;
	}

	public function getSearchIn () {
		return $this->getSource('searchIn');
	}

	public function getEmptyHtml () {
		if (strpos($this->emptyHtml,'.phtml') !== false) {
			$this->emptyHtml = $this->fetchTemplateByName($this->emptyHtml);
		}

		return $this->emptyHtml;
	}

	public function getSuggestionHtml () {
		if (strpos($this->suggestionHtml,'.phtml') !== false) {
			$this->suggestionHtml = $this->fetchTemplateByName($this->suggestionHtml);
		}

		return $this->suggestionHtml;
	}

	public function getOnSelect () {
		return $this->onSelect;
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

			if (!$this->isPost && $this->isLoad && $value !== null) {
				$value = $this->getValueFromLoad($value);
			}
		}

		$this->value = $value;
	}

	public function getValueFromLoad ($value) {
		// @ is because if there's an error eg. in the given class/method the user should not see it
		$items = @$this->getItems(false, $this->getSourceParamsWithDynamicValues($this->getRequestParams($this->getNthInstance())));

		if (empty($items)) {
			$items = array();
		}

		$found = $display = false;
		foreach ($items as $key => $one) {
			if ($this->isKeyAsValue()) {
				if ((string) $key === (string) $value) {
					$display = isset($one[$this->getSource('display')]) ? $one[$this->getSource('display')] : $one;
					$found = true;
				}
			} else {
				if (strtolower($one[$this->getSource('display')]) == strtolower($value)) {
					$display = $one[$this->getSource('display')];
					$found = true;
				}
			}
		}
		if ($found) {
			$value = array(
				'value' => $value,
				'display' => $display,
				'new' => 'false',
			);
		} else {
			$value = array(
				'value' => $value,
				'display' => $value,
				'new' => 'true',
			);
		}

		return $value;
	}

	public function getValue ($key = false, $language = null) {
		/*if (empty($this->value)) {
			$this->setValue();
		}*/
		# a WX_Form hívásakor minden példányosításra kerül és a value
		# értéket kap. Később, amikor a WX_Form példányosítása után
		# módosítunk a requestParams-on (pl. belekerül az id), akkor
		# nem fog eljutni a templatehez, mivel előzőleg már be lett
		# állítva a value értéke. Ezért kell mindig settelni.
		if (!$this->emptied) {
			$this->setValue();
		}

		if (!empty($this->value) && !is_array($this->value)) {
			$this->value = $this->getValueFromLoad($this->value);
		}
		
		if ($key) {
			if (isset($this->value[$key])) {
				return $this->value[$key];
			} else {
				return false;
			}
		} else {
			$value = $this->value['value'];

			return $value;
		}
	}

	public function getValueArray () {
		if (empty($this->value)) {
			$this->setValue();
		}

		return $this->value;
	}

	public function emptyField () {
		$this->value = array(
			'value' => '',
			'display' => '',
			'new' => '',
		);
		
		$this->emptied = true;
	}
}

// To extend functionality check out:
// https://github.com/twitter/typeahead.js
// https://twitter.github.io/typeahead.js/examples/