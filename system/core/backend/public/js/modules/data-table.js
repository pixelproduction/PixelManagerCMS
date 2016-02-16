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

    var $ = require('jquery');
    var translate = require('modules/translate');
    var dataEditorBootstrap = require('modules/data-editor-bootstrap');
    var fixModals = require('modules/fixmodals');
    require('plugins/jquery-ui-bootstrap-no-conflict');
    require('plugins/data-tables');
    require('plugins/data-tables-bootstrap');
    require('plugins/data-tables-locale-sort');

    function DataTable(moduleUrl, containerSelector, languagesConfig) {
        this.setModuleUrl(moduleUrl);
        this.setContainerSelector(containerSelector);
        this.setLanguagesConfig(languagesConfig);
        this.dataTablesObject = null;
        this.dataTablesSavedState = null;
        this.rawData = null;
        this.dataTableRows = [];
        this.fields = [];
        this.columns = [];
        this.editorTabs = null;
        this.options = null;
        this.getStructureCallbackId = null;
        this.getAllRowsCallbackId = null;
        this.getRowCallbackId = null;
        this.getStandardValuesCallbackId = null;
        this.saveRowCallbackId = null;
        this.deleteRowsCallbackId = null;
        this.queryCreateRowCallbackId = null;
        this.queryUpdateRowCallbackId = null;
        this.queryDeleteRowsCallbackId = null;
        this.eventListenerId = null;
        this.currentEditorAction = 'closed';
        this.currentEditorRowId = null;
        this.dataEditorPlugins = [];
    }

    DataTable.prototype.getDataEditorPlugin = function (pluginId) {
        if (typeof(this.dataEditorPlugins[pluginId]) != 'undefined') {
            return (this.dataEditorPlugins[pluginId]);
        } else {
            if (typeof($.fn.dataEditorPlugins[pluginId]) != 'undefined') {
                this.dataEditorPlugins[pluginId] = $.fn.dataEditorPlugins[pluginId]();
                this.dataEditorPlugins[pluginId].init();
                return (this.dataEditorPlugins[pluginId]);
            } else {
                return (null);
            }
        }
    };

    DataTable.prototype.callDataEditorPluginFunction = function (pluginId, functionName) {
        var plugin = this.getDataEditorPlugin(pluginId);
        if (plugin != null) {
            return (plugin[functionName].apply(plugin, Array.prototype.slice.call(arguments, 2)));
        }
    };

    DataTable.prototype.setModuleUrl = function (moduleUrl) {
        if (typeof(moduleUrl) != 'undefined') {
            this.moduleUrl = moduleUrl;
        } else {
            this.moduleUrl = null;
        }
    };

    DataTable.prototype.setContainerSelector = function (containerSelector) {
        if (typeof(containerSelector) != 'undefined') {
            this.containerSelector = containerSelector;
        } else {
            this.containerSelector = null;
        }
    };

    DataTable.prototype.setLanguagesConfig = function (languagesConfig) {
        if (typeof(languagesConfig) != 'undefined') {
            this.languagesConfig = languagesConfig;
        } else {
            this.languagesConfig = this.loadLanguagesConfigFromPixelmanagerGlobal();
        }
    };

    DataTable.prototype.loadLanguagesConfigFromPixelmanagerGlobal = function () {
        return {
            languages: parent.pixelmanagerGlobal.languages,
            standardLanguage: parent.pixelmanagerGlobal.standardLanguage,
            preferredLanguageSubstitutes: parent.pixelmanagerGlobal.preferredLanguageSubstitutes,
            languageIcons: parent.pixelmanagerGlobal.languageIcons,
            activeLanguage: parent.pixelmanagerGlobal.activeLanguage,
            activeSecondaryLanguage: parent.pixelmanagerGlobal.activeSecondaryLanguage
        };
    };

    DataTable.prototype.getAllRowsRequest = function () {
        if ((this.getAllRowsCallbackId !== null) && (this.moduleUrl !== null)) {
            parent.pixelmanagerGlobal.dataExchange.request(
                'DataTable',
                parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/getallrows',
                {},
                this.getAllRowsCallbackId,
                window.frameElement.id,
                $
            );
        }
    };

    DataTable.prototype.getStructureRequest = function () {
        if ((this.getStructureCallbackId !== null) && (this.moduleUrl !== null)) {
            parent.pixelmanagerGlobal.dataExchange.request(
                'DataTable',
                parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/getstructure',
                {},
                this.getStructureCallbackId,
                window.frameElement.id,
                $
            );
        }
    };

    DataTable.prototype.getRowRequest = function (rowId) {
        if ((this.getRowCallbackId !== null) && (this.moduleUrl !== null)) {
            parent.pixelmanagerGlobal.dataExchange.request(
                'DataTable Row',
                parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/getrow',
                {
                    id: rowId
                },
                this.getRowCallbackId,
                window.frameElement.id,
                $
            );
        }
    };


    DataTable.prototype.getStandardValuesRequest = function () {
        if ((this.getStandardValuesCallbackId !== null) && (this.moduleUrl !== null)) {
            parent.pixelmanagerGlobal.dataExchange.request(
                'DataTable Standard Values',
                parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/getstandardvalues',
                {},
                this.getStandardValuesCallbackId,
                window.frameElement.id,
                $
            );
        }
    };

    DataTable.prototype.refresh = function () {
        this.getAllRowsRequest();
    };

    DataTable.prototype.getFieldById = function (id) {
        var i;
        if (this.fields.length > 0) {
            for (i = 0; i < this.fields.length; i++) {
                if (this.fields[i].id == id) {
                    return this.fields[i];
                }
            }
        }
        return ({});
    };

    DataTable.prototype.dataTablesCheckboxColumnRenderFunction = function (data, type, full) {
        return '<input class="data-table-row-checkbox" type="checkbox" value="1" data-id="' + full.id + '" name="data-table-row-' + full.id + '">';
    };

    DataTable.prototype.getSelectedRowIds = function () {
        var ret = [];
        if (this.dataTablesObject != null) {
            var selectedCheckboxes = $('.data-table-row-checkbox:checked:visible', this.dataTablesObject.fnGetNodes());
            if (selectedCheckboxes.length > 0) {
                $(selectedCheckboxes).each(function () {
                    ret.push($(this).attr('data-id'));
                });
            }
        }
        return ret;
    };

    DataTable.prototype.getFieldValueForActiveLanguage = function (data, field) {
        var untranslatable = false;
        if (typeof(field.untranslatable) != 'undefined') {
            untranslatable = field.untranslatable;
        }
        if (this.isMultiLanguage() && ( !untranslatable)) {
            if (typeof(data[this.languagesConfig.activeLanguage]) != 'undefined') {
                if (data[this.languagesConfig.activeLanguage] != null) {
                    return (data[this.languagesConfig.activeLanguage]);
                }
            }
            if (typeof(this.languagesConfig.preferredLanguageSubstitutes[this.languagesConfig.activeLanguage]) != 'undefined') {
                var substitutes = this.languagesConfig.preferredLanguageSubstitutes[this.languagesConfig.activeLanguage];
                if (substitutes.length > 0) {
                    var i = 0;
                    for (i = 0; i < substitutes.length; i++) {
                        if (typeof(data[substitutes[i]]) != 'undefined') {
                            if (data[substitutes[i]] != null) {
                                return (data[substitutes[i]]);
                            }
                        }
                    }
                }
            }
            return (null);
        } else {
            return (data);
        }
    };

    DataTable.prototype.isMultiLanguage = function () {
        if (typeof(this.languagesConfig) != 'undefined') {
            if (this.languagesConfig != null) {
                if (typeof(this.languagesConfig.languages) != 'undefined') {
                    if (this.languagesConfig.languages != null) {
                        if (typeof(this.languagesConfig.activeLanguage) != 'undefined') {
                            if (this.languagesConfig.activeLanguage != null) {
                                return (true);
                            }
                        }
                    }
                }
            }
        }
        return (false);
    };

    DataTable.prototype.dataTablesColumnDataFunction = function (column, field, index, rowData, typeOfCall, newValue) {
        if (typeof(rowData) == 'undefined') {
            return (null);
        }
        if (typeof(rowData[column.id]) == 'undefined') {
            return (null);
        }
        if (typeof(typeOfCall) == 'undefined') {
            // Laut Doku erwartet DataTables.js die rohen Daten zurück, wenn typeOfCall undefined ist
            return (rowData[column.id]);
        } else {
            // Ansonsten gibt es 5 Möglichkeiten
            switch (typeOfCall) {
                case 'set':
                    // rowData[column.id]['de'] = newValue;
                    break;
                case 'filter':
                case 'display':
                case 'type':
                    if (rowData[column.id] != null) {
                        var fieldValue = this.getFieldValueForActiveLanguage(rowData[column.id], field);
                        if (typeof(fieldValue) != 'undefined') {
                            fieldValue = this.callDataEditorPluginFunction(field.type, 'getRowHtml', fieldValue, field.parameters);
                        }
                        return (fieldValue);
                    } else {
                        return null;
                    }
                    break;
                case 'sort':
                    if (rowData[column.id] != null) {
                        var fieldValue = this.getFieldValueForActiveLanguage(rowData[column.id], field);
                        if (typeof(fieldValue) != 'undefined') {
                            fieldValue = this.callDataEditorPluginFunction(field.type, 'getSortableValue', fieldValue, field.parameters);
                        }
                        return (fieldValue);
                    } else {
                        return null;
                    }
                    break;
            }
        }
    };

    DataTable.prototype.getDataTablesColumn = function (column, field, index) {
        var that = this;
        var ret = {
            "sTitle": column.caption,
            "aTargets": [index]
            /*"sType": "html"*/
        };
        if (typeof(column.customDataSource) != 'undefined') {
            if (column.customDataSource == true) {
                ret["mData"] = column.id;
            }
        }
        if (typeof(column.unsortable) != 'undefined') {
            if (column.unsortable == true) {
                ret["bSortable"] = false;
            }
        }
        if (typeof(ret.mData) == 'undefined') {
            ret["mData"] = function (rowData, typeOfCall, newValue) {
                return (that.dataTablesColumnDataFunction.call(that, column, field, index, rowData, typeOfCall, newValue));
            };
        }
        return (ret);
    };

    DataTable.prototype.getDataTablesIdColumn = function (index) {
        return {
            "bSearchable": false,
            "bSortable": false,
            "bVisible": false,
            "mData": "id",
            "aTargets": [index]
        };
    };

    DataTable.prototype.getDataTablesCheckboxColumn = function (index) {
        return {
            "sTitle": " ",
            "bSearchable": false,
            "bSortable": false,
            "mData": null,
            "mRender": this.dataTablesCheckboxColumnRenderFunction,
            "aTargets": [index]
        };
    };

    DataTable.prototype.getDataTablesColumns = function () {
        var ret = [];
        var column = {};
        var columnOffset = 0;
        var i;
        if (this.columns != null) {
            if (this.columns.length > 0) {
                var idColumn = this.getDataTablesIdColumn(columnOffset);
                if (typeof(idColumn) != 'undefined') {
                    if (idColumn != null) {
                        ret.push(idColumn);
                        columnOffset++;
                    }
                }
                var checkboxColumn = this.getDataTablesCheckboxColumn(columnOffset);
                if (typeof(checkboxColumn) != 'undefined') {
                    if (checkboxColumn != null) {
                        ret.push(checkboxColumn);
                        columnOffset++;
                    }
                }
                for (i = 0; i < this.columns.length; i++) {
                    column = this.getDataTablesColumn(this.columns[i], this.getFieldById(this.columns[i].id), i + columnOffset);
                    ret.push(column);
                }
            }
        }
        return ret;
    };

    DataTable.prototype.getDataTablesDefaultSorting = function () {
        var columns = this.getDataTablesColumns();
        var i = 0;
        var indexOfFirstSortableColumn = 0;
        for (i = 0; i < columns.length; i++) {
            var visible = true;
            var sortable = true;
            if (typeof(columns[i]["bVisible"]) != 'undefined') {
                if (columns[i]["bVisible"] == false) {
                    visible = false;
                }
            }
            if (typeof(columns[i]["bSortable"]) != 'undefined') {
                if (columns[i]["bSortable"] == false) {
                    sortable = false;
                }
            }
            if (visible && sortable) {
                indexOfFirstSortableColumn = i;
                break;
            }
        }
        var ret = [[indexOfFirstSortableColumn, "asc"]];
        return (ret);
    };

    DataTable.prototype.setData = function (rawData) {
        this.rawData = rawData;
    };

    DataTable.prototype.setFields = function (fields) {
        this.fields = fields;
    };

    DataTable.prototype.setColumns = function (columns) {
        this.columns = columns;
    };

    DataTable.prototype.setEditorTabs = function (editorTabs) {
        this.editorTabs = editorTabs;
    };

    DataTable.prototype.setOptions = function (options) {
        this.options = options;
    };

    DataTable.prototype.getOption = function (key) {
        if (this.options != null) {
            if (typeof(this.options[key]) != 'undefined') {
                return (this.options[key]);
            }
        }
        return (null);
    };

    DataTable.prototype.createRequestCallbackItems = function () {
        this.getAllRowsCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.getRowCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.getStandardValuesCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.saveRowCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.getStructureCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.deleteRowsCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.queryCreateRowCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.queryUpdateRowCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
        this.queryDeleteRowsCallbackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('body', window.frameElement.id);
    };

    DataTable.prototype.bindRequestCallbackEvents = function () {
        var that = this;
        $('#' + this.getStructureCallbackId).on("success.pixelmanager", function (event, data) {
            that.getStructureSuccess.call(that, event, data);
        });
        $('#' + this.getAllRowsCallbackId).on("success.pixelmanager", function (event, data) {
            that.getAllRowsSuccess.call(that, event, data);
        });
        $('#' + this.getRowCallbackId).on("success.pixelmanager", function (event, data) {
            that.getRowSuccess.call(that, event, data);
        });
        $('#' + this.getStandardValuesCallbackId).on("success.pixelmanager", function (event, data) {
            that.getStandardValueSuccess.call(that, event, data);
        });
        $('#' + this.saveRowCallbackId).on("success.pixelmanager", function (event, data) {
            that.saveRowSuccess.call(that, event, data);
        });
        $('#' + this.deleteRowsCallbackId).on("success.pixelmanager", function (event, data) {
            that.deleteRowsSuccess.call(that, event, data);
        });
        $('#' + this.queryCreateRowCallbackId).on("success.pixelmanager", function (event, data) {
            that.queryCreateRowSuccess.call(that, event, data);
        });
        $('#' + this.queryUpdateRowCallbackId).on("success.pixelmanager", function (event, data) {
            that.queryUpdateRowSuccess.call(that, event, data);
        });
        $('#' + this.queryDeleteRowsCallbackId).on("success.pixelmanager", function (event, data) {
            that.queryDeleteRowsSuccess.call(that, event, data);
        });
    };

    DataTable.prototype.getStructureSuccess = function (event, data) {
        this.setFields(data.fields);
        this.setColumns(data.columns);
        this.setEditorTabs(data.editorTabs);
        this.setOptions(data.options);
        this.insertEditorHtml();
        this.getAllRowsRequest();
    };

    DataTable.prototype.getAllRowsSuccess = function (event, data) {
        this.setData(data.rows);
        this.update();
    };

    DataTable.prototype.getRowSuccess = function (event, data) {
        this.openEditor(data.row, data.id);
    };

    DataTable.prototype.getStandardValueSuccess = function (event, data) {
        this.openEditor(data, null);
    };

    DataTable.prototype.showCrudMessage = function (message, type) {
        $('.pixelmanager-iframe-content').scrollTop(0);
        $('.data-table-main-content').prepend(
            '<div class="alert data-table-crud-message" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><span class="message-text"></span></div>'
        );
        var alertClass = 'alert-info';
        if (typeof(type) != 'undefine') {
            switch (type) {
                case 0:
                    alertClass = 'alert-info';
                    break;
                case 1:
                    alertClass = 'alert-success';
                    break;
                case 2:
                    alertClass = 'alert-warning';
                    break;
                case 3:
                    alertClass = 'alert-danger';
                    break;
            }
        }
        $('.data-table-crud-message > .message-text').html(message);
        $('.data-table-crud-message')
            .addClass(alertClass)
            .stop(true, true)
            .fadeIn()
        ;
    }

    DataTable.prototype.deleteCrudMessage = function () {
        $('.data-table-crud-message').stop(true, true).remove();
    }

    DataTable.prototype.saveRowSuccess = function (event, data) {
        this.closeEditor();
        this.refresh();
        if (typeof(data.message) != 'undefined') {
            if (data.message != '') {
                this.showCrudMessage(data.message, data.messageType);
            }
        }
    };

    DataTable.prototype.queryDeleteRowsSuccess = function (event, data) {
        if (data.queryOk === true) {
            this.deleteSelectedRows();
        } else {
            console.log('MANNN');
            this.showCrudMessage(data.queryErrorMessage, 3);
        }
    }

    DataTable.prototype.deleteRowsSuccess = function (event, data) {
        this.refresh();
    };

    DataTable.prototype.queryCreateRowSuccess = function (event, data) {
        if (data.queryOk === true) {
            this.saveCurrentEditorDataCreateRow();
        } else {
            this.editorShowErrorMessage(data.queryErrorMessage);
        }
    };

    DataTable.prototype.queryUpdateRowSuccess = function (event, data) {
        if (data.queryOk === true) {
            this.saveCurrentEditorDataUpdateRow();
        } else {
            this.editorShowErrorMessage(data.queryErrorMessage);
        }
    };

    DataTable.prototype.createEventListenerCallbackItems = function () {
        this.eventListenerId = parent.pixelmanagerGlobal.tabs.eventListener('body', window.frameElement.id, $);
    };

    DataTable.prototype.bindEventListenerCallbackEvents = function () {
        var that = this;
        $("#" + this.eventListenerId).on("switchlanguage.pixelmanager", function (event, data) {
            that.switchLanguageEvent.call(that, event, data);
        });
    };

    DataTable.prototype.switchLanguageEvent = function (event, data) {
        if (this.isMultiLanguage()) {
            this.languagesConfig.activeLanguage = data.languageId;
            this.languagesConfig.activeSecondaryLanguage = data.secondaryLanguageId;
            if (this.dataTablesObject != null) {
                this.update();
            }
            $('.data-table-data-editor-container').dataEditor('setLanguage', this.languagesConfig.activeLanguage, this.languagesConfig.activeSecondaryLanguage);
        }
    };

    DataTable.prototype.getDataTablesTranslation = function () {
        return {
            "sEmptyTable": translate.get("No data available in table"),
            "sInfo": translate.get("Showing _START_ to _END_ of _TOTAL_ entries"),
            "sInfoEmpty": translate.get("Showing 0 to 0 of 0 entries"),
            "sInfoFiltered": translate.get("(filtered from _MAX_ total entries)"),
            "sInfoPostFix": "",
            "sInfoThousands": translate.get("_thousand_"),
            "sLengthMenu": translate.get('Display <select><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option><option value="-1">All</option></select> records'),
            "sLoadingRecords": translate.get("Loading..."),
            "sProcessing": translate.get("Processing..."),
            "sSearch": translate.get("Search:"),
            "sZeroRecords": translate.get("No matching records found"),
            "oPaginate": {
                "sFirst": translate.get("First"),
                "sLast": translate.get("Last"),
                "sNext": translate.get("Next"),
                "sPrevious": translate.get("Previous")
            },
            "oAria": {
                "sSortAscending": translate.get(": activate to sort column ascending"),
                "sSortDescending": translate.get(": activate to sort column descending")
            }
        };
    };

    DataTable.prototype.dataTablesCreateRowFunction = function (row, data, dataIndex) {
        $(row).attr('data-id', data.id);
    };

    DataTable.prototype.dataTablesStateSaveFunction = function (oSettings, oData) {
        this.dataTablesSavedState = oData;
    };

    DataTable.prototype.dataTablesStateLoadFunction = function (oSettings) {
        return (this.dataTablesSavedState);
    };

    DataTable.prototype.getDataTablesOptions = function () {
        var that = this;
        return {
            "oLanguage": this.getDataTablesTranslation(),
            "aaData": this.rawData,
            "aoColumnDefs": this.getDataTablesColumns(),
            "aaSorting": this.getDataTablesDefaultSorting(), // [[ 4, "desc" ]],
            "fnCreatedRow": function (row, data, dataIndex) {
                that.dataTablesCreateRowFunction.call(that, row, data, dataIndex);
            },
            "sDom": "<'data-table-header'<'data-table-header-length'l><'data-table-header-filter'f><'data-table-header-information'i>r>t<'data-table-footer'<'data-table-footer-pagination'p>>",
            "sPaginationType": "bootstrap",
            "iDisplayLength": -1,
            "bStateSave": true,
            "fnStateLoad": function (oSettings) {
                return (that.dataTablesStateLoadFunction.call(that, oSettings));
            },
            "fnStateSave": function (oSettings, oData) {
                that.dataTablesStateSaveFunction.call(that, oSettings, oData);
            }
        };
    };

    DataTable.prototype.getMainButtonsHtml = function () {
        return (
            '<button id="btn_add" class="btn btn-default btn-sm pull-left"><span class="glyphicon glyphicon-plus"></span> ' + translate.get('Add new') + '</button>' +
            '<div class="btn-group dropup">' +
            '<a class="btn btn-sm dropdown-toggle btn-default" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-wrench"></span> ' + translate.get('Edit') + ' <span class="caret"></span></a>' +
            '<ul class="dropdown-menu">' +
            '<li><a href="#" id="btn_select_all">' + translate.get('Select all') + '</a></li>' +
            '<li><a href="#" id="btn_select_none">' + translate.get('Select none') + '</a></li>' +
            '<li class="divider"></li>' +
            '<li><a href="#" id="btn_delete">' + translate.get('Delete selected') + '</a></li>' +
            '</ul>' +
            '</div>' +

            '<button id="btn_refresh" class="btn btn-default btn-sm pull-left"><span class="glyphicon glyphicon-refresh"></span></button>' +
            '<button id="btn_close_window" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-remove"></span> ' + translate.get('Close window') + '</button>'
        );
    };

    DataTable.prototype.getEditorButtonsHtml = function () {
        return (
            '<button id="btn_editor_apply" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-ok icon-white"></span> ' + translate.get('Save') + '</button>' +
            '<button id="btn_editor_cancel" class="btn btn-sm btn-danger"><span class="glyphicon glyphicon-remove icon-white"></span> ' + translate.get('Close') + '</button>'
        );
    };

    DataTable.prototype.insertEditorHtml = function () {
        if (this.editorTabs != null) {
            var html = '';
            var tabsHtml = '';
            var contentHtml = '';
            var i;
            for (i = 0; i < this.editorTabs.length; i++) {
                tabsHtml = tabsHtml + '<li><a href="#' + this.editorTabs[i].id + '" data-toggle="tab">' + this.editorTabs[i].caption + '</a></li>';
                contentHtml = contentHtml + '<div class="tab-pane" id="' + this.editorTabs[i].id + '"><div class="data-table-data-editor-container data-table-data-editor-container-' + this.editorTabs[i].id + '" id="tab-editor-' + this.editorTabs[i].id + '"></div></div>';
            }
            html =
                '<div class="data-table-editor-error-message-container"></div>' +
                '<ul class="nav nav-tabs">' +
                tabsHtml +
                '</ul>' +
                '<div class="tab-content">' +
                contentHtml +
                '</div>'
            ;
            $('.data-table-editor-content').append(html);
            $('.data-table-editor-content .nav-tabs a:first').tab('show');
        } else {
            $('.data-table-editor-content').append(
                '<div class="data-table-editor-error-message-container"></div>' +
                '<div class="data-table-data-editor-container"></div>'
            );
        }
    };

    DataTable.prototype.insertHtml = function () {
        if (this.containerSelector !== null) {
            $(this.containerSelector).append(
                '<div class="pixelmanager-iframe-content data-table-main-content">' +
                '<div class="data-table-container">' +
                '<table width="100%" cellpadding="0" cellspacing="0" border="0" id="data-table" class="table table-striped table-hover table-condensed table-bordered"></table>' +
                '</div>' +
                '</div>' +
                '<div class="pixelmanager-iframe-buttons data-table-main-buttons">' +
                '<div class="btn-toolbar">' +
                this.getMainButtonsHtml() +
                '</div>' +
                '</div>' +
                '<div class="pixelmanager-iframe-content data-table-editor-content">' +
                '</div>' +
                '<div class="pixelmanager-iframe-buttons data-table-editor-buttons">' +
                '<div class="btn-toolbar">' +
                this.getEditorButtonsHtml() +
                '</div>' +
                '</div>' +
                '<div class="modal" id="confirm-delete-rows">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<div class="modal-header">' +
                '<h3>' + translate.get('Confirm deleting <span class="confirm-delete-rows-count"></span> row(s)') + '</h3>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p><strong>' + translate.get('Do you really want to delete the selected <strong class="confirm-delete-rows-count"></strong> row(s)? This can\'t be undone!') + '</strong></p>' +
                '</div>' +
                '<div class="modal-footer">' +
                '<a href="javascript:;" class="btn btn-danger" id="btn-confirm-delete-rows">' + translate.get('Delete') + '</a>' +
                '<a href="javascript:;" class="btn btn-default" id="btn-cancel-delete-rows">' + translate.get('Cancel') + '</a>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>	'
            );
            fixModals.reinitialize(); // Weil das Modal-Fenster hier dynamisch nachtgräglich erzeugt wurde
        }
    };

    DataTable.prototype.showMain = function () {
        this.deleteCrudMessage();
        $('.data-table-main-content').show();
        $('.data-table-main-button').show();
        $('.data-table-editor-content').hide();
        $('.data-table-editor-buttons').hide();
    };

    DataTable.prototype.showEditor = function () {
        this.deleteCrudMessage();
        $('.data-table-main-content').hide();
        $('.data-table-main-button').hide();
        $('.data-table-editor-content').show();
        $('.data-table-editor-buttons').show();
    };

    DataTable.prototype.isMainVisible = function () {
        return ($('.data-table-main-content').is(':visible'));
    };

    DataTable.prototype.isEditorVisible = function () {
        return ($('.data-table-editor-content').is(':visible'));
    };

    DataTable.prototype.extractDataForTabFromRowData = function (rowData, tabId) {
        if (this.editorTabs == null) {
            return (rowData);
        }
        var tabData = {};
        var i, m, fieldId;
        if (typeof(rowData) != 'undefined') {
            if (rowData != null) {
                for (i = 0; i < this.editorTabs.length; i++) {
                    if (this.editorTabs[i].id == tabId) {
                        for (m = 0; m < this.editorTabs[i].fields.length; m++) {
                            fieldId = this.editorTabs[i].fields[m];
                            if (typeof(rowData[fieldId]) != 'undefined') {
                                tabData[fieldId] = rowData[fieldId];
                            }
                        }
                    }
                }
            }
        }
        return (tabData);
    };

    DataTable.prototype.extractFieldsForTab = function (tabId) {
        if (this.editorTabs == null) {
            return (this.fields);
        }
        var fields = [];
        var i, m, fieldId;
        for (i = 0; i < this.editorTabs.length; i++) {
            if (this.editorTabs[i].id == tabId) {
                for (m = 0; m < this.editorTabs[i].fields.length; m++) {
                    fieldId = this.editorTabs[i].fields[m];
                    fields.push(this.getFieldById(fieldId));
                }
            }
        }
        return (fields);
    };

    DataTable.prototype.openEditor = function (rowData, rowId) {
        this.editorRemoveErrorMessages();
        var action = 'edit';
        if (typeof(rowData) == 'undefined') {
            var rowData = {};
        } else {
            if (rowData === null) {
                rowData = {};
            }
        }
        if (typeof(rowId) == 'undefined') {
            action = 'add';
            var rowId = null;
        } else {
            if (rowId === null) {
                action = 'add';
            }
        }
        this.currentEditorAction = action;
        this.currentEditorRowId = rowId;
        this.showEditor();
        if (this.editorTabs != null) {
            var i, tabData, tabFields;
            for (i = 0; i < this.editorTabs.length; i++) {
                tabData = this.extractDataForTabFromRowData(rowData, this.editorTabs[i].id);
                tabFields = this.extractFieldsForTab(this.editorTabs[i].id);
                dataEditorBootstrap.createInstance('.data-table-data-editor-container-' + this.editorTabs[i].id, tabData, tabFields, this.languagesConfig);
            }
        } else {
            dataEditorBootstrap.createInstance('.data-table-data-editor-container', rowData, this.fields, this.languagesConfig);
        }
    };

    DataTable.prototype.closeEditor = function () {
        this.currentEditorAction = 'closed';
        $('.data-table-data-editor-container').dataEditor('destroy');
        this.showMain();
    }

    DataTable.prototype.editRow = function (rowId) {
        this.getRowRequest(rowId);
    };

    DataTable.prototype.addRow = function () {
        this.getStandardValuesRequest();
        // this.openEditor(null, null);
    };

    DataTable.prototype.getEditorData = function () {
        var data = {};
        var i;
        if (this.editorTabs != null) {
            for (i = 0; i < this.editorTabs.length; i++) {
                var tabData = {};
                tabData = $('.data-table-data-editor-container-' + this.editorTabs[i].id).dataEditor('get');
                $.extend(data, tabData);
            }
        } else {
            data = $('.data-table-data-editor-container').dataEditor('get');
        }
        return (data);
    };

    DataTable.prototype.editorRemoveErrorMessages = function () {
        $('.data-table-editor-error-message-container').empty();
    }

    DataTable.prototype.editorShowErrorMessage = function (message) {
        $('.data-table-editor-error-message-container')
            .empty()
            .append('<div class="alert alert-danger data-table-editor-error-message" style="display:none;" role="alert"></div>'
            );
        $('.data-table-editor-error-message-container .data-table-editor-error-message').html(message);
        $('.pixelmanager-iframe-content').scrollTop(0);
        $('.data-table-editor-error-message')
            .stop(true, true)
            .fadeIn()
        ;
    }

    DataTable.prototype.saveCurrentEditorDataCreateRow = function () {
        var rowData = this.getEditorData();
        parent.pixelmanagerGlobal.dataExchange.request(
            'DataTable Add Row',
            parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/createrow',
            {
                row: JSON.stringify(rowData),
            },
            this.saveRowCallbackId,
            window.frameElement.id,
            $
        );
    }

    DataTable.prototype.saveCurrentEditorDataUpdateRow = function () {
        var rowData = this.getEditorData();
        parent.pixelmanagerGlobal.dataExchange.request(
            'DataTable Update Row',
            parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/updaterow',
            {
                row: JSON.stringify(rowData),
                id: this.currentEditorRowId
            },
            this.saveRowCallbackId,
            window.frameElement.id,
            $
        );
    }

    DataTable.prototype.saveCurrentEditorQueryDataCreateRow = function () {
        var rowData = this.getEditorData();
        parent.pixelmanagerGlobal.dataExchange.request(
            'Query DataTable Add Row',
            parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/querycreaterow',
            {
                row: JSON.stringify(rowData),
            },
            this.queryCreateRowCallbackId,
            window.frameElement.id,
            $
        );
    }

    DataTable.prototype.saveCurrentEditorDataQueryUpdateRow = function () {
        var rowData = this.getEditorData();
        parent.pixelmanagerGlobal.dataExchange.request(
            'Query DataTable Update Row',
            parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/queryupdaterow',
            {
                row: JSON.stringify(rowData),
                id: this.currentEditorRowId
            },
            this.queryUpdateRowCallbackId,
            window.frameElement.id,
            $
        );
    }

    DataTable.prototype.saveCurrentEditorData = function () {
        if (this.currentEditorAction != 'closed') {
            this.editorRemoveErrorMessages();
            var rowData = this.getEditorData();
            switch (this.currentEditorAction) {
                case 'add':
                    if (this.getOption('queryBeforeCreate') === true) {
                        this.saveCurrentEditorQueryDataCreateRow();
                    } else {
                        this.saveCurrentEditorDataCreateRow();
                    }
                    break;
                case 'edit':
                    if (this.getOption('queryBeforeUpdate') === true) {
                        this.saveCurrentEditorDataQueryUpdateRow();
                    } else {
                        this.saveCurrentEditorDataUpdateRow();
                    }
                    break;
            }
        }
    };

    DataTable.prototype.bindMainButtonEvents = function () {
        var that = this;
        $('#btn_add').on('click', function (event) {
            that.buttonAddClick.call(that, event);
        });
        $('#btn_select_all').on('click', function (event) {
            that.buttonSelectAllClick.call(that, event);
        });
        $('#btn_select_none').on('click', function (event) {
            that.buttonSelectNoneClick.call(that, event);
        });
        $('#btn_delete').on('click', function (event) {
            that.buttonDeleteClick.call(that, event);
        });
        $('#btn_refresh').on('click', function (event) {
            that.buttonRefreshClick.call(that, event);
        });
        $('#btn_close_window').on('click', function (event) {
            that.buttonCloseWindowClick.call(that, event);
        });
        $('.data-table-container #data-table').on('click', 'tr', function (event) {
            that.tableRowClick.call(that, event);
        });
        $('#btn-confirm-delete-rows').on('click', function (event) {
            that.buttonConfirmDeleteRowsClick.call(that, event);
        });
        $('#btn-cancel-delete-rows').on('click', function (event) {
            that.buttonCancelDeleteRowsClick.call(that, event);
        });
    };

    DataTable.prototype.buttonAddClick = function (event) {
        this.addRow();
    };

    DataTable.prototype.deleteRowsRequest = function (idList) {
        parent.pixelmanagerGlobal.dataExchange.request(
            'DataTable Delete Rows',
            parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/deleterows',
            {
                idList: JSON.stringify(idList),
            },
            this.deleteRowsCallbackId,
            window.frameElement.id,
            $
        );
    };

    DataTable.prototype.deleteSelectedRows = function () {
        this.deleteCrudMessage();
        var idList = this.getSelectedRowIds();
        this.deleteRowsRequest(idList);
    };

    DataTable.prototype.queryDeleteRowsRequest = function (idList) {
        parent.pixelmanagerGlobal.dataExchange.request(
            'DataTable Delete Rows',
            parent.pixelmanagerGlobal.baseUrl + 'admin/modules/data-exchange/' + this.moduleUrl + '/querydeleterows',
            {
                idList: JSON.stringify(idList),
            },
            this.queryDeleteRowsCallbackId,
            window.frameElement.id,
            $
        );
    };

    DataTable.prototype.queryDeleteSelectedRows = function () {
        this.deleteCrudMessage();
        var idList = this.getSelectedRowIds();
        this.queryDeleteRowsRequest(idList);
    };

    DataTable.prototype.setTableCheckboxesState = function (state) {
        if (this.dataTablesObject != null) {
            var visibleCheckboxes = $('.data-table-row-checkbox:visible', this.dataTablesObject.fnGetNodes());
            if (visibleCheckboxes.length > 0) {
                $(visibleCheckboxes).each(function () {
                    $(this).prop('checked', state);
                });
            }
        }
    };

    DataTable.prototype.buttonSelectAllClick = function (event) {
        this.setTableCheckboxesState(true);
    };

    DataTable.prototype.buttonSelectNoneClick = function (event) {
        this.setTableCheckboxesState(false);
    };

    DataTable.prototype.buttonDeleteClick = function (event) {
        var selectedRowIds = this.getSelectedRowIds();
        if (selectedRowIds.length > 0) {
            $("#confirm-delete-rows .confirm-delete-rows-count").html(selectedRowIds.length.toString());
            $('#confirm-delete-rows').modal({
                keyboard: true,
                backdrop: true
            });
        }
    };

    DataTable.prototype.buttonRefreshClick = function (event) {
        this.deleteCrudMessage();
        this.refresh();
    };

    DataTable.prototype.buttonCloseWindowClick = function (event) {
        parent.pixelmanagerGlobal.tabs.closeTabContainingFrame(window);
    };

    DataTable.prototype.tableRowClick = function (event) {
        if ((!$(event.target).is('input')) && (!$(event.target).is('a'))) {
            var tr = $(event.target).parents('tr').last();
            var rowId = $(tr).attr('data-id');
            if (typeof(rowId) != 'undefined') {
                if (rowId != null) {
                    this.editRow(rowId);
                }
            }
        }
    };

    DataTable.prototype.buttonConfirmDeleteRowsClick = function (event) {
        $('#confirm-delete-rows').modal('hide');
        if (this.getOption('queryBeforeDelete') === true) {
            this.queryDeleteSelectedRows();
        } else {
            this.deleteSelectedRows();
        }
    };

    DataTable.prototype.buttonCancelDeleteRowsClick = function (event) {
        $('#confirm-delete-rows').modal('hide');
    };

    DataTable.prototype.bindEditorButtonEvents = function () {
        var that = this;
        $('#btn_editor_apply').on('click', function (event) {
            that.buttonEditorApplyClick.call(that, event);
        });
        $('#btn_editor_cancel').on('click', function (event) {
            that.buttonEditorCancelClick.call(that, event);
        });
    };

    DataTable.prototype.buttonEditorApplyClick = function (event) {
        this.saveCurrentEditorData();
    };

    DataTable.prototype.buttonEditorCancelClick = function (event) {
        this.closeEditor();
    };

    DataTable.prototype.create = function () {
        if (this.containerSelector !== null) {
            this.dataTablesObject = $(this.containerSelector).find('#data-table').dataTable(this.getDataTablesOptions());
        }
    };

    DataTable.prototype.destroy = function () {
        if (this.dataTablesObject !== null) {
            this.dataTablesObject.fnDestroy();
        }
    };

    DataTable.prototype.update = function () {
        this.destroy();
        this.create();
    };

    DataTable.prototype.init = function () {
        this.insertHtml();
        this.showMain();
        this.createRequestCallbackItems();
        this.bindRequestCallbackEvents();
        this.createEventListenerCallbackItems();
        this.bindEventListenerCallbackEvents();
        this.bindMainButtonEvents();
        this.bindEditorButtonEvents();
        this.getStructureRequest();
    };

    return DataTable;

});