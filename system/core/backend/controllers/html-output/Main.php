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

class MainController extends HtmlOutputController
{
    public function defaultAction()
    {
        if (Translate::getId() != '') {
            $config = Config::getArray();
            $languages = $config['backendLanguages']['list'];
            $language_id = Translate::getId();
            $strings = array();
            if (is_array($languages[$language_id]['translationClientside'])) {
                if (count($languages[$language_id]['translationClientside']) > 0) {
                    foreach ($languages[$language_id]['translationClientside'] as $translation_file) {
                        $temp = include($translation_file);
                        $strings = array_merge($strings, $temp);
                    }
                }
            } else {
                if ($languages[$language_id]['translationClientside'] != '') {
                    $strings = include($languages[$language_id]['translationClientside']);
                }
            }
            $this->view->assign('backendTranslation', $strings);
        } else {
            $this->view->assign('backendTranslation', array());
        }
        $useGlobalElementsPage = false;
        $pages = new Pages();
        if ($pages->isGlobalElementsPageTemplateAvailable()) {
            if ($pages->isGlobalElementsPageAvailable()) {
                $useGlobalElementsPage = true;
            }
        }
        $this->view->assign('useGlobalElementsPage', $useGlobalElementsPage);
        $this->view->assign('globalElementsPageId', $pages->getGlobalElementsPageId());
    }
}
