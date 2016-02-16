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

class HtmlOutputController extends Controller
{

    public function userIsAuthorized($action)
    {
        // Dafür sorgen, dass eine Anfrage ggf. auf das Login weitergeleitet wird,
        // wenn die Session abgelaufen ist und nach erfolgtem Login nahtlos auf
        // die angeforderte Seite weitergeleitet wird
        if (($this->module_name != 'login') && (!Auth::isLoggedIn())) {
            $_SESSION['pixelmanager_temp_login_redirect']['uri'] = $_SERVER['REQUEST_URI'];
            $_SESSION['pixelmanager_temp_login_redirect']['get'] = $_GET;
            $_SESSION['pixelmanager_temp_login_redirect']['post'] = $_POST;
            $_SESSION['pixelmanager_temp_login_redirect']['request'] = $_REQUEST;
            Helpers::redirect(Config::get()->baseUrl . 'admin/html-output/login');
        } else {
            if ($this->module_name != 'login') {
                if (isset($_SESSION['pixelmanager_temp_login_redirect'])) {
                    $_GET = $_SESSION['pixelmanager_temp_login_redirect']['get'];
                    $_POST = $_SESSION['pixelmanager_temp_login_redirect']['post'];
                    $_REQUEST = $_SESSION['pixelmanager_temp_login_redirect']['request'];
                    unset($_SESSION['pixelmanager_temp_login_redirect']);
                }
            }
        }
        // Wenn es sich um ein Zusatzmodul handelt, ggf. auf Zugangsberechtigung prüfen
        if ($this->isCustomBackendModule()) {
            $can_user_access = $this->canUserAccessCustomBackendModule();
            if ($can_user_access === false) {
                $this->accessDenied();
            }
            return ($can_user_access);
        }
        // Anfrage wurde bis jetzt nicht abgefangen, also erstmal annehmen, dass der Zugriff erlaubt ist
        return (true);
    }

    public function accessDenied()
    {
        $this->view->assignTemplate(APPLICATION_ROOT . 'system/core/backend/views/html-output/error-accessdenied.php');
    }

    public function doesNotExist()
    {
        $this->view->assignTemplate(APPLICATION_ROOT . 'system/core/backend/views/html-output/error-doesnotexist.php');
    }

    public function assignView($view)
    {
        parent::assignView($view);
        $view = $this->getView();
        $link_tags_html = '';
        $loadDataEditorPlugins = array();
        $config = Config::getArray();
        $dataEditorPlugins = $config['dataEditorPlugins'];
        foreach ($dataEditorPlugins as $key => $plugin) {
            if (is_array($plugin)) {
                if (isset($plugin['additionalCSS'])) {
                    if (is_array($plugin['additionalCSS'])) {
                        foreach ($plugin['additionalCSS'] as $add_css) {
                            $link_tags_html .= '<link rel="stylesheet" href="' . $config['baseUrl'] . $add_css . '">';
                        }
                    } else {
                        $link_tags_html .= '<link rel="stylesheet" href="' . $config['baseUrl'] . $plugin['additionalCSS'] . '">';
                    }
                }
            }
        }
        $view->assign('additionalStylesheets', $link_tags_html);
    }

}
