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

class UserGroups
{
    const SORT_ORDER_LEVEL = 0;
    const SORT_ORDER_NAME = 1;

    public function getById($id)
    {
        return Db::getFirst(
            "SELECT * FROM [prefix]user_groups WHERE `id`=:id",
            array(
                ':id' => $id
            )
        );
    }

    public function getAll($sortorder = self::SORT_ORDER_LEVEL)
    {
        if ($sortorder == self::SORT_ORDER_NAME) {
            $order_by = "ORDER BY `name` ASC, `level` ASC";
        } else {
            $order_by = "ORDER BY `level` ASC, `name` ASC";
        }
        return Db::get("SELECT * FROM [prefix]user_groups " . $order_by, array());
    }

    public function add($name, $level, $action_create = 0, $action_edit = 0, $action_publish = 0, $action_delete = 0)
    {
        return Db::insert(
            "user_groups",
            array(
                'name' => trim($name),
                'level' => $level,
                'action-create' => $action_create,
                'action-edit' => $action_edit,
                'action-publish' => $action_publish,
                'action-delete' => $action_delete
            )
        );
    }

    public function delete($id)
    {
        Db::delete(
            "user_groups_to_acl_resources",
            "`user-group-id`=:userGroupId",
            array(
                ":userGroupId" => $id
            )
        );
        Db::delete(
            "users_to_user_groups",
            "`user-group-id`=:userGroupId",
            array(
                ":userGroupId" => $id
            )
        );
        return Db::delete(
            "user_groups",
            "`id`=:id",
            array(
                ':id' => $id
            )
        );
    }

    public function update($id, $properties)
    {
        return Db::update(
            "user_groups",
            $properties,
            "`id` = :id",
            array(':id' => $id)
        );
    }

    protected function removeAclResources($id)
    {
        return Db::delete(
            "user_groups_to_acl_resources",
            "`user-group-id`=:userGroupId",
            array(
                ":userGroupId" => $id
            )
        );
    }

    protected function assignAclResource($id, $acl_resource_id)
    {
        $existing = Db::getFirst(
            "SELECT * FROM [prefix]user_groups_to_acl_resources WHERE `user-group-id`=:userGroupId AND `acl-resource-id`=:aclResourceId",
            array(
                ':userGroupId' => $id,
                ':aclResourceId' => $acl_resource_id
            )
        );
        if ($existing === false) {
            return Db::insert(
                "user_groups_to_acl_resources",
                array(
                    'user-group-id' => $id,
                    'acl-resource-id' => $acl_resource_id
                )
            );
        } else {
            return $existing["id"];
        }
    }

    public function assignAclResources($id, $acl_resources = array())
    {
        $user_group = self::getById($id);
        if ($user_group !== false) {
            if (is_array($acl_resources)) {
                self::removeAclResources($id);
                if (count($acl_resources) > 0) {
                    foreach ($acl_resources as $acl_resource_id) {
                        self::assignAclResource($id, $acl_resource_id);
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

    public function getAclResources($id)
    {
        $res = Db::get("SELECT * FROM [prefix]user_groups_to_acl_resources WHERE `user-group-id`=:userGroupId",
            array(':userGroupId' => $id));
        $groups = array();
        if ($res !== false) {
            if (count($res) > 0) {
                foreach ($res as $row) {
                    $groups[] = $row['acl-resource-id'];
                }
            }
        }
        return ($groups);
    }

}
