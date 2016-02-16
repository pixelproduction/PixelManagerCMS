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

interface FrontendModuleInterface
{
    // Von jedem Modul wird NUR EINE Instanz erzeugt
    // daher ist es möglich und geplant, dass die Funktion init()
    // Daten lädt / Variablen initialisiert, die dann von output()
    // werwendet werden können.

    // Wird aufgerufen, um das Modul zu initialisieren,
    // es wird kein Rückgabewert erwartet.
    // Diese Funktion wird NUR EINMAL aufgerufen, auch wenn
    // das Modul mehrfach in einem Template ausgerufen wird
    // $config ist ein assoziatives Array mit den Konfigurationsdaten für dieses Modul
    public function init($config);

    // Wird für jede Verwendung im Template einmal aufgerufen,
    // erwartet einen String als Rückgabewert.
    // $params enthält die Parameter, die im Template angegeben wurden
    // $smarty enthält eine Referenz auf das Smarty-Objekt
    public function output($params, $smarty);

}
