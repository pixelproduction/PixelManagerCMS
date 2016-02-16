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

class DataEditorPluginsModifier extends DataModifier
{

    public function modifyField(&$data, $structure, $parameters)
    {
        if (isset($structure['parameters'])) {
            $fieldParameters = $structure['parameters'];
        } else {
            $fieldParameters = array();
        }
        $field_parameters = array(
            'fieldId' => $structure['id'],
            'fieldType' => $structure['type'],
            'fieldParameters' => $fieldParameters,
        );
        $field_parameters = array_merge($parameters, $field_parameters);
        Plugins::call(Plugins::DATA_EDITOR_PLUGIN_PREPARE_FOR_OUTPUT, $field_parameters, $data);
    }
    
}
