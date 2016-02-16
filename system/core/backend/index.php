<?php

/**
 * PixelManager CMS (Community Edition)
 * Copyright (C) 2016 PixelProduction (http://www.pixelproduction.de)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$config = Config::getArray();

// Wenn im Development-Modus, die Custom-Konfiguration überprüfen,
// einige Dinge müssen zwingend gesetzt sein
if (APPLICATION_ENV == 'development') {
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

// Wenn in der Session eine Sprach-ID steht, diese laden, ansonsten die Standard-Sprache
$languages = $config['backendLanguages']['list'];
$language_id = Config::get()->backendLanguages->standard;
if (isset($_SESSION['pixelmanager']['backendLanguage'])) {
    if (isset($languages[$_SESSION['pixelmanager']['backendLanguage']])) {
        $language_id = $_SESSION['pixelmanager']['backendLanguage'];
    }
}
setlocale(LC_ALL, $languages[$language_id]['locale']);
if (is_array($languages[$language_id]['translationServerside'])) {
    if (count($languages[$language_id]['translationServerside']) > 0) {
        foreach ($languages[$language_id]['translationServerside'] as $translation_file) {
            Translate::loadStrings($translation_file, $language_id);
        }
    }
} else {
    if ($languages[$language_id]['translationServerside'] != '') {
        Translate::loadStrings($languages[$language_id]['translationServerside'], $language_id);
    }
}

// Datenstruktur laden (ist hier eigentlich nocht nicht nötig,
// aber so wird überprüft, ob alle nötigen Dateien existieren
// und die Arrays zurückgegebeb werden)
DataStructure::load();

// Plugins laden
Plugins::load();

// Falls die gesonderte Seite mit den globalen Elementen verwendet werden soll,
// diese ggf. anlegen
$pages = new Pages();
if ($pages->isGlobalElementsPageTemplateAvailable()) {
    $pages->createGlobalElementsPage();
}

// Request-Pfad auslesen
$path = Request::path();

// Feststellen, ob ein Modul geladen werden soll, oder Grundsystem
$is_module = false;
if ((isset($path[0])) && (isset($path[1])) && (isset($path[2]))) {
    if (($path[0] == 'modules') && (trim($path[1]) != '') && (trim($path[2]) != '')) {
        $is_module = true;
    }
}

if ($is_module) {

    // ************************************************************************************
    // Modul
    // ************************************************************************************

    // Modus
    $mode = 'html-output';
    if (isset($path[1])) {
        $mode = trim(UTF8String::strtolower($path[1]));
    }

    // Passendes Modul suchen
    $module_url = trim($path[2]);
    $module = array();
    $module_id = null;
    if (isset($config['backendModules'])) {
        $modules = $config['backendModules'];
        foreach ($modules as $key => $value) {
            if ($value['url'] == $module_url) {
                $module = $value;
                $module_id = $key;
            }
        }
        if ($module_id === null) {
            Helpers::fatalError('Page not found (A module with the URL "' . $module_url . '" was not found.)', true);
        }
    } else {
        Helpers::fatalError('Page not found (A module with the URL "' . $module_url . '" was not found. No modules configured!)',
            true);
    }

    // Dazu passender Controller
    $controller_file = '';
    switch ($mode) {
        case 'html-output':
            if ((isset($module['htmlOutputControllerFile'])) && (isset($module['htmlOutputControllerClass']))) {
                $controller_file = $module['htmlOutputControllerFile'];
                $controller_class = $module['htmlOutputControllerClass'];
            }
            break;
        case 'data-exchange':
            if ((isset($module['dataExchangeControllerFile'])) && (isset($module['dataExchangeControllerClass']))) {
                $controller_file = $module['dataExchangeControllerFile'];
                $controller_class = $module['dataExchangeControllerClass'];
            }
            break;
    }
    if (($controller_file == '') || ($controller_class == '')) {
        Helpers::fatalError('Page not found (No controller file specified for Module with ID "' . $module_id . '")!',
            true);
    }
    if (!file_exists($controller_file)) {
        Helpers::fatalError('Page not found (' . $controller_file . ' doesn\'t exist)!', true);
    }

    // Datei inkludieren
    require_once($controller_file);

    // Prüfen, ob Klasse existiert
    if (!class_exists($controller_class)) {
        Helpers::fatalError('Page not found (class "' . $controller_class . '" doesn\'t exist in ' . $controller_file . ')!',
            true);
    }

    // Klasse instanziieren
    $controller = new $controller_class($module_id, $module);

    // Klassen-Methode feststellen
    if (isset($path[3])) {
        $action = $path[3];
    } else {
        $action = 'default';
    }
    $method = $action . 'Action';

    // Prüfen, ob Methode existiert
    if (!method_exists($controller, $method)) {
        Helpers::fatalError('Page not found (class-method "' . $method . '" doesn\'t exist in class "' . $controller_class . '" in ' . $controller_file . ')!',
            true);
    }

    // View erzeugen und mit Controller verbinden
    $view = new View();
    $view->assignTemplate($module['htmlOutputView']);
    $view->assign('baseUrl', Config::get()->baseUrl);
    $view->assign('moduleUrl', Config::get()->baseUrl . 'admin/modules/' . $mode . '/' . $module['url'] . '/');
    if (isset($module['publicFolderUrl'])) {
        $view->assign('modulePublicUrl', Config::get()->baseUrl . $module['publicFolderUrl']);
    }
    $view->assign('publicUrl', Config::get()->baseUrl . 'system/core/backend/public/');
    $view->assign('backendLanguage', $language_id);
    $controller->assignView($view);

    // Action-Methode ausführen
    $controller->callActionMethod($action);

    // Ausgabe des Views...
    if ($controller->getView() != null) {
        $controller->getView()->output();
    }

} else {

    // ************************************************************************************
    // Grundsystem
    // ************************************************************************************

    // Mehr als 3 Ebenen sind nicht möglich
    if (count($path) > 3) {
        Helpers::fatalError('Page not found (path to deep, no matching controller found)!', true);
    }

    // 1. Ebene : Pfad
    if (isset($path[0])) {
        $folder = $path[0];
    } else {
        $folder = 'html-output';
    }
    if (!file_exists(APPLICATION_ROOT . 'system/core/backend/controllers/' . $folder)) {
        Helpers::fatalError('Page not found (system/core/backend/controllers/' . $folder . ' doesn\'t exist)!', true);
    }

    // 2. Ebene : Datei / Klasse
    if (isset($path[1])) {
        $file = ucfirst($path[1]) . '.php';
        $class = ucfirst($path[1]) . 'Controller';
        $module = $path[1];
    } else {
        $file = 'Main.php';
        $class = 'MainController';
        $module = 'main';
    }
    if (!file_exists(APPLICATION_ROOT . 'system/core/backend/controllers/' . $folder . '/' . $file)) {
        Helpers::fatalError('Page not found (system/core/backend/controllers/' . $folder . '/' . $file . ' doesn\'t exist)!',
            true);
    }

    if ($folder == 'custom') {

        // **************************************************************
        // Ein Custom-Controller, einfach nur die Datei inkludieren
        // **************************************************************

        require_once(APPLICATION_ROOT . 'system/core/backend/controllers/' . $folder . '/' . $file);

    } else {

        // **************************************************************
        // Ein Standard-Controller,
        // Klasse instanziieren, View erzeugen etc ...
        // **************************************************************

        // 3. Ebene : Klassen-Methode
        if (isset($path[2])) {
            $action = $path[2];
        } else {
            $action = 'default';
        }
        $method = $action . 'Action';

        // Datei inkludieren
        require_once(APPLICATION_ROOT . 'system/core/backend/controllers/' . $folder . '/' . $file);

        // Prüfen, ob Klasse existiert
        if (!class_exists($class)) {
            Helpers::fatalError('Page not found (class "' . $class . '" doesn\'t exist in system/core/backend/controllers/' . $folder . '/' . $file . ')!',
                true);
        }

        // Klasse instanziieren
        $controller = new $class($module);

        // Prüfen, ob Methode existiert
        if (!method_exists($controller, $method)) {
            Helpers::fatalError('Page not found (class-method "' . $method . '" doesn\'t exist in class "' . $class . '" ist in system/core/backend/controllers/' . $folder . '/' . $file . ')!',
                true);
        }

        // View erzeugen und mit Controller verbinden
        $view = new View();
        $view->assignTemplate(APPLICATION_ROOT . 'system/core/backend/views/' . $folder . '/' . UTF8String::strtolower($file));
        $view->assign('baseUrl', Config::get()->baseUrl);
        $view->assign('moduleUrl', Config::get()->baseUrl . 'admin/' . $folder . '/' . $module . '/');
        $view->assign('publicUrl', Config::get()->baseUrl . 'system/core/backend/public/');
        $view->assign('backendLanguage', $language_id);
        $controller->assignView($view);

        // Action-Methode ausführen
        $controller->callActionMethod($action);

        // Ausgabe des Views...
        if ($controller->getView() != null) {
            $controller->getView()->output();
        }

    }

}
