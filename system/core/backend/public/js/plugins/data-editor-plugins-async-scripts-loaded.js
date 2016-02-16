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

    var scripts = {};
    var callback = null;
    var pageState = null;
    var callbackAlreadyCalled = false;

    function check() {
        if (!callbackAlreadyCalled) {
            var id;
            var allLoaded = true;
            for (id in scripts) {
                if (!scripts[id]) {
                    allLoaded = false;
                }
            }
            if (allLoaded && (callback != null)) {
                callback();
                callbackAlreadyCalled = true;
            }
        }
    }

    var methods = {

        startPageCreation: function (setCallbackFunc) {
            callback = setCallbackFunc;
            callbackAlreadyCalled = false;
            pageState = 'started';
        },

        finishPageCreation: function () {
            pageState = 'finished';
            check();
        },

        startLoadingScript: function (id) {
            scripts[id] = false;
        },

        finishLoadingScript: function (id) {
            scripts[id] = true;
            if (pageState == 'finished') {
                check();
            }
        }

    };

    $.fn.dataEditorPluginsAsyncScriptsLoaded = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.startPageCreation.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.dataEditorPluginsAsyncScriptsLoaded');
        }
    }

})(jQuery);