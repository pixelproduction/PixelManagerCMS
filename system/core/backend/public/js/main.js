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

// ****************************************************************
// Das Modul für die Seite "html-output/main/"
// ****************************************************************

'use strict';

require(
    [
        "jquery",
        "modules/translate",
        "modules/data-exchange",
        "modules/tabs",
        "modules/pagetree",
        "modules/backend-modules",
        "modules/modal-popup",
        "plugins/jquery-ui-bootstrap-no-conflict",
        "plugins/hotkeys",
        "plugins/cookie",
        "plugins/jstree",
        "modules/fixmodals",
    ],
    function ($, translate, dataExchange, tabs, pagetree, backendModules, modalPopup) {

        // ****************************************************************
        // Das globale Objekt mit Daten füllen, auf die dann alle iFrames zugreifen können über parent.pixelmanagerGlobal.[...]
        // ****************************************************************

        pixelmanagerGlobal.dataExchange = dataExchange;
        pixelmanagerGlobal.tabs = tabs;
        pixelmanagerGlobal.pagetree = pagetree;
        pixelmanagerGlobal.backendModules = backendModules;
        pixelmanagerGlobal.modalPopup = modalPopup;
        pixelmanagerGlobal.translate = translate;
        pixelmanagerGlobal.activeSecondaryLanguage = null;

        var updateLanguageSwitcherHtml = function () {
            var languageCaption = pixelmanagerGlobal.languages[pixelmanagerGlobal.activeLanguage];
            $('.navbar .main-language input[type="radio"]').prop('checked', false);
            $('.navbar .main-language input[name="rdo_language_' + pixelmanagerGlobal.activeLanguage + '"]').prop('checked', true);
            $('.navbar .secondary-language').show();
            $('.navbar .secondary-language input[type="radio"]').prop('checked', false);
            $('.navbar #btn_secondary_language_' + pixelmanagerGlobal.activeLanguage).parents('li').first().hide();
            if (pixelmanagerGlobal.activeSecondaryLanguage != null) {
                $('.navbar .secondary-language input[name="rdo_secondary_language_' + pixelmanagerGlobal.activeSecondaryLanguage + '"]').prop('checked', true);
                languageCaption = languageCaption + ' / ' + pixelmanagerGlobal.languages[pixelmanagerGlobal.activeSecondaryLanguage];
            } else {
                $('.navbar .secondary-language input[name="rdo_secondary_language_none"]').prop('checked', true);
            }
            $(".navbar #active-language-name").html(languageCaption);
        }
        updateLanguageSwitcherHtml();

        pixelmanagerGlobal.switchLanguage = function (languageId, secondaryLanguageId) {
            if (typeof(secondaryLanguageId) != 'undefined') {
                if (secondaryLanguageId != null) {
                    if (typeof(pixelmanagerGlobal.languages[secondaryLanguageId]) == "undefined") {
                        secondaryLanguageId = null;
                    }
                }
            } else {
                secondaryLanguageId = null;
            }
            if (typeof(pixelmanagerGlobal.languages[languageId]) != "undefined") {
                if (languageId == secondaryLanguageId) {
                    secondaryLanguageId = null;
                }
                pixelmanagerGlobal.activeLanguage = languageId;
                pixelmanagerGlobal.activeSecondaryLanguage = secondaryLanguageId;
                updateLanguageSwitcherHtml();
                pixelmanagerGlobal.tabs.broadcast(
                    "switchlanguage.pixelmanager",
                    {
                        'languageId': languageId,
                        'secondaryLanguageId': secondaryLanguageId
                    }
                );
                pixelmanagerGlobal.pagetree.setLanguage(languageId);
            }
        }

        pixelmanagerGlobal.fileSelectorCallback = null;
        pixelmanagerGlobal.fileSelectorMultiSelect = false;
        pixelmanagerGlobal.fileSelectorStartPath = null;
        pixelmanagerGlobal.openImageSelector = function (callbackFunction, multiSelect, startPath) {
            pixelmanagerGlobal.fileSelectorCallback = callbackFunction;
            if (typeof(multiSelect) != 'undefined') {
                pixelmanagerGlobal.fileSelectorMultiSelect = multiSelect;
            } else {
                pixelmanagerGlobal.fileSelectorMultiSelect = false;
            }
            if (typeof(startPath) != 'undefined') {
                pixelmanagerGlobal.fileSelectorStartPath = startPath;
            } else {
                pixelmanagerGlobal.fileSelectorStartPath = null;
            }
            pixelmanagerGlobal.modalPopup.open(pixelmanagerGlobal.baseUrl + 'admin/html-output/imageselector');
        }
        pixelmanagerGlobal.closeImageSelector = function () {
            pixelmanagerGlobal.modalPopup.close();
        }

        pixelmanagerGlobal.openDownloadSelector = function (callbackFunction, multiSelect, startPath) {
            pixelmanagerGlobal.fileSelectorCallback = callbackFunction;
            if (typeof(multiSelect) != 'undefined') {
                pixelmanagerGlobal.fileSelectorMultiSelect = multiSelect;
            } else {
                pixelmanagerGlobal.fileSelectorMultiSelect = false;
            }
            if (typeof(startPath) != 'undefined') {
                pixelmanagerGlobal.fileSelectorStartPath = startPath;
            } else {
                pixelmanagerGlobal.fileSelectorStartPath = null;
            }
            pixelmanagerGlobal.modalPopup.open(pixelmanagerGlobal.baseUrl + 'admin/html-output/downloadselector');
        }
        pixelmanagerGlobal.closeDownloadSelector = function () {
            pixelmanagerGlobal.modalPopup.close();
        }

        pixelmanagerGlobal.linkSelectorCallback = null;
        pixelmanagerGlobal.openLinkSelector = function (callbackFunction) {
            pixelmanagerGlobal.linkSelectorCallback = callbackFunction;
            pixelmanagerGlobal.modalPopup.open(pixelmanagerGlobal.baseUrl + 'admin/html-output/linkselector');
        }
        pixelmanagerGlobal.closeLinkSelector = function () {
            pixelmanagerGlobal.modalPopup.close();
        }

        pixelmanagerGlobal.openPagePreview = function (url) {
            pixelmanagerGlobal.modalPopup.open(url);
        }
        pixelmanagerGlobal.closePagePreview = function () {
            pixelmanagerGlobal.modalPopup.close();
        }

        // ****************************************************************
        // jQuery-Code für die Seite "main"
        // ****************************************************************

        $(function () {

            // ****************************************************************
            // Einstellungen fürs Layout
            // ****************************************************************

            var settings = {
                leftColumnMinWidth: 330,
                rightColumnMinWidth: 300,
                borderWidth: 10
            }


            // ****************************************************************
            // Event-Handler für die Sprachumschaltung
            // ****************************************************************

            $("#btn_secondary_language_none").click(function () {
                pixelmanagerGlobal.switchLanguage(pixelmanagerGlobal.activeLanguage, null);
            });

            var _TempLanguageId
            for (_TempLanguageId in pixelmanagerGlobal.languages) {

                $("#btn_language_" + _TempLanguageId).click(function () {
                    pixelmanagerGlobal.switchLanguage($(this).attr('data-language'), pixelmanagerGlobal.activeSecondaryLanguage);
                });

                $("#btn_secondary_language_" + _TempLanguageId).click(function () {
                    pixelmanagerGlobal.switchLanguage(pixelmanagerGlobal.activeLanguage, $(this).attr('data-language'));
                });

            }


            // ****************************************************************
            // Inititalisierung der Steuerelemente
            // ****************************************************************

            backendModules.init('#pages-modules-tab', '#modules-menu', '.pixelmanager-main-left-column');
            $('.pixelmanager-main-left-column').show();
            tabs.init("tabs");
            pagetree.init('#page-tree', '.pixelmanager-main-left-column');


            // ****************************************************************
            // Hauptmenü Click-Handler
            // ****************************************************************

            $("#btn_about").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/about', translate.get('About...'), 'about');
            });
            $("#menu_images").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/images', translate.get('Images'), 'images');
            });
            $("#menu_downloads").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/downloads', translate.get('Downloads'), 'downloads');
            });
            $("#menu_users").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/users', translate.get('Users'), 'users');
            });
            $("#menu_user_groups").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/usergroups', translate.get('User groups'), 'usergroups');
            });
            $("#menu_user_account").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/useraccount', translate.get('User account'), 'useraccount');
            });
            $("#menu_global_elements").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/pagecontent/?pageId=' + pixelmanagerGlobal.globalElementsPageId, translate.get('Global elements'), 'content_' + pixelmanagerGlobal.globalElementsPageId);
            });
            $("#menu_settings").click(function () {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/settings', translate.get('Settings'), 'settings');
            });


            // ****************************************************************
            // Seitenbaum Click-Handler
            // ****************************************************************

            $("#btn_add_page").click(function () {
                pagetree.openNewPageTab();
            });
            $("#btn_action_open").click(function () {
                pagetree.openContentTab();
            });
            $("#btn_action_rename").click(function () {
                pagetree.rename();
            });
            $("#btn_action_publish").click(function () {
                if (pagetree.getSelectedCount() > 0) {
                    $("#pixelmanager-main-pagetree-publish input[name='publish-recursive']").prop('checked', false);
                    $('#pixelmanager-main-pagetree-publish').modal({
                        keyboard: true,
                        backdrop: true
                    });
                }
            });
            $("#btn_action_copy").click(function () {
                pagetree.copySelected();
            });
            $("#btn_action_cut").click(function () {
                pagetree.cutSelected();
            });
            $("#btn_action_paste").click(function () {
                pagetree.pasteSelected();
            });
            $("#btn_action_delete").click(function () {
                if (pagetree.getSelectedCount() > 0) {
                    var subpageCount = pagetree.getSelectedSubpageCount();
                    if (subpageCount > 0) {
                        $("#pixelmanager-main-pagetree-delete-subpages").show();
                        $("#pixelmanager-main-pagetree-delete-subpages-count").html(subpageCount.toString());
                        $("#pixelmanager-main-pagetree-delete-subpages input[name='delete-subpages']").prop('checked', false);
                    } else {
                        $("#pixelmanager-main-pagetree-delete-subpages").hide();
                        $("#pixelmanager-main-pagetree-delete-subpages input[name='delete-subpages']").prop('checked', true);
                    }
                    $("#pixelmanager-main-pagetree-delete .pixelmanager-error-container").empty();
                    $('#pixelmanager-main-pagetree-delete').modal({
                        keyboard: true,
                        backdrop: true
                    });
                }
            });
            $("#btn_action_expand_all").click(function () {
                pagetree.expandAll();
            });
            $("#btn_action_collapse_all").click(function () {
                pagetree.collapseAll();
            });
            $("#btn_action_properties").click(function () {
                pagetree.openPropertiesTab();
            });
            $("#btn_refresh").click(function () {
                pagetree.refresh();
            });
            $("#btn_info").click(function () {
                pagetree.togglePageInfo();
            });

            // ****************************************************************
            // Seitenbaum Löschen Dialog
            // ****************************************************************

            $("#btn_pagtree_delete_ok").click(function () {
                if ($("#pixelmanager-main-pagetree-delete-subpages input[name='delete-subpages']").is(':checked')) {
                    $('#pixelmanager-main-pagetree-delete').one('hidden.bs.modal', function () {
                        pagetree.deleteSelected();
                    });
                    $('#pixelmanager-main-pagetree-delete').modal('hide');
                } else {
                    $("#pixelmanager-main-pagetree-delete .pixelmanager-error-container").empty();
                    $("#pixelmanager-main-pagetree-delete .pixelmanager-error-container").append('<div class="alert alert-danger">' + translate.get('Please confirm that you really want to delete the selected pages including all the subpages') + '</div>');
                }
            });
            $("#btn_pagtree_delete_cancel").click(function () {
                $('#pixelmanager-main-pagetree-delete').modal('hide');
            });


            // ****************************************************************
            // Seitenbaum Veröffentlichen Dialog
            // ****************************************************************

            $("#btn_pagtree_publish_ok").click(function () {
                $('#pixelmanager-main-pagetree-publish').one('hidden.bs.modal', function () {
                    if ($("#pixelmanager-main-pagetree-publish input[name='publish-recursive']").is(':checked')) {
                        pagetree.publishSelected(true);
                    } else {
                        pagetree.publishSelected(false);
                    }
                });
                $('#pixelmanager-main-pagetree-publish').modal('hide');
            });
            $("#btn_pagtree_publish_cancel").click(function () {
                $('#pixelmanager-main-pagetree-publish').modal('hide');
            });

            // ****************************************************************
            // Synchronisation
            // ****************************************************************

            var syncCallbackId = dataExchange.createCallbackItem('#menu_synchronize');

            $('#' + syncCallbackId).on('success.pixelmanager', function () {
                window.history.go(0);
            });

            var $confirmDialog = $('#pixelmanager-main-sync-confirm');

            $confirmDialog.find('.btn-ok').click(function () {
                dataExchange.request(translate.get('Synchronizing page data'), pixelmanagerGlobal.baseUrl + 'admin/data-exchange/sync/fetch', {}, syncCallbackId);
                $confirmDialog.modal('hide');
            });

            $confirmDialog.find('.btn-cancel').click(function () {
                $confirmDialog.modal('hide');
            });

            $("#menu_synchronize").click(function () {
                $confirmDialog.modal({
                    keyboard: true,
                    backdrop: true
                });
            });


            // ****************************************************************
            // Fehler-Fenster Click-Handler
            // ****************************************************************

            $("#btn_error_retry").click(function () {
                $('#pixelmanager-main-synchronize-error').on('hidden.bs.modal', function () {
                    $('#pixelmanager-main-synchronize-error').off('hidden.bs.modal');
                    pixelmanagerGlobal.dataExchange.retryFailedRequest($("#pixelmanager-main-synchronize-error-request-id").val());
                })
                $('#pixelmanager-main-synchronize-error').modal('hide')
            });
            $("#btn_error_dismiss").click(function () {
                $('#pixelmanager-main-synchronize-error').on('hidden.bs.modal', function () {
                    $('#pixelmanager-main-synchronize-error').off('hidden.bs.modal');
                    pixelmanagerGlobal.dataExchange.dismissFailedRequest($("#pixelmanager-main-synchronize-error-request-id").val());
                })
                $('#pixelmanager-main-synchronize-error').modal('hide')
            });

            $("#btn_login_retry").click(function () {
                $('#pixelmanager-main-synchronize-login').on('hidden.bs.modal', function () {
                    $('#pixelmanager-main-synchronize-login').off('hidden.bs.modal');
                    pixelmanagerGlobal.dataExchange.login($("#pixelmanager-main-synchronize-login-password").val());
                })
                $('#pixelmanager-main-synchronize-login').modal('hide')
            });
            $("#btn_login_dismiss").click(function () {
                $('#pixelmanager-main-synchronize-login').on('hidden.bs.modal', function () {
                    $('#pixelmanager-main-synchronize-login').off('hidden.bs.modal');
                    pixelmanagerGlobal.dataExchange.dismissLogin();
                })
                $('#pixelmanager-main-synchronize-login').modal('hide')
            });

            // ****************************************************************
            // Tooltips und Buttons
            // ****************************************************************

            $(".show-tooltip").tooltip();
            $(".show-tooltip-btn-group").tooltip({container: 'body'});
            // $("#btn_info").button();

            // ****************************************************************
            // Resize
            // ****************************************************************

            function resizeRightColumn() {
                var left = $(".pixelmanager-main-left-column").width() + (settings.borderWidth * 2);
                $(".pixelmanager-main-right-column").css("left", left.toString() + "px");
            }

            var resizable = $(".pixelmanager-main-left-column").resizable({
                minWidth: settings.leftColumnMinWidth,
                handles: "e",
                resize: function (event, ui) {
                    resizeRightColumn();
                },
                start: function (event, ui) {
                    $(".pixelmanager-main-right-column .pixelmanager-main-resize-overlay").css("display", "block");
                },
                stop: function (event, ui) {
                    $(".pixelmanager-main-right-column .pixelmanager-main-resize-overlay").css("display", "none");
                }
            });

            function nanPreventer(value) {
                function isNumber(o) {
                    return !isNaN(o - 0) && o != null;
                }

                if (!isNumber(value)) {
                    return (0);
                } else {
                    return (value);
                }
            }

            $(window).resize(function (e) {
                var top = nanPreventer(parseInt($(".pixelmanager-main-left-column").css("top")))
                var height = $(window).height() - settings.borderWidth - top;
                var minHeight = nanPreventer(parseInt($(".pixelmanager-main-container").css("minHeight"))) - settings.borderWidth - top;
                if (height < minHeight) {
                    height = minHeight;
                }
                $(".pixelmanager-main-left-column").css("height", height.toString() + 'px');
                var windowWidth = $(window).width();
                var documentMinWidth = nanPreventer(parseInt($(".pixelmanager-main-container").css("minWidth")));
                if (windowWidth < documentMinWidth) {
                    windowWidth = documentMinWidth;
                }
                var maxWidth = windowWidth - settings.rightColumnMinWidth - (settings.borderWidth * 3);
                if (maxWidth < settings.leftColumnMinWidth) {
                    maxWidth = settings.leftColumnMinWidth;
                }
                $(".pixelmanager-main-left-column").resizable("option", "maxWidth", maxWidth);
                if ($(".pixelmanager-main-left-column").width() > maxWidth) {
                    $(".pixelmanager-main-left-column").css("width", maxWidth.toString() + 'px');
                    resizeRightColumn();
                }
            });

            $(window).resize();


        });
    });
