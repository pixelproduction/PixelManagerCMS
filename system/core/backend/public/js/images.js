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
        "plugins/jquery-ui",
        "elfinder",
        "elfinder-i18n-de"
    ],
    function ($, translate) {

        $(function () {

            var myCommands = elFinder.prototype._options.commands;
            var disabled = ['mkfile', 'edit'];
            $.each(disabled, function (i, cmd) {
                var idx;
                (idx = $.inArray(cmd, myCommands)) !== -1 && myCommands.splice(idx, 1);
            });
            var elf = $('#elfinder').elfinder({
                lang: parent.pixelmanagerGlobal.backendLanguage,
                url: parent.pixelmanagerGlobal.baseUrl + 'admin/custom/elfinderimagesconnector',
                resizable: false,
                defaultView: 'icons',
                uiOptions: {
                    cwd: {
                        oldSchool: true
                    }
                },
                cookie: {
                    expires: 0
                }
            });

            $(window).resize(function (e) {
                var win_height = $('.pixelmanager-iframe-content').height();
                if (elf.height() != win_height) {
                    elf.height(win_height).resize();
                }
            });
            $(window).resize();

        });
    });
