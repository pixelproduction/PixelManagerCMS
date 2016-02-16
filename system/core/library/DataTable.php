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

class DataTable implements DataTableInterface
{
    const CRUD_MESSAGE_TYPE_INFO = 0;
    const CRUD_MESSAGE_TYPE_SUCCESS = 1;
    const CRUD_MESSAGE_TYPE_WARNING = 2;
    const CRUD_MESSAGE_TYPE_ERROR = 3;

    protected $db_table = '';
    protected $languages_config = null;
    protected $fields = array();
    protected $columns = array();
    protected $editor_tabs = null;
    protected $query_before_create = false;
    protected $query_before_update = false;
    protected $query_before_delete = false;
    protected $last_query_error_message = '';
    protected $crud_message = '';
    protected $crud_message_type = self::CRUD_MESSAGE_TYPE_INFO;
    protected $image_root_dir = null;
    protected $image_root_url = null;
    protected $image_files_to_delete = array();
    protected $image_files_to_keep = array();
    protected $assignment_list_column_id = null;
    protected $connected_data_tables = array();

    public function tableUpdated()
    {
        // Zum Überschreiben in abgeleiteten Klassen
    }

    public function prepareFieldValueForOutput($row_id, &$value, $field, $language_id)
    {
        // Zum Überschreiben in abgeleiteten Klassen
    }

    public function editRowArrayBeforeOutput(&$row, $all_columns = true, $reduced_to_language = null)
    {
        // Zum Überschreiben in abgeleiteten Klassen
    }

    public function prepareFieldValueForDb($row_id, &$value, $field, $language_id)
    {
        // Zum Überschreiben in abgeleiteten Klassen
    }

    public function getCustomDataSource($row_id, $language_id, $field)
    {
        // Zum Überschreiben in abgeleiteten Klassen
        return (null);
    }

    public function saveCustomDataSource($row_id, $language_id, $data, $field)
    {
        // Zum Überschreiben in abgeleiteten Klassen
        return (true);
    }

    public function prepareWhereClauseForGetAllRows(&$where)
    {
        // Zum Überschreiben in abgeleiteten Klassen
    }

    public function queryCreateRow($data)
    {
        // Zum Überschreiben in abgeleiteten Klassen
        return (true);
    }

    public function queryUpdateRow($id, $new_data)
    {
        // Zum Überschreiben in abgeleiteten Klassen
        return (true);
    }

    public function queryDeleteRow($id)
    {
        // Zum Überschreiben in abgeleiteten Klassen
        return (true);
    }

    public function __construct($db_table = '', $fields = array(), $columns = array(), $editor_tabs = null)
    {
        $this->setDbTable($db_table);
        $this->setFields($fields);
        $this->setColumns($columns);
        $this->loadStandardLanguagesConfig();
        $this->setEditorTabs($editor_tabs);
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return ($this->fields);
    }

    public function setQueryBeforeCreate($value)
    {
        $this->query_before_create = $value;
    }

    public function getQueryBeforeCreate()
    {
        return ($this->query_before_create);
    }

    public function setQueryBeforeUpdate($value)
    {
        $this->query_before_update = $value;
    }

    public function getQueryBeforeUpdate()
    {
        return ($this->query_before_update);
    }

    public function setQueryBeforeDelete($value)
    {
        $this->query_before_delete = $value;
    }

    public function getQueryBeforeDelete()
    {
        return ($this->query_before_delete);
    }

    public function getFieldById($id)
    {
        foreach ($this->fields as $field) {
            if ($field['id'] == $id) {
                return ($field);
            }
        }
        return (false);
    }

    public function setDbTable($db_table)
    {
        $this->db_table = $db_table;
    }

    public function getDbTable()
    {
        return ($this->db_table);
    }

    public function getDbTranslationsTable()
    {
        return ($this->db_table . '_translations');
    }

    public function setLanguagesConfig($languages_config)
    {
        $this->languages_config = $languages_config;
    }

    public function getLanguagesConfig()
    {
        return ($this->languages_config);
    }

    public function loadStandardLanguagesConfig()
    {
        $config = Config::getArray();
        $this->setLanguagesConfig($config['languages']);
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setEditorTabs($editor_tabs)
    {
        $this->editor_tabs = $editor_tabs;
    }

    public function getEditorTabs()
    {
        return ($this->editor_tabs);
    }

    public function setImageRootDir($image_root_dir)
    {
        $this->image_root_dir = rtrim($image_root_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function getImageRootDir()
    {
        return ($this->image_root_dir);
    }

    public function setAssignmentListColumnId($column_id)
    {
        $this->assignment_list_column_id = $column_id;
    }

    public function getAssignmentListColumnId()
    {
        return ($this->assignment_list_column_id);
    }

    public function setConnectedDataTables($connected_data_tables)
    {
        $this->connected_data_tables = $connected_data_tables;
    }

    public function getConnectedDataTables()
    {
        return ($this->connected_data_tables);
    }

    public function getConnectedDataTableById($connection_id)
    {
        if (isset($this->connected_data_tables[$connection_id])) {
            return ($this->connected_data_tables[$connection_id]);
        } else {
            return (false);
        }
    }

    public function getImageDir($row_id)
    {
        $row_id = (string)$row_id;
        if ($row_id == '') {
            return (false);
        }
        if ($this->getImageRootDir() == '') {
            return (false);
        }
        return ($this->getImageRootDir() . $row_id . '/');
    }

    public function setImageRootUrl($image_root_url)
    {
        $this->image_root_url = rtrim($image_root_url, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function getImageRootUrl()
    {
        return ($this->image_root_url);
    }

    public function getImageUrl($row_id)
    {
        $row_id = (string)$row_id;
        if ($row_id == '') {
            return (false);
        }
        if ($this->getImageRootUrl() == '') {
            return (false);
        }
        return ($this->getImageRootUrl() . $row_id . DIRECTORY_SEPARATOR);
    }

    public function ensureImageDirIsAvailable($row_id)
    {
        $image_dir = $this->getImageDir($row_id);
        if ($image_dir !== false) {
            if (!file_exists($image_dir)) {
                FileUtils::createFolderRecursive($image_dir);
            }
        }
    }

    public function deleteImageDir($row_id)
    {
        $image_dir = $this->getImageDir($row_id);
        if (($image_dir !== false) && ($image_dir != '')) {
            $image_dir = trim($image_dir, DIRECTORY_SEPARATOR);
            if (is_dir($image_dir)) {
                FileUtils::deleteFolder($image_dir);
            }
        }
    }

    public function setImageFolder($dir)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->setImageRootDir(APPLICATION_ROOT . 'user-data/' . $dir);
        $this->setImageRootUrl(Config::get()->baseUrl . 'user-data/' . $dir);
    }

    public function getDbFieldsForColumns($backticks = false)
    {
        $db_fields = array('id');
        foreach ($this->columns as $column) {
            foreach ($this->fields as $field) {
                if (!$this->hasFieldCustomDataSource($field)) {
                    if (isset($field['dbFieldType'])) {
                        if (!$this->isFieldTranslatable($field)) {
                            if ($column['id'] == $field['id']) {
                                $db_fields[] = $field['id'];
                            }
                        }
                    }
                }
            }
        }
        if ($backticks) {
            for ($i = 0; $i < count($db_fields); $i++) {
                $db_fields[$i] = "`" . $db_fields[$i] . "`";
            }
        }
        return ($db_fields);
    }

    public function getDbTranslationsFieldsForColumns($backticks = false)
    {
        $db_fields = array('row-id', 'language-id');
        foreach ($this->columns as $column) {
            foreach ($this->fields as $field) {
                if (!$this->hasFieldCustomDataSource($field)) {
                    if (isset($field['dbFieldType'])) {
                        if ($this->isFieldTranslatable($field)) {
                            if ($column['id'] == $field['id']) {
                                $db_fields[] = $field['id'];
                            }
                        }
                    }
                }
            }
        }
        if ($backticks) {
            for ($i = 0; $i < count($db_fields); $i++) {
                $db_fields[$i] = "`" . $db_fields[$i] . "`";
            }
        }
        return ($db_fields);
    }

    public function getAllRowsRaw($select_string = "WHERE 1", $select_parameters = array())
    {
        $this->prepareWhereClauseForGetAllRows($select_string);
        return (Db::get("SELECT * FROM [prefix]" . $this->getDbTable() . " " . $select_string, $select_parameters));
    }

    public function getRowRaw($row_id)
    {
        return (Db::getFirst("SELECT * FROM [prefix]" . $this->getDbTable() . " WHERE `id`=:rowId",
            array(':rowId' => $row_id)));
    }

    protected function getRowTranslationsValue(&$array, $index, $std_value)
    {
        if (isset($array[$index])) {
            return ($array[$index]);
        } else {
            return ($std_value);
        }
    }

    public function getRowTranslations($row_id, $all_columns, $reduced_to_language = null)
    {
        if ($all_columns) {
            $db_fields_string = '*';
        } else {
            $db_fields = $this->getDbTranslationsFieldsForColumns(true);
            $db_fields_string = implode(",", $db_fields);
        }
        $translation_rows = Db::get(
            "SELECT " . $db_fields_string . " FROM [prefix]" . $this->getDbTranslationsTable() . " WHERE `row-id` = :rowId;",
            array(
                ':rowId' => $row_id,
            )
        );
        $values = array();
        if ($translation_rows !== false) {
            foreach ($translation_rows as $row) {
                foreach ($row as $key => $value) {
                    if (($key != 'id') && ($key != 'row-id') && ($key != 'language-id') && ($value !== null)) {
                        $values[$key][$row['language-id']] = $value;
                    }
                }
            }
            if ($reduced_to_language !== null) {
                foreach ($values as $key => &$value) {
                    $merged = null;
                    $preferred_language_id = $reduced_to_language;
                    $single_value = $this->getRowTranslationsValue($value, $preferred_language_id, null);
                    if ($single_value !== null) {
                        $merged = $single_value;
                    } else {
                        if (isset($this->languages_config['list'][$preferred_language_id])) {
                            $substitutes = $this->languages_config['list'][$preferred_language_id]['preferredSubstitutes'];
                            foreach ($substitutes as $substitute) {
                                $single_value = $this->getRowTranslationsValue($value, $substitute, null);
                                if ($single_value !== null) {
                                    $merged = $single_value;
                                    break;
                                }
                            }
                        } else {
                            $merged = null;
                        }
                    }
                    $value = $merged;
                }
            }
        }
        return ($values);
    }

    public function mergeTranslationsIntoArray(&$row, $all_columns, $reduced_to_language = null)
    {
        if ($this->languages_config !== null) {
            $row_translations = $this->getRowTranslations($row['id'], $all_columns, $reduced_to_language);
            $row = array_merge($row, $row_translations);
        }
    }

    public function typeCastFieldValue(&$value, &$field)
    {
        if (!isset($value)) {
            return;
        }
        if (isset($field['dbFieldType'])) {
            switch ($field['dbFieldType']) {
                case 'int':
                    $value = (int)$value;
                    break;
                case 'float':
                    $value = (float)$value;
                    break;
                case 'boolean':
                    $value = (boolean)$value;
                    break;
                default:
                    // bei allem anderen als String belassen
                    break;
            }
        }
    }

    public function typeCastFieldValuesInArray(&$row, $reduced_to_language = null)
    {
        foreach ($this->fields as $field) {
            if ($this->isFieldTranslatable($field) && ($reduced_to_language === null)) {
                if (isset($row[$field['id']])) {
                    $translation_id = $row[$field['id']];
                    if (is_numeric($translation_id)) {
                        foreach ($this->languages_config['list'] as $language_id => $language) {
                            if (isset($row[$field['id']][$language_id])) {
                                $this->typeCastFieldValue($row[$field['id']][$language_id], $field);
                            }
                        }
                    }
                }
            } else {
                if (isset($row[$field['id']])) {
                    $this->typeCastFieldValue($row[$field['id']], $field);
                }
            }
        }
    }

    protected function prepareImagesAutomagically($row_id, &$value, $field, $language_id)
    {
        if ($this->isFieldAutoMagic($field)) {
            if ($field['type'] == 'array') {
                if (is_array($value)) {
                    if (count($value) > 0) {
                        foreach ($value as &$row) {
                            foreach ($field['parameters']['fields'] as $array_field) {
                                if ($array_field['type'] == 'image') {
                                    if (isset($row[$array_field['id']])) {
                                        if (is_array($row[$array_field['id']])) {
                                            if ($this->isFieldTranslatable($array_field)) {
                                                foreach (Config::get()->languages->list as $array_language_id => $language) {
                                                    $row[$array_field['id']][$array_language_id]['pageFilesUrl'] = $this->getImageUrl($row_id);
                                                }
                                            } else {
                                                $row[$array_field['id']]['pageFilesUrl'] = $this->getImageUrl($row_id);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($field['type'] == 'image') {
                    if (is_array($value)) {
                        $value['pageFilesUrl'] = $this->getImageUrl($row_id);
                    }
                }
            }
        }
    }

    public function prepareFieldValuesInArrayForOutput(&$row, $reduced_to_language = null)
    {
        foreach ($this->fields as $field) {
            if ($this->isFieldTranslatable($field) && ($reduced_to_language === null)) {
                if (isset($row[$field['id']])) {
                    foreach ($this->languages_config['list'] as $language_id => $language) {
                        if (isset($row[$field['id']][$language_id])) {
                            $this->decodeFieldValueFromJsonIfNecessary($row['id'], $row[$field['id']][$language_id],
                                $field, $language_id);
                            $this->prepareImagesAutomagically($row['id'], $row[$field['id']][$language_id], $field,
                                $language_id);
                            $this->prepareFieldValueForOutput($row['id'], $row[$field['id']][$language_id], $field,
                                $language_id);
                        }
                    }
                }
            } else {
                if (isset($row[$field['id']])) {
                    $this->decodeFieldValueFromJsonIfNecessary($row['id'], $row[$field['id']], $field, null);
                    $this->prepareImagesAutomagically($row['id'], $row[$field['id']], $field, null);
                    $this->prepareFieldValueForOutput($row['id'], $row[$field['id']], $field, null);
                }
            }
        }
    }

    protected function addCustomDataToRow(&$row, $all_columns)
    {
        if ($all_columns) {
            $column_filter = false;
        } else {
            $column_filter = array();
            foreach ($this->columns as $column) {
                $column_filter[] = $column['id'];
            }
        }
        $row_id = $row['id'];
        foreach ($this->fields as $field) {
            $add_this_field = true;
            if ($column_filter !== false) {
                if (!in_array($field['id'], $column_filter)) {
                    $add_this_field = false;
                }
            }
            if ($add_this_field) {
                if ($this->hasFieldCustomDataSource($field)) {
                    if ($this->isFieldTranslatable($field)) {
                        $value = array();
                        foreach ($this->languages_config['list'] as $language_id => $language) {
                            $value[$language_id] = $this->getCustomDataSource($row_id, $language_id, $field);
                        }
                        $row[$field['id']] = $value;
                    } else {
                        $row[$field['id']] = $this->getCustomDataSource($row_id, null, $field);
                    }
                }
            }
        }
    }

    protected function addAutoMagicConntectedDataTableRows(&$row, $all_columns)
    {
        if ($all_columns) {
            $column_filter = false;
        } else {
            $column_filter = array();
            foreach ($this->columns as $column) {
                $column_filter[] = $column['id'];
            }
        }
        $row_id = $row['id'];
        foreach ($this->fields as $field) {
            $add_this_field = true;
            if ($column_filter !== false) {
                if (!in_array($field['id'], $column_filter)) {
                    $add_this_field = false;
                }
            }
            if ($add_this_field) {
                if ($this->isFieldAutoMagicConnectedDataTable($field)) {
                    $row[$field['id']] = $this->getAssignedConntectedDataTableRows($row_id, $field['connectionId']);
                }
            }
        }
    }

    public function prepareRowArrayForOutput(&$row, $all_columns = true, $reduced_to_language = null)
    {
        $this->addAutoMagicConntectedDataTableRows($row, $all_columns);
        $this->addCustomDataToRow($row, $all_columns);
        $this->mergeTranslationsIntoArray($row, $all_columns, $reduced_to_language);
        $this->typeCastFieldValuesInArray($row, $reduced_to_language);
        $this->prepareFieldValuesInArrayForOutput($row, $reduced_to_language);
        $this->editRowArrayBeforeOutput($row, $all_columns, $reduced_to_language);
    }

    public function getAllRows(
        $all_columns = true,
        $reduced_to_language = null,
        $select_string = "WHERE 1",
        $select_parameters = array()
    ) {
        if ($all_columns) {
            $db_fields_string = '*';
        } else {
            $db_fields = $this->getDbFieldsForColumns(true);
            $db_fields_string = implode(",", $db_fields);
        }
        $this->prepareWhereClauseForGetAllRows($select_string);
        $rows = Db::get("SELECT " . $db_fields_string . " FROM [prefix]" . $this->getDbTable() . " " . $select_string,
            $select_parameters);
        if ($rows !== false) {
            if (count($rows) > 0) {
                for ($i = 0; $i < count($rows); $i++) {
                    $this->prepareRowArrayForOutput($rows[$i], $all_columns, $reduced_to_language);
                }
            }
        }
        return $rows;
    }

    public function getRow($row_id, $all_columns = true, $reduced_to_language = null)
    {
        if ($all_columns) {
            $db_fields_string = '*';
        } else {
            $db_fields = $this->getDbFieldsForColumns(true);
            $db_fields_string = implode(",", $db_fields);
        }
        $row = Db::getFirst("SELECT " . $db_fields_string . " FROM [prefix]" . $this->getDbTable() . " WHERE `id`=:rowId",
            array(':rowId' => $row_id));
        if ($row !== false) {
            $this->prepareRowArrayForOutput($row, $all_columns, $reduced_to_language);
            return ($row);
        }
        return (false);
    }

    public function isFieldTranslatable(&$field)
    {
        $translatable = true;
        if ($this->languages_config === null) {
            $translatable = false;
        } else {
            if (isset($field['untranslatable'])) {
                if ($field['untranslatable'] === true) {
                    $translatable = false;
                }
            }
        }
        return ($translatable);
    }

    public function hasFieldCustomDataSource($field)
    {
        if (!is_array($field)) {
            $field = $this->getFieldById($field);
        }
        if (isset($field['customDataSource'])) {
            if ($field['customDataSource'] === true) {
                return (true);
            }
        }
        return (false);
    }

    public function isFieldAutoMagic($field)
    {
        if (!is_array($field)) {
            $field = $this->getFieldById($field);
        }
        if (isset($field['autoMagic'])) {
            if ($field['autoMagic'] === true) {
                return (true);
            }
        }
        return (false);
    }

    public function isFieldAutoMagicConnectedDataTable($field)
    {
        if (!is_array($field)) {
            $field = $this->getFieldById($field);
        }
        if ($this->isFieldAutoMagic($field) && (!$this->isFieldTranslatable($field))) {
            if (isset($field['connectionId'])) {
                if ($field['connectionId'] != '') {
                    return (true);
                }
            }
        }
        return (false);
    }

    protected function isJsonEncodingNecessaryForField($row_id, &$value, $field, $language_id)
    {
        $use_frontend_modifiers = $this->isFieldAutoMagic($field);
        if ($use_frontend_modifiers) {
            $string_only_field_types = array(
                'singleLineText',
                'tinyMCE',
                'multiLineText',
                'redactor',
                'assignDataTableRows',
            );
            if (in_array($field['type'], $string_only_field_types)) {
                return (false);
            } else {
                return (true);
            }
        } else {
            return (false);
        }
    }

    protected function encodeFieldValueAsJsonIfNecessary($row_id, &$value, $field, $language_id)
    {
        if ($this->isJsonEncodingNecessaryForField($row_id, $value, $field, $language_id)) {
            $value = json_encode($value);
        }
    }

    protected function decodeFieldValueFromJsonIfNecessary($row_id, &$value, $field, $language_id)
    {
        if ($this->isJsonEncodingNecessaryForField($row_id, $value, $field, $language_id)) {
            if ($value !== null) {
                $value = json_decode($value, true);
            } else {
                $value = array();
            }
        }
    }

    protected function saveAndRemoveCustomRowData($row_id, $language_id, &$data)
    {
        $error = false;
        if (is_array($data)) {
            if (count($data) > 0) {
                $new_data = array();
                foreach ($data as $field_id => $field_value) {
                    if ($this->hasFieldCustomDataSource($field_id)) {
                        $field = $this->getFieldById($field_id);
                        $result = $this->saveCustomDataSource($row_id, $language_id, $field_value, $field);
                        if ($result !== false) {
                            $error = true;
                        }
                    } else {
                        $new_data[$field_id] = $field_value;
                    }
                }
                $data = $new_data;
            }
        }
        return (!$error);
    }

    protected function saveAndRemoveAutoMagicConntectedDataTableRows($row_id, &$data)
    {
        $error = false;
        if (is_array($data)) {
            if (count($data) > 0) {
                $new_data = array();
                foreach ($data as $field_id => $field_value) {
                    if ($this->isFieldAutoMagicConnectedDataTable($field_id)) {
                        $field = $this->getFieldById($field_id);
                        $result = $this->updateConnectedDataTableRows($row_id, $field['connectionId'], $field_value);
                        if ($result !== false) {
                            $error = true;
                        }
                    } else {
                        $new_data[$field_id] = $field_value;
                    }
                }
                $data = $new_data;
            }
        }
        return (!$error);
    }

    public function saveUntranslatableRowData($row_id, $data)
    {
        $this->saveAndRemoveAutoMagicConntectedDataTableRows($row_id, $data);
        $this->saveAndRemoveCustomRowData($row_id, null, $data);
        if (count($data) > 0) {
            $res = Db::update(
                $this->getDbTable(),
                $data,
                "`id` = :rowId",
                array(':rowId' => $row_id)
            );
            $this->tableUpdated();
            return ($res);
        } else {
            return (null);
        }
    }

    public function doesTranslationRowExist($row_id, $language_id)
    {
        $res = Db::getFirst(
            "SELECT id FROM [prefix]" . $this->getDbTranslationsTable() . " WHERE `row-id` = :rowId AND `language-id` = :languageId;",
            array(
                ':rowId' => $row_id,
                ':languageId' => $language_id,
            )
        );
        return ($res !== false);
    }

    public function saveTranslatedRowData($row_id, $language_id, $data)
    {
        $this->saveAndRemoveCustomRowData($row_id, $language_id, $data);
        if (count($data) > 0) {
            if ($this->doesTranslationRowExist($row_id, $language_id)) {
                $res = Db::update(
                    $this->getDbTranslationsTable(),
                    $data,
                    "(`row-id` = :rowId) AND (`language-id` = :languageId)",
                    array(
                        ':rowId' => $row_id,
                        ':languageId' => $language_id,
                    )
                );
            } else {
                $data['row-id'] = $row_id;
                $data['language-id'] = $language_id;
                $res = Db::insert(
                    $this->getDbTranslationsTable(),
                    $data
                );
            }
            $this->tableUpdated();
            return ($res !== false);
        } else {
            return (false);
        }
    }

    protected function beginSavingImages($row_id)
    {
        $this->image_files_to_delete = array();
        $this->image_files_to_keep = array();
    }

    protected function getRandomFilename($orig_filename)
    {
        $info = pathinfo($orig_filename);
        $ext = UTF8String::strtolower($info['extension']);
        return (md5(uniqid() . time() . rand(1, 99999)) . '.' . $ext);
    }

    protected function endSavingImages($row_id, $error = false)
    {
        // Zur Sicherheit nur irgendwelche Bilder löschen, wenn das Speichern ohne Fehler verlaufen ist
        if (!$error) {
            $image_dir = $this->getImageDir($row_id);

            // Erstmal alle Dateien löschen, die auf der Liste der zu löschenden Dateien stehen
            if (count($this->image_files_to_delete) > 0) {
                foreach ($this->image_files_to_delete as $filename) {
                    if (is_file($image_dir . $filename)) {
                        FileUtils::deleteFile($image_dir . $filename);
                    }
                }
            }

            // Dann alle Dateien durchgehen, die noch zu finden sind
            // Wenn eine Datei nicht auf der Liste der zu erhaltenden Dateien steht,
            // ist anzunehmen, dass sie verwaist ist und gelöscht werden soll.
            $handle = @opendir($image_dir);
            if ($handle !== false) {
                while (false !== ($item = @readdir($handle))) {
                    if ($item != "." && $item != "..") {
                        if (@is_file($image_dir . $item)) {
                            if (!in_array($item, $this->image_files_to_keep)) {
                                if (is_file($image_dir . $item)) {
                                    FileUtils::deleteFile($image_dir . $item);
                                }
                            }
                        }
                    }
                }
                @closedir($handle);
            }

        }
    }

    protected function saveImage($row_id, &$data, $field, $language_id)
    {
        $orig_data = $data;
        $edit_data = null;
        $image_dir = $this->getImageDir($row_id);
        $this->ensureImageDirIsAvailable($row_id);

        if (isset($data['action'])) {

            // ********************************************************************
            // Daten wurden verändert / durch "getData" des Plugins bearbeitet
            // ********************************************************************

            if ($data['action'] == 'none') {
                if (isset($data['overwriteOccured'])) {
                    if ($data['overwriteOccured'] == true) {
                        $data['action'] = 'overwrite';
                    }
                }
            }

            // Wenn Bild gelöscht oder überschrieben werden soll,
            // die alte(n) Datei(en) in die Liste der zu löschenden Dateien aufnehmen
            if (($data['action'] == 'remove') || ($data['action'] == 'overwrite')) {
                if (isset($data['existingImage'])) {
                    if (trim($data['existingImage']) != '') {
                        if (file_exists($image_dir . $data['existingImage'])) {
                            $this->image_files_to_delete[] = basename($data['existingImage']);
                        }
                    }
                }
                if (isset($data['existingAdditionalSizes'])) {
                    if (is_array($data['existingAdditionalSizes'])) {
                        foreach ($data['existingAdditionalSizes'] as $additional) {
                            $this->image_files_to_delete[] = basename($additional);
                        }
                    }
                }
            }

            // Wenn ein neues Bild eingesetzt werden soll,
            // die neue(n) Datei(en) in den Bilder-Ordner dieses Elements verschieben
            // und in die Liste der zu erhaltenden Dateien aufnehmen
            if ($data['action'] == 'overwrite') {

                if (isset($data['newImage'])) {
                    if (trim($data['newImage']) != '') {
                        $orig_file = APPLICATION_ROOT . $data['newImage'];
                        if (file_exists($orig_file)) {
                            $new_image_name = $this->getRandomFilename($orig_file);
                            FileUtils::rename($orig_file, $image_dir . $new_image_name);
                            $this->image_files_to_keep[] = basename($new_image_name);
                            $edit_data = array(
                                'imageRelativePath' => $new_image_name,
                                'additionalSizes' => null
                            );
                            if (isset($data['originalImage'])) {
                                $edit_data['originalImage'] = $data['originalImage'];
                            }
                            if (isset($data['customSettings'])) {
                                $edit_data['customSettings'] = $data['customSettings'];
                            }
                        }
                        if (isset($data['newAdditionalSizes'])) {
                            if (is_array($data['newAdditionalSizes'])) {
                                $edit_data['additionalSizes'] = array();
                                foreach ($data['newAdditionalSizes'] as $additional_id => $additional) {
                                    $orig_file = APPLICATION_ROOT . $additional;
                                    if (file_exists($orig_file)) {
                                        $new_image_name = $this->getRandomFilename($orig_file);
                                        FileUtils::rename($orig_file, $image_dir . $new_image_name);
                                        $this->image_files_to_keep[] = basename($new_image_name);
                                        $edit_data['additionalSizes'][$additional_id] = $new_image_name;
                                    }
                                }
                            }
                        }
                    }
                }

                // Wenn keine Änderung stattfinden soll,
                // die Datei(en) in die Liste der zu erhaltenden Dateien aufnehmen
            } elseif ($data['action'] == 'none') {

                $this->image_files_to_keep[] = basename($data['existingImage']);
                if (isset($data['existingAdditionalSizes'])) {
                    if (is_array($data['existingAdditionalSizes'])) {
                        foreach ($data['existingAdditionalSizes'] as $additional_id => $additional) {
                            $this->image_files_to_keep[] = basename($additional);
                        }
                    }
                }
                $edit_data = array(
                    'imageRelativePath' => $data['existingImage'],
                    'additionalSizes' => $data['existingAdditionalSizes']
                );
                if (isset($data['originalImage'])) {
                    $edit_data['originalImage'] = $data['originalImage'];
                }
                if (isset($data['customSettings'])) {
                    $edit_data['customSettings'] = $data['customSettings'];
                }

            }

        } else {

            // ********************************************************************
            // Daten wurden unverändert durchgeschleust
            // ********************************************************************

            if (isset($data['imageRelativePath'])) {
                if (trim($data['imageRelativePath']) != '') {
                    $this->image_files_to_keep[] = basename($data['imageRelativePath']);
                    $edit_data = array(
                        'imageRelativePath' => $data['imageRelativePath'],
                        'additionalSizes' => null
                    );
                    if (isset($data['additionalSizes'])) {
                        if (is_array($data['additionalSizes'])) {
                            foreach ($data['additionalSizes'] as $additional_id => $additional) {
                                $this->image_files_to_keep[] = basename($additional);
                            }
                        }
                        $edit_data['additionalSizes'] = $data['additionalSizes'];
                    }
                    if (isset($data['originalImage'])) {
                        $edit_data['originalImage'] = $data['originalImage'];
                    }
                    if (isset($data['customSettings'])) {
                        $edit_data['customSettings'] = $data['customSettings'];
                    }
                }
            }

        }

        $data = $edit_data;
    }

    protected function automagicallySaveImages($row_id, &$value, $field, $language_id)
    {
        if ($this->isFieldAutoMagic($field)) {
            if ($field['type'] == 'array') {
                if (is_array($value)) {
                    if (count($value) > 0) {
                        foreach ($value as &$row) {
                            foreach ($field['parameters']['fields'] as $array_field) {
                                if ($array_field['type'] == 'image') {
                                    if (isset($row[$array_field['id']])) {
                                        if ($this->isFieldTranslatable($array_field)) {
                                            foreach (Config::get()->languages->list as $array_language_id => $language) {
                                                if (isset($row[$array_field['id']][$array_language_id])) {
                                                    $this->saveImage($row_id,
                                                        $row[$array_field['id']][$array_language_id], $array_field,
                                                        $array_language_id);
                                                }
                                            }
                                        } else {
                                            $this->saveImage($row_id, $row[$array_field['id']], $array_field,
                                                $language_id);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($field['type'] == 'image') {
                    $this->saveImage($row_id, $value, $field, $language_id);
                }
            }
        }
    }

    public function saveRowData($row_id, $new_data)
    {
        $this->beginSavingImages($row_id);
        $values = array();
        $translated_values = array();
        if ($this->languages_config !== null) {
            foreach ($this->languages_config['list'] as $language_id => $language) {
                $translated_values[$language_id] = array();
            }
        }
        if (count($this->fields) > 0) {
            foreach ($this->fields as $field) {
                if (isset($new_data[$field['id']])) {
                    if ($this->isFieldTranslatable($field)) {
                        foreach ($this->languages_config['list'] as $language_id => $language) {
                            if (isset($new_data[$field['id']][$language_id])) {
                                $value = $new_data[$field['id']][$language_id];
                                $this->prepareFieldValueForDb($row_id, $value, $field, $language_id);
                                $this->automagicallySaveImages($row_id, $value, $field, $language_id);
                                $this->encodeFieldValueAsJsonIfNecessary($row_id, $value, $field, $language_id);
                                $translated_values[$language_id][$field['id']] = $value;
                            } else {
                                $translated_values[$language_id][$field['id']] = null;
                            }
                        }
                    } else {
                        $value = $new_data[$field['id']];
                        $this->prepareFieldValueForDb($row_id, $value, $field, null);
                        $this->automagicallySaveImages($row_id, $value, $field, null);
                        $this->encodeFieldValueAsJsonIfNecessary($row_id, $value, $field, null);
                        $values[$field['id']] = $value;
                    }
                }
            }
        }
        $error = false;
        if (count($values) > 0) {
            $res = $this->saveUntranslatableRowData($row_id, $values);
            if ($res === false) {
                $error = true;
            }
        }
        if (count($translated_values) > 0) {
            foreach ($translated_values as $language_id => $values) {
                if (count($values) > 0) {
                    $res = $this->saveTranslatedRowData($row_id, $language_id, $values);
                    if ($res === false) {
                        $error = true;
                    }
                }
            }
        }
        $this->endSavingImages($row_id, $error);
        return (!$error);
    }

    public function createRow($data)
    {
        $new_id = Db::insert($this->getDbTable(), array('id' => null));
        $this->tableUpdated();
        if ($new_id !== false) {
            $res = $this->updateRow($new_id, $data);
            return (($res !== false) ? $new_id : false);
        }
        return (false);
    }

    public function updateRow($id, $new_data)
    {
        return ($this->saveRowData($id, $new_data));
    }

    public function deleteRow($id)
    {
        $row = $this->getRowRaw($id);
        if ($row !== false) {
            $this->unassignAllConnectedDataTables($id);
            $this->deleteImageDir($id);
            if ($this->languages_config !== null) {
                Db::delete(
                    $this->getDbTranslationsTable(),
                    "`row-id`=:rowId",
                    array(
                        ':rowId' => $id,
                    )
                );
            }
            $res = Db::delete(
                $this->getDbTable(),
                "`id`=:id",
                array(
                    ':id' => $id,
                )
            );
            $this->tableUpdated();
            return ($res);
        }
        return (false);
    }

    protected function setLastQueryErrorMessage($message)
    {
        $this->last_query_error_message = $message;
    }

    public function getLastQueryErrorMessage()
    {
        if (trim($this->last_query_error_message) != '') {
            return ($this->last_query_error_message);
        } else {
            return (Translate::get('Sorry, the data can not be saved. Please fill out all fields correctly and try again.'));
        }
    }

    public function setCrudMessage($message, $message_type = self::CRUD_MESSAGE_TYPE_INFO)
    {
        $this->crud_message = $message;
        $this->crud_message_type = $message_type;
    }

    public function getCrudMessageArray()
    {
        $message_array = array(
            'message' => $this->crud_message,
            'type' => $this->crud_message_type,
        );
        $this->crud_message = '';
        $this->crud_message_type = self::CRUD_MESSAGE_TYPE_INFO;
        return ($message_array);
    }

    protected function prepareRowArrayAutoMagicFields(&$row, $page_id, $language_id)
    {
        $auto_magic_fields = array();
        $auto_magic_data = array();
        foreach ($this->fields as $field) {
            if ($this->isFieldAutoMagic($field)) {
                $auto_magic_fields[] = $field;
                if (isset($row[$field['id']])) {
                    $auto_magic_data[$field['id']] = $row[$field['id']];
                }
            }
        }
        if (count($auto_magic_fields) > 0) {
            $fake_page_structure = array(
                'autoMagic' => array(
                    'type' => 'datablock',
                    'caption' => 'autoMagic',
                    'fields' => $auto_magic_fields,
                )
            );
            $fake_parameters = array(
                'pageId' => $page_id,
                'languageId' => $language_id,
                'pageFiles' => $this->getImageDir($row['id']),
                'pageFilesUrl' => $this->getImageUrl($row['id']),
            );
            $auto_magic_data = array('autoMagic' => $auto_magic_data);
            DataModifiers::applyAll($auto_magic_data, $fake_page_structure, $fake_parameters);
            foreach ($auto_magic_data['autoMagic'] as $field_id => &$field_value) {
                $row[$field_id] = $field_value;
            }
        }
    }

    protected function getMergedRowForFrontend(&$row_auto_magic, &$row_standard)
    {
        $frontend_row = $row_standard;
        foreach ($this->fields as $field) {
            if ($this->isFieldAutoMagic($field)) {
                if (isset($row_auto_magic[$field['id']])) {
                    $frontend_row[$field['id']] = $row_auto_magic[$field['id']];
                }
            }
        }
        return ($frontend_row);
    }

    protected function getRowArrayForFrontend($row, $page_id, $language_id)
    {
        $row_standard = $row;
        $row_auto_magic = $row;

        // Alle Felder bei denen NICHT autoMagic => true gesetzt ist, müssen ganz normal auf eine Sprache reduziert werden
        $this->prepareRowArrayForOutput($row_standard, true, $language_id);

        // Alle Felder bei denen autoMagic => true gesetzt ist, müssen durch den DataModifier-Stack gejagt werden,
        // welcher die Reduzierung auf die Sprache vornimmt (unter anderem)
        $this->prepareRowArrayForOutput($row_auto_magic, true, null);
        $this->prepareRowArrayAutoMagicFields($row_auto_magic, $page_id, $language_id);

        // Danach werden die autoMagic-Felder und nicht-autoMagic-Felder in ein Array überführt
        return ($this->getMergedRowForFrontend($row_auto_magic, $row_standard));
    }

    public function getRowForFrontend($row_id, $page_id, $language_id)
    {
        $row = Db::getFirst("SELECT * FROM [prefix]" . $this->getDbTable() . " WHERE `id`=:rowId",
            array(':rowId' => $row_id));
        if ($row !== false) {
            $row = $this->getRowArrayForFrontend($row, $page_id, $language_id);
            return ($row);
        }
        return (false);
    }

    public function getSomeRowsForFrontend($id_list, $page_id, $language_id)
    {
        if (!is_array($id_list)) {
            $id_list = array($id_list);
        }
        if (count($id_list) > 0) {
            foreach ($id_list as &$row_id) {
                $row_id = intval($row_id); // Weil die Daten in den SQL-Query eingesetzt werden, lieber vorsichtig sein
            }
            $rows = Db::get("SELECT * FROM [prefix]" . $this->getDbTable() . " WHERE `id` IN (" . implode(',',
                    $id_list) . ");");
            if ($rows !== false) {
                if (count($rows) > 0) {
                    for ($i = 0; $i < count($rows); $i++) {
                        $rows[$i] = $this->getRowArrayForFrontend($rows[$i], $page_id, $language_id);
                    }
                }
            }
        } else {
            $rows = false;
        }
        return $rows;
    }

    public function getAllRowsForFrontend(
        $page_id,
        $language_id,
        $select_string = "WHERE 1",
        $select_parameters = array()
    ) {
        $this->prepareWhereClauseForGetAllRows($select_string);
        $rows = Db::get("SELECT * FROM [prefix]" . $this->getDbTable() . " " . $select_string, $select_parameters);
        if ($rows !== false) {
            if (count($rows) > 0) {
                for ($i = 0; $i < count($rows); $i++) {
                    $rows[$i] = $this->getRowArrayForFrontend($rows[$i], $page_id, $language_id);
                }
            }
        }
        return $rows;
    }

    protected function doesFieldValueContainSearchString($field_value, $search_array)
    {
        // $search_array wird als Array aus UTF8 codierten, in Kleinbuchstaben umgewandelten Strings erwartet
        // $field_value dagegen kann ein UTF8 codierter String oder ein beliebig tief verschachteltes Array sein
        if (is_array($field_value)) {
            foreach ($field_value as $field_value_item) {
                if ($this->doesFieldValueContainSearchString($field_value_item, $search_array)) {
                    return (true);
                }
            }
        } else {
            $unencoded_field_value = UTF8String::strtolower(trim($field_value));
            // Möglicherweise sind Teile des Strings als HTML-Entities codiert
            $html_decoded_field_value = html_entity_decode($unencoded_field_value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            foreach ($search_array as $search_item) {
                if (UTF8String::strpos($unencoded_field_value, $search_item) !== false) {
                    return (true);
                }
                if (UTF8String::strpos($html_decoded_field_value, $search_item) !== false) {
                    return (true);
                }
            }
        }
        return (false);
    }

    public function getSearchResultForFrontend($search_string, $columns, $page_id, $language_id)
    {
        if (is_array($columns) && (count($columns) == 0)) {
            $columns = null;
        }
        if ($columns === null) {
            $columns = array();
            foreach ($this->fields as $field) {
                if (isset($field['dbFieldType'])) {
                    if ($field['dbFieldType'] == 'string') {
                        $columns[] = $field['id'];
                    }
                }
            }
        } else {
            $columns = array_map(
                function ($column_id) {
                    return (trim($column_id));
                },
                $columns
            );
            $columns = array_filter(
                $columns,
                function ($string) {
                    return (UTF8String::strlen($string) > 0);
                }
            );
        }
        $sanitized_search_string = UTF8String::strtolower(trim($search_string));
        $search_array = array_filter(
            explode(' ', $sanitized_search_string),
            function ($string) {
                return (UTF8String::strlen($string) > 0);
            }
        );
        $result = array();
        if ((count($columns) > 0) && (count($search_array) > 0)) {
            $rows = $this->getAllRowsForFrontend($page_id, $language_id);
            if ($rows !== false) {
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        $row_contains_search_string = false;
                        foreach ($columns as $column_id) {
                            if (isset($row[$column_id])) {
                                if ($this->doesFieldValueContainSearchString($row[$column_id], $search_array)) {
                                    $row_contains_search_string = true;
                                    break;
                                }
                            }
                        }
                        if ($row_contains_search_string) {
                            $result[] = $row;
                        }
                    }
                }
            }
        }
        return ($result);
    }

    public function getRowsByAssignedConntectedDataTableRows($connection_id, $assigned_id_list)
    {
        $connected_data_table = $this->getConnectedDataTableById($connection_id);
        if ($connected_data_table !== false) {
            if (!is_array($assigned_id_list)) {
                $assigned_id_list = array($assigned_id_list);
            }
            $id_list = array();
            if (count($assigned_id_list) > 0) {
                foreach ($assigned_id_list as &$assigned_id) {
                    $assigned_id = intval($assigned_id);
                }
                $rows = Db::get("SELECT * FROM [prefix]" . $connected_data_table['dbTable'] . " WHERE `" . $connected_data_table['dbKeyForeign'] . "` IN (" . implode(',',
                        $assigned_id_list) . ")");
                if ($rows !== false) {
                    if (count($rows) > 0) {
                        $id_list = array_map(
                            function ($element) use ($connected_data_table) {
                                return ($element[$connected_data_table['dbKeySelf']]);
                            },
                            $rows
                        );
                    }
                }
            }
            return ($id_list);
        }
        return (false);
    }

    protected function getCaptionForAssignmentListRow($row_id, $row, $reduced_to_language = null)
    {
        $column_id = $this->getAssignmentListColumnId();
        if ($column_id !== null) {
            if (isset($row[$column_id])) {
                return ($row[$column_id]);
            }
        }
        return ('');
    }

    public function getAllRowsForAssignmentList(
        $reduced_to_language = null,
        $select_string = "WHERE 1",
        $select_parameters = array()
    ) {
        $this->prepareWhereClauseForGetAllRows($select_string);
        $rows = Db::get("SELECT * FROM [prefix]" . $this->getDbTable() . " " . $select_string, $select_parameters);
        $return = array();
        if ($rows !== false) {
            if (count($rows) > 0) {
                for ($i = 0; $i < count($rows); $i++) {
                    $this->mergeTranslationsIntoArray($rows[$i], true, $reduced_to_language);
                    $return[] = array(
                        'id' => $rows[$i]['id'],
                        'caption' => $this->getCaptionForAssignmentListRow($rows[$i]['id'], $rows[$i],
                            $reduced_to_language),
                    );
                }
            }
        }
        return ($return);
    }

    public function getAssignedConntectedDataTableRows($row_id, $connection_id)
    {
        $connected_data_table = $this->getConnectedDataTableById($connection_id);
        if ($connected_data_table !== false) {
            $row_id = intval($row_id);
            $rows = Db::get("SELECT * FROM [prefix]" . $connected_data_table['dbTable'] . " WHERE `" . $connected_data_table['dbKeySelf'] . "` = :rowId",
                array(':rowId' => $row_id));
            $id_list = array();
            if ($rows !== false) {
                if (count($rows) > 0) {
                    foreach ($rows as $row) {
                        if (isset($row[$connected_data_table['dbKeyForeign']])) {
                            $id_list[] = intval($row[$connected_data_table['dbKeyForeign']]);
                        }
                    }
                }
            }
            return ($id_list);
        }
        return (false);
    }

    public function assignConnectedDataTableRows($row_id, $connection_id, $id_list)
    {
        $connected_data_table = $this->getConnectedDataTableById($connection_id);
        if ($connected_data_table !== false) {
            $row_id = intval($row_id);
            $assigned_id_list = $this->getAssignedConntectedDataTableRows($row_id, $connection_id);
            if ($assigned_id_list !== false) {
                if (!is_array($id_list)) {
                    $id_list = array($id_list);
                }
                if (count($id_list) > 0) {
                    foreach ($id_list as &$id) {
                        $id = intval($id);
                        if (!in_array($id, $assigned_id_list)) {
                            Db::insert(
                                $connected_data_table['dbTable'],
                                array(
                                    $connected_data_table['dbKeySelf'] => $row_id,
                                    $connected_data_table['dbKeyForeign'] => $id,
                                )
                            );
                        }
                    }
                }
                return (true);
            }
        }
        return (false);
    }

    public function unassignConnectedDataTableRows($row_id, $connection_id, $id_list)
    {
        $connected_data_table = $this->getConnectedDataTableById($connection_id);
        if ($connected_data_table !== false) {
            $row_id = intval($row_id);
            if (!is_array($id_list)) {
                $id_list = array($id_list);
            }
            if (count($id_list) > 0) {
                foreach ($id_list as &$id) {
                    $id = intval($id);
                    Db::delete(
                        $connected_data_table['dbTable'],
                        "(`" . $connected_data_table['dbKeySelf'] . "` = :rowId) AND (`" . $connected_data_table['dbKeyForeign'] . "` = :id)",
                        array(
                            ':rowId' => $row_id,
                            ':id' => $id,
                        )
                    );
                }
            }
            return (true);
        }
        return (false);
    }

    public function unassignAllConntectedDataTableRows($row_id, $connection_id)
    {
        $connected_data_table = $this->getConnectedDataTableById($connection_id);
        if ($connected_data_table !== false) {
            $row_id = intval($row_id);
            $res = Db::delete(
                $connected_data_table['dbTable'],
                "`" . $connected_data_table['dbKeySelf'] . "` = :rowId",
                array(
                    ':rowId' => $row_id,
                )
            );
            return ($res !== false);
        }
        return (false);
    }

    public function updateConnectedDataTableRows($row_id, $connection_id, $id_list)
    {
        $this->unassignAllConntectedDataTableRows($row_id, $connection_id);
        return ($this->assignConnectedDataTableRows($row_id, $connection_id, $id_list));
    }

    public function unassignAllConnectedDataTables($row_id)
    {
        $connected_data_tables = $this->getConnectedDataTables();
        if (count($connected_data_tables) > 0) {
            foreach ($connected_data_tables as $connection_id => $connected_data_table) {
                $this->unassignAllConntectedDataTableRows($row_id, $connection_id);
            }
        }
        return (true);
    }

}
