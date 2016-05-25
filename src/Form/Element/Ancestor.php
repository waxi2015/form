<?php

namespace Waxis\Form\Form\Element;

class Ancestor {

	public $formDescriptor = null;

	public $formIdentifier = null; // that's the ID attribute of the form

	public $formId = null; // that's a unique generated key

	public $type = false;

	public $nodeLevel = 0;

	public $isFirst = false;

	public $isLast = false;

	public $nth = 0;

	public $isCloneable = false;

	public $clones = 0;

	public $addClone = true;

	public $isClone = false;

	public $isRemovable = false;

	public $nthClone = 0;

	public $templateDirectory = false;

	public $template = false;

	public $descriptor = null;	

	public $filters = array();

	public $parent = null;	

	//public $requestParams = array();

	public $registryNamespace = null;

	public $config = false;

	public $errors = array();

	public $isPost = false;

	public $isLoad = false;

	public $isCloneRequest = false;

	public $vars = null;

	public $data = null;

	public $description = null;

	public $descriptorName = false;

	public $nthInstance = null;

	public $condition = null;

	public $id = null;

	public $class = null;

	public $multilingual = false;

	public $viewMode = false;

	public function __construct($descriptor, $nth = null) {
		if ($this->descriptor === null) {
			$this->descriptor = $descriptor;
		}

		if (isset($this->descriptor['id'])) {
			$this->id = $descriptor['id'];
		}
		
		if (isset($this->descriptor['clones']) && !$this->clones) {
			$this->clones = $descriptor['clones'];
		}
		
		if (isset($this->descriptor['addClone'])) {
			$this->addClone = $descriptor['addClone'];
		}

		if (isset($this->descriptor['condition'])) {
			$this->condition = $this->descriptor['condition'];
		}

		if (isset($this->descriptor['data'])) {
			$this->data = $this->descriptor['data'];
		}

		if (isset($this->descriptor['viewMode'])) {
			$this->viewMode = $this->descriptor['viewMode'];
		}

		$this->isCloneable = isset($this->descriptor['clone']) && $this->descriptor['clone'] ? true : false;
	}

	public function isMultilingual () {
		return $this->multilingual;
	}

	public function scriptCaptureStart () {
		ob_start();
	}

	public function scriptCaptureEnd ($type = 'APPEND') {
		$content = ob_get_contents();
		ob_end_clean();

		$key = $this->getRegistryNamespace() . '-' . $this->formId . '-capture';

		if (\Registry::isRegistered($key)) {
			$capture =  \Registry::get($key);
		} else {
			$capture = array();
		}

		switch ($type) {
			case 'PREPEND':
				array_unshift($capture, $content);
				break;

			default:
				$capture[] = $content;
				break;
		}

		\Registry::set($key, $capture);
	}

	public function renderCapturedScript () {
		$key = $this->getRegistryNamespace() . '-' . $this->formId . '-capture';

		if (\Registry::isRegistered($key)) {
			$capture = \Registry::get($key);
		} else {
			$capture = array();
		}

		$return = '<script>';
		foreach ($capture as $one) {
			$return .= $one;
		}
		$return .= '</script>';

		return $return;
	}

	public function setId ($id = null, $regenerateDefault = false, $language = null) {
		if ($id !== null) {
			$this->id = $id;
		} elseif ($this->id === null || $regenerateDefault || $language !== null) {

			if ($this->nodeLevel == 6) {
				$id = $this->getParam('name');

				if ($language !== null) {
					$id .= '-' . $language;
				}

				if ($this->isMultiple()) {
					$id .= '-' . $this->getValueKey();
				}
			} else {
				$id = $this->getType(false);

				$id .= '-' . $this->getNodeTreeString('-');

				$nth = $this->getNthInstance();
				if ($nth !== null) {
					$id .= '-' . $nth;
				}
			}

			$this->id = $id;
		}
	}

	public function getId ($language = null) {
		if ($language !== null) {
			$this->setId(null, false, $language);
		}

		return $this->id;
	}

	public function getCleanId () {
		if ($this instanceof \Waxis\Form\Form\Element\Structure) {
			return $this->getId();
		} else {
			$element = $this->getSiblingElement($this->getName(false));
			return $element->getId();	
		}
	}

	public function setPropertiesToChildrenRecursive ($properties, $element) {
		$nodeLevel = $element->getNodeLevel();

		switch ($nodeLevel) {
			case 5:
				$children = $element->elements;
				break;
			case 4:
				$children = $element->columns;
				break;
			case 3:
				$children = $element->rows;
				break;
			case 2:
				$children = $element->bcolumns;
				break;
			case 1:
				$children = $element->brows;
				break;
			case 0:
				$children = $element->sections;
				break;
		}

		if (isset($children)) {
			foreach ($children as $one) {
				foreach ($properties as $key => $value) {
					$one->$key = $value;
				}

				if ($nodeLevel > 0) {
					$this->setPropertiesToChildrenRecursive($properties, $one);
				}
			}
		}
	}

	public function getSiblingElement ($name) {
		$form = new \Form($this->formDescriptor);
		return $form->getElementByName($name);
	}

	public function getElementsByConditionalSourceParamName ($paramName, $type = false) {
		$elements = array();

		if ($type && !is_array($type)) {
			$type = array($type);
		}
		
		$form = new \Form($this->formDescriptor);

		foreach ($form->sections as $section) {
			foreach ($section->brows as $brow) {
				foreach ($brow->bcolumns as $bcolumn) {
					foreach ($bcolumn->rows as $row) {
						foreach ($row->columns as $column) {
							foreach ($column->elements as $element) {
								$found = false;
								if (property_exists($element, 'source') && isset($element->source['params'])) {
									foreach ($element->source['params'] as $param) {
										if ($param == '%' . $paramName && !$found && (!$type || in_array($element->getType(), $type))) {
											$elements[] = $element;
											$found = true;
										}
									}
								}
							}
						}
					}
				}		
			}
		}
		
		return $elements;
	}

	public function getDescription () {
		return $this->description;
	}

	public function getVars () {
		return $this->vars;
	}

	public function getVar ($key) {
		if (isset($this->vars[$key])) {
			return $this->vars[$key];
		}

		return false;
	}

	public function getClass () {
		$class = $this->class;

		if ($this->hasCondition()) {
			foreach ($this->condition as $field => $value) {
				if (!$this->isConditionTrue($field, $value)) {
					$class .= ' hidden';
				}
			}
		}

		return $class;
	}

	public function addError ($errors) {
		if (is_array($errors)) {
			$this->errors = array_merge($this->errors, $errors);
		} else {
			$this->errors[] = $errors;
		}
	}

	public function getErrors ($language = null) {
		if ($this->isMultilingual() && $this->translate && $language !== null) {
			return $this->errors[$language];
		} else {
			return $this->errors;
		}
	}

	public function isError ($language = null) {
		if ($language === null) {
			return !empty($this->errors) ? true : false;
		} else {
			if (isset($this->errors[$language]) && !empty($this->errors[$language])) {
				return true;
			}

			return false;
		}
	}

	public function isConditionTrue ($field, $value) {
		$nth = false;

		$conditionElement = $this->getSiblingElement($field);

		if ($conditionElement->isMultiple()) {
			$nth = $this->getValueKey();
		}

		$fieldValue = $this->getRequestParam($field, $nth);

		if ($value === true) {
			if (!empty($fieldValue)) {
				return true;
			}
		} else {
			if ($fieldValue == $value || (is_array($value) && in_array($fieldValue, $value))) {
				return true;
			}
		}
		
		return false;
	}

	public function isMultiple () {
		if ($this->isCloneable || $this->isParentCloneable()) {
			return true;
		}

		return false;
	}

	public function hasCondition () {
		if ($this->condition === null) {
			return false;
		}

		return true;
	}

	public function getValueKey () {
		$key = $this->nthInstance;
		
		if ($key === null) {
			if ($this->isCloneable) {
				$key = $this->nthClone;
			} elseif ($this->isParentCloneable()) {
				$parent = $this->getClosestParent(array('clone' => true));
				$key = $parent['nthClone'];
			}
		}

		return $key;
	}

	public function getNthInstance () {
		return $this->getValueKey();
	}

	public function setNthInstance ($nth) {
		$this->nthInstance = $nth;
	}

	public function setChildren () {
		switch ($this->nodeLevel) {
			case 1:
				$this->setBrows();
				break;

			case 2:
				$this->setBcolumns();
				break;

			case 3:
				$this->setRows();
				break;

			case 4:
				$this->setColumns();
				break;

			case 5:
				$this->setElements();
				break;
		}
	}

	public function getClonesCountFromRequest () {
		$clones = $this->getRequestParam('clone-' . $this->getNodeTreeString('-'));
		return $clones ? $clones : 0;
	}

	public function getRegistryNamespace () {
		if (empty($this->registryNamespace)) {
			$this->registryNamespace = $this->getParam('id');
		}

		return $this->registryNamespace;
	}

	// $mode = rewrite | extend
	public function setRequestParam ($param, $value, $key = null) {
		$params = $this->getRequestParams();

		if ($key !== null) {
			$params[$param][$key] = $value;
		} else {
			$params[$param] = $value;
		}

		$this->setRequestParams($params);
	}

	// $mode = rewrite | extend
	public function setRequestParams ($params) {
		\Registry::set($this->getRegistryNamespace() . '-' . $this->formId . '-params', $params);
	}

	public function getRequestParam ($param, $key = null) {
		$params = $this->getRequestParams();

		if (isset($params[$param])) {
			if ($key !== null && $key !== false) {
				if (isset($params[$param][$key])) {
					return @$params[$param][$key];
				} else {
					return null;
				}
			} else {
				return $params[$param];
			}
		}

		return null;
	}

	public function getRequestParams ($nth = null) {
		$key = $this->getRegistryNamespace() . '-' . $this->formId . '-params';

		if (\Registry::isRegistered($key)) {
			$params =  \Registry::get($key);
		} else {
			return array();
		}

		$return = array();
		foreach ($params as $key => $value) {
			if (is_array($value) && $nth !== null) {
				if (isset($value[$nth])) {
					$return[$key] = $value[$nth];
				} else {
					$return[$key] = false;
				}
			} else {
				$return[$key] = $value;
			}
		}

		return $return;
	}

	public function isParentCloneable ($parent = false) {
		if (!$parent) {
			$parent = $this->parent;
		}

		if (!empty($parent) && isset($parent['clone']) && $parent['clone']) {
			return true;
		} elseif (!empty($parent) && isset($parent['parent']) && !empty($parent['parent'])) {
			return $this->isParentCloneable($parent['parent']);
		}	

		return false;
	}

	// $where = array('isClone' => true)
	public function getClosestParent ($where = array(), $parent = false) {
		if (!$parent) {
			$parent = $this->getParent($this);
		}

		if (empty($where)) {
			return $parent;
		} else {
			$match = true;

			foreach ($where as $key => $value) {
				if ($parent[$key] != $value) {
					$match = false;
				}
			}

			if ($match) {
				return $parent;
			} else {
				if (isset($parent['parent']) && !empty($parent['parent'])) {
					return $this->getClosestParent($where, $parent['parent']);
				} else {
					return false;
				}
			}
		}
	}

	public function setParent (\Waxis\Form\Form\Element\Ancestor $object) {
		$this->parent = array(
			'nodeLevel' => $object->nodeLevel,
			'type' => $object->type,
			'nth' => $object->nth,
			'clone' => $object->isCloneable,
			'isClone' => $object->isClone,
			'nthClone' => $object->nthClone,
			'parent' => $object->parent,
		);
	}

	public function getParent () {
		return $this->parent;
	}

	public function getParents ($level = 1) {
		$return = $this->parent;

		for ($i = 1; $i < $level; $i++) {
			$return = $return['parent'];
		}

		return $return;
	}

	public function getElements () {
		$elements = [];

		switch ($this->type) {
			case 'form':
				foreach ($this->sections as $section) {
					foreach ($section->brows as $brow) {
						foreach ($brow->bcolumns as $bcolumn) {
							foreach ($bcolumn->rows as $row) {
								foreach ($row->columns as $column) {
									foreach ($column->elements as $element) {
										$elements[] = $element;
									}
								}
							}
						}		
					}
				}
				break;

			case 'section':
				foreach ($this->brows as $brow) {
					foreach ($brow->bcolumns as $bcolumn) {
						foreach ($bcolumn->rows as $row) {
							foreach ($row->columns as $column) {
								foreach ($column->elements as $element) {
									$elements[] = $element;
								}
							}
						}
					}		
				}
				break;

			case 'brow':
				foreach ($this->bcolumns as $bcolumn) {
					foreach ($bcolumn->rows as $row) {
						foreach ($row->columns as $column) {
							foreach ($column->elements as $element) {
								$elements[] = $element;
							}
						}
					}
				}
				break;

			case 'bcolumn':
				foreach ($this->rows as $row) {
					foreach ($row->columns as $column) {
						foreach ($column->elements as $element) {
							$elements[] = $element;
						}
					}
				}
				break;

			case 'row':
				foreach ($this->columns as $column) {
					foreach ($column->elements as $element) {
						$elements[] = $element;
					}
				}
				break;

			case 'column':
				foreach ($this->elements as $element) {
					$elements[] = $element;
				}
				break;

			default:
				$elements[] = $this;
				break;
		}
		

		return $elements;
	}

	public function getColumns () {
		$columns = [];

		switch ($this->type) {
			case 'form':
				foreach ($this->sections as $section) {
					foreach ($section->brows as $brow) {
						foreach ($brow->bcolumns as $bcolumn) {
							foreach ($bcolumn->rows as $row) {
								foreach ($row->columns as $column) {
									$columns[] = $column;
								}
							}
						}		
					}
				}
				break;

			case 'section':
				foreach ($this->brows as $brow) {
					foreach ($brow->bcolumns as $bcolumn) {
						foreach ($bcolumn->rows as $row) {
							foreach ($row->columns as $column) {
								$columns[] = $column;
							}
						}
					}		
				}
				break;

			case 'brow':
				foreach ($this->bcolumns as $bcolumn) {
					foreach ($bcolumn->rows as $row) {
						foreach ($row->columns as $column) {
							$columns[] = $column;
						}
					}
				}
				break;

			case 'bcolumn':
				foreach ($this->rows as $row) {
					foreach ($row->columns as $column) {
						$columns[] = $column;
					}
				}
				break;

			case 'row':
				foreach ($this->columns as $column) {
					$columns[] = $column;
				}
				break;

			default:
				$columns[] = $this;
				break;
		}
		

		return $columns;
	}

	public function getRows () {
		$rows = [];

		switch ($this->type) {
			case 'form':
				foreach ($this->sections as $section) {
					foreach ($section->brows as $brow) {
						foreach ($brow->bcolumns as $bcolumn) {
							foreach ($bcolumn->rows as $row) {
								$rows[] = $row;
							}
						}		
					}
				}
				break;

			case 'section':
				foreach ($this->brows as $brow) {
					foreach ($brow->bcolumns as $bcolumn) {
						foreach ($bcolumn->rows as $row) {
							$rows[] = $row;
						}
					}		
				}
				break;

			case 'brow':
				foreach ($this->bcolumns as $bcolumn) {
					foreach ($bcolumn->rows as $row) {
						$rows[] = $row;
					}
				}
				break;

			case 'bcolumn':
				foreach ($this->rows as $row) {
					$rows[] = $row;
				}
				break;

			default:
				$rows[] = $this;
				break;
		}
		

		return $rows;
	}

	public function getBcolumns () {
		$bcolumns = [];

		switch ($this->type) {
			case 'form':
				foreach ($this->sections as $section) {
					foreach ($section->brows as $brow) {
						foreach ($brow->bcolumns as $bcolumn) {
							$bcolumns[] = $bcolumn;
						}		
					}
				}
				break;

			case 'section':
				foreach ($this->brows as $brow) {
					foreach ($brow->bcolumns as $bcolumn) {
						$bcolumns[] = $bcolumn;
					}		
				}
				break;

			case 'brow':
				foreach ($this->bcolumns as $bcolumn) {
					$bcolumns[] = $bcolumn;
				}
				break;

			default:
				$bcolumns[] = $this;
				break;
		}
		

		return $bcolumns;
	}

	public function getBrows () {
		$brows = [];

		switch ($this->type) {
			case 'form':
				foreach ($this->sections as $section) {
					foreach ($section->brows as $brow) {
						$brows[] = $brow;
					}
				}
				break;

			case 'section':
				foreach ($this->brows as $brow) {
					$brows[] = $brow;
				}
				break;

			default:
				$brows[] = $this;
				break;
		}
		

		return $brows;
	}

	public function getSections () {
		$sections = [];

		switch ($this->type) {
			case 'form':
				foreach ($this->sections as $section) {
					$sections[] = $section;
				}
				break;

			default:
				$sections[] = $this;
				break;
		}
		

		return $sections;
	}

	public function getCloneTree () {
		if (!$this->isCloneable) {
			$level = $this->getNodeLevel();
			$return = array();
			for ($i = $level; $i >= 0; $i--) {
				$parent = $this->getParents($i);

				if (!empty($parent)) {
					$return[$parent['nodeLevel']] = $parent['nth'];
				}
			}
			//$return[$this->getNodeLevel()] = $this->nth;

			$tree = implode('-', $return);
		} else {
			$tree = $this->getNodeTreeString('-');
		}

		return 'clone-' . $tree;
	}

	public function getNodeTree () {
		$level = $this->getNodeLevel();

		$return = array();
		for ($i = $level; $i >= 0; $i--) {
			$parent = $this->getParents($i);

			if (!empty($parent)) {
				$return[$parent['nodeLevel']] = $parent['nth'];
			}
		}

		$return[$this->getNodeLevel()] = $this->nth;

		return $return;
	}

	public function getNodeTreeString ($separator = '') {
		$tree = $this->getNodeTree();

		return implode($separator, $tree);
	}

	public function getNodeLevel () {
		return $this->nodeLevel;
	}
	
	public function render () {
		echo $this->fetch();
	}
	
	public function renderObjects ($objects) {
		foreach ($objects as $key => $one) {
			
			$one->nth = $one->nth;
			$one->isFirst = $key == 0 ? true : false;
			$one->isLast = count($objects) == ($key + 1) ? true : false;

			$one->render();
		}
	}

	public function fetch () {
		$return = '';

		foreach ($this->getTemplatesToRender() as $key => $template) {
			ob_start();
			include($template);
			$content = ob_get_contents();
			ob_end_clean();

			$return .= $this->getTemplatePrefix() . rtrim($content) . PHP_EOL . $this->getTemplateSuffix();
		}

		return $return;
	}

	public function getTemplatesToRender () {
		$config = $this->getConfig();

		$pathToTemplate = '';

		if ($this->viewMode === true) {
			$pathToTemplate = $this->templateDirectory . 'view/' . $this->getTemplate();

			if (!file_exists($pathToTemplate)) {
				$pathToTemplate = resource_path('views/form/') . 'view/' . $this->getTemplate();
			}

			if (!file_exists($pathToTemplate)) {
				$pathToTemplate = $this->getTemplateDirectory() . 'view/' . $this->getTemplate();
			}
		}

		if (!file_exists($pathToTemplate)) {
			$pathToTemplate = resource_path('views/form/') . $this->getTemplate();
		}

		if (!file_exists($pathToTemplate)) {
			$pathToTemplate = $this->getTemplateDirectory() . $this->getTemplate();
		}

		$return = array(
			$pathToTemplate
		);

		
		if ($this->isCloneable && $this->nthClone == $this->clones && $this->addClone) {
			$config = $this->getConfig();

			$pathToClone = $this->getTemplateDirectory() . $config->getTemplate('clone');

			if (!file_exists($pathToClone)) {
				$pathToClone = $config->getTemplateDirectory() . $config->getTemplate('clone');
			}

			$return[] = $pathToClone;
		}

		return $return;
	}

	public function setTemplateDirectory ($templateDirectory) {
		$this->templateDirectory = $templateDirectory;
	}

	public function getTemplateDirectory () {
		if (!$this->templateDirectory) {
			$config = $this->getConfig();
			return __DIR__ . '/..' . $config->getTemplateDirectory();
		}

		return $this->templateDirectory;
	}

	public function getTemplate () {
		if (!$this->template) {
			$config = $this->getConfig();
			$this->template = $config->getTemplate($this->getType());
		}

		return $this->template;
	}

	public function fetchTemplate ($type, $params = array()) {
		$config = $this->getConfig();

		$pathToTemplate = $this->getTemplateDirectory() . $config->getTemplate($type);

		if (!file_exists($pathToTemplate)) {
			$pathToTemplate = $config->getTemplateDirectory() . $config->getTemplate($type);
		}

		foreach ($params as $key => $value) {
			$$key = $value;
		}

		ob_start();
		include($pathToTemplate);
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	// javascript name must be the same as the template's name itself but in the /javascript folder
	public function fetchJavascript ($template = null, $params = array()) {
		$type = $this->getType();

		$config = $this->getConfig();

		if ($template == null) {
			$template = $config->getTemplate($type);
		} else {
			$template = $template;
		}

		$pathToTemplate = $this->getTemplateDirectory() . 'javascript/' . $template;

		if (!file_exists($pathToTemplate)) {
			$pathToTemplate = $config->getTemplateDirectory() . 'javascript/' . $template;
		}

		foreach ($params as $key => $value) {
			$$key = $value;
		}

		ob_start();
		include($pathToTemplate);
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function renderTemplate ($type = null, $params = []) {
		echo $this->fetchTemplate($type, $params);
	}

	public function fetchTemplateByName ($name) {
		$config = $this->getConfig();
		$paths = array(
			$this->getTemplateDirectory() . $this->getType() . '/' . $name,
			$this->getTemplateDirectory() . $name,
			resource_path('views/form/') . $this->getType() . '/' . $name,
			resource_path('views/form/') . $name,
			$config->getTemplateDirectory() . $this->getType() . '/' . $name,
			$config->getTemplateDirectory() . $name
		);

		$pathToTemplate = false;

		foreach ($paths as $one) {
			if (file_exists($one)) {
				$pathToTemplate = $one;
				break;
			}
		}

		if (!$pathToTemplate) {
			throw new \Exception('Template "'.$name.'" not found in paths: ' . implode($paths, ':'),1);
			return false;
		}

		ob_start();
		include($pathToTemplate);
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function getTemplatePrefix () {
		$multiplier = max($this->nodeLevel - 1,0);

		if (!$this->isFirst) {
			$multiplier = 1;
		}

		if ($this->nodeLevel == 0) {
			$multiplier = 0;
		}

		return str_repeat("\t",$multiplier);
	}

	public function getTemplateSuffix () {
		$multiplier = max($this->nodeLevel - 1,0);

		return str_repeat("\t",$multiplier);
	}

	public function setNth ($nth) {
		$this->nth = $nth;
	}

	public function setConfig (\Waxis\Form\Form\Config $config) {
		$this->config = $config;
	}

	public function getConfig () {
		if (!$this->config) {
			if (file_exists(app_path() . '/Configs/Form.php')) {
				$config = new \App\Configs\Form;
			} else {
				$config = new \Waxis\Form\Form\Config;
			}
			$this->config = $config;
		}

		return $this->config;
	}

	public function getConfigVar ($var) {
		$config = $this->getConfig();

		return $config->$var;
	}

	public function getType () {
		if (!isset($this->type) || !$this->type) {
			throw new \Exception('Type must be specified.',1);
		}

		return $this->type;
	}

	public function getParam ($key) {
		if ($this->descriptor === null || !isset($this->descriptor[$key])) {
			return false;
		}

		return $this->descriptor[$key];
	}

	public function getData () {
		$data = $this->getParam('data');

		if (empty($data)) {
			$data = array();
		}

		$data['tree'] = $this->getNodeTreeString('-');
		$data['clone'] = trim(json_encode($this->isClone),'"');
		$data['clone-removable'] = $this->isClone && $this->isRemovable ? 'true' : 'false';

		$return = '';
		foreach ($data as $key => $value) {
			$return .= ' data-' . $key . '="' . $value . '" ';
		}

		return $return;
	}

	public function getDescriptorName () {
		return $this->descriptorName;
	}

	public function getDefaultClones () {
		if (isset($this->descriptor['clones'])) {
			return $this->descriptor['clones'];
		}

		return 0;
	}
}