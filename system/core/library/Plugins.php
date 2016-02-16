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

final class Plugins
{
    private static $instance = null;

    // *********************************************************************
    // Verfügbare Hooks
    // *********************************************************************

    const STARTUP = 'startup';
    const BEFORE_DISPLAY = 'before_display';
    const SEND_HEADER = 'send_header';
    const MODIFY_OUTPUT_BEFORE_DISPLAY = 'modify_output_before_display';
    const AFTER_DISPLAY = 'after_display';
    const ASSIGN_PAGE_DATA = 'assign_page_data';
    const ASSIGN_PAGE_ELEMENT_DATA = 'assign_page_element_data';
    const ASSIGN_PAGE_DATA_FIELD = 'assign_page_data_field';
    const BEFORE_SAVE_PAGE_DATA_FIELDS = 'before_save_page_data_fields';
    const AFTER_SAVE_PAGE_DATA_FIELDS = 'after_save_page_data_fields';
    const SAVE_PAGE_DATA_FIELD = 'save_page_data_field';
    const BEFORE_LOAD_PAGE_DATA_FIELDS = 'before_load_page_data_fields';
    const AFTER_LOAD_PAGE_DATA_FIELDS = 'after_load_page_data_fields';
    const LOAD_PAGE_DATA_FIELD = 'load_page_data_field';
    const LOAD_PAGE_DATA_FIELD_PARAMETERS = 'load_page_data_field_parameters';
    const CREATE_PAGE = 'create_page';
    const PUBLISH_PAGE = 'publish_page';
    const COPY_PAGE = 'copy_page';
    const MOVE_PAGE = 'move_page';
    const RENAME_PAGE = 'rename_page';
    const DELETE_PAGE = 'delete_page';
    const AFTER_DELETE_PAGE = 'after_delete_page';
    const SET_PAGE_PROPERTIES = 'set_page_properties';
    const PAGETREE_EDIT_PAGE_PROPERTIES = 'pagetree_edit_page_properties';
    const PAGETREE_SET_PAGE_TITLE = 'pagetree_set_page_title';
    const PAGETREE_BEGIN_BATCH_EDIT_PAGE_PROPERTIES = 'pagetree_begin_batch_edit_page_properties';
    const PAGETREE_END_BATCH_EDIT_PAGE_PROPERTIES = 'pagetree_end_batch_edit_page_properties';
    const PAGETREE_BEGIN_BATCH_COPY_PAGE = 'pagetree_begin_batch_copy_page';
    const PAGETREE_END_BATCH_COPY_PAGE = 'pagetree_end_batch_copy_page';
    const PAGETREE_BEGIN_BATCH_MOVE_PAGE = 'pagetree_begin_batch_move_page';
    const PAGETREE_END_BATCH_MOVE_PAGE = 'pagetree_end_batch_move_page';
    const PAGETREE_BEGIN_BATCH_DELETE_PAGE = 'pagetree_begin_batch_delete_page';
    const PAGETREE_END_BATCH_DELETE_PAGE = 'pagetree_end_batch_delete_page';
    const PAGETREE_BEGIN_BATCH_PUBLISH_PAGE = 'pagetree_begin_batch_publish_page';
    const PAGETREE_END_BATCH_PUBLISH_PAGE = 'pagetree_end_batch_publish_page';
    const PAGETREE_COLLECT_PAGE_ANCHORS = 'pagetree_collect_page_anchors';
    const DATA_EDITOR_PLUGIN_PREPARE_FOR_OUTPUT = 'data_editor_plugin_prepare_for_output';
    const DATA_EDITOR_PLUGIN_SORT_COMPARE_VALUES = 'data_editor_plugin_sort_compare_values';
    const ACCEPT_QUERY_STRING = 'accept_query_string';
    const GET_BACKEND_MODULES_MENU_CAPTION = 'get_backend_modules_menu_caption';
    const GET_BACKEND_MODULES_TAB_CAPTION = 'get_backend_modules_tab_caption';
    const AFTER_IMAGE_RESIZE = 'after_image_resize';

    protected static $registeredHooks = array();
    protected static $registeredPlugins = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    public static function isPluginRegistered($class_name)
    {
        if (count(self::$registeredPlugins) > 0) {
            foreach (self::$registeredPlugins as $plugin) {
                if ($plugin['className'] == $class_name) {
                    return (true);
                }
            }
        }
        return (false);
    }

    protected static function registerHook($hook_id, $class_name, $method_name)
    {
        self::$registeredHooks[$hook_id][$class_name] = $method_name;
    }

    public static function loadClass($class_path, $class_name)
    {
        if (self::isPluginRegistered($class_name)) {
            return (false);
        }

        if (!file_exists($class_path)) {
            Helpers::debugError('RegisterPlugin: Class file not found (' . $class_name . ' doesn\'t exist)!');
            return (false);
        }
        require_once($class_path);

        if (!class_exists($class_name)) {
            Helpers::debugError('RegisterPlugin: Class not found (class "' . $class_name . '" doesn\'t exist in ' . $class_path . ')!');
        }
        $plugin = new $class_name();
        $hooks = $plugin->register();

        if (is_array($hooks)) {
            if (count($hooks) > 0) {
                foreach ($hooks as $hook) {
                    if (isset($hook['hookId']) && isset($hook['methodName'])) {
                        if (!method_exists($plugin, $hook['methodName'])) {
                            Helpers::debugError('RegisterPlugin: class method not found (class-method "' . $hook['methodName'] . '" doesn\'t exist in class "' . $class_name . '")!');
                            return (false);
                        }
                        self::registerHook($hook['hookId'], $class_name, $hook['methodName']);
                    }
                }
                self::$registeredPlugins[$class_name] = array(
                    'className' => $class_name,
                    'classInstance' => new $class_name(),
                    'hooks' => $hooks
                );
            } else {
                return (false);
            }
        } else {
            return (false);
        }

        return (true);
    }

    public static function unregisterHooks($class_name)
    {
        foreach (self::$registeredPlugins[$class_name]['hooks'] as $hook) {
            unset(self::$registeredHooks[$hook['hookId']][$class_name]);
        }
    }

    public static function unloadClass($class_name)
    {
        if (self::isPluginRegistered($class_name)) {
            self::unregisterHooks($class_name);
            unset(self::$registeredPlugins[$class_name]);
        }
    }

    public static function loadFrontend()
    {
        $config = Config::getArray();
        if (count($config['frontendPlugins']) > 0) {
            foreach ($config['frontendPlugins'] as $plugin) {
                self::loadClass($plugin['classFile'], $plugin['className']);
            }
        }
    }

    public static function loadBackend()
    {
        $config = Config::getArray();
        if (count($config['backendPlugins']) > 0) {
            foreach ($config['backendPlugins'] as $plugin) {
                Plugins::loadClass($plugin['classFile'], $plugin['className']);
            }
        }
    }

    public static function load()
    {
        if (Request::isFrontend()) {
            self::loadFrontend();
        } else {
            self::loadBackend();
        }
    }

    public static function call($hook_id, $parameters, &$data = null)
    {
        if (isset(self::$registeredHooks[$hook_id])) {
            if (count(self::$registeredHooks[$hook_id]) > 0) {
                foreach (self::$registeredHooks[$hook_id] as $class => $method) {
                    $plugin = self::$registeredPlugins[$class]['classInstance'];
                    $plugin->{$method}($parameters, $data);
                }
            }
        }
    }

}
