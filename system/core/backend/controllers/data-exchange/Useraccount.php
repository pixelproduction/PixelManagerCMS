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

class UseraccountController extends DataExchangeController
{
    public function __construct()
    {
        $this->users = new Users();
        $this->user_groups = new UserGroups();
        $this->helpers = new ControllerHelpers();
    }

    public function defaultAction()
    {
        $this->updateAction();
    }

    public function updateAction()
    {
        $id = Auth::getUserId();
        $screenname = Request::postParam('screenname', '');
        $login = Request::postParam('login', '');
        $password = Request::postParam('password', '');
        $preferred_language = Request::postParam('preferred-language');

        if ((trim($screenname) == '') || (trim($login) == '')) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $user = $this->users->getById($id);
        if ($user === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        $res = array(
            'action' => 'update',
            'loginAlreadyExists' => true,
            'accountUpdated' => false
        );

        if (!$this->users->loginExists($login, $id)) {
            $res['loginAlreadyExists'] = false;

            $properties = array(
                'screenname' => trim($screenname),
                'login' => trim($login),
                'preferred-language' => $preferred_language,
            );

            if (trim($password) != '') {
                $properties['password'] = trim($password);
            }

            $updated = $this->users->update($id, $properties);
            if ($updated !== false) {
                $res['accountUpdated'] = true;
            }
        }

        $this->success($res);
    }

}
