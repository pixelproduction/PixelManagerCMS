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

class PageContent
{
    private $pages = null;
    private $loaded_page_id = null;
    private $content_as_json = null;
    private $content_as_array = null;
    private $content_is_published_version = null;

    public function __construct()
    {
        $this->pages = new Pages();
    }

    private function setPublished($published)
    {
        $page_properties = $this->pages->getProperties($this->loaded_page_id);
        if ($page_properties !== false) {
            if ($published === true) {
                if ($page_properties['status'] == Pages::STATUS_NEW) {
                    $published = false;
                }
            } else {
                if ($page_properties['status'] == Pages::STATUS_PUBLISHED) {
                    $published = true;
                }
            }
        }
        $this->content_is_published_version = $published;
    }

    private function isPublished()
    {
        return ($this->content_is_published_version);
    }

    public function loadById($page_id, $published = true, $ignore_plugins = false)
    {
        $this->content_as_json = null;
        $this->content_as_array = null;
        $content = $this->pages->getData($page_id, $published, $ignore_plugins);
        if ($content !== false) {
            $this->content_as_json = $content;
            $this->loaded_page_id = $page_id;
            $this->setPublished($published);
            return (true);
        }
        return (false);
    }

    public function loadByUniqueId($page_unique_id, $published = true, $ignore_plugins = false)
    {
        $page_id = $this->pages->getPageIdByUniqueId($mixed_id);
        if ($page_id !== false) {
            return ($this->loadById($page_id, $published, $ignore_plugins));
        }
        return (false);
    }

    public function load($mixed_id, $published = true, $ignore_plugins = false)
    {
        $page_id = false;
        if (is_int($mixed_id)) {
            $page_id = $mixed_id;
        } else if (is_numeric($mixed_id)) {
            $page_id = (int)$mixed_id;
        } else if (is_string($mixed_id)) {
            $page_id = $this->pages->getPageIdByUniqueId($mixed_id);
        }
        if ($page_id !== false) {
            return ($this->loadById($page_id, $published, $ignore_plugins));
        }
        return (false);
    }

    public function getJson()
    {
        return ($this->content_as_json);
    }

    public function getArrayRaw()
    {
        if ($this->content_as_array === null) {
            $json = $this->getJson();
            if ($json !== null) {
                $this->content_as_array = json_decode($json, true);
            }
        }
        return ($this->content_as_array);
    }

    public function getArray($language_id = null, $smarty = null, $parameters = null)
    {
        // Wenn keine Sprache angegeben, Standard-Sprache annehmen
        if ($language_id === null) {
            $language_id = Config::get()->languages->standard;
        }

        // Die "rohen" Daten laden
        $input = $this->getArrayRaw();
        $output = null;

        if ($input !== null) {

            // Seiten-Eigenschaften laden
            $page_id = $this->loaded_page_id;
            $page_properties = $this->pages->getProperties($page_id);
            if ($page_properties === false) {
                return (null);
            }

            // Daten-Struktur für diese Seite laden
            $pages_structure = DataStructure::pagesArray();
            if (!isset($pages_structure[$page_properties['template-id']]['structure'])) {
                Helpers::debugError('A page template with the ID "' . $page_properties['template-id'] . '" does not exist!');
                return (null);
            }
            $page_structure = $pages_structure[$page_properties['template-id']]['structure'];

            // Der Pfad zu den zugehörigen Dateien (Bilder) einer Seite unterscheiden sich
            // je nachdem, ob die veröffentlichte oder die editierte Version der Seite angefragt wurde
            if ($this->isPublished()) {
                $page_files = $this->pages->getPageFilesPublishedFolder($page_id, $page_properties);
                $page_files_url = $this->pages->getPageFilesPublishedUrl($page_id, $page_properties);
            } else {
                $page_files = $this->pages->getPageFilesEditFolder($page_id, $page_properties);
                $page_files_url = $this->pages->getPageFilesEditUrl($page_id, $page_properties);
            }
            $page_url = $this->pages->getPageUrl($page_id, $language_id, $page_properties);

            // Die Parameter für die Plugins und Modifikatoren zusammenstellen
            // ggf. werden einige oder alle dieser Parameter über die Variable $parameters
            // wieder überschrieben
            if (!is_array($parameters)) {
                $parameters = array();
            }
            $standard_parameters = array(
                'preview' => (!$this->isPublished()),
                'pageId' => $page_id,
                'languageId' => $language_id,
                'templateId' => $page_properties['template-id'],
                'uniqueId' => $page_properties['unique-id'],
                'pageUrl' => $page_url,
                'pageFiles' => $page_files,
                'pageFilesUrl' => $page_files_url,
            );
            if ($smarty !== null) {
                $standard_parameters['smarty'] = $smarty;
            }
            $parameters = array_merge($standard_parameters, $parameters);

            // Das Ausgabe-Array wird ggf. durch Plugins und
            // höchstwahrscheinlich durch die Modifikatoren geändert
            $output = $input;
            if ($smarty !== null) {
                $this->pages->applyPluginsToDataFields(Plugins::ASSIGN_PAGE_DATA_FIELD, $parameters, $output,
                    $page_properties);
            }
            DataModifiers::applyAll($output, $page_structure, $parameters);

        }
        return ($output);
    }

    private function extendedTrim($string)
    {
        return (trim($string, " \t\n\r\0\x0B>"));
    }

    private function getPathFromString($path_string)
    {
        $path_string = $this->extendedTrim($path_string);
        $path = explode('>', $path_string);
        for ($i = 0; $i < count($path); $i++) {
            $path[$i] = $this->extendedTrim($path[$i]);
        }
        return ($path);
    }

    public function extract($path_string, &$data = null)
    {
        if ($data === null) {
            $data = $this->getArray();
        }
        if (!is_array($data)) {
            return (null);
        }
        $path = $this->getPathFromString($path_string);
        $pointer =& $data;
        $counter = 0;
        if (count($path) > 0) {
            foreach ($path as $key) {

                $index = $key;
                if (UTF8String::substr($key, 0, 1) == '[') {
                    if (UTF8String::substr($key, -1, 1) == ']') {
                        $number = $this->extendedTrim(UTF8String::substr($key, 1, UTF8String::strlen($key) - 2));
                        if (is_numeric($number)) {
                            $index = (int)$number;
                        }
                    }
                }

                if (isset($pointer[$index])) {
                    $pointer =& $pointer[$index];
                    if ($counter == count($path) - 1) {
                        return ($pointer);
                    }
                } else {
                    break;
                }
                $counter++;
            }
        }
        return (null);
    }

}
