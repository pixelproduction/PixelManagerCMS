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

    $.fn.dataEditorPlugins.checkbox = function (method) {

        var defaultParameters = {
            checked: false,
            label: parent.pixelmanagerGlobal.translate.get('Yes')
        };

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = parameters.checked;
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                $('#' + containerId).append(
                    '<div class="pixelmanager-checkbox-group">' +
                    '<input id="' + containerId + '-checkbox" type="checkbox" value="checked"> ' +
                    '<label for="' + containerId + '-checkbox">' + parameters.label + '</label>' +
                    '</div>'
                );
                $('#' + containerId + '-checkbox').prop('checked', value);
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            getData: function (containerId, assignedParameters) {
                return ($('#' + containerId + '-checkbox').is(':checked'));
            },

            getRowHtml: function (data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = parameters.checked;
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                if (value == true) {
                    return (parent.pixelmanagerGlobal.translate.get('Yes'));
                } else {
                    return (parent.pixelmanagerGlobal.translate.get('No'));
                }
            },

            beforeMove: function (containerId, assignedParameters, eventNamespace) {
            },

            afterMove: function (containerId, assignedParameters, eventNamespace) {
            },

            sortCompareValues: function (value1, value2) {
                if ((value1 != null) && (value2 != null)) {
                    if (value1 < value2) {
                        return (-1)
                    } else if (value1 > value2) {
                        return (1);
                    } else {
                        return (0);
                    }
                } else {
                    return (0);
                }
            },

            getSortableValue: function (data, assignedParameters) {
                return (this.getRowHtml(data, assignedParameters));
            }

        };

    }

})(jQuery);
