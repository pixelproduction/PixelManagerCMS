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

'use strict';

require(
    [
        "jquery",
        "modules/translate",
        "plugins/placeholder"
    ],
    function ($, translate) {

        // jQuery-Code für die Seite "login"
        $(function () {

            $('input').placeholder();

            // Wenn sich die Seite in einem iFrame befindet, dann muss der HTML-Code noch angepasst werden
            if (parent.location.href != location.href) {
                // Wenn das gesetzt ist, befinden wir uns in einem iframe in einem Pixelmanager-Tab
                if (typeof(parent.pixelmanagerGlobal) != "undefined") {
                    // Das bedeutet, dass höchstwahrscheinlich die Session abgelaufen ist und deshalb das
                    // Login-Fenster erscheint. Der Benutzer soll sich wieder einloggen können,
                    // aber er soll den verwendeten Account nicht wechseln können, daher deaktivieren
                    // wir das Login-Feld und erzeugen ein Hidden-Field mit dem Login-Namen als Wert...
                    $(".pixelmanager-login-container").css("backgroundColor", "#fff");
                    $("#login").val(parent.pixelmanagerGlobal.userLoginName).attr('disabled', true);
                    $("#login").attr('name', 'login-deactivated');
                    $("#login-form").append('<input type="hidden" name="login" id="login-hidden" value="' + parent.pixelmanagerGlobal.userLoginName + '">');
                    $("#language-container").remove();
                    $(".pixelmanager-login-container form").append('<input type="hidden" name="language" value="' + parent.pixelmanagerGlobal.backendLanguage + '">');
                }
            }

        });
    });
