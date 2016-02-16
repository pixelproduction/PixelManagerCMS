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
        "plugins/cookie",
        "plugins/jstree"
    ],
    function ($, translate) {

        $(function () {

            var loadDataCallbackId = null;
            var getLinkCallbackId = null;
            var treeInstance = null;

            function dataLoadSuccess(event, data) {
                createTree(data);
            }

            function dataLoadFail(event, data) {
            }

            function deleteTree() {
                if (treeInstance != null) {
                    $('#page-tree').off(".jstree");
                    $('#page-tree').jstree("destroy").empty();
                    treeInstance = null;
                }
            }

            function isTreeAvailable() {
                return (treeInstance != null);
            }

            function createTree(jsonData) {
                var langKey = null;
                var langCodes = [];
                var counter = 0;
                for (langKey in parent.pixelmanagerGlobal.languages) {
                    langCodes[counter] = langKey;
                    counter++;
                }
                treeInstance = $('#page-tree').jstree(
                    {
                        "json_data": {
                            "data": jsonData.children
                        },
                        "languages": langCodes,
                        "themes": {
                            "theme": "default",
                            "url": parent.pixelmanagerGlobal.baseUrl + "system/core/backend/public/css/libs/jstree/default/style.css"
                        },
                        "core": {
                            "animation": 0
                        },
                        "ui": {
                            "selected_parent_close": "deselect",
                            "disable_selecting_children": true
                        },
                        "cookies": {
                            "save_opened": "pixelmanager_link_selector_opened",
                            "save_selected": "pixelmanager_link_selector_selected",
                            "auto_save": true
                        },
                        "plugins": ["themes", "json_data", "languages", "ui", "cookies"]
                    }
                );
                if (isTreeAvailable()) {
                    $('#page-tree').jstree("set_lang", parent.pixelmanagerGlobal.activeLanguage);
                }

            }

            function getSelectedPageIds() {
                var idList = [];
                if (isTreeAvailable()) {
                    var selected = $('#page-tree').jstree("get_selected");
                    if (selected.length > 0) {
                        var counter = 0;
                        var language = $('#create-link-to-language-id').val();
                        $(selected).each(function () {
                            var $this = $(this);
                            var anchorName = '';

                            if ($this.data('isPageAnchor')) {
                                anchorName = $(this).find('.' + language).text().trim();
                            }

                            var id = $this.data("id");
                            idList[counter] = {
                                id: id,
                                anchorName: anchorName
                            };
                            counter++;
                        });
                    }
                }
                return (idList);
            }

            function getLinkSuccess(event, data) {
                if (typeof(parent.pixelmanagerGlobal.linkSelectorCallback) != 'undefined') {
                    if (parent.pixelmanagerGlobal.linkSelectorCallback != null) {
                        parent.pixelmanagerGlobal.linkSelectorCallback(data);
                    }
                }
                deleteTree();
                parent.pixelmanagerGlobal.closeLinkSelector();
            }

            function returnSelectedPage() {
                var idList = getSelectedPageIds();
                if (idList.length == 1) {
                    var postData = {
                        linkToPageId: idList[0].id,
                        linkToLanguageId: $('#create-link-to-language-id').val(),
                        linkToAnchorName: idList[0].anchorName
                    };
                    parent.pixelmanagerGlobal.dataExchange.request(
                        translate.get('Loading link to selected page'),
                        parent.pixelmanagerGlobal.baseUrl + "admin/data-exchange/linkselector/getlink",
                        postData,
                        getLinkCallbackId,
                        window.frameElement.id,
                        $
                    );
                }
            }

            $('#page-tree').on("dblclick", function (event) {
                if (($(event.target).is("a")) || ($(event.target).parent().first().is('a'))) {
                    returnSelectedPage();
                }
            });

            $('#create-link-to-language-id').val(parent.pixelmanagerGlobal.activeLanguage);

            loadDataCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $('#' + loadDataCallbackId).on("success.pixelmanager", dataLoadSuccess);
            $('#' + loadDataCallbackId).on("fail.pixelmanager", dataLoadFail);

            getLinkCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            $('#' + getLinkCallbackId).on("success.pixelmanager", getLinkSuccess);

            parent.pixelmanagerGlobal.dataExchange.request(
                translate.get('Loading page tree'),
                parent.pixelmanagerGlobal.baseUrl + "admin/data-exchange/pagetree/get",
                {
                    includeAnchors: true
                },
                loadDataCallbackId,
                window.frameElement.id,
                $
            );

            $('#btn_select_page').click(function (e) {
                returnSelectedPage();
            });

            $('#btn_expand_all').click(function (e) {
                $('#page-tree').jstree("open_all", -1, false);
            });

            $('#btn_close_all').click(function (e) {
                $('#page-tree').jstree("close_all", -1, false);
            });

        });
    });
