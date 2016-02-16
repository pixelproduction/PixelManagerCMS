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

class BackendmodulesController extends DataExchangeController
{

    const NO_GROUP_ASSIGNED = '__no_group_assigned__';

    public function defaultAction()
    {
        $this->gettabcontentAction();
    }

    public function sortModuleGroupsCompare($a, $b)
    {
        if ($a['position'] == $b['position']) {
            return 0;
        }
        return ($a['position'] < $b['position']) ? -1 : 1;
    }

    protected function getModuleGroups()
    {
        $config = Config::getArray();
        $groups = array(
            self::NO_GROUP_ASSIGNED => array(
                'caption' => Translate::get('Other'),
                'modules' => array(),
                'position' => 9999999,
            )
        );

        if (count($config['backendModules']) > 0) {
            foreach ($config['backendModules'] as $module_id => $module) {

                $temp_controller = new Controller($module_id, $module);
                if ($temp_controller->canUserAccessCustomBackendModule()) {

                    $visible = true;
                    if (isset($module['invisible'])) {
                        if ($module['invisible'] === true) {
                            $visible = false;
                        }
                    }

                    if ($visible) {
                        $group_id = self::NO_GROUP_ASSIGNED;
                        if (isset($module['showInTabGroup'])) {
                            if (isset($config['backendModulesTabGroups'][$module['showInTabGroup']])) {
                                $group_id = $module['showInTabGroup'];
                            }
                        }

                        if (!isset($groups[$group_id])) {
                            $groups[$group_id] = $config['backendModulesTabGroups'][$group_id];
                            $groups[$group_id]['modules'] = array();
                        }

                        $groups[$group_id]['modules'][$module_id] = $module;
                    }

                }

            }
        }

        if (count($groups > 0)) {
            foreach ($groups as $group_id => $group) {
                uasort($groups[$group_id]['modules'], array($this, 'sortModuleGroupsCompare'));
            }
            uasort($groups, array($this, 'sortModuleGroupsCompare'));
        }

        return ($groups);
    }

    public function getmenucontentAction()
    {
        $config = Config::getArray();
        $html = '';
        if ($config['showBackendModulesMenu'] === true) {
            if (count($config['backendModules']) > 0) {

                $groups = $this->getModuleGroups();

                if (count($groups) > 0) {
                    $counter = 0;
                    foreach ($groups as $group_id => $group) {

                        if (count($group['modules']) > 0) {
                            if ($counter > 0) {
                                $html .= '<li class="divider"></li>';
                            }
                            foreach ($group['modules'] as $module_id => $module) {
                                $module_name = Translate::get($module['name']);
                                $caption = $module_name;
                                $plugin_parameters = array(
                                    'moduleId' => $module_id,
                                    'moduleConfig' => $module,
                                );

                                Plugins::call(
                                    Plugins::GET_BACKEND_MODULES_MENU_CAPTION,
                                    $plugin_parameters,
                                    $caption
                                );

                                $html .=
                                    '<li><a href="javascript:;" class="open-module-in-tab" data-module-id="' . Helpers::htmlEntities($module_id) . '" data-module-url="' . Helpers::htmlEntities($module['url']) . '" data-module-name="' . Helpers::htmlEntities($module_name) . '">' . $caption . '</a></li>';
                            }
                            $html .=
                                '</div>';
                            $counter++;
                        }

                    }
                }

            }
        }
        $this->success($html);
    }

    public function gettabcontentAction()
    {
        $config = Config::getArray();
        $html = '';
        if ($config['showBackendModulesTab'] === true) {
            if (count($config['backendModules']) > 0) {

                $groups = $this->getModuleGroups();

                if (count($groups) > 0) {
                    foreach ($groups as $group_id => $group) {

                        if (count($group['modules']) > 0) {
                            $html .=
                                '<div class="pixelmanager-backend-modules-group-heading">' . Helpers::htmlEntities($group['caption']) . '</div>' .
                                '<div class="list-group">';
                            foreach ($group['modules'] as $module_id => $module) {
                                $module_name = Translate::get($module['name']);
                                $caption = $module_name;
                                $plugin_parameters = array(
                                    'moduleId' => $module_id,
                                    'moduleConfig' => $module,
                                );

                                Plugins::call(
                                    Plugins::GET_BACKEND_MODULES_TAB_CAPTION,
                                    $plugin_parameters,
                                    $caption
                                );

                                $html .=
                                    '<a href="javascript:;" class="list-group-item open-module-in-tab" data-module-id="' . Helpers::htmlEntities($module_id) . '" data-module-url="' . Helpers::htmlEntities($module['url']) . '" data-module-name="' . Helpers::htmlEntities($module_name) . '">' . $caption . '</a>';
                            }
                            $html .= '</div>';
                        }

                    }
                }

            }
        }
        $this->success($html);
    }

}
