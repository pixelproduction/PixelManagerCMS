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

class Controller
{
    protected $view = null;
    protected $module_name = '';
    protected $is_custom_backend_module = false;
    protected $custom_backend_module_data = null;

    public function __construct($module_name, $custom_backend_module_data = null)
    {
        $this->module_name = $module_name;
        if ($custom_backend_module_data !== null) {
            $this->setCustomBackendModule($custom_backend_module_data);
        }
    }

    public function isCustomBackendModule()
    {
        return ($this->is_custom_backend_module);
    }

    public function isCustomBackendModuleProteced()
    {
        if ($this->isCustomBackendModule()) {
            $module = $this->getCustomBackendModule();
            if (isset($module['aclResourceId'])) {
                if (trim($module['aclResourceId']) != '') {
                    return (true);
                }
            }
        }
        return (false);
    }

    public function getAclResourceForCustomBackendModule()
    {
        if ($this->isCustomBackendModule()) {
            $module = $this->getCustomBackendModule();
            if (isset($module['aclResourceId'])) {
                if (trim($module['aclResourceId']) != '') {
                    return (Acl::getResourceData(Acl::RESOURCE_GROUP_MODULES, $module['aclResourceId']));
                }
            }
        }
        return (false);
    }

    public function createAclResourceForCustomBackendModule()
    {
        if ($this->is_custom_backend_module) {
            $module = $this->custom_backend_module_data;
            if (isset($module['aclResourceId'])) {
                if (trim($module['aclResourceId']) != '') {
                    $resource = Acl::getResourceData(Acl::RESOURCE_GROUP_MODULES, $module['aclResourceId']);
                    if ($resource === false) {
                        Acl::registerResource(
                            Acl::RESOURCE_GROUP_MODULES,
                            $module['aclResourceId'],
                            $module['name'],
                            Acl::RESOURCE_USER_WHITELIST
                        );
                    }
                }
            }
        }
    }

    public function setCustomBackendModule($module_data)
    {
        $this->is_custom_backend_module = true;
        $this->custom_backend_module_data = $module_data;
        $this->createAclResourceForCustomBackendModule();
    }

    public function getCustomBackendModule()
    {
        return ($this->custom_backend_module_data);
    }

    public function canUserAccessCustomBackendModule()
    {
        if ($this->isCustomBackendModule()) {
            if ($this->isCustomBackendModuleProteced()) {
                $acl_resource = $this->getAclResourceForCustomBackendModule();
                if ($acl_resource === false) {
                    // Das Modul soll eigentlich gesch�tzt sein (hat eine Acl ResourceId in der Config)
                    // aber es konnte keine Acl Resource gefunden werden, also Zugriff verweigern
                    return (false);
                }
                // Eine Acl Resource wurde gefunden also diese p�fen und das Ergebnis zur�ckgeben
                $result = Acl::canAccessById($acl_resource['id'], Acl::ACTION_EDIT);
                return ($result);
            } else {
                return (true);
            }
        }
        return (false);
    }

    public function defaultAction()
    {
    }

    public function assignView($view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return ($this->view);
    }

    public function beforeAction($action)
    {
        // Kann in abgeleiteten Klassen �berschrieben werden, um die Action ggf. abzubrechen, oder so
        return (true);
    }

    public function userIsAuthorized($action)
    {
        // Kann in abgeleiteten Klassen �berschrieben werden, um die Action ggf. abzubrechen wenn der Benutzer nicht eingeloggt / authorisiert ist
        return (true);
    }

    public function callActionMethod($action)
    {
        // JS 26.02.2014 - habe gerade gesehen dass eine Aktion "before" zu �blen Problemen f�hren k�nnte...
        // Eigentlich m�sste man die Funktion "beforeAction" umbenennen, aber aus Kompatibilit�tsgr�nden lassen wir es mal so
        // und fangen es einfach ggf. ab...
        if ($action == 'before') {
            Helpers::fatalError('An action can not have the name "before" because it is used internal.', true);
        }

        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            if ($this->userIsAuthorized($action)) {
                if ($this->beforeAction($action)) {
                    $this->{$method}();
                }
            }
            return (true);
        } else {
            return (false);
        }
    }

}
