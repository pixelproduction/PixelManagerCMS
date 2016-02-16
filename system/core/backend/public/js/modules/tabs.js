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
    var translate = require('modules/translate');
    var jquery_ui_bootstrap = require('plugins/jquery-ui-bootstrap-no-conflict');

    // Interne Variablen (aufrufende Module können nicht darauf zugreifen)
    var tabsSelector = '';
    var tabsId = "";
    var tabsCounter = 0;
    var eventListeners = {};

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

    function eventListenerExists(eventListenerId) {
        if (typeof(eventListeners[eventListenerId]) != "undefined") {
            return (true);
        } else {
            return (false);
        }
    }

    function addEventListener(eventListenerId, iFrameId, iFrameJQuery) {
        if (!eventListenerExists(eventListenerId)) {
            eventListeners[eventListenerId] = {
                iframe: iFrameId,
                jquery: iFrameJQuery
            };
        }
    }

    function removeEventListener(eventListenerId) {
        if (eventListenerExists(eventListenerId)) {
            delete(eventListeners[eventListenerId]);
        }
    }

    function triggerEvent(eventListenerId, event, eventData) {
        var eventListenerDoesNotExistAnymore = true;
        if (eventListenerExists(eventListenerId)) {
            var listener = eventListeners[eventListenerId];
            if (typeof(listener.iframe) != "undefined") {
                var iframe = $("#" + listener.iframe);
                if (iframe.length > 0) {
                    var callbackElement = $("#" + listener.iframe).contents().find("#" + eventListenerId);
                    if (callbackElement.length > 0) {
                        if (typeof(listener.jquery) != "undefined") {
                            callbackElement = listener.jquery("#" + eventListenerId);
                            if (callbackElement.length > 0) {
//								callbackElement.triggerHandler({type:event, data:eventData});
                                listener.jquery(callbackElement).triggerHandler(event, eventData);
                                eventListenerDoesNotExistAnymore = false;
                            }
                        }
                    }
                }
            }
        }
        return (!eventListenerDoesNotExistAnymore);
    }

    // Objekt zurückgeben (alles was hier zurückgegeben wird, ist für aufrufende Module sichtbar)
    return {

        frameIdPrefix: "frame-",

        init: function (id) {
            tabsId = id;
            tabsSelector = '#' + tabsId;
            $(tabsSelector).tabs();
            var init_this = this;
            $(tabsSelector).on("click", "span.ui-icon-close", function () {
                var id = $(this).parent().find('a').first().attr('href');
                id = id.substr(1, id.length - 1);
                init_this.closeTab(id);
            });
        },

        tabExists: function (id) {
            var panel = $(tabsSelector).find("#" + id).first();
            if (panel.length > 0) {
                return (true);
            }
            return (false);
        },

        selectTab: function (id) {
            if (this.tabExists(id)) {
                var panel = $(tabsSelector + ' #' + id);
                var index = $(tabsSelector + ' .ui-tabs-panel').index(panel);
                $(tabsSelector).tabs('option', 'active', index);
            }
        },

        closeTab: function (id) {
            if (this.tabExists(id)) {
                $(tabsSelector + ' > ul > li > a[href="#' + id + '"]').parents('li').first().remove();
                $(tabsSelector + ' #' + id).remove();
                $(tabsSelector).tabs('refresh');
            }
        },

        openTab: function (url, title, id) {
            if (typeof(id) != "undefined") {
                if (this.tabExists(id)) {
                    this.selectTab(id);
                    return;
                }
            }
            tabsCounter = tabsCounter + 1;
            if (typeof(id) != "undefined") {
                var newId = id;
            } else {
                var newId = "pixelmanager-tab-" + tabsCounter.toString();
            }
            $(tabsSelector + ' > ul').append('<li><a href="#' + newId + '">' + title + '</a> <span class="ui-icon ui-icon-close">Tab schließen</span></li>');
            $(tabsSelector).append(
                '<div id="' + newId + '">' +
                '<div class="pixelmanager-tab-iframe-wrapper">' +
                '<iframe id="' + this.frameIdPrefix + newId + '" class="pixelmanager-tab-iframe" src="' + url + '" frameborder="0"></iframe>' +
                '</div>' +
                '</div>'
            );
            $(tabsSelector).tabs('refresh');
            this.selectTab(newId);
        },

        getTabIdFromFrameId: function (frameId) {
            var id = '';
            if (typeof(frameId) != "undefined") {
                id = frameId.substr(this.frameIdPrefix.length, frameId.length - this.frameIdPrefix.length);
            }
            return (id);
        },

        getTabIdFromFrame: function (frame_window) {
            var id = '';
            if (typeof(frame_window) != "undefined") {
                var frameId = frame_window.frameElement.id;
                id = this.getTabIdFromFrameId(frameId);
            }
            return (id);
        },

        closeTabContainingFrame: function (frame_window) {
            var id = this.getTabIdFromFrame(frame_window);
            this.closeTab(id);
        },

        eventListener: function (container_selector, iframeId, iframeJQuery) {
            var elementId = getRandomId();
            var htmlCode = '<input type="hidden" id="' + elementId + '" value="">';
            $("#" + iframeId).contents().find(container_selector).append(htmlCode);
            addEventListener(elementId, iframeId, iframeJQuery)
            return (elementId);
        },

        broadcast: function (eventType, eventData) {
            var removeEventListeners = {};
            var listenerId = null;
            for (listenerId in eventListeners) {
                if (!triggerEvent(listenerId, eventType, eventData)) {
                    removeEventListeners[listenerId] = true;
                }
            }
            for (listenerId in removeEventListeners) {
                removeEventListener(listenerId);
            }
        }

    };
});