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
                $('#settings-tab a[href="#tab-root"]').tab('show');
                $('.pixelmanager-iframe-content').scrollTop(0);
            }

            function checkSubmit() {
                removeErrorMessages();
                var batchEdit = $('input[name="batchEdit"]');
                if (batchEdit.length == 0) {
                    var captionInAnyLanguage = false;
                    var key = null;
                    if ($.trim($("#name").val()) == '') {
                        displayErrorMessage(translate.get('The URL can not be empty'));
                        return (false);
                    }
                    for (key in parent.pixelmanagerGlobal.languages) {
                        if ($.trim($("#caption_" + key).val()) != '') {
                            captionInAnyLanguage = true;
                        }
                    }
                    if (!captionInAnyLanguage) {
                        displayErrorMessage(translate.get('Please provide a caption (at least in one language)'));
                        return (false);
                    }
                }
                return (true);
            }

            $('#settings-tab a').click(function (e) {
                e.preventDefault();
                $(this).tab('show');
            })

            var callBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);

            $("#" + callBackId).on("success.pixelmanager", function (event, data) {
                var closeTab = true;
                if (data.validAliases == false) {
                    displayErrorMessage(translate.get('The alias URL for the language: ') + parent.pixelmanagerGlobal.languages[data.offendingAliasLanguageId] + translate.get(' is invalid. Please use only alphabetic characters (a-z),  numerics (0-9), the underscore (_) or the hyphen (-).'));
                    closeTab = false;
                } else {
                    if (data.aliasAlreadyExists == true) {
                        displayErrorMessage(translate.get('An other page uses the alias URL for the language: ') + parent.pixelmanagerGlobal.languages[data.offendingAliasLanguageId] + translate.get(' already. Please change it.'));
                        closeTab = false;
                    } else {
                        if (data.validName == false) {
                            displayErrorMessage(translate.get('The URL is invalid. Please use only alphabetic characters (a-z),  numerics (0-9), the underscore (_) or the hyphen (-).'));
                            closeTab = false;
                        } else {
                            if (data.nameAlreadyExists == true) {
                                displayErrorMessage(translate.get('An other page uses this URL already. Please change it.'));
                                closeTab = false;
                            }
                        }
                    }
                }
                if (closeTab) {
                    parent.pixelmanagerGlobal.pagetree.refresh();
                    parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
                }
            });

            $("#" + callBackId).on("fail.pixelmanager", function () {
                if ($('input[name="action"]').val() == 'edit') {
                    displayErrorMessage(translate.get('The properties could not be saved'));
                } else {
                    displayErrorMessage(translate.get('The page could not be created'));
                }
            });

            function updateLinkPropertiesVisibility() {
                if ($('#template-id').val() == 'NULL') {
                    $('#link-properties').show();
                } else {
                    $('#link-properties').hide();
                }
                if ($('#link-translated').is(':checked')) {
                    $('.pixelmanager-link-properties-language-specific-url').show();
                    $('.pixelmanager-link-properties-url').hide()
                } else {
                    $('.pixelmanager-link-properties-language-specific-url').hide();
                    $('.pixelmanager-link-properties-url').show()
                }
            }

            $('#template-id').change(function (e) {
                if ($(this).hasClass('edit-template-id')) {
                    var oldValue = $(this).attr('data-saved-value');
                    var newValue = $(this).val();
                    if (oldValue != newValue) {
                        var answer = confirm(translate.get('Caution: If you change the template all content of the page is lost. You will start with a blank, new page again. Are you sure that you want to change the template?'));
                        if (answer != true) {
                            $(this).val(oldValue);
                        }
                    }
                }
                updateLinkPropertiesVisibility();
            });

            $('#link-translated').click(function (e) {
                updateLinkPropertiesVisibility();
            });

            $('.btn-get-link-to-page').click(function (event) {
                var languageId = $(this).attr('data-language-id');

                function linkSelectorCallback(url) {
                    if (languageId != '') {
                        $('#translated-link-urls-' + languageId).val('link://' + url);
                    } else {
                        $('#link-url').val('link://' + url);
                    }
                }

                parent.pixelmanagerGlobal.openLinkSelector(linkSelectorCallback, false);
            });

            $('.btn-get-download').click(function (event) {
                var languageId = $(this).attr('data-language-id');

                function downloadSelectorCallback(file) {
                    var relativePath = file.url;
                    relativePath = relativePath.substr(file.baseUrl.length, (file.url.length - file.baseUrl.length));
                    if (languageId != '') {
                        $('#translated-link-urls-' + languageId).val('download://' + relativePath);
                    } else {
                        $('#link-url').val('download://' + relativePath);
                    }
                }

                parent.pixelmanagerGlobal.openDownloadSelector(downloadSelectorCallback, false);
            });

            $("#btn_save").click(function () {
                if (checkSubmit()) {
                    if ($('input[name="action"]').val() == 'edit') {
                        parent.pixelmanagerGlobal.dataExchange.request(
                            translate.get('Saving page properties'),
                            parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagetree/edit',
                            $("form").serialize(),
                            callBackId,
                            window.frameElement.id,
                            $
                        );
                    } else {
                        parent.pixelmanagerGlobal.dataExchange.request(
                            translate.get('Add new page'),
                            parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagetree/create',
                            $("form").serialize(),
                            callBackId,
                            window.frameElement.id,
                            $
                        );
                    }
                }
            });

            $("#btn_close").click(function () {
                parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
            });

            updateLinkPropertiesVisibility();

        });
    });
