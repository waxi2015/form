<?php

namespace Waxis\Form\Form\Element;

class Table extends Element {

	public $type = 'table';

	public $static = true;

	public $assign = [];

	public function __construct ($descriptor, $nth = 0, $constructParams = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}
		
		if (!isset($this->descriptor['descriptor'])) {
			throw new \Exception('Repeater\'s descriptor must exist in form\'s descriptor.');
		} else {
			$this->repeaterDescriptor = $this->descriptor['descriptor'];
		}

		if (isset($this->descriptor['assign'])) {
			$this->assign = $this->descriptor['assign'];
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

		if (!empty($this->assign)) {
			$vars = [];
			$rp = $this->getRequestParams();

			foreach ($this->descriptor['assign'] as $fromVar => $toVar) {
				if (isset($rp[$fromVar])) {
					$vars[$toVar] = $rp[$fromVar];
				}
			}

			$this->repeaterDescriptor['vars'] = $vars;
		}

		if (isset($this->repeaterDescriptor['actions'])) {
			$actions = $this->repeaterDescriptor['actions'];

			if (in_array('edit', $actions)) {
				if (!isset($this->repeaterDescriptor['buttons'])) {
					$this->repeaterDescriptor['buttons'] = [];
				}

				$this->repeaterDescriptor['buttons'][] = [
					'type' => 'editpopup',
					'label' => '<span class="fa fa-pencil"></span>',
				];
			}

			if (in_array('delete', $actions)) {
				if (!isset($this->repeaterDescriptor['buttons'])) {
					$this->repeaterDescriptor['buttons'] = [];
				}

				$this->repeaterDescriptor['buttons'][] = [
					'type' => 'delete',
					'label' => '<span class="fa fa-trash"></span>',
				];
			}
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