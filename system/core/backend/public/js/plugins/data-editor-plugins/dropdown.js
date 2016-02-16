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

    $.fn.dataEditorPlugins.dropdown = function (method) {

        var defaultParameters = {
            newLine: true,
            selectedValue: '',
            radiobuttons: null
        };

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = null;
                if (typeof(data) != 'undefined') {
                    value = data;
                } else {
                    value = parameters.selectedValue;
                }
                $('#' + containerId).append('<div class="control-group"><select id="' + containerId + '-select" class="form-control"></select></div>');

                if (parameters.options != null) {
                    if (parameters.options.length) {
                        for (var i = 0; i < parameters.options.length; i++) {

                            var option = {};
                            var defaultOption = {
                                label: 'Option ' + i,
                                value: i.toString()
                            };
                            var assignedOption = parameters.options[i];
                            $.extend(option, defaultOption, assignedOption);

                            $('#' + containerId + '-select').append('<option id="' + containerId + '-select-opt-' + i + '" value=""></option>');
                            $('#' + containerId + '-select-opt-' + i)
                                .val(option.value)
                                .html(option.label)
                            ;

                            if (value == option.value) {
                                $('#' + containerId + '-select-opt-' + i).attr('selected', true);
                            }

                        }
                    }
                }
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            getData: function (containerId, assignedParameters) {
                return ($('#' + containerId + '-select').val());
            },

            getRowHtml: function (data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = null;
                if (typeof(data) != 'undefined') {
                    value = data;
                } else {
                    value = parameters.selectedValue;
                }

                var returnValue = '';

                if (parameters.options != null) {
                    if (parameters.options.length) {
                        for (var i = 0; i < parameters.options.length; i++) {

                            var option = {};
                            var defaultOption = {
                                label: 'Option ' + i,
                                value: i.toString()
                            };
                            var assignedOption = parameters.options[i];
                            $.extend(option, defaultOption, assignedOption);

                            if (value == option.value) {
                                returnValue = option.label;
                                break;
                            }

                        }
                    }
                }

                return (returnValue);
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
