<?php

namespace Waxis\Form\Form\Converter;

class Multilingual {

	public function before($data, $rp, $form)
	{
		$result = $elements = [];
		foreach ($data as $key => $value) {
			$element = $elements[$key] = $form->getElementByName($key);
			
			if ($element->translate && isJson($value)) {
				$value = json_decode($value, true);

				$transformedValue = [];

				foreach ($value as $language => $rawValue) {
					if (is_array($rawValue)) {
						$transformedValue[$language] = json_encode($rawValue, JSON_FORCE_OBJECT);
					} else {
						$transformedValue[$language] = $rawValue;
					}
				}

				$value = $transformedValue;
			}

			$result[$key] = $value;
		}
		$data = $result;

		$defaultLanguage = config('app.locale');
		$idElement = $form->getIdElement();
		$id = $data[$idElement];

		$return = [];
		foreach ($data as $key => $one) {
			$element = $elements[$key];

			if ($element->translate) {
				$return[$key] = isset($one[$defaultLanguage]) ? $one[$defaultLanguage] : null;
			} else {
				$return[$key] = $one;
			}
		}
		$return['language'] = $defaultLanguage;

		if (empty($id)) {
			$return['connector'] = $this->getLastConnector($form->getTable()) + 1;
		}

		return $return;
	}

	public function after($data, $rp, $form)
	{
		$result = $elements = [];
		foreach ($data as $key => $value) {
			$element = $elements[$key] = $form->getElementByName($key);

			if ($element->translate && isJson($value)) {
				$value = json_decode($value, true);

				$transformedValue = [];

				foreach ($value as $language => $rawValue) {
					if (is_array($rawValue)) {
						$transformedValue[$language] = json_encode($rawValue, JSON_FORCE_OBJECT);
					} else {
						$transformedValue[$language] = $rawValue;
					}
				}

				$value = $transformedValue;
			}

			$result[$key] = $value;
		}
		$data = $result;

		$table = $form->getTable();
		$languages = config('locale.languages');
		$defaultLanguage = config('app.locale');
		$idElement = $form->getIdElement();
		$id = $data[$idElement];
		$connector = \DB::table($table)->where('id', $id)->first()->connector;

		foreach ($languages as $language) {
			$toDb = [];

			$iso = $language['iso2'];

			$record = \DB::table($table)->where('language', $iso)->where('connector', $connector)->first();

			if (!empty($record) && isset($record->id)) {
				$id = $record->id;
			} else {
				$id = null;
			}

			if ($iso != $defaultLanguage) {
				foreach ($data as $key => $one) {
					$element = $elements[$key];

					if ($element->translate) {
						$toDb[$key] = isset($one[$iso]) ? $one[$iso] : null;
					} else {
						$toDb[$key] = $one;
					}

					$toDb['language'] = $iso;
					$toDb['connector'] = $connector;
					$toDb['id'] = $id;
				}

				if ($id === null) {
					\DB::table($table)->insert($toDb);
				} else {
					\DB::table($table)->where('id', $id)->update($toDb);
				}
			}
		}
	}

	public function getLastConnector($table) {
		$last = \DB::table($table)->orderBy('connector', 'DESC')->first();

		if (empty($last)) {
			return 0;
		}

		return $last->connector;
	}
}