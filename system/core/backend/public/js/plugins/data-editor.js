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

    var defaultOptions = {
        'content': {},
        'structure': {},
        'languages': null,
        'standardLanguage': null,
        'activeLanguage': null,
        'activeSecondaryLanguage': null,
        'preferredLanguageSubstitutes': null,
        'languageIcons': null,
        'buttonAddTranslation': '<button class="data-editor-add-translation">+</button>',
        'buttonRemoveTranslation': '<button class="data-editor-remove-translation">-</button>',
        'helpTextPrepend': '<br>',
        'helpTextAppend': '',
        'noTranslationAvailablePlaceholderText': 'No translation defined yet',
        'arrays': {
            'tableClasses': '',
            'emptyContent': 'No content yet',
            'subEditorHeadlineNew': '<h1>New row</h1>',
            'subEditorHeadlineEdit': '<h1>Edit row</h1>',
            'buttons': '<div>' +
            '<button class="data-editor-array-add-row">Add new</button>' +
            '<label><input type="checkbox" class="data-editor-array-insert-at-top"> Insert at the top</label>' +
            '<button class="data-editor-array-select-all">Select all</button>' +
            '<button class="data-editor-array-select-none">Select none</button>' +
            '<button class="data-editor-array-selected-up">Move selected up</button>' +
            '<button class="data-editor-array-selected-down">Move slected Down</button>' +
            '<button class="data-editor-array-selected-first">Move selected to top</button>' +
            '<button class="data-editor-array-selected-last">Move selected to bottom</button>' +
            '<button class="data-editor-array-selected-delete">Delete selected</button>' +
            '</div>',
            'rowButtons': '<div class="data-editor-array-row-buttons">' +
            '<button class="data-editor-array-row-button-up">Up</button>' +
            '<button class="data-editor-array-row-button-down">Down</button>' +
            '<button class="data-editor-array-row-button-first">First</button>' +
            '<button class="data-editor-array-row-button-last">Last</button>' +
            '<button class="data-editor-array-row-button-delete">Delete</button>' +
            '</div>',
            'buttonsOrdered': '<div>' +
            '<button class="data-editor-array-add-row">Add new</button>' +
            '<input type="hidden" class="data-editor-array-insert-at-top" value="">' +
            '<button class="data-editor-array-select-all">Select all</button>' +
            '<button class="data-editor-array-select-none">Select none</button>' +
            '<button class="data-editor-array-selected-delete">Delete selected</button>' +
            '</div>',
            'rowButtonsOrdered': '<div class="data-editor-array-row-buttons">' +
            '<button class="data-editor-array-row-button-delete">Delete</button>' +
            '</div>',
            'subEditorButtons': '<div class="data-editor-array-subeditor-buttons">' +
            '<button class="data-editor-array-ok">OK</button>' +
            '<button class="data-editor-array-cancel">Cancel</button>' +
            '</div>',
            'appendSubEditorTo': 'body'
        },
        'isSubEditor': false
    };

    var plugins = [];
    var lastCreatedRow = 0;

    function getOptions(instanceContainer) {
        var options = instanceContainer.data('dataEditorOptions');
        if (!options) {
            return (null);
        }
        return (options);
    }

    function getLanguages(instanceContainer) {
        var options = getOptions(instanceContainer);
        if (options == null) {
            return (null);
        }
        if (options.languages) {
            return (options.languages);
        }
        return (null);
    }

    function getStandardLanguage(instanceContainer) {
        var options = getOptions(instanceContainer);
        if (options == null) {
            return (null);
        }
        if ((options.standardLanguages) || (options.standardLanguages != null)) {
            return (options.standardLanguage);
        }
        if (( !options.languages) || (options.languages == null)) {
            return (null);
        }
        if (options.languages.length < 1) {
            return (null);
        }
        return (options.languages[0]);
    }

    function getActiveLanguage(instanceContainer) {
        var activeLanguage = instanceContainer.data('dataEditorActiveLanguage');
        if (activeLanguage) {
            return (activeLanguage);
        }
        var options = getOptions(instanceContainer);
        if (options == null) {
            return (null);
        }
        if ((options.activeLanguage) && (options.activeLanguage != null)) {
            return (options.activeLanguage);
        }
        if (( !options.languages) || (options.languages == null)) {
            return (null);
        }
        if (options.languages.length < 1) {
            return (null);
        }
        return (options.languages[0]);
    }

    function getActiveSecondaryLanguage(instanceContainer) {
        var activeSecondaryLanguage = instanceContainer.data('dataEditorActiveSecondaryLanguage');
        if (activeSecondaryLanguage) {
            return (activeSecondaryLanguage);
        }
        return (null);
    }

    function getPreferredLanguageSubstitutes(instanceContainer, languageId) {
        var options = getOptions(instanceContainer);
        if (options == null) {
            return (null);
        }
        if ((options.preferredLanguageSubstitutes) || (options.preferredLanguageSubstitutes != null)) {
            if (typeof(languageId) != 'undefined') {
                if (typeof(options.preferredLanguageSubstitutes[languageId]) != 'undefined') {
                    return (options.preferredLanguageSubstitutes[languageId]);
                } else {
                    return (null);
                }
            } else {
                return (options.preferredLanguageSubstitutes);
            }
        }
        return (null);
    }

    function getLanguageIcon(instanceContainer, languageId) {
        var options = getOptions(instanceContainer);
        if (options == null) {
            return (null);
        }
        if ((options.languageIcons) || (options.languageIcons != null)) {
            if (typeof(languageId) != 'undefined') {
                if (typeof(options.languageIcons[languageId]) != 'undefined') {
                    return (options.languageIcons[languageId]);
                } else {
                    return (null);
                }
            } else {
                return (null);
            }
        }
        return (null);
    }

    function getStructureByFieldId(instanceContainer, fieldId, structure) {
        if (typeof(structure) == 'undefined') {
            var options = getOptions(instanceContainer);
            if (options == null) {
                return (null);
            }
            if (typeof(options.structure) == 'undefined') {
                return (null);
            }
            var structure = options.structure;
        }
        for (var i = 0; i < structure.length; i++) {
            if (typeof(structure[i].id) != 'undefined') {
                if (structure[i].id == fieldId) {
                    return (structure[i]);
                }
            }
        }
        return (null);
    }

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
        return (str);
    }

    function getPlugin(pluginId) {
        if (typeof(plugins[pluginId]) != 'undefined') {
            return (plugins[pluginId]);
        } else {
            if (typeof($.fn.dataEditorPlugins[pluginId]) != 'undefined') {
                plugins[pluginId] = $.fn.dataEditorPlugins[pluginId]();
                plugins[pluginId].init();
                return (plugins[pluginId]);
            } else {
                return (null);
            }
        }
    }

    function callPluginFunction(pluginId, functionName) {
        var plugin = getPlugin(pluginId);
        if (plugin != null) {
            return (plugin[functionName].apply(plugin, Array.prototype.slice.call(arguments, 2)));
        }
    }

    function getArrayColumns(parameters) {
        var columns = [];
        if (typeof(parameters.columns) == 'undefined') {
            for (var i = 0; i < parameters.fields.length; i++) {
                columns[i] = {
                    'caption': parameters.fields[i].caption,
                    'fieldId': parameters.fields[i].id
                };
            }
        } else {
            columns = parameters.columns;
        }
        return (columns);
    }

    function getArrayOrderBy(parameters) {
        var orderBy = [];
        if (typeof(parameters.orderBy) == 'undefined') {
            orderBy = {
                'fieldId': '',
                'direction': 'asc'
            }
        } else {
            orderBy = jQuery.extend(true, {}, parameters.orderBy);
        }
        if (typeof(orderBy.fieldId) == 'undefined') {
            orderBy.fieldId = '';
        }
        if (typeof(orderBy.direction) == 'undefined') {
            orderBy.direction = 'asc';
        }
        if (typeof(orderBy.additional) == 'undefined') {
            orderBy.additional = [];
        }
        if (orderBy.additional.length > 0) {
            var i = 0;
            for (i = 0; i < orderBy.additional.length; i++) {
                if (typeof(orderBy.additional[i].fieldId) == 'undefined') {
                    orderBy.additional[i].fieldId = '';
                }
                if (typeof(orderBy.additional[i].direction) == 'undefined') {
                    orderBy.additional[i].direction = 'asc';
                }
            }
        }
        return (orderBy);
    }

    function getArrayRowCells(container, instanceId, rowContainerId, rowData, parameters, selected) {

        var languages = getLanguages(container);
        var options = getOptions(container);
        var columns = getArrayColumns(parameters);
        var orderBy = getArrayOrderBy(parameters);
        var html = '';
        var tbody = $('#' + rowContainerId + ' > table.data-editor-row-array > tbody');

        var checked = '';
        if (selected) {
            checked = ' checked';
        }
        html = html + '<td class="data-editor-row-array-ignore-click"><input type="checkbox" rowData="1" class="data-editor-row-array-selected-row"' + checked + '></td>';

        for (var i = 0; i < columns.length; i++) {
            html = html + '<td>';
            var fieldStructure = getStructureByFieldId(container, columns[i].fieldId, parameters.fields);
            var fieldData = undefined;
            if (typeof(rowData) != 'undefined') {
                if (typeof(rowData[columns[i].fieldId]) != 'undefined') {
                    fieldData = rowData[columns[i].fieldId];
                }
            }
            if (fieldStructure != null) {
                var fieldParameters = undefined;
                if (typeof(fieldStructure.parameters) != 'undefined') {
                    fieldParameters = fieldStructure.parameters;
                }
                var untranslatable = false;
                if (typeof(fieldStructure.untranslatable) != 'undefined') {
                    untranslatable = fieldStructure.untranslatable;
                }
                if ((languages != null) && ( !untranslatable)) {
                    for (var languageIndex = 0; languageIndex < languages.length; languageIndex++) {
                        var translationData = undefined;
                        if (typeof(fieldData) != 'undefined') {
                            if (typeof(fieldData[languages[languageIndex]]) != 'undefined') {
                                translationData = fieldData[languages[languageIndex]];
                            }
                        }
                        if (typeof(translationData) != 'undefined') {
                            html = html +
                                '<div class="data-editor-row-array-field data-editor-row-array-field-language-' + languages[languageIndex] + '">' +
                                callPluginFunction(fieldStructure.type, 'getRowHtml', translationData, fieldParameters) +
                                '</div>';
                        }
                    }
                } else {
                    var data = undefined;
                    if (typeof(fieldData) != 'undefined') {
                        data = fieldData;
                    }
                    var untranslatableClass = '';
                    if (untranslatable) {
                        untranslatableClass = ' data-editor-row-array-field-untranslatable';
                    }
                    html = html +
                        '<div class="data-editor-row-array-field' + untranslatableClass + '">' +
                        callPluginFunction(fieldStructure.type, 'getRowHtml', data, fieldParameters) +
                        '</div>';
                }
            }
            html = html + '</td>';
        }

        var buttonsCode = options.arrays.rowButtons;
        if (orderBy.fieldId != '') {
            buttonsCode = options.arrays.rowButtonsOrdered;
        }

        html = html + '<td class="data-editor-row-array-ignore-click">' + buttonsCode + '</td>';

        return (html);
    }

    function appendRowToArray(container, instanceId, rowContainerId, rowData, parameters, insertAtTop) {
        var html = '';
        var tbody = $('#' + rowContainerId + ' > table.data-editor-row-array > tbody');
        $(tbody).find('.data-editor-row-array-empty').remove();
        var rowTrId = rowContainerId + '-array-row-' + getRandomId();
        html = html +
            '<tr id="' + rowTrId + '">' +
            getArrayRowCells(
                container,
                instanceId,
                rowContainerId,
                rowData,
                parameters,
                false
            ) +
            '</tr>';

        if (insertAtTop) {
            $(tbody).prepend(html);
        } else {
            $(tbody).append(html);
        }
        $('#' + rowTrId).data('rowData', rowData);
        return (rowTrId);
    }

    function updateRowInArray(container, instanceId, rowContainerId, arrayRowId, rowData, parameters) {
        var html = '';
        var tbody = $('#' + rowContainerId + ' > table.data-editor-row-array > tbody');
        var selected = $('#' + arrayRowId).find('.data-editor-row-array-selected-row').first().is(':checked');
        html = getArrayRowCells(
            container,
            instanceId,
            rowContainerId,
            rowData,
            parameters,
            selected
        );
        $('#' + arrayRowId).empty().append(html);
        $('#' + arrayRowId).data('rowData', rowData);
    }

    function getArrayOrderByFieldData(container, rowData, orderByFieldId, preferredLanguageId, fieldStructure) {
        var languages = getLanguages(container);
        var standardLanguage = getStandardLanguage(container);
        var preferredLanguageSubstitutes = getPreferredLanguageSubstitutes(container, preferredLanguageId);
        var fieldData = null;
        var untranslatable = false;
        if (typeof(fieldStructure.untranslatable) != 'undefined') {
            untranslatable = fieldStructure.untranslatable;
        }
        if (typeof(rowData[orderByFieldId]) != 'undefined') {
            if ((languages != null) && ( !untranslatable)) {
                if (typeof(rowData[orderByFieldId][preferredLanguageId]) != 'undefined') {
                    fieldData = rowData[orderByFieldId][preferredLanguageId];
                } else {
                    var substitutedLanguageFound = false;
                    if (preferredLanguageSubstitutes != null) {
                        for (var i = 0; i < preferredLanguageSubstitutes.length; i++) {
                            if (typeof(rowData[orderByFieldId][preferredLanguageSubstitutes[i]]) != 'undefined') {
                                fieldData = rowData[orderByFieldId][preferredLanguageSubstitutes[i]];
                                substitutedLanguageFound = true;
                                break;
                            }
                        }
                    }
                    if (!substitutedLanguageFound) {
                        var languages = getLanguages(container);
                        for (var i = 0; i < languages.length; i++) {
                            if (typeof(rowData[orderByFieldId][languages[i]]) != 'undefined') {
                                fieldData = rowData[orderByFieldId][languages[i]];
                                substitutedLanguageFound = true;
                                break;
                            }
                        }
                    }
                }
            } else {
                fieldData = rowData[orderByFieldId];
            }
        }
        return (fieldData);
    }

    function sortCompareArrayItems(a, b, orderBy, orderByFieldType) {
        var value1 = $(a).data('orderByFieldData');
        var value2 = $(b).data('orderByFieldData');
        var compareResult = callPluginFunction(orderByFieldType, 'sortCompareValues', value1, value2);
        if (orderBy.direction == 'desc') {
            compareResult = compareResult * (-1);
        }
        if (compareResult == 0) {
            if (orderBy.additional.length > 0) {
                var additionalValues1 = $(a).data('orderByAdditionalFieldData');
                var additionalFieldTypes = $(a).data('orderByAdditionalFieldTypes');
                var additionalValues2 = $(b).data('orderByAdditionalFieldData');
                var i = 0;
                for (i = 0; i < orderBy.additional.length; i++) {
                    if (orderBy.additional[i].fieldId != '') {
                        var compareResult = callPluginFunction(additionalFieldTypes[i], 'sortCompareValues', additionalValues1[i], additionalValues2[i]);
                        if (orderBy.additional[i].direction == 'desc') {
                            compareResult = compareResult * (-1);
                        }
                        if (compareResult != 0) {
                            break;
                        }
                    }
                }
            }
        }
        return (compareResult);
    }

    function orderArrayItems(tbody, orderBy, orderByFieldType) {
        $(tbody).children('tr')
            .sort(
                function (a, b) {
                    return (sortCompareArrayItems(a, b, orderBy, orderByFieldType));
                }
            )
            .appendTo(tbody)
        ;
    }

    function orderArrayData(container, instanceId, rowContainerId, parameters, languageId) {
        var orderBy = getArrayOrderBy(parameters);
        if (typeof(languageId) == 'undefined') {
            var languageId = getActiveLanguage(container);
        }

        if (orderBy.fieldId != '') {

            var rowContainer = $('#' + rowContainerId);
            var tbody = $(rowContainer).find('table.data-editor-row-array > tbody');

            var orderFieldStructure = getStructureByFieldId(container, orderBy.fieldId, parameters.fields);
            if (orderFieldStructure != null) {
                var orderByFieldType = orderFieldStructure.type;

                $(tbody).children('tr').each(function () {
                    var rowData = $(this).data('rowData');
                    if (rowData) {
                        var orderByFieldData = getArrayOrderByFieldData(container, rowData, orderBy.fieldId, languageId, orderFieldStructure);
                        $(this).data('orderByFieldData', orderByFieldData);
                        var orderByAdditionalFieldData = [];
                        var orderByAdditionalFieldTypes = [];
                        if (orderBy.additional.length > 0) {
                            var i = 0;
                            for (i = 0; i < orderBy.additional.length; i++) {
                                var orderByAdditionalFieldStructure = getStructureByFieldId(container, orderBy.additional[i].fieldId, parameters.fields);
                                orderByAdditionalFieldData[i] = getArrayOrderByFieldData(container, rowData, orderBy.additional[i].fieldId, languageId, orderByAdditionalFieldStructure);
                                if (orderByAdditionalFieldStructure != null) {
                                    orderByAdditionalFieldTypes[i] = orderByAdditionalFieldStructure.type;
                                } else {
                                    orderByAdditionalFieldTypes[i] = null;
                                }
                            }
                        }
                        $(this).data('orderByAdditionalFieldData', orderByAdditionalFieldData);
                        $(this).data('orderByAdditionalFieldTypes', orderByAdditionalFieldTypes);
                    }
                });

                orderArrayItems(tbody, orderBy, orderByFieldType);
            }

        }
    }

    function appendArrayToRowContainer(container, instanceId, rowContainerId, value, parameters) {
        if (typeof(parameters) == 'undefined') {
            return;
        }
        if (typeof(parameters.fields) == 'undefined') {
            return;
        }
        if (typeof(parameters.fields.length) < 1) {
            return;
        }

        var options = getOptions(container);
        var columns = getArrayColumns(parameters);
        var orderBy = getArrayOrderBy(parameters);
        var html = '';
        var data = [];

        if (typeof(value) != 'undefined') {
            data = value;
        }

        if (data === null) {
            data = [];
        }

        var buttonsCode = options.arrays.buttons;
        if (orderBy.fieldId != '') {
            buttonsCode = options.arrays.buttonsOrdered;
        }

        html = html + buttonsCode;

        html = html +
            '<table class="data-editor-row-array ' + options.arrays.tableClasses + '">' +
            '<thead>' +
            '<tr>' +
            '<th>&nbsp;</th>';

        for (var i = 0; i < columns.length; i++) {
            html = html + '<th>' + columns[i].caption + '</th>';
        }

        html = html +
            '<th>&nbsp;</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody>';

        if (data.length < 1) {
            var colNum = columns.length + 2;
            html = html +
                '<tr class="data-editor-row-array-empty">' +
                '<td colspan="' + colNum.toString() + '">' +
                options.arrays.emptyContent +
                '</td>' +
                '</tr>';
        }

        html = html +
            '</tbody>' +
            '</table>';

        $('#' + rowContainerId).append(html);

        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                appendRowToArray(
                    container,
                    instanceId,
                    rowContainerId,
                    data[i],
                    parameters,
                    false
                );
            }
        }

        orderArrayData(container, instanceId, rowContainerId, parameters);

    }

    function appendRow(container, instanceId, type, fieldId, id, caption, value, parameters, help, untranslatable, hidden) {

        var languages = getLanguages(container);
        var activeLanguage = getActiveLanguage(container);
        var options = getOptions(container);

        var captionAndHelpText = caption;
        if (help != '') {
            captionAndHelpText = captionAndHelpText + options.helpTextPrepend + help + options.helpTextAppend;
        }

        var hiddenClass = '';
        if (hidden === true) {
            hiddenClass = 'data-editor-row-hidden';
        }

        if ((languages == null) || (type == 'array') || (untranslatable)) {
            var rowId = 'data-editor-row-' + id;
            var rowContainerId = 'data-editor-container-' + id;
            var arrayClass = '';

            if (type == 'array') {
                arrayClass = 'data-editor-row-contains-array';
            }

            container.append(
                '<div class="data-editor-row clearfix ' + hiddenClass + ' ' + arrayClass + '" id="' + rowId + '" data-field-id="' + fieldId + '" data-type="' + type + '">' +
                '<div class="data-editor-row-caption">' + captionAndHelpText + '</div>' +
                '<div class="data-editor-row-container-wrapper clearfix">' +
                '<div class="data-editor-row-container" id="' + rowContainerId + '"></div>' +
                '</div>' +
                '</div>'
            );

            if (type == 'array') {
                appendArrayToRowContainer(
                    container,
                    instanceId,
                    rowContainerId,
                    value,
                    parameters
                );
                if (languages != null) {
                    updateRowLanguage(container, rowId)
                }
            } else {
                callPluginFunction(type, 'insertHtml', rowContainerId, value, parameters);
                callPluginFunction(type, 'bindEvents', rowContainerId, parameters, instanceId);
            }
            lastCreatedRow++;

        } else {

            var rowId = 'data-editor-row-' + id;
            var rowContainerHtml = '';
            var rowContainerId = [];
            var rowIsEmpty = true;
            var definedLanguageValues = 0;

            var valueDefined = [];
            for (var i = 0; i < languages.length; i++) {
                valueDefined[i] = false;
                if (typeof(value) != 'undefined') {
                    if (value != null) {
                        if (typeof(value[languages[i]]) != 'undefined') {
                            valueDefined[i] = true;
                        }
                    }
                }
            }

            for (var i = 0; i < languages.length; i++) {
                if (valueDefined[i]) {
                    rowIsEmpty = false;
                    definedLanguageValues++;
                }
            }

            for (var i = 0; i < languages.length; i++) {
                rowContainerId[i] = 'data-editor-container-' + id + '-' + languages[i];
                if (( (valueDefined[i])) || ( (rowIsEmpty) && (languages[i] == activeLanguage) )) {
                    rowContainerHtml = rowContainerHtml + '<div class="data-editor-row-container data-editor-row-container-language-' + languages[i] + '" id="' + rowContainerId[i] + '"></div>';
                }
            }

            container.append(
                '<div class="data-editor-row ' + hiddenClass + ' clearfix" id="' + rowId + '" data-field-id="' + fieldId + '" data-type="' + type + '">' +
                '<div class="data-editor-row-caption">' + captionAndHelpText + '</div>' +
                '<div class="data-editor-row-container-wrapper clearfix">' +
                rowContainerHtml +
                '<div class="data-editor-row-translation-button data-editor-row-translation-button-left"></div>' +
                '<div class="data-editor-row-translation-button data-editor-row-translation-button-right"></div>' +
                '</div>' +
                '</div>'
            );

            for (var i = 0; i < languages.length; i++) {
                if ((valueDefined[i]) || ( (rowIsEmpty) && (languages[i] == activeLanguage) )) {
                    var elementValue = undefined;
                    if (valueDefined[i]) {
                        elementValue = value[languages[i]];
                    }
                    callPluginFunction(type, 'insertHtml', rowContainerId[i], elementValue, parameters);
                    callPluginFunction(type, 'bindEvents', rowContainerId[i], parameters, instanceId);
                    lastCreatedRow++;
                }
            }

            updateRowLanguage(container, rowId)

        }

    }

    function create(container, instanceId, content, structure) {
        for (var i = 0; i < structure.length; i++) {
            if ((typeof(structure[i].type) != 'undefined') && (typeof(structure[i].id) != 'undefined') && (typeof(structure[i].caption) != 'undefined')) {
                var rowData = undefined;
                if (typeof(content[structure[i].id]) != 'undefined') {
                    rowData = content[structure[i].id];
                }
                var parameters = {};
                if (typeof(structure[i].parameters) != 'undefined') {
                    parameters = $.extend(true, {}, structure[i].parameters);
                }
                var help = '';
                if (typeof(structure[i].help) != 'undefined') {
                    help = structure[i].help;
                }
                var untranslatable = false;
                if (typeof(structure[i].untranslatable) != 'undefined') {
                    untranslatable = structure[i].untranslatable;
                }
                var hidden = false;
                if (typeof(structure[i].hidden) != 'undefined') {
                    hidden = structure[i].hidden;
                }
                appendRow(
                    container,
                    instanceId,
                    structure[i].type,
                    structure[i].id,
                    instanceId + '-' + structure[i].id,
                    structure[i].caption,
                    rowData,
                    parameters,
                    help,
                    untranslatable,
                    hidden
                );
            }
        }
    }

    function getRowData(container, type, id, parameters, fieldStructure) {
        var languages = getLanguages(container);
        var untranslatable = false;
        if (typeof(fieldStructure.untranslatable) != 'undefined') {
            untranslatable = fieldStructure.untranslatable;
        }
        if ((languages != null) && ( !untranslatable)) {
            var data = {};
            for (var i = 0; i < languages.length; i++) {
                var elementContainerId = 'data-editor-container-' + id + '-' + languages[i];
                var elementContainerObject = $('#' + elementContainerId);
                if (elementContainerObject.length > 0) {
                    data[languages[i]] = callPluginFunction(type, 'getData', elementContainerId, parameters);
                }
            }
            return (data);
        } else {
            return (callPluginFunction(type, 'getData', 'data-editor-container-' + id, parameters));
        }
    }

    function getArrayData(container, id) {
        var rowContainer = $('#data-editor-container-' + id);
        var tbody = $(rowContainer).find('table.data-editor-row-array > tbody');
        var data = [];
        var counter = 0;
        $(tbody).children('tr').each(function () {
            var rowData = $(this).data('rowData');
            if (rowData) {
                data[counter] = rowData;
                counter++;
            }
        });
        return (data);
    }

    function getData(container, instanceId, structure) {
        var data = {};
        for (var i = 0; i < structure.length; i++) {
            if ((typeof(structure[i].type) != 'undefined') && (typeof(structure[i].id) != 'undefined')) {
                var parameters = {};
                if (typeof(structure[i].parameters) != 'undefined') {
                    parameters = $.extend(true, {}, structure[i].parameters);
                }
                if (structure[i].type == 'array') {
                    data[structure[i].id] = getArrayData(
                        container,
                        instanceId + '-' + structure[i].id
                    );
                } else {
                    data[structure[i].id] = getRowData(
                        container,
                        structure[i].type,
                        instanceId + '-' + structure[i].id,
                        parameters,
                        structure[i]
                    );
                }
            }
        }
        return (data);
    }

    function setArrayRowLanguage(container, arrayRowId, newLanguageId) {

        var preferredLanguageSubstitutes = getPreferredLanguageSubstitutes(container, newLanguageId);
        var standardLanguage = getStandardLanguage(container);
        var arrayRow = $('#' + arrayRowId);

        $(arrayRow).each(function () {
            $(this).children('td').each(function () {

                var fieldFound = false;

                $(this).find('.data-editor-row-array-field:not(.data-editor-row-array-field-untranslatable)').each(function () {
                    fieldFound = true;
                    $(this).children('.data-editor-row-array-field-disable-overlay').remove();
                    if ($(this).hasClass('data-editor-row-array-field-language-' + newLanguageId)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                if (fieldFound) {
                    var activeLanguageContainer = $(this).find('.data-editor-row-array-field-language-' + newLanguageId);
                    if (activeLanguageContainer.length < 1) {

                        var substitutedLanguageFound = false;
                        var substitutedLangContainer = undefined;

                        if (preferredLanguageSubstitutes != null) {
                            for (var i = 0; i < preferredLanguageSubstitutes.length; i++) {
                                substitutedLangContainer = $(this).find('.data-editor-row-array-field-language-' + preferredLanguageSubstitutes[i]);
                                if (substitutedLangContainer.length > 0) {
                                    substitutedLanguageFound = true;
                                    break;
                                }
                            }
                        }

                        if (!substitutedLanguageFound) {
                            substitutedLangContainer = $(this).find('.data-editor-row-array-field-language-' + standardLanguage);
                            if (substitutedLangContainer.length > 0) {
                                substitutedLanguageFound = true;
                            }
                        }

                        if (!substitutedLanguageFound) {
                            var languages = getLanguages(container);
                            for (var i = 0; i < languages.length; i++) {
                                substitutedLangContainer = $(this).find('.data-editor-row-array-field-language-' + languages[i]);
                                if (substitutedLangContainer.length > 0) {
                                    substitutedLanguageFound = true;
                                    break;
                                }
                            }
                        }

                        if (substitutedLanguageFound) {
                            $(substitutedLangContainer)
                                .show()
                                .append('<div class="data-editor-row-array-field-disable-overlay"></div>')
                            ;
                        }
                    }
                }

            });
        });
    }

    function setArrayLanguageInRow(container, rowId, languageId) {
        var tbody = $('#' + rowId + ' .data-editor-row-array > tbody');
        if (tbody.length > 0) {
            $(tbody).children('tr').each(function () {
                setArrayRowLanguage(container, $(this).attr('id'), languageId);
            });
        }
    }

    function getLanguageIconImageHtml(instanceContainer, languageId) {
        var iconUrl = getLanguageIcon(instanceContainer, languageId);
        if (iconUrl != '') {
            return ('<div class="data-editor-row-translation-language-icon"><img src="' + iconUrl + '" alt=""></div>')
        } else {
            return ('');
        }
    }

    function updateRowContainersForSingleLanguage(container, rowId, options, languages, languageId) {
        $('#' + rowId).find('.data-editor-row-translation-button').empty();
        $('#' + rowId).find('.data-editor-row-translation-button-left').hide();
        $('#' + rowId).find('.data-editor-row-placeholder').remove();
        $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container').each(function () {
            $(this).removeClass('data-editor-row-container-dual-language data-editor-row-container-dual-language-left data-editor-row-container-dual-language-right');
            $(this).children('.data-editor-row-container-disable-overlay').remove();
            if ($(this).hasClass('data-editor-row-container-language-' + languageId)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        var languageIconAdded = false;
        var availableTranslations = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container');
        if (availableTranslations.length > 1) {
            $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-translation-button-right')
                .empty()
                .append(options.buttonRemoveTranslation + getLanguageIconImageHtml(container, languageId))
            ;
            languageIconAdded = true;
        }
        var activeLanguageContainer = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + languageId);
        if (activeLanguageContainer.length < 1) {
            var preferredLanguageSubstitutes = getPreferredLanguageSubstitutes(container, languageId);
            var standardLanguage = getStandardLanguage(container);
            var substitutedLanguageFound = false;
            var substitutedLangContainer;
            if (preferredLanguageSubstitutes != null) {
                for (var i = 0; i < preferredLanguageSubstitutes.length; i++) {
                    substitutedLangContainer = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + preferredLanguageSubstitutes[i]);
                    if (substitutedLangContainer.length > 0) {
                        substitutedLanguageFound = true;
                        break;
                    }
                }
            }
            if (!substitutedLanguageFound) {
                substitutedLangContainer = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + standardLanguage);
                if (substitutedLangContainer.length > 0) {
                    substitutedLanguageFound = true;
                }
            }
            if (!substitutedLanguageFound) {
                for (var i = 0; i < languages.length; i++) {
                    substitutedLangContainer = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + languages[i]);
                    if (substitutedLangContainer.length > 0) {
                        substitutedLanguageFound = true;
                        break;
                    }
                }
            }
            if (substitutedLanguageFound) {
                $(substitutedLangContainer)
                    .show()
                    .append('<div class="data-editor-row-container-disable-overlay"></div>')
                ;
                $(substitutedLangContainer)
                    .parents('.data-editor-row')
                    .first()
                    .find('.data-editor-row-translation-button-right')
                    .first()
                    .empty()
                    .append(options.buttonAddTranslation + getLanguageIconImageHtml(container, languageId))
                ;
                languageIconAdded = true;
            }
        }
        if (!languageIconAdded) {
            $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-translation-button-right')
                .empty()
                .append(getLanguageIconImageHtml(container, languageId))
            ;
        }
    }

    function updateRowContainersForDualLanguage(container, rowId, options, languages, languageId, secondaryLanguageId) {
        $('#' + rowId).find('.data-editor-row-translation-button')
            .empty()
            .show()
        ;
        $('#' + rowId).find('.data-editor-row-placeholder').remove();
        $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container').each(function () {
            $(this).children('.data-editor-row-container-disable-overlay').remove();
            $(this).hide();
        });
        var availableTranslations = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container');
        var mainLanguageContainer = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + languageId);
        if (mainLanguageContainer.length > 0) {
            $(mainLanguageContainer)
                .addClass('data-editor-row-container-dual-language data-editor-row-container-dual-language-left')
                .show();
            ;
            if (availableTranslations.length > 1) {
                $('#' + rowId).find('.data-editor-row-translation-button-left').append(options.buttonRemoveTranslation + getLanguageIconImageHtml(container, languageId));
                $('#' + rowId).find('.data-editor-row-translation-button-left').find('.data-editor-remove-translation').attr('data-language-id', languageId);
            } else {
                $('#' + rowId).find('.data-editor-row-translation-button-left').append(getLanguageIconImageHtml(container, languageId));
            }
        } else {
            $('#' + rowId).find('.data-editor-row-translation-button-left').append(options.buttonAddTranslation + getLanguageIconImageHtml(container, languageId));
            $('#' + rowId).find('.data-editor-row-translation-button-left').find('.data-editor-add-translation').attr('data-language-id', languageId);
            $('#' + rowId).find('.data-editor-row-container-wrapper').append('<div class="data-editor-row-placeholder data-editor-row-placeholder-left"><div class="outer"><div class="inner"><div class="placeholder-text">' + options.noTranslationAvailablePlaceholderText + '</div></div></div></div>');
        }
        var secondaryLanguageContainer = $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + secondaryLanguageId);
        if (secondaryLanguageContainer.length > 0) {
            $(secondaryLanguageContainer)
                .addClass('data-editor-row-container-dual-language data-editor-row-container-dual-language-right')
                .show();
            ;
            if (availableTranslations.length > 1) {
                $('#' + rowId).find('.data-editor-row-translation-button-right').append(options.buttonRemoveTranslation + getLanguageIconImageHtml(container, secondaryLanguageId));
                $('#' + rowId).find('.data-editor-row-translation-button-right').find('.data-editor-remove-translation').attr('data-language-id', secondaryLanguageId);
            } else {
                $('#' + rowId).find('.data-editor-row-translation-button-right').append(getLanguageIconImageHtml(container, secondaryLanguageId));
            }
        } else {
            $('#' + rowId).find('.data-editor-row-translation-button-right').append(options.buttonAddTranslation + getLanguageIconImageHtml(container, secondaryLanguageId));
            $('#' + rowId).find('.data-editor-row-translation-button-right').find('.data-editor-add-translation').attr('data-language-id', secondaryLanguageId);
            $('#' + rowId).find('.data-editor-row-container-wrapper').append('<div class="data-editor-row-placeholder data-editor-row-placeholder-right"><div class="outer"><div class="inner"><div class="placeholder-text">' + options.noTranslationAvailablePlaceholderText + '</div></div></div></div>');
        }
    }

    function setRowLanguage(container, rowId, languageId, secondaryLanguageId) {
        var options = getOptions(container);
        var languages = getLanguages(container);
        if (languages == null) {
            return (false);
        }
        var type = $('#' + rowId).attr('data-type');
        var fieldId = $('#' + rowId).attr('data-field-id');
        var fieldStructure = getStructureByFieldId(container, fieldId, options.structure);
        if (type == 'array') {
            setArrayLanguageInRow(container, rowId, languageId);
            var instanceId = container.data('dataEditorInstanceId');
            var fieldId = $('#' + rowId).attr('data-field-id');
            orderArrayData(container, instanceId, rowId, fieldStructure.parameters, languageId);
        } else {
            var untranslatable = false;
            if (typeof(fieldStructure) != 'undefined') {
                untranslatable = fieldStructure.untranslatable;
                if (untranslatable == true) {
                    return (false);
                }
            }
            if (secondaryLanguageId != null) {
                updateRowContainersForDualLanguage(container, rowId, options, languages, languageId, secondaryLanguageId);
            } else {
                updateRowContainersForSingleLanguage(container, rowId, options, languages, languageId);
            }
        }
    }

    function updateRowLanguage(container, rowId) {
        var activeLanguage = getActiveLanguage(container);
        var activeSecondaryLanguage = getActiveSecondaryLanguage(container);
        setRowLanguage(container, rowId, activeLanguage, activeSecondaryLanguage);
    }

    function setLanguage(container, languageId, secondaryLanguageId) {
        if (typeof(secondaryLanguageId) == 'undefined') {
            secondaryLanguageId = null;
        }
        var options = container.data('dataEditorOptions');
        if ((!options) || (options.languages == null)) {
            return;
        }
        container.data('dataEditorActiveLanguage', languageId);
        container.data('dataEditorActiveSecondaryLanguage', secondaryLanguageId);
        var rows = container.children('.data-editor-row');
        if (rows.length > 0) {
            $(rows).each(function () {
                setRowLanguage(container, $(this).attr('id'), languageId, secondaryLanguageId);
            });
        }
        var subEditors = $(options.arrays.appendSubEditorTo).find('.data-editor-subeditor-window');
        if (subEditors.length > 0) {
            $(subEditors).each(function () {
                var instanceId = $(container).data('dataEditorInstanceId');
                var subEditorParentInstanceId = $(this).attr('data-parent-instance-id');
                if ((instanceId) && (subEditorParentInstanceId)) {
                    if (instanceId == subEditorParentInstanceId) {
                        var subEditorInstance = $(this).find('.data-editor-subeditor-window-content').first();
                        $(subEditorInstance).dataEditor('setLanguage', languageId, secondaryLanguageId);
                    }
                }
            });
        }
    }

    function addRowTranslation(instanceContainer, instanceId, rowId, languageId) {
        var fieldId = $('#' + rowId).attr('data-field-id');
        if (fieldId) {
            var type = null;
            var parameters;
            var structure = getStructureByFieldId(instanceContainer, fieldId);
            if (structure != null) {
                if (typeof(structure.type) != 'undefined') {
                    type = structure.type;
                }
                if (typeof(structure.parameters) != 'undefined') {
                    parameters = structure.parameters;
                }
                if (type != null) {
                    var rowContainerId = 'data-editor-container-' + instanceId + '-' + fieldId + '-' + languageId;
                    $('#' + rowId + ' > .data-editor-row-container-wrapper').append('<div class="data-editor-row-container data-editor-row-container-language-' + languageId + '" id="' + rowContainerId + '"></div>');
                    callPluginFunction(type, 'insertHtml', rowContainerId, undefined, parameters);
                    callPluginFunction(type, 'bindEvents', rowContainerId, parameters, instanceId);
                    updateRowLanguage(instanceContainer, rowId);
                    lastCreatedRow++;
                }
            }
        }
    }

    function removeRowTranslation(instanceContainer, instanceId, rowId, languageId) {
        var type = $('#' + rowId).attr('data-type');
        var fieldId = $('#' + rowId).attr('data-field-id');
        var structure = getStructureByFieldId(instanceContainer, fieldId);
        if ((type) && (fieldId) && (structure != null)) {
            var parameters;
            if (typeof(structure.parameters) != 'undefined') {
                parameters = structure.parameters;
            }
            callPluginFunction(type, 'unbindEvents', $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + languageId).attr('id'), parameters, instanceId);
        }
        $('#' + rowId + ' > .data-editor-row-container-wrapper > .data-editor-row-container-language-' + languageId).remove();
        updateRowLanguage(instanceContainer, rowId);
    }

    function bindEvents(instanceContainer, instanceId) {

        var options = getOptions(instanceContainer);

        function getTranslationButtonLanguageId(e) {
            var activeSecondaryLanguage = getActiveSecondaryLanguage(instanceContainer);
            var languageId;
            if (activeSecondaryLanguage != null) {
                languageId = $(e.target).attr('data-language-id');
            } else {
                languageId = getActiveLanguage(instanceContainer);
            }
            return (languageId);
        }

        instanceContainer.on('click.' + instanceId, '.data-editor-add-translation', function (e) {
            var rowId = $(e.target).parents('.data-editor-row').first().attr('id');
            var languageId = getTranslationButtonLanguageId(e);
            addRowTranslation(instanceContainer, instanceId, rowId, languageId);
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-remove-translation', function (e) {
            var rowId = $(e.target).parents('.data-editor-row').first().attr('id');
            var languageId = getTranslationButtonLanguageId(e);
            removeRowTranslation(instanceContainer, instanceId, rowId, languageId);
        });

        function getArrayRowId(target) {
            var id = $(target).parents('tr').first().attr('id');
            return (id);
        }

        function getArrayRowParent(target) {
            var result = $(target).parents('tbody').first();
            return (result);
        }

        instanceContainer.on('click.' + instanceId, '.data-editor-array-row-button-up', function (e) {
            var elementId = getArrayRowId(e.target);
            $('#' + elementId).insertBefore($('#' + elementId).prev());
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-row-button-down', function (e) {
            var elementId = getArrayRowId(e.target);
            $('#' + elementId).insertAfter($('#' + elementId).next());
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-row-button-first', function (e) {
            var elementId = getArrayRowId(e.target);
            var tbody = getArrayRowParent(e.target);
            $('#' + elementId).insertBefore($(tbody).children().first());
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-row-button-last', function (e) {
            var elementId = getArrayRowId(e.target);
            var tbody = getArrayRowParent(e.target);
            $('#' + elementId).insertAfter($(tbody).children().last());
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-row-button-delete', function (e) {
            var elementId = getArrayRowId(e.target);
            var tbody = getArrayRowParent(e.target);
            var cols = $('#' + elementId).children('tr');
            var colNum = cols.length;
            $('#' + elementId).remove();
            var rows = $(tbody).children();
            if (rows.length < 1) {
                var options = getOptions(instanceContainer);
                $(tbody).append(
                    '<tr class="data-editor-row-array-empty">' +
                    '<td colspan="' + colNum.toString() + '">' +
                    options.arrays.emptyContent +
                    '</td>' +
                    '</tr>'
                );
            }
        });

        function getArrayTBody(target) {
            var dataEditorRow = $(target).parents('.data-editor-row-container').first();
            var tbody = $(dataEditorRow).find('table.data-editor-row-array tbody').first();
            return (tbody);
        }

        function callClickHandlerOfRow(row, buttonSelector) {
            $(row).find(buttonSelector).first().click();
        }

        instanceContainer.on('click.' + instanceId, '.data-editor-array-select-all', function (e) {
            var tbody = getArrayTBody(e.target);
            var checkboxes = $(tbody).find('.data-editor-row-array-selected-row').each(function () {
                $(this).prop('checked', true);
            });
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-select-none', function (e) {
            var tbody = getArrayTBody(e.target);
            var checkboxes = $(tbody).find('.data-editor-row-array-selected-row').each(function () {
                $(this).prop('checked', false);
            });
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-selected-up', function (e) {
            var tbody = getArrayTBody(e.target);
            var rows = $(tbody).children('tr');
            $(rows).each(function () {
                var checkbox = $(this).find('.data-editor-row-array-selected-row');
                if (checkbox.length > 0) {
                    if ($(checkbox).is(':checked')) {
                        var previousRowCheckbox = $(this).prev().find('.data-editor-row-array-selected-row').first();
                        if (previousRowCheckbox.length > 0) {
                            if (!(previousRowCheckbox.is(':checked'))) {
                                callClickHandlerOfRow(this, '.data-editor-array-row-button-up');
                            }
                        }
                    }
                }
            });
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-selected-down', function (e) {
            var tbody = getArrayTBody(e.target);
            var rows = $(tbody).children('tr').get().reverse();
            $(rows).each(function () {
                var checkbox = $(this).find('.data-editor-row-array-selected-row');
                if (checkbox.length > 0) {
                    if ($(checkbox).is(':checked')) {
                        var nextRowCheckbox = $(this).next().find('.data-editor-row-array-selected-row').first();
                        if (nextRowCheckbox.length > 0) {
                            if (!(nextRowCheckbox.is(':checked'))) {
                                callClickHandlerOfRow(this, '.data-editor-array-row-button-down');
                            }
                        }
                    }
                }
            });
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-selected-first', function (e) {
            var tbody = getArrayTBody(e.target);
            var rows = $(tbody).children('tr').get().reverse();
            $(rows).each(function () {
                var checkbox = $(this).find('.data-editor-row-array-selected-row');
                if (checkbox.length > 0) {
                    if ($(checkbox).is(':checked')) {
                        callClickHandlerOfRow(this, '.data-editor-array-row-button-first');
                    }
                }
            });
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-selected-last', function (e) {
            var tbody = getArrayTBody(e.target);
            var rows = $(tbody).children('tr');
            $(rows).each(function () {
                var checkbox = $(this).find('.data-editor-row-array-selected-row');
                if (checkbox.length > 0) {
                    if ($(checkbox).is(':checked')) {
                        callClickHandlerOfRow(this, '.data-editor-array-row-button-last');
                    }
                }
            });
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-array-selected-delete', function (e) {
            var tbody = getArrayTBody(e.target);
            var rows = $(tbody).children('tr');
            $(rows).each(function () {
                var checkbox = $(this).find('.data-editor-row-array-selected-row');
                if (checkbox.length > 0) {
                    if ($(checkbox).is(':checked')) {
                        callClickHandlerOfRow(this, '.data-editor-array-row-button-delete');
                    }
                }
            });
        });

        function openSubEditor(newRow, containerId, arrayRowId, fieldId, rowData) {
            var html = '';
            var options = getOptions(instanceContainer);
            var structure = getStructureByFieldId(instanceContainer, fieldId, options.structure);
            if (structure != null) {
                var headline;
                if (newRow) {
                    headline = options.arrays.subEditorHeadlineNew;
                } else {
                    headline = options.arrays.subEditorHeadlineEdit;
                }
                var subEditorWindowId = 'data-editor-subeditor-window-' + getRandomId();
                var subEditorContainerId = subEditorWindowId + '-data-editor-' + getRandomId();

                html = html +
                    '<div id="' + subEditorWindowId + '" class="data-editor-subeditor-window" data-container-id="' + containerId + '" data-array-row-id="' + arrayRowId + '" data-parent-instance-id="' + instanceId + '">' +
                    '<div class="data-editor-subeditor-window-wrapper">' +
                    '<div class="data-editor-subeditor-window-headline">' +
                    headline +
                    '</div>' +
                    '<div id="' + subEditorContainerId + '" class="data-editor-subeditor-window-content"></div>' +
                    '<div class="data-editor-subeditor-window-buttons">' +
                    options.arrays.subEditorButtons +
                    '</div>' +
                    '</div>' +
                    '</div>';

                $(options.arrays.appendSubEditorTo).append(html);

                var subEditorOptions = $.extend(true, {}, options);
                subEditorOptions.structure = structure.parameters.fields;
                subEditorOptions.content = rowData;
                subEditorOptions.isSubEditor = true;
                subEditorOptions.activeLanguage = getActiveLanguage(instanceContainer);
                subEditorOptions.activeSecondaryLanguage = getActiveSecondaryLanguage(instanceContainer);
                console.log(subEditorOptions.activeSecondaryLanguage);

                $('#' + subEditorContainerId).dataEditor(subEditorOptions);
            }
        }

        instanceContainer.on('click.' + instanceId, '.data-editor-array-add-row', function (e) {
            var fieldId = $(e.target).parents('.data-editor-row').first().attr('data-field-id');
            var containerId = $(e.target).parents('.data-editor-row').first().find('.data-editor-row-container').first().attr('id');
            var arrayRowId = '';
            if (fieldId) {
                openSubEditor(true, containerId, arrayRowId, fieldId, {});
            }
        });

        function closeSubEditor(subEditorId) {
            $('#' + subEditorId).find('.data-editor-subeditor-window-content').dataEditor('destroy');
            $('#' + subEditorId).remove();
        }

        function saveSubEditorDataToArray(subEditorId) {
            var containerId = $('#' + subEditorId).attr('data-container-id');
            var fieldId = $('#' + containerId).parents('.data-editor-row').first().attr('data-field-id');
            var arrayRowId = $('#' + subEditorId).attr('data-array-row-id');
            var dataEditorInstance = $('#' + subEditorId).find('.data-editor-subeditor-window-content').first();
            var rowData = $(dataEditorInstance).dataEditor('get');
            var structure = getStructureByFieldId(instanceContainer, fieldId);
            if (arrayRowId == '') {
                var insertAtTop = $('#' + containerId).find('.data-editor-array-insert-at-top').first().is(':checked');
                var arrayRowId = appendRowToArray(instanceContainer, instanceId, containerId, rowData, structure.parameters, insertAtTop);
            } else {
                updateRowInArray(instanceContainer, instanceId, containerId, arrayRowId, rowData, structure.parameters);
            }
            setArrayRowLanguage(instanceContainer, arrayRowId, getActiveLanguage(instanceContainer));
            orderArrayData(instanceContainer, instanceId, containerId, structure.parameters);
        }

        $(options.arrays.appendSubEditorTo).on('click.' + instanceId, '.data-editor-array-ok', function (e) {
            var subEditor = $(e.target).parents('.data-editor-subeditor-window').first();
            if (subEditor.length > 0) {
                var subEditorId = $(subEditor).attr('id');
                var containerId = $('#' + subEditorId).attr('data-container-id');
                var outmostContainer = $('#' + containerId).parents('.data-editor-row').first().parent();
                var test1 = $(outmostContainer).attr('id');
                var test2 = $(instanceContainer).attr('id');
                if (test1 == test2) {
                    e.stopImmediatePropagation();
                    saveSubEditorDataToArray(subEditorId);
                    closeSubEditor(subEditorId);
                }
            }
        });

        $(options.arrays.appendSubEditorTo).on('click.' + instanceId, '.data-editor-array-cancel', function (e) {
            var subEditor = $(e.target).parents('.data-editor-subeditor-window').first();
            if (subEditor.length > 0) {
                e.stopImmediatePropagation();
                closeSubEditor($(subEditor).attr('id'));
            }
        });

        instanceContainer.on('click.' + instanceId, '.data-editor-row-array > tbody > tr > td', function (e) {
            var td;
            if ($(e.target).is('.data-editor-row-array > tbody > tr > td')) {
                td = e.target;
            } else {
                td = $(e.target).parents('td').first();
            }
            if (!$(td).hasClass('data-editor-row-array-ignore-click')) {
                var tr = $(td).parent();
                var arrayRowId = $(tr).attr('id');
                var containerId = $(tr).parents('.data-editor-row').first().find('.data-editor-row-container').first().attr('id');
                var fieldId = $(tr).parents('.data-editor-row').first().attr('data-field-id');
                var rowData = $(tr).data('rowData');
                openSubEditor(false, containerId, arrayRowId, fieldId, rowData);
            }
        });

    }

    function unbindEvents(instanceContainer, instanceId) {
        var options = getOptions(instanceContainer);
        instanceContainer.children('.data-editor-row').each(function () {
            var type = $(this).attr('data-type');
            var fieldId = $(this).attr('data-field-id');
            var structure = getStructureByFieldId(instanceContainer, fieldId);
            if (structure != null) {
                var parameters;
                if (typeof(structure.parameters) != 'undefined') {
                    parameters = structure.parameters;
                }
                $(this).children('.data-editor-row-container').each(function () {
                    callPluginFunction(type, 'unbindEvents', $(this).attr('id'), parameters, instanceId);
                });
            }
        });
        $(options.arrays.appendSubEditorTo).off('.' + instanceId);
        instanceContainer.off('.' + instanceId);
    }

    function applyPluginEventToRow(container, type, id, parameters, pluginFunction, instanceId) {
        var languages = getLanguages(container);
        if (languages != null) {
            var data = {};
            for (var i = 0; i < languages.length; i++) {
                var elementContainerId = 'data-editor-container-' + id + '-' + languages[i];
                var elementContainerObject = $('#' + elementContainerId);
                if (elementContainerObject.length > 0) {
                    callPluginFunction(type, pluginFunction, elementContainerId, parameters, instanceId);
                }
            }
            return (data);
        } else {
            callPluginFunction(type, pluginFunction, 'data-editor-container-' + id, parameters, instanceId);
        }
    }

    function applyPluginEvent(container, instanceId, structure, pluginFunction) {
        var data = {};
        for (var i = 0; i < structure.length; i++) {
            if ((typeof(structure[i].type) != 'undefined') && (typeof(structure[i].id) != 'undefined')) {
                var parameters = {};
                if (typeof(structure[i].parameters) != 'undefined') {
                    parameters = $.extend(true, {}, structure[i].parameters);
                }
                if (structure[i].type !== 'array') {
                    applyPluginEventToRow(
                        container,
                        structure[i].type,
                        instanceId + '-' + structure[i].id,
                        parameters,
                        pluginFunction,
                        instanceId
                    );
                }
            }
        }
        return (data);
    }

    var methods = {

        init: function (options) {
            return this.each(function () {
                var $this = $(this);
                var savedOptions = $this.data('dataEditorOptions');
                if (!savedOptions) {
                    var finalOptions = {};
                    $.extend(true, finalOptions, defaultOptions, options);
                    var instanceId = getRandomId();
                    $this.data('dataEditorInstance', $this);
                    $this.data('dataEditorInstanceId', instanceId);
                    $this.data('dataEditorOptions', finalOptions);
                    $this.data('dataEditorActiveLanguage', finalOptions.activeLanguage);
                    $this.data('dataEditorActiveSecondaryLanguage', finalOptions.activeSecondaryLanguage);
                    create($this, instanceId, finalOptions.content, finalOptions.structure, finalOptions.languages);
                    bindEvents($this, instanceId);
                }
            });
        },

        destroy: function () {
            return this.each(function () {
                var $this = $(this);
                var dataEditorInstanceId = $this.data('dataEditorInstanceId');
                if (dataEditorInstanceId) {
                    $(window).unbind('.' + dataEditorInstanceId);
                    var dataEditorInstance = $this.data('dataEditorInstance');
                    if (dataEditorInstance) {
                        unbindEvents(dataEditorInstance, dataEditorInstanceId);
                        dataEditorInstance.empty();
                    }
                    $this.removeData('dataEditorInstance');
                    $this.removeData('dataEditorInstanceId');
                    $this.removeData('dataEditorOptions');
                    $this.removeData('dataEditorActiveLanguage');
                    $this.removeData('dataEditorActiveSecondaryLanguage');
                }
            });
        },

        get: function () {
            var returnData;
            this.first().each(function () {
                var $this = $(this);
                var dataEditorInstanceId = $this.data('dataEditorInstanceId');
                var dataEditorOptions = $this.data('dataEditorOptions');
                if ((dataEditorInstanceId) && (dataEditorOptions)) {
                    returnData = getData($this, dataEditorInstanceId, dataEditorOptions.structure);
                }
            });
            return (returnData);
        },

        setLanguage: function (languageId, secondaryLanguageId) {
            return this.each(function () {
                var dataEditorInstance = $(this).data('dataEditorInstance');
                if (dataEditorInstance) {
                    setLanguage(dataEditorInstance, languageId, secondaryLanguageId);
                }
            });
        },

        beforeMove: function () {
            return this.each(function () {
                var $this = $(this);
                var dataEditorInstanceId = $this.data('dataEditorInstanceId');
                var dataEditorOptions = $this.data('dataEditorOptions');
                if ((dataEditorInstanceId) && (dataEditorOptions)) {
                    applyPluginEvent($this, dataEditorInstanceId, dataEditorOptions.structure, 'beforeMove');
                }
            });
        },

        afterMove: function () {
            return this.each(function () {
                var $this = $(this);
                var dataEditorInstanceId = $this.data('dataEditorInstanceId');
                var dataEditorOptions = $this.data('dataEditorOptions');
                if ((dataEditorInstanceId) && (dataEditorOptions)) {
                    applyPluginEvent($this, dataEditorInstanceId, dataEditorOptions.structure, 'afterMove');
                }
            });
        }

    };

    $.fn.dataEditor = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.dataEditor');
        }
    };

})(jQuery);
