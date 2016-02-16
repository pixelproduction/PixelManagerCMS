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

(function ($) {

    'use strict';

    if (typeof($.fn.dataEditorPlugins) == 'undefined') {
        $.fn.dataEditorPlugins = {};
    }

    $.fn.dataEditorPlugins.link = function (method) {

        var defaultParameters = {
            showLinkButton: true,
            showDownloadButton: true,
            showNewWindow: true
        };

        var defaultValue = {
            url: '',
            newWindow: 'auto'
        };

        function onSelectLinkClick(event) {
            var targetId = $(event.target).attr('data-container-id');

            function linkSelectorCallback(url) {
                $('#' + targetId + '-link').val('link://' + url);
            }

            parent.pixelmanagerGlobal.openLinkSelector(linkSelectorCallback, false);
        }

        function onSelectDownloadClick(event) {
            var targetId = $(event.target).attr('data-container-id');

            function downloadSelectorCallback(file) {
                var relativePath = file.url;
                relativePath = relativePath.substr(file.baseUrl.length, (file.url.length - file.baseUrl.length));
                $('#' + targetId + '-link').val('download://' + relativePath);
            }

            parent.pixelmanagerGlobal.openDownloadSelector(downloadSelectorCallback, false);
        }

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {
                var parameters = {};
                var value = {};
                var loadedValue = {};

                $.extend(parameters, defaultParameters, assignedParameters);

                if (typeof(data) != 'undefined') {
                    loadedValue = data;
                }
                $.extend(value, defaultValue, loadedValue);

                if (parameters.showLinkButton && parameters.showDownloadButton) {
                    $('#' + containerId).append(
                        '<div class="control-group">' +
                        '<div class="input-group">' +
                        '<input id="' + containerId + '-link" class="form-control" type="text" value="">' +
                        '<span class="input-group-btn">' +
                        '<button class="btn btn-default btn-link-data-editor-plugin-select-link" type="button" data-container-id="' + containerId + '"><span class="glyphicon glyphicon-share"></span> ' + parent.pixelmanagerGlobal.translate.get('Link to page') + '</button>' +
                        '<button class="btn btn-default btn-link-data-editor-plugin-select-download" type="button" data-container-id="' + containerId + '"><span class="glyphicon glyphicon-folder-open"></span> ' + parent.pixelmanagerGlobal.translate.get('Download') + '</button>' +
                        '</span>' +
                        '</div>' +
                        '</div>'
                    );
                } else if (parameters.showLinkButton) {
                    $('#' + containerId).append(
                        '<div class="control-group">' +
                        '<div class="input-group">' +
                        '<input id="' + containerId + '-link" class="form-control" type="text" value="">' +
                        '<span class="input-group-btn">' +
                        '<button class="btn btn-default btn-link-data-editor-plugin-select-link" type="button" data-container-id="' + containerId + '"><span class="glyphicon glyphicon-share"></span> ' + parent.pixelmanagerGlobal.translate.get('Link to page') + '</button>' +
                        '</span>' +
                        '</div>' +
                        '</div>'
                    );
                } else if (parameters.showDownloadButton) {
                    $('#' + containerId).append(
                        '<div class="control-group">' +
                        '<div class="input-group">' +
                        '<input id="' + containerId + '-link" class="form-control" type="text" value="">' +
                        '<span class="input-group-btn">' +
                        '<button class="btn btn-default btn-link-data-editor-plugin-select-download" type="button" data-container-id="' + containerId + '"><span class="glyphicon glyphicon-folder-open"></span> ' + parent.pixelmanagerGlobal.translate.get('Download') + '</button>' +
                        '</span>' +
                        '</div>' +
                        '</div>'
                    );
                } else {
                    $('#' + containerId).append(
                        '<div class="control-group">' +
                        '<input id="' + containerId + '-link" class="form-control" type="text" value="">' +
                        '</div>'
                    );
                }

                $('#' + containerId + '-link').val(value.url);

                if (parameters.showNewWindow) {
                    $('#' + containerId).append(
                        '<div class="pixelmanager-radio-group">' +
                        parent.pixelmanagerGlobal.translate.get('Open in new window') + ': ' +
                        '<label><input type="radio" name="' + containerId + '-link-target" id="' + containerId + '-link-target-yes" value="yes"> ' + parent.pixelmanagerGlobal.translate.get('Yes') + '</label>' +
                        '<label><input type="radio" name="' + containerId + '-link-target" id="' + containerId + '-link-target-no" value="no"> ' + parent.pixelmanagerGlobal.translate.get('No') + '</label>' +
                        '<label><input type="radio" name="' + containerId + '-link-target" id="' + containerId + '-link-target-auto" value="auto"> ' + parent.pixelmanagerGlobal.translate.get('Automatic') + '</label>' +
                        '</div>'
                    );
                    $('#' + containerId + ' input[name="' + containerId + '-link-target"][value="' + value.newWindow + '"]').prop('checked', true);
                }
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-link-data-editor-plugin-select-link', onSelectLinkClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-link-data-editor-plugin-select-download', onSelectDownloadClick);

            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                $('#' + containerId).off('.' + eventNamespace);
            },

            getData: function (containerId, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var returnValue = {};
                $.extend(returnValue, defaultValue);
                returnValue.url = $('#' + containerId + '-link').val();
                if (parameters.showNewWindow) {
                    returnValue.newWindow = $('#' + containerId + ' input:radio[name="' + containerId + '-link-target"]:checked').val();
                }
                return (returnValue);
            },

            getRowHtml: function (data, assignedParameters) {
                var parameters = {};
                var value = {};
                var loadedValue = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                if (typeof(data) != 'undefined') {
                    loadedValue = data;
                }
                $.extend(value, defaultValue, loadedValue);
                return (value.url);
            },

            beforeMove: function (containerId, assignedParameters, eventNamespace) {
            },

            afterMove: function (containerId, assignedParameters, eventNamespace) {
            },

            sortCompareValues: function (value1, value2) {
                var url1 = '';
                var url2 = '';
                if (typeof(value1) != 'undefined') {
                    if (typeof(value1.url) != 'undefined') {
                        url1 = value1.url;
                    }
                }
                if (typeof(value2) != 'undefined') {
                    if (typeof(value2.url) != 'undefined') {
                        url2 = value2.url;
                    }
                }
                return (strnatcasecmp(url1, url2));
            },

            getSortableValue: function (data, assignedParameters) {
                return (this.getRowHtml(data, assignedParameters));
            }

        };

    }

})(jQuery);
