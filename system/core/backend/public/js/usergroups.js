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

            var loadCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            var loadUserGroupCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            var loadModulesCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);

            var selectedModules = [];

            function removeErrorMessages() {
                $(".pixelmanager-error-container").empty();
            }

            function displayErrorMessage(message) {
                $(".pixelmanager-error-container").append('<div class="alert alert-danger">' + message + '</div>');
            }

            function checkSubmit() {
                removeErrorMessages();
                if ($.trim($("#name").val()) == '') {
                    displayErrorMessage(translate.get('Please enter a name'));
                    return (false);
                }
                if ($.trim($("#level").val()) == '') {
                    displayErrorMessage(translate.get('Please enter a level'));
                    return (false);
                }
                return (true);
            }

            function refresh() {
                parent.pixelmanagerGlobal.dataExchange.request(
                    translate.get('Loading user groups table'),
                    parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/usergroups/get',
                    {},
                    loadCallBackId,
                    window.frameElement.id,
                    $
                );
            }

            $("#" + loadCallBackId).on("success.pixelmanager", function (event, data) {
                $('#user-groups-table>tbody').html(data.html);
            });

            $("#" + loadUserGroupCallBackId).on("success.pixelmanager", function (event, data) {
                var i = 0;
                $('#action').val('update');
                $('#user-group-id').val(data.id);
                $('#name').val(data.name);
                $('#level').val(data.level);
                $('#action-create').prop('checked', (parseInt(data['action-create']) > 0));
                $('#action-edit').prop('checked', (parseInt(data['action-edit']) > 0));
                $('#action-publish').prop('checked', (parseInt(data['action-publish']) > 0));
                $('#action-delete').prop('checked', (parseInt(data['action-delete']) > 0));
                selectedModules = data.modules;
                loadModules();
            });

            var saveUserGroupCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $("#" + saveUserGroupCallBackId).on("success.pixelmanager", function (event, data) {
                if (data.userGroupSaved) {
                    $('#edit-user-group').one('hidden.bs.modal', function () {
                        refresh();
                    });
                    $('#edit-user-group').modal('hide');
                } else {
                    removeErrorMessages();
                    displayErrorMessage(translate.get('The user group data could not be saved'));
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

            $("#btn_add_new_user_group").click(function () {
                $('#action').val('create');
                $('#user-group-id').val('');
                $('#name').val('');
                $('#level').val('0');
                $('#action-create').prop('checked', false);
                $('#action-edit').prop('checked', false);
                $('#action-publish').prop('checked', false);
                $('#action-delete').prop('checked', false);
                selectedModules = [];
                loadModules();
            });

            $("#btn_edit_user_group_ok").click(function () {
                if (checkSubmit()) {
                    var url = 'update';
                    if ($('#action').val() == 'create') {
                        url = 'create';
                    }
                    parent.pixelmanagerGlobal.dataExchange.request(
                        translate.get('Saving user data'),
                        parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/usergroups/' + url,
                        $("#edit-user-group-form").serialize(),
                        saveUserGroupCallBackId,
                        window.frameElement.id,
                        $
                    );
                }
            });

            $("#btn_edit_user_group_cancel").click(function () {
                $('#edit-user-group').modal('hide');
            });

            $('#user-groups-table>tbody').on("click", "tr", function (event) {
                if (!$(event.target).is('input')) {
                    var parentElement = $(event.target).parents('#user-groups-table>tbody>tr');
                    if (parentElement.length > 0) {
                        var id = $(parentElement).attr('data-id');
                        parent.pixelmanagerGlobal.dataExchange.request(
                            translate.get('Loading user group data'),
                            parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/usergroups/getusergroup',
                            {'userGroupId': id},
                            loadUserGroupCallBackId,
                            window.frameElement.id,
                            $
                        );
                    }
                }
            });

            $('#edit-user-group').on('shown', function () {
                removeErrorMessages();
            });

            function countSelectedUserGroups() {
                var count = 0;
                $('#user-groups-table input[type="checkbox"]').each(function () {
                    if ($(this).is(':checked')) {
                        count++;
                    }
                });
                return (count);
            }

            $('#btn_delete_selected_user_groups').click(function () {
                if (countSelectedUserGroups() > 0) {
                    $('#delete-user-groups').modal({
                        keyboard: true,
                        backdrop: true
                    });
                }
            });

            $('#btn_delete_user_groups_ok').click(function () {
                $('#delete-user-groups').one('hidden.bs.modal', function () {
                    parent.pixelmanagerGlobal.dataExchange.request(
                        translate.get('Deleting the selected user groups'),
                        parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/usergroups/delete',
                        $("#user-groups-form").serialize(),
                        deleteCallBackId,
                        window.frameElement.id,
                        $
                    );
                });
                $('#delete-user-groups').modal('hide');
            });

            $("#btn_delete_user_groups_cancel").click(function () {
                $('#delete-user-groups').modal('hide');
            });


            function loadModules() {
                parent.pixelmanagerGlobal.dataExchange.request(
                    translate.get('Loading modules'),
                    parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/usergroups/getmodules',
                    {},
                    loadModulesCallBackId,
                    window.frameElement.id,
                    $
                );
            }

            $("#" + loadModulesCallBackId).on("success.pixelmanager", function (event, data) {
                $('#modules-table>tbody').html(data.html);
                for (var i = 0; i < selectedModules.length; i++) {
                    $('#modules-table #module_' + selectedModules[i]).prop('checked', true);
                }
                $('#edit-user-group').modal({
                    keyboard: true,
                    backdrop: 'static'
                });
            });


            refresh();
        });
    });
