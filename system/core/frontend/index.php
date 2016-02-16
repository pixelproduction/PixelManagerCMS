<?php
	
	// Wenn im Development-Modus, die Custom-Konfiguration überprüfen,
	// einige Dinge müssen zwingend gesetzt sein
	if (APPLICATION_ENV == 'development') {
		$config = Config::getArray();
		$configuration_ok = true;
		if (!isset($config['database'])) {
			$configuration_ok = false;
		}
		if (!isset($config['languages']['standard'])) {
			$configuration_ok = false;
		}
		if (!isset($config['languages']['list'])) {
			$configuration_ok = false;
		} else {
			if (!is_array($config['languages']['list'])) {
				$configuration_ok = false;
			} else {
				if (count($config['languages']['list']) < 1) {
					$configuration_ok = false;
				}
			}
		}
		if (!$configuration_ok) {
			Helpers::fatalError('The system is not properly configured (in /system/custom/config/main.config.php)', true);
		}
	}
	
	// Datenstruktur, Plugins, Module, Modifikatoren laden
	DataStructure::load();
	Plugins::load();
	FrontendModules::load();
	DataModifiers::load();
	
	// Frontend-Controller-Klassen-Datei laden
	$controller_file_name = Config::get()->frontendController->classFile;
	if (!file_exists($controller_file_name)) {
		Helpers::fatalError('Frontend controller class file not found (' . $controller_file_name . ' doesn\'t exist)!', true);
	}
	require_once($controller_file_name);
	
	// Klasse instanziieren
	$controller_class_name = Config::get()->frontendController->className;
	if (!class_exists($controller_class_name)) {
		Helpers::fatalError('Frontend controller class not found (class "' . $controller_class_name . '" doesn\'t exist in ' . $controller_file_name . ')!', true);
	}
	$controller = new $controller_class_name();

	Registry::set('frontendController', $controller);
	
	// Und los...
	$controller->run();
	
?>