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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.translate.php
 * Type:     function
 * Name:     translate
 * Purpose:  a translatet text
 * -------------------------------------------------------------
 */

function smarty_function_pageurl($params, Smarty_Internal_Template $template)
{
    $output = '';
    if (isset($params['page'])) {
        if (isset($params['language'])) {
            $language_id = $params['language'];
        } else {
            $language_id = $template->getTemplateVars('languageId');
            if ($language_id === null) {
                $language_id = Config::get()->languages->standard;
            }
        }
        $mixed_id = $params['page'];
        $page_id = false;
        $pages = new Pages();
        if (is_int($mixed_id)) {
            $page_id = $mixed_id;
        } else if (is_numeric($mixed_id)) {
            $page_id = (int)$mixed_id;
        } else if (is_string($mixed_id)) {
            $page_id = $pages->getPageIdByUniqueId($mixed_id);
        }
        if ($page_id !== false) {
            $output = $pages->getPageUrl($page_id, $language_id);
        }
    }
    return ($output);
}
