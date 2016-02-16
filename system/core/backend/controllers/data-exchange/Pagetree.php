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

class PagetreeController extends DataExchangeController
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

    public function getIcon($element, $languageId)
    {
        if ($element['visibility'] == Pages::VISIBILITY_SELECT) {
            if ($this->pages->getVisibilityInLanguage($element['id'], $languageId)) {
                $visibility = 'visible';
            } else {
                $visibility = 'invisible';
            }
        } else {
            if ($element['visibility'] == Pages::VISIBILITY_ALWAYS) {
                $visibility = 'visible';
            } else {
                $visibility = 'invisible';
            }
        }
        if ($element['active'] == 0) {
            $icon = "page-icon-deactivated";
        } else {
            switch ($element['status']) {
                case Pages::STATUS_EDIT:
                    $icon = "edited";
                    break;
                case Pages::STATUS_NEW:
                    $icon = "new";
                    break;
                case Pages::STATUS_PUBLISHED:
                default:
                    $icon = "published";
                    break;
            }
            $icon = 'page-icon-' . $icon . '-' . $visibility;
        }
        return ($icon);
    }

    protected function getSubElements($elements)
    {
        $data = array();
        $languages = Config::get()->languages->list->getArrayCopy();
        if (is_array($elements)) {
            if (count($elements) > 0) {
                foreach ($elements as $element) {
                    $itemData = array();
                    $itemCaption = $this->pages->getCaption($element["id"]);
                    foreach ($languages as $languageId => $language) {
                        $itemData[] = array(
                            "title" => $this->pages->getAnyCaption($element["id"], $languageId, $languages,
                                $itemCaption),
                            "icon" => $this->getIcon($element, $languageId),
                            "language" => $languageId
                        );
                    }
                    $children = array();
                    if (isset($element['children'])) {
                        if (count($element['children']) > 0) {
                            $children = $this->getSubElements($element['children']);
                        }
                    }
                    $row = array(
                        'data' => $itemData,
                        'attr' => array('id' => 'page_' . $element['id']),
                        'metadata' => array(
                            'id' => $element['id'],
                            'isPageLink' => $this->pages->isPageLink($element['id'], $element)
                        )
                    );
                    if (count($children) > 0) {
                        $row['children'] = $children;
                    }
                    $data[] = $row;
                }
            }
        }
        return ($data);
    }

    public function getAction()
    {
        $elements = $this->pages->getChildren();
        $children = $this->getSubElements($elements);

        $includeAnchors = Helpers::isTrue(Request::postParam('includeAnchors',
            Request::getParam('includeAnchors', false)));

        if ($includeAnchors) {
            Plugins::call(Plugins::PAGETREE_COLLECT_PAGE_ANCHORS, array(), $children);
        }

        $this->success(array(
            'children' => $children,
        ));
    }

    public function createAction()
    {
        // Die wichtigsten Parameter auslesen
        $parent_id = Request::postParam('parent-id');
        $name = Request::postParam('name');
        $caption = Request::postParam('caption');

        // �berpr�fen, ob die Lebenswichtigen Parameter gesetzt sind
        if (($parent_id === null) || ($name === null) || ($caption === null)) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // �berpf�fen, ob der eingloggte Benutzer �berhaupt die n�tigen Rechte besitzt
        if (!$this->helpers->canAccessPage($parent_id, Acl::ACTION_CREATE)) {
            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
            return;
        }

        // Standard R�ckgabe-Werte annehmen
        $res = array(
            'action' => 'create',
            'validName' => false,
            'nameAlreadyExists' => true,
            'pageCreated' => false,
            'pageId' => null
        );

        // Versuchen, die Seite zu erzeugen
        if ($this->pages->isValidName($name)) {

            // Der Name ist schonmal g�ltig...
            $res['validName'] = true;
            if (!$this->pages->nameExists($parent_id, $name)) {

                // Der Name existiert auch nocht nicht
                $res['nameAlreadyExists'] = false;

                // Seite anlegen
                $pageId = $this->pages->create($parent_id, $name, $caption);
                if ($pageId !== false) {

                    // ************************************************************************
                    // Die Seite ist nun angelegt
                    // ************************************************************************

                    // Soll die Seite nur ein Link sein?
                    $linkTranslated = 0;
                    $templateId = Request::postParam('template-id');
                    $linkUrl = '';
                    $linkNewWindow = 0;
                    if ($templateId == 'NULL') {
                        $templateId = null;
                        $linkTranslated = Request::postParam('link-translated', 0);
                        if ($linkTranslated) {
                            $linkUrl = '';
                            $translatedLinkUrls = Request::postParam('translated-link-urls');
                            $this->pages->setTranslatedLinkUrls($pageId, $translatedLinkUrls);
                        } else {
                            $linkUrl = Request::postParam('link-url', '');
                        }
                        $linkNewWindow = Request::postParam('link-new-window', 0);
                    }

                    // Eigenschaften setzen
                    $properties = array(
                        'template-id' => $templateId,
                        'visibility' => Request::postParam('visibility', 0),
                        'active' => Request::postParam('active', 0),
                        'cachable' => Request::postParam('cachable', 0),
                        'status' => Pages::STATUS_NEW,
                        'position' => $this->pages->getLastPosition($parent_id),
                        'creation-date' => time(),
                        'last-change-date' => time(),
                        'creation-user-id' => Auth::getUserId(),
                        'creation-user-name' => Auth::getScreenName(),
                        'last-change-user-id' => Auth::getUserId(),
                        'last-change-user-name' => Auth::getScreenName(),
                        'link-translated' => $linkTranslated,
                        'link-url' => $linkUrl,
                        'link-new-window' => $linkNewWindow,
                        'unique-id' => Request::postParam('unique-id', ''),
                    );
                    $this->pages->setProperties($pageId, $properties);

                    // Sprach-spefizische Sichtbarkeit speichern
                    $languages = Config::get()->languages->list;
                    $visibility = array();
                    $postVisibility = Request::postParam('visible-in', array());
                    foreach ($languages as $key => $language) {
                        $visibility[$key] = 0;
                        if (isset($postVisibility[$key])) {
                            if ($postVisibility[$key] == 1) {
                                $visibility[$key] = 1;
                            }
                        }
                    }
                    $this->pages->setVisibility($pageId, $visibility);

                    // Zugriff-Steuerung
                    if (Auth::isAdmin()) {
                        $inherit_acl_resource = Request::postParam('inherit-acl-resource', 0);
                        if ($inherit_acl_resource == 0) {
                            $user_groups_mode = Request::postParam('user-groups-mode', Acl::RESOURCE_SUPERUSER_ONLY);
                            $description = $this->pages->getAnyCaption($pageId);
                            Acl::registerResource(Acl::RESOURCE_GROUP_PAGES, (string)$pageId, $description,
                                $user_groups_mode);
                            $user_groups = Request::postParam('user-groups', array());
                            if (count($user_groups) > 0) {
                                Acl::assignUserGroups(Acl::RESOURCE_GROUP_PAGES, (string)$pageId, $user_groups);
                            }
                        }
                    }

                    // Seiten-Aliasse
                    if (Config::get()->allowPageAliases === true) {
                        $aliases = Request::postParam('alias');
                        $this->pages->setPageAliases($pageId, $aliases);
                    }

                    // ID der erzeugten Seite an den Client �bergeben
                    $res['pageCreated'] = true;
                    $res['pageId'] = $pageId;

                } else {

                    // Irgendwas ist schiefgegangen
                    $this->error();
                    return;
                }
            }
        }

        // Cache l�schen (da �nderung am Seitenbaum, die Navigation erscheint i.d.R. auf allen Seiten)
        PageCache::invalidateAll();

        // War anscheinend erfolgreich
        $this->success($res);
    }

    protected function setVisibilityOfElements($elements, $visibility, $resursive = false)
    {
        foreach ($elements as $element) {
            if ($resursive) {
                $children = $this->pages->getChildren($element['id'], false);
                if ($children !== false) {
                    if (count($children) > 0) {
                        if ($this->setVisibilityOfElements($children, $visibility, true) === false) {
                            return (false);
                        }
                    }
                }
            }
            $result = $this->pages->setVisibility($element['id'], $visibility);
            if ($result === false) {
                return (false);
            }
        }
        return (true);
    }

    protected function setPropertiesOfElements($elements, $properties, $resursive = false)
    {
        foreach ($elements as $element) {
            if ($resursive) {
                $children = $this->pages->getChildren($element['id'], false);
                if ($children !== false) {
                    if (count($children) > 0) {
                        if ($this->setPropertiesOfElements($children, $properties, true) === false) {
                            return (false);
                        }
                    }
                }
            }
            $result = $this->pages->setProperties($element['id'], $properties);
            if ($result === false) {
                return (false);
            }
        }
        return (true);
    }

    protected function callOnPagetreeEditPropetiesPluginsForElements($elements, $resursive = false)
    {
        foreach ($elements as $element) {
            if ($resursive) {
                $children = $this->pages->getChildren($element['id'], false);
                if ($children !== false) {
                    if (count($children) > 0) {
                        $this->callOnPagetreeEditPropetiesPluginsForElements($children, true);
                    }
                }
            }
            $parameters = array(
                'pageId' => $element['id'],
            );
            $data = null;
            Plugins::call(Plugins::PAGETREE_EDIT_PAGE_PROPERTIES, $parameters, $data);
        }
    }

    protected function applyAclSettingsToElements($elements, $resursive = false)
    {
        if (Auth::isAdmin()) {
            foreach ($elements as $element) {
                if ($resursive) {
                    $children = $this->pages->getChildren($element['id'], false);
                    if ($children !== false) {
                        if (count($children) > 0) {
                            if ($this->applyAclSettingsToElements($children, true) === false) {
                                return (false);
                            }
                        }
                    }
                }
                $pageId = $element['id'];
                $inherit_acl_resource = Request::postParam('inherit-acl-resource', 0);
                if ($inherit_acl_resource == 0) {
                    // Die Seite soll nun einen eigenen Eintrag in der Zugriffsteuerung haben
                    $user_groups_mode = Request::postParam('user-groups-mode', Acl::RESOURCE_SUPERUSER_ONLY);
                    $description = $this->pages->getAnyCaption($pageId);
                    // Schauen, ob schon ein Eintrag existiert
                    $resource = Acl::getResourceData(Acl::RESOURCE_GROUP_PAGES, $pageId);
                    if ($resource !== false) {
                        // Den bestehenden Eintrag �ndern
                        $updateAcl = array(
                            'description' => $description,
                            'user-groups-mode' => $user_groups_mode
                        );
                        Acl::updateResource(Acl::RESOURCE_GROUP_PAGES, $pageId, $updateAcl);
                    } else {
                        // Einene neuen Eintrag erzeugen
                        Acl::registerResource(Acl::RESOURCE_GROUP_PAGES, (string)$pageId, $description,
                            $user_groups_mode);
                    }
                    // Benutzer-Gruppen setzen
                    $user_groups = Request::postParam('user-groups', array());
                    Acl::assignUserGroups(Acl::RESOURCE_GROUP_PAGES, (string)$pageId, $user_groups);
                } else {
                    // Die Seite soll nun die Zugriffsteuerung von einer �bergeordneten Seite erben,
                    // falls ein Eintrag in der ACL besteht, diesen entfernen...
                    Acl::removeResource(Acl::RESOURCE_GROUP_PAGES, $pageId);
                }
            }
            return (true);
        } else {
            return (false);
        }
    }

    private function switchTemplate($page_id, $new_template_id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->pages->getProperties($page_id);
        }
        if (($properties !== null) && ($properties !== false)) {
            $old_template_id = $properties['template-id'];
            if ($new_template_id != $old_template_id) {

                // Ver�ffentlichte Seite samt Dateien l�schen
                $published_folder = $this->pages->getPagePublishedFolder($page_id, $properties);
                $published_data_file = $published_folder . Pages::DATA_FILE_NAME;
                $published_files_folder = $published_folder . Pages::PAGE_FILES_FOLDER_NAME;
                if (file_exists($published_data_file)) {
                    FileUtils::deleteFile($published_data_file);
                }
                if (file_exists($published_files_folder)) {
                    FileUtils::deleteFolder($published_files_folder);
                }

                // Bearbeitete Seite samt Dateien l�schen
                $edit_folder = $this->pages->getPageeditFolder($page_id, $properties);
                $edit_data_file = $edit_folder . Pages::DATA_FILE_NAME;
                $edit_files_folder = $edit_folder . Pages::PAGE_FILES_FOLDER_NAME;
                if (file_exists($edit_data_file)) {
                    FileUtils::deleteFile($edit_data_file);
                }
                if (file_exists($edit_files_folder)) {
                    FileUtils::deleteFolder($edit_files_folder);
                }

                // ge�nderte Eigenschaften setzen
                $edit_properties = array(
                    'template-id' => $new_template_id,
                    'last-change-date' => time(),
                    'last-change-user-id' => Auth::getUserId(),
                    'last-change-user-name' => Auth::getScreenName(),
                    'status' => Pages::STATUS_NEW,
                );
                $this->pages->setProperties($page_id, $edit_properties);
                return (true);
            }
        }
        return (false);
    }

    public function editAction()
    {
        // Die wichtigsten Parameter auslesen
        $pageId = Request::postParam('pageId');
        $name = Request::postParam('name');
        $caption = Request::postParam('caption');
        $recursive = $this->sanitizeBoolean(Request::postParam('recursive', '0'));

        // �berpr�fen, ob pageId gesetzt ist
        if ($pageId === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // Feststellen, ob sich das ganze auf eine oder mehrere Seiten bezieht
        if (is_array($pageId)) {
            $batchEdit = true;
            $pageIdList = $pageId;
        } else {
            $batchEdit = false;
            $pageIdList = array($pageId);
        }

        // ggf. �berpr�fen, ob Name und Titel gesetzt sind
        if (!$batchEdit) {
            if (($name === null) || ($caption === null)) {
                $this->error(self::RESULT_ERROR_BAD_REQUEST);
                return;
            }
        }

        // �berpr�fen, ob die Seite �berhaupt (noch) existiert
        $elements = array();
        foreach ($pageIdList as $id) {
            $element = $this->pages->getProperties($id);
            if ($element === false) {
                $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
                return;
            } else {
                $elements[] = $element;
            }
        }

        // �berpf�fen, ob der eingloggte Benutzer �berhaupt die n�tigen Rechte besitzt
        if (!$this->helpers->canAccessAllElements($elements, Acl::ACTION_EDIT, $recursive)) {
            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
            return;
        }

        // OnPageTreeBeginBatchEditPageProperties
        $parameters = array();
        $data = null;
        Plugins::call(Plugins::PAGETREE_BEGIN_BATCH_EDIT_PAGE_PROPERTIES, $parameters, $data);

        // Standard R�ckgabe-Werte annehmen
        $res = array(
            'action' => 'edit',
            'validName' => false,
            'nameAlreadyExists' => true,
            'propertiesSaved' => false,
            'validAliases' => false,
            'aliasAlreadyExists' => true,
            'offendingAliasLanguageId' => '',
        );

        // Wenn nur eine Seite ge�ndert werden soll, Namen und Titel, etc. �bernehmen
        if (!$batchEdit) {

            // ggf. Aliasse �berpr�fen
            $aliases_are_valid = true;
            $an_alias_already_exists = false;
            $offending_alias_language_id = '';
            if (Config::get()->allowPageAliases === true) {
                $languages = Config::get()->languages->list;
                $postAliases = Request::postParam('alias', array());
                foreach ($languages as $language_id => $language) {
                    if (isset($postAliases[$language_id])) {
                        if (trim($postAliases[$language_id]) != '') {
                            if (!$this->pages->isValidName($postAliases[$language_id])) {
                                $aliases_are_valid = false;
                                $offending_alias_language_id = $language_id;
                                break;
                            }
                        }
                    }
                }
                if ($aliases_are_valid) {
                    foreach ($languages as $language_id => $language) {
                        if (isset($postAliases[$language_id])) {
                            if (trim($postAliases[$language_id]) != '') {
                                if ($this->pages->pageAliasExistsForLanguage($elements[0]['parent-id'],
                                    $postAliases[$language_id], $language_id, $pageId)
                                ) {
                                    $an_alias_already_exists = true;
                                    $offending_alias_language_id = $language_id;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $res['validAliases'] = $aliases_are_valid;
            $res['aliasAlreadyExists'] = $an_alias_already_exists;
            $res['offendingAliasLanguageId'] = $offending_alias_language_id;

            // Wenn die Aliasse OK sind, dann weitermachen...
            if (($res['validAliases'] === true) && ($res['aliasAlreadyExists'] === false)) {

                $name = $this->pages->normalizeName($name);
                if ($this->pages->isValidName($name)) {

                    // Der Name ist schonmal g�ltig...
                    $res['validName'] = true;

                    if (!$this->pages->nameExists($elements[0]['parent-id'], $name, $pageId)) {

                        // Der Name existiert auch nocht nicht
                        $res['nameAlreadyExists'] = false;

                        // ggf. umbenennen
                        if ($elements[0]['name'] != $name) {
                            if (!$this->pages->rename($pageId, $name)) {
                                $this->error();
                                return;
                            }
                        }

                        // Titel speichern
                        $this->pages->setCaption($pageId, $caption);

                        // Soll der Template-Typ ge�ndert werden?
                        $new_template_id = Request::postParam('template-id');
                        if ($new_template_id === 'NULL') {
                            $new_template_id = null;
                        }
                        $old_template_id = $elements[0]['template-id'];
                        if ($new_template_id != $old_template_id) {
                            $this->switchTemplate($pageId, $new_template_id, $elements[0]);
                        }

                        // Wenn die Seite ein Link ist, dann die Link-Eigenschaften �bernehmen
                        if ($new_template_id == null) {
                            $linkTranslated = Request::postParam('link-translated', 0);
                            if ($linkTranslated) {
                                $linkUrl = '';
                                $translatedLinkUrls = Request::postParam('translated-link-urls');
                                $this->pages->setTranslatedLinkUrls($pageId, $translatedLinkUrls);
                            } else {
                                $linkUrl = Request::postParam('link-url', '');
                                $this->pages->deleteTranslatedLinkUrls($pageId);
                            }
                            $link_properties = array(
                                'link-translated' => $linkTranslated,
                                'link-url' => $linkUrl,
                                'link-new-window' => Request::postParam('link-new-window', 0),
                            );
                            $this->pages->setProperties($pageId, $link_properties);
                        } else {
                            $link_properties = array(
                                'link-translated' => 0,
                                'link-url' => '',
                                'link-new-window' => 0,
                            );
                            $this->pages->setProperties($pageId, $link_properties);
                            $this->pages->deleteTranslatedLinkUrls($pageId);
                        }

                        // Seiten-Aliasse
                        if (Config::get()->allowPageAliases === true) {
                            $aliases = Request::postParam('alias');
                            $this->pages->setPageAliases($pageId, $aliases);
                        }

                        // Und noch weitere Eigenschaften speichern, die nur f�r eine einzelne Seite ge�ndert werden k�nnen
                        $single_page_properties = array(
                            'unique-id' => Request::postParam('unique-id', ''),
                        );
                        $this->pages->setProperties($pageId, $single_page_properties);

                        // OnPageTreeEditPageProperties ausl�sen
                        $parameters = array(
                            'pageId' => $pageId,
                        );
                        $data = null;
                        Plugins::call(Plugins::PAGETREE_EDIT_PAGE_PROPERTIES, $parameters, $data);
                    }
                }
            }
        } else {
            $res['validName'] = true;
            $res['nameAlreadyExists'] = false;
            $res['validAliases'] = true;
            $res['aliasAlreadyExists'] = false;
        }

        if (($res['validName'] == true) && ($res['nameAlreadyExists'] == false)) {

            // Nun alle Eigenschaften �bernehmen, die ggf. auch f�r mehrere Seiten gespeichert werden k�nnen
            $properties = array(
                'last-change-date' => time(),
                'last-change-user-id' => Auth::getUserId(),
                'last-change-user-name' => Auth::getScreenName()
            );
            if (Request::postParam('applyVisibility', 0) > 0) {
                $properties['visibility'] = Request::postParam('visibility', 0);
            }
            if (Request::postParam('applyMiscellaneous', 0) > 0) {
                $properties['active'] = Request::postParam('active', 0);
                $properties['cachable'] = Request::postParam('cachable', 0);
            }
            $this->setPropertiesOfElements($elements, $properties, $recursive);

            // Sprach-spefizische Sichtbarkeit speichern
            if (Request::postParam('applyVisibility', 0) > 0) {
                $languages = Config::get()->languages->list;
                $visibility = array();
                $postVisibility = Request::postParam('visible-in', array());
                foreach ($languages as $key => $language) {
                    $visibility[$key] = 0;
                    if (isset($postVisibility[$key])) {
                        if ($postVisibility[$key] == 1) {
                            $visibility[$key] = 1;
                        }
                    }
                }
                $this->setVisibilityOfElements($elements, $visibility, $recursive);
            }

            // Zugriff-Steuerung
            if (Request::postParam('applyAcl', 0) > 0) {
                $this->applyAclSettingsToElements($elements, $recursive);
            }

            // Plugins aufrufen
            $this->callOnPagetreeEditPropetiesPluginsForElements($elements, true);

            // alles war gut
            $res['propertiesSaved'] = true;
        }

        // Cache l�schen (da �nderung am Seitenbaum, die Navigation erscheint i.d.R. auf allen Seiten)
        PageCache::invalidateAll();

        // OnPageTreeEndBatchEditPageProperties
        $parameters = array();
        $data = null;
        Plugins::call(Plugins::PAGETREE_END_BATCH_EDIT_PAGE_PROPERTIES, $parameters, $data);

        // War anscheinend erfolgreich
        $this->success($res);
    }

    protected function copyElements($elements, $dest_id, $position = null)
    {
        $counter = 0;
        foreach ($elements as $element) {
            if ($position !== null) {
                $dest_position = $position + $counter;
            } else {
                $dest_position = null;
            }
            $result = $this->pages->copy($element['id'], $dest_id, $dest_position);
            if ($result === false) {
                return (false);
            }
            $newId = $result;
            $existing_acl_resource = Acl::getResourceData(Acl::RESOURCE_GROUP_PAGES, $element['id']);
            if ($existing_acl_resource !== false) {
                $new_acl_resource_id = Acl::registerResource(Acl::RESOURCE_GROUP_PAGES, $newId,
                    $this->pages->getAnyCaption($result), $existing_acl_resource['user-groups-mode']);
                $user_groups = Acl::getUserGroups(Acl::RESOURCE_GROUP_PAGES, $element['id']);
                if ($user_groups !== false) {
                    Acl::assignUserGroupsById($new_acl_resource_id, $user_groups);
                }
            }
            $children = $this->pages->getChildren($element['id'], false);
            if ($children !== false) {
                if (count($children) > 0) {
                    if ($this->copyElements($children, $newId) === false) {
                        return (false);
                    }
                }
            }
            $counter++;
        }
        return (true);
    }

    public function dragdropAction()
    {
        // Parameter auslesen
        $dest_id = Request::postParam('destId');
        $dest_position = Request::postParam('destPosition');
        $elements = Request::postParam('elements');
        $action = Request::postParam('action');

        // Parameter �berpr�fen
        if (!is_array($elements) || !is_numeric($dest_id) || !is_numeric($dest_position) || !(($action == 'move') || ($action == 'copy'))) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // Wenn $elements leer ist, nichts tun
        if (count($elements) == 0) {
            $this->success();
            return;
        }

        // �berpr�fen, ob das Ziel existiert
        $parent_exists = true;
        if ($dest_id != Pages::ROOT_ID) {
            $parent_exists = ($this->pages->getProperties($dest_id) !== false);
        }

        // �berpr�fen, ob die zu kopierenden / verschiebenden Elemente existieren
        $elements_exist = true;
        foreach ($elements as $key => $value) {
            if (is_numeric($value)) {
                $elements[$key] = $this->pages->getProperties($value);
                if ($elements[$key] === false) {
                    $elements_exist = false;
                    break;
                }
            } else {
                $elements_exist = false;
                break;
            }
        }

        // Wenn Ziel oder eine der Quellen nicht existieren, abbrechen
        if (!($parent_exists && $elements_exist)) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        // Falls $action == 'move', herausfinden, ob das Verschieben innerhalb einer Ebene stattfindet (blo�es Umsortieren)
        // oder ob die Quell-Elemente von einem anderen Eltern-Element stammen
        if ($action == 'move') {
            $rearrange = true;
            foreach ($elements as $element) {
                if ($element['parent-id'] != $dest_id) {
                    $rearrange = false;
                    break;
                }
            }
            if ($rearrange) {
                $action = 'rearrange';
            }
        }

        // �berpr�fen, ob der Benutzer die n�tigen Rechte f�r das Ziel-Objekt besitzt
        $acl_action = Acl::ACTION_CREATE;
        switch ($action) {
            case 'rearrange':
                $acl_action = Acl::ACTION_EDIT;
                break;
            case 'move':
                $acl_action = Acl::ACTION_CREATE;
                break;
            case 'copy':
                $acl_action = Acl::ACTION_CREATE;
                break;
        }
        if (!$this->helpers->canAccessPage($dest_id, $acl_action)) {
            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
            return;
        }

        // Sofern die Elemente verschoben werden sollen, m�ssen auch die Rechte
        // f�r das Bearbeiten der einzelnen Quell-Objekte (samt Unterseiten) vorhanden sein
        if ($action == 'move') {
            if (!$this->helpers->canAccessAllElements($elements, Acl::ACTION_EDIT, true)) {
                $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
                return;
            }
        }

        // Die Aktion durchf�hren
        $this->pages->sanitizePositions($dest_id);
        $this->pages->splicePositions($dest_id, $dest_position, count($elements));
        if ($action == 'rearrange') {
            $counter = 0;
            foreach ($elements as $element) {
                $this->pages->setProperties($element['id'], array('position' => $dest_position + $counter));
                $elements_id_list[] = $element['id'];
                $counter++;
            }
        } else {

            $parameters = array();
            $data = null;
            if ($action == 'copy') {
                // OnPageTreeBeginBatchCopyPage ausl�sen
                Plugins::call(Plugins::PAGETREE_BEGIN_BATCH_COPY_PAGE, $parameters, $data);
            } elseif ($action == 'move') {
                // OnPageTreeBeginBatchMovePage ausl�sen
                Plugins::call(Plugins::PAGETREE_BEGIN_BATCH_MOVE_PAGE, $parameters, $data);
            }

            $counter = 0;
            foreach ($elements as $element) {
                if ($action == 'copy') {
                    // Kopieren
                    $result = $this->copyElements(array($element), $dest_id, $dest_position + $counter);
                } else {
                    // Verschieben
                    $result = $this->pages->move($element['id'], $dest_id, $dest_position + $counter);
                }
                if ($result === false) {
                    $this->error();
                    return;
                }
                $counter++;
            }

            $parameters = array();
            $data = null;
            if ($action == 'copy') {
                // OnPageTreeEndBatchCopyPage ausl�sen
                Plugins::call(Plugins::PAGETREE_END_BATCH_COPY_PAGE, $parameters, $data);
            } elseif ($action == 'move') {
                // OnPageTreeEndBatchMovePage ausl�sen
                Plugins::call(Plugins::PAGETREE_END_BATCH_MOVE_PAGE, $parameters, $data);
            }

        }
        $this->pages->sanitizePositions($dest_id);

        // Cache l�schen (da �nderung am Seitenbaum, die Navigation erscheint i.d.R. auf allen Seiten)
        PageCache::invalidateAll();

        // War wohl alles gut...
        $this->success(array('action' => $action));
    }

    protected function deleteElements($elements)
    {
        foreach ($elements as $element) {
            $children = $this->pages->getChildren($element['id'], false);
            if ($children !== false) {
                if (count($children) > 0) {
                    if ($this->deleteElements($children) === false) {
                        return (false);
                    }
                }
            }
            $result = $this->pages->delete($element['id']);
            if ($result === false) {
                return (false);
            }
            Acl::removeResource(Acl::RESOURCE_GROUP_PAGES, $element['id']);
        }
        return (true);
    }

    public function deleteAction()
    {
        // Parameter auslesen
        $elements = Request::postParam('elements');

        // Parameter �berpr�fen
        if (!is_array($elements)) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // Wenn $elements leer ist, nichts tun
        if (count($elements) == 0) {
            $this->success();
            return;
        }

        // �berpr�fen, ob die zu l�schenden Elemente existieren
        $elements_exist = true;
        foreach ($elements as $key => $value) {
            if (is_numeric($value)) {
                $elements[$key] = $this->pages->getProperties($value);
                if ($elements[$key] === false) {
                    $elements_exist = false;
                    break;
                }
            } else {
                $elements_exist = false;
                break;
            }
        }

        // Wenn eines der zu l�schenden Elemente jetzt schon nicht mehr existiert, abbrechen
        if (!$elements_exist) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        // Nutzerrechte �berpr�fen
        if (!$this->helpers->canAccessAllElements($elements, Acl::ACTION_DELETE, true)) {
            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
            return;
        }

        // OnPageTreeBeginBatchDeletePage ausl�sen
        $parameters = array();
        $data = null;
        Plugins::call(Plugins::PAGETREE_BEGIN_BATCH_DELETE_PAGE, $parameters, $data);

        // L�schen
        foreach ($elements as $element) {
            if ($this->deleteElements(array($element)) === false) {
                $this->error();
                return;
            }
        }

        // OnPageTreeEndBatchDeletePage ausl�sen
        $parameters = array();
        $data = null;
        Plugins::call(Plugins::PAGETREE_END_BATCH_DELETE_PAGE, $parameters, $data);

        // Cache l�schen (da �nderung am Seitenbaum, die Navigation erscheint i.d.R. auf allen Seiten)
        PageCache::invalidateAll();

        $this->success();
    }

    public function getStatusString($status)
    {
        $str = '';
        switch ($status) {
            case Pages::STATUS_NEW:
                $str = Translate::get('New /  not published');
                break;
            case Pages::STATUS_EDIT:
                $str = Translate::get('Unpublished changes');
                break;
            case Pages::STATUS_PUBLISHED:
                $str = Translate::get('Published');
                break;
        }
        return ($str);
    }

    public function getDateString($timestamp)
    {
        if ($timestamp !== null) {
            return (date(Translate::get('m-d-Y H:i'), $timestamp));
        } else {
            return (Translate::get('n/a'));
        }
    }

    public function getUserName($id, $fallback)
    {
        $username = '';
        if ($id !== null) {
            $users = new Users;
            $user = $users->getById($id);
            if ($user !== false) {
                $username = $user['screenname'];
            } else {
                $username = $fallback;
            }
        }
        $username = trim($username);
        if ($username != '') {
            return ($username);
        } else {
            return (Translate::get('Unknown'));
        }
    }

    public function infoAction()
    {
        $id = Request::postParam('pageId');
        $languageId = Request::postParam('languageId');

        // �berpr�fen, ob die Lebenswichtigen Parameter gesetzt sind
        if (($id === null) || ($languageId === null)) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // �berpr�fen, ob die Seite �berhaupt (noch) existiert
        $properties = $this->pages->getProperties($id);
        if ($properties === false) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        // Name des Templates auslesen
        $pages = DataStructure::pagesArray();
        if ($properties['template-id'] === null) {
            $template_name = Translate::get('Link / Redirection');
        } else {
            if (isset($pages[$properties['template-id']])) {
                $template_name = $pages[$properties['template-id']]['name'];
            } else {
                $template_name = Translate::get('Unknown');
            }
        }

        // Infos sammeln
        $result = array(
            array('description' => Translate::get('Title'), 'value' => $this->pages->getAnyCaption($id, $languageId)),
            array('description' => Translate::get('Status'), 'value' => $this->getStatusString($properties['status'])),
            array(
                'description' => Translate::get('Active'),
                'value' => ($properties['active'] > 0) ? Translate::get('Yes') : Translate::get('No')
            ),
            array(
                'description' => Translate::get('Visible'),
                'value' => ($this->pages->isPageVisible($id,
                    $languageId)) ? Translate::get('Yes') : Translate::get('No')
            ),
            array(
                'description' => Translate::get('Last change'),
                'value' => $this->getDateString($properties['last-change-date']) . '&nbsp;&nbsp;(' . Translate::get('by') . ' ' . $this->getUserName($properties['last-change-user-id'],
                        $properties['last-change-user-name']) . ')'
            ),
            array(
                'description' => Translate::get('Created'),
                'value' => $this->getDateString($properties['creation-date']) . '&nbsp;&nbsp;(' . Translate::get('by') . ' ' . $this->getUserName($properties['creation-user-id'],
                        $properties['creation-user-name']) . ')'
            ),
            array('description' => Translate::get('Subpages'), 'value' => $this->pages->getSubpageCount($id)),
            array('description' => Translate::get('Template'), 'value' => $template_name)
        );

        // Infos zur�ckgeben
        $this->success(array('infos' => $result));

    }

    public function settitleAction()
    {
        $id = Request::postParam('pageId');
        $languageId = Request::postParam('languageId');
        $title = Request::postParam('title');

        // �berpr�fen, ob die Lebenswichtigen Parameter gesetzt sind
        if (($id === null) || ($languageId === null) || ($title === null)) {
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

        // Titel setzen
        if ($this->pages->setCaptionInLanguage($id, $languageId, $title) === false) {
            $this->error();
            return;
        }

        // OnPageTreeSetPageTitle ausl�sen
        $parameters = array(
            'pageId' => $id,
            'languageId' => $languageId,
            'newTitle' => $title,
        );
        $data = null;
        Plugins::call(Plugins::PAGETREE_SET_PAGE_TITLE, $parameters, $data);

        // Cache l�schen (da �nderung am Seitenbaum, die Navigation erscheint i.d.R. auf allen Seiten)
        PageCache::invalidateAll();

        $this->success();
    }

    protected function publishElements($elements, $recursive = false)
    {
        foreach ($elements as $element) {
            if ($recursive === true) {
                $children = $this->pages->getChildren($element['id'], false);
                if ($children !== false) {
                    if (count($children) > 0) {
                        if ($this->publishElements($children, true) === false) {
                            return (false);
                        }
                    }
                }
            }
            $result = $this->pages->publish($element['id']);
            if ($result === false) {
                return (false);
            }
        }
        return (true);
    }

    public function publishAction()
    {
        // Parameter auslesen
        $elements = Request::postParam('elements');
        $recursive = $this->sanitizeBoolean(Request::postParam('recursive', '0'));

        // Parameter �berpr�fen
        if ((!is_array($elements)) || ($recursive === null)) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        // Wenn $elements leer ist, nichts tun
        if (count($elements) == 0) {
            $this->success();
            return;
        }

        // �berpr�fen, ob die zu ver�ffentlichenden Elemente existieren
        $elements_exist = true;
        foreach ($elements as $key => $value) {
            if (is_numeric($value)) {
                $elements[$key] = $this->pages->getProperties($value);
                if ($elements[$key] === false) {
                    $elements_exist = false;
                    break;
                }
            } else {
                $elements_exist = false;
                break;
            }
        }

        // Wenn eines der zu ver�ffentlichenden Elemente jetzt schon nicht mehr existiert, abbrechen
        if (!$elements_exist) {
            $this->error(self::RESULT_ERROR_DOES_NOT_EXIST);
            return;
        }

        // Nutzerrechte �berpr�fen
        if (!$this->helpers->canAccessAllElements($elements, Acl::ACTION_PUBLISH, $recursive)) {
            $this->error(self::RESULT_ERROR_NOT_AUHTORIZED);
            return;
        }

        // OnPageTreeBeginBatchPublishPage ausl�sen
        $parameters = array();
        $data = null;
        Plugins::call(Plugins::PAGETREE_BEGIN_BATCH_PUBLISH_PAGE, $parameters, $data);

        // Ver�ffentlichen
        foreach ($elements as $element) {
            if ($this->publishElements(array($element), $recursive) === false) {
                $this->error();
                return;
            }
        }

        // OnPageTreeEndBatchPublishPage ausl�sen
        $parameters = array();
        $data = null;
        Plugins::call(Plugins::PAGETREE_END_BATCH_PUBLISH_PAGE, $parameters, $data);

        // Cache l�schen (da �nderung am Seitenbaum, die Navigation erscheint i.d.R. auf allen Seiten)
        PageCache::invalidateAll();

        $this->success();
    }

    public function clearcacheAction()
    {
        PageCache::invalidateAll();
        $this->success();
    }

}
