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

    $.fn.dataEditorPlugins.colorPicker = function (method) {
        var defaultParameters = {
            node: '',
            style: '',
            color: ''
        };
        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = '';
                if (typeof(data) != 'undefined') {
                    if (data == "") {
                        value = parameters.defaultVal;
                    } else {
                        value = data;
                    }
                }
                $('#' + containerId).append(
                    '<div class="control-group colPick">' +
                    '<div class="input-group" id="' + containerId + '-cont">' +
                    '<input type="text" id="' + containerId + '-picker" value="" class="form-control" /><span class="input-group-addon"><i></i></span>' +
                    '</div>' +
                    '</div>'
                );


                if (value != "") {

                    $('#' + containerId + '-picker').val(value.color);
                }
                $('#' + containerId + '-cont').colorpicker();
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
                delete($('#' + containerId + '-picker'));
            },

            getData: function (containerId, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var returnValue = {};
                returnValue.color = $('#' + containerId + '-picker').val();
                if ((parameters.node instanceof Array) && (parameters.style instanceof Array)) {
                    returnValue.node = new Array();
                    returnValue.style = new Array();
                    parameters.node.forEach(function (e) {
                        returnValue.node.push(e);
                    });
                    parameters.style.forEach(function (e) {
                        returnValue.style.push(e);
                    });
                }
                return (returnValue);
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
