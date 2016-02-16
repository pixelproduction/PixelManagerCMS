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

class InheritDataPlugin implements PluginInterface
{
    private $parameters = array();
    private $page_data = array();
    private $ancestor_pages = null;
    private $ancestor_page_file_url = null;
    private $ancestor_page_data_structure = null;
    private $pages_structure = null;

    public function register()
    {
        return array(
            array(
                'hookId' => Plugins::BEFORE_LOAD_PAGE_DATA_FIELDS,
                'methodName' => 'onBeforeLoadPageDataFields'
            )
        );
    }

    private function getPageStructure($template_id)
    {
        if ($this->pages_structure == null) {
            $this->pages_structure = DataStructure::pagesArray();
        }
        if (isset($this->pages_structure[$template_id]['structure'])) {
            return ($this->pages_structure[$template_id]['structure']);
        }
        return (null);
    }

    private function loadAncestorPages($page_id)
    {
        if ($this->ancestor_pages === null) {
            $pages = new Pages();
            $path = $pages->getPath($page_id);
            for ($i = count($path) - 1; $i >= 0; $i--) {
                $data = $pages->getData($path[$i], true, true);
                $properties = $pages->getProperties($path[$i]);
                $this->ancestor_pages[] = json_decode($data, true);
                $this->ancestor_page_file_url[] = $pages->getPageFilesPublishedUrl($path[$i]);
                $this->ancestor_page_data_structure[] = $this->getPageStructure($properties['template-id']);
            }
        }
    }

    private function isDataFieldTypeOfText($field_data)
    {
        if (is_string($field_data)) {
            return (true);
        }
        return (false);
    }

    private function isDataFieldEmpty($field_data, $field_structure)
    {
        if ($field_data === null) {
            return (true);
        }
        if ($field_structure['type'] == 'image') {
            if ($field_data['imageRelativePath'] == '') {
                return (true);
            }
        }
        if ($this->isDataFieldTypeOfText($field_data)) {
            if ($field_data == '') {
                return (true);
            }
        }
        return (false);
    }

    private function isFieldSetInAnyLanguage($field_data, $field_structure)
    {
        if ($field_structure['type'] == 'array') {
            if (is_array($field_data)) {
                if (count($field_data) > 0) {
                    return (true);
                }
            }
        } else {
            foreach (Config::get()->languages->list as $language_key => $language) {
                if (isset($field_data[$language_key])) {
                    if (!$this->isDataFieldEmpty($field_data[$language_key], $field_structure)) {
                        return (true);
                    }
                }
            }
        }
        return (false);
    }

    private function changeDataFieldImagePath(&$field_data, $field_structure, $ancestor_files_url)
    {
        if ($field_structure['type'] == 'array') {
            if (is_array($field_data)) {
                if (count($field_data) > 0) {
                    foreach ($field_data as $array_data_row_key => $array_data_row) {
                        foreach ($field_structure['parameters']['fields'] as $array_field) {
                            if (isset($array_data_row[$array_field['id']])) {
                                $array_field_structure = $array_field;
                                $array_field_data = $array_data_row[$array_field['id']];
                                $this->changeDataFieldImagePath($array_field_data, $array_field_structure,
                                    $ancestor_files_url);
                                $field_data[$array_data_row_key][$array_field['id']] = $array_field_data;
                            }
                        }
                    }
                }
            }
        } else {
            foreach (Config::get()->languages->list as $language_key => $language) {
                if (isset($field_data[$language_key])) {
                    if (!$this->isDataFieldEmpty($field_data[$language_key], $field_structure)) {
                        if ($field_structure['type'] == 'image') {
                            $field_data[$language_key]['imageAbsoluteUrl'] = $ancestor_files_url . $field_data[$language_key]['imageRelativePath'];
                        }
                    }
                }
            }
        }
        return (false);
    }

    private function isStructureCompatible($a, $b)
    {
        return ($a['type'] == $b['type']);
    }

    private function inherit($template_id, $data_block_id, $field_id)
    {
        $path = $data_block_id . ' > ' . $field_id;
        $field_data = $this->getDataItem($path, $this->page_data);
        $field_structure = $this->getFieldStructure($this->getPageStructure($template_id), $data_block_id, $field_id);
        if (!$this->isFieldSetInAnyLanguage($field_data, $field_structure)) {
            for ($i = 0; $i < count($this->ancestor_pages); $i++) {
                $ancestor_field_structure = $this->getFieldStructure($this->ancestor_page_data_structure[$i],
                    $data_block_id, $field_id);
                if ($this->isStructureCompatible($field_structure, $ancestor_field_structure)) {
                    $ancestor_field_data = $this->getDataItem($path, $this->ancestor_pages[$i]);
                    if ($this->isFieldSetInAnyLanguage($ancestor_field_data, $ancestor_field_structure)) {
                        $this->changeDataFieldImagePath($ancestor_field_data, $ancestor_field_structure,
                            $this->ancestor_page_file_url[$i]);
                        $this->setDataItem($path, $ancestor_field_data, $this->page_data);
                        return (true);
                    }
                }
            }
        }
        return (false);
    }

    private function extendedTrim($string)
    {
        return (trim($string, " \t\n\r\0\x0B>"));
    }

    private function getPathFromString($path_string)
    {
        $path_string = $this->extendedTrim($path_string);
        $path = explode('>', $path_string);
        for ($i = 0; $i < count($path); $i++) {
            $path[$i] = $this->extendedTrim($path[$i]);
        }
        return ($path);
    }

    private function getFieldStructure($page_structure, $data_block_id, $field_id)
    {
        if (is_array($page_structure)) {
            if (isset($page_structure[$data_block_id]['type'])) {
                if ($page_structure[$data_block_id]['type'] == 'datablock') {
                    $fields = &$page_structure[$data_block_id]['fields'];
                    for ($i = 0; $i < count($fields); $i++) {
                        if ($fields[$i]['id'] == $field_id) {
                            return ($fields[$i]);
                        }
                    }
                }
            }
        }
        return (null);
    }

// $this->ancestor_page_data_structure[]

    private function getDataItem($path_string, $data)
    {
        $path = $this->getPathFromString($path_string);
        $pointer =& $data;
        $counter = 0;
        if (count($path) > 0) {
            foreach ($path as $key) {
                if (isset($pointer[$key])) {
                    $pointer =& $pointer[$key];
                    if ($counter == count($path) - 1) {
                        return ($pointer);
                    }
                } else {
                    break;
                }
                $counter++;
            }
        }
        return (null);
    }

    private function setDataItem($path_string, $new_value, &$data)
    {
        $path = $this->getPathFromString($path_string);
        $pointer =& $data;
        if (count($path) > 0) {
            for ($i = 0; $i < count($path) - 1; $i++) {
                if (!isset($pointer[$path[$i]])) {
                    return (false);
                }
                $pointer =& $pointer[$path[$i]];
            }
            $pointer[$path[count($path) - 1]] = $new_value;
            return (true);
        }
        return (false);
    }

    public function onBeforeLoadPageDataFields($parameters, &$data)
    {
        $pages = new Pages();
        $this->parameters = $parameters;
        $page_id = $parameters['pageId'];
        $page_properties = $pages->getProperties($page_id);
        $template_id = $page_properties['template-id'];
        $page_structure = $this->getPageStructure($template_id);
        $inherit = array();
        foreach ($page_structure as $block_id => $block) {
            if ($block['type'] == 'datablock') {
                foreach ($block['fields'] as $field) {
                    if (isset($field['inherit'])) {
                        if ($field['inherit'] === true) {
                            $inherit[] = array(
                                'data_block_id' => $block_id,
                                'field_id' => $field['id'],
                            );
                        }
                    }
                }
            }
        }
        if (count($inherit) > 0) {
            $this->page_data = $data;
            $this->loadAncestorPages($page_id);
            foreach ($inherit as $item) {
                $this->inherit($template_id, $item['data_block_id'], $item['field_id']);
            }
            $data = $this->page_data;
        }

    }

}
