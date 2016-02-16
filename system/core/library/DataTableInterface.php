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

interface DataTableInterface
{
    public function setDbTable($db_table);

    public function getDbTable();

    public function getDbTranslationsTable();

    public function setLanguagesConfig($languages);

    public function getLanguagesConfig();

    public function setFields($fields);

    public function getFields();

    public function getFieldById($id);

    public function setColumns($columns);

    public function getColumns();

    public function setEditorTabs($editor_tabs);

    public function getEditorTabs();

    public function setAssignmentListColumnId($column_id);

    public function getAssignmentListColumnId();

    public function setQueryBeforeCreate($value);

    public function getQueryBeforeCreate();

    public function setQueryBeforeUpdate($value);

    public function getQueryBeforeUpdate();

    public function setQueryBeforeDelete($value);

    public function getQueryBeforeDelete();

    public function setImageRootDir($image_root_dir);

    public function getImageRootDir();

    public function getImageDir($row_id);

    public function setImageRootUrl($image_root_url);

    public function getImageRootUrl();

    public function getImageUrl($row_id);

    public function setImageFolder($dir);

    public function setConnectedDataTables($connected_data_tables);

    public function getConnectedDataTables();

    public function getConnectedDataTableById($connection_id);

    public function getAllRows($all_columns, $reduced_to_language);

    public function getRow($row_id, $all_columns, $reduced_to_language);

    public function createRow($data);

    public function updateRow($id, $new_data);

    public function deleteRow($id);

    public function getLastQueryErrorMessage();

    public function setCrudMessage($message, $message_type);

    public function getCrudMessageArray();

    public function getRowForFrontend($row_id, $page_id, $language_id);

    public function getAllRowsForFrontend($page_id, $language_id, $select_string, $select_parameters);

    public function getSomeRowsForFrontend($id_list, $page_id, $language_id);

    public function getSearchResultForFrontend($search_string, $columns, $page_id, $language_id);

    public function getAllRowsForAssignmentList($reduced_to_language, $select_string, $select_parameters);

    public function getRowsByAssignedConntectedDataTableRows($connection_id, $assigned_id_list);

    public function getAssignedConntectedDataTableRows($row_id, $connection_id);

    public function assignConnectedDataTableRows($row_id, $connection_id, $id_list);

    public function unassignConnectedDataTableRows($row_id, $connection_id, $id_list);

    public function unassignAllConntectedDataTableRows($row_id, $connection_id);

    public function updateConnectedDataTableRows($row_id, $connection_id, $id_list);

    public function unassignAllConnectedDataTables($row_id);
}
