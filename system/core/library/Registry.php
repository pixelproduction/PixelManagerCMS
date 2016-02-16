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

final class Registry
{
    private static $registry = array();

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


    public static function get($key, $std_value = null)
    {
        if (isset(self::$registry[$key])) {
            return (self::$registry[$key]);
        } else {
            return ($std_value);
        }
    }

    public static function set($key, $value)
    {
        if (is_string($key)) {
            if (trim($key != '')) {
                self::$registry[$key] = $value;
                return (true);
            }
        }
        return (false);
    }

    public static function delete($key)
    {
        if (is_string($key)) {
            if (trim($key != '')) {
                if (isset(self::$registry[$key])) {
                    unset(self::$registry[$key]);
                    return (true);
                }
            }
        }
        return (false);
    }

    public static function getAll()
    {
        return (self::$registry);
    }

}
