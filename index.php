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

// -------------------------------------------------------------------
// Bootstrap
// -------------------------------------------------------------------

// PHP 5.4 is required
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('This software needs PHP Version 5.4 or higher / Diese Software benötigt mindestens PHP 5.4 oder höher');
}

// Start a session
session_start();

// Store the root path in a global constant
if (!defined('APPLICATION_ROOT')) {
    define('APPLICATION_ROOT', realpath(dirname(__FILE__)) . '/');
}

// Store the enviroment in a global constant
// (the enviroment can be defined in the .htaccess file)
if (!defined('APPLICATION_ENV')) {
    if (getenv('APPLICATION_ENV')) {
        define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
    } else {
        define('APPLICATION_ENV', 'development');
    }
}

// Create the constant APPLICATION_CONTAINER (for using the CMS in a Vagrantbox)
if (!defined('APPLICATION_CONTAINER')) {
    if (getenv('APPLICATION_CONTAINER')) {
        define('APPLICATION_CONTAINER', getenv('APPLICATION_CONTAINER'));
    } else {
        define('APPLICATION_CONTAINER', '');
    }
}

// Load the config files
require_once(APPLICATION_ROOT . 'system/core/library/Config.php');
$config = Config::get();

// Set the PHP Error-Reporting according to the settings in the config
if (isset($config['phpErrorReporting']['displayErrors'])) {
    ini_set('display_errors', $config['phpErrorReporting']['displayErrors']);
}
if (isset($config['phpErrorReporting']['errorReportingValue'])) {
    error_reporting($config['phpErrorReporting']['errorReportingValue']);
}
if (isset($config['phpErrorReporting']['logErrors'])) {
    ini_set('log_errors', $config['phpErrorReporting']['logErrors']);
}
if (isset($config['phpErrorReporting']['errorLogFile'])) {
    if ($config['phpErrorReporting']['errorLogFile'] != '') {
        ini_set('error_log', $config['phpErrorReporting']['errorLogFile']);
    }
}

// Load and initialize the AutoLoader class
require_once(APPLICATION_ROOT . 'system/core/library/AutoLoader.php');
AutoLoader::init();

// Start the InvokeConfig mechanism
InvokeConfig::init();

// Set the timezone
if (function_exists("date_default_timezone_set")) {
    if (isset($config['timezone'])) {
        date_default_timezone_set($config['timezone']);
    } else {
        date_default_timezone_set('Europe/Berlin');
    }
}

// Set up the FileUtils-library
if (isset($config['fileUtils'])) {
    FileUtils::setChmodSettings(
        $config['fileUtils']['useChmod'],
        $config['fileUtils']['directoryMode'],
        $config['fileUtils']['fileMode']
    );
}

// delete the global variable $config
unset($config);

// Inspect the request URI to determine if the front- or the backend was requested
if (Request::isFrontend()) {
    require_once(APPLICATION_ROOT . 'system/core/frontend/index.php');
} else {
    require_once(APPLICATION_ROOT . 'system/core/backend/index.php');
}
