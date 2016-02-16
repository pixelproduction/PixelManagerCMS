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

class DataExchangeController extends Controller
{

    const RESULT_OK = 100;
    const RESULT_ERROR_UNKOWN = 0;
    const RESULT_ERROR_NOT_LOGGED_IN = 1;
    const RESULT_ERROR_NOT_AUHTORIZED = 2;
    const RESULT_ERROR_BAD_REQUEST = 3;
    const RESULT_ERROR_DOES_NOT_EXIST = 4;
    const RESULT_ERROR_CUSTOM = 99;

    public function assignView($view)
    {
        parent::assignView($view);
        if ($this->view != null) {
            $this->view->setMimeType('application/json');
            $this->view->assignTemplate(APPLICATION_ROOT . 'system/core/backend/views/data-exchange/json.php');
        }
    }

    public function userIsAuthorized($action)
    {
        if (($this->module_name != 'login') && (!Auth::isLoggedIn())) {
            // Kein Benutzer eingeloggt, das Ausf�hren der angeforderten Action verhindern
            $this->error(self::RESULT_ERROR_NOT_LOGGED_IN);
            return (false);
        } else if (Auth::isLoggedIn()) {
            // Wenn es sich um ein Zusatzmodul handelt, ggf. auf Zugangsberechtigung pr�fen
            if ($this->isCustomBackendModule()) {
                $can_user_access = $this->canUserAccessCustomBackendModule();
                if ($can_user_access === false) {
                    $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
                }
                return ($can_user_access);
            }
        }
        // Anfrage wurde bis jetzt nicht abgefangen, also erstmal annehmen, dass der Zugriff erlaubt ist
        return (true);
    }

    public function success($data = array())
    {
        $this->view->assign('data', $data);
        $this->view->assign('result', self::RESULT_OK);
        $this->view->assign('customErrorMessage', '');
    }

    public function error($resultCode = self::RESULT_ERROR_UNKOWN, $customErrorMessage = '')
    {
        $this->view->assign('data', array());
        $this->view->assign('result', $resultCode);
        $this->view->assign('customErrorMessage', $customErrorMessage);
    }

    public function customError($customErrorMessage = '')
    {
        $this->error(self::RESULT_ERROR_CUSTOM, $customErrorMessage);
    }

    public function sanitizeBoolean($boolish_string)
    {
        if (is_bool($boolish_string)) {
            return ($boolish_string);
        } else if (is_numeric($boolish_string)) {
            $boolish_int = (int)$boolish_string;
            if ($boolish_int != 0) {
                return (true);
            } else {
                return (false);
            }
        } else {
            $boolish_string = trim(UTF8String::strtolower($boolish_string));
            if ($boolish_string == 'true') {
                return (true);
            } else {
                return (false);
            }
        }
    }

    public function sanitizeInteger($numeric_string)
    {
        if (is_integer($numeric_string)) {
            return ($numeric_string);
        } else if (is_numeric($numeric_string)) {
            return ((int)$numeric_string);
        } else {
            return (0);
        }
    }

}
