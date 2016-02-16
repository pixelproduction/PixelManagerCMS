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

class SettingsController extends DataExchangeController
{
    public function __construct()
    {
    }

    public function userIsAuthorized($action)
    {
        // Nur Administratoren d�rfen die Einstellungen �ndern
        $result = parent::userIsAuthorized($action);
        if ($result === true) {
            if (!Auth::isAdmin()) {
                $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
                $result = false;
            }
        }
        return ($result);
    }

    public function defaultAction()
    {
        $this->updateAction();
    }

    public function updateAction()
    {
        Settings::load();
        $languages = Config::get()->languages->list;

        $start_pages = array();
        foreach ($languages as $language_id => $language) {
            $start_page = 0;
            if (isset($_POST['start-page-' . $language_id])) {
                $start_page = $this->sanitizeInteger($_POST['start-page-' . $language_id]);
            }
            $start_pages[$language_id] = $start_page;
        }
        Settings::set('startPages', $start_pages);

        $error_pages = array();
        foreach ($languages as $language_id => $language) {
            $error_page = 0;
            if (isset($_POST['error-page-' . $language_id])) {
                $error_page = $this->sanitizeInteger($_POST['error-page-' . $language_id]);
            }
            $error_pages[$language_id] = $error_page;
        }
        Settings::set('errorPages', $error_pages);

        $use_cache = $this->sanitizeBoolean(Request::postParam('use-cache', false));
        Settings::set('useCache', $use_cache);

        $cache_lifetime = $this->sanitizeInteger(Request::postParam('cache-lifetime', 0));
        Settings::set('cacheLifetime', $cache_lifetime);

        Settings::save();
        $this->success();
    }

}
