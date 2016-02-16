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

    "use strict";

    if (typeof($.fn.dataEditorPlugins) == 'undefined') {
        $.fn.dataEditorPlugins = {};
    }

    $.fn.dataEditorPlugins.image = function (method) {

        var defaultParameters = {
            editable: false,
            maxWidth: null,
            maxHeight: null,
            forceWidth: null,
            forceHeight: null,
            additionalSizes: null,
            rowHtmlMaxWidth: 150,
            rowHtmlMaxHeight: 150,
            imageSelectorStartFolder: null
        };

        var defaultValue = {
            action: 'none',
            imageRelativePath: '',
            pageFilesUrl: '',
            additionalSizes: null,
            existingImage: null,
            existingAdditionalSizes: null,
            newImage: null,
            newAdditionalSizes: null,
            originalImage: {
                relativeUrl: null,
                width: null,
                height: null
            },
            customSettings: {
                customSize: false,
                width: null,
                height: null,
                customCrop: false,
                cropX1: null,
                cropY1: null,
                cropX2: null,
                cropY2: null,
                convertToBlackAndWhite: false
            },
            overwriteOccured: false
        };

        var jcropApi = null;

        function urldecode(str) {
            return decodeURIComponent((str + '').replace(/\+/g, '%20'));
        }

        function onSelectImageClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-buttons').first().attr('data-container-id');
            var parameters = $('#' + targetId).data('parameters');

            function imageSelectorCallback(file) {
                var relativePath = urldecode(file.url);
                relativePath = relativePath.substr(file.baseUrl.length, (file.url.length - file.baseUrl.length));
                var postData = {
                    parameters: parameters,
                    source: relativePath,
                    isNewImage: true,
                    isEditedImage: false
                };
                parent.pixelmanagerGlobal.dataExchange.request(
                    parent.pixelmanagerGlobal.translate.get('Load and resize selected image'),
                    parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/imageresize',
                    postData,
                    $('#' + targetId).data('resizeImageCallBackId'),
                    window.frameElement.id,
                    $
                );

            }

            parent.pixelmanagerGlobal.openImageSelector(imageSelectorCallback, false, parameters.imageSelectorStartFolder);
        }

        function setImageSize(targetId, $imageWrapper, width, height, zoom, setImgDimensions) {
            var calculatedWidth = Math.round(width * zoom);
            var calculatedHeight = Math.round(height * zoom);
            var $image = $imageWrapper.find('img');
            var $innerWrapper = $imageWrapper.find('.image-data-editor-plugin-edit-image-image-wrapper-inner');
            $imageWrapper
                .css('min-width', calculatedWidth.toString() + 'px')
                .css('min-height', calculatedHeight.toString() + 'px')
            ;
            $innerWrapper
                .css('width', calculatedWidth.toString() + 'px')
                .css('height', calculatedHeight.toString() + 'px')
            ;
            if (setImgDimensions) {
                $image
                    .css('width', calculatedWidth.toString() + 'px')
                    .css('height', calculatedHeight.toString() + 'px')
                ;
            }
        }

        function removeSizeOfOriginalImageTag(targetId) {
            var $imageWrapper = $('#' + targetId).find('.image-data-editor-plugin-edit-image-source-image-wrapper');
            var $image = $imageWrapper.find('img');
            $image
                .css('width', 'auto')
                .css('height', 'auto')
            ;
        }

        function setSizeOfOriginalImageTag(targetId, width, height) {
            var $imageWrapper = $('#' + targetId).find('.image-data-editor-plugin-edit-image-source-image-wrapper');
            var $image = $imageWrapper.find('img');
            $image
                .css('width', Math.round(width).toString() + 'px')
                .css('height', Math.round(height).toString() + 'px')
            ;
        }

        function setOriginalImageSize(targetId, width, height, zoom, ignoreJCrop) {
            var $imageWrapper = $('#' + targetId).find('.image-data-editor-plugin-edit-image-source-image-wrapper');
            var originalImageZoom = {
                'width': width,
                'height': height,
                'zoom': zoom
            };
            $('#' + targetId).data('originalImageZoom', originalImageZoom);
            var customSettings = getCustomSettings(targetId);
            setImageSize(targetId, $imageWrapper, width, height, zoom, !customSettings.customCrop);
            var $headlineZoom = $('#' + targetId).find('.image-data-editor-plugin-edit-image-source-container .image-data-editor-plugin-edit-image-content-headline-zoom');
            var zoomText = Math.round(zoom * 100).toString();
            $headlineZoom.html('(' + zoomText + '%)');
            if (typeof(ignoreJCrop) == 'undefined') {
                updateJCrop(targetId);
            } else {
                if (ignoreJCrop === false) {
                    updateJCrop(targetId);
                }
            }
        }

        function setPreviewImageSize(targetId, width, height, zoom) {
            var $imageWrapper = $('#' + targetId).find('.image-data-editor-plugin-edit-image-preview-image-wrapper');
            var previewImageZoom = {
                'width': width,
                'height': height,
                'zoom': zoom
            };
            $('#' + targetId).data('previewImageZoom', previewImageZoom);
            setImageSize(targetId, $imageWrapper, width, height, zoom, true);
            var $headlineZoom = $('#' + targetId).find('.image-data-editor-plugin-edit-image-preview-container .image-data-editor-plugin-edit-image-content-headline-zoom');
            var zoomText = Math.round(zoom * 100).toString();
            $headlineZoom.html('(' + zoomText + '%)');
        }

        function sanitizeZoom(zoom) {
            if (zoom < 0.1) {
                zoom = 0.1;
            }
            if (zoom > 1) {
                zoom = 1;
            }
            zoom = Math.round(zoom / 0.1) * 0.1;
            return (zoom);
        }

        function onOriginalImageZoomInClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var originalImageZoom = $('#' + targetId).data('originalImageZoom');
            var zoom = sanitizeZoom(originalImageZoom.zoom + 0.1);
            setOriginalImageSize(targetId, originalImageZoom.width, originalImageZoom.height, zoom);
        }

        function onOriginalImageZoomOutClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var originalImageZoom = $('#' + targetId).data('originalImageZoom');
            var zoom = sanitizeZoom(originalImageZoom.zoom - 0.1);
            setOriginalImageSize(targetId, originalImageZoom.width, originalImageZoom.height, zoom);
        }

        function onOriginalImageFitInClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var originalImageZoom = $('#' + targetId).data('originalImageZoom');
            var $scrollPane = $('#' + targetId).find('.image-data-editor-plugin-edit-image-source-container .image-data-editor-plugin-edit-image-content-scrollpane');
            var $imageWrapperInner = $scrollPane.find('.image-data-editor-plugin-edit-image-image-wrapper-inner');
            var paddingLeft = getIntegerFromInputValue($imageWrapperInner.css('padding-left'));
            var paddingTop = getIntegerFromInputValue($imageWrapperInner.css('padding-top'));
            var paddingRight = getIntegerFromInputValue($imageWrapperInner.css('padding-right'));
            var paddingBottom = getIntegerFromInputValue($imageWrapperInner.css('padding-bottom'));
            var containerWidth = $scrollPane.width() - paddingLeft - paddingRight;
            var containerHeight = $scrollPane.height() - paddingTop - paddingBottom;
            var zoom = 1;
            var maxZoomX = 1;
            var maxZoomY = 1;
            if (originalImageZoom.width > 0) {
                maxZoomX = containerWidth / originalImageZoom.width;
            }
            if (originalImageZoom.height > 0) {
                maxZoomY = containerHeight / originalImageZoom.height;
            }
            if (maxZoomX < maxZoomY) {
                zoom = maxZoomX;
            } else {
                zoom = maxZoomY;
            }
            if (zoom > 1) {
                zoom = 1;
            }
            setOriginalImageSize(targetId, originalImageZoom.width, originalImageZoom.height, zoom);
        }

        function onOriginalImageFullSizeClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var originalImageZoom = $('#' + targetId).data('originalImageZoom');
            setOriginalImageSize(targetId, originalImageZoom.width, originalImageZoom.height, 1);
        }

        function onPreviewImageZoomInClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var previewImageZoom = $('#' + targetId).data('previewImageZoom');
            var zoom = sanitizeZoom(previewImageZoom.zoom + 0.1);
            setPreviewImageSize(targetId, previewImageZoom.width, previewImageZoom.height, zoom);
        }

        function onPreviewImageZoomOutClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var previewImageZoom = $('#' + targetId).data('previewImageZoom');
            var zoom = sanitizeZoom(previewImageZoom.zoom - 0.1);
            setPreviewImageSize(targetId, previewImageZoom.width, previewImageZoom.height, zoom);
        }

        function onPreviewImageFitInClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var previewImageZoom = $('#' + targetId).data('previewImageZoom');
            var $scrollPane = $('#' + targetId).find('.image-data-editor-plugin-edit-image-preview-container .image-data-editor-plugin-edit-image-content-scrollpane');
            var $imageWrapperInner = $scrollPane.find('.image-data-editor-plugin-edit-image-image-wrapper-inner');
            var paddingLeft = getIntegerFromInputValue($imageWrapperInner.css('padding-left'));
            var paddingTop = getIntegerFromInputValue($imageWrapperInner.css('padding-top'));
            var paddingRight = getIntegerFromInputValue($imageWrapperInner.css('padding-right'));
            var paddingBottom = getIntegerFromInputValue($imageWrapperInner.css('padding-bottom'));
            var containerWidth = $scrollPane.width() - paddingLeft - paddingRight;
            var containerHeight = $scrollPane.height() - paddingTop - paddingBottom;
            var zoom = 1;
            var maxZoomX = 1;
            var maxZoomY = 1;
            if (previewImageZoom.width > 0) {
                maxZoomX = containerWidth / previewImageZoom.width;
            }
            if (previewImageZoom.height > 0) {
                maxZoomY = containerHeight / previewImageZoom.height;
            }
            if (maxZoomX < maxZoomY) {
                zoom = maxZoomX;
            } else {
                zoom = maxZoomY;
            }
            if (zoom > 1) {
                zoom = 1;
            }
            setPreviewImageSize(targetId, previewImageZoom.width, previewImageZoom.height, zoom);
        }

        function onPreviewImageFullSizeClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var previewImageZoom = $('#' + targetId).data('previewImageZoom');
            setPreviewImageSize(targetId, previewImageZoom.width, previewImageZoom.height, 1);
        }

        function updateCropValues(targetId, coord) {
            var settingsDialog = $('#' + targetId).find('.image-data-editor-plugin-edit-image-settings');
            settingsDialog.find('.image-data-editor-plugin-input-crop-x1').val(getStringFromInputValue(coord.x));
            settingsDialog.find('.image-data-editor-plugin-input-crop-y1').val(getStringFromInputValue(coord.y));
            settingsDialog.find('.image-data-editor-plugin-input-crop-x2').val(getStringFromInputValue(coord.x2));
            settingsDialog.find('.image-data-editor-plugin-input-crop-y2').val(getStringFromInputValue(coord.y2));
        }

        function createJCrop(targetId) {
            if (jcropApi != null) {
                destroyJCrop();
            }
            var isOnSelectFirstCall = true;
            var customSettings = getCustomSettings(targetId);
            var originalImage = $('#' + targetId).data('originalImage');
            var originalImageZoom = $('#' + targetId).data('originalImageZoom');
            var options = {
                setSelect: [
                    (customSettings.cropX1 != null ? customSettings.cropX1 : 0),
                    (customSettings.cropY1 != null ? customSettings.cropY1 : 0),
                    (customSettings.cropX2 != null ? customSettings.cropX2 : originalImage.width),
                    (customSettings.cropY2 != null ? customSettings.cropY2 : originalImage.height)
                ],

                // TrueSize funktioniert nicht in Webkit-Browsern (Chrome / safari)
                // wohl aber BoxWidth / BoxHeight, warum auch immer.
                // TrueSize ist praktischer und bietet die Möglichkeit, Bilder auch vergrößert darzustellen
                // aber leider, leider...

                // trueSize: [
                // 	originalImage.width,
                // 	originalImage.height
                // ],

                boxWidth: Math.round(originalImageZoom.width * originalImageZoom.zoom),
                boxHeight: Math.round(originalImageZoom.height * originalImageZoom.zoom),
                onSelect: function (coord) {
                    if (!isOnSelectFirstCall) {
                        updateCropValues(targetId, coord);
                        updatePreview(targetId);
                    }
                    isOnSelectFirstCall = false;
                },
                onRelease: function () {
                    if (jcropApi != null) {
                        var customSettings = getCustomSettings(targetId);
                        jcropApi.setSelect([
                            customSettings.cropX1,
                            customSettings.cropY1,
                            customSettings.cropX2,
                            customSettings.cropY2
                        ]);
                    }
                }
            };
            jcropApi = $.Jcrop('#' + targetId + ' .image-data-editor-plugin-edit-image-source-image-wrapper img', options);
        }

        function destroyJCrop() {
            if (jcropApi != null) {
                jcropApi.destroy();
                jcropApi = null;
            }
        }

        function updateJCrop(targetId) {
            var customSettings = getCustomSettings(targetId);
            var originalImageZoom = $('#' + targetId).data('originalImageZoom');
            if (customSettings.customCrop) {
                removeSizeOfOriginalImageTag(targetId);
                createJCrop(targetId);
                return (true);
            } else {
                destroyJCrop();
                setSizeOfOriginalImageTag(targetId, Math.round(originalImageZoom.width * originalImageZoom.zoom), Math.round(originalImageZoom.height * originalImageZoom.zoom));
                return (false);
            }
        }

        function openCustomSettingsDialog(targetId) {

            var originalImage = $('#' + targetId).data('originalImage');
            var customSettings = $('#' + targetId).data('customSettings');
            var container = $('#' + targetId).find('.image-data-editor-plugin-edit-image-container');
            var sourceImageContainer = container.find('.image-data-editor-plugin-edit-image-source-container');
            var timestamp = new Date().getTime();
            var previewImageZoom = {
                width: 0,
                height: 0,
                zoom: 1
            };

            setCustomSettings(targetId, customSettings);
            setOriginalImageSize(targetId, originalImage.width, originalImage.height, 1, true);

            $('#' + targetId).data('previewImageZoom', previewImageZoom);
            sourceImageContainer
                .find('.image-data-editor-plugin-edit-image-source-image-wrapper')
                .remove()
            ;
            sourceImageContainer
                .find('.image-data-editor-plugin-edit-image-content-scrollpane')
                .append(
                    '<div class="image-data-editor-plugin-edit-image-source-image-wrapper">' +
                    '<div class="image-data-editor-plugin-edit-image-image-wrapper-inner">' +
                    '<img class="image-data-editor-plugin-edit-image-source-image-img" src="" alt="">' +
                    '</div>' +
                    '</div>'
                )
            ;

            sourceImageContainer.find('.image-data-editor-plugin-edit-image-source-image-img').one("load", function (event) {
                updateJCrop(targetId);
            });
            sourceImageContainer.find('.image-data-editor-plugin-edit-image-source-image-img').attr('src', parent.pixelmanagerGlobal.baseUrl + 'user-data/images/' + originalImage.relativeUrl + '?time=' + timestamp);

            var $previewImageContainer = $('#' + targetId).find('.image-data-editor-plugin-edit-image-preview-container .image-data-editor-plugin-edit-image-content-scrollpane');
            $previewImageContainer.empty();

            container.show();
            updatePreview(targetId);
        }

        function onEditImageClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-buttons').first().attr('data-container-id');
            var test = $('#' + targetId).data();
            var originalImage = $('#' + targetId).data('originalImage');
            if (originalImage.relativeUrl != null) {
                var postData = {
                    'originalImage': originalImage
                };
                parent.pixelmanagerGlobal.dataExchange.request(
                    parent.pixelmanagerGlobal.translate.get('Check if original image still exists'),
                    parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/imageresize/checkoriginalimage',
                    postData,
                    $('#' + targetId).data('checkOriginalImageCallBackId'),
                    window.frameElement.id,
                    $
                );
            } else {
                alert(parent.pixelmanagerGlobal.translate.get('Please select an image first.'));
            }
        }

        function NaNPreventer(value) {
            function isNumber(o) {
                return !isNaN(o - 0) && o != null;
            }

            if (!isNumber(value)) {
                return (null);
            } else {
                return (value);
            }
        }

        function getIntegerFromInputValue(value) {
            value = $.trim(value);
            if (value != '') {
                return (NaNPreventer(parseInt(value, 10)));
            } else {
                return (null);
            }
        }

        function getStringFromInputValue(value) {
            if (typeof(value) == 'undefined') {
                return ('');
            } else if (value == null) {
                return ('');
            } else {
                return (value.toString());
            }
        }

        function closeCustomSettingsDialog(containerId) {
            $('#' + containerId).find('.image-data-editor-plugin-edit-image-container').hide();
        }

        function getCustomSettings(containerId) {
            var settingsDialog = $('#' + containerId).find('.image-data-editor-plugin-edit-image-settings');
            var customSettings = {
                customSize: settingsDialog.find('.image-data-editor-plugin-radio-manual-size').is(':checked'),
                width: getIntegerFromInputValue(settingsDialog.find('.image-data-editor-plugin-input-width').val()),
                height: getIntegerFromInputValue(settingsDialog.find('.image-data-editor-plugin-input-height').val()),
                customCrop: settingsDialog.find('.image-data-editor-plugin-checkbox-crop').is(':checked'),
                cropX1: getIntegerFromInputValue(settingsDialog.find('.image-data-editor-plugin-input-crop-x1').val()),
                cropY1: getIntegerFromInputValue(settingsDialog.find('.image-data-editor-plugin-input-crop-y1').val()),
                cropX2: getIntegerFromInputValue(settingsDialog.find('.image-data-editor-plugin-input-crop-x2').val()),
                cropY2: getIntegerFromInputValue(settingsDialog.find('.image-data-editor-plugin-input-crop-y2').val()),
                convertToBlackAndWhite: settingsDialog.find('.image-data-editor-plugin-checkbox-black-and-white').is(':checked')
            };
            return (customSettings);
        }

        function setCustomSettings(containerId, customSettings) {
            var settingsDialog = $('#' + containerId).find('.image-data-editor-plugin-edit-image-settings');
            settingsDialog.find('.image-data-editor-plugin-radio-auto-size').prop('checked', !customSettings.customSize);
            settingsDialog.find('.image-data-editor-plugin-radio-manual-size').prop('checked', customSettings.customSize);
            settingsDialog.find('.image-data-editor-plugin-input-width').val(getStringFromInputValue(customSettings.width));
            settingsDialog.find('.image-data-editor-plugin-input-height').val(getStringFromInputValue(customSettings.height));
            settingsDialog.find('.image-data-editor-plugin-checkbox-crop').prop('checked', customSettings.customCrop);
            settingsDialog.find('.image-data-editor-plugin-input-crop-x1').val(getStringFromInputValue(customSettings.cropX1));
            settingsDialog.find('.image-data-editor-plugin-input-crop-y1').val(getStringFromInputValue(customSettings.cropY1));
            settingsDialog.find('.image-data-editor-plugin-input-crop-x2').val(getStringFromInputValue(customSettings.cropX2));
            settingsDialog.find('.image-data-editor-plugin-input-crop-y2').val(getStringFromInputValue(customSettings.cropY2));
            settingsDialog.find('.image-data-editor-plugin-checkbox-black-and-white').prop('checked', customSettings.convertToBlackAndWhite);
        }

        function updatePreview(containerId) {
            var originalImage = $('#' + containerId).data('originalImage');
            var postData = {
                parameters: $('#' + containerId).data('parameters'),
                customSettings: getCustomSettings(containerId),
                source: originalImage.relativeUrl,
                preview: true
            };
            parent.pixelmanagerGlobal.dataExchange.request(
                parent.pixelmanagerGlobal.translate.get('Create preview image'),
                parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/imageresize',
                postData,
                $('#' + containerId).data('previewImageCallBackId'),
                window.frameElement.id,
                $,
                true
            );
        }

        function onUpdatePreviewClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            updatePreview(targetId);
        }

        function onApplyCustomSettingsClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            var customSettings = getCustomSettings(targetId);
            $('#' + targetId).data('customSettings', customSettings);
            var originalImage = $('#' + targetId).data('originalImage');
            var postData = {
                'parameters': $('#' + targetId).data('parameters'),
                'customSettings': customSettings,
                'source': originalImage.relativeUrl,
                'preview': false,
                'isNewImage': false,
                'isEditedImage': true
            };
            parent.pixelmanagerGlobal.dataExchange.request(
                parent.pixelmanagerGlobal.translate.get('Load and resize selected image'),
                parent.pixelmanagerGlobal.baseUrl + 'admin/data-exchange/imageresize',
                postData,
                $('#' + targetId).data('resizeImageCallBackId'),
                window.frameElement.id,
                $
            );
            closeCustomSettingsDialog(targetId);
        }

        function onCloseCustomSettingsClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-edit-image-container').first().attr('data-container-id');
            closeCustomSettingsDialog(targetId);
        }

        function onRemoveImageClick(event) {
            var targetId = $(event.target).parents('.image-data-editor-plugin-buttons').first().attr('data-container-id');
            $('#' + targetId).find('.image-data-editor-plugin-image-container').empty();
            appendImagePlaceholder(targetId, $('#' + targetId).data('parameters'));
            var customSettings = $.extend(true, {}, defaultValue.customSettings);
            var originalImage = $.extend(true, {}, defaultValue.originalImage);
            $('#' + targetId)
                .data('newImage', null)
                .data('newAdditionalSizes', null)
                .data('action', 'remove')
                .data('customSettings', customSettings)
                .data('originalImage', originalImage)
            ;
        }

        function appendImagePlaceholder(containerId, parameters) {
            $('#' + containerId + ' > .image-data-editor-plugin-image-container').append('<div class="image-data-editor-plugin-image-placeholder"><strong>&nbsp;</strong><span>' + parent.pixelmanagerGlobal.translate.get('No image selected') + '</span></div>');
            if (typeof(parameters.forceWidth) != 'undefined') {
                if (parameters.forceWidth != null) {
                    $('#' + containerId + ' .image-data-editor-plugin-image-placeholder').css('width', parameters.forceWidth + 'px');
                }
            }
            if (typeof(parameters.forceHeight) != 'undefined') {
                if (parameters.forceHeight != null) {
                    $('#' + containerId + ' .image-data-editor-plugin-image-placeholder').css('height', parameters.forceHeight + 'px');
                }
            }
        }

        return {

            init: function () {
            },

            insertHtml: function (containerId, data, assignedParameters) {

                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);

                var value = {};

                if (typeof(data) != 'undefined') {
                    $.extend(value, defaultValue, data);
                } else {
                    value = $.extend(true, {}, defaultValue);
                }

                $('#' + containerId).append(
                    '<div class="image-data-editor-plugin-buttons" data-container-id="' + containerId + '">' +
                    '<div class="btn-group">' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-select-image" title="' + parent.pixelmanagerGlobal.translate.get('Select an image') + '"><span class="glyphicon glyphicon-picture"></span></button>' +
                    (parameters.editable === true ? '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-edit-image" title="' + parent.pixelmanagerGlobal.translate.get('Edit image') + '"><span class="glyphicon glyphicon-wrench"></span></button>' : '') +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-remove-image" title="' + parent.pixelmanagerGlobal.translate.get('Remove image') + '"><span class="glyphicon glyphicon-remove"></span></button>' +
                    '</div>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-image-container">' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-container" data-container-id="' + containerId + '">' +
                    '<div class="image-data-editor-plugin-edit-image-settings">' +
                    '<div class="form-inline image-data-editor-plugin-edit-image-settings-spacer">' +
                    '<div class="image-data-editor-plugin-edit-image-settings-line-wrapper">' +
                    '<label class="radio"><input type="radio" class="image-data-editor-plugin-radio-auto-size" name="' + containerId + '-radio-size"> ' + parent.pixelmanagerGlobal.translate.get('Auto size') + '</label>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-settings-line-wrapper">' +
                    '<label class="radio"><input type="radio" class="image-data-editor-plugin-radio-manual-size" name="' + containerId + '-radio-size"> ' + parent.pixelmanagerGlobal.translate.get('Custom size') + '</label>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-settings-size">' +
                    '<div class="image-data-editor-plugin-edit-image-settings-line-wrapper">' +
                    '<input type="text" class="input-sm form-control image-data-editor-plugin-input-width" placeholder="" name="' + containerId + '-input-width"> <label>' + parent.pixelmanagerGlobal.translate.get('px width') + '</label><br>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-settings-line-wrapper">' +
                    '<input type="text" class="input-sm form-control image-data-editor-plugin-input-height" placeholder="" name="' + containerId + '-input-height"> <label>' + parent.pixelmanagerGlobal.translate.get('px height') + '</label><br>' +
                    '</div>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-settings-line-wrapper">' +
                    '<label class="checkbox"><input type="checkbox" class="image-data-editor-plugin-checkbox-crop" name="' + containerId + '-checkbox-crop"> ' + parent.pixelmanagerGlobal.translate.get('Crop') + '</label>' +
                    '<input type="hidden" class="image-data-editor-plugin-input-crop-x1" name="' + containerId + '-input-crop-x1">' +
                    '<input type="hidden" class="image-data-editor-plugin-input-crop-y1" name="' + containerId + '-input-crop-y1">' +
                    '<input type="hidden" class="image-data-editor-plugin-input-crop-x2" name="' + containerId + '-input-crop-x2">' +
                    '<input type="hidden" class="image-data-editor-plugin-input-crop-y2" name="' + containerId + '-input-crop-y2">' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-settings-line-wrapper">' +
                    '<label class="checkbox"><input type="checkbox" class="image-data-editor-plugin-checkbox-black-and-white" name="' + containerId + '-checkbox-black-and-white"> ' + parent.pixelmanagerGlobal.translate.get('Convert to black and white') + '</label>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-settings-line-wrapper">' +
                    '<button class="btn btn-primary btn-image-data-editor-plugin-apply-custom-settings" data-container-id="' + containerId + '">' + parent.pixelmanagerGlobal.translate.get('Apply') + '</button> ' +
                    '<button class="btn btn-default btn-image-data-editor-plugin-close-custom-settings" data-container-id="' + containerId + '">' + parent.pixelmanagerGlobal.translate.get('Cancel') + '</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-content">' +
                    '<div class="image-data-editor-plugin-edit-image-source-container">' +
                    '<div class="image-data-editor-plugin-edit-image-content-scrollpane">' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-content-buttons clearfix">' +
                    '<div class="btn-group">' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-zoom-in-original" title="' + parent.pixelmanagerGlobal.translate.get('Zoom in') + '"><span class="glyphicon glyphicon-zoom-in"></span></button>' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-zoom-out-original" title="' + parent.pixelmanagerGlobal.translate.get('Zoom out') + '"><span class="glyphicon glyphicon-zoom-out"></span></button>' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-fit-in-original" title="' + parent.pixelmanagerGlobal.translate.get('Fit in') + '"><span class="glyphicon glyphicon-fullscreen"></span></button>' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-fullsize-original" title="' + parent.pixelmanagerGlobal.translate.get('Original size') + '"><span class="glyphicon glyphicon-resize-full"></span></button>' +
                    '</div>' +
                    '<span class="image-data-editor-plugin-edit-image-content-headline">' + parent.pixelmanagerGlobal.translate.get('Original image') + ' <span class="image-data-editor-plugin-edit-image-content-headline-zoom"></span></span>' +
                    '</div>' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-preview-container">' +
                    '<div class="image-data-editor-plugin-edit-image-content-scrollpane">' +
                    '</div>' +
                    '<div class="image-data-editor-plugin-edit-image-content-buttons clearfix">' +
                    '<div class="btn-group">' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-update-preview" title="' + parent.pixelmanagerGlobal.translate.get('Refresh') + '"><span class="glyphicon glyphicon-refresh"></span></button>' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-zoom-in-preview" title="' + parent.pixelmanagerGlobal.translate.get('Zoom in') + '"><span class="glyphicon glyphicon-zoom-in"></span></button>' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-zoom-out-preview" title="' + parent.pixelmanagerGlobal.translate.get('Zoom out') + '"><span class="glyphicon glyphicon-zoom-out"></span></button>' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-fit-in-preview" title="' + parent.pixelmanagerGlobal.translate.get('Fit in') + '"><span class="glyphicon glyphicon-fullscreen"></span></button>' +
                    '<button class="btn btn-xs btn-default btn-image-data-editor-plugin-fullsize-preview" title="' + parent.pixelmanagerGlobal.translate.get('Original size') + '"><span class="glyphicon glyphicon-resize-full"></span></button>' +
                    '</div>' +
                    '<span class="image-data-editor-plugin-edit-image-content-headline">' + parent.pixelmanagerGlobal.translate.get('Preview') + ' <span class="image-data-editor-plugin-edit-image-content-headline-zoom"></span></span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                );
                if (value.imageRelativePath != '') {
                    $('#' + containerId + ' > .image-data-editor-plugin-image-container').append('<img src="" alt="">');
                    var baseUrl = value.pageFilesUrl;
                    if (value.overwriteOccured) {
                        baseUrl = parent.pixelmanagerGlobal.baseUrl;
                    }
                    $('#' + containerId + ' > .image-data-editor-plugin-image-container > img').attr('src', baseUrl + value.imageRelativePath);
                } else {
                    appendImagePlaceholder(containerId, parameters);
                }
                var resizeImageCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('#' + containerId, window.frameElement.id);
                var previewImageCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('#' + containerId, window.frameElement.id);
                var checkOriginalImageCallBackId = parent.pixelmanagerGlobal.dataExchange.createCallbackItem('#' + containerId, window.frameElement.id);
                $('#' + containerId)
                    .data('parameters', parameters)
                    .data('resizeImageCallBackId', resizeImageCallBackId)
                    .data('previewImageCallBackId', previewImageCallBackId)
                    .data('checkOriginalImageCallBackId', checkOriginalImageCallBackId)
                    .data('existingImage', value.imageRelativePath)
                    .data('existingAdditionalSizes', value.additionalSizes)
                    .data('newImage', value.newImage)
                    .data('newAdditionalSizes', value.newAdditionalSizes)
                    .data('action', value.action)
                    .data('imageRelativePath', value.imageRelativePath)
                    .data('pageFilesUrl', value.pageFilesUrl)
                    .data('additionalSizes', value.additionalSizes)
                    .data('overwriteOccured', value.overwriteOccured)
                    .data('originalImage', value.originalImage)
                    .data('customSettings', value.customSettings)
                ;
            },

            bindEvents: function (containerId, assignedParameters, eventNamespace) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);

                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-select-image', onSelectImageClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-edit-image', onEditImageClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-remove-image', onRemoveImageClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-apply-custom-settings', onApplyCustomSettingsClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-close-custom-settings', onCloseCustomSettingsClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-update-preview', onUpdatePreviewClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-zoom-in-original', onOriginalImageZoomInClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-zoom-out-original', onOriginalImageZoomOutClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-fit-in-original', onOriginalImageFitInClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-fullsize-original', onOriginalImageFullSizeClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-zoom-in-preview', onPreviewImageZoomInClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-zoom-out-preview', onPreviewImageZoomOutClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-fit-in-preview', onPreviewImageFitInClick);
                $('#' + containerId).on('click.' + eventNamespace, '.btn-image-data-editor-plugin-fullsize-preview', onPreviewImageFullSizeClick);

                $('#' + containerId).on('click.' + eventNamespace, '.image-data-editor-plugin-radio-auto-size', function (event) {
                    updatePreview(containerId);
                });

                $('#' + containerId).on('click.' + eventNamespace, '.image-data-editor-plugin-radio-manual-size', function (event) {
                    updatePreview(containerId);
                });

                $('#' + containerId).on('click.' + eventNamespace, '.image-data-editor-plugin-checkbox-black-and-white', function (event) {
                    updatePreview(containerId);
                });

                $('#' + containerId).on('blur.' + eventNamespace, '.image-data-editor-plugin-input-width', function (event) {
                    if ($('#' + containerId).find('.image-data-editor-plugin-radio-manual-size').is(':checked')) {
                        updatePreview(containerId);
                    }
                });

                $('#' + containerId).on('blur.' + eventNamespace, '.image-data-editor-plugin-input-height', function (event) {
                    if ($('#' + containerId).find('.image-data-editor-plugin-radio-manual-size').is(':checked')) {
                        updatePreview(containerId);
                    }
                });

                $('#' + containerId).on('click.' + eventNamespace, '.image-data-editor-plugin-checkbox-crop', function (event) {
                    updateJCrop(containerId);
                    updatePreview(containerId);
                });

                var resizeImageCallBackId = $('#' + containerId).data('resizeImageCallBackId');
                $('#' + resizeImageCallBackId).on("success.pixelmanager", function (event, data) {
                    var $container = $('#' + containerId).find('.image-data-editor-plugin-image-container');
                    if ($container.length > 0) {
                        $container.empty();
                        $container.append('<img src="' + parent.pixelmanagerGlobal.baseUrl + data.resizedImage + '" alt="">');
                    }
                    $('#' + containerId)
                        .data('newImage', data.resizedImage)
                        .data('newAdditionalSizes', data.additionalSizes)
                        .data('action', 'overwrite')
                        .data('originalImage', data.originalImage)
                    ;
                    if (typeof(data.isNewImage) != 'undefined') {
                        if (data.isNewImage === true) {
                            var customSettings = {};
                            customSettings = $.extend(true, {}, defaultValue.customSettings);
                            $('#' + containerId).data('customSettings', customSettings);
                        }
                    }
                });

                var previewImageCallBackId = $('#' + containerId).data('previewImageCallBackId');
                $('#' + previewImageCallBackId).on("success.pixelmanager", function (event, data) {
                    var $previewImageContainer = $('#' + containerId).find('.image-data-editor-plugin-edit-image-preview-container .image-data-editor-plugin-edit-image-content-scrollpane');
                    $previewImageContainer.empty();
                    $previewImageContainer.append(
                        '<div class="image-data-editor-plugin-edit-image-preview-image-wrapper">' +
                        '<div class="image-data-editor-plugin-edit-image-image-wrapper-inner">' +
                        '<img src="' + parent.pixelmanagerGlobal.baseUrl + data.resizedImage + '" alt="">' +
                        '</div>' +
                        '</div>'
                    );
                    var previewImageZoom = $('#' + containerId).data('previewImageZoom');
                    if (typeof(previewImageZoom) == 'undefined') {
                        previewImageZoom = {
                            width: data.resizedWidth,
                            height: data.resizedHeight,
                            zoom: 1
                        };
                    }
                    setPreviewImageSize(containerId, data.resizedWidth, data.resizedHeight, previewImageZoom.zoom);
                });

                var checkOriginalImageCallBackId = $('#' + containerId).data('checkOriginalImageCallBackId');
                $('#' + checkOriginalImageCallBackId).on("success.pixelmanager", function (event, data) {
                    if (data.originalImageStillExists && data.originalImageHasStillSameDimensions) {
                        openCustomSettingsDialog(containerId);
                    } else {
                        if (!data.originalImageStillExists) {
                            alert(parent.pixelmanagerGlobal.translate.get('The original image file could no be found. Please select a new source image file.'));
                        } else if (!data.originalImageHasStillSameDimensions) {
                            alert(parent.pixelmanagerGlobal.translate.get('The original image file changed and has different dimensions now. Please select a new source image file.'));
                        } else {
                            alert(parent.pixelmanagerGlobal.translate.get('Please select a new source image file.'));
                        }
                    }
                });

            },

            unbindEvents: function (containerId, assignedParameters, eventNamespace) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                destroyJCrop();
                $('#' + containerId).off('.' + eventNamespace);
                var resizeImageCallBackId = $('#' + containerId).data('resizeImageCallBackId');
                $('#' + resizeImageCallBackId).off('.pixelmanager');
                var previewImageCallBackId = $('#' + containerId).data('previewImageCallBackId');
                $('#' + previewImageCallBackId).off('.pixelmanager');
                var checkOriginalImageCallBackId = $('#' + containerId).data('checkOriginalImageCallBackId');
                $('#' + checkOriginalImageCallBackId).off('.pixelmanager');
            },

            getData: function (containerId, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);
                var $container = $('#' + containerId);
                var returnValue = {
                    existingImage: $container.data('existingImage'),
                    existingAdditionalSizes: $container.data('existingAdditionalSizes'),
                    newImage: $container.data('newImage'),
                    newAdditionalSizes: $container.data('newAdditionalSizes'),
                    action: $container.data('action'),
                    imageRelativePath: $container.data('imageRelativePath'),
                    pageFilesUrl: $container.data('pageFilesUrl'),
                    additionalSizes: $container.data('additionalSizes'),
                    overwriteOccured: $container.data('overwriteOccured'),
                    originalImage: $container.data('originalImage'),
                    customSettings: $container.data('customSettings')
                };
                if (returnValue.action == 'none') {
                    // in diesem Fall nichts ändern
                } else if (returnValue.action == 'overwrite') {
                    returnValue.imageRelativePath = returnValue.newImage;
                    returnValue.additionalSizes = returnValue.newAdditionalSizes;
                    returnValue.overwriteOccured = true;
                } else {
                    // wenn nicht 'none' und nicht 'overwrite'
                    // bleibt nur "remove" übrig...
                    returnValue.imageRelativePath = '';
                    returnValue.additionalSizes = null;
                }
                // $.data() speichert keine Kopien sondern Referenzen auf Objekte,
                // daher müssen wir hier per Hand eine Deep-Copy vom Rückgabe-Objekt machen
                // sonst könnte es theoretisch zu Probleme führen, wenn das zurückgegebene
                // Objekt von der aufrufenden Funktion verändert wird
                var copyOfReturnValue = $.extend(true, {}, returnValue);
                return (copyOfReturnValue);
            },

            getRowHtml: function (data, assignedParameters) {
                var parameters = {};
                $.extend(parameters, defaultParameters, assignedParameters);

                var value = {};
                if (typeof(data) != 'undefined') {
                    $.extend(value, defaultValue, data);
                } else {
                    value = $.extend(true, {}, defaultValue);
                }

                if (value.imageRelativePath != '') {
                    if ((value.action == 'none') && ( !value.overwriteOccured)) {
                        return ('<img style="max-width:' + parameters.rowHtmlMaxWidth + 'px; max-height:' + parameters.rowHtmlMaxHeight + 'px;" src="' + value.pageFilesUrl + value.imageRelativePath + '" alt="">');
                    } else if ((value.action == 'overwrite') || (value.overwriteOccured)) {
                        return ('<img style="max-width:' + parameters.rowHtmlMaxWidth + 'px; max-height:' + parameters.rowHtmlMaxHeight + 'px;" src="' + parent.pixelmanagerGlobal.baseUrl + value.imageRelativePath + '" alt="">');
                    } else {
                        return ('');
                    }
                } else {
                    return ('');
                }

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
