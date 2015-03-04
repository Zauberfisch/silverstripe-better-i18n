<?php

/**
 * @author zauberfisch
 */
class BetterI18nTextCollectorTask extends BuildTask {
	protected $title = "Better i18n Textcollector Task";

	public function init() {
		parent::init();

		$canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
		if (!$canAccess) {
			return Security::permissionFailure($this);
		}
	}

	public function run($request) {
		increase_time_limit_to();
		$modules = ['mysite'];
		foreach (['module', 'modules'] as $key) {
			if ($request->getVar($key)) {
				$modules = explode(',', $request->getVar($key));
				break;
			}
		}
		$c = new BetterI18nTextCollector('raw', $modules);
		$entitiesByModule = $c->collect();

		DB::alteration_message("legend:");
		DB::alteration_message("[untranslated] class.key: value");
		DB::alteration_message("[translated] class.key: modified value (original value)", 'changed');
		DB::alteration_message("[new] class.key: new value", 'created');
		DB::alteration_message("[removed] class.key: value", 'deleted');
		DB::alteration_message("");

		$locales = $request->getVar('targetlocale') ? preg_replace('/[^a-zA-Z0-9_\-,]/', '', $request->getVar('targetlocale')) : 'en';
		$locales = explode(',', $locales);
		foreach ($locales as $locale) {
			$_target = "lang/$locale.yml";
			// Write each module language file
			if ($entitiesByModule) {
				foreach ($entitiesByModule as $module => $entities) {
					unset($entities['SiteTree.PLURALNAME']);
					unset($entities['SiteTree.SINGULARNAME']);
					$folder = $c->baseSavePath . '/' . $module;
					$target = "$folder/$_target";

					DB::alteration_message("");
					DB::alteration_message("locale '$locale' - module '$module':");

					// TODO use the existing yml adapter for this
					$merged = [];
					$existing = [];
					if (file_exists($target)) {
						$existingTmp = file_get_contents($target);
						$existingTmp = \Symfony\Component\Yaml\Yaml::parse($existingTmp);
						$existingTmp = $existingTmp[array_keys($existingTmp)[0]];
						foreach ($existingTmp as $class => $arr) {
							foreach ($arr as $key => $val) {
								$key = preg_replace('/^__/', '', $key);
								$existing["$class.$key"] = $val;
							}
						}
					}
					foreach ($entities as $key => $val) {
						$val = is_array($val) ? $val[0] : $val;
						if (isset($existing[$key])) {
							if ($existing[$key] != $val) {
								$merged[$key] = [$existing[$key]];
								DB::alteration_message(sprintf(
									'%s: %s (%s)',
									$key,
									htmlentities($merged[$key][0]),
									htmlentities($val)
								), 'changed');
							} else {
								$merged[preg_replace('/\.([^\.]*)$/', '.__$1', $key)] = [$existing[$key]];
								DB::alteration_message(sprintf('%s: %s', $key, htmlentities($existing[$key])));
							}
						} else {
							$merged[preg_replace('/\.([^\.]*)$/', '.__$1', $key)] = [$val];
							DB::alteration_message(sprintf('%s: %s', $key, htmlentities($val)), 'created');
						}
					}
					foreach ($existing as $key => $val) {
						if (!isset($merged[$key]) && !isset($merged[preg_replace('/\.([^\.]*)$/', '.__$1', $key)])) {
							DB::alteration_message(sprintf('%s: %s', $key, htmlentities($val)), 'deleted');
						}
					}
					$c->getWriter()->write($merged, $locale, $folder);
				}
			}
		}
	}
}
