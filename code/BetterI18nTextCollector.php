<?php

/**
 * @author zauberfisch
 */
class BetterI18nTextCollector extends i18nTextCollector {
	protected $restrictedModules = null;

	/**
	 * @param string $locale
	 * @param array $restrictedModules
	 */
	public function __construct($locale = null, $restrictedModules = null) {
		$this->restrictedModules = $restrictedModules;
		parent::__construct($locale);
	}

	public function collect($restrictToModules = null, $mergeWithExisting = null) {
		if (!is_null($restrictToModules) || !is_null($mergeWithExisting)) {
			throw new InvalidArgumentException("use of BetterI18nTextCollector->collect() with arguments is deprecated");
		}
		return parent::collect($this->restrictedModules, false);
	}

	protected function processModule($module) {
		//if (!is_array($this->restrictedModules) || in_array($module, $this->restrictedModules)) {
		return parent::processModule($module);
		//}
		//return [];
	}

	public function collectFromEntityProviders($filePath, $module = null) {
		$entities = [];

		// HACK Ugly workaround to avoid "Cannot redeclare class PHPUnit_Framework_TestResult" error
		// when running text collector with PHPUnit 3.4. There really shouldn't be any dependencies
		// here, but the class reflection enforces autloading of seemingly unrelated classes.
		// The main problem here is the CMSMenu class, which iterates through test classes,
		// which in turn trigger autoloading of PHPUnit.
		$phpunitwrapper = PhpUnitWrapper::inst();
		$phpunitwrapper->init();

		$classes = ClassInfo::classes_for_file($filePath);
		if ($classes) {
			foreach ($classes as $class) {
				// Not all classes can be instanciated without mandatory arguments,
				// so entity collection doesn't work for all SilverStripe classes currently
				// Requires PHP 5.1+
				if (class_exists($class) && in_array('i18nEntityProvider', class_implements($class))) {
					$reflectionClass = new ReflectionClass($class);
					if ($reflectionClass->isAbstract()) {
						continue;
					}

					$obj = singleton($class);
					$entities = array_merge($entities, (array)$obj->provideI18nEntities());
					if ($obj->is_a('DataObject')) {
						foreach (['db', 'has_one', 'has_many', 'many_many', 'belongs_many_many'] as $type) {
							foreach ((array)Config::inst()->get($obj->class, $type, Config::UNINHERITED) as $name => $spec) {
								$entities["{$obj->class}.{$type}_{$name}"] = [
									FormField::name_to_label($name)
								];
							}
						}
					}
				}
			}
		}

		ksort($entities);
		return $entities;
	}

	public function collectFromTemplate($content, $fileName, $module, &$parsedFiles = []) {
		$entities = [];

		// use parser to extract <%t style translatable entities
		$translatables = i18nTextCollector_Parser::GetTranslatables($content);
		$entities = array_merge($entities, (array)$translatables);

		// use the old method of getting _t() style translatable entities
		// Collect in actual template
		if (preg_match_all('/(_t\([^\)]*?\))/ms', $content, $matches)) {
			foreach ($matches[1] as $match) {
				$entities = array_merge($entities, $this->collectFromCode($match, $module));
			}
		}

		foreach ($entities as $entity => $spec) {
			unset($entities[$entity]);
			$entities[$this->normalizeEntity($entity, $module)] = $spec;
		}
		ksort($entities);

		return $entities;
	}

	protected function getFilesRecursive($folder, $fileList = null, $type = null, $folderExclude = null) {
		if (!$folderExclude) {
			$folderExclude = '/\/(tests)$/';
		}
		if (!$fileList) {
			$fileList = [];
		}
		$items = scandir($folder);
		$isValidFolder = (
			!in_array('_manifest_exclude', $items)
			&& !preg_match($folderExclude, $folder)
		);

		if ($items && $isValidFolder) {
			foreach ($items as $item) {
				if (substr($item, 0, 1) == '.') {
					continue;
				}
				if (substr($item, -4) == '.php' && (!$type || $type == 'php')) {
					$fileList[] = "$folder/$item";
				} elseif (substr($item, -3) == '.ss' && (!$type || $type == 'ss')) {
					$fileList[] = "$folder/$item";
				} elseif (is_dir("$folder/$item")) {
					$fileList = $this->getFilesRecursive("$folder/$item", $fileList, $type, $folderExclude);
				}
			}
		}
		return $fileList;
	}
}
