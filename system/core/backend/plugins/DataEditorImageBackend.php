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

class DataEditorImageBackendPlugin implements PluginInterface
{
    private $files_to_delete = array();
    private $files_to_keep = array();

    public function register()
    {
        return array(
            array(
                'hookId' => Plugins::BEFORE_SAVE_PAGE_DATA_FIELDS,
                'methodName' => 'onBeforeSavePageDataFields'
            ),
            array(
                'hookId' => Plugins::SAVE_PAGE_DATA_FIELD,
                'methodName' => 'onSavePageDataField'
            ),
            array(
                'hookId' => Plugins::AFTER_SAVE_PAGE_DATA_FIELDS,
                'methodName' => 'onAfterSavePageDataFields'
            ),
            array(
                'hookId' => Plugins::LOAD_PAGE_DATA_FIELD,
                'methodName' => 'onLoadPageDataField'
            )
        );
    }

    private function getRandomFilename($orig_filename)
    {
        $info = pathinfo($orig_filename);
        $ext = UTF8String::strtolower($info['extension']);
        return (md5(uniqid() . time() . rand(1, 99999)) . '.' . $ext);
    }

    public function onBeforeSavePageDataFields($parameters, &$data)
    {
        $this->files_to_delete = array();
        $this->files_to_keep = array();
    }

    public function onSavePageDataField($parameters, &$data)
    {
        // Nur auf Bilder andwenden
        if ($parameters['fieldType'] == 'image') {
            $orig_data = $data;
            $edit_data = null;

            if (isset($data['action'])) {

                // ********************************************************************
                // Daten wurden verändert / durch "getData" des Plugins bearbeitet
                // ********************************************************************

                if ($data['action'] == 'none') {
                    if (isset($data['overwriteOccured'])) {
                        if ($data['overwriteOccured'] == true) {
                            $data['action'] = 'overwrite';
                        }
                    }
                }

                // Wenn Bild gelöscht oder überschrieben werden soll,
                // die alte(n) Datei(en) in die Liste der zu löschenden Dateien aufnehmen
                if (($data['action'] == 'remove') || ($data['action'] == 'overwrite')) {
                    if (isset($data['existingImage'])) {
                        if (trim($data['existingImage']) != '') {
                            if (file_exists($parameters['pageFiles'] . $data['existingImage'])) {
                                $this->files_to_delete[] = basename($data['existingImage']);
                            }
                        }
                    }
                    if (isset($data['existingAdditionalSizes'])) {
                        if (is_array($data['existingAdditionalSizes'])) {
                            foreach ($data['existingAdditionalSizes'] as $additional) {
                                $this->files_to_delete[] = basename($additional);
                            }
                        }
                    }
                }

                // Wenn ein neues Bild eingesetzt werden soll,
                // die neue(n) Datei(en) in den "Edit"-Ordner der Seite verschieben
                // und in die Liste der zu erhaltenden Dateien aufnehmen
                if ($data['action'] == 'overwrite') {

                    if (isset($data['newImage'])) {
                        if (trim($data['newImage']) != '') {
                            $orig_file = APPLICATION_ROOT . $data['newImage'];
                            if (file_exists($orig_file)) {
                                $new_image_name = $this->getRandomFilename($orig_file);
                                FileUtils::rename($orig_file, $parameters['pageFiles'] . $new_image_name);
                                $this->files_to_keep[] = basename($new_image_name);
                                $edit_data = array(
                                    'imageRelativePath' => $new_image_name,
                                    'additionalSizes' => null
                                );
                                if (isset($data['originalImage'])) {
                                    $edit_data['originalImage'] = $data['originalImage'];
                                }
                                if (isset($data['customSettings'])) {
                                    $edit_data['customSettings'] = $data['customSettings'];
                                }
                            }
                            if (isset($data['newAdditionalSizes'])) {
                                if (is_array($data['newAdditionalSizes'])) {
                                    $edit_data['additionalSizes'] = array();
                                    foreach ($data['newAdditionalSizes'] as $additional_id => $additional) {
                                        $orig_file = APPLICATION_ROOT . $additional;
                                        if (file_exists($orig_file)) {
                                            $new_image_name = $this->getRandomFilename($orig_file);
                                            FileUtils::rename($orig_file, $parameters['pageFiles'] . $new_image_name);
                                            $this->files_to_keep[] = basename($new_image_name);
                                            $edit_data['additionalSizes'][$additional_id] = $new_image_name;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Wenn keine Änderung stattfinden soll,
                    // die Datei(en) in die Liste der zu erhaltenden Dateien aufnehmen
                } elseif ($data['action'] == 'none') {

                    $this->files_to_keep[] = basename($data['existingImage']);
                    if (isset($data['existingAdditionalSizes'])) {
                        if (is_array($data['existingAdditionalSizes'])) {
                            foreach ($data['existingAdditionalSizes'] as $additional_id => $additional) {
                                $this->files_to_keep[] = basename($additional);
                            }
                        }
                    }
                    $edit_data = array(
                        'imageRelativePath' => $data['existingImage'],
                        'additionalSizes' => $data['existingAdditionalSizes']
                    );
                    if (isset($data['originalImage'])) {
                        $edit_data['originalImage'] = $data['originalImage'];
                    }
                    if (isset($data['customSettings'])) {
                        $edit_data['customSettings'] = $data['customSettings'];
                    }

                }

            } else {

                // ********************************************************************
                // Daten wurden unverändert durchgeschleust
                // ********************************************************************

                if (isset($data['imageRelativePath'])) {
                    if (trim($data['imageRelativePath']) != '') {
                        $this->files_to_keep[] = basename($data['imageRelativePath']);
                        $edit_data = array(
                            'imageRelativePath' => $data['imageRelativePath'],
                            'additionalSizes' => null
                        );
                        if (isset($data['additionalSizes'])) {
                            if (is_array($data['additionalSizes'])) {
                                foreach ($data['additionalSizes'] as $additional_id => $additional) {
                                    $this->files_to_keep[] = basename($additional);
                                }
                            }
                            $edit_data['additionalSizes'] = $data['additionalSizes'];
                        }
                        if (isset($data['originalImage'])) {
                            $edit_data['originalImage'] = $data['originalImage'];
                        }
                        if (isset($data['customSettings'])) {
                            $edit_data['customSettings'] = $data['customSettings'];
                        }
                    }
                }

            }

            $data = $edit_data;
        }
    }

    public function onLoadPageDataField($parameters, &$data)
    {
        // Nur auf Bilder anwenden
        if ($parameters['fieldType'] == 'image') {
            // Da das DataEditorImagePlugin keine Möglichkeit hat, an den Pfad zu den Dateien der Seite zu kommen,
            // muss der komplette, absolute Pfad zu den Bildern hier nochmal explizit mit übergeben werden.
            if (isset($data['imageRelativePath'])) {
                $data['pageFilesUrl'] = $parameters['pageFilesUrl'];
            }
        }
    }

    public function onAfterSavePageDataFields($parameters, &$data)
    {
        // Erstmal alle Dateien löschen, die auf der Liste der zu l�schenden Dateien stehen
        if (count($this->files_to_delete) > 0) {
            foreach ($this->files_to_delete as $filename) {
                if (is_file($parameters['pageFiles'] . $filename)) {
                    FileUtils::deleteFile($parameters['pageFiles'] . $filename);
                }
            }
        }

        // Dann alle Dateien durchgehen, die noch zu finden sind
        // Wenn eine Datei nicht auf der Liste der zu erhaltenden Dateien steht,
        // ist anzunehmen, dass sie verwaist ist und gelöscht werden soll.
        $handle = @opendir($parameters['pageFiles']);
        if ($handle !== false) {
            while (false !== ($item = @readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (@is_file($parameters['pageFiles'] . $item)) {
                        if (!in_array($item, $this->files_to_keep)) {
                            if (is_file($parameters['pageFiles'] . $item)) {
                                FileUtils::deleteFile($parameters['pageFiles'] . $item);
                            }
                        }
                    }
                }
            }
            @closedir($handle);
        }
    }

}
