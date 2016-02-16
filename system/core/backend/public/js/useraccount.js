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
        "plugins/jquery-ui-bootstrap-no-conflict"
    ],
    function ($, translate) {

        $(function () {

            function removeErrorMessages() {
                $(".pixelmanager-error-container").empty();
            }

            function displayErrorMessage(message) {
                $(".pixelmanager-error-container").append('<div class="alert alert-danger">' + message + '</div>');
            }

            function checkSubmit() {
                removeErrorMessages();
                if ($.trim($("#screenname").val()) == '') {
                    displayErrorMessage(translate.get('Please enter a name'));
                    return (false);
                }
                if ($.trim($("#login").val()) == '') {
                    displayErrorMessage(translate.get('Please enter a login-name'));
                    return (false);
                }
                return (true);
            }

            var updateAccountCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $("#" + updateAccountCallBackId).on("success.pixelmanager", function (event, data) {
                removeErrorMessages();
                if (!data.accountUpdated) {
                    if (data.loginAlreadyExists) {
                        displayErrorMessage(translate.get('The login is already in use. Please use a different one.'));
                    } else {
                        displayErrorMessage(translate.get('The user-data could not be saved'));
                    }
                } else {
                    parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
                }
            });

            $("#btn_save").click(function () {
                if (checkSubmit()) {
                    parent.pixelmanagerGlobal.dataExchange.request(
                        translate.get('Saving user account'),
                        parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/useraccount/update',
                        $("form").serialize(),
                        updateAccountCallBackId,
                        window.frameElement.id,
                        $
                    );
                }
            });

            $("#btn_close").click(function () {
                parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
            });

        });
    });
