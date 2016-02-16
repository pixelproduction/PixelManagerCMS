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

// Hier kann man Funktionen unterbringen, die in mehreren Controllern benötigt werden,
// aber nicht so allgemeingültig sind, dass es Sinn machen würde, sie in die Basisklassen
// Controller, DataExchangeController oder HtmlOutputController zu übernehmen...

class ControllerHelpers
{

    var $pages = null;

    public function __construct()
    {
        $this->pages = new Pages();
    }

    public function canAccessPage($id, $action)
    {
        $acl = Acl::getResourceData(Acl::RESOURCE_GROUP_PAGES, $id);
        if ($acl !== false) {
            return (Acl::canAccess(Acl::RESOURCE_GROUP_PAGES, $id, $action));
        } else {
            $finished = false;
            $ret = false;
            $next_id = $id;
            $safety_counter = 0;
            do {
                if ($next_id == Pages::ROOT_ID) {
                    $ret = Acl::canAccess(Acl::RESOURCE_GROUP_PAGES, Pages::ROOT_ID, $action);
                    $finished = true;
                } else {
                    $res = $this->pages->getProperties($next_id);
                    if ($res !== false) {
                        $acl = Acl::getResourceData(Acl::RESOURCE_GROUP_PAGES, $next_id);
                        if ($acl !== false) {
                            $ret = Acl::canAccess(Acl::RESOURCE_GROUP_PAGES, $next_id, $action);
                            $finished = true;
                        }
                        $next_id = $res['parent-id'];
                    } else {
                        $finished = true;
                    }
                }
                $safety_counter++;
            } while ((!$finished) && ($safety_counter < 50));
            return ($ret);
        }
    }

    public function canAccessAllElements($elements, $action, $resursive = false)
    {
        foreach ($elements as $element) {
            if ($resursive) {
                $children = $this->pages->getChildren($element['id'], false);
                if ($children !== false) {
                    if (count($children) > 0) {
                        if (!$this->canAccessAllElements($children, $action, true)) {
                            return (false);
                        }
                    }
                }
            }
            $result = $this->canAccessPage($element['id'], $action);
            if ($result == false) {
                return (false);
            }
        }
        return (true);
    }

}
