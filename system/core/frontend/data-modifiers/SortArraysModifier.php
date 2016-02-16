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

class SortArraysModifier extends DataModifier
{

    protected $tmp_sort_order_by = null;
    protected $tmp_sort_fields = null;

    public function getField($fields, $fieldId)
    {
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if ($field['id'] == $fieldId) {
                    return ($field);
                }
            }
        }
        return (false);
    }

    public function sortCompareItems($a, $b)
    {
        $order_by = $this->tmp_sort_order_by;
        $fields = $this->tmp_sort_fields;
        $result = 0;
        $first_sort_field = $this->getField($fields, $order_by['fieldId']);
        Plugins::call(
            Plugins::DATA_EDITOR_PLUGIN_SORT_COMPARE_VALUES,
            array(
                'type' => $first_sort_field['type'],
                'fieldDefinition' => $first_sort_field,
                'a' => $a[$order_by['fieldId']],
                'b' => $b[$order_by['fieldId']],
            ),
            $result
        );
        if (isset($order_by['direction'])) {
            if ($order_by['direction'] == 'desc') {
                $result = $result * (-1);
            }
        }
        if ($result === 0) {
            if (isset($order_by['additional'])) {
                if (is_array($order_by['additional'])) {
                    foreach ($order_by['additional'] as $additional_order_by) {
                        if (isset($additional_order_by['fieldId'])) {
                            $additional_sort_field = $this->getField($fields, $additional_order_by['fieldId']);
                            if ($additional_sort_field !== false) {
                                Plugins::call(
                                    Plugins::DATA_EDITOR_PLUGIN_SORT_COMPARE_VALUES,
                                    array(
                                        'type' => $additional_sort_field['type'],
                                        'fieldDefinition' => $additional_sort_field,
                                        'a' => $a[$additional_order_by['fieldId']],
                                        'b' => $b[$additional_order_by['fieldId']],
                                    ),
                                    $result
                                );
                                if (isset($additional_order_by['direction'])) {
                                    if ($additional_order_by['direction'] == 'desc') {
                                        $result = $result * (-1);
                                    }
                                }
                                if ($result != 0) {
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        return ($result);
    }

    protected function getSortedArrayData($order_by, $fields, $data)
    {
        if (isset($order_by['fieldId'])) {
            $first_sort_field = $this->getField($fields, $order_by['fieldId']);
            if ($first_sort_field !== false) {
                $this->tmp_sort_order_by = $order_by;
                $this->tmp_sort_fields = $fields;
                usort($data, array($this, "sortCompareItems"));
            }
        }
        return ($data);
    }


    public function modifyArray(&$data, $structure, $parameters)
    {
        if (isset($structure['parameters']['orderBy'])) {
            $data = $this->getSortedArrayData($structure['parameters']['orderBy'], $structure['parameters']['fields'],
                $data);
        }
    }

}
