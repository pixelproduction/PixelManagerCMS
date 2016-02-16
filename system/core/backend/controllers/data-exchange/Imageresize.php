<?php

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

require_once(realpath(dirname(__FILE__) . '/../../../library/wideimage/WideImage.php'));

class ImageresizeController extends DataExchangeController
{
    const ERROR_MESSAGE = 'The image could not be resized. Possible reasons are: 1) Not enough memory available for the PHP script / image to big, 2) image is not a valid JPEG, PNG or GIF file, 3) problems with the server configuration.';
    const TEMP_FILES_MAX_AGE = 86400; // Tempor�re Dateien nach 1 Tag (60 * 60 * 24 = 86400 Sekunden) l�schen

    private function isParamSet($params, $key)
    {
        if (isset($params[$key])) {
            if ($params[$key] !== null) {
                return (true);
            }
        }
        return (false);
    }

    private function isParameterPositiveNumber($parameters, $key)
    {
        if ($this->isParamSet($parameters, $key)) {
            if (is_numeric($parameters[$key])) {
                if ($parameters[$key] > 0) {
                    return (true);
                }
            }
        }
        return (false);
    }

    private function getRandomFilenameWithTimeStamp($orig_filename)
    {
        $info = pathinfo($orig_filename);
        $ext = UTF8String::strtolower($info['extension']);
        // Der Zeitstempel ist Teil des Dateinamens, da filectime usw nicht
        // unbedingt zuverl�ssig sind (z.B. nach Kopie auf einen anderen Server)
        return (time() . '-' . md5(uniqid() . time() . rand(1, 99999)) . '.' . $ext);
    }

    private function saveImage(&$image, $dest, $jpegQuality)
    {
        if ($jpegQuality !== null) {
            $image->saveToFile($dest, $jpegQuality);
        } else {
            $image->saveToFile($dest);
        }
    }

    private function getDimensions($image)
    {
        $dimensions = array();
        $dimensions['width'] = $image->getWidth();
        $dimensions['height'] = $image->getHeight();
        return ($dimensions);
    }

    private function equalDimensions($dim1, $dim2)
    {
        if ((!is_array($dim1)) || (!is_array($dim2))) {
            return (false);
        }
        if (($dim1['width'] !== $dim2['width']) || ($dim1['height'] !== $dim2['height'])) {
            return (false);
        }
        return (true);
    }

    private function resizeImage($source, $parameters, $preview = false, $custom_settings = null)
    {

        // TODO *******
        //
        // Leider bietet diese drecks GD-Lib keine M�glichkeit, einen "out of memory"-Fehler zu umgehen,
        // oder vorauszusehen, wann ein Bild zu gro� f�r den Speicher ist.
        //
        // Daher sollte hier noch ein Mechanismus geschaffen werden, um voraus zu berechnen, wieviel Speicher
        // es brauchen wird, das Bild zu laden und einen Fehlercode zur�ckzugeben, falls der Speicher nicht reicht!


        // gew�nschte JPEG-Kompression feststellen
        $source_path_info = pathinfo($source);
        $source_ext = UTF8String::strtolower($source_path_info['extension']);
        if (($source_ext == 'jpg') || ($source_ext == 'jpg') || ($source_ext == 'jpe')) {
            if ($this->isParameterPositiveNumber($parameters, 'jpegQuality')) {
                $jpeqQuality = $parameters['jpegQuality'];
            } else {
                $jpeqQuality = Config::get()->jpegQuality;
            }
        } else {
            $jpeqQuality = null;
        }

        // Erstmal keine Gr��en�nderung annehmen
        $mode = 'copy';

        // Pr�fen, ob maximal-Werte angegeben sind
        $max_width = 0;
        $max_height = 0;
        if ($this->isParameterPositiveNumber($parameters, 'maxWidth')) {
            $max_width = $parameters['maxWidth'];
        }
        if ($this->isParameterPositiveNumber($parameters, 'maxHeight')) {
            $max_height = $parameters['maxHeight'];
        }

        // Wenn Maximal-Werte gesetzt sind, Einpassung annehmen
        if (($max_width > 0) || ($max_height > 0)) {
            $mode = 'fitIn';
        }

        // Pr�fen, ob genaue Abmessungen gefordert sind
        $force_width = 0;
        $force_height = 0;
        if ($this->isParameterPositiveNumber($parameters, 'forceWidth')) {
            $force_width = $parameters['forceWidth'];
        }
        if ($this->isParameterPositiveNumber($parameters, 'forceHeight')) {
            $force_height = $parameters['forceHeight'];
        }

        // ggf. Benutzereingaben auswerten
        if ($custom_settings !== null) {

            // der Benutzer hat selbst eine Gr��e festgelegt...
            // diese Werte �berschreiben forceWidth und forceHeight
            if ($this->sanitizeBoolean($custom_settings['customSize'])) {
                if ($this->sanitizeInteger($custom_settings['width']) > 0) {
                    $force_width = $this->sanitizeInteger($custom_settings['width']);
                } else {
                    $force_width = 0;
                }
                if ($this->sanitizeInteger($custom_settings['height']) > 0) {
                    $force_height = $this->sanitizeInteger($custom_settings['height']);
                } else {
                    $force_height = 0;
                }
            }

            // der Benutzer hat einen Ausschnitt festgelegt oder die s/w option gew�hlt...
            if ($this->sanitizeBoolean($custom_settings['customCrop']) || $this->sanitizeBoolean($custom_settings['convertToBlackAndWhite'])) {
                $tmp_file = APPLICATION_ROOT . 'user-data/tmp/images/' . $this->getRandomFilenameWithTimeStamp($source);
                $tmp_image = WideImage::load($source);

                if ($this->sanitizeBoolean($custom_settings['customCrop'])) {
                    $tmp_image = $tmp_image->crop(
                        $this->sanitizeInteger($custom_settings['cropX1']),
                        $this->sanitizeInteger($custom_settings['cropY1']),
                        $this->sanitizeInteger($custom_settings['cropX2']) - $this->sanitizeInteger($custom_settings['cropX1']),
                        $this->sanitizeInteger($custom_settings['cropY2']) - $this->sanitizeInteger($custom_settings['cropY1'])
                    );
                }

                if ($this->sanitizeBoolean($custom_settings['convertToBlackAndWhite'])) {
                    $tmp_image = $tmp_image->asGrayscale();
                }

                $this->saveImage($tmp_image, $tmp_file, $jpeqQuality);
                $tmp_image->destroy();
                $source = $tmp_file;
            }
        }


        // Wenn genaue Abmessungen gefordert sind, entsprechenden Modus setzen...
        if (($force_width > 0) && ($force_height > 0)) {
            $mode = 'exact';
        } elseif ($force_width > 0) {
            $mode = 'forceWidth';
        } elseif ($force_height > 0) {
            $mode = 'forceHeight';
        }

        // Tempor�ren Dateinamen erstellen
        $temp_file_name = $this->getRandomFilenameWithTimeStamp($source);
        $relative_path_to_dest_file = 'user-data/tmp/images/' . $temp_file_name;
        $absolute_path_to_dest_file = APPLICATION_ROOT . $relative_path_to_dest_file;

        // Je nach Modus Bild kopieren / ge�nderte Version speichern
        $result = true;
        try {
            switch ($mode) {
                case 'copy':
                    // keine Gr��en�nderung erfoderlich
                    $result = FileUtils::copyFile($source, $absolute_path_to_dest_file);
                    break;
                case 'exact':
                    // Gr��e genau vorgegeben
                    $image = WideImage::load($source);
                    $image_dimensions = $this->getDimensions($image);
                    $resized_image = $image->resize($force_width, $force_height, 'outside', 'any');
                    $resized_image_dimensions = $this->getDimensions($resized_image);
                    $image->destroy();
                    if (($resized_image->getWidth() > $force_width) || ($resized_image->getHeight() > $force_height)) {
                        $crop_x = 0;
                        if ($resized_image->getWidth() > $force_width) {
                            $crop_x = floor(($resized_image->getWidth() - $force_width) / 2);
                        }
                        $crop_y = 0;
                        if ($resized_image->getHeight() > $force_height) {
                            $crop_y = floor(($resized_image->getHeight() - $force_height) / 2);
                        }
                        $cropped_image = $resized_image->crop($crop_x, $crop_y, $force_width, $force_height);
                        $resized_image->destroy();
                        $this->saveImage($cropped_image, $absolute_path_to_dest_file, $jpeqQuality);
                        $cropped_image->destroy();
                    } else {
                        if ($this->equalDimensions($image_dimensions, $resized_image_dimensions)) {
                            FileUtils::copyFile($source, $absolute_path_to_dest_file);
                        } else {
                            $this->saveImage($resized_image, $absolute_path_to_dest_file, $jpeqQuality);
                        }
                        $resized_image->destroy();
                    }
                    break;
                case 'forceWidth':
                    // Breite vorgegeben
                    $image = WideImage::load($source);
                    $image_dimensions = $this->getDimensions($image);
                    $resized_image = $image->resize($force_width, null, 'outside', 'any');
                    $resized_image_dimensions = $this->getDimensions($resized_image);
                    $image->destroy();
                    if (($max_height > 0) && ($resized_image->getHeight() > $max_height)) {
                        $crop_y = 0;
                        if ($resized_image->getHeight() > $max_height) {
                            $crop_y = floor(($resized_image->getHeight() - $max_height) / 2);
                        }
                        $cropped_image = $resized_image->crop(0, $crop_y, $force_width, $max_height);
                        $resized_image->destroy();
                        $this->saveImage($cropped_image, $absolute_path_to_dest_file, $jpeqQuality);
                        $cropped_image->destroy();
                    } else {
                        if ($this->equalDimensions($image_dimensions, $resized_image_dimensions)) {
                            FileUtils::copyFile($source, $absolute_path_to_dest_file);
                        } else {
                            $this->saveImage($resized_image, $absolute_path_to_dest_file, $jpeqQuality);
                        }
                        $resized_image->destroy();
                    }
                    break;
                case 'forceHeight':
                    // H�he vorgegeben
                    $image = WideImage::load($source);
                    $image_dimensions = $this->getDimensions($image);
                    $resized_image = $image->resize(null, $force_height, 'outside', 'any');
                    $resized_image_dimensions = $this->getDimensions($resized_image);
                    $image->destroy();
                    if (($max_width > 0) && ($resized_image->getWidth() > $max_width)) {
                        $crop_x = 0;
                        if ($resized_image->getWidth() > $max_width) {
                            $crop_x = floor(($resized_image->getWidth() - $max_width) / 2);
                        }
                        $cropped_image = $resized_image->crop($crop_x, 0, $max_width, $force_height);
                        $resized_image->destroy();
                        $this->saveImage($cropped_image, $absolute_path_to_dest_file, $jpeqQuality);
                        $cropped_image->destroy();
                    } else {
                        if ($this->equalDimensions($image_dimensions, $resized_image_dimensions)) {
                            FileUtils::copyFile($source, $absolute_path_to_dest_file);
                        } else {
                            $this->saveImage($resized_image, $absolute_path_to_dest_file, $jpeqQuality);
                        }
                        $resized_image->destroy();
                    }
                    break;
                case 'fitIn':
                    // in angegebenes Rechteck einpassen
                    $image = WideImage::load($source);
                    $image_dimensions = $this->getDimensions($image);
                    if ($max_width == 0) {
                        $max_width = null;
                    }
                    if ($max_height == 0) {
                        $max_height = null;
                    }
                    $resized_image = $image->resize($max_width, $max_height, 'inside', 'down');
                    $resized_image_dimensions = $this->getDimensions($resized_image);
                    $image->destroy();
                    if ($this->equalDimensions($image_dimensions, $resized_image_dimensions)) {
                        FileUtils::copyFile($source, $absolute_path_to_dest_file);
                    } else {
                        $this->saveImage($resized_image, $absolute_path_to_dest_file, $jpeqQuality);
                    }
                    $resized_image->destroy();
                    break;
            }
        } catch (WideImage_Exception $e) {
            $result = false;
        }

        // im Erfolgsfall zum Stammverzeichnis relativen Pfad zur�ckgeben
        if ($result) {

            // M�glicherweise wird das Bild nochmal von einem geladenen Plugin bearbeitet
            $plugin_parameters = array(
                'source' => $source,
                'parameters' => $parameters,
                'preview' => $preview,
                'customSettings' => $custom_settings
            );
            Plugins::call(Plugins::AFTER_IMAGE_RESIZE, $plugin_parameters, $relative_path_to_dest_file);

            return ($relative_path_to_dest_file);
        } else {
            return (false);
        }
    }

    private function removeOldTempFiles()
    {
        $handle = @opendir(APPLICATION_ROOT . 'user-data/tmp/images/');
        if ($handle !== false) {
            while (false !== ($item = @readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (@is_file(APPLICATION_ROOT . 'user-data/tmp/images/' . $item)) {
                        // Der Zeitstempel ist Teil des Dateinamens, da filectime usw nicht
                        // unbedingt zuverl�ssig sind (z.B. nach Kopie auf einen anderen Server)
                        $temp = explode('-', $item);
                        $timestamp = $temp[0];
                        if (is_numeric($timestamp)) {
                            if ((time() - $timestamp) > self::TEMP_FILES_MAX_AGE) {
                                FileUtils::deleteFile(APPLICATION_ROOT . 'user-data/tmp/images/' . $item);
                            }
                        }
                    }
                }
            }
            @closedir($handle);
        }
    }

    public function defaultAction()
    {
        $parameters = Request::postParam('parameters');
        $relative_path_to_source = Request::postParam('source');
        $preview = $this->sanitizeBoolean(Request::postParam('preview', false));
        if ($preview === null) {
            $preview = false;
        }
        $custom_settings = Request::postParam('customSettings');
        $is_new_image = $this->sanitizeBoolean(Request::postParam('isNewImage'));
        if ($is_new_image === null) {
            $is_new_image = false;
        }
        $is_edited_image = $this->sanitizeBoolean(Request::postParam('isEditedImage'));
        if ($is_edited_image === null) {
            $is_edited_image = false;
        }

        if ((!is_array($parameters)) || ($relative_path_to_source === null) || (trim($relative_path_to_source) == '')) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $absolute_path_to_source = APPLICATION_ROOT . 'user-data/images/' . $relative_path_to_source;
        if (!is_file($absolute_path_to_source)) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        $this->removeOldTempFiles();

        $relative_path_to_resized_image = $this->resizeImage($absolute_path_to_source, $parameters, $preview,
            $custom_settings);

        if ($relative_path_to_resized_image !== false) {
            $additional_sizes = array();
            if (($this->isParamSet($parameters, 'additionalSizes')) && (!$preview)) {
                if (is_array($parameters['additionalSizes'])) {
                    foreach ($parameters['additionalSizes'] as $additional) {
                        if (is_array($additional)) {
                            if ($this->isParamSet($additional, 'id')) {
                                if (trim($additional['id']) != '') {
                                    $relative_path_to_additional_size = $this->resizeImage($absolute_path_to_source,
                                        $additional);
                                    if ($relative_path_to_additional_size !== false) {
                                        $additional_sizes[$additional['id']] = $relative_path_to_additional_size;
                                    } else {
                                        $this->customError(self::ERROR_MESSAGE);
                                        return;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (count($additional_sizes) == 0) {
                $additional_sizes = null;
            }

            $source_image = WideImage::load($absolute_path_to_source);
            $source_image_width = $source_image->getWidth();
            $source_image_height = $source_image->getHeight();
            $source_image->destroy();

            $resized_image = WideImage::load(APPLICATION_ROOT . $relative_path_to_resized_image);
            $resized_image_width = $resized_image->getWidth();
            $resized_image_height = $resized_image->getHeight();
            $resized_image->destroy();

            $result = array(
                'resizedImage' => $relative_path_to_resized_image,
                'resizedWidth' => $resized_image_width,
                'resizedHeight' => $resized_image_height,
                'originalImage' => array(
                    'relativeUrl' => $relative_path_to_source,
                    'width' => $source_image_width,
                    'height' => $source_image_height
                ),
                'additionalSizes' => $additional_sizes,
                'preview' => $preview,
                'isNewImage' => $is_new_image,
                'isEditedImage' => $is_edited_image,
            );
            $this->success($result);

        } else {
            $this->customError(self::ERROR_MESSAGE);
            return;
        }

    }

    public function checkoriginalimageAction()
    {
        $original_image = Request::postParam('originalImage');

        if (!is_array($original_image)) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $result = array(
            'originalImageStillExists' => false,
            'originalImageHasStillSameDimensions' => false,
        );

        $absolute_path_to_source = APPLICATION_ROOT . 'user-data/images/' . $original_image['relativeUrl'];
        if (is_file($absolute_path_to_source)) {
            $result['originalImageStillExists'] = true;
            $image = WideImage::load($absolute_path_to_source);
            if (($original_image['width'] == $image->getWidth()) && ($original_image['height'] == $image->getHeight())) {
                $result['originalImageHasStillSameDimensions'] = true;
            }
        }

        $this->success($result);
    }

}
