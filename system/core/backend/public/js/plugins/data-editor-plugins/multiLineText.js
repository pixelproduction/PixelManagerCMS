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

    $.fn.dataEditorPlugins.multiLineText = function (method) {

        var defaultParameters = {
            cols: 40,
            rows: 5
        };

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = '';
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                $('#' + containerId).append(
                    '<div class="control-group">' +
                    '<textarea id="' + containerId + '-text" class="form-control" type="text" cols="' + parameters.cols + '" rows="' + parameters.rows + '"></textarea>' +
                    '</div>'
                );
                $('#' + containerId + '-text').val(value);
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
                var i = 0;
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
                var i = 0;
            },

            getData: function (containerId, assignedParameters) {
                return ($('#' + containerId + '-text').val());
            },

            getRowHtml: function (data, assignedParameters) {
                var value = '';
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                return (value);
            },

            beforeMove: function (containerId, assignedParameters, eventNamespace) {
            },

            afterMove: function (containerId, assignedParameters, eventNamespace) {
            },

            sortCompareValues: function (value1, value2) {
                if ((value1 != null) && (value2 != null)) {
                    return (strnatcasecmp(value1, value2));
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
