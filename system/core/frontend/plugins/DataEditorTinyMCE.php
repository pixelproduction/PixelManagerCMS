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

class DataEditorTinyMCEPlugin implements PluginInterface
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
            ),
        );
    }

    public function onDataEditorPluginPrepareForOutput($parameters, &$data)
    {
        if ($parameters['fieldType'] == 'tinyMCE') {
            if (isset($data)) {
                if (is_string($data)) {
                    $data = str_replace('link://', Config::get()->baseUrl, $data);
                    $data = str_replace('download://', Config::get()->baseUrl . 'user-data/downloads/', $data);
                }
            }
        }
    }

    public function onDataEditorPluginSortCompareValues($parameters, &$data)
    {
        if ($parameters['type'] == 'tinyMCE') {
            if (is_string($parameters['a']) && is_string($parameters['b'])) {
                $data = strnatcasecmp(utf8_decode($parameters['a']), utf8_decode($parameters['b']));
            } else {
                $data = 0;
            }
        }
    }

}
