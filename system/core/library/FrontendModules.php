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

final class FrontendModules
{

    private static $instance = null;
    protected static $modules = array();

    private function __construct()
    {
    }

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

    public static function isModuleLoaded($id)
    {
        return (isset(self::$modules[$id]));
    }

    public static function loadClass($id, $class_path, $class_name)
    {
        if (self::isModuleLoaded($id)) {
            return (false);
        }

        if (!file_exists($class_path)) {
            Helpers::debugError('FrontendModules: Class file not found (' . $class_name . ' doesn\'t exist)!');
            return (false);
        }
        require_once($class_path);

        if (!class_exists($class_name)) {
            Helpers::debugError('FrontendModules: Class not found (class "' . $class_name . '" doesn\'t exist in ' . $class_path . ')!');
        }

        $module = new $class_name();
        self::$modules[$id] = $module;

        return ($module);
    }

    public static function load()
    {
        $config = Config::getArray();
        if (count($config['frontendModules']) > 0) {
            foreach ($config['frontendModules'] as $id => $module) {
                $last_loaded_module = self::loadClass($id, $module['classFile'], $module['className']);
                if ($last_loaded_module !== false) {
                    if (isset($module['config'])) {
                        $last_loaded_module->init($module['config']);
                    } else {
                        $last_loaded_module->init(array());
                    }
                }
            }
        }
    }

    public static function getInstanceOf($id)
    {
        if (self::isModuleLoaded($id)) {
            return (self::$modules[$id]);
        } else {
            return (null);
        }
    }

}
