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

interface PluginInterface
{
    /*
        Muss ein Array zurückgeben, das der folgenden Form entspricht:

        array(
            array(
                'hookId' => [Identifikation des gewünschten Hooks] siehe Konstanten in /system/core/library/Plugin.php,
                'methodName' => [Name der auszuführenden Klassen-Methode]
            ),
            array( ... )
        )

    */
    public function register();

    /*

        Die Klassen-Methoden, die als Callback für die Hooks dienen, müssen folgendem Schema entsprechen:
        public function [name] ($parameters, &$data)

        das & vor $data ist wichtig! Denn so kann man die Daten manipulieren, wenn nötig.

    */
}
