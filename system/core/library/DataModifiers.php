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

final class DataModifiers
{

    private static $instance = null;
    protected static $modifiers = array();

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

    public static function isModifierLoaded($id)
    {
        return (isset(self::$modifiers[$id]));
    }

    public static function loadClass($id, $class_path, $class_name)
    {
        if (self::isModifierLoaded($id)) {
            return (false);
        }

        if (!file_exists($class_path)) {
            Helpers::debugError('DataModifiers: Class file not found (' . $class_name . ' doesn\'t exist)!');
            return (false);
        }
        require_once($class_path);

        if (!class_exists($class_name)) {
            Helpers::debugError('DataModifiers: Class not found (class "' . $class_name . '" doesn\'t exist in ' . $class_path . ')!');
        }

        $modifier = new $class_name();
        self::$modifiers[$id] = $modifier;

        return ($modifier);
    }

    public static function getInstanceOf($id)
    {
        if (self::isModifierLoaded($id)) {
            return (self::$modifiers[$id]);
        } else {
            return (null);
        }
    }

    public static function load()
    {
        $config = Config::getArray();
        if (count($config['dataModifiers']) > 0) {
            foreach ($config['dataModifiers'] as $id => $modifier) {
                self::loadClass($id, $modifier['classFile'], $modifier['className']);
            }
        }

    }

    public static function applyAll(&$data, $structure, $parameters)
    {
        $config = Config::getArray();
        $modifier_list = array();
        if (count($config['dataModifiers']) > 0) {
            foreach ($config['dataModifiers'] as $id => $modifier) {
                $modifier_list[$modifier['position']] = $id;
            }
        }
        ksort($modifier_list, SORT_NUMERIC);
        self::applySelected($modifier_list, $data, $structure, $parameters);
    }

    public static function applySelected($modifier_list, &$data, $structure, $parameters)
    {
        if (count($modifier_list) > 0) {
            foreach ($modifier_list as $modifier_id) {
                self::apply($modifier_id, $data, $structure, $parameters);
            }
        }
    }

    public static function apply($modifier_id, &$data, $structure, $parameters)
    {
        if (self::isModifierLoaded($modifier_id)) {
            $modifier = self::getInstanceOf($modifier_id);
            $modifier->apply($data, $structure, $parameters);
        } else {
            Helpers::debugError('DataModifiers: A modifier with the ID "' . $modifier_id . '" was not loaded.');
        }
    }

}
