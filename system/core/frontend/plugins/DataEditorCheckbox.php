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

class DataEditorCheckboxPlugin implements PluginInterface
{
    public function register()
    {
        return array(
            array(
                'hookId' => Plugins::DATA_EDITOR_PLUGIN_SORT_COMPARE_VALUES,
                'methodName' => 'onDataEditorPluginSortCompareValues'
            )
        );
    }

    public function onDataEditorPluginSortCompareValues($parameters, &$data)
    {
        if ($parameters['type'] == 'checkbox') {
            if (isset($parameters['a']) && isset($parameters['b'])) {
                if ($parameters['a'] < $parameters['b']) {
                    $data = -1;
                } else if ($parameters['a'] > $parameters['b']) {
                    $data = 1;
                } else {
                    $data = 0;
                }
            } else {
                $data = 0;
            }
        }
    }

}
