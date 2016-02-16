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

class DataModifier implements DataModifierInterface
{

    public function __construct()
    {
    }

    public function apply(&$data, $structure, $parameters)
    {
        $this->modifyRoot($data, $structure, $parameters);
        $this->walkRoot($data, $structure, $parameters);
    }

    public function modifyRoot(&$data, $structure, $parameters)
    {
    }

    public function walkRoot(&$data, $structure, $parameters)
    {
        foreach ($structure as $block_id => $block_structure) {
            $block_parameters = array(
                'blockId' => $block_id
            );
            $block_parameters = array_merge($parameters, $block_parameters);
            if ($block_structure['type'] == 'datablock') {
                $this->modifyFields($data[$block_id], $block_structure['fields'], $block_parameters);
                $this->walkFields($data[$block_id], $block_structure['fields'], $block_parameters);
            } elseif ($block_structure['type'] == 'container') {
                $this->modifyElementContainer($data[$block_id], $block_structure, $block_parameters);
                $this->walkElementContainer($data[$block_id], $block_structure, $block_parameters);
            }
        }

    }

    public function modifyElementContainer(&$data, $structure, $parameters)
    {
    }

    public function walkElementContainer(&$data, $structure, $parameters)
    {
        if (is_array($data)) {
            if (count($data) > 0) {
                $elements_structure = DataStructure::elementsArray();
                for ($element_index = 0; $element_index < count($data); $element_index++) {
                    $element_data = &$data[$element_index]['content'];
                    $element_id = $data[$element_index]['elementId'];
                    $element_parameters = array(
                        'elementId' => $element_id,
                        'elementIndex' => $element_index
                    );
                    $element_parameters = array_merge($parameters, $element_parameters);
                    if (!isset($elements_structure[$element_id])) {
                        Helpers::fatalError('DataModifier: A element with the ID [' . $element_id . '] does not exist.');
                    }
                    $element_structure = $elements_structure[$element_id]['structure'];
                    $this->modifyFields($element_data, $element_structure, $element_parameters);
                    $this->walkFields($element_data, $element_structure, $element_parameters);
                }
            }
        }

    }

    public function modifyFields(&$data, $structure, $parameters)
    {
    }

    public function walkFields(&$data, $structure, $parameters)
    {
        if (is_array($structure)) {
            if (count($structure) > 0) {
                foreach ($structure as $field) {
                    if (isset($data[$field['id']])) {
                        $field_id = $field['id'];
                        if ($field['type'] == 'array') {
                            $this->modifyArray($data[$field_id], $field, $parameters);
                            $this->walkArray($data[$field_id], $field, $parameters);
                        } else {
                            $this->modifyField($data[$field_id], $field, $parameters);
                        }
                    }
                }
            }
        }
    }

    public function modifyArray(&$data, $structure, $parameters)
    {
    }

    public function walkArray(&$data, $structure, $parameters)
    {
        if (is_array($data)) {
            if (count($data) > 0) {
                for ($row_index = 0; $row_index < count($data); $row_index++) {
                    $row_data = &$data[$row_index];
                    $fields_parameters = array(
                        'arrayId' => $structure['id'],
                    );
                    $fields_parameters = array_merge($parameters, $fields_parameters);
                    $this->modifyFields($row_data, $structure['parameters']['fields'], $fields_parameters);
                    $this->walkFields($row_data, $structure['parameters']['fields'], $fields_parameters);
                }
            }
        }
    }

    public function modifyField(&$data, $structure, $parameters)
    {
    }

}
