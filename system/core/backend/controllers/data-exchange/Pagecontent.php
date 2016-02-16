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

class PagecontentController extends DataExchangeController
{
    protected $pages = null;
    protected $helpers = null;

    public function __construct()
    {
        $this->pages = new Pages();
        $this->helpers = new ControllerHelpers();
    }

    public function defaultAction()
    {
        $this->getAction();
    }

    public function getAction()
    {
        $id = Request::postParam('pageId');

        // �berpr�fen, ob die Lebenswichtigen Parameter gesetzt sind
        if ($id === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // �berpr�fen, ob die Seite �berhaupt (noch) existiert
        $properties = $this->pages->getProperties($id);
        if ($properties === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        // Nutzerrechte �berpr�fen
        if (!$this->helpers->canAccessPage($id, Acl::ACTION_EDIT)) {
            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
            return;
        }

        // Daten der gew�nschten Seite laden (kommt als JSON-encodierter String)
        $data = $this->pages->getData($id);
        if ($data === false) {
            $this->error();
            return;
        }
        $decoded_data = json_decode($data);

        // Die Struktur-Arrays f�r diese Seite und die verf�gbaren Elemente laden
        $pages_structure = DataStructure::pagesArray();
        if (!isset($pages_structure[$properties['template-id']]['structure'])) {
            $this->customError('A template with the ID "' . $properties['template-id'] . '" does not exist.');
            return;
        }
        $page_structure = $pages_structure[$properties['template-id']]['structure'];
        $elements = DataStructure::elementsArray();

        // Plugins aufrufen, die m�glicherweise noch was zu den Parametern der Daten-Felder hinzuzuf�gen haben...
        $parameters = array(
            'pageId' => $id,
            'templateId' => $properties['template-id']
        );
        $this->applyPluginsToDataFieldsParametersPage(Plugins::LOAD_PAGE_DATA_FIELD_PARAMETERS, $parameters,
            $page_structure);
        $this->applyPluginsToDataFieldsParametersElements(Plugins::LOAD_PAGE_DATA_FIELD_PARAMETERS, $parameters,
            $elements);

        // Die Ausgabe in ein Array packen
        $return = array(
            'pageContent' => $decoded_data,
            'pageStructure' => $page_structure,
            'elements' => $elements
        );

        // Yo.
        $this->success($return);
    }

    public function updateAction()
    {
        $id = Request::postParam('pageId');
        $jsonData = Request::postParam('jsonData');
        $preview = $this->sanitizeBoolean(Request::postParam('preview'));
        $preview_language_id = Request::postParam('previewLanguageId');

        // �berpr�fen, ob die Lebenswichtigen Parameter gesetzt sind
        if (($id === null) || ($jsonData === null) || ($preview === null) || ($preview_language_id === null)) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // �berpr�fen, ob die Seite �berhaupt (noch) existiert
        $properties = $this->pages->getProperties($id);
        if ($properties === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        // Nutzerrechte �berpr�fen
        if (!$this->helpers->canAccessPage($id, Acl::ACTION_EDIT)) {
            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
            return;
        }

        // Daten der gew�nschten Seite speichern
        if ($this->pages->setData($id, $jsonData) === false) {
            $this->error();
            return;
        }

        // �nderungs-Datum setzen
        $properties = array(
            'last-change-date' => time(),
            'last-change-user-id' => Auth::getUserId(),
            'last-change-user-name' => Auth::getScreenName(),
        );
        $this->pages->setProperties($id, $properties);
        $properties = $this->pages->getProperties($id);

        // Wenn das die Seite mit den globalen Elementen ist,
        // muss sie sofort ver�ffentlich werden und der Cache muss geleert werden,
        // da die �nderungen potenziell die Ausgabe aller Seiten betreffen k�nnte
        if ($properties['template-id'] == Pages::GLOBAL_ELEMENTS) {
            $this->pages->publish($id);
            PageCache::invalidateAll();
        }

        // R�ckgabe
        $res = array(
            'preview' => $preview
        );

        // Wenn Vorschau-Modus, dann Frontend-URL zur Vorschau-Version der gespeicherten Seite zur�ckgeben
        if ($preview) {
            $res['previewUrl'] = $this->pages->getPageUrl($id, $preview_language_id,
                    $properties) . '?pixelmanager-preview=true';
        }

        // Yo.
        $this->success($res);
    }


    protected function applyPluginsToDataFieldParameters($hook, $parameters, $field, &$field_parameters)
    {
        if ($field['type'] == 'array') {
            if (is_array($field_parameters['fields'])) {
                $plugin_parameters = array(
                    'arrayId' => $field['id'],
                );
                $plugin_parameters = array_merge($parameters, $plugin_parameters);
                for ($i = 0; $i < count($field_parameters['fields']); $i++) {
                    $changed_field_parameters = array();
                    if (isset($field_parameters['fields'][$i]['parameters'])) {
                        $changed_field_parameters = $field_parameters['fields'][$i]['parameters'];
                    }
                    $this->applyPluginsToDataFieldParameters($hook, $plugin_parameters, $field_parameters['fields'][$i],
                        $changed_field_parameters);
                    $field_parameters['fields'][$i]['parameters'] = $changed_field_parameters;
                }
            }
        } else {
            $plugin_parameters = array(
                'fieldId' => $field['id'],
                'fieldType' => $field['type'],
            );
            $plugin_parameters = array_merge($parameters, $plugin_parameters);
            $changed_field_parameters = $field_parameters;
            Plugins::call($hook, $plugin_parameters, $changed_field_parameters);
            $field_parameters = $changed_field_parameters;
        }
    }

    public function applyPluginsToDataFieldsParametersPage($hook, $parameters, &$page = null)
    {
        if (!is_array($parameters)) {
            $parameters = array();
        }
        foreach ($page as $block_id => $block) {
            if ($block['type'] == 'datablock') {
                if (isset($block['fields'])) {
                    for ($i = 0; $i < count($block['fields']); $i++) {
                        $plugin_parameters = array(
                            'blockId' => $block_id
                        );
                        $plugin_parameters = array_merge($parameters, $plugin_parameters);
                        $field_parameters = array();
                        if (isset($block['fields'][$i]['parameters'])) {
                            $field_parameters = $block['fields'][$i]['parameters'];
                        }
                        $this->applyPluginsToDataFieldParameters($hook, $plugin_parameters, $block['fields'][$i],
                            $field_parameters);
                        $page[$block_id]['fields'][$i]['parameters'] = $field_parameters;
                    }
                }
            }
        }
        return (true);
    }


    public function applyPluginsToDataFieldsParametersElements($hook, $parameters, &$elements = null)
    {
        if (!is_array($parameters)) {
            $parameters = array();
        }
        foreach ($elements as $element_id => $element) {
            for ($i = 0; $i < count($element['structure']); $i++) {
                $plugin_parameters = array(
                    'elementId' => $element_id
                );
                $plugin_parameters = array_merge($parameters, $plugin_parameters);
                $field_parameters = array();
                if (isset($element['structure'][$i]['parameters'])) {
                    $field_parameters = $element['structure'][$i]['parameters'];
                }
                $this->applyPluginsToDataFieldParameters($hook, $plugin_parameters, $element['structure'][$i],
                    $field_parameters);
                $elements[$element_id]['structure'][$i]['parameters'] = $field_parameters;
            }
        }
        return (true);
    }

}
