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

    $.fn.dataEditorPlugins.tinyMCE = function (method) {

        var defaultParameters = {
            cssFile: null,
            styleFormats: false
        };

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
            return ('_' + str);
        }

        /*
         *	initTinyMce wird mehrmals aufgerufen.
         */
        function initTinyMce(containerId, tinymceId, parameters) {
            tinymce.init({
                selector: '#' + containerId + '-tinyMCE' + tinymceId,
                language: 'de',
                element_format: 'html',
                toolbar: "newdocument, bold, italic, underline, strikethrough, alignleft, aligncenter, alignright, alignjustify, table, styleselect, formatselect, bullist, numlist, undo, redo, code, link",
                plugins: "pplink code table wordcount",
                menubar: false,
                content_css: parameters.cssFile,
                style_formats: parameters.styleFormats,
            });
        }

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {

                // Die gleiche ID zweimal zu benuzten funktioniert nicht!
                // Mit "zweimal" meine ich, die gleiche ID wieder zu verwenden,
                // nachdem man die Editor-Instanz per "tinyMCE.execCommand('mceRemoveControl'[...]" zerst�rt hat.
                // Man muss immer eine neue ID genrerien. Ob der TinyMCE einen Bug hat und nicht komplett aufr�umt,
                // keine Ahnung... Jedenfalls geht's jetzt so und ich habe schon Stunden herumprobiert
                // und graue Haare und das kalte Kotzen gekriegt.
                // Drecks Wysiwyg-Editoren. Die sind alle schei�e. Leider ist TinyMCE noch das beste OpenSource...
                // Die anderen sind alle auch nicht besser oder noch viel schlechter :-(
                var tinymceId = getRandomId();
                $('#' + containerId).data('tinymceId', tinymceId);

                /*if (typeof($.fn.dataEditorPluginsAsyncScriptsLoaded) != 'undefined') {
                 $.fn.dataEditorPluginsAsyncScriptsLoaded('startLoadingScript', containerId + '-tinyMCE' + tinymceId);
                 }

                 function initialized() {
                 if (typeof($.fn.dataEditorPluginsAsyncScriptsLoaded) != 'undefined') {
                 $.fn.dataEditorPluginsAsyncScriptsLoaded('finishLoadingScript', containerId + '-tinyMCE' + tinymceId);
                 }
                 }*/
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var value = '';
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                $('#' + containerId).append(
                    '<div class="controls tinymceControls">' +
                    '<textarea id="' + containerId + '-tinyMCE' + tinymceId + '" class="input-xxlarge"></textarea>' +
                    '</div>'
                );

                $('#' + containerId + '-tinyMCE' + tinymceId).val(value);
                initTinyMce(containerId, tinymceId, parameters);

            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
                var tinymceId = $('#' + containerId).data('tinymceId');
                tinymce.remove(tinymce.get(containerId + '-tinyMCE' + tinymceId));
            },

            getData: function (containerId, assignedParameters) {
                var tinymceId = $('#' + containerId).data('tinymceId');
                return (tinymce.get(containerId + '-tinyMCE' + tinymceId).getContent());
            },

            getRowHtml: function (data, assignedParameters) {
                var value = '';
                if (typeof(data) != 'undefined') {
                    value = data;
                }
                return (value);
            },

            beforeMove: function (containerId, assignedParameters, eventNamespace) {
                var tinymceId = $('#' + containerId).data('tinymceId');
                var data = this.getData(containerId, assignedParameters);
                //tinymce beenden
                tinymce.remove(tinymce.get(containerId + '-tinyMCE' + tinymceId));
                $('#' + containerId).data('savedDataBeforeMove', data);
                //this.unbindEvents(containerId, assignedParameters, eventNamespace);
            },

            afterMove: function (containerId, assignedParameters, eventNamespace) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var tinymceId = $('#' + containerId).data('tinymceId');
                var data = $('#' + containerId).data('savedDataBeforeMove');
                //tinymce neu initialisiern
                initTinyMce(containerId, tinymceId, parameters);
                $('#' + containerId).removeData('savedDataBeforeMove');
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
