<?php

namespace Waxis\Form\Form\Element;

class Table extends Element {

	public $type = 'table';

	public $static = true;

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (!isset($this->descriptor['descriptor'])) {
			throw new \Exception('Repeater\'s descriptor must exist in form\'s descriptor.');
		} else {
			$this->repeaterDescriptor = $this->descriptor['descriptor'];
		}

		parent::__construct($descriptor, $nth, $constructParams);
	}

	public function setWhere () {
		$where = getValue($this->repeaterDescriptor,'where',null);
		$rp = $this->getRequestParams();

		if ($where !== null && preg_match_all('/{+(.*?)}/', $where, $matches)) {
			foreach ($matches[1] as $match) {
				if (isset($rp[$match])) {
					if ($match == 'id' && !is_int($rp[$match])) {
						$rp[$match] = decode($rp[$match]);
					}
		    		$where = str_replace('{'.$match.'}', $rp[$match], $where);
				} else {
					$where = str_replace('{'.$match.'}', '"undefined"', $where);
				}
			}
		}

		$this->repeaterDescriptor['where'] = $where;
	}

	public function getRepeater () {
		if (!isset($this->repeaterDescriptor['source']) && !isset($this->repeaterDescriptor['table'])) {
			$rp = $this->getRequestParams();

			if (isset($rp[$this->getName(false)])) {
				$this->repeaterDescriptor['source'] = $rp[$this->getName(false)];
			} else {
				return null;
			}
		} else {
			$this->setWhere();
		}

		$repeater = new \Repeater($this->repeaterDescriptor);
		return $repeater;
	}

	public function renderTable () {
		$repeater = $this->getRepeater();

		if ($repeater === null) {
			return;
		}

		return $repeater->render();
	}
}