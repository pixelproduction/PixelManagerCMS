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

    // Dependencies
    var $ = require('jquery');

    // Interne Variablen (aufrufende Module können nicht darauf zugreifen)
    function closeModalPopup() {
        var container = $('#modal-popup-container');
        if (container.length > 0) {
            $(container).hide();

            // Da diese Funktion teilweise von Funktionen INNERHALB des iFrames aufgerufen wird,
            // und diese noch nicht beendet sind, wenn der iFrame entfernt wird,
            // muss erst ein neuer Content in den iFrame geladen werden.
            // Nach einigem Testen und Fluchen habe ich das herausgefunden.
            // Den Trick mit dem "about:blank" habe ich mir aus dem Quellcode der Fancybox abgeschaut.
            // Jetzt funktioniert es in IE8-11, Firefox und Chrome.
            // Der IE hatte sonst immer einen kruden JavaScript-Fehler ausgegeben und der Chrome war einfach abgeschmiert.
            $(container).find('iframe').first()
                .load(function () {
                    $(container).find('.modal-popup-close-button').off('click');
                    $(container).empty();
                })
                .attr('src', 'about:blank')
            ;
        }
    }

    function closeButtonClickHandler(event) {
        closeModalPopup();
    }

    // Objekt zurückgeben (alles was hier zurückgegeben wird, ist für aufrufende Module sichtbar)
    return {

        close: function () {
            closeModalPopup();
        },

        open: function (iframeUrl) {
            this.close();
            var container = $('#modal-popup-container');
            if (container.length > 0) {
                $(container).empty();
                $(container).append(
                    '<div class="modal-popup-backdrop"></div>' +
                    '<div class="modal-popup-outer">' +
                    '<div class="modal-popup-inner">' +
                    '<iframe id="modal-popup-iframe" src="" frameborder="0"></iframe>' +
                    '</div>' +
                    '<div class="modal-popup-close-button-container">' +
                    '<button class="btn btn-danger btn-xs modal-popup-close-button"><span class="glyphicon glyphicon-remove"></span></button>' +
                    '</div>' +
                    '</div>'
                );
                $(container).find('.modal-popup-close-button').on('click', closeButtonClickHandler);
                $(container).show();
                $(container).find('#modal-popup-iframe').first().attr('src', iframeUrl);
            }
        }

    };
});