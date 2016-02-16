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

/**
 *   Die AutoLoader Class beitet alle Methoden um alle anderen Klassen beim spl zu registrieren
 */

require_once(realpath(dirname(__FILE__) . '/Helpers.php'));
require_once(realpath(dirname(__FILE__) . '/Config.php'));

final class AutoLoader
{

    private static $instance = null;
    private static $path_array = array();

    private function __construct()
    {
    }

    /**
     *   Singleton - bekomme die laufen instanz vom objekt
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    /**
     *   Holt den Pfad aus der config und legt diesen in der registerPath variable/array ab
     *   Meldete die Methode tryToLoadClass() bei der spl autoload an
     *
     * @see tryToLoadClass()
     */
    public static function init()
    {
        $config = Config::getArray();
        if (isset($config['autoLoader'])) {
            if (count($config['autoLoader']) > 0) {
                foreach ($config['autoLoader'] as $path) {
                    if (is_array($path)) {
                        self::registerPath($path['path'], $path['subDir'],
                            (isset($path['except'])) ? $path['except'] : array());
                    } else {
                        self::registerPath($path);
                    }
                }
            }
        }
        spl_autoload_register(array('Autoloader', 'tryToLoadClass'));
    }

    /**
     *    Prüft ob der angegebene Pfad existiert und erstellt ein array mit den dateien aus dem Pfad
     */
    public static function registerPath($path, $recursive = false, array $except = array())
    {
        if ($recursive) {
            //Add a slash on the end of the string
            $path = ($path[strlen($path) - 1] == "/") ? $path : $path . "/";
            //Register the path to the path_array
            if (array_search($path, self::$path_array) === false) {
                self::$path_array[] = $path;
            }
            //scan the path dir and get rid of the Unix dirs
            $dir = array_diff(scandir($path), array('..', '.'));
            //delete the directorys from the array that are not wanted
            $dir = array_diff($dir, $except);
            if ($dir !== false) {
                foreach ($dir as $registerNewDir) {
                    if (is_dir($path . $registerNewDir)) {
                        self::registerPath($path . $registerNewDir, $recursive, $except);
                    }
                }
            }
        } else {
            if (array_search($path, self::$path_array) === false) {
                self::$path_array[] = $path;
            }
        }
    }

    /**
     *   Wird von der spl_autoload_register aufgerufen ein neues Objekt erstellt wird und dieses noch
     *   nicht bekannt ist
     *
     * @see init()
     */
    public static function tryToLoadClass($class_name)
    {
        if (count(self::$path_array) > 0) {
            foreach (self::$path_array as $path) {
                if (is_file($path . $class_name . '.php')) {
                    include_once($path . $class_name . '.php');
                    return (true);
                }
            }
        }
        return (false);
    }
}
