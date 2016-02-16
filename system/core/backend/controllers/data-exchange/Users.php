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

class UsersController extends DataExchangeController
{
    public function __construct()
    {
        $this->users = new Users();
        $this->user_groups = new UserGroups();
        $this->helpers = new ControllerHelpers();
    }

    public function userIsAuthorized($action)
    {
        // Nur Administratoren d�rfen Benutzer anlegen oder �ndern
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
        $this->getAction();
    }

    public function getHtml()
    {
        $users = $this->users->getAll();
        $userGroups = new UserGroups();
        $html = '';
        foreach ($users as $user) {
            $user_groups = $this->users->getUserGroups($user['id']);
            $user_groups_html = '';
            if ($user_groups !== false) {
                if (count($user_groups) > 0) {
                    foreach ($user_groups as $user_group_id) {
                        $user_group_data = $this->user_groups->getById($user_group_id);
                        if ($user_groups_html != '') {
                            $user_groups_html .= ', ';
                        }
                        $user_groups_html .= $user_group_data['name'];
                    }
                }
            }
            $html .= '
					<tr data-id="' . $user['id'] . '">
						<td><input type="checkbox" name="users[' . $user['id'] . ']" value="1" id="user_' . $user['id'] . '"></td>
						<td>' . $user['screenname'] . '</td>
						<td>' . (($user['privileges'] == Auth::PRIVILEGES_ADMIN) ? Translate::get('Administrator') : Translate::get('User')) . '</td>
						<td>' . $user['login'] . '</td>
						<td>' . $user_groups_html . '</td>
					</tr>
				';
        }
        return ($html);
    }

    public function getAction()
    {
        $this->success(array('html' => $this->getHtml()));
    }

    public function getuserAction()
    {
        $id = Request::postParam('userId');
        if (($id === null) || (!is_numeric($id))) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }
        $user = $this->users->getById($id);
        if ($user === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }
        $user_groups = $this->users->getUserGroups($id);
        $user['userGroups'] = $user_groups;
        $this->success($user);
    }

    public function sanitizePrivileges($privileges)
    {
        if (is_numeric($privileges)) {
            $privileges = (int)$privileges;
            if (($privileges !== Auth::PRIVILEGES_USER) && ($privileges !== Auth::PRIVILEGES_ADMIN)) {
                $privileges = Auth::PRIVILEGES_USER;
            }
        } else {
            $privileges = Auth::PRIVILEGES_USER;
        }
        return ($privileges);
    }

    protected function assignUserGroups($id, $postparam_user_groups)
    {
        $user_groups = array();
        if (is_array($postparam_user_groups)) {
            if (count($postparam_user_groups) > 0) {
                foreach ($postparam_user_groups as $key => $value) {
                    if ($value == '1') {
                        $user_groups[] = $key;
                    }
                }
            }
        }
        $this->users->assignUserGroups($id, $user_groups);
    }

    public function createAction()
    {
        $screenname = Request::postParam('screenname', '');
        $login = Request::postParam('login', '');
        $password = Request::postParam('password', '');
        $privileges = Request::postParam('privileges', '');
        $user_groups = Request::postParam('usergroups');

        if (($screenname == '') || ($login == '') || ($password == '') || ($privileges == '')) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $res = array(
            'action' => 'create',
            'loginAlreadyExists' => true,
            'userSaved' => false,
            'userId' => null
        );

        if (!$this->users->loginExists($login)) {
            $res['loginAlreadyExists'] = false;
            $created = $this->users->add($screenname, $login, $password, $this->sanitizePrivileges($privileges));
            if ($created !== false) {
                $res['userSaved'] = true;
                $res['userId'] = $created;
                $this->assignUserGroups($created, $user_groups);
            }
        }

        $this->success($res);
    }

    public function updateAction()
    {
        $id = Request::postParam('user-id');
        $screenname = Request::postParam('screenname', '');
        $login = Request::postParam('login', '');
        $password = Request::postParam('password', '');
        $privileges = Request::postParam('privileges', '');
        $user_groups = Request::postParam('usergroups');

        if ((trim($screenname) == '') || (trim($login) == '') || (trim($privileges) == '') || ($id === null) || (!is_numeric($id))) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $user = $this->users->getById($id);
        if ($user === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        // Verhindern, dass der eingeloggte Administrator seine eigenen Privilegien herabstuft
        if ($id == Auth::getUserId()) {
            if ($this->sanitizePrivileges($privileges) != Auth::PRIVILEGES_ADMIN) {
                $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
                return;
            }
        }

        $res = array(
            'action' => 'update',
            'loginAlreadyExists' => true,
            'userSaved' => false,
            'userId' => $id
        );

        if (!$this->users->loginExists($login, $id)) {
            $res['loginAlreadyExists'] = false;

            $properties = array(
                'screenname' => trim($screenname),
                'login' => trim($login),
                'privileges' => $this->sanitizePrivileges($privileges)
            );

            if (trim($password) != '') {
                $properties['password'] = trim($password);
            }

            $updated = $this->users->update($id, $properties);
            if ($updated !== false) {
                $res['userSaved'] = true;
                $this->assignUserGroups($id, $user_groups);
            }
        }

        $this->success($res);
    }

    function getusergroupsAction()
    {
        $user_groups = $this->user_groups->getAll();
        $html = '';
        if ($user_groups !== false) {
            if (count($user_groups) > 0) {
                foreach ($user_groups as $user_group) {
                    $html .= '
							<tr>
								<td><input type="checkbox" name="usergroups[' . $user_group['id'] . ']" value="1" id="usergroup_' . $user_group['id'] . '"></td>
								<td>' . $user_group['name'] . '</td>
								<td>' . $user_group['level'] . '</td>
							</tr>
						';
                }
            }
        }
        $this->success(array('html' => $html));
    }

    function deleteAction()
    {
        $users = Request::postParam('users');
        if (($users === null) || (!is_array($users))) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }
        if (count($users) > 0) {
            foreach ($users as $user_id => $value) {
                if ($value == '1') {
                    if (is_numeric($user_id)) {

                        // Verhindern, dass der eingeloggte Administrator sich selber l�scht
                        if ($user_id == Auth::getUserId()) {
                            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
                            return;
                        }

                        $this->users->delete($user_id);
                    }
                }
            }

        }
        $this->success();
    }

}
