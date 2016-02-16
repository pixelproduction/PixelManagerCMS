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

class CategoryFrontendModule extends FrontendModule
{

    protected $pages = null;

    public function __construct()
    {
        $this->config = array();
        $this->pages = new Pages();
    }

    public function output($params, $smarty)
    {
        $output = '';
        $level = $this->getIntParam($params, 'level', 0);
        $page_id = $this->getIntParam($params, 'page', 0);
        $language_id = $this->getStringParam($params, 'language', Config::get()->languages->standard);

        $path = $this->pages->getPath($page_id);
        if ($path === false) {
            return ('');
        }

        if (count($path) == 0) {
            $path[] = $page_id;
        }

        if (count($path) < $level) {
            return ('');
        }

        $parent_id = $path[count($path) - 1];
        if ($level > 0) {
            $parent_id = $path[$level - 1];
        }

        $output = $this->pages->getAnyCaption($parent_id, $language_id);
        if ($output === false) {
            $output = '';
        }

        return ($output);
    }

}
