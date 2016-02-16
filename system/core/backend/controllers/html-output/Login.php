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

class LoginController extends HtmlOutputController
{
    public function defaultAction()
    {
        if ((!Request::isSecure()) && Config::get()->forceAdminHttps) {
            Helpers::redirect(Helpers::getCompleteUrl(Config::get()->baseUrl . 'admin/html-output/login', 'https://'));
        }
    }

    public function authenticateAction()
    {
        if (isset($_POST['login']) && isset($_POST['password'])) {
            if (Auth::login($_POST['login'], $_POST['password'])) {
                if (isset($_POST['language'])) {
                    if ($_POST['language'] != '') {
                        $_SESSION['pixelmanager']['backendLanguage'] = $_POST['language'];
                    } else {
                        $_SESSION['pixelmanager']['backendLanguage'] = Auth::getUserPreferredLanguage();
                    }
                }
                if (isset($_SESSION['pixelmanager_temp_login_redirect']['uri'])) {
                    Helpers::redirect($_SESSION['pixelmanager_temp_login_redirect']['uri']);
                } else {
                    Helpers::redirect(Config::get()->baseUrl . 'admin/html-output/main');
                }
            } else {
                $this->view->assign('error', true);
            }
        } else {
            $this->view->assign('error', true);
        }
    }

}
