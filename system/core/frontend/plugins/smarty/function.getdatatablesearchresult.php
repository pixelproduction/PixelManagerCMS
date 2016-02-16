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

function smarty_function_getdatatablesearchresult($params, Smarty_Internal_Template $template)
{
    if ((!isset($params['class'])) || (!isset($params['var'])) || (!isset($params['search']))) {
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

    if (isset($params['columns'])) {
        $columns = $params['columns'];
        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }
    } else {
        $columns = null;
    }

    if (isset($params['connected'])) {
        $connected = $params['connected'];
        if (!is_bool($connected)) {
            $connected = ($connected == 'true') ? true : false;
        }
    } else {
        $connected = false;
    }

    $data_table = new $params['class']();
    $data = $data_table->getSearchResultForFrontend($params['search'], $columns, $page_id, $language_id);

    if ($connected && is_array($data)) {
        $connections = $data_table->getConnectedDataTables();
        if (count($connections) > 0) {

            $already_found_row_ids = array_map(
                function ($row) {
                    return (intval($row['id']));
                },
                $data
            );

            foreach ($connections as $connection_id => $connection) {

                $connected_data_table = new $connection['dataTableClassName']();
                $connected_rows = $connected_data_table->getSearchResultForFrontend($params['search'], null, $page_id,
                    $language_id);

                if (count($connected_rows) > 0) {

                    $connected_row_ids = array_map(
                        function ($row) {
                            return (intval($row['id']));
                        },
                        $connected_rows
                    );

                    $additional_row_ids = $data_table->getRowsByAssignedConntectedDataTableRows($connection_id,
                        $connected_row_ids);

                    $additional_row_ids = array_filter(
                        $additional_row_ids,
                        function ($row_id) use ($already_found_row_ids) {
                            return (!in_array(intval($row_id), $already_found_row_ids));
                        }
                    );

                    $additional_rows = $data_table->getSomeRowsForFrontend($additional_row_ids, $page_id, $language_id);
                    if (is_array($additional_rows)) {
                        $data = array_merge($data, $additional_rows);
                    }

                }

            }
        }
    }

    $template->assign($params['var'], $data);
}
