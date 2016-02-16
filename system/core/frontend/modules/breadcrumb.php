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

class BreadcrumbFrontendModule extends FrontendModule
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
        $page_id = $this->getIntParam($params, 'page', 0);
        $language_id = $this->getStringParam($params, 'language', Config::get()->languages->standard);
        $divider = $this->getStringParam($params, 'divider', '');

        $path = $this->pages->getPath($page_id);
        if ($path === false) {
            return ('');
        }

        $path[] = $page_id;

        $output = '<ul>';
        $elements = array();
        foreach ($path as $page_id) {
            $caption = $this->pages->getAnyCaption($page_id, $language_id);
            $url = $this->pages->getPageUrl($page_id, $language_id);
            if (($caption !== false) && ($url !== false)) {
                $elements[] = '<li><a href="' . $url . '">' . $caption . '</a></li>';
            }
        }
        $output .= implode($divider, $elements);
        $output .= '</ul>';

        return ($output);
    }

}

?>