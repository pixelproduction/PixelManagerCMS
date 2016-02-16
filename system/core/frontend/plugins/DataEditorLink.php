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

class DataEditorLinkPlugin implements PluginInterface
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

    public function onDataEditorPluginPrepareForOutput($parameters, &$data)
    {
        if ($parameters['fieldType'] == 'link') {

            $edit_data = null;

            if (isset($data)) {
                if (is_array($data)) {
                    if (isset($data['url'])) {
                        if (is_string($data['url'])) {

                            $edit_data = array('url' => $data['url']);

                            if (isset($data['newWindow'])) {
                                $edit_data['newWindow'] = $data['newWindow'];
                            } else {
                                $edit_data['newWindow'] = 'auto';
                            }

                            $edit_data['type'] = 'external';
                            $auto_link_target = '';
                            if (UTF8String::strtolower(UTF8String::substr($data['url'], 0,
                                    UTF8String::strlen('link://'))) == 'link://'
                            ) {
                                $edit_data['type'] = 'internal';
                            } elseif (UTF8String::strtolower(UTF8String::substr($data['url'], 0,
                                    UTF8String::strlen('download://'))) == 'download://'
                            ) {
                                $edit_data['type'] = 'download';
                            }

                            switch ($edit_data['type']) {
                                case 'external':
                                    $edit_data['url'] = $data['url'];
                                    $auto_link_target = '_blank';
                                    break;
                                case 'internal':
                                    $edit_data['url'] = Config::get()->baseUrl . UTF8String::substr($data['url'],
                                            UTF8String::strlen('link://'));
                                    $auto_link_target = '';
                                    break;
                                case 'download':
                                    $edit_data['url'] = Config::get()->baseUrl . 'user-data/downloads/' . UTF8String::substr($data['url'],
                                            UTF8String::strlen('download://'));
                                    $auto_link_target = '_blank';
                                    $edit_data['path'] = APPLICATION_ROOT . 'user-data/downloads/' . urldecode(UTF8String::substr($data['url'],
                                            UTF8String::strlen('download://')));
                                    $path_info = pathinfo($edit_data['path']);
                                    $edit_data['basename'] = isset($path_info['basename']) ? $path_info['basename'] : '';
                                    $edit_data['extension'] = isset($path_info['extension']) ? $path_info['extension'] : '';
                                    $edit_data['filename'] = isset($path_info['filename']) ? $path_info['filename'] : '';
                                    if (file_exists($edit_data['path'])) {
                                        $edit_data['filesize'] = @filesize($edit_data['path']);
                                    }
                                    break;
                                default:
                                    break;
                            }

                            switch ($edit_data['newWindow']) {
                                case 'yes':
                                    $edit_data['target'] = '_blank';
                                    break;
                                case 'no':
                                    $edit_data['target'] = '';
                                    break;
                                default:
                                    $edit_data['target'] = $auto_link_target;
                                    break;
                            }
                        }
                    }
                }
            }

            $data = $edit_data;

        }
    }

    public function onDataEditorPluginSortCompareValues($parameters, &$data)
    {
        if ($parameters['type'] == 'link') {
            $url1 = '';
            $url2 = '';
            if (is_array($parameters['a'])) {
                if (isset($parameters['a']['url'])) {
                    $url1 = $parameters['a']['url'];
                }
            }
            if (is_array($parameters['b'])) {
                if (isset($parameters['b']['url'])) {
                    $url2 = $parameters['b']['url'];
                }
            }
            $data = strnatcasecmp(utf8_decode($url1), utf8_decode($url2));
        }
    }

}
