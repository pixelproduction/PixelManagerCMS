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
    var translate = require('modules/translate');

    // ***************************************************************************
    // Interne Variablen unf Funktionen (aufrufende Module können nicht darauf zugreifen)
    // ***************************************************************************

    // Liste mit laufenden Anfragen
    var pendingRequests = {};

    // Login nötig?
    var loginRequired = false;

    // Status-Codes dieser Anfragen
    var STATUS_PENDING = 0;
    var STATUS_SUCCESS = 1;
    var STATUS_ERROR = -1;
    var STATUS_LOG_IN_REQUIRED = -2;

    // Mögliche Rückgabe-Werte im Feld "Result" der Server-Antworten
    // (diese Werte müssen die gleichen sein wie in system/core/library/DataExchangeController.php)
    var RESULT_OK = 100;
    var RESULT_ERROR_UNKOWN = 0;
    var RESULT_ERROR_NOT_LOGGED_IN = 1;
    var RESULT_ERROR_NOT_AUHTORIZED = 2;
    var RESULT_ERROR_BAD_REQUEST = 3;
    var RESULT_ERROR_DOES_NOT_EXIST = 4;
    var RESULT_ERROR_CUSTOM = 99;

    function cloneObject(obj) {
        var newObj = jQuery.extend(true, {}, obj);
        return (newObj);
    }

    function getResultErrorString(resultCode) {
        if (resultCode != null) {
            switch (resultCode) {
                case RESULT_OK:
                    return (translate.get('No error'));
                    break;
                case RESULT_ERROR_UNKOWN:
                    return (translate.get('The request could not be processed (unkown error)'));
                    break;
                case RESULT_ERROR_NOT_LOGGED_IN:
                    return (translate.get('No user logged in (session expired)'));
                    break;
                case RESULT_ERROR_NOT_AUHTORIZED:
                    return (translate.get('Insufficent user privileges'));
                    break;
                case RESULT_ERROR_BAD_REQUEST:
                    return (translate.get('The request could not be processed (bad request)'));
                    break;
                case RESULT_ERROR_DOES_NOT_EXIST:
                    return (translate.get('The resource / page does not exist anymore. Maybe it was deleted by an other user.'));
                    break;
            }
        }
        return (translate.get('The request could not be processed'));
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
        return ('_' + str);
    }

    function addRequest(requestDescription, requestUrl, requestData, callbackElementId, iFrameId, iFrameJquery, isSilent) {
        var requestId = getRandomId();
        pendingRequests[requestId] = {
            description: requestDescription,
            url: requestUrl,
            data: requestData,
            callback: callbackElementId,
            iframe: iFrameId,
            jquery: iFrameJquery,
            silent: isSilent,
            status: STATUS_PENDING,
            resultCode: null,
            customErrorMessage: ''
        };
        return (requestId);
    }

    function startRequest(requestDescription, requestUrl, requestData, callbackElementId, iFrameId, iFrameJquery, silent) {
        var requestId = addRequest(requestDescription, requestUrl, requestData, callbackElementId, iFrameId, iFrameJquery, silent);
        updateVisualFeedback();
        $.ajax(requestUrl, {
            cache: false,
            data: requestData,
            dataType: 'json',
            type: 'POST',
            success: function (data, textStatus, jqXHR) {
                requestSuccess(data, textStatus, jqXHR, requestId);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                requestError(jqXHR, textStatus, errorThrown, requestId)
            }
        });
    }

    function restartRequest(requestId) {
        var request = getRequest(requestId);
        if (request != null) {
            removeRequest(requestId);
            startRequest(
                request.description,
                request.url,
                request.data,
                request.callback,
                request.iframe,
                request.jquery,
                request.silent
            );
        }
        updateVisualFeedback();
    }

    function removeRequest(requestId) {
        if (typeof(pendingRequests[requestId]) != "undefined") {
            delete(pendingRequests[requestId]);
        }
    }

    function requestExists(requestId) {
        return ( (typeof(pendingRequests[requestId]) != "undefined") );
    }

    function getRequest(requestId) {
        var ret = null;
        if (requestExists(requestId)) {
            ret = pendingRequests[requestId];
        }
        return (ret);
    }

    function requestFailed(requestId) {
        if (requestExists(requestId)) {
            pendingRequests[requestId].status = STATUS_ERROR;
        }
    }

    function requestRequiresLogin(requestId) {
        if (requestExists(requestId)) {
            pendingRequests[requestId].status = STATUS_LOG_IN_REQUIRED;
        }
        loginRequired = true;
    }

    function triggerCallback(event, request, requestData) {
        if (typeof(request.callback) != "undefined") {
            if (typeof(request.iframe) != "undefined") {
                var iframe = $("#" + request.iframe);
                if (iframe.length > 0) {
                    var callbackElement = $("#" + request.iframe).contents().find("#" + request.callback);
                    if (callbackElement.length > 0) {
                        if (typeof(request.jquery) != "undefined") {
                            callbackElement = request.jquery("#" + request.callback);
                            if (callbackElement.length > 0) {
                                request.jquery(callbackElement).triggerHandler(event, requestData.data);
                            }
                        }
                    }
                }
            } else {
                $("#" + request.callback).triggerHandler(event, requestData.data);
            }
        }
    }

    function requestsPending() {
        var counter = 0;
        var request;
        for (request in pendingRequests) {
            counter = counter + 1;
        }
        if (counter > 0) {
            return (true);
        } else {
            return (false);
        }
    }

    function getNextFailedRequestId() {
        var request;
        for (request in pendingRequests) {
            if (pendingRequests[request].status == STATUS_ERROR) {
                return (request);
            }
        }
        return (false);
    }

    function isRequestSilent(requestId) {
        if (requestExists(requestId)) {
            if (typeof(pendingRequests[requestId].silent) != 'undefined') {
                if (pendingRequests[requestId].silent == true) {
                    return (true);
                }
            }
        }
        return (false);
    }

    function arePendingRequestsSilent() {
        var request;
        for (request in pendingRequests) {
            if (!isRequestSilent(request)) {
                return (false);
            }
        }
        return (true);
    }

    function updateVisualFeedback() {
        $(".pixelmanager-main-synchronize").removeClass('pixelmanager-main-synchronize-error');
        if (loginRequired) {
            $(".pixelmanager-main-synchronize").addClass('pixelmanager-main-synchronize-error');
            $(".pixelmanager-main-synchronize").show();
            $('#pixelmanager-main-synchronize-login').modal({
                keyboard: false,
                backdrop: false
            });
        } else {
            if (requestsPending()) {
                if (!arePendingRequestsSilent()) {
                    $(".pixelmanager-main-synchronize").show();
                }
                var failedRequestId = getNextFailedRequestId();
                if (failedRequestId != false) {
                    var failedRequest = getRequest(failedRequestId);
                    if (!isRequestSilent(failedRequestId)) {
                        var errorString = '';
                        if (failedRequest.resultCode == RESULT_ERROR_CUSTOM) {
                            errorString = failedRequest.customErrorMessage;
                        } else {
                            errorString = getResultErrorString(failedRequest.resultCode);
                        }
                        $(".pixelmanager-main-synchronize").addClass('pixelmanager-main-synchronize-error');
                        $('#pixelmanager-main-synchronize-error-request-id').val(failedRequestId);
                        $('#pixelmanager-main-synchronize-error .modal-body').empty().append('<p><strong>' + failedRequest.description + '</strong><br>' + errorString + '</p>');
                        $('#pixelmanager-main-synchronize-error').modal({
                            keyboard: false,
                            backdrop: false
                        });
                    } else {
                        var clonedRequestObj = cloneObject(failedRequest);
                        removeRequest(failedRequestId);
                        triggerCallback("fail.pixelmanager", clonedRequestObj, {})
                    }
                }
            } else {
                $(".pixelmanager-main-synchronize").hide();
            }
        }
    }

    function requestSuccess(data, textStatus, jqXHR, requestId) {
        if ((typeof(data) != 'undefined') && (data != null)) {
            if (typeof(data.result) != "undefined") {
                if (data.result == RESULT_OK) {
                    var requestObj = getRequest(requestId);
                    var clonedRequestObj = cloneObject(requestObj);
                    var clonedDataObj = cloneObject(data);
                    removeRequest(requestId);
                    updateVisualFeedback();
                    triggerCallback("success.pixelmanager", clonedRequestObj, clonedDataObj)
                } else {
                    if (requestExists(requestId)) {
                        pendingRequests[requestId].resultCode = data.result;
                        pendingRequests[requestId].customErrorMessage = data.customErrorMessage;
                        if (data.result == RESULT_ERROR_NOT_LOGGED_IN) {
                            requestRequiresLogin(requestId);
                        } else {
                            requestFailed(requestId);
                        }
                    }
                }
            } else {
                requestFailed(requestId);
            }
        } else {
            requestFailed(requestId);
        }
        updateVisualFeedback();
    }

    function requestError(jqXHR, textStatus, errorThrown, requestId) {
        requestFailed(requestId);
        updateVisualFeedback();
    }

    function restartRequestsRequiringLogin() { // Hammer Name oder? :-)
        var request;
        for (request in pendingRequests) {
            if (pendingRequests[request].status == STATUS_LOG_IN_REQUIRED) {
                restartRequest(request);
            }
        }
    }

    function tryLogin(userPassword) {
        $.ajax(pixelmanagerGlobal.baseUrl + 'admin/data-exchange/login', {
            cache: false,
            data: {
                login: pixelmanagerGlobal.userLoginName,
                password: userPassword,
                language: pixelmanagerGlobal.backendLanguage
            },
            dataType: 'json',
            type: 'POST',
            success: function (data, textStatus, jqXHR) {
                if (typeof(data.result) != "undefined") {
                    if (data.result == RESULT_OK) {
                        loginRequired = false;
                        restartRequestsRequiringLogin();
                    }
                }
                updateVisualFeedback();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                updateVisualFeedback();
            }
        });
    }

    function loginFailed() {
        var request;
        for (request in pendingRequests) {
            if (pendingRequests[request].status == STATUS_LOG_IN_REQUIRED) {
                var requestObj = getRequest(request);
                var clonedRequestObj = cloneObject(requestObj);
                removeRequest(request);
                triggerCallback("fail.pixelmanager", clonedRequestObj, {});
            }
        }
        loginRequired = false;
        updateVisualFeedback();
    }

    // ***************************************************************************
    // Objekt zurückgeben (alles was hier zurückgegeben wird, ist für aufrufende Module sichtbar)
    // ***************************************************************************

    return {

        createCallbackItem: function (container_selector, iframeId) {
            var elementId = getRandomId();
            var htmlCode = '<input type="hidden" id="' + elementId + '" value="">';
            if (typeof(iframeId) != "undefined") {
                $("#" + iframeId).contents().find(container_selector).append(htmlCode);
            } else {
                $(container_selector).append(htmlCode);
            }
            return (elementId);
        },

        request: function (requestDescription, requestUrl, requestData, callbackElementId, iFrameId, iFrameJquery, silent) {
            startRequest(requestDescription, requestUrl, requestData, callbackElementId, iFrameId, iFrameJquery, silent);
        },

        retryFailedRequest: function (requestId) {
            restartRequest(requestId);
        },

        dismissFailedRequest: function (requestId) {
            var requestObj = getRequest(requestId);
            var clonedRequestObj = cloneObject(requestObj);
            removeRequest(requestId);
            updateVisualFeedback();
            triggerCallback("fail.pixelmanager", clonedRequestObj, {})
        },

        login: function (password) {
            tryLogin(password);
        },

        dismissLogin: function () {
            loginFailed();
        }

    };
});