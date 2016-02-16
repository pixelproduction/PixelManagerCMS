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

define(function (require) {

    "use strict";

    // Dependencies
    var $ = require('jquery');
    var hotkeys = require('plugins/hotkeys');
    var cookie = require('plugins/cookie');
    var jstree = require('plugins/jstree');
    var translate = require('modules/translate');
    var dataExchange = require('modules/data-exchange');
    var tabs = require('modules/tabs');
    var bootstrap = require('plugins/jquery-ui-bootstrap-no-conflict');

    // "Konstanten"
    var ROOT_ID = 0;

    // Interne Variablen
    var treeSelector = '';
    var callbackElementSelector = '';
    var treeInstance = null;
    var loadDataCallbackId = null;
    var dragdropCallbackId = null;
    var deleteCallbackId = null;
    var pageInfoCallbackId = null;
    var renameCallbackId = null;
    var publishCallbackId = null;
    var newPageCount = 1;
    var showPageInfo = false;
    var savedScrollPosition = {left: 0, top: 0};

    // Interne Funktionen...

    function getRandomId() {
        function getRandomNumber(range) {
            return Math.floor(Math.random() * range);
        }

        function getRandomChar() {
            var chars = "0123456789abcdefghijklmnopqurstuvwxyzABCDEFGHIJKLMNOPQURSTUVWXYZ";
            return chars.substr(getRandomNumber(62), 1);
        }

        var str = "";
        for (var i = 0; i < 31; i++) {
            str += getRandomChar();
        }
        return ('_' + str);
    }

    function dataLoadSuccess(event, data) {
        recreateTree(data);
    }

    function dataLoadFail(event, data) {
    }

    function dragdropSuccess(event, data) {
        if (typeof(data.action) != 'undefined') {
            if (data.action == 'copy') {
                reloadTree();
            }
        }
    }

    function dragdropFail(event, data) {
        reloadTree();
    }

    function deleteSuccess(event, data) {
        reloadTree();
    }

    function deleteFail(event, data) {
        reloadTree();
    }

    function publishSuccess(event, data) {
        reloadTree();
    }

    function publishFail(event, data) {
        reloadTree();
    }

    function loadPageInfoSuccess(event, data) {
        if (typeof(data.infos) != 'undefined') {
            $('#page-tree-info-content').empty();
            var html = '<table class="table table-striped table-condensed"><tbody>';
            for (var i = 0; i < data.infos.length; i++) {
                html = html + '<tr><td>' + data.infos[i].description + '</td><td>' + data.infos[i].value + '</td></tr>';
            }
            html = html + '</tbody></table>';
            $('#page-tree-info-content').html(html);
        }
        $('#page-tree-info').show();
    }

    function loadPageInfoFail(event, data) {
    }

    function renameSuccess(event, data) {
    }

    function renameFail(event, data) {
        reloadTree();
    }

    function deleteTree() {
        if (treeInstance != null) {
            $(treeSelector).off(".jstree");
            $(treeSelector).jstree("destroy").empty();
            treeInstance = null;
        }
    }

    function openInfoOverlay(jqueryNode) {
        closeInfoOverlay();
        var left = 0;
        var top = 0;
        var nodePos = $(jqueryNode).offset();
        left = nodePos.left + $(jqueryNode).children('a:visible').first().width() + 30;
        var maxLeft = $(".pixelmanager-main-left-column").width() + 20;
        if (left > maxLeft) {
            left = maxLeft;
        }
        top = nodePos.top - 8;
        $('#page-tree-info').css('left', left.toString() + 'px');
        $('#page-tree-info').css('top', top.toString() + 'px');
        var postData = {
            'pageId': $(jqueryNode).data('id'),
            'languageId': pixelmanagerGlobal.activeLanguage
        };
        dataExchange.request(translate.get('Get page information (on hover)'), pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagetree/info', postData, pageInfoCallbackId, undefined, undefined, true);
    }

    function closeInfoOverlay() {
        $('#page-tree-info').hide();
    }

    function recreateTree(jsonData) {
        $(treeSelector).hide();
        deleteTree();
        var langKey = null;
        var langCodes = [];
        var counter = 0;
        for (langKey in pixelmanagerGlobal.languages) {
            langCodes[counter] = langKey;
            counter++;
        }
        treeInstance = $(treeSelector).jstree(
            {
                "json_data": {
                    "data": jsonData.children
                },
                "languages": langCodes,
                "themes": {
                    "theme": "default",
                    "url": pixelmanagerGlobal.baseUrl + "system/core/backend/public/css/libs/jstree/default/style.css"
                },
                "hotkeys": {
                    "f2": false,
                    "del": false
                },
                "core": {
                    "animation": 0
                },
                "ui": {
                    "selected_parent_close": "deselect",
                    "disable_selecting_children": true
                },
                "plugins": ["themes", "json_data", "languages", "ui", "crrm", "cookies", "dnd", "search", "hotkeys"]
            }
        );
        if (isTreeAvailable()) {

            $(treeSelector).on("rename.jstree", function (event, data) {
                if ((typeof(data.rslt.new_name) != 'undefined') && (typeof(data.rslt.old_name) != 'undefined') && (typeof(data.rslt.obj) != 'undefined')) {
                    if (data.rslt.new_name != data.rslt.old_name) {
                        if (data.rslt.new_name != '') {
                            postData = {
                                'pageId': $(data.rslt.obj).data('id'),
                                'languageId': pixelmanagerGlobal.activeLanguage,
                                'title': data.rslt.new_name
                            };
                            dataExchange.request(translate.get('Setting new page title'), pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagetree/settitle', postData, renameCallbackId);
                            return (true);
                        } else {
                            return (false);
                        }
                    }
                }
            });

            $(treeSelector).on("loaded.jstree", function (event, data) {
                $(treeSelector).show();
            });

            $(treeSelector).on("reopen.jstree", function (event, data) {
                $(treeSelector).scrollLeft(savedScrollPosition.left);
                $(treeSelector).scrollTop(savedScrollPosition.top);
            });

            $(treeSelector).on("hover_node.jstree", function (event, data) {
                if (showPageInfo) {
                    if (typeof(data.rslt.obj) != 'undefined') {
                        openInfoOverlay(data.rslt.obj);
                    }
                }
                return (true);
            });

            $(treeSelector).on("dehover_node.jstree", function (event, data) {
                if (showPageInfo) {
                    closeInfoOverlay();
                }
                return (true);
            });

            // Da jsTree standardm��ig, aus welchen Gr�nden auch immer, nicht
            // dazu in der Lage ist, kopierte/ausgeschnittene Elemente
            // in die Root-Ebene einzuf�gen, m�ssen wir hier von Hand nachhelfen...
            $(treeSelector).on("before.jstree", function (event, data) {
                if (data.func == "paste") {
                    if (countSelectedNodes() == 0) {
                        var action = 'nothing';
                        var nodes = null;
                        if (typeof(data.inst.data.crrm.cp_nodes) != 'undefined') {
                            if (data.inst.data.crrm.cp_nodes != false) {
                                action = 'copy';
                                nodes = data.inst.data.crrm.cp_nodes;
                            }
                        }
                        if (action == 'nothing') {
                            if (typeof(data.inst.data.crrm.ct_nodes) != 'undefined') {
                                if (data.inst.data.crrm.ct_nodes != false) {
                                    action = 'move';
                                    nodes = data.inst.data.crrm.ct_nodes;
                                }
                            }
                        }
                        if (action != 'nothing') {
                            var isCopy = false;
                            if (action == 'copy') {
                                isCopy = true;
                            }
                            $(treeSelector).jstree("move_node", nodes, -1, "last", isCopy);
                        }
                    }
                }
            });

            $(treeSelector).on("move_node.jstree", function (event, data) {
                var nodes = [];
                var counter = 0;
                data.rslt.o.each(function () {
                    nodes[counter] = $(this).data('id')
                    counter++;
                });
                var action = 'move';
                if (data.rslt.cy) {
                    action = 'copy';
                }
                var destId = ROOT_ID;
                if (data.rslt.cr != -1) {
                    destId = data.rslt.np.data('id');
                }
                var postData = {
                    'destId': destId,
                    'destPosition': data.rslt.cp,
                    'action': action,
                    'elements': nodes
                };
                dataExchange.request(translate.get('Executing drag&drop command'), pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagetree/dragdrop', postData, dragdropCallbackId);
            });

            $(treeSelector).jstree("set_lang", pixelmanagerGlobal.activeLanguage);
        }

    }

    function isTreeAvailable() {
        return (treeInstance != null);
    }

    function getSelectedPageIds() {
        var idList = [];
        if (isTreeAvailable()) {
            var selected = $(treeSelector).jstree("get_selected");
            if (selected.length > 0) {
                var counter = 0;
                $(selected).each(function () {
                    var id = $(this).data("id");
                    idList[counter] = id;
                    counter++;
                });
            }
        }
        return (idList);
    }

    function countSelectedNodes() {
        var count = 0;
        var selected = $(treeSelector).jstree("get_selected");
        if (typeof(selected) != 'undefined') {
            if (selected.length > 0) {
                $(selected).each(function () {
                    count++;
                });
            }
        }
        return (count);
    }

    function deselectAll() {
        if (isTreeAvailable()) {
            $(treeSelector).jstree("deselect_all");
            $(treeSelector).jstree("save_cookie");
        }
    }

    function reloadTree() {
        savedScrollPosition.left = $(treeSelector).scrollLeft();
        savedScrollPosition.top = $(treeSelector).scrollTop();
        deleteTree();
        dataExchange.request(translate.get('Loading page tree'), pixelmanagerGlobal.baseUrl + "admin/data-exchange/pagetree/get", {}, loadDataCallbackId);
    }

    function countNodeChildren(node) {
        var count = 0;
        var childUl = $(node).children('ul');
        if (childUl.length > 0) {
            var children = $(childUl).children();
            if (children.length > 0) {
                $(children).each(function () {
                    count = count + 1 + countNodeChildren(this);
                });
            }
        }
        return (count);
    }

    function shortenTabName(str, n) {
        return str.substr(0, n - 1) + (str.length > n ? '&hellip;' : '');
    };

    function getCaptionByPageId(id) {
        return (shortenTabName($(treeSelector).jstree("get_text", '#page_' + id), 20));
    }

    function openPropertiesTab() {
        var idList = getSelectedPageIds();
        if (idList.length > 0) {
            if (idList.length > 1) {
                var url = pixelmanagerGlobal.baseUrl + 'admin/html-output/pageproperties/edit?';
                var params = '';
                for (var i = 0; i < idList.length; i++) {
                    if (params != '') {
                        params = params + '&';
                    }
                    params = params + 'pageId[' + i.toString() + ']=' + idList[i].toString();
                }
                tabs.openTab(url + params, translate.get('Edit properties'), 'properties' + getRandomId());
            } else {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/pageproperties/edit?pageId=' + idList[0], translate.get('Properties of') + ' ' + getCaptionByPageId(idList[0]), 'properties_' + idList[0].toString());
            }
        }
    }

    function openContentTab() {
        var idList = getSelectedPageIds();
        if (idList.length > 0) {
            var isPageLink = $('#page_' + idList[0].toString()).data('isPageLink');
            if (isPageLink == true) {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/pageproperties/edit?pageId=' + idList[0], translate.get('Properties of') + ' ' + getCaptionByPageId(idList[0]), 'properties_' + idList[0].toString());
            } else {
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/pagecontent/?pageId=' + idList[0], getCaptionByPageId(idList[0]), 'content_' + idList[0].toString());
            }
        }
    }

    return {

        // �ffentliche Funktionen

        init: function (newTreeSelector, newCallbackElementSelector) {
            treeSelector = newTreeSelector;
            callbackElementSelector = newCallbackElementSelector;

            loadDataCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
            $('#' + loadDataCallbackId).on("success.pixelmanager", dataLoadSuccess);
            $('#' + loadDataCallbackId).on("fail.pixelmanager", dataLoadFail);

            dragdropCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
            $('#' + dragdropCallbackId).on("success.pixelmanager", dragdropSuccess);
            $('#' + dragdropCallbackId).on("fail.pixelmanager", dragdropFail);

            deleteCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
            $('#' + deleteCallbackId).on("success.pixelmanager", deleteSuccess);
            $('#' + deleteCallbackId).on("fail.pixelmanager", deleteFail);

            pageInfoCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
            $('#' + pageInfoCallbackId).on("success.pixelmanager", loadPageInfoSuccess);
            $('#' + pageInfoCallbackId).on("fail.pixelmanager", loadPageInfoFail);

            renameCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
            $('#' + renameCallbackId).on("success.pixelmanager", renameSuccess);
            $('#' + renameCallbackId).on("fail.pixelmanager", renameFail);

            publishCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
            $('#' + publishCallbackId).on("success.pixelmanager", publishSuccess);
            $('#' + publishCallbackId).on("fail.pixelmanager", publishFail);

            $(treeSelector).on("click", function (event) {
                if (($(event.target).attr('id') == 'page-tree') || ($(event.target).is("li"))) {
                    deselectAll();
                }
            });

            $(treeSelector).on("mousedown", function (event) {
                if (event.which == 3) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    if (countSelectedNodes() <= 1) {
                        var mouseX = event.pageX;
                        var mouseY = event.pageY;
                        $(treeSelector + ' a:visible').each(function () {
                            var offset = $(this).offset();
                            var elementX = offset.left;
                            var elementY = offset.top;
                            if (
                                (mouseX >= elementX) &&
                                (mouseY >= elementY) &&
                                (mouseX <= elementX + $(this).outerWidth(true)) &&
                                (mouseY <= elementY + $(this).outerHeight(true))
                            ) {
                                $(treeSelector).jstree('select_node', $(this).parent(), true);
                            }
                        });
                    }
                    $('#page-tree-edit-menu-container')
                        .addClass('open')
                        .addClass('opened-via-right-click')
                    ;
                    $('#page-tree-edit-menu').css({
                        'position': 'fixed',
                        'left': -1000, // erstmal in einen nicht sichbaren Bereich verschieben...
                        'top': -1000,
                        'width': 'auto',
                        'height': 'auto',
                        'float': 'none',
                        'bottom': 'auto'
                    });
                    var menuX = event.pageX - $(window).scrollLeft() + 10;  // etwas nach rechts vom Mauszeiger verschoben, damit das Kontext-Men� des Browsers nicht angezeigt wird
                    var menuY = event.pageY - $(window).scrollTop();
                    var menuHeight = $('#page-tree-edit-menu').outerHeight(true);
                    var windowHeight = $(window).height();
                    if ((event.pageY + menuHeight) > windowHeight) {
                        if (menuHeight < windowHeight) {
                            menuX = menuX;
                            menuY = windowHeight - menuHeight - $(window).scrollTop();
                        }
                    }
                    $('#page-tree-edit-menu').css({
                        'left': menuX, // jetzt an der berechneten Stelle anzeigen
                        'top': menuY
                    });
                }
            });

            $('#page-tree-edit-menu-button').on("click", function (event) {
                $('#page-tree-edit-menu').css({
                    'position': 'absolute',
                    'left': '0',
                    'top': 'auto',
                    'width': 'auto',
                    'height': 'auto',
                    'float': 'left',
                    'bottom': '100%'
                });
            });

            $('#page-tree-edit-menu-container').on("hide.bs.dropdown", function (event) {
                if ($('#page-tree-edit-menu-container').hasClass('opened-via-right-click')) {
                    $('#page-tree-edit-menu-container').removeClass('opened-via-right-click');
                    event.preventDefault();
                }
            });

            $(treeSelector).bind('contextmenu', function (e) {
                e.preventDefault();
                return false;
            });

            $(treeSelector).on("dblclick", function (event) {
                if (($(event.target).is("a")) || ($(event.target).parent().first().is('a'))) {
                    openContentTab();
                }
            });

            $(treeSelector).on("mouseover", function (event) {
                if (($(event.target).attr('id') == 'page-tree') || ($(event.target).is("li"))) {
                    closeInfoOverlay();
                }
            });

            this.refresh();
        },

        refresh: function () {
            reloadTree();
        },

        setLanguage: function (languageId) {
            $(treeSelector).jstree("set_lang", languageId);
        },

        getSelectedPageIds: function () {
            return (getSelectedPageIds());
        },

        getSelectedCount: function () {
            return (countSelectedNodes());
        },

        getSelectedSubpageCount: function () {
            var count = 0;
            var selected = $(treeSelector).jstree("get_selected");
            if (typeof(selected) != 'undefined') {
                if (selected.length > 0) {
                    $(selected).each(function () {
                        count = count + countNodeChildren(this);
                    });
                }
            }
            return (count);
        },

        openPropertiesTab: function () {
            openPropertiesTab();
        },

        openContentTab: function () {
            openContentTab();
        },

        openNewPageTab: function () {
            var idList = getSelectedPageIds();
            var parentId = ROOT_ID;
            if (idList.length > 0) {
                parentId = idList[0];
            }
            tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/html-output/pageproperties/create?parentId=' + parentId, translate.get('New page'), 'newpage_' + newPageCount.toString());
            newPageCount++;
        },

        expandAll: function () {
            $(treeSelector).jstree("open_all", -1, false);
        },

        collapseAll: function () {
            $(treeSelector).jstree("close_all", -1, false);
        },

        clearSelection: function () {
            deselectAll();
        },

        deleteSelected: function () {
            var postData = {
                'elements': getSelectedPageIds()
            };
            dataExchange.request(translate.get('Deleting selected pages'), pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagetree/delete', postData, deleteCallbackId);
        },

        publishSelected: function (recursive) {
            if (typeof(recursive) == 'undefined') {
                var recursive = false;
            }
            var postData = {
                'elements': getSelectedPageIds(),
                'recursive': recursive ? '1' : '0'
            };
            dataExchange.request(translate.get('Publishing selected pages'), pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagetree/publish', postData, publishCallbackId);
        },

        copySelected: function () {
            $(treeSelector).jstree("copy", null);
        },

        cutSelected: function () {
            $(treeSelector).jstree("cut", null);
        },

        pasteSelected: function () {
            $(treeSelector).jstree("paste", null);
        },

        togglePageInfo: function () {
            showPageInfo = (!showPageInfo);
            if (!showPageInfo) {
                closeInfoOverlay();
            }
        },

        isPageInfoDisplayed: function () {
            return (showPageInfo);
        },

        rename: function () {
            $(treeSelector).jstree("rename", null);
        }

    };

});