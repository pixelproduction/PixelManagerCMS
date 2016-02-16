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

    $.fn.dataEditorPlugins.assignDataTableRows = function (method) {

        var defaultParameters = {
            dataTableClassName: ''
        };

        var availableRowsContainerClassName = 'assign-data-table-rows-plugin-available-rows-container';
        var assignedRowsContainerClassName = 'assign-data-table-rows-plugin-assigned-rows-container';

        function insertTableInto(container, rows, isLeftContainer) {
            var containerClassName, $rowContainer;
            if (isLeftContainer) {
                containerClassName = availableRowsContainerClassName;
            } else {
                containerClassName = assignedRowsContainerClassName;
            }
            $(container)
                .empty()
                .append(
                    '<div class="assign-data-table-rows-plugin-rows-container ' + containerClassName + '"></div>'
                )
            ;
            $rowContainer = $($(container).find('.' + containerClassName));
            if (typeof(rows) == 'undefined') {
                rows = [];
            }
            if (rows === null) {
                rows = [];
            }
            if (rows.length > 0) {
                rows.forEach(function (row) {
                    var rowElement = $('<div class="assign-data-table-rows-plugin-row"><input type="checkbox"><span></span></div>')
                        .attr('data-id', row.id)
                        .attr('data-caption', row.caption)
                        ;
                    rowElement
                        .find('span')
                        .text(row.caption)
                    ;
                    $rowContainer.append(rowElement);
                });
            } else {
                $(container).find('.' + containerClassName + ' > tbody').append(
                    '<tr><td colspan="2">' + parent.pixelmanagerGlobal.translate.get('(Empty)') + '</td></tr>'
                );
            }
        }

        function refreshDataTableRows(containerId) {
            var container = $('#' + containerId);
            var loadDataTableRowsCallBackId = $(container).data('loadDataTableRowsCallBackId');
            var dataTableClassName = $(container).data('dataTableClassName');
            parent.pixelmanagerGlobal.dataExchange.request(
                parent.pixelmanagerGlobal.translate.get('Loading items'),
                parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/assigndatatablerows/getallrows',
                {
                    'dataTableClassName': dataTableClassName,
                    'languageId': parent.pixelmanagerGlobal.activeLanguage
                },
                loadDataTableRowsCallBackId,
                window.frameElement.id,
                $
            );
        }

        function processLoadedDataTableRows(containerId, data) {
            var $container = $('#' + containerId);
            var assignedDataTableRowIds = $container.data('assignedDataTableRowIds');
            var assignRows = [];
            var availableRows = [];
            var index;
            var leftListContainer, rightListContainer, availableRowsContainer, assignedRowsContainer;

            if ($.isArray(data)) {
                for (index = 0; index < data.length; index++) {
                    if ($.inArray(data[index].id.toString(), assignedDataTableRowIds) > -1) {
                        assignRows.push(data[index]);
                    } else {
                        availableRows.push(data[index]);
                    }
                }
            }

            assignedDataTableRowIds = assignRows.map(function (row) {
                return row.id.toString();
            });

            $container
                .data('allDataTableRows', data)
                .data('assignedDataTableRowIds', assignedDataTableRowIds)
            ;

            leftListContainer = $container.find('.assign-data-table-rows-plugin-container-left .assign-data-table-rows-plugin-inner');
            rightListContainer = $container.find('.assign-data-table-rows-plugin-container-right .assign-data-table-rows-plugin-inner');
            insertTableInto(leftListContainer, availableRows, true);
            insertTableInto(rightListContainer, assignRows, false);

            availableRowsContainer = getAvailableRowsContainer(containerId);
            assignedRowsContainer = getAssignedRowsContainer(containerId);
            unselectAllRows(availableRowsContainer);
            unselectAllRows(assignedRowsContainer);
            sortRowsInContainer(availableRowsContainer);
            sortRowsInContainer(assignedRowsContainer);
            updateSearchResult(availableRowsContainer);
            updateSearchResult(assignedRowsContainer);
        }

        function getAllRows(rowsContainer) {
            return $(rowsContainer).find('.assign-data-table-rows-plugin-row');
        }

        function getSelectedRows(rowsContainer) {
            return $(rowsContainer)
                .find('.assign-data-table-rows-plugin-row')
                .has('input[type="checkbox"]:checked')
                ;
        }

        function setAllRowsPropertyChecked(rowsContainer, checkedValue) {
            $(rowsContainer)
                .find('.assign-data-table-rows-plugin-row input[type="checkbox"]')
                .prop('checked', checkedValue)
            ;
            $(rowsContainer)
                .closest('.assign-data-table-rows-plugin-container')
                .first()
                .find('.assign-data-table-rows-plugin-container-headline > .assign-data-table-rows-plugin-row > input[type="checkbox"]')
                .prop('checked', checkedValue)
            ;
        }

        function selectAllRows(rowsContainer) {
            setAllRowsPropertyChecked(rowsContainer, true);
        }

        function unselectAllRows(rowsContainer) {
            setAllRowsPropertyChecked(rowsContainer, false);
        }

        function getAvailableRowsContainer(containerId) {
            return $('#' + containerId)
                .find('.assign-data-table-rows-plugin-container-left .assign-data-table-rows-plugin-inner')
                .first()
                ;
        }

        function getAssignedRowsContainer(containerId) {
            return $('#' + containerId)
                .find('.assign-data-table-rows-plugin-container-right .assign-data-table-rows-plugin-inner')
                .first()
                ;
        }

        function sortRowsInContainer(rowsContainer) {
            var rows = $(rowsContainer).find('.assign-data-table-rows-plugin-row');
            rows.sort(function (a, b) {
                return (strnatcasecmp($(a).attr('data-caption'), $(b).attr('data-caption')));
            });
            rows.detach().appendTo(rowsContainer);
        }

        function updateAssignedDataTableRows(containerId) {
            var $container = $('#' + containerId);
            var allDataTableRows = $container.data('allDataTableRows');
            var assignedRowsContainer = getAssignedRowsContainer(containerId);
            var assignedRowsInContainer = getAllRows(assignedRowsContainer);
            var assignedRowIds = $.makeArray(assignedRowsInContainer).map(function (row) {
                return ($(row).attr('data-id'));
            });
            var assignedDataTableRows = allDataTableRows.filter(function (row) {
                return ($.inArray(row.id.toString(), assignedRowIds) > -1);
            });
            var assignedDataTableRowIds = assignedDataTableRows.map(function (row) {
                return row.id.toString();
            });
            $container.data('assignedDataTableRowIds', assignedDataTableRowIds);
        }

        function moveSelectedRows(containerId, isAssignAction) {
            var $container = $('#' + containerId);
            var availableRowsContainer = getAvailableRowsContainer(containerId);
            var assignedRowsContainer = getAssignedRowsContainer(containerId);
            var selectedRows;
            if (isAssignAction) {
                selectedRows = getSelectedRows(availableRowsContainer);
                $(assignedRowsContainer).append(selectedRows);
            } else {
                selectedRows = getSelectedRows(assignedRowsContainer);
                $(availableRowsContainer).append(selectedRows);
            }
            unselectAllRows(availableRowsContainer);
            unselectAllRows(assignedRowsContainer);
            sortRowsInContainer(availableRowsContainer);
            sortRowsInContainer(assignedRowsContainer);
            updateAssignedDataTableRows(containerId);
            updateSearchResult(availableRowsContainer);
            updateSearchResult(assignedRowsContainer);
        }

        function assignSelectedRows(containerId) {
            moveSelectedRows(containerId, true);
        }

        function unassignSelectedRows(containerId) {
            moveSelectedRows(containerId, false);
        }

        function getOuterContainer(element) {
            return ($(element).closest('.assign-data-table-rows-plugin-container'));
        }

        function getRowsContainer(element) {
            var outerContainer = getOuterContainer(element);
            return ($(outerContainer).find('.assign-data-table-rows-plugin-inner'));
        }

        function getSearchTextField(rowsContainer) {
            var outerContainer = getOuterContainer(rowsContainer);
            return ($(outerContainer.find('.assign-data-table-rows-plugin-search')));
        }

        function updateInfoText(rowsContainer, text) {
            var outerContainer = getOuterContainer(rowsContainer);
            var infoText = $(outerContainer).find('.assign-data-table-rows-plugin-container-headline-info-text');
            $(infoText).text(text);
        }

        function updateSearchResult(rowsContainer) {
            var searchTextField = getSearchTextField(rowsContainer);
            var searchString = $(searchTextField).val().toLowerCase();
            var searchArray = searchString.split(' ');
            var rows = getAllRows(rowsContainer);
            var total = rows.length;
            var visible = 0;
            var infoText = '';
            $(rows).each(function () {
                var caption = $(this).attr('data-caption').toLowerCase();
                var containsSearchString = false;
                searchArray.forEach(function (partialString) {
                    if (caption.indexOf(partialString) > -1) {
                        containsSearchString = true;
                    }
                });
                if (!containsSearchString) {
                    $(this).addClass('assign-data-table-rows-plugin-row-hidden');
                } else {
                    $(this).removeClass('assign-data-table-rows-plugin-row-hidden');
                    visible++;
                }
            });
            if (searchString != '') {
                infoText = ' (' + parent.pixelmanagerGlobal.translate.get('Showing') + ' ' + visible.toString() + ' ' + parent.pixelmanagerGlobal.translate.get('of') + ' ' + total.toString() + ')';
            } else {
                infoText = ' (' + total.toString() + ')';
            }
            updateInfoText(rowsContainer, infoText);
        }

        function clearSearch(rowsContainer) {
            var searchTextField = getSearchTextField(rowsContainer);
            $(searchTextField).val('');
            updateSearchResult(rowsContainer);
        }

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {
                var $container = $('#' + containerId);
                var parameters = {};
                var assignedDataTableRowIds;
                $.extend(parameters, defaultParameters, assignedParameters);
                if (typeof(data) != 'undefined') {
                    if (data !== null) {
                        assignedDataTableRowIds = data.map(function (rowId) {
                            return (rowId.toString());
                        });
                    } else {
                        assignedDataTableRowIds = [];
                    }
                } else {
                    assignedDataTableRowIds = [];
                }
                $container.append(
                    '<div class="assign-data-table-rows-plugin-container-wrapper clearfix">' +
                    '<div class="assign-data-table-rows-plugin-container assign-data-table-rows-plugin-container-left">' +

                    '<div class="row">' +
                    '<div class="col-lg-12">' +
                    '<div class="input-group input-group-sm">' +
                    '<input type="text" class="form-control assign-data-table-rows-plugin-search" placeholder="' + parent.pixelmanagerGlobal.translate.get('Search for...') + '">' +
                    '<span class="input-group-btn">' +
                    '<button class="btn btn-default assign-data-table-rows-plugin-clear-search" type="button" title="' + parent.pixelmanagerGlobal.translate.get('Clear search result') + '"><span class="glyphicon glyphicon-remove"></span></button>' +
                    '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="assign-data-table-rows-plugin-container-headline">' +
                    '<div class="assign-data-table-rows-plugin-row">' +
                    '<input type="checkbox" class="assign-data-table-rows-select-all-available-rows-checkbox">' +
                    '<span>' + parent.pixelmanagerGlobal.translate.get('Available items') + '<span class="assign-data-table-rows-plugin-container-headline-info-text"></span></span>' +
                    '</div>' +
                    '</div>' +

                    '<div class="assign-data-table-rows-plugin-inner"></div>' +
                    '</div>' +

                    '<div class="assign-data-table-rows-plugin-buttons-container">' +
                    '<div class="btn-group-vertical btn-group-sm" role="group">' +
                    '<button type="button" class="btn btn-default assign-data-table-rows-plugin-button-assign" title="' + parent.pixelmanagerGlobal.translate.get('Assign selected items') + '"><span class="glyphicon glyphicon-chevron-right"></span></button>' +
                    '<button type="button" class="btn btn-default assign-data-table-rows-plugin-button-unassign" title="' + parent.pixelmanagerGlobal.translate.get('Unassign selected items') + '"><span class="glyphicon glyphicon-chevron-left"></span></button>' +
                    '<button type="button" class="btn btn-default assign-data-table-rows-plugin-button-refresh" title="' + parent.pixelmanagerGlobal.translate.get('Refresh') + '"><span class="glyphicon glyphicon-refresh"></span></button>' +
                    '</div>' +
                    '</div>' +

                    '<div class="assign-data-table-rows-plugin-container assign-data-table-rows-plugin-container-right">' +

                    '<div class="row">' +
                    '<div class="col-lg-12">' +
                    '<div class="input-group input-group-sm">' +
                    '<input type="text" class="form-control assign-data-table-rows-plugin-search" placeholder="' + parent.pixelmanagerGlobal.translate.get('Search for...') + '">' +
                    '<span class="input-group-btn">' +
                    '<button class="btn btn-default assign-data-table-rows-plugin-clear-search" type="button" title="' + parent.pixelmanagerGlobal.translate.get('Clear search result') + '"><span class="glyphicon glyphicon-remove"></span></button>' +
                    '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +

                    '<div class="assign-data-table-rows-plugin-container-headline">' +
                    '<div class="assign-data-table-rows-plugin-row">' +
                    '<input type="checkbox" class="assign-data-table-rows-select-all-assigned-rows-checkbox">' +
                    '<span>' + parent.pixelmanagerGlobal.translate.get('Assigned items') + '<span class="assign-data-table-rows-plugin-container-headline-info-text"></span></span>' +
                    '</div>' +
                    '</div>' +

                    '<div class="assign-data-table-rows-plugin-inner"></div>' +
                    '</div>' +
                    '</div>'
                );
                var loadDataTableRowsCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('#' + containerId, window.frameElement.id);
                $container
                    .data('loadDataTableRowsCallBackId', loadDataTableRowsCallBackId)
                    .data('dataTableClassName', parameters.dataTableClassName)
                    .data('allDataTableRows', [])
                    .data('assignedDataTableRowIds', assignedDataTableRowIds)
                ;
                refreshDataTableRows(containerId);
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
                var $container = $('#' + containerId);

                var loadDataTableRowsCallBackId = $container.data('loadDataTableRowsCallBackId');

                $('#' + loadDataTableRowsCallBackId).on("success.pixelmanager", function (event, data) {
                    processLoadedDataTableRows(containerId, data);
                });

                $container.on('click.' + eventNamespace, '.assign-data-table-rows-plugin-button-assign', function (event) {
                    assignSelectedRows(containerId);
                });

                $container.on('click.' + eventNamespace, '.assign-data-table-rows-plugin-button-unassign', function (event) {
                    unassignSelectedRows(containerId);
                });

                $container.on('click.' + eventNamespace, '.assign-data-table-rows-plugin-button-refresh', function (event) {
                    refreshDataTableRows(containerId);
                });

                $container.on('click.' + eventNamespace, '.assign-data-table-rows-select-all-available-rows-checkbox', function (event) {
                    var availableRowsContainer = getAvailableRowsContainer(containerId);
                    if ($(this).prop('checked') == true) {
                        selectAllRows(availableRowsContainer);
                    } else {
                        unselectAllRows(availableRowsContainer);
                    }
                });

                $container.on('click.' + eventNamespace, '.assign-data-table-rows-select-all-assigned-rows-checkbox', function (event) {
                    var assignedRowsContainer = getAssignedRowsContainer(containerId);
                    if ($(this).prop('checked') == true) {
                        selectAllRows(assignedRowsContainer);
                    } else {
                        unselectAllRows(assignedRowsContainer);
                    }
                });

                $container.on('keyup.' + eventNamespace, '.assign-data-table-rows-plugin-search', function (event) {
                    var rowsContainer = getRowsContainer(event.target);
                    updateSearchResult(rowsContainer);
                });

                $container.on('click.' + eventNamespace, '.assign-data-table-rows-plugin-clear-search', function (event) {
                    var rowsContainer = getRowsContainer(event.target);
                    clearSearch(rowsContainer);
                });
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
                var $container = $('#' + containerId);
                var loadDataTableRowsCallBackId = $container.data('loadDataTableRowsCallBackId');
                $container.off('.' + eventNamespace);
                $('#' + loadDataTableRowsCallBackId).off('.pixelmanager');
            },

            getData: function (containerId, assignedParameters) {
                var assignedDataTableRowIds = $('#' + containerId).data('assignedDataTableRowIds');
                var returnValue = assignedDataTableRowIds.map(function (rowId) {
                    return (parseInt(rowId, 10));
                });
                return (returnValue);
            },

            getRowHtml: function (data, assignedParameters) {
                return ('');
            },

            beforeMove: function (containerId, assignedParameters, eventNamespace) {
            },

            afterMove: function (containerId, assignedParameters, eventNamespace) {
            },

            sortCompareValues: function (value1, value2) {
                return (0);
            },

            getSortableValue: function (data, assignedParameters) {
                return ('');
            }

        };

    }

})(jQuery);
