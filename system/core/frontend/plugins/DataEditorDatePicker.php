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

class DataEditorDatePickerPlugin implements PluginInterface
{
    public function register()
    {
        return array(
            array(
                'hookId' => Plugins::DATA_EDITOR_PLUGIN_PREPARE_FOR_OUTPUT,
                'methodName' => 'onDataEditorPluginPrepareForOutput'
            ),
            array(
                'hookId' => Plugins::DATA_EDITOR_PLUGIN_SORT_COMPARE_VALUES,
                'methodName' => 'onDataEditorPluginSortCompareValues'
            )
        );
    }

    protected function getTimestampFromString($string)
    {
        $return_value = null;
        if (is_string($string)) {
            $date_array = explode('-', $string);
            if (count($date_array) >= 3) {
                $year = $date_array[0];
                $month = $date_array[1];
                $day = $date_array[2];
                $hour = 0;
                $minute = 0;
                if (count($date_array) == 5) {
                    $hour = $date_array[3];
                    $minute = $date_array[4];
                }
                $timestamp = mktime($hour, $minute, 0, $month, $day, $year);
                $return_value = $timestamp;
            }
        }
        return ($return_value);
    }


    public function onDataEditorPluginPrepareForOutput($parameters, &$data)
    {
        if ($parameters['fieldType'] == 'datePicker') {
            // Die Daten kommen als String in der Form jjjj-mm-tt
            // Da PHP (und damit Smarty) aber tausend Funktionen bereitstellt,
            // mit denen Unix-Timestamps verarbeitet und formatiert werden k�nnen,
            // wollen wir lieber einen solchen haben...
            $return_value = null;
            if (isset($data)) {
                if (is_string($data)) {
                    $return_value = $this->getTimestampFromString($data);
                }
            }
            $data = $return_value;
        }
    }

    public function onDataEditorPluginSortCompareValues($parameters, &$data)
    {
        if ($parameters['type'] == 'datePicker') {
            $date1 = $this->getTimestampFromString($parameters['a']);
            $date2 = $this->getTimestampFromString($parameters['b']);
            if ($date1 < $date2) {
                $data = -1;
            } else if ($date1 > $date2) {
                $data = 1;
            } else {
                $data = 0;
            }
        }
    }

}
