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
        "plugins/jquery-ui-bootstrap-no-conflict",
        "modules/fixmodals"
    ],
    function ($, translate) {

        $(function () {

            var selectedUserGroups = [];

            function removeErrorMessages() {
                $(".pixelmanager-error-container").empty();
            }

            function displayErrorMessage(message) {
                $(".pixelmanager-error-container").append('<div class="alert alert-danger">' + message + '</div>');
                $('#edit-user .modal-body').scrollTop(0);
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
                if ($('#action').val() == 'create') {
                    if ($.trim($("#password").val()) == '') {
                        displayErrorMessage(translate.get('Please enter a password'));
                        return (false);
                    }
                }
                return (true);
            }

            function refresh() {
                parent.pixelmanagerGlobal.dataExchange.request(
                    translate.get('Loading user table'),
                    parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/users/get',
                    {},
                    loadCallBackId,
                    window.frameElement.id,
                    $
                );
            }

            function loadUserGroups() {
                parent.pixelmanagerGlobal.dataExchange.request(
                    translate.get('Loading user groups'),
                    parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/users/getusergroups',
                    {},
                    loadUserGroupsCallBackId,
                    window.frameElement.id,
                    $
                );
            }

            var loadCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $("#" + loadCallBackId).on("success.pixelmanager", function (event, data) {
                $('#user-table>tbody').html(data.html);
            });

            var loadUserGroupsCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $("#" + loadUserGroupsCallBackId).on("success.pixelmanager", function (event, data) {
                $('#user-groups-table>tbody').html(data.html);
                for (var i = 0; i < selectedUserGroups.length; i++) {
                    $('#user-groups-table #usergroup_' + selectedUserGroups[i]).prop('checked', true);
                }
                $('#edit-user').modal({
                    keyboard: true,
                    backdrop: 'static'
                });
            });

            var loadUserCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $("#" + loadUserCallBackId).on("success.pixelmanager", function (event, data) {
                $('#action').val('update');
                $('#user-id').val(data.id);
                $('#screenname').val(data.screenname);
                $('#login').val(data.login);
                $('#password').val('');
                $('#privileges').val(data.privileges);
                $('#password-notice').show();
                selectedUserGroups = data.userGroups;
                loadUserGroups();
            });

            var saveUserCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $("#" + saveUserCallBackId).on("success.pixelmanager", function (event, data) {
                if (data.userSaved) {
                    $('#edit-user').one('hidden.bs.modal', function () {
                        refresh();
                    });
                    $('#edit-user').modal('hide');
                } else {
                    removeErrorMessages();
                    if (data.loginAlreadyExists) {
                        displayErrorMessage(translate.get('The login is already in use. Please use a different one.'));
                    } else {
                        displayErrorMessage(translate.get('The user-data could not be saved'));
                    }
                }
            });

            var deleteCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $("#" + deleteCallBackId).on("success.pixelmanager", function (event, data) {
                refresh();
            });

            $("#btn_close").click(function () {
                parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
            });

            $("#btn_refresh").click(function () {
                refresh();
            });

            $("#btn_add_new_user").click(function () {
                $('#action').val('create');
                $('#user-id').val('');
                $('#screenname').val('');
                $('#login').val('');
                $('#password').val('');
                $('#privileges').val('0');
                $('#password-notice').hide();
                selectedUserGroups = [];
                loadUserGroups();
            });

            $("#btn_edit_user_ok").click(function () {
                if (checkSubmit()) {
                    var url = 'update';
                    if ($('#action').val() == 'create') {
                        url = 'create';
                    }
                    parent.pixelmanagerGlobal.dataExchange.request(
                        translate.get('Saving user data'),
                        parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/users/' + url,
                        $("#edit-user-form").serialize(),
                        saveUserCallBackId,
                        window.frameElement.id,
                        $
                    );
                }
            });

            $("#btn_edit_user_cancel").click(function () {
                $('#edit-user').modal('hide');
            });

            $('#user-table>tbody').on("click", "tr", function (event) {
                if (!$(event.target).is('input')) {
                    var parentElement = $(event.target).parents('#user-table>tbody>tr');
                    if (parentElement.length > 0) {
                        var id = $(parentElement).attr('data-id');
                        parent.pixelmanagerGlobal.dataExchange.request(
                            translate.get('Loading user data'),
                            parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/users/getuser',
                            {'userId': id},
                            loadUserCallBackId,
                            window.frameElement.id,
                            $
                        );
                    }
                }
            });

            $('#edit-user').on('shown', function () {
                removeErrorMessages();
                $('#edit-user .modal-body').scrollTop(0);
            });

            function countSelectedUsers() {
                var count = 0;
                $('#user-table input[type="checkbox"]').each(function () {
                    if ($(this).is(':checked')) {
                        count++;
                    }
                });
                return (count);
            }

            $('#btn_delete_selected_users').click(function () {
                if (countSelectedUsers() > 0) {
                    $('#delete-users').modal({
                        keyboard: true,
                        backdrop: true
                    });
                }
            });

            $('#btn_delete_users_ok').click(function () {
                $('#delete-users').one('hidden.bs.modal', function () {
                    parent.pixelmanagerGlobal.dataExchange.request(
                        translate.get('Deleting the selected users'),
                        parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/users/delete',
                        $("#users-form").serialize(),
                        deleteCallBackId,
                        window.frameElement.id,
                        $
                    );
                });
                $('#delete-users').modal('hide');
            });

            $("#btn_delete_users_cancel").click(function () {
                $('#delete-users').modal('hide');
            });

            refresh();
        });
    });
