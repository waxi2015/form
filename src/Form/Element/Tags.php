<?php

namespace Waxis\Form\Form\Element;

class Tags extends Select {

	public $type = 'tags';

	public $emptyMessageType = 'emptyTags';

	public $static = false;

	public $save = null;

	public $freeInput = false;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (isset($this->descriptor['save'])) {
			$this->save = $this->descriptor['save'];
			$this->static = true;
		}
		
		if (isset($this->descriptor['freeInput'])) {
			$this->freeInput = $this->descriptor['freeInput'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function isFreeInput () {
		return $this->freeInput;
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

	public function saveExternalData ($data = []) {

		if ($this->save === null) {
			return true;
		}

		$table = $this->save['table'];
		$recordField = $this->save['recordField'];
		$recordFieldParam = getValue($this->save, 'recordFieldParam', 'id');
		$itemField = $this->save['itemField'];

		$id = $this->getRequestParam($recordFieldParam);

		if (empty($id) && isset($data[$recordFieldParam])) {
			$id = $data[$recordFieldParam];
		}

		if (empty($id) && !empty($this->getRequestParam('id'))) {
			$id = $this->getRequestParam('id');
			if (!is_int($id)) {
				$id = decode($id);
			}

			$record = to_array(\DB::table($this->formDescriptor['table'])->where('id', $id)->first());
			$id = (int) $record[$recordFieldParam];
		}


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
			if ($this->isFreeInput()) {
				$sourceTable = $this->save['sourceTable'];

				$tag = \DB::table($sourceTable)
						->where('name', $value)
						->first();

				if (empty($tag)) {
					\DB::table($sourceTable)
						->insert(['name' => $value]);
					$value = \DB::getPdo()->lastInsertId();
				} else {
					$value = $tag->id;
				}
			}

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
		$recordFieldParam = getValue($this->save, 'recordFieldParam', 'id');
		$itemField = $this->save['itemField'];

		$id = $this->getRequestParam('id');

		if (empty($id)) {
			$value = $this->defaultValue;
		} else {
			if (!is_int($id)) {
				$id = decode($id);
			}

			if ($recordFieldParam != 'id') {
				$record = to_array(\DB::table($this->formDescriptor['table'])->where('id', $id)->first());
				$id = $record[$recordFieldParam];
			}

			$valuesQuery = \DB::table($table)
						->where($recordField, $id);

			if ($this->isFreeInput()) {
				$sourceTable = $this->save['sourceTable'];
				
				$valuesQuery->leftJoin(
					$sourceTable,
					$table . '.' . $itemField,
					'=', $sourceTable . '.id'
				);
			}

			$values = $valuesQuery->get();

			$values = to_array($values);

			$value = [];
			foreach ($values as $one) {
				if ($this->isFreeInput()) {
					$value[] = $one['name'];
				} else {
					$value[] = $one[$itemField];
				}
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

	public function getPlaceholder () {
		if ($this->placeholder !== null) {
			return 'placeholder="' . $this->placeholder . '"';
		}

		return false;
	}
 }

 /* 
https://github.com/bootstrap-tagsinput/bootstrap-tagsinput
*/