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

    var jquery_ui_bootstrap = require('plugins/jquery-ui-bootstrap-no-conflict');

    function stupidNaNPreventer(value) {
        function isNumber(o) {
            return !isNaN(o - 0) && o != null;
        }

        if (!isNumber(value)) {
            return (0);
        } else {
            return (value);
        }
    }

    function repositionModal(modal) {
        var modalDialog = modal.find('.modal-dialog');
        if (modalDialog.hasClass('pixelmanager-full-width-modal-dialog')) {
            var width = $(window).width() - 40;
            modalDialog.css('width', width.toString() + 'px');
        }
        var modalContent = modal.find('.modal-content');
        var modalBody = modal.find('.modal-body').first();
        var contentBorderTop = stupidNaNPreventer(parseInt(modalContent.css('border-top-width'), 10));
        var contentBorderBottom = stupidNaNPreventer(parseInt(modalContent.css('border-bottom-width'), 10));
        var headerHeight = modal.find('.modal-header').first().outerHeight(true);
        var footerHeight = modal.find('.modal-footer').first().outerHeight(true);
        var bodyMarginTop = stupidNaNPreventer(parseInt(modalBody.css('margin-top'), 10));
        var bodyMarginBottom = stupidNaNPreventer(parseInt(modalBody.css('margin-bottom'), 10));
        var bodyMaxHeight = $(window).height() - headerHeight - footerHeight - bodyMarginTop - bodyMarginBottom - contentBorderTop - contentBorderBottom;
        modalBody.css('max-height', bodyMaxHeight.toString() + 'px');
        modalDialog.css('margin-left', Math.floor(($(window).width() - modalDialog.outerWidth(true)) / 2) + 'px');
        modalDialog.css('margin-top', Math.floor(($(window).height() - modalDialog.outerHeight(true)) / 2) + 'px');
    }

    function init() {
        $('.modal').removeClass('fade');

        $('.modal').on('shown.bs.modal', function (e) {
            repositionModal($(this));
            return this;
        });

    }

    init();

    $(window).resize(function (event) {
        $('.modal:visible').each(function () {
            repositionModal($(this));
        });
    });

    return {
        reinitialize: function () {
            init();
        }
    };

});