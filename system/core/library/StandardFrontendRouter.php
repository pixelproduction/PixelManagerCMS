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

class StandardFrontendRouter implements FrontendRouterInterface
{
    var $pages = null;
    var $pageFound = false;
    var $pageId = false;
    var $languageId = false;
    var $errorPageId = null;
    var $isPreview = false;
    var $isPageLink = false;
    var $pageLinkUrl = '';
    var $pageLinkNewWindow = false;
    var $parameter = array();
    var $pluginQueryString = '';

    public function __construct()
    {
        $this->pages = new Pages();
    }

    public function route()
    {
        $path = Request::path();

        // Vorschau-Modus? (Nur für eingeloggte Benutzer des Backends verfügbar)
        $this->isPreview = false;
        if (isset($_GET['pixelmanager-preview'])) {
            if ($_GET['pixelmanager-preview'] == 'true') {
                if (Auth::isLoggedIn()) {
                    $this->isPreview = true;
                }
            }
        }

        // Sprache feststellen
        if (count($path) > 0) {
            $found_language = false;
            $found_language = $this->findLanguageByUrl($path[0]);
            if ($found_language !== false) {
                $this->languageId = $found_language;
                array_splice($path, 0, 1);
            }
        }
        if ($this->languageId === false) {
            $this->languageId = Config::get()->languages->standard;
        }

        // Seite feststellen
        if (count($path) > 0) {
            $this->pageId = $this->pages->getPageIdByPath(Pages::ROOT_ID, $path, $this->languageId);
        } else {
            $this->pageId = $this->pages->getStartPageId($this->languageId);
        }

        // Wenn die URL auch keine Seite direkt spezifiziert,
        // verweist möglicherweise der erste Teil der URL auf eine Seite
        // und der Rest sind Parameter für diese Seite.
        // Dazu fangen wir mit der kompletten URL an und dann nehmen wir immer ein
        // Teil von rechts weg, bis wir eine URL haben, die eine Seite angibt.
        // Dann fragen wir die installieren Plugins, ob für diese Seite
        // ein Aufruf von ACCEPT_QUERY_STRING uns TRUE (oder eine alternative PageId) liefert. Falls ja,
        // wird diese Seite als die aufgerufene Seite festgelegt und die
        // Variable $this->pluginQueryString auf den rechten Teil der URL gesetzt,
        // zu dem keine passende Seite gefunde werden konnte.
        if (($this->pageId === false) && (count($path) > 0)) {
            $test_path = $path;
            $query_path_for_plugin = array();
            for ($i = count($path) - 1; $i >= 0; $i--) {
                $test_page_id = $this->pages->getPageIdByPath(Pages::ROOT_ID, $test_path, $this->languageId);
                if ($test_page_id !== false) {
                    $page_properties = $this->pages->getProperties($test_page_id);
                    $query_string = implode('/', $query_path_for_plugin);
                    $parameters = array(
                        'pageId' => $test_page_id,
                        'languageId' => $this->languageId,
                        'queryString' => $query_string,
                        'pageProperties' => $page_properties
                    );
                    $query_string_accepted = false;
                    Plugins::call(Plugins::ACCEPT_QUERY_STRING, $parameters, $query_string_accepted);
                    if ($query_string_accepted === true) {
                        $this->pageId = $test_page_id;
                        $this->pluginQueryString = $query_string;
                        break;
                    } elseif ($query_string_accepted === false) {
                        // nichts tun
                    } elseif (is_numeric($query_string_accepted)) {
                        $test_page = $this->pages->getProperties($query_string_accepted);
                        if ($test_page !== false) {
                            $this->pageId = $query_string_accepted;
                            $this->pluginQueryString = $query_string;
                            break;
                        }
                    }
                }
                $test_path = array_slice($test_path, 0, count($test_path) - 1);
                array_unshift($query_path_for_plugin, $path[$i]);
            }
        }

        // Prüfen, ob die Seite aktiv ist (im Vorschau-Modus trotzdem zulassen)
        if (($this->pageId !== false) && (!$this->isPreview)) {
            if (!$this->pages->isPageActive($this->pageId)) {
                $this->pageId = false;
            }
        }

        if ($this->pageId !== false) {
            $this->pageFound = true;
        } else {
            $this->pageFound = false;
        }

        // Wenn die Seite gefunden wurde, feststellen, ob die Seite eine Weiterleitung ist
        if ($this->pageFound) {
            $properties = $this->pages->getProperties($this->pageId);
            if ($properties !== false) {
                $this->isPageLink = $this->pages->isPageLink($this->pageId, $properties);
                if ($this->pages->isPageLinkTranslated($this->pageId, $properties)) {
                    $this->pageLinkUrl = $this->pages->getTranslatedPageLinkUrl($this->pageId, $this->languageId);
                } else {
                    $this->pageLinkUrl = $this->pages->getPageLinkUrl($this->pageId, $properties);
                }
                $this->pageLinkNewWindow = $this->pages->getPageLinkNewWindow($this->pageId, $properties);
            }
        }

    }

    public function getPageId()
    {
        return ($this->pageId);
    }

    public function getLanguageId()
    {
        return ($this->languageId);
    }

    public function pageFound()
    {
        return ($this->pageFound);
    }

    public function getErrorPageId()
    {
        if ($this->errorPageId === null) {
            $this->errorPageId = false;
            if ($this->languageId !== false) {
                $error_pages = Settings::get('errorPages', array());
                $error_page_id = 0;
                if (isset($error_pages[$this->languageId])) {
                    $error_page_id = $error_pages[$this->languageId];
                    if (is_numeric($error_page_id)) {
                        if (($error_page_id != Pages::ROOT_ID) && ($error_page_id > 0)) {
                            $page_properties = $this->pages->getProperties($error_page_id);
                            if ($page_properties !== false) {
                                if ($this->pages->isPageActive($error_page_id, $page_properties)) {
                                    $this->errorPageId = $error_page_id;
                                }
                            }
                        }
                    }
                }
            }

        }
        return ($this->errorPageId);
    }

    public function getParameter()
    {
        return (Request::parameters());
    }

    public function getPluginQueryString()
    {
        return ($this->pluginQueryString);
    }

    public function isPreview()
    {
        return ($this->isPreview);
    }

    public function isPageLink()
    {
        return ($this->isPageLink);
    }

    public function getPageLinkUrl()
    {
        return ($this->pageLinkUrl);
    }

    public function getPageLinkNewWindow()
    {
        return ($this->pageLinkNewWindow);
    }

    protected function findLanguageByUrl($partial_url)
    {
        $config = Config::getArray();
        $languages = $config['languages']['list'];
        foreach ($languages as $languageId => $language) {
            $in_development = false;
            if (isset($language['inDevelopment'])) {
                if ($language['inDevelopment'] === true) {
                    $in_development = true;
                }
            }
            if (($language['url'] == $partial_url) && (!$in_development)) {
                return ($languageId);
            }
        }
        return (false);
    }

}
