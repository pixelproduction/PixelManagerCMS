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

require_once(realpath(dirname(__FILE__) . '/Helpers.php'));
require_once(realpath(dirname(__FILE__) . '/Db.php'));

final class Settings
{

    private static $instance = null;
    private static $settings = null;

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

    public static function load($reload = false)
    {
        if ((self::$settings === null) || ($reload === true)) {
            self::$settings = array();
            $settings = Db::getFirst('SELECT json FROM [prefix]settings');
            if ($settings !== false) {
                if (isset($settings['json'])) {
                    if (is_string($settings['json'])) {
                        if (trim($settings['json']) != '') {
                            self::$settings = json_decode($settings['json'], true);
                        }
                    }
                }
            }
        }
    }

    public static function save()
    {
        if (self::$settings !== null) {
            // Da nicht gewährleistet ist, dass überhaupt ein Eintrag existiert, oder dass es wirklich nur einer ist,
            // einfach die Settings-Tabelle komplett platt machen und den Eintrag neu erzeugen.
            Db::delete('settings', '1', array());
            Db::insert('settings', array('json' => json_encode(self::$settings)));
        }
    }

    public static function get($key, $std_value = null)
    {
        self::load();
        if (isset(self::$settings[$key])) {
            return (self::$settings[$key]);
        } else {
            return ($std_value);
        }
    }

    public static function set($key, $value)
    {
        self::load();
        if (is_string($key)) {
            if (trim($key != '')) {
                self::$settings[$key] = $value;
            }
        }
    }

    public static function getAll()
    {
        self::load();
        return (self::$settings);
    }

    public static function setAll($settings_array)
    {
        if (is_array($settings_array)) {
            self::$settings = $settings_array;
        }
    }

}
