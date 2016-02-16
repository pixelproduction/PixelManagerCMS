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

class Users
{
    public function getById($id)
    {
        return Db::getFirst(
            "SELECT * FROM [prefix]users WHERE `id`=:id",
            array(
                ':id' => $id
            )
        );
    }

    public function getAll()
    {
        return Db::get(
            "SELECT * FROM [prefix]users ORDER by `screenname` ASC",
            array()
        );
    }

    public function add($screenname, $login, $password, $privileges = Auth::PRIVILEGES_USER)
    {
        if ((trim($login) != '') && (trim($password) != '')) {
            if (!self::loginExists($login)) {
                return Db::insert(
                    "users",
                    array(
                        'screenname' => $screenname,
                        'login' => trim($login),
                        'password' => Auth::getPasswordHash()->HashPassword(trim($password)),
                        'privileges' => $privileges
                    )
                );
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    public function delete($id)
    {
        $this->removeUserGroups($id);
        return Db::delete(
            "users",
            "`id`=:id",
            array(
                ':id' => $id
            )
        );
    }

    public function update($id, $properties)
    {
        foreach ($properties as $key => $value) {
            if ($key == 'login') {
                if (trim($value) == '') {
                    return (false);
                }
                if ($this->loginExists($value, $id)) {
                    return (false);
                }
            }
            if ($key == 'password') {
                if (trim($value) == '') {
                    return (false);
                }
                $properties[$key] = Auth::getPasswordHash()->HashPassword(trim($value));
            }
        }
        return Db::update(
            "users",
            $properties,
            "`id` = :id",
            array(':id' => $id)
        );
    }

    public function loginExists($login, $self_id = null)
    {
        if ($self_id == null) {
            $found = Db::getFirst(
                "SELECT * FROM [prefix]users WHERE `login`=:login",
                array(
                    ':login' => trim($login)
                )
            );
        } else {
            $found = Db::getFirst(
                "SELECT * FROM [prefix]users WHERE `login`=:login AND `id` <> :id",
                array(
                    ':login' => trim($login),
                    ':id' => $self_id
                )
            );
        }
        return ($found !== false);
    }

    public function assignUserGroups($id, $user_group_ids = array())
    {
        $group = $this->getById($id);
        if ($group !== false) {
            if (is_array($user_group_ids)) {
                $this->removeUserGroups($id);
                if (count($user_group_ids) > 0) {
                    foreach ($user_group_ids as $group_id) {
                        $this->assignUserGroup($id, $group_id);
                    }
                }
                return (true);
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    public function getUserGroups($id)
    {
        $user_groups = array();
        $result = Db::get('SELECT `user-id`, `user-group-id` FROM [prefix]users_to_user_groups WHERE `user-id`=:userId',
            array(':userId' => $id));
        if ($result !== false) {
            if (count($result) > 0) {
                foreach ($result as $row) {
                    $user_groups[] = $row['user-group-id'];
                }
            }
        }
        return ($user_groups);
    }

    protected function removeUserGroups($id)
    {
        return Db::delete(
            "users_to_user_groups",
            "`user-id`=:userId",
            array(
                ":userId" => (string)$id
            )
        );
    }

    protected function assignUserGroup($id, $user_group_id)
    {
        $existing = Db::getFirst(
            "SELECT * FROM [prefix]users_to_user_groups WHERE `user-group-id`=:userGroupId AND `user-id`=:userId",
            array(
                ':userGroupId' => $user_group_id,
                ':userId' => $id
            )
        );
        if ($existing === false) {
            return Db::insert(
                "users_to_user_groups",
                array(
                    'user-group-id' => $user_group_id,
                    'user-id' => $id
                )
            );
        } else {
            return $existing["id"];
        }
    }

}
