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

    $.fn.dataEditorPlugins.datePicker = function (method) {

        var defaultParameters = {
            time: false
        };

        function convertStringToDate(dateString) {
            if (typeof(dateString) != 'undefined') {
                var date = null;
                var dateArray = dateString.split('-');
                if (dateArray.length >= 3) {
                    // Für JavaScripts Date-Objekt gilt: Monate beginnen bei 0 und Enden bei 11.
                    date = new Date(parseInt(dateArray[0], 10), parseInt(dateArray[1], 10) - 1, parseInt(dateArray[2], 10));
                    if (dateArray.length == 5) {
                        date.setHours(parseInt(dateArray[3], 10));
                        date.setMinutes(parseInt(dateArray[4], 10));
                    }
                }
                return (date);
            } else {
                return (null);
            }
        }

        function parseFormat(format) {
            var validParts = /dd?|mm?|yy(?:yy)?/g;
            // IE treats \0 as a string end in inputs (truncating the value),
            // so it's a bad format delimiter, anyway
            var separators = format.replace(validParts, '\0').split('\0'),
                parts = format.match(validParts);
            if (!separators || !separators.length || !parts || parts.length === 0) {
                throw new Error("Invalid date format.");
            }
            return {separators: separators, parts: parts};
        }

        function formatDate(date, format) {
            if (!date)
                return '';
            if (typeof format === 'string')
                format = parseFormat(format);
            var val = {
                d: date.getDate(),
                m: date.getMonth() + 1,
                yy: date.getFullYear().toString().substring(2),
                yyyy: date.getFullYear()
            };
            val.dd = (val.d < 10 ? '0' : '') + val.d;
            val.mm = (val.m < 10 ? '0' : '') + val.m;
            date = [];
            var seps = $.extend([], format.separators);
            for (var i = 0, cnt = format.parts.length; i <= cnt; i++) {
                if (seps.length)
                    date.push(seps.shift());
                date.push(val[format.parts[i]]);
            }
            return date.join('');
        };

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {
                var parameters = {};
                var value = '';
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                var date = null;
                if (value != '') {
                    date = convertStringToDate(value);
                } else {
                    date = new Date();
                }

                $.extend(parameters, defaultParameters, assignedParameters);

                $('#' + containerId).append(
                    '<div class="control-group form-inline">' +
                    '<input id="' + containerId + '-datepicker" type="text" class="form-control input-large" autocomplete="off"> ' +
                    (parameters.time === true ? '<input id="' + containerId + '-timepicker" type="text" class="form-control input-medium" autocomplete="off">' : '') +
                    '</div>'
                );

                $('#' + containerId + '-datepicker').bootstrapDP({
                    todayBtn: "linked",
                    format: parent.pixelmanagerGlobal.translate.get('dd/mm/yyyy'),
                    language: parent.pixelmanagerGlobal.backendLanguage,
                    autoclose: true,
                    todayHighlight: true,
                    weekStart: 1
                });
                $('#' + containerId + '-datepicker').bootstrapDP('setDate', date);

                if (parameters.time === true) {
                    $('#' + containerId + '-timepicker').timepicker({
                        minTime: 0,
                        timeFormat: 'H:i'
                    });
                    $('#' + containerId + '-timepicker').timepicker('setTime', date);
                }

            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                $('#' + containerId + '-datepicker').bootstrapDP('remove');
                if (parameters.time === true) {
                    $('#' + containerId + '-timepicker').timepicker('remove');
                }
            },

            getData: function (containerId, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var date = $('#' + containerId + '-datepicker').bootstrapDP('getDate');
                var dateTimeString = formatDate(date, 'yyyy-m-d');
                if (parameters.time === true) {
                    var time = $('#' + containerId + '-timepicker').timepicker('getTime', date);
                    var hours = time.getHours().toString();
                    var minutes = time.getMinutes().toString();
                    dateTimeString = dateTimeString + '-' + (hours.length == 1 ? '0' : '') + hours + '-' + (minutes.length == 1 ? '0' : '') + minutes;
                }
                return (dateTimeString);
            },

            getRowHtml: function (data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = '';
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                if (value != '') {
                    var date = convertStringToDate(value);
                    var dateTimeString = formatDate(date, parent.pixelmanagerGlobal.translate.get('dd/mm/yyyy'));
                    if (parameters.time === true) {
                        var hours = date.getHours().toString();
                        var minutes = date.getMinutes().toString();
                        dateTimeString = dateTimeString + ' ' + (hours.length == 1 ? '0' : '') + hours + ':' + (minutes.length == 1 ? '0' : '') + minutes;
                    }
                    return (dateTimeString);
                }
                return ('');
            },

            beforeMove: function (containerId, assignedParameters, eventNamespace) {
            },

            afterMove: function (containerId, assignedParameters, eventNamespace) {
            },

            sortCompareValues: function (value1, value2) {
                var date1 = convertStringToDate(value1);
                var date2 = convertStringToDate(value2);
                if ((date1 != null) && (date2 != null)) {
                    if (date1.getTime() < date2.getTime()) {
                        return (-1)
                    } else if (date1.getTime() > date2.getTime()) {
                        return (1);
                    } else {
                        return (0);
                    }
                } else {
                    return (0);
                }
            },

            getSortableValue: function (data, assignedParameters) {
                var dateArray = data.split('-');
                var sortString = '';
                if (dateArray.length >= 3) {

                    // Jahr (immer 4 Stellig)
                    sortString = dateArray[0];

                    // Monat (1-2 Stellig, 0 ergänzen wenn nötig)
                    if (dateArray[1].length == 1) {
                        sortString = sortString + '0';
                    }
                    sortString = sortString + dateArray[1];

                    // Tag (1-2 Stellig, 0 ergänzen wenn nötig)
                    if (dateArray[2].length == 1) {
                        sortString = sortString + '0';
                    }
                    sortString = sortString + dateArray[2];

                    // Möglicherweise ist noch eine Zeitangabe enthalten
                    if (dateArray.length == 5) {
                        // Stunde (1-2 Stellig, 0 ergänzen wenn nötig)
                        if (dateArray[3].length == 1) {
                            sortString = sortString + '0';
                        }
                        sortString = sortString + dateArray[3];

                        // Minute (1-2 Stellig, 0 ergänzen wenn nötig)
                        if (dateArray[4].length == 1) {
                            sortString = sortString + '0';
                        }
                        sortString = sortString + dateArray[4];
                    }
                }
                return (sortString);
            }

        };

    }

})(jQuery);
