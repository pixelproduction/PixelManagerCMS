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
 *   Die nötigen tools um daten aus der Config zu bekommen
 */
require_once(realpath(dirname(__FILE__) . '/RecursiveArrayObject.php'));

final class Config
{

    private static $instance = null;
    private static $config_data = null;
    private static $config_data_object = null;

    /**
     * @see load()
     */
    private function __construct()
    {
        self::load();
    }

    /**
     *   Singleton
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
     *   Lädt die Config files und mergt sie zusammen
     *
     * @param bool $reload
     *
     * @return bool
     */
    public static function load($reload = false)
    {
        if ($reload) {
            unset(self::$config_data);
            self::$config_data = null;
            unset(self::$config_data_object);
            self::$config_data_object = null;
        }
        if (self::$config_data == null) {
            if (defined('APPLICATION_ROOT') && defined('APPLICATION_ENV')) {
                $config_core = self::mergeRecursive(
                    include(APPLICATION_ROOT . 'system/core/config/main.config.php'),
                    include(APPLICATION_ROOT . 'system/core/config/' . APPLICATION_ENV . '.config.php')
                );
                $config_custom = self::mergeRecursive(
                    include(APPLICATION_ROOT . 'system/custom/config/main.config.php'),
                    include(APPLICATION_ROOT . 'system/custom/config/' . APPLICATION_ENV . '.config.php')
                );

                $localConfigFile = APPLICATION_ROOT . 'system/custom/config/local.' . APPLICATION_ENV . '.config.php';

                if (file_exists($localConfigFile)) {
                    $config_custom = self::mergeRecursive(
                        $config_custom,
                        (array)include($localConfigFile)
                    );
                }

                self::$config_data = self::mergeRecursive($config_core, $config_custom);
                self::$config_data_object = new RecursiveArrayObject(self::$config_data);
                return (true);
            } else {
                return (false);
            }
        } else {
            return (true);
        }
    }

    /**
     * @see getObject()
     */
    public static function get()
    {
        return (self::getObject());
    }

    /**
     *   Holt das Config array
     *
     * @see load()
     * @return array of configs
     */
    public static function getArray()
    {
        self::load();
        return (self::$config_data);
    }

    /**
     * @see load()
     * @return Object
     */
    public static function getObject()
    {
        self::load();
        return (self::$config_data_object);
    }

    /**
     *   Merged die arrays zusammen
     *
     * @return array
     */
    private static function mergeRecursive()
    {
        if (func_num_args() < 2) {
            return;
        }
        $arrays = func_get_args();
        $merged = array();
        while ($arrays) {
            $array = array_shift($arrays);
            if (!is_array($array)) {
                return;
            }
            if (!$array) {
                continue;
            }
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                        $merged[$key] = self::mergeRecursive($merged[$key], $value);
                    } else {
                        $merged[$key] = $value;
                    }
                } else {
                    $merged[] = $value;
                }
            }
        }
        return $merged;
    }

}
