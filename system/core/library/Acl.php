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

final class Acl
{

    const RESOURCE_SUPERUSER_ONLY = 0;
    const RESOURCE_ALL_USERS = 1;
    const RESOURCE_USER_WHITELIST = 2;

    const ACTION_CREATE = 0;
    const ACTION_EDIT = 1;
    const ACTION_PUBLISH = 2;
    const ACTION_DELETE = 3;

    const RESOURCE_GROUP_PAGES = 'pages';
    const RESOURCE_GROUP_SYSTEM = 'system';
    const RESOURCE_GROUP_MODULES = 'modules';

    private static $instance = null;
    private static $user_assigned_ugroups = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    public static function registerResource(
        $group,
        $resource_id,
        $description,
        $user_groups_mode = self::RESOURCE_SUPERUSER_ONLY,
        $overwrite_if_exists = false
    ) {
        $existing_resource = self::getResourceData($group, $resource_id);
        $new_data = array();
        $new_data["description"] = $description;
        $new_data["user-groups-mode"] = $user_groups_mode;
        if ($existing_resource === false) {
            $new_data["group"] = $group;
            $new_data["resource-id"] = $resource_id;
            $id = Db::insert("acl_resources", $new_data);
        } else {
            $id = $existing_resource["id"];
            if ($overwrite_if_exists) {
                Db::update("acl_resources", $new_data, "`id`=:id", array(':id' => $id));
            }
        }
        return ($id);
    }

    public static function updateResource($group, $resource_id, $properties)
    {
        $resource = self::getResourceData($group, $resource_id);
        if ($resource !== false) {
            return (self::updateResourceById($resource["id"], $properties));
        } else {
            return (false);
        }
    }

    public static function updateResourceById($id, $properties)
    {
        return Db::update(
            "acl_resources",
            $properties,
            "`id` = :id",
            array(':id' => $id)
        );
    }

    public static function getResourceData($group, $resource_id)
    {
        return Db::getFirst(
            "SELECT * FROM [prefix]acl_resources WHERE `group`=:group AND `resource-id`=:resourceId",
            array(
                ':group' => $group,
                ':resourceId' => $resource_id
            )
        );
    }

    public static function getResources()
    {
        return Db::get("SELECT * FROM [prefix]acl_resources WHERE 1", array());
    }

    public static function getResourcesFiltered($group = null, $user_groups_mode = null)
    {
        if (($group !== null) && ($user_groups_mode !== null)) {
            return Db::get(
                "SELECT * FROM [prefix]acl_resources WHERE `group`=:group AND `user-groups-mode`=:userGroupsMode",
                array(
                    ':group' => $group,
                    ':userGroupsMode' => $user_groups_mode
                )
            );
        } elseif (($group !== null) && ($user_groups_mode === null)) {
            return Db::get(
                "SELECT * FROM [prefix]acl_resources WHERE `group`=:group",
                array(
                    ':group' => $group
                )
            );
        } elseif (($group === null) && ($user_groups_mode !== null)) {
            return Db::get(
                "SELECT * FROM [prefix]acl_resources WHERE `user-groups-mode`=:userGroupsMode",
                array(
                    ':userGroupsMode' => $user_groups_mode
                )
            );
        } elseif (($group === null) && ($user_groups_mode === null)) {
            return (self::getResources());
        }
    }

    public static function getResourceDataById($id)
    {
        return Db::getFirst(
            "SELECT * FROM [prefix]acl_resources WHERE `id`=:id",
            array(
                ':id' => $id
            )
        );
    }

    public static function removeResource($group, $resource_id)
    {
        $resource = self::getResourceData($group, $resource_id);
        if ($resource !== false) {
            return (self::removeResourceById($resource["id"]));
        } else {
            return (true);
        }
    }

    public static function removeResourceById($id)
    {
        self::removeUserGroups($id);
        return Db::delete(
            "acl_resources",
            "`id`=:id",
            array(
                ':id' => $id
            )
        );
    }

    public static function assignUserGroups($group, $resource_id, $user_group_ids = array())
    {
        $resource = self::getResourceData($group, $resource_id);
        if ($resource !== false) {
            return (self::assignUserGroupsById($resource["id"], $user_group_ids));
        } else {
            return (false);
        }
    }

    protected static function removeUserGroups($id)
    {
        return Db::delete(
            "user_groups_to_acl_resources",
            "`acl-resource-id`=:aclResourceId",
            array(
                ":aclResourceId" => $id
            )
        );
    }

    protected static function assignUserGroup($id, $user_group_id)
    {
        $existing = Db::getFirst(
            "SELECT * FROM [prefix]user_groups_to_acl_resources WHERE `user-group-id`=:userGroupId AND `acl-resource-id`=:aclResourceId",
            array(
                ':userGroupId' => $user_group_id,
                ':aclResourceId' => $id
            )
        );
        if ($existing === false) {
            return Db::insert(
                "user_groups_to_acl_resources",
                array(
                    'user-group-id' => $user_group_id,
                    'acl-resource-id' => $id
                )
            );
        } else {
            return $existing["id"];
        }
    }

    public static function assignUserGroupsById($id, $user_group_ids = array())
    {
        $resource = self::getResourceDataById($id);
        if ($resource !== false) {
            if (is_array($user_group_ids)) {
                self::removeUserGroups($id);
                if (count($user_group_ids) > 0) {
                    foreach ($user_group_ids as $group_id) {
                        self::assignUserGroup($id, $group_id);
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

    public static function getUserGroups($group, $resource_id)
    {
        $resource = self::getResourceData($group, $resource_id);
        if ($resource !== false) {
            return (self::getUserGroupsById($resource["id"]));
        } else {
            return (false);
        }
    }

    public static function getUserGroupsById($id)
    {
        $res = Db::get("SELECT * FROM [prefix]user_groups_to_acl_resources WHERE `acl-resource-id`=:aclResourceId",
            array(':aclResourceId' => $id));
        $groups = array();
        if ($res !== false) {
            if (count($res) > 0) {
                foreach ($res as $row) {
                    $groups[] = $row['user-group-id'];
                }
            }
        }
        return ($groups);
    }

    public static function canAccess($group, $resource_id, $action)
    {
        $resource = self::getResourceData($group, $resource_id);
        if ($resource !== false) {
            return (self::canAccessById($resource["id"], $action));
        } else {
            return (false);
        }
    }

    protected static function isActionPermitted($action, $user_group_data)
    {
        switch ($action) {
            case Acl::ACTION_CREATE:
                return ($user_group_data['action-create'] > 0);
                break;
            case Acl::ACTION_EDIT:
                return ($user_group_data['action-edit'] > 0);
                break;
            case Acl::ACTION_PUBLISH:
                return ($user_group_data['action-publish'] > 0);
                break;
            case Acl::ACTION_DELETE:
                return ($user_group_data['action-delete'] > 0);
                break;
            default:
                return (false);
                break;
        }
    }

    public static function canAccessById($id, $action)
    {
        if (Auth::isAdmin()) {

            // Admins d�rfen immer alles, YEAH!
            return (true);

        } else {

            // I can haz?

            // Resource laden
            $resource = self::getResourceDataById($id);
            $resource_assigned_ugroup_ids = self::getUserGroupsById($id);

            // �berpr�fen, ob die Resource geladen werden konnte
            if (($resource === false) || ($resource_assigned_ugroup_ids === false)) {
                return (false);
            }

            // ggf. die Benutzer-Gruppen des aktuellen Benutzers laden und in statischer Veriable ablegen, damit sie nicht mehrfach geladen werden m�ssen
            if (self::$user_assigned_ugroups === null) {
                self::$user_assigned_ugroups = array();
                $result = Db::get('SELECT `user-id`, `user-group-id` FROM [prefix]users_to_user_groups WHERE `user-id`=:userId',
                    array(':userId' => Auth::getUserId()));
                if ($result !== false) {
                    if (count($result) > 0) {
                        foreach ($result as $row) {
                            $group_properties = Db::getFirst("SELECT * FROM [prefix]user_groups WHERE `id`=:id",
                                array(':id' => $row['user-group-id']));
                            if ($group_properties !== false) {
                                self::$user_assigned_ugroups[$row['user-group-id']] = $group_properties;
                            }
                        }
                    }
                }
            }

            // In Abh�ngigkeit der gew�hlten Einstellung den Zugriff ablehnen oder zulassen
            switch ($resource['user-groups-mode']) {

                // "Nur Administratoren d�rfen diese Resource bearbeiten"
                case Acl::RESOURCE_SUPERUSER_ONLY:
                    return (Auth::isAdmin());
                    break;

                // "Alle Benutzer..."
                case Acl::RESOURCE_ALL_USERS:
                    return (true);
                    break;

                // "Nur Benutzer in einer der folgenden Benutzergruppen..."
                case Acl::RESOURCE_USER_WHITELIST:
                    $result = false;

                    // Pr�fen, ob der Benutzer �berhaupt in irgendeiner Gruppe ist
                    // und der angeforderten Resource auch Benutzer-Gruppen zugeordnet sind...
                    if ((count(self::$user_assigned_ugroups) > 0) && (count($resource_assigned_ugroup_ids) > 0)) {

                        // Pr�fen, ob der Benutzer in einer dieser Resource zugewiesenen Benutzer-Gruppen ist...
                        foreach (self::$user_assigned_ugroups as $ugroup_id => $ugroup_properties) {
                            if (in_array($ugroup_id, $resource_assigned_ugroup_ids)) {

                                // Eine �berschneidung wurde gefunden, jetzt noch pr�fen, ob der gefundenen Gruppe die gew�nschte Aktion erlaubt ist...
                                $result = self::isActionPermitted($action, $ugroup_properties);

                                // Wenn eine Gruppe mit der gew�nschten Aktion gefunden wurde, abbrechen
                                if ($result) {
                                    break;
                                }
                            }
                        }

                        // Falls keine Gruppe gefunden werden konnte, die die Voraussetzungen erf�llt,
                        // alle Gruppen, die ein h�heres Level als die explizit zugewiesenen aufweisen, pr�fen
                        if (!$result) {

                            // Dazu m�ssen wir erstmal das niedrigste Level der der Resource zugewiesenen Benutzergruppen herausfinden
                            $min_level = PHP_INT_MAX;
                            $ugroup_lowest_level = Db::getFirst(
                                "
											SELECT
												[prefix]user_groups.*
											FROM
												[prefix]user_groups
											JOIN
												[prefix]user_groups_to_acl_resources
											ON
												[prefix]user_groups.`id` = [prefix]user_groups_to_acl_resources.`user-group-id`
											WHERE
												[prefix]user_groups_to_acl_resources.`acl-resource-id` = :aclResourceId
											ORDER BY
												[prefix]user_groups.`level` ASC
											LIMIT
												0,1
										",
                                array(':aclResourceId' => $id)
                            );
                            if ($ugroup_lowest_level !== false) {
                                $min_level = $ugroup_lowest_level['level'];
                            }

                            // Dann gehen wir alle dem Benutzer zugeordnete Gruppen durch und suchen alle,
                            // die ein h�heres Level haben und die gew�nschte Aktion durchf�hren d�rfen
                            foreach (self::$user_assigned_ugroups as $ugroup_id => $ugroup_properties) {
                                if ($ugroup_properties['level'] > $min_level) {

                                    $result = self::isActionPermitted($action, $ugroup_properties);

                                    // Wenn eine Gruppe mit der gew�nschten Aktion gefunden wurde, abbrechen
                                    if ($result) {
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    return ($result);
                    break;

                // Zur Sicherheit...
                default:
                    return (false);
                    break;

            }

            return (false);
        }
    }

}
