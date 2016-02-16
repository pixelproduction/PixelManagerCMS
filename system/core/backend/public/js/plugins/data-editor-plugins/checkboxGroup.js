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

    $.fn.dataEditorPlugins.checkboxGroup = function (method) {

        var defaultParameters = {
            newLine: true,
            checkboxes: null
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
                }
                $('#' + containerId).append('<div class="pixelmanager-checkbox-group"></div>');

                var divider = ' ';
                if (parameters.newLine) {
                    divider = '<br>';
                }

                if (parameters.checkboxes != null) {
                    if (parameters.checkboxes.length) {
                        for (var i = 0; i < parameters.checkboxes.length; i++) {

                            var checkbox = {};
                            var defaultCheckbox = {
                                id: 'checkbox' + i,
                                label: 'Checkbox ' + i,
                                checked: false
                            };
                            var assignedCheckbox = parameters.checkboxes[i];
                            $.extend(checkbox, defaultCheckbox, assignedCheckbox);

                            $('#' + containerId + ' .pixelmanager-checkbox-group').append(
                                '<input id="' + containerId + '-checkbox-' + checkbox.id + '" type="checkbox" value="checked">&nbsp;' +
                                '<label for="' + containerId + '-checkbox-' + checkbox.id + '">' + checkbox.label + '</label>' +
                                divider
                            );

                            var checked = checkbox.checked;
                            if (value != null) {
                                if (typeof(value[checkbox.id]) != 'undefined') {
                                    checked = value[checkbox.id];
                                }
                            }

                            $('#' + containerId + '-checkbox-' + checkbox.id).prop('checked', checked);

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
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = null;
                if (parameters.checkboxes != null) {
                    if (parameters.checkboxes.length) {
                        value = {};

                        for (var i = 0; i < parameters.checkboxes.length; i++) {

                            var checkbox = {};
                            var defaultCheckbox = {
                                id: 'checkbox' + i,
                                label: 'Checkbox ' + i,
                                checked: false
                            };
                            var assignedCheckbox = parameters.checkboxes[i];

                            $.extend(checkbox, defaultCheckbox, assignedCheckbox);
                            value[checkbox.id] = $('#' + containerId + '-checkbox-' + checkbox.id).is(':checked');
                        }

                    }
                }
                return (value);
            },

            getRowHtml: function (data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = null;
                if (typeof(data) != 'undefined') {
                    value = data;
                }

                var returnValue = '';

                var divider = ', ';
                if (parameters.newLine) {
                    divider = '<br>';
                }

                if (parameters.checkboxes != null) {
                    if (parameters.checkboxes.length) {
                        for (var i = 0; i < parameters.checkboxes.length; i++) {

                            var checkbox = {};
                            var defaultCheckbox = {
                                id: 'checkbox' + i,
                                label: 'Checkbox ' + i,
                                checked: false
                            };
                            var assignedCheckbox = parameters.checkboxes[i];
                            $.extend(checkbox, defaultCheckbox, assignedCheckbox);

                            var checked = checkbox.checked;
                            if (typeof(value[checkbox.id]) != 'undefined') {
                                checked = value[checkbox.id];
                            }

                            if (returnValue != '') {
                                returnValue = returnValue + divider;
                            }
                            if (checked) {
                                returnValue = returnValue + checkbox.label + ': ' + parent.pixelmanagerGlobal.translate.get('Yes');
                            } else {
                                returnValue = returnValue + checkbox.label + ': ' + parent.pixelmanagerGlobal.translate.get('No');
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
                return (0);
            },

            getSortableValue: function (data, assignedParameters) {
                return (this.getRowHtml(data, assignedParameters));
            }

        };

    }

})(jQuery);
