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

interface FrontendRouterInterface
{

    // Führt das Routing aus, das Ergebnis sollte gespeichert werden
    // Rückgabewert: Boolean
    public function route();

    // Gibt die ID der gefunden Seite zurück, wurde keine gefunden,
    // sollte FALSE zurückgegeben werden
    // Rückgabewert: Integer
    public function getPageId();

    // Gibt die ID der gefunden Sprache zurück, wurde keine gefunden,
    // sollte die ID der Standard-Sprache zurückgegeben werden
    // Rückgabewert: String
    public function getLanguageId();

    // Gibt TRUE oder FALSE zurück, je nachdem, ob eine Seite gefunden wurde, oder nicht
    // Rückgabewert: Boolean
    public function pageFound();

    // Gibt die ID der 404-Seite zurück, oder FALSE, wenn keine existiert
    // Rückgabewert: Integer
    public function getErrorPageId();

    // Gibt die zusätzlichen Parameter als assoziatives Array zurück,
    // z.B. den Teil des Request-Pfads, der nicht benötigt wurde, um die Seite zu finden,
    // oder ein leeres Array, wenn keine Parameter existieren
    // Rückgabewert: Array
    public function getParameter();

    // Gibt TRUE oder FALSE zurück, je nachdem, ob der Vorschau-Modus gewünscht ist
    // Rückgabewert: Boolean
    public function isPreview();

    // Gibt TRUE oder FALSE zurück, je nachdem, ob die Seite ein Link / eine Weiterleitung ist
    // Rückgabewert: Boolean
    public function isPageLink();

    // Gibt die URL der gewünschten Weiterleitung zurück
    // Rückgabewert: String
    public function getPageLinkUrl();

    // Gibt zurück, ob der Link in einem neuen Fenster geöffnert werden soll
    // Rückgabewert: Boolean
    public function getPageLinkNewWindow();

}
