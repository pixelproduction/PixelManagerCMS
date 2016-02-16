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
    var dataExchange = require('modules/data-exchange');
    var tabs = require('modules/tabs');
    var bootstrap = require('plugins/jquery-ui-bootstrap-no-conflict');

    // Interne Funktionen und Variablen
    var backendModulesTabAvailable = false;
    var backendModulesMenuAvailable = false;
    var getTabContentCallbackId = null;
    var getMenuContentCallbackId = null;
    var modulesTabContainerSelector = '';
    var modulesMenuContainerSelector = '';
    var callbackElementSelector = '';

    function getTabContentSuccess(event, data) {
        if (backendModulesTabAvailable) {
            $(modulesTabContainerSelector + ' .pixelmanager-main-left-modules-content-wrapper')
                .empty()
                .append(data)
            ;
        }
    }

    function refreshTabContent() {
        if (backendModulesTabAvailable) {
            dataExchange.request(
                translate.get('Loading backend modules tab'),
                pixelmanagerGlobal.baseUrl + "admin/data-exchange/backendmodules/gettabcontent",
                {},
                getTabContentCallbackId
            );
        }
    }

    function getMenuContentSuccess(event, data) {
        if (backendModulesMenuAvailable) {
            $(modulesMenuContainerSelector)
                .empty()
                .append(data)
            ;
        }
    }

    function refreshMenuContent() {
        if (backendModulesMenuAvailable) {
            dataExchange.request(
                translate.get('Loading backend modules menu'),
                pixelmanagerGlobal.baseUrl + "admin/data-exchange/backendmodules/getmenucontent",
                {},
                getMenuContentCallbackId
            );
        }
    }

    return {

        // �ffentliche Funktionen

        init: function (newModulesTabContainerSelector, newModulesMenuContainerSelector, newCallbackElementSelector) {

            modulesTabContainerSelector = newModulesTabContainerSelector;
            modulesMenuContainerSelector = newModulesMenuContainerSelector;
            callbackElementSelector = newCallbackElementSelector;

            if ($(modulesTabContainerSelector).length > 0) {
                backendModulesTabAvailable = true;
                getTabContentCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
                $('#' + getTabContentCallbackId).on("success.pixelmanager", getTabContentSuccess);
                $(modulesTabContainerSelector).tabs();
                refreshTabContent();
            }

            if ($(modulesMenuContainerSelector).length > 0) {
                backendModulesMenuAvailable = true;
                getMenuContentCallbackId = dataExchange.createCallbackItem(callbackElementSelector);
                $('#' + getMenuContentCallbackId).on("success.pixelmanager", getMenuContentSuccess);
                refreshMenuContent();
            }

            $("body").on("click", ".open-module-in-tab", function (e) {
                var moduleId = $(e.target).attr('data-module-id');
                var moduleUrl = $(e.target).attr('data-module-url');
                var moduleName = $(e.target).attr('data-module-name');
                tabs.openTab(pixelmanagerGlobal.baseUrl + 'admin/modules/html-output/' + moduleUrl, moduleName, moduleId);
            });
        },

        refresh: function () {
            refreshTabContent();
            refreshMenuContent();
        }

    };

});