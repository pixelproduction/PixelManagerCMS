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

final class Translate
{
    private static $instance = null;
    private static $strings = array();
    private static $id = '';

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

    public static function loadStrings($file, $id = '')
    {
        if (is_file($file)) {
            $temp = include($file);
            if (is_array($temp)) {
                self::$strings = array_merge(self::$strings, $temp);
                self::$id = $id;
            }
        } else {
            Helpers::fatalError('The language file [' . $file . '] could not be found.');
        }
    }

    public static function clearStrings()
    {
        self::$strings = array();
    }

    public static function get($key)
    {
        if (isset(self::$strings[$key])) {
            return (self::$strings[$key]);
        } else {
            return ($key);
        }
    }

    public static function getStrings()
    {
        return (self::$strings);
    }

    public static function getId()
    {
        return (self::$id);
    }

}
