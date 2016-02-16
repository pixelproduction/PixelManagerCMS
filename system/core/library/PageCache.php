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

final class PageCache
{

    const CACHE_FILENAME = '-cached.html';

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

    public static function isPageAvailable($page_id, $language_id)
    {
        if (Config::get()->cacheActivated) {
            if (Settings::get('useCache', false) === true) {
                $available = Db::getFirst(
                    "SELECT * FROM [prefix]page_cache WHERE (`page-id` = :pageId) AND (`language-id` = :languageId)",
                    array(
                        ':pageId' => $page_id,
                        ':languageId' => $language_id
                    )
                );
                if ($available !== false) {
                    $pages = new Pages();
                    $page_properties = $pages->getProperties($page_id);
                    if ($page_properties['cachable'] > 0) {
                        $cache_lifetime = Settings::get('cacheLifetime', 0);
                        if ((($available['timestamp'] + $cache_lifetime) >= time()) || ($cache_lifetime == 0)) {
                            $cache_file = $pages->getPagePublishedFolder($page_id,
                                    $page_properties) . $language_id . self::CACHE_FILENAME;
                            if (is_file($cache_file)) {
                                return (true);
                            }
                        }
                    }
                }
            }
        }
        return (false);
    }

    public static function getPage($page_id, $language_id)
    {
        if (self::isPageAvailable($page_id, $language_id)) {
            $pages = new Pages();
            $cache_file = $pages->getPagePublishedFolder($page_id) . $language_id . self::CACHE_FILENAME;
            if (is_file($cache_file)) {
                return (@file_get_contents($cache_file));
            }
        }
        return (false);
    }

    public static function cachePage($page_id, $language_id, $content)
    {
        if (Config::get()->cacheActivated) {
            if (Settings::get('useCache', false) === true) {
                $pages = new Pages();
                $page_properties = $pages->getProperties($page_id);
                if ($page_properties['cachable'] > 0) {
                    $page_folder = $pages->getPagePublishedFolder($page_id, $page_properties);
                    if (is_dir(rtrim($page_folder, "/"))) {
                        $cache_file = $page_folder . $language_id . self::CACHE_FILENAME;
                        $res = @file_put_contents($cache_file, $content);
                        if ($res !== false) {
                            Db::delete(
                                'page_cache',
                                "(`page-id` = :pageId) AND (`language-id` = :languageId)",
                                array(
                                    ':pageId' => $page_id,
                                    ':languageId' => $language_id
                                )
                            );
                            Db::insert(
                                'page_cache',
                                array(
                                    'page-id' => $page_id,
                                    'language-id' => $language_id,
                                    'timestamp' => time()
                                )
                            );
                        }
                    }
                }
            }
        }
        return (true);
    }

    public static function invalidatePage($page_id, $language_id = null, $resursive = false)
    {
        if ($language_id !== null) {
            return (Db::delete('page_cache', "(`page-id` = :pageId) AND (`language-id` = :languageId)",
                array(':pageId' => $page_id, ':languageId' => $language_id)));
        } else {
            return (Db::delete('page_cache', "`page-id` = :pageId", array(':pageId' => $page_id)));
        }
        if ($resursive) {
            $pages = new Pages();
            $children = $pages->getChildren($page_id, false);
            if ($children !== false) {
                if (count($children) > 0) {
                    foreach ($children as $child) {
                        self::invalidatePage($child['id'], $language_id, true);
                    }
                }
            }
        }
    }

    public static function invalidateAll()
    {
        return (Db::delete('page_cache', '1', array()));
    }

}
