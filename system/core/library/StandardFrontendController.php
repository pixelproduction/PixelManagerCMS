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

class StandardFrontendController implements FrontendControllerInterface
{
    public function run()
    {
        // Startup-Ereignis
        Plugins::call(Plugins::STARTUP, null);

        // Router laden
        $router_file_name = Config::get()->frontendRouter->classFile;
        if (!file_exists($router_file_name)) {
            Helpers::fatalError('Frontend router class file not found (' . $router_file_name . ' doesn\'t exist)!',
                true);
        }
        require_once($router_file_name);
        $router_class_name = Config::get()->frontendRouter->className;
        if (!class_exists($router_class_name)) {
            Helpers::fatalError('Frontend router class not found (class "' . $router_class_name . '" doesn\'t exist in ' . $router_file_name . ')!',
                true);
        }
        $router = new $router_class_name();
        Registry::set('frontendRouter', $router);

        // PageBuilder laden
        $page_builder_file_name = Config::get()->pageBuilder->classFile;
        if (!file_exists($page_builder_file_name)) {
            Helpers::fatalError('PageBuilder class file not found (' . $page_builder_file_name . ' doesn\'t exist)!',
                true);
        }
        require_once($page_builder_file_name);
        $page_builder_class_name = Config::get()->pageBuilder->className;
        if (!class_exists($page_builder_class_name)) {
            Helpers::fatalError('PageBuilder class not found (class "' . $page_builder_class_name . '" doesn\'t exist in ' . $page_builder_file_name . ')!',
                true);
        }
        $page_builder = new $page_builder_class_name();
        Registry::set('pageBuilder', $page_builder);

        // routing
        $router->route();
        $languageId = $router->getLanguageId();
        if ($router->pageFound()) {
            $pageId = $router->getPageId();
            $error_404 = false;
        } else {
            $pageId = $router->getErrorPageId();
            $error_404 = true;
        }

        if ($pageId !== false) {

            // Ist die Seite ein Link? Dann einfach auf die angegebene URL weiterleiten...
            if ($router->isPageLink()) {
                Helpers::redirect($router->getPageLinkUrl(), Config::get()->pageLinkRedirectionResponseCode);
                exit();
            }

            // �bersetzungen laden
            $config = Config::getArray();
            $languages = $config['languages']['list'];
            setlocale(LC_ALL, $languages[$languageId]['locale']);
            if (is_array($languages[$languageId]['translation'])) {
                if (count($languages[$languageId]['translation']) > 0) {
                    foreach ($languages[$languageId]['translation'] as $translation_file) {
                        Translate::loadStrings($translation_file, $languageId);
                    }
                }
            } else {
                if ($languages[$languageId]['translation'] != '') {
                    Translate::loadStrings($languages[$languageId]['translation'], $languageId);
                }
            }

            // Before-Display-Ereignis
            Plugins::call(
                Plugins::BEFORE_DISPLAY,
                array(
                    'preview' => $router->isPreview(),
                    'pageId' => $pageId,
                    'languageId' => $languageId
                )
            );

            if (!$router->isPreview()) {

                // Versuchen, die Seite aus dem Cache zu holen
                $output = PageCache::getPage($pageId, $languageId);
                $output_cached = false;

                // Keine Version im Cache verf�gbar, Seite neu erzeugen
                if ($output === false) {
                    $output = $page_builder->getPage($pageId, $languageId);
                } else {
                    $output_cached = true;
                }

                // Wenn noch nicht im Cache, erzeugte Ausgabe im Cache ablegen
                if (!$output_cached) {
                    PageCache::cachePage($pageId, $languageId, $output);
                }

            } else {

                // Im Vorschau-Modus den Cache nicht verwenden
                // Und dem PageBuilder sagen, dass er die Vorschau-Version erstellen soll
                $output = $page_builder->getPage($pageId, $languageId, true);
                $output_cached = false;

            }

            // HTTP-Header senden
            if ($error_404) {
                if (!headers_sent()) {
                    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                }
            }
            $page_builder->outputHeader($pageId, $languageId);

            // Header-Senden-Ereignis
            Plugins::call(
                Plugins::SEND_HEADER,
                array(
                    'preview' => $router->isPreview(),
                    'pageId' => $pageId,
                    'languageId' => $languageId
                )
            );

            // Modify-Output-Before-Display-Ereignis, bietet die M�glichkeit,
            // dass ein Plugin die Ausgabe vor der Ausgabe nochmal ver�ndert,
            // unabh�ngig davon, ob die Seite aus dem Cache geladen wurde oder nicht
            Plugins::call(
                Plugins::MODIFY_OUTPUT_BEFORE_DISPLAY,
                array(
                    'preview' => $router->isPreview(),
                    'pageId' => $pageId,
                    'languageId' => $languageId,
                    'isCached' => $output_cached,
                ),
                $output
            );

            // Seite ausgeben
            print($output);

            // After-Display-Ereignis
            Plugins::call(
                Plugins::AFTER_DISPLAY,
                array(
                    'preview' => $router->isPreview(),
                    'pageId' => $pageId,
                    'languageId' => $languageId
                )
            );

        } else {

            Helpers::fatalError('Error 404: page not found ', true);

        }
    }

}
