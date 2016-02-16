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
 * File:     function.extract.php
 * Type:     function
 * Name:     extract
 * Purpose:  loads the content of a page, extracts the 
 			 specified data and assigns it to a variable
 * -------------------------------------------------------------
 */

function smarty_function_extract($params, Smarty_Internal_Template $template)
{
    if (isset($params['page']) && isset($params['var']) && isset($params['path'])) {
        $page_id = trim($params['page']);
    } else {
        return;
    }

    if (isset($params['language'])) {
        $language_id = trim($params['language']);
    } else {
        $language_id = null;
    }

    $page_content = new PageContent();
    if ($page_content->load($page_id)) {
        $page_data = $page_content->getArray($language_id);
        $data = $page_content->extract($params['path'], $page_data);
        $template->assign($params['var'], $data);
    }
}
