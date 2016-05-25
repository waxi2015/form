<?php

namespace Waxis\Form\Form\Element;

class Tags extends Select {

	public $type = 'tags';

	public $emptyMessageType = 'emptyTags';

	public $static = false;

	public $save = null;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['save'])) {
			$this->save = $this->descriptor['save'];
			$this->static = true;
		}

		parent::__construct($descriptor, $nth, $constructParams);
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

	public function getValueArray() {
		$value = $this->getValue();

		if (empty($value)) {
			return [];
		}

		$items = $this->getItems();

		$return = [];
		foreach (explode(',',$value) as $one) {
			$return[$one] = $items[$one];
		}

		return $return;
	}

	public function saveExternalData () {
		if ($this->save === null) {
			return true;
		}

		$table = $this->save['table'];
		$recordField = $this->save['recordField'];
		$itemField = $this->save['itemField'];

		$id = $this->getRequestParam('id');
		$this->setValue();
		$values = explode(',',$this->getRequestParam($this->getName(false)));

		if (empty($id)) {
			return true;
		}

		if (!is_int($id)) {
			$id = decode($id);
		}

		\DB::table($table)->where($recordField, $id)->delete();

		foreach ($values as $value) {
			\DB::table($table)->insert([
				$recordField => $id,
				$itemField => $value,
				'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
				'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
			]);
		}
	}

	public function setValue ($language = null) {
		if ($this->save === null) {
			return parent::setValue();
		}

		$table = $this->save['table'];
		$recordField = $this->save['recordField'];
		$itemField = $this->save['itemField'];

		$id = $this->getRequestParam('id');

		if (empty($id)) {
			$value = $this->defaultValue;
		} else {
			if (!is_int($id)) {
				$id = decode($id);
			}

			$values = to_array(\DB::table($table)->where($recordField, $id)->get());

			$value = [];
			foreach ($values as $one) {
				$value[] = $one[$itemField];
			}

			$value = implode(',',$value);
		}
		
		$this->value = $value;
	}

	public function getSelectedTags () {
		$tags = explode(',',$this->getValue());
		$items = $this->getItems();

		$return = [];
		foreach ($tags as $tag) {
			if (isset($items[$tag])) {
				$return[$tag] = $items[$tag];
			}
		}

		return $return;
	}
 }

 /* 
https://github.com/bootstrap-tagsinput/bootstrap-tagsinput
*/