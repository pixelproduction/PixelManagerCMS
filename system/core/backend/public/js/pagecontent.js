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
        "modules/data-editor-bootstrap",
        "plugins/jquery-ui-bootstrap-no-conflict"
    ],
    function ($, translate, dataEditorBootstrap) {

        $(function () {

            // "globale" Variablen
            var pageId = $('#pageId').val();
            var loadedData;
            var loadCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            var saveCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
            var eventListenerId = parent.pixelmanagerGlobal.tabs.eventListener('body', window.frameElement.id, $);


            // **********************************************************************************************************
            // **********************************************************************************************************
            // Seiten-Daten und Struktur laden
            // **********************************************************************************************************
            // **********************************************************************************************************

            function enableSaveButtons() {
                $('#btn_save').removeAttr('disabled');
                $('#btn_preview').removeAttr('disabled');
            }

            $("#" + loadCallBackId).on("success.pixelmanager", function (event, data) {
                loadedData = data;
                if (typeof(data.pageStructure) != 'undefined') {
                    $.fn.dataEditorPluginsAsyncScriptsLoaded('startPageCreation', enableSaveButtons);
                    for (var id in data.pageStructure) {
                        if ((typeof(data.pageStructure[id].type) != 'undefined') && (typeof(data.pageStructure[id].caption) != 'undefined')) {
                            var closed = false;
                            if (typeof(data.pageStructure[id].closed) != 'undefined') {
                                closed = data.pageStructure[id].closed;
                            }
                            if (data.pageStructure[id].type == 'datablock') {
                                if (typeof(data.pageStructure[id].fields) != 'undefined') {
                                    var blockData = {};
                                    if (typeof(data.pageContent[id]) != 'undefined') {
                                        blockData = data.pageContent[id];
                                    }
                                    createContentBlock(id, data.pageStructure[id].caption, blockData, data.pageStructure[id].fields, closed);
                                }
                            } else if (data.pageStructure[id].type == 'container') {
                                if ((typeof(data.pageStructure[id].parameters) != 'undefined') && (typeof(data.elements) != 'undefined')) {
                                    var containerData = [];
                                    if (typeof(data.pageContent[id]) != 'undefined') {
                                        containerData = data.pageContent[id];
                                    }
                                    createContainer(id, data.pageStructure[id].caption, containerData, data.pageStructure[id].parameters, data.elements, closed);
                                }
                            }
                        }
                    }
                    bindEvents();
                    $.fn.dataEditorPluginsAsyncScriptsLoaded('finishPageCreation');
                } else {
                    displayErrorMessage(translate.get('An error occured: The page content could not be loaded.'));
                }
            });

            $("#" + loadCallBackId).on("fail.pixelmanager", function (event, data) {
                displayErrorMessage(translate.get('An error occured: The page content could not be loaded.'));
            });

            parent.pixelmanagerGlobal.dataExchange.request(
                translate.get('Loading page content'),
                parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagecontent/get/',
                {'pageId': pageId},
                loadCallBackId,
                window.frameElement.id,
                $
            );


            // **********************************************************************************************************
            // **********************************************************************************************************
            // Funktionen zum Erstellen des HTML-Codes
            // **********************************************************************************************************
            // **********************************************************************************************************

            function createContentBlock(id, caption, content, structure, closed) {
                var block_closed_class = '';
                var closed_class = '';
                var hide_button_class = 'icon-chevron-up';
                if (closed) {
                    block_closed_class = ' pagecontent-block-content-hidden';
                    closed_class = ' hidden';
                    hide_button_class = 'icon-chevron-down';
                }
                $('#content').append(
                    '<div class="pagecontent-block' + block_closed_class + '">' +
                    '<div class="pagecontent-block-header clearfix" id="' + id + '-header">' +
                    '<span class="pagecontent-block-header-caption">' + caption + '</span>' +
                    '<button rel="tooltip" class="btn btn-xs btn-inverse pull-right pagecontent-block-visibility-button" title="' + translate.get('Close / expand') + '"><span class="glyphicon glyph' + hide_button_class + ' icon-white"></span></button>' +
                    '</div>' +
                    '<div class="pagecontent-block-content' + closed_class + '">' +
                    '<div class="pagecontent-container" id="' + id + '-data-editor"></div>' +
                    '</div>' +
                    '</div>'
                );
                dataEditorBootstrap.createInstance('#' + id + '-data-editor', content, structure);
            }

            function createContainer(id, caption, content, parameters, elements, closed) {

                if (typeof(elements) == 'undefined') {
                    return;
                }
                if (elements.length < 1) {
                    return;
                }

                var block_closed_class = '';
                var closed_class = '';
                var hide_button_class = 'icon-chevron-up';
                if (closed) {
                    block_closed_class = ' pagecontent-block-content-hidden';
                    closed_class = ' hidden';
                    hide_button_class = 'icon-chevron-down';
                }

                var element_menu_html = '';
                var allowedElementsCount = 0;
                for (var elementId in elements) {
                    var elementAllowed = true;
                    if (typeof(parameters.allowElements) != 'undefined') {
                        elementAllowed = false;
                        if (parameters.allowElements.length > 0) {
                            for (var i = 0; i < parameters.allowElements.length; i++) {
                                if (parameters.allowElements[i] == elementId) {
                                    elementAllowed = true;
                                }
                            }
                        }
                    }
                    if (elementAllowed) {
                        element_menu_html = element_menu_html + '<li><a href="javascript:;" class="add-element" data-container-id="' + id + '-container" data-element-id="' + elementId + '">' + elements[elementId].name + '</a></li>'
                        allowedElementsCount++;
                    }
                }

                if (allowedElementsCount < 1) {
                    return;
                }

                $('#content').append(
                    '<div class="pagecontent-block' + block_closed_class + '">' +
                    '<div class="pagecontent-block-header clearfix" id="' + id + '-header">' +

                    '<span class="pagecontent-block-header-caption">' + caption + '</span>' +

                    '<button class="btn btn-xs btn-inverse pull-right pagecontent-block-visibility-button" title="' + translate.get('Close / expand') + '"><span class="glyphicon glyph' + hide_button_class + ' icon-white"></span></button>' +

                    '<div class="btn-group pull-right">' +
                    '<a class="btn btn-xs btn-inverse dropdown-toggle" data-toggle="dropdown" href="#">' +
                    '<span class="glyphicon glyphicon-wrench icon-white"></span> ' +
                    translate.get('Edit') +
                    ' <span class="caret"></span>' +
                    '</a>' +
                    '<ul class="dropdown-menu">' +
                    '<li><a href="javascript:;" class="container-expand-elements">' + translate.get('Expand all') + '</a></li>' +
                    '<li><a href="javascript:;" class="container-close-elements">' + translate.get('Close all') + '</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a href="javascript:;" class="container-delete-elements">' + translate.get('Delete selected') + '</a></li>' +
                    '</ul>' +
                    '</div>' +

                    '<div class="btn-group pull-right">' +
                    '<a class="btn btn-xs btn-inverse dropdown-toggle" data-toggle="dropdown" href="#">' +
                    '<span class="glyphicon glyphicon-plus icon-white"></span> ' +
                    translate.get('Add element') +
                    ' <span class="caret"></span>' +
                    '</a>' +
                    '<ul class="dropdown-menu">' +
                    element_menu_html +
                    '</ul>' +
                    '</div>' +

                    '</div>' +
                    '<div class="pagecontent-block-content' + closed_class + '">' +
                    '<div class="pagecontent-container" id="' + id + '-container"></div>' +
                    '</div>' +
                    '</div>'
                );

                if (typeof(content) != 'undefined') {
                    if (content.length > 0) {
                        for (var i = 0; i < content.length; i++) {
                            if ((typeof(content[i].elementId) != 'undefined') && (typeof(content[i].content) != 'undefined')) {
                                appendElementToContainer(id + '-container', content[i].elementId, content[i].content);
                            }
                        }
                    } else {
                        appendEmptyElementToContainer(id + '-container');
                    }
                }

            }

            function appendEmptyElementToContainer(id) {
                $('#' + id).append(
                    '<div class="pagecontent-container-element pagecontent-container-element-empty">' +
                    '<div class="pagecontent-container-element-header data-editor-row">' +
                    '<span class="pagecontent-container-element-header-caption">' + translate.get('No content yet') + '</span>' +
                    '</div>' +
                    '</div>'
                );
            }

            function appendElementToContainer(containerId, elementId, data) {
                if (typeof(loadedData.elements[elementId].structure) == 'undefined') {
                    return;
                }
                var newElementId = getRandomId();
                $('#' + containerId + ' .pagecontent-container-element-empty').remove();
                $('#' + containerId).append(
                    '<div class="pagecontent-container-element" data-element-id="' + elementId + '" id="element-' + newElementId + '">' +
                    '<div class="pagecontent-container-element-header data-editor-row">' +
                    '<span class="pagecontent-container-element-header-caption"><label><input type="checkbox" value="1" id="' + newElementId + '-selected" class="pagecontent-container-element-header-selected"> ' + loadedData.elements[elementId].name + '</label></span>' +
                    '<button class="btn btn-xs btn-inverse pull-right element-visibility-button" title="' + translate.get('Close / expand') + '"><span class="glyphicon glyphicon-chevron-up icon-white"></span></button>' +
                    '<div class="btn-group pull-right">' +
                    '<button class="btn btn-xs btn-inverse element-first" title="' + translate.get('To the top') + '"><span class="glyphicon glyphicon-circle-arrow-up icon-white"></span></button>' +
                    '<button class="btn btn-xs btn-inverse element-last" title="' + translate.get('To the end') + '"><span class="glyphicon glyphicon-circle-arrow-down icon-white"></span></button>' +
                    '<button class="btn btn-xs btn-inverse element-one-up" title="' + translate.get('One up') + '"><span class="glyphicon glyphicon-arrow-up icon-white"></span></button>' +
                    '<button class="btn btn-xs btn-inverse element-one-down" title="' + translate.get('One down') + '"><span class="glyphicon glyphicon-arrow-down icon-white"></span></button>' +
                    '</div>' +
                    '<button class="btn btn-xs btn-inverse pull-right element-delete" title="' + translate.get('Delete') + '"><span class="glyphicon glyphicon-remove icon-white"></span></button>' +
                    '</div>' +
                    '<div class="pagecontent-container-element-content">' +
                    '<div class="pagecontent-container-element-content-data-editor" id="data-editor' + newElementId + '"></div>' +
                    '</div>' +
                    '</div>'
                );
                dataEditorBootstrap.createInstance('#data-editor' + newElementId, data, loadedData.elements[elementId].structure);
                return ('element-' + newElementId);
            }

            function deleteContainerElement(containerId, elementId) {
                $('#' + elementId).find('.pagecontent-container-element-content-data-editor').each(function () {
                    var dataEditorId = $(this).attr('id');
                    $('#' + dataEditorId).dataEditor('destroy');
                });
                $('#' + elementId).remove();
                var elementsOfParentContainer = $('#' + containerId).children();
                if (elementsOfParentContainer.length < 1) {
                    appendEmptyElementToContainer(containerId);
                }
            }

            function setContainerElementsVisibility(container, visible) {
                container.find('.pagecontent-block-content > .pagecontent-container > .pagecontent-container-element').each(function () {
                    var contentContainer = $(this).children('.pagecontent-container-element-content').first();
                    var isCurrentlyVisible = ( !$(contentContainer).hasClass('hidden'));
                    if (visible != isCurrentlyVisible) {
                        $(this)
                            .find('.pagecontent-container-element-header > .element-visibility-button')
                            .first()
                            .click()
                        ;
                    }
                });
            }

            function beforeDataEditorMove(elementId) {
                $('#' + elementId).find('.pagecontent-container-element-content-data-editor').each(function () {
                    var dataEditorId = $(this).attr('id');
                    $('#' + dataEditorId).dataEditor('beforeMove');
                });
            }

            function afterDataEditorMove(elementId) {
                $('#' + elementId).find('.pagecontent-container-element-content-data-editor').each(function () {
                    var dataEditorId = $(this).attr('id');
                    $('#' + dataEditorId).dataEditor('afterMove');
                });
            }

            function isContentBlockVisible(contentBlock) {
                return ( !$(contentBlock).hasClass('pagecontent-block-content-hidden') );
            }

            function openContentBlock(contentBlock) {
                if (!isContentBlockVisible(contentBlock)) {
                    $(contentBlock)
                        .find('.pagecontent-block-visibility-button')
                        .children('.glyphicon')
                        .removeClass('glyphicon-chevron-down')
                        .addClass('glyphicon-chevron-up')
                    ;
                    $(contentBlock).removeClass('pagecontent-block-content-hidden');
                    $(contentBlock)
                        .find('.pagecontent-block-content')
                        .removeClass('hidden')
                    ;
                }
            }

            function closeContentBlock(contentBlock) {
                if (isContentBlockVisible(contentBlock)) {
                    $(contentBlock)
                        .find('.pagecontent-block-visibility-button')
                        .children('.glyphicon')
                        .addClass('glyphicon-chevron-down')
                        .removeClass('glyphicon-chevron-up')
                    ;
                    $(contentBlock).addClass('pagecontent-block-content-hidden');
                    $(contentBlock)
                        .find('.pagecontent-block-content')
                        .addClass('hidden')
                    ;
                }
            }

            function toggleContentBlock(contentBlock) {
                if (isContentBlockVisible(contentBlock)) {
                    closeContentBlock(contentBlock);
                } else {
                    openContentBlock(contentBlock);
                }
            }


            function makeElementVisible(elementContainerId) {
                var contentBlock = $('#' + elementContainerId).parents('.pagecontent-block').first();
                if (contentBlock.length > 0) {
                    openContentBlock(contentBlock);
                    $('.pixelmanager-iframe-content').scrollTop($('#' + elementContainerId).position().top);
                }
            }

            function bindEvents() {

                $('#content').on('click', '.pagecontent-block-visibility-button', function (e) {
                    toggleContentBlock($(e.target).parents('.pagecontent-block').first());
                });

                $('#content').on('click', '.pagecontent-block-header', function (e) {
                    if ($(e.target).hasClass('pagecontent-block-header')) {
                        toggleContentBlock($(e.target).parents('.pagecontent-block').first());
                    }
                });

                $('#content').on('click', '.add-element', function (e) {
                    var containerId = $(e.target).attr('data-container-id');
                    var elementId = $(e.target).attr('data-element-id');
                    if ((typeof(containerId) != 'undefined') && (typeof(elementId) != 'undefined')) {
                        if ((containerId) && (elementId)) {
                            var elementContainerId = appendElementToContainer(containerId, elementId, {});
                            makeElementVisible(elementContainerId);
                        }
                    }
                });

                $('#content').on('click', '.element-visibility-button', function (e) {
                    $(e.target).children('.glyphicon').toggleClass('glyphicon-chevron-up glyphicon-chevron-down');
                    // $(e.target).parents('.pagecontent-container-element-content').toggleClass('pagecontent-block-content-hidden');
                    $(e.target).parents('.pagecontent-container-element').first().children('.pagecontent-container-element-content').toggleClass('hidden');
                });

                $('#content').on('click', '.pagecontent-container-element-header', function (e) {
                    if ($(e.target).hasClass('pagecontent-container-element-header')) {
                        $(e.target).children('.element-visibility-button').first().click();
                    }
                });

                $('#content').on('click', '.element-delete', function (e) {
                    var elementId = $(e.target).parents('.pagecontent-container-element').first().attr('id');
                    var containerId = $(e.target).parents('.pagecontent-container').first().attr('id');
                    deleteContainerElement(containerId, elementId);
                });

                $('#content').on('click', '.element-one-up', function (e) {
                    var elementId = $(e.target).parents('.pagecontent-container-element').first().attr('id');
                    beforeDataEditorMove(elementId);
                    $('#' + elementId).insertBefore($('#' + elementId).prev());
                    afterDataEditorMove(elementId);
                });

                $('#content').on('click', '.element-one-down', function (e) {
                    var elementId = $(e.target).parents('.pagecontent-container-element').first().attr('id');
                    beforeDataEditorMove(elementId);
                    $('#' + elementId).insertAfter($('#' + elementId).next());
                    afterDataEditorMove(elementId);
                });

                $('#content').on('click', '.element-first', function (e) {
                    var elementId = $(e.target).parents('.pagecontent-container-element').first().attr('id');
                    var containerId = $(e.target).parents('.pagecontent-container').first().attr('id');
                    beforeDataEditorMove(elementId);
                    $('#' + elementId).insertBefore($('#' + containerId + ' .pagecontent-container-element').first());
                    afterDataEditorMove(elementId);
                });

                $('#content').on('click', '.element-last', function (e) {
                    var elementId = $(e.target).parents('.pagecontent-container-element').first().attr('id');
                    var containerId = $(e.target).parents('.pagecontent-container').first().attr('id');
                    beforeDataEditorMove(elementId);
                    $('#' + elementId).insertAfter($('#' + containerId + ' .pagecontent-container-element').last());
                    afterDataEditorMove(elementId);
                });

                $('#content').on('click', '.container-expand-elements', function (e) {
                    setContainerElementsVisibility($(e.target).parents('.pagecontent-block').first(), true);
                });

                $('#content').on('click', '.container-close-elements', function (e) {
                    setContainerElementsVisibility($(e.target).parents('.pagecontent-block').first(), false);
                });

                $('#content').on('click', '.container-delete-elements', function (e) {
                    var container = $(e.target)
                        .parents('.pagecontent-block')
                        .first()
                        .find('.pagecontent-block-content > .pagecontent-container')
                        .first()
                        ;
                    var containerId = $(container).attr('id');
                    $(container).children('.pagecontent-container-element').each(function () {
                        var checkbox = $(this).find('.pagecontent-container-element-header-selected').first();
                        if ($(checkbox).is(':checked')) {
                            var elementId = $(this).attr('id');
                            deleteContainerElement(containerId, elementId);
                        }
                    });
                });

                $('#btn_action_outline').on('click', function (e) {
                    $('#content > .pagecontent-block').each(function () {
                        var elements = $(this).find('.pagecontent-container-element');
                        if (!$(this).find('.pagecontent-block-content').first().hasClass('hidden')) {
                            if (elements.length < 1) {
                                $(this).find('.pagecontent-block-header .pagecontent-block-visibility-button').first().click();
                            }
                        } else {
                            if (elements.length > 0) {
                                $(this).find('.pagecontent-block-header .pagecontent-block-visibility-button').first().click();
                            }
                        }
                        setContainerElementsVisibility($(this), false);
                    });
                });

                $('#btn_action_expand_all').on('click', function (e) {
                    $('#content > .pagecontent-block').each(function () {
                        if ($(this).find('.pagecontent-block-content').first().hasClass('hidden')) {
                            $(this).find('.pagecontent-block-header .pagecontent-block-visibility-button').first().click();
                        }
                        setContainerElementsVisibility($(this), true);
                    });
                });

                $('#btn_action_collapse_all').on('click', function (e) {
                    $('#content > .pagecontent-block').each(function () {
                        if (!$(this).find('.pagecontent-block-content').first().hasClass('hidden')) {
                            $(this).find('.pagecontent-block-header .pagecontent-block-visibility-button').first().click();
                        }
                        setContainerElementsVisibility($(this), false);
                    });
                });

            }


            // **********************************************************************************************************
            // **********************************************************************************************************
            // Daten speichern
            // **********************************************************************************************************
            // **********************************************************************************************************

            function getContentBlockData(id) {
                var data = $('#' + id + '-data-editor').dataEditor('get');
                return (data);
            }

            function getContainerData(id) {
                var data = [];
                var count = 0;
                $('#' + id + '-container > .pagecontent-container-element').each(function () {
                    var elementId = $(this).attr('data-element-id');
                    if (elementId) {
                        var editor = $(this).find('.pagecontent-container-element-content-data-editor').first();
                        data[count] = {
                            'elementId': elementId,
                            'content': $(editor).dataEditor('get')
                        };
                        count++;
                    }
                });
                return (data);
            }

            $("#" + saveCallBackId).on("success.pixelmanager", function (event, data) {
                parent.pixelmanagerGlobal.pagetree.refresh();
                if (data.preview) {
                    // window.open(data.previewUrl, "_blank");
                    parent.pixelmanagerGlobal.openPagePreview(data.previewUrl);
                    window.location.reload();
                } else {
                    parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
                }
            });

            $("#" + saveCallBackId).on("fail.pixelmanager", function (event, data) {
                $('#btn_save').removeAttr('disabled');
                $('#btn_preview').removeAttr('disabled');
                displayErrorMessage(translate.get('An error occured: The page content could not be saved.'));
            });

            function save(preview) {
                var returnData = {};
                for (var id in loadedData.pageStructure) {
                    if (loadedData.pageStructure[id].type == 'datablock') {
                        returnData[id] = getContentBlockData(id);
                    } else if (loadedData.pageStructure[id].type == 'container') {
                        returnData[id] = getContainerData(id);
                    }
                }
                $('#btn_save').attr('disabled');
                $('#btn_preview').attr('disabled');
                parent.pixelmanagerGlobal.dataExchange.request(
                    translate.get('Saving page content'),
                    parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/pagecontent/update/',
                    {
                        'pageId': pageId,
                        'jsonData': JSON.stringify(returnData),
                        'preview': preview,
                        'previewLanguageId': parent.pixelmanagerGlobal.activeLanguage
                    },
                    saveCallBackId,
                    window.frameElement.id,
                    $
                );
            }

            $("#btn_save").click(function () {
                save(false);
            });

            $("#btn_preview").click(function () {
                save(true);
            });


            // **********************************************************************************************************
            // **********************************************************************************************************
            // Sprach-Umschaltung
            // **********************************************************************************************************
            // **********************************************************************************************************

            $("#" + eventListenerId).on("switchlanguage.pixelmanager", function (event, data) {
                $('.pagecontent-container, .pagecontent-container-element-content-data-editor').dataEditor(
                    'setLanguage',
                    data.languageId,
                    data.secondaryLanguageId
                );
            });

            // **********************************************************************************************************
            // **********************************************************************************************************
            // Sonstiges
            // **********************************************************************************************************
            // **********************************************************************************************************

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

            function removeErrorMessages() {
                $(".pixelmanager-error-container").empty();
            }

            function displayErrorMessage(message) {
                $(".pixelmanager-error-container").append('<div class="alert alert-danger">' + message + '</div>');
                $('.pixelmanager-iframe-content').scrollTop(0);
            }

            $("#btn_close").click(function () {
                parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
            });

            $(".pixelmanager-iframe-buttons .show-tooltip-btn-group").tooltip({container: 'body'});

            $("#content").tooltip({
                selector: '.show-tooltip',
                placement: 'right',
                animation: true
            });

        });
    });
