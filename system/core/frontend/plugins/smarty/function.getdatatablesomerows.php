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

function smarty_function_getdatatablesomerows($params, Smarty_Internal_Template $template)
{
    if ((!isset($params['class'])) || (!isset($params['var'])) || (!isset($params['rows']))) {
        return;
    }

    if (isset($params['page'])) {
        $page_id = $params['page'];
    } else {
        $page_id = $template->getTemplateVars('pageId');
    }

    if (isset($params['language'])) {
        $language_id = $params['language'];
    } else {
        $language_id = $template->getTemplateVars('languageId');
        if ($language_id === null) {
            $language_id = Config::get()->languages->standard;
        }
    }

    // Sicherstellen, dass $id_list auch wirklich ein Array ist
    // es ist auch möglich, einen kommaseparierten String zu übergeben
    $id_list = $params['rows'];
    if (!is_array($id_list)) {
        if (is_numeric($id_list)) {
            $id_list = array(intval($id_list));
        } else {
            $id_list = strval($id_list);
            $id_list = explode(',', $id_list);
            if (count($id_list) > 0) {
                foreach ($id_list as &$row_id) {
                    $row_id = intval(trim($row_id));
                }
            }
        }
    }

    $data_table = new $params['class']();
    $data = $data_table->getSomeRowsForFrontend($id_list, $page_id, $language_id);
    $template->assign($params['var'], $data);
}
