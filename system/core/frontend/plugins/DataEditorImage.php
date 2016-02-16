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

class DataEditorImagePlugin implements PluginInterface
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

    private function getImageInfo($image_path, $image_url)
    {
        $return_value = null;
        if (is_file($image_path)) {
            $info = @getimagesize($image_path);
            if ($info !== false) {
                $return_value = array(
                    'url' => $image_url,
                    'path' => $image_path,
                    'basename' => basename($image_path),
                    'width' => $info[0],
                    'height' => $info[1],
                    'mime' => $info['mime']
                );
            }
        }
        return ($return_value);
    }

    public function onDataEditorPluginPrepareForOutput($parameters, &$data)
    {
        if ($parameters['fieldType'] == 'image') {
            $return_value = null;
            if (isset($data['imageRelativePath']) || isset($data['imageAbsoluteUrl'])) {
                if (isset($data['imageAbsoluteUrl'])) {
                    $return_value = $this->getImageInfo(
                        $_SERVER['DOCUMENT_ROOT'] . $data['imageAbsoluteUrl'],
                        $data['imageAbsoluteUrl']
                    );
                } else {
                    $return_value = $this->getImageInfo(
                        $parameters['pageFiles'] . $data['imageRelativePath'],
                        $parameters['pageFilesUrl'] . $data['imageRelativePath']
                    );
                }
                if (isset($data['additionalSizes'])) {
                    if (is_array($data['additionalSizes'])) {
                        foreach ($data['additionalSizes'] as $additional_id => $additional) {
                            $return_value['additionalSizes'][$additional_id] = $this->getImageInfo(
                                $parameters['pageFiles'] . $additional,
                                $parameters['pageFilesUrl'] . $additional
                            );
                        }
                    }
                }
            }
            $data = $return_value;
        }
    }

    public function onDataEditorPluginSortCompareValues($parameters, &$data)
    {
        if ($parameters['type'] == 'image') {
            $data = 0;
        }
    }

}
