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

class UsergroupsController extends DataExchangeController
{
    public function __construct()
    {
        $this->userGroups = new UserGroups();
        $this->helpers = new ControllerHelpers();
    }

    public function userIsAuthorized($action)
    {
        // Nur Administratoren d�rfen Benutzergruppen anlegen oder �ndern
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
        $userGroups = $this->userGroups->getAll();
        $html = '';
        $yes = Translate::get('Yes');
        $no = Translate::get('No');
        if ($userGroups !== false) {
            if (count($userGroups) > 0) {
                foreach ($userGroups as $userGroup) {
                    $modules_text = '';
                    $modules = $this->userGroups->getAclResources($userGroup['id']);
                    if ($modules !== false) {
                        if (count($modules) > 0) {
                            foreach ($modules as $module_acl_resource_id) {
                                $module_acl_resource = Acl::getResourceDataById($module_acl_resource_id);
                                if ($module_acl_resource !== false) {
                                    $modules_text .= ($modules_text != '' ? ', ' : '') . $module_acl_resource['description'];
                                }
                            }
                        }
                    }
                    if (UTF8String::strlen($modules_text) > 75) {
                        $modules_text = UTF8String::substr($modules_text, 0, 75) . '...';
                    }
                    $html .= '
							<tr data-id="' . $userGroup['id'] . '">
								<td><input type="checkbox" name="usergroups[' . $userGroup['id'] . ']" value="1" id="usergroup_' . $userGroup['id'] . '"></td>
								<td>' . $userGroup['name'] . '</td>
								<td>' . $userGroup['level'] . '</td>
								<td>' . (($userGroup['action-create'] > 0) ? $yes : $no) . '</td>
								<td>' . (($userGroup['action-edit'] > 0) ? $yes : $no) . '</td>
								<td>' . (($userGroup['action-publish'] > 0) ? $yes : $no) . '</td>
								<td>' . (($userGroup['action-delete'] > 0) ? $yes : $no) . '</td>
								<td>' . $modules_text . '</td>
							</tr>
						';
                }
            }
        }
        return ($html);
    }

    public function getAction()
    {
        $this->success(array('html' => $this->getHtml()));
    }

    public function getusergroupAction()
    {
        $id = Request::postParam('userGroupId');
        if (($id === null) || (!is_numeric($id))) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }
        $user_group = $this->userGroups->getById($id);
        if ($user_group === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }
        $user_group['modules'] = $this->userGroups->getAclResources($id);
        $this->success($user_group);
    }

    function getmodulesAction()
    {
        $modules = Acl::getResourcesFiltered(Acl::RESOURCE_GROUP_MODULES, Acl::RESOURCE_USER_WHITELIST);
        $html = '';
        if ($modules !== false) {
            if (count($modules) > 0) {
                foreach ($modules as $module) {
                    $html .= '
							<tr>
								<td><input type="checkbox" name="modules[' . $module['id'] . ']" value="1" id="module_' . $module['id'] . '"></td>
								<td>' . $module['description'] . '</td>
							</tr>
						';
                }
            }
        }
        $this->success(array('html' => $html));
    }

    protected function assignModules($id, $postparam_modules)
    {
        $modules = array();
        if (is_array($postparam_modules)) {
            if (count($postparam_modules) > 0) {
                foreach ($postparam_modules as $key => $value) {
                    if ($value == '1') {
                        $modules[] = $key;
                    }
                }
            }
        }
        $this->userGroups->assignAclResources($id, $modules);
    }

    public function createAction()
    {
        $name = Request::postParam('name', '');
        $level = Request::postParam('level', '');
        $action_create = Request::postParam('action-create', '0');
        $action_edit = Request::postParam('action-edit', '0');
        $action_publish = Request::postParam('action-publish', '0');
        $action_delete = Request::postParam('action-delete', '0');
        $modules = Request::postParam('modules');

        if (($name == '') || ($level == '')) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $res = array(
            'action' => 'create',
            'userGroupSaved' => false,
            'userGroupId' => null
        );

        $created = $this->userGroups->add($name, $level, $action_create, $action_edit, $action_publish, $action_delete);
        if ($created !== false) {
            $res['userGroupSaved'] = true;
            $res['userGroupId'] = $created;
            $this->assignModules($created, $modules);
        }

        $this->success($res);
    }

    public function updateAction()
    {
        $id = Request::postParam('user-group-id');
        $name = Request::postParam('name', '');
        $level = Request::postParam('level', '');
        $action_create = Request::postParam('action-create', '0');
        $action_edit = Request::postParam('action-edit', '0');
        $action_publish = Request::postParam('action-publish', '0');
        $action_delete = Request::postParam('action-delete', '0');
        $modules = Request::postParam('modules');

        if ((trim($name) == '') || (trim($level) == '')) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $user_group = $this->userGroups->getById($id);
        if ($user_group === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        $res = array(
            'action' => 'update',
            'userGroupSaved' => false,
            'userGroupId' => $id
        );

        $properties = array(
            'name' => trim($name),
            'level' => trim($level),
            'action-create' => $action_create,
            'action-edit' => $action_edit,
            'action-publish' => $action_publish,
            'action-delete' => $action_delete
        );

        $updated = $this->userGroups->update($id, $properties);
        if ($updated !== false) {
            $res['userGroupSaved'] = true;
            $this->assignModules($id, $modules);
        }

        $this->success($res);
    }

    function deleteAction()
    {
        $user_groups = Request::postParam('usergroups');
        if (($user_groups === null) || (!is_array($user_groups))) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }
        if (count($user_groups) > 0) {
            foreach ($user_groups as $user_group_id => $value) {
                if ($value == '1') {
                    if (is_numeric($user_group_id)) {
                        $this->userGroups->delete($user_group_id);
                    }
                }
            }

        }
        $this->success();
    }

}
