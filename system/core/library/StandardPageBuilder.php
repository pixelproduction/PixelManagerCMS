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

class StandardPageBuilder implements PageBuilderInterface
{

    protected $global_elements_page_structure = null;
    protected $global_elements_page_content = null;

    protected function getFieldsData($fields, $data, $language_id)
    {
        return ($data);
    }

    protected function getElement($page_id, $language_id, $element_id, $content, $preview)
    {
        $elements = DataStructure::elementsArray();
        if (!isset($elements[$element_id])) {
            Helpers::fatalError('An element with the ID "' . $element_id . '" does not exist!');
        }

        $structure = $elements[$element_id]['structure'];
        $template = $elements[$element_id]['template'];

        $pages = new Pages();
        $smarty = new Template();

        $page_properties = $pages->getProperties($page_id);
        if ($page_properties === false) {
            Helpers::fatalError('The properties of the page with the ID "' . $page_id . '" could not be loaded!');
        }

        if ($preview) {
            $page_files = $pages->getPageEditFolder($page_id, $page_properties) . Pages::PAGE_FILES_FOLDER_NAME . '/';
            $page_files_url = $pages->getPageFilesEditUrl($page_id, $page_properties);
        } else {
            $page_files = $pages->getPagePublishedFolder($page_id,
                    $page_properties) . Pages::PAGE_FILES_FOLDER_NAME . '/';
            $page_files_url = $pages->getPageFilesPublishedUrl($page_id, $page_properties);
        }
        $page_url = $pages->getPageUrl($page_id, $language_id, $page_properties);

        $smarty->assign('baseUrl', Config::get()->baseUrl);
        $smarty->assign('publicUrl', Config::get()->baseUrl . 'system/custom/frontend/public/');
        $smarty->assign('pageUrl', $page_url);
        $smarty->assign('pageId', $page_id);
        $smarty->assign('languageId', $language_id);
        $smarty->assign('templateId', $page_properties['template-id']);
        $smarty->assign('uniqueId', $page_properties['unique-id']);
        $smarty->assign('pageFiles', $page_files);
        $smarty->assign('pageFilesUrl', $page_files_url);

        if (is_array($content)) {
            foreach ($content as $key => $value) {
                $smarty->assign($key, $value);
            }
        }

        if ($page_properties['template-id'] != Pages::GLOBAL_ELEMENTS) {
            $this->assignGlobalElementsToSmarty($smarty, $language_id);
        }

        Plugins::call(
            Plugins::ASSIGN_PAGE_ELEMENT_DATA,
            array(
                'preview' => $preview,
                'pageId' => $page_id,
                'languageId' => $language_id,
                'templateId' => $page_properties['template-id'],
                'uniqueId' => $page_properties['unique-id'],
                'elementId' => $element_id,
                'pageUrl' => $page_url,
                'pageFiles' => $page_files,
                'pageFilesUrl' => $page_files_url,
                'smarty' => $smarty
            )
        );

        return ($smarty->fetch('file:[elements]' . $template));

    }

    public function loadGlobalElementsPage($smarty, $language_id)
    {
        $pages = new Pages();
        if ($pages->isGlobalElementsPageTemplateAvailable()) {
            if ($pages->isGlobalElementsPageAvailable()) {
                $global_elements_page_id = $pages->getGlobalElementsPageId();
                $global_elements_page_properties = $pages->getProperties($global_elements_page_id);
                if ($global_elements_page_properties !== false) {

                    $pages_structure = DataStructure::pagesArray();
                    if (!isset($pages_structure[Pages::GLOBAL_ELEMENTS]['structure'])) {
                        Helpers::fatalError('A page template with the ID "' . Pages::GLOBAL_ELEMENTS . '" does not exist!');
                    }
                    $this->global_elements_page_structure = $pages_structure[Pages::GLOBAL_ELEMENTS]['structure'];

                    $content_object = new PageContent();
                    if (!$content_object->load($global_elements_page_id)) {
                        Helpers::fatalError('The content of the page with the ID "' . $global_elements_page_id . '" could not be loaded!');
                    }
                    $this->global_elements_page_content = $content_object->getArray($language_id, $smarty);

                }
            }
        }
    }

    public function assignGlobalElementsToSmarty($smarty, $language_id)
    {
        $pages = new Pages();

        if ($pages->isGlobalElementsPageTemplateAvailable()) {
            if ($pages->isGlobalElementsPageAvailable()) {
                $global_elements_page_id = $pages->getGlobalElementsPageId();
                $global_elements_page_properties = $pages->getProperties($global_elements_page_id);
                if ($global_elements_page_properties !== false) {
                    $global_elements = array();
                    foreach ($this->global_elements_page_structure as $block_id => $block) {
                        if ($block['type'] == 'datablock') {
                            $block_data = null;
                            if (isset($this->global_elements_page_content[$block_id])) {
                                $block_data = $this->global_elements_page_content[$block_id];
                            }
                            $field_data = $this->getFieldsData($block['fields'], $block_data, $language_id);
                            $global_elements[$block_id] = $field_data;
                        } elseif ($block['type'] == 'container') {
                            $container_content = '';
                            if (isset($this->global_elements_page_content[$block_id])) {
                                if (is_array($this->global_elements_page_content[$block_id])) {
                                    foreach ($this->global_elements_page_content[$block_id] as $container_element_key => $container_element) {
                                        $container_content = $container_content . $this->getElement($global_elements_page_id,
                                                $language_id, $container_element['elementId'],
                                                $container_element['content'], false);
                                    }
                                }
                            }
                            $global_elements[$block_id] = $container_content;
                        }
                    }
                    $smarty->assign('globalElements', $global_elements);
                }
            }
        }
    }

    public function getPage($page_id, $language_id, $preview = false)
    {

        // Smarty initialisieren
        $smarty = new Template();

        // Seiten-Infos laden
        $pages = new Pages();
        $page_properties = $pages->getProperties($page_id);
        if ($page_properties === false) {
            Helpers::fatalError('A page with the ID "' . $page_id . '" does not exist!');
        }

        // Wenn die Vorschau-Version der Seite angefordert wurde,
        // überprüfen, ob überhaupt eine editierte Version der Seite existiert,
        // wenn nicht, Vorschau-Modus ignorieren
        if ($preview) {
            if ($page_properties['status'] == Pages::STATUS_PUBLISHED) {
                $preview = false;
            }
        }

        // Seiteneigenes Verzeichnis abhängig von $preview auslesen
        if ($preview) {
            $page_files = $pages->getPageFilesEditFolder($page_id, $page_properties);
            $page_files_url = $pages->getPageFilesEditUrl($page_id, $page_properties);
        } else {
            $page_files = $pages->getPageFilesPublishedFolder($page_id, $page_properties);
            $page_files_url = $pages->getPageFilesPublishedUrl($page_id, $page_properties);
        }
        $page_url = $pages->getPageUrl($page_id, $language_id, $page_properties);

        // Grundlegende Variablen zuweisen, die zwar mit dem Inhalt nichts zu tun haben, aber wichtig sind :-)
        $smarty->assign('baseUrl', Config::get()->baseUrl);
        $smarty->assign('publicUrl', Config::get()->baseUrl . 'system/custom/frontend/public/');
        $smarty->assign('pageUrl', $page_url);
        $smarty->assign('pageId', $page_id);
        $smarty->assign('languageId', $language_id);
        $smarty->assign('templateId', $page_properties['template-id']);
        $smarty->assign('uniqueId', $page_properties['unique-id']);
        $smarty->assign('pageFiles', $page_files);
        $smarty->assign('pageFilesUrl', $page_files_url);
        $languages = Config::get()->languages->list->getArrayCopy();
        foreach ($languages as $key => $value) {
            $languages[$key]['id'] = $key;
        }
        $smarty->assign('languages', $languages);

        // Seiten-Struktur laden
        $pages_structure = DataStructure::pagesArray();
        if (!isset($pages_structure[$page_properties['template-id']]['structure'])) {
            Helpers::fatalError('A page template with the ID "' . $page_properties['template-id'] . '" does not exist!');
        }
        $page_structure = $pages_structure[$page_properties['template-id']]['structure'];


        // Die folgenden Parameter werden an die Modifikatoren und Plugins übergeben
        $parameters = array(
            'preview' => $preview,
            'pageId' => $page_id,
            'languageId' => $language_id,
            'templateId' => $page_properties['template-id'],
            'uniqueId' => $page_properties['unique-id'],
            'pageUrl' => $page_url,
            'pageFiles' => $page_files,
            'pageFilesUrl' => $page_files_url,
            'smarty' => $smarty,
        );

        // Seiten-Inhalt laden
        $content_object = new PageContent();
        if (!$content_object->load($page_id, !$preview)) {
            Helpers::fatalError('The content of the page with the ID "' . $page_id . '" could not be loaded!');
        }
        $page_content = $content_object->getArray($language_id, $smarty, $parameters);

        // Globale Elemente laden
        $this->loadGlobalElementsPage($smarty, $language_id);
        $this->assignGlobalElementsToSmarty($smarty, $language_id);

        // Inhalte zuweisen
        foreach ($page_structure as $block_id => $block) {
            if ($block['type'] == 'datablock') {
                $block_data = null;
                if (isset($page_content[$block_id])) {
                    $block_data = $page_content[$block_id];
                }
                $smarty->assign($block_id, $block_data);
            } elseif ($block['type'] == 'container') {
                $container_content = '';
                if (isset($page_content[$block_id])) {
                    if (is_array($page_content[$block_id])) {
                        foreach ($page_content[$block_id] as $container_element_key => $container_element) {
                            $container_content = $container_content . $this->getElement($page_id, $language_id,
                                    $container_element['elementId'], $container_element['content'], $preview);
                        }
                    }
                }
                $smarty->assign($block_id, $container_content);
            }
        }

        // registrierte Plugins aufrufen, vielleicht hat ja noch jemand etwas hinzuzufügen :-)
        Plugins::call(Plugins::ASSIGN_PAGE_DATA, $parameters);

        // Smarty-Template für die Template-ID der aktuellen Seite zurückgeben
        $page_template = $pages_structure[$page_properties['template-id']];
        return ($smarty->fetch('file:[pages]' . $page_template['template']));
    }

    public function outputHeader($page_id, $language_id)
    {
        if (!headers_sent()) {
            header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
            header("Content-type: text/html; utf-8"); // MIME-Type und Encoding senden
            header("Content-Language: " . $language_id); // MIME-Type und Encoding senden
        }
    }

}
