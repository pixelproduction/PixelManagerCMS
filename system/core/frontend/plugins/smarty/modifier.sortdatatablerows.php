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

function smarty_modifier_sortdatatablerows($array, $column_id = '', $sort_order = 'asc')
{
    if ($column_id != '') {
        $sort_order = strtolower(trim($sort_order));
        if ($sort_order != 'desc') {
            $sort_order = 'asc';
        }
        usort($array, function ($a, $b) use ($column_id, $sort_order) {
            if (isset($a[$column_id]) && isset($b[$column_id])) {
                $result = strnatcasecmp(utf8_decode($a[$column_id]), utf8_decode($b[$column_id]));
                if ($sort_order == 'desc') {
                    $result = $result * (-1);
                }
                return ($result);
            } else {
                return (0);
            }
        });
    }
    return ($array);
}
