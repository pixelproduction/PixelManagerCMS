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

    $.fn.dataEditorPlugins.radioGroup = function (method) {

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
                $('#' + containerId).append('<div class="pixelmanager-radio-group"></div>');

                var divider = ' ';
                if (parameters.newLine) {
                    divider = '<br>';
                }

                if (parameters.radiobuttons != null) {
                    if (parameters.radiobuttons.length) {
                        for (var i = 0; i < parameters.radiobuttons.length; i++) {

                            var radiobutton = {};
                            var defaultRadiobutton = {
                                label: 'Radiobutton ' + i,
                                value: i.toString()
                            };
                            var assignedRadiobutton = parameters.radiobuttons[i];
                            $.extend(radiobutton, defaultRadiobutton, assignedRadiobutton);

                            $('#' + containerId + ' .pixelmanager-radio-group').append(
                                '<input id="' + containerId + '-radiobutton-radio' + i + '" name="' + containerId + '-radiogroup" type="radio" value="">&nbsp;' +
                                '<label for="' + containerId + '-radiobutton-radio' + i + '">' + radiobutton.label + '</label>' +
                                divider
                            );
                            $('#' + containerId + '-radiobutton-radio' + i).val(radiobutton.value);

                            if (value == radiobutton.value) {
                                $('#' + containerId + '-radiobutton-radio' + i).prop('checked', true);
                            }

                        }
                    }
                }

                $('#' + containerId + '-checkbox').prop('checked', value);
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            getData: function (containerId, assignedParameters) {
                return ($('#' + containerId + ' .pixelmanager-radio-group > input:radio:checked').val());
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

                if (parameters.radiobuttons != null) {
                    if (parameters.radiobuttons.length) {
                        for (var i = 0; i < parameters.radiobuttons.length; i++) {

                            var radiobutton = {};
                            var defaultRadiobutton = {
                                id: 'radio' + i,
                                label: 'Radiobutton ' + i,
                                value: i.toString()
                            };
                            var assignedRadiobutton = parameters.radiobuttons[i];
                            $.extend(radiobutton, defaultRadiobutton, assignedRadiobutton);

                            if (value == radiobutton.value) {
                                returnValue = radiobutton.label;
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
