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

final class DataStructure
{
    private static $instance = null;
    private static $pages = null;
    private static $pages_object = null;
    private static $elements = null;
    private static $elements_object = null;

    private function __construct()
    {
        self::load();
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

    protected static function loadArray($file, $can_not_be_empty)
    {
        if (!file_exists($file)) {
            Helpers::fatalError('DataStructure: Missing file: ' . $file . '!');
        }
        $array = include($file);
        if (!is_array($array)) {
            Helpers::fatalError('DataStructure: file must return an array in: ' . $file . '!');
        }
        if (($can_not_be_empty) && (count($array) < 1)) {
            Helpers::fatalError('DataStructure: the array is empty in: ' . $file . '!');
        }
        return ($array);
    }

    public static function load($reload = false)
    {
        if ($reload) {
            unset(self::$pages);
            self::$pages = null;
            unset(self::$pages_object);
            self::$pages_object = null;
            unset(self::$elements);
            self::$elements = null;
            unset(self::$elements_object);
            self::$elements_object = null;
        }
        if (self::$pages == null) {

            self::$pages = self::loadArray(APPLICATION_ROOT . 'system/custom/data-structure/pages.php', true);
            foreach (self::$pages as $key => $page) {
                self::$pages[$key]['structure'] = self::loadArray(APPLICATION_ROOT . 'system/custom/data-structure/pages/' . $page['structure'],
                    true);
            }

            self::$elements = self::loadArray(APPLICATION_ROOT . 'system/custom/data-structure/elements.php', false);
            if (count(self::$elements) > 0) {
                foreach (self::$elements as $key => $element) {
                    self::$elements[$key]['structure'] = self::loadArray(APPLICATION_ROOT . 'system/custom/data-structure/elements/' . $element['structure'],
                        true);
                }
            }

            self::$pages_object = new RecursiveArrayObject(self::$pages);
            self::$elements_object = new RecursiveArrayObject(self::$elements);

        }
    }

    public static function pages()
    {
        return (self::pagesObject());
    }

    public static function pagesArray()
    {
        self::load();
        return (self::$pages);
    }

    public static function pagesObject()
    {
        self::load();
        return (self::$pages_object);
    }

    public static function elements()
    {
        return (self::elementsObject());
    }

    public static function elementsArray()
    {
        self::load();
        return (self::$elements);
    }

    public static function elementsObject()
    {
        self::load();
        return (self::$elements_object);
    }

}
