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

class Pages
{
    const ROOT_ID = 0;

    const VISIBILITY_ALWAYS = 0;
    const VISIBILITY_NEVER = 1;
    const VISIBILITY_SELECT = 2;

    const STATUS_NEW = 0;
    const STATUS_EDIT = 1;
    const STATUS_PUBLISHED = 2;

    const PUBLISHED_FOLDER_NAME = '__published__';
    const EDIT_FOLDER_NAME = '__edit__';
    const DATA_FILE_NAME = 'page-content.json';
    const PAGE_FILES_FOLDER_NAME = 'files';

    const GLOBAL_ELEMENTS = '__globalElements__';
    const GLOBAL_ELEMENTS_PAGE_NAME = '__global-elements__';

    protected $db = null;
    protected $config = array();
    protected static $properties_cache = array();

    public function __construct()
    {
        $this->db = Db::getPDO();
        $this->config = Config::getArray();
    }

    protected function removeGlobalElementsPageFromChildren(&$children)
    {
        if (is_array($children)) {
            for ($i = 0; $i < count($children); $i++) {
                if ($children[$i]['name'] == self::GLOBAL_ELEMENTS_PAGE_NAME) {
                    array_splice($children, $i, 1);
                    return (true);
                }
            }
        }
        return (false);
    }

    public function getChildren($parent_id = self::ROOT_ID, $recursive = true)
    {
        $pages = Db::get(
            "SELECT * FROM [prefix]pages WHERE `parent-id` = :parentId ORDER BY position ASC",
            array(":parentId" => $parent_id)
        );
        if ($parent_id == self::ROOT_ID) {
            $this->removeGlobalElementsPageFromChildren($pages);
        }
        if (($pages !== false) && $recursive) {
            foreach ($pages as $key => $page) {
                $pages[$key]["children"] = $this->getChildren($page["id"], true);
            }
        }
        return ($pages);
    }

    public function getPageIdByPath($parent_id, $path, $alias_language_id = null)
    {
        $use_aliases = (($alias_language_id !== null) && (Config::get()->allowPageAliases === true));
        if (is_array($path)) {
            if (count($path) > 0) {
                $page = false;
                // Erstmal schauen, ob es eine Seite mit einem Alias in der gewünschten Sprache gibt...
                if ($use_aliases) {
                    $page = Db::getFirst(
                        "SELECT
								[prefix]pages.*
							FROM
								[prefix]pages
							JOIN
								[prefix]page_aliases
							ON
								[prefix]pages.`id` = [prefix]page_aliases.`page-id`
							WHERE
								[prefix]pages.`parent-id` = :parentId
							AND 
								[prefix]page_aliases.`language-id` = :languageId
							AND 
								[prefix]page_aliases.`alias` = :alias
							;",
                        array(
                            ':parentId' => $parent_id,
                            ':languageId' => $alias_language_id,
                            ':alias' => $path[0]
                        )
                    );
                }
                // Wenn keine Seite mit diesem Alias gefunden wurde, eine Seite mit diesem Namen suchen
                if ($page === false) {
                    $page = Db::getFirst("SELECT * FROM [prefix]pages WHERE `parent-id` = :parentId AND `name` = :name",
                        array(':parentId' => $parent_id, ':name' => $path[0]));
                    // Wenn eine Seite mittels ihres Namens gefunden wurde, die ein Alias für die gewünschte Sprache besitzt
                    // und dieses Alias anders lautet, als die gesuchte Teil-URL, dann darf die Seite nicht gefunden werden.
                    // Sonst würde es dazu führen, dass Seiten unter 2 verschiedenen URLs zu erreichen sind, was nicht so gut ist.
                    if (($page !== false) && $use_aliases) {
                        $page_alias = $this->getPageAliasForLanguage($page['id'], $alias_language_id);
                        if (($page_alias != '') && ($page_alias != $path[0])) {
                            $page = false;
                        }
                    }
                }
                if ($page !== false) {
                    if (count($path) > 1) {
                        array_splice($path, 0, 1);
                        return ($this->getPageIdByPath($page['id'], $path, $alias_language_id));
                    } else {
                        return ($page['id']);
                    }
                } else {
                    return (false);
                }
            }
        }
        return (false);
    }

    public function getPageIdByUniqueId($unique_id)
    {
        $page = Db::getFirst("SELECT * FROM [prefix]pages WHERE `unique-id` = :uniqueId",
            array(':uniqueId' => $unique_id));
        if ($page !== false) {
            return ($page['id']);
        }
        return (false);
    }

    public function getPath($id)
    {
        $properties = $this->getProperties($id);
        if ($properties === false) {
            return (false);
        }
        $path = array();
        $safety_counter = 0;
        do {
            $parent = $properties['parent-id'];
            if ($parent != self::ROOT_ID) {
                array_unshift($path, $parent);
                $properties = $this->getProperties($parent);
                if ($properties === false) {
                    return (false);
                }
            }
            $safety_counter++;
        } while (($parent != self::ROOT_ID) && ($safety_counter < 50));
        return ($path);
    }

    public function getStartPageId($languageId)
    {
        $start_pages = Settings::get('startPages', array());
        $home_page_id = 0;
        if (isset($start_pages[$languageId])) {
            $home_page_id = $start_pages[$languageId];
            if (is_numeric($home_page_id)) {
                if (($home_page_id != self::ROOT_ID) && ($home_page_id > 0)) {
                    $page_properties = $this->getProperties($home_page_id);
                    if ($page_properties !== false) {
                        return ($home_page_id);
                    }
                }
            }
        }
        return ($this->getFirstActivePageId());
    }

    protected function getFirstActivePageId()
    {
        $first_level_pages = $this->getChildren(self::ROOT_ID, false);
        if ($first_level_pages !== false) {
            if (count($first_level_pages) > 0) {
                foreach ($first_level_pages as $page) {
                    if ($this->isPageActive($page['id'])) {
                        return ($page['id']);
                    }
                }
            }
        }
        return (false);
    }

    public function getPageUrl($id, $language_id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        $path = $this->getPath($id);
        if (($properties === false) || ($path === false)) {
            return (false);
        }
        $url = $this->config['baseUrl'];
        $start_page_id = $this->getStartPageId($language_id);

        // Wenn es die Startseite der gesamten Website ist (d.h. die Startseite der Standard-Sprache)
        // dann nur Config::get()->baseUrl zurückgeben
        if (($start_page_id == $id) && ($language_id == $this->config['languages']['standard'])) {
            return ($url);
        }

        // Ggf. die URL der gewünschten Sprache anhängen
        if ((!$this->config['omitStandardLanguageInPageUrl']) || ($language_id != $this->config['languages']['standard'])) {
            $url .= $this->config['languages']['list'][$language_id]['url'] . '/';
        }

        // Wenn es die Startseite der Sprache ist, dann nur die Basis-URL mit der Sprach-URL zurückgeben
        if ($start_page_id == $id) {
            return ($url);
        }

        // Ansonsten die gesamte URL der Seite zurückgeben, dabei ggf. die Alias-URL für diese Seite in dieser Sprache berücksichtigen
        if (count($path) > 0) {
            foreach ($path as $parent_id) {
                $parent = $this->getProperties($parent_id);
                $alias = '';
                if (Config::get()->allowPageAliases === true) {
                    $alias = $this->getPageAliasForLanguage($parent['id'], $language_id);
                }
                if ($alias == '') {
                    $url .= $parent['name'] . '/';
                } else {
                    $url .= $alias . '/';
                }
            }
        }
        $alias = '';
        if (Config::get()->allowPageAliases === true) {
            $alias = $this->getPageAliasForLanguage($id, $language_id);
        }
        if ($alias == '') {
            $url .= $properties['name'] . '/';
        } else {
            $url .= $alias . '/';
        }

        return ($url);
    }

    public function getPageFilesBaseUrl($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        $path = $this->getPath($id);
        if (($properties === false) || ($path === false)) {
            return (false);
        }
        $url = Config::get()->baseUrl . 'user-data/pages/';
        if (count($path) > 0) {
            foreach ($path as $parent_id) {
                $parent = $this->getProperties($parent_id);
                $url .= $parent['name'] . '/';
            }
        }
        $url .= $properties['name'] . '/';
        return ($url);
    }

    public function getPageBaseFolder($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        $path = $this->getPath($id);
        if (($properties === false) || ($path === false)) {
            return (false);
        }
        $folder = APPLICATION_ROOT . 'user-data/pages/';
        if (count($path) > 0) {
            foreach ($path as $parent_id) {
                $parent = $this->getProperties($parent_id);
                $folder .= $parent['name'] . '/';
            }
        }
        $folder .= $properties['name'] . '/';
        return ($folder);
    }

    public function createPagePublishedFolder($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPagePublishedFolder($id, $properties);
        return (FileUtils::createFolderRecursive($folder));
    }

    public function createPageEditFolder($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPageEditFolder($id, $properties);
        return (FileUtils::createFolderRecursive($folder));
    }

    public function getPagePublishedFolder($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPageBaseFolder($id, $properties) . self::PUBLISHED_FOLDER_NAME . '/';
        return ($folder);
    }

    public function getPageFilesPublishedFolder($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPageBaseFolder($id,
                $properties) . self::PUBLISHED_FOLDER_NAME . '/' . self::PAGE_FILES_FOLDER_NAME . '/';
        return ($folder);
    }

    public function getPageEditFolder($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPageBaseFolder($id, $properties) . self::EDIT_FOLDER_NAME . '/';
        return ($folder);
    }

    public function getPageFilesEditFolder($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPageBaseFolder($id,
                $properties) . self::EDIT_FOLDER_NAME . '/' . self::PAGE_FILES_FOLDER_NAME . '/';
        return ($folder);
    }

    public function getPageFilesPublishedUrl($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPageFilesBaseUrl($id,
                $properties) . self::PUBLISHED_FOLDER_NAME . '/' . self::PAGE_FILES_FOLDER_NAME . '/';
        return ($folder);
    }

    public function getPageFilesEditUrl($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties === false) {
            return (false);
        }
        $folder = $this->getPageFilesBaseUrl($id,
                $properties) . self::EDIT_FOLDER_NAME . '/' . self::PAGE_FILES_FOLDER_NAME . '/';
        return ($folder);
    }

    public function nameExists($parent_id, $new_name, $self_id = null)
    {
        $new_name = $this->normalizeName($new_name);
        $where = "`name`=:newName AND `parent-id`=:parentId";
        $parameters = array(
            ':newName' => $new_name,
            ':parentId' => $parent_id
        );
        if ($self_id != null) {
            $where .= " AND `id` <> :selfId";
            $parameters["selfId"] = $self_id;
        }
        $res = Db::get("SELECT `name`, `id` FROM [prefix]pages WHERE " . $where, $parameters);
        if ($res !== false) {
            return (true);
        } else {
            return (false);
        }
    }

    public function normalizeName($name)
    {
        return (trim(UTF8String::strtolower($name)));
    }

    public function isValidName($name)
    {
        $name = $this->normalizeName($name);
        if ($name != '') {
            if (
                (UTF8String::strtolower($name) == UTF8String::strtolower(self::PUBLISHED_FOLDER_NAME))
                || (UTF8String::strtolower($name) == UTF8String::strtolower(self::EDIT_FOLDER_NAME))
            ) {
                return (false);
            } else {
                return (preg_match("=^[a-zA-Z0-9-_.]+$=", $name) > 0);
            }
        } else {
            return (false);
        }
    }

    public function create($parent_id, $name, $caption, $is_copy = false)
    {
        $name = $this->normalizeName($name);
        if ($this->isValidName($name)) {
            $parent_exists = true;
            if ($parent_id != self::ROOT_ID) {
                $parent_data = $this->getProperties($parent_id);
                if ($parent_data === false) {
                    $parent_exists = false;
                }
            }
            if ($parent_exists) {
                if (!$this->nameExists($parent_id, $name)) {
                    $res = Db::insert(
                        "pages",
                        array(
                            "parent-id" => $parent_id,
                            "name" => $name
                        )
                    );
                    if ($res !== false) {
                        $id = $res;
                        $this->setCaption($id, $caption);
                        if (!$is_copy) {
                            $page_folder = $this->getPageEditFolder($id);
                            FileUtils::createFolderRecursive($page_folder);
                        }
                        $parameters = array(
                            'pageId' => $id
                        );
                        $data = null;
                        Plugins::call(Plugins::CREATE_PAGE, $parameters, $data);
                        return ($id);
                    } else {
                        return (false);
                    }
                } else {
                    return (false);
                }
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    public function rename($id, $new_name)
    {
        $new_name = $this->normalizeName($new_name);
        if ($this->isValidName($new_name)) {
            $properties = $this->getProperties($id);
            if ($properties !== false) {
                if (!$this->nameExists($properties['parent-id'], $new_name, $id)) {
                    $new_properties = array('name' => $new_name);
                    $old_folder = $this->getPageBaseFolder($id);
                    $result = $this->setProperties($id, $new_properties);
                    if ($result !== false) {
                        $new_folder = $this->getPageBaseFolder($id);
                        FileUtils::rename($old_folder, $new_folder);
                    }
                    $parameters = array(
                        'pageId' => $id,
                        'newName' => $new_name,
                    );
                    $data = null;
                    Plugins::call(Plugins::RENAME_PAGE, $parameters, $data);
                    return ($result);
                }
            }
        }
        return (false);
    }

    public function pageAliasExistsForLanguage($parent_id, $new_alias, $language_id, $self_id = null)
    {
        $new_alias = $this->normalizeName($new_alias);
        $elements = $this->getChildren($parent_id, false);
        if (trim($new_alias) != '') {
            if (count($elements) > 0) {
                foreach ($elements as $element) {
                    $check_this_element = true;
                    if ($self_id !== null) {
                        if ($element['id'] == $self_id) {
                            $check_this_element = false;
                        }
                    }
                    if ($check_this_element) {
                        $element_alias = $this->getPageAliasForLanguage($element['id'], $language_id);
                        if ($element_alias == $new_alias) {
                            return (true);
                        }
                    }
                }
            }
        }
        return (false);
    }

    public function setPageAliasForLanguage($id, $language_id, $alias)
    {
        $alias = $this->normalizeName($alias);
        $existing = Db::getFirst(
            "SELECT * FROM [prefix]page_aliases WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                'pageId' => $id,
                'languageId' => $language_id
            )
        );
        $res = false;
        if ($existing !== false) {
            $res = Db::update(
                "page_aliases",
                array(
                    'alias' => $alias
                ),
                "`page-id` = :pageId AND `language-id` = :languageId",
                array(
                    "pageId" => $id,
                    "languageId" => $language_id,
                )
            );
        } else {
            $res = Db::insert(
                "page_aliases",
                array(
                    'language-id' => $language_id,
                    'page-id' => $id,
                    'alias' => $alias
                )
            );
        }
        return ($res !== false);
    }

    public function setPageAliases($id, $aliases)
    {
        $res = true;
        if (count($aliases) > 0) {
            foreach ($aliases as $language_id => $alias) {
                if (!$this->setPageAliasForLanguage($id, $language_id, $alias)) {
                    $res = false;
                }
            }
        }
        return ($res);
    }

    public function getPageAliasForLanguage($id, $language_id)
    {
        $alias = Db::getFirst(
            "SELECT * FROM [prefix]page_aliases WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                'pageId' => $id,
                'languageId' => $language_id
            )
        );
        if ($alias !== false) {
            return ($alias['alias']);
        } else {
            return ('');
        }
    }

    public function getPageAliases($id)
    {
        $aliases = array();
        foreach ($this->config['languages']['list'] as $language_id => $language) {
            $aliases[$language_id] = $this->getPageAliasForLanguage($id, $language_id);
        }
        return ($aliases);
    }

    public function deletePageAliasForLanguage($id, $language_id)
    {
        return (
        Db::delete(
            'page_aliases',
            "(`page-id` = :pageId) AND (`language-id` = :languageId)",
            array(
                ':pageId' => $id,
                ':languageId' => $language_id
            )
        )
        );
    }

    public function deletePageAliases($id)
    {
        return (
        Db::delete(
            'page_aliases',
            "`page-id` = :pageId",
            array(
                ':pageId' => $id
            )
        )
        );
    }

    public function setTranslatedLinkUrlForLanguage($id, $language_id, $link_url)
    {
        $existing = Db::getFirst(
            "SELECT * FROM [prefix]page_translated_link_urls WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                'pageId' => $id,
                'languageId' => $language_id
            )
        );
        $res = false;
        if ($existing !== false) {
            $res = Db::update(
                "page_translated_link_urls",
                array(
                    'link-url' => $link_url
                ),
                "`page-id` = :pageId AND `language-id` = :languageId",
                array(
                    "pageId" => $id,
                    "languageId" => $language_id,
                )
            );
        } else {
            $res = Db::insert(
                "page_translated_link_urls",
                array(
                    'language-id' => $language_id,
                    'page-id' => $id,
                    'link-url' => $link_url
                )
            );
        }
        return ($res !== false);
    }

    public function setTranslatedLinkUrls($id, $link_urls)
    {
        $res = true;
        if (count($link_urls) > 0) {
            foreach ($link_urls as $language_id => $link_url) {
                if (!$this->setTranslatedLinkUrlForLanguage($id, $language_id, $link_url)) {
                    $res = false;
                }
            }
        }
        return ($res);
    }


    public function getTranslatedLinkUrlForLanguage($id, $language_id)
    {
        $link_url = Db::getFirst(
            "SELECT * FROM [prefix]page_translated_link_urls WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                'pageId' => $id,
                'languageId' => $language_id
            )
        );
        if ($link_url !== false) {
            return ($link_url['link-url']);
        } else {
            return ('');
        }
    }

    public function getTranslatedLinkUrls($id)
    {
        $link_urls = array();
        foreach ($this->config['languages']['list'] as $language_id => $language) {
            $link_urls[$language_id] = $this->getTranslatedLinkUrlForLanguage($id, $language_id);
        }
        return ($link_urls);
    }

    public function deleteTranslatedLinkUrlForLanguage($id, $language_id)
    {
        return (
        Db::delete(
            'page_translated_link_urls',
            "(`page-id` = :pageId) AND (`language-id` = :languageId)",
            array(
                ':pageId' => $id,
                ':languageId' => $language_id
            )
        )
        );
    }

    public function deleteTranslatedLinkUrls($id)
    {
        return (
        Db::delete(
            'page_translated_link_urls',
            "`page-id` = :pageId",
            array(
                ':pageId' => $id
            )
        )
        );
    }

    protected function getCaptionArray($caption)
    {
        if (is_array($caption)) {
            return ($caption);
        } else {
            return (array(Config::get()->languages->standard => $caption));
        }
    }

    public function setCaptionInLanguage($id, $language_id, $value)
    {
        $existing = Db::getFirst(
            "SELECT * FROM [prefix]page_captions WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                'pageId' => $id,
                'languageId' => $language_id
            )
        );
        $res = false;
        if ($existing !== false) {
            $res = Db::update(
                "page_captions",
                array(
                    'value' => $value
                ),
                "`page-id` = :pageId AND `language-id` = :languageId",
                array(
                    "pageId" => $id,
                    "languageId" => $language_id,
                )
            );
        } else {
            $res = Db::insert(
                "page_captions",
                array(
                    'language-id' => $language_id,
                    'page-id' => $id,
                    'value' => $value
                )
            );
        }
        return ($res !== false);
    }

    public function setCaption($id, $caption)
    {
        $caption = $this->getCaptionArray($caption);
        $res = true;
        if (count($caption) > 0) {
            foreach ($caption as $key => $value) {
                if (!$this->setCaptionInLanguage($id, $key, $value)) {
                    $res = false;
                }
            }
        }
        return ($res);
    }

    public function getCaptionInLanguage($id, $language_id)
    {
        $caption = Db::getFirst(
            "SELECT * FROM [prefix]page_captions WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                "pageId" => $id,
                "languageId" => $language_id
            )
        );
        if ($caption !== false) {
            return ($caption["value"]);
        } else {
            return (false);
        }
    }

    public function getCaption($id)
    {
        $languages = Config::get()->languages->list;
        $caption = array();
        foreach ($languages as $key => $value) {
            $res = $this->getCaptionInLanguage($id, $key);
            if ($res !== false) {
                $caption[$key] = $res;
            } else {
                $caption[$key] = "";
            }
        }
        return ($caption);
    }

    public function getAnyCaption($id, $preferred_language = null, $languages = null, $caption = null)
    {
        if ($preferred_language === null) {
            $preferred_language = Config::get()->languages->standard;
        }
        if ($languages === null) {
            $languages = Config::get()->languages->list->getArrayCopy();
        }
        $preferredSubstitutes = $languages[$preferred_language]['preferredSubstitutes'];
        if ($caption === null) {
            $caption = $this->getCaption($id);
        }
        if (isset($caption[$preferred_language])) {
            if ($caption[$preferred_language] != '') {
                return ($caption[$preferred_language]);
            }
        }
        foreach ($preferredSubstitutes as $substitute) {
            if (isset($caption[$substitute])) {
                if ($caption[$substitute] != '') {
                    return ($caption[$substitute]);
                }
            }
        }
        return ('');
    }

    protected function getVisibilityArray($visibility)
    {
        if (is_array($visibility)) {
            return ($visibility);
        } else {
            return (array(Config::get()->languages->standard => $visibility));
        }
    }

    public function setVisibilityInLanguage($id, $language_id, $value)
    {
        $existing = Db::getFirst(
            "SELECT * FROM [prefix]page_visibility WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                'pageId' => $id,
                'languageId' => $language_id
            )
        );
        $res = false;
        if ($existing !== false) {
            $res = Db::update(
                "page_visibility",
                array(
                    'value' => $value
                ),
                "`page-id` = :pageId AND `language-id` = :languageId",
                array(
                    "pageId" => $id,
                    "languageId" => $language_id,
                )
            );
        } else {
            $res = Db::insert(
                "page_visibility",
                array(
                    'language-id' => $language_id,
                    'page-id' => $id,
                    'value' => $value
                )
            );
        }
        return ($res !== false);
    }

    public function setVisibility($id, $visibility)
    {
        $visibility = $this->getVisibilityArray($visibility);
        $res = true;
        if (count($visibility) > 0) {
            foreach ($visibility as $key => $value) {
                if (!$this->setVisibilityInLanguage($id, $key, $value)) {
                    $res = false;
                }
            }
        }
        return ($res);
    }

    public function getVisibilityInLanguage($id, $language_id)
    {
        $visibility = Db::getFirst(
            "SELECT * FROM [prefix]page_visibility WHERE `page-id` = :pageId AND `language-id` = :languageId",
            array(
                "pageId" => $id,
                "languageId" => $language_id
            )
        );
        if ($visibility !== false) {
            return ($visibility["value"]);
        } else {
            return (false);
        }
    }

    public function getVisibility($id)
    {
        $languages = Config::get()->languages->list;
        $visibility = array();
        foreach ($languages as $key => $value) {
            $res = $this->getVisibilityInLanguage($id, $key);
            if ($res !== false) {
                $visibility[$key] = $res;
            } else {
                $visibility[$key] = 0;
            }
        }
        return ($visibility);
    }

    public function isPageVisible($id, $languageId, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            switch ($properties['visibility']) {
                case self::VISIBILITY_ALWAYS:
                    return (true);
                    break;
                case self::VISIBILITY_NEVER:
                    return (false);
                    break;
                case self::VISIBILITY_SELECT:
                    $vis = $this->getVisibilityInLanguage($id, $languageId);
                    if ($vis !== false) {
                        return ($vis > 0);
                    } else {
                        return (false);
                    }
                    break;
            }
        } else {
            return (false);
        }
    }

    public function isPageActive($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            return ($properties['active'] > 0);
        } else {
            return (false);
        }
    }

    public function showPageInNavigation($id, $language_id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            if ($properties['status'] != self::STATUS_NEW) {
                if ($this->isPageActive($id, $properties = null)) {
                    if ($this->isPageVisible($id, $language_id, $properties)) {
                        return (true);
                    }
                }
            }
        }
        return (false);
    }

    public function isPageLink($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            return ($properties['template-id'] === null);
        } else {
            return (false);
        }
    }

    public function isPageLinkTranslated($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            return ($properties['link-translated'] > 0);
        } else {
            return (false);
        }
    }

    private function replaceLinkPlaceholders($url)
    {
        if (UTF8String::strtolower(UTF8String::substr($url, 0, UTF8String::strlen('link://'))) == 'link://') {
            $url = Config::get()->baseUrl . UTF8String::substr($url, UTF8String::strlen('link://'));
        } elseif (UTF8String::strtolower(UTF8String::substr($url, 0, UTF8String::strlen('download://'))) == 'download://') {
            $url = Config::get()->baseUrl . 'user-data/downloads/' . UTF8String::substr($url,
                    UTF8String::strlen('download://'));
        }
        return ($url);
    }

    public function getPageLinkUrl($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            $url = $properties['link-url'];
            return ($this->replaceLinkPlaceholders($url));
        } else {
            return ('');
        }
    }

    public function getTranslatedPageLinkUrl($id, $language_id)
    {
        $url = $this->getTranslatedLinkUrlForLanguage($id, $language_id);
        return ($this->replaceLinkPlaceholders($url));
    }

    public function getPageLinkNewWindow($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            return ($properties['link-new-window'] > 0);
        } else {
            return (false);
        }
    }

    public function getSubpageCount($id)
    {
        $count = 0;
        $children = $this->getChildren($id, false);
        if ($children !== false) {
            if (count($children) > 0) {
                foreach ($children as $child) {
                    $count = $count + $this->getSubpageCount($child['id']);
                    $count++;
                }
            }
        }
        return ($count);
    }

    public function getLastPosition($parent_id)
    {
        $res = Db::getFirst('SELECT id, position FROM [prefix]pages WHERE `parent-id`=:parentId ORDER BY position DESC',
            array(':parentId' => $parent_id));
        if ($res !== false) {
            return ($res['position'] + 1);
        } else {
            return (0);
        }
    }

    public function delete($id, $properties = null)
    {
        if ($properties === null) {
            $properties = $this->getProperties($id);
        }
        if ($properties !== false) {
            $children = $this->getChildren($id, false);
            if ($children !== false) {
                if (count($children) > 0) {
                    // Wenn die Seite noch Unterseiten enth�lt, darf sie nicht gel�scht werden,
                    // um die Gefahr von verwaisten Seiten nicht aufkommen zu lassen
                    return (false);
                }
            }
            $folder = $this->getPageBaseFolder($id, $properties);
            $parameters = array(
                'pageId' => $id,
                'pageFolder' => $folder
            );
            $data = null;
            Plugins::call(Plugins::DELETE_PAGE, $parameters, $data);
            if ($folder !== false) {
                FileUtils::deleteFolder($folder);
            }
            Db::delete('page_captions', "`page-id` = :pageId", array(':pageId' => $id));
            Db::delete('page_visibility', "`page-id` = :pageId", array(':pageId' => $id));
            Db::delete('page_cache', "`page-id` = :pageId", array(':pageId' => $id));
            Db::delete('pages', "`id` = :id", array(':id' => $id));
            $this->deletePageAliases($id);
            $this->deleteTranslatedLinkUrls($id);
            Plugins::call(Plugins::AFTER_DELETE_PAGE, $parameters, $data);
            return (true);
        } else {
            return (false);
        }
    }

    protected function getCachedProperties($id)
    {
        if (isset(self::$properties_cache[$id])) {
            return (self::$properties_cache[$id]);
        } else {
            return (false);
        }
    }

    protected function setCachedProperties($id, $properties)
    {
        if (Request::isFrontend()) {
            self::$properties_cache[$id] = $properties;
            return (true);
        } else {
            return (false);
        }
    }

    protected function removeCachedProperties($id)
    {
        if (isset(self::$properties_cache[$id])) {
            unset(self::$properties_cache[$id]);
            return (true);
        } else {
            return (false);
        }
    }

    public function clearPropertiesCache()
    {
        self::$properties_cache = array();
    }

    public function getProperties($id, $force_reload = false)
    {
        if ($force_reload) {
            $cached_properties = false;
        } else {
            $cached_properties = $this->getCachedProperties($id);
        }
        if ($cached_properties !== false) {
            return ($cached_properties);
        } else {
            $properties = Db::getFirst('SELECT * FROM [prefix]pages WHERE `id`=:id', array('id' => $id));
            $this->setCachedProperties($id, $properties);
            return ($properties);
        }
    }

    public function setProperties($id, $properties)
    {
        $parameters = array(
            'pageId' => $id,
            'newProperties' => $properties
        );
        $data = null;
        Plugins::call(Plugins::SET_PAGE_PROPERTIES, $parameters, $data);
        $res = Db::update(
            "pages",
            $properties,
            "`id`=:id",
            array('id' => $id)
        );
        $this->removeCachedProperties($id);
        return ($res !== false);
    }

    public function move($id, $dest_id, $position = null)
    {
        if ($dest_id != self::ROOT_ID) {
            if ($this->getProperties($dest_id) === false) {
                return (false);
            }
        }
        $source_element = $this->getProperties($id);
        $old_folder = $this->getPageBaseFolder($id, $source_element);
        if ($source_element === false) {
            return (false);
        }
        if ($source_element['parent-id'] == $dest_id) {
            return (true);
        }
        if ($position === null) {
            $position = $this->getLastPosition($dest_id);
        }
        $new_name = $this->getUniqueName($dest_id, $source_element['name']);
        $properties = array(
            'parent-id' => $dest_id,
            'position' => $position,
            'name' => $new_name
        );
        $result = $this->setProperties($id, $properties);
        if ($result !== false) {
            $new_folder = $this->getPageBaseFolder($id);
            FileUtils::rename($old_folder, $new_folder);
            $parameters = array(
                'pageId' => $id,
                'destinationId' => $dest_id,
                'position' => $position,
            );
            $data = null;
            Plugins::call(Plugins::MOVE_PAGE, $parameters, $data);
        }
        return ($result);
    }

    public function copy($id, $dest_id, $position = null)
    {
        if ($dest_id != self::ROOT_ID) {
            if ($this->getProperties($dest_id) === false) {
                return (false);
            }
        }
        $source_element = $this->getProperties($id);
        $source_folder = $this->getPageBaseFolder($id, $source_element);
        if ($source_element === false) {
            return (false);
        }
        if ($position === null) {
            $position = $this->getLastPosition($dest_id);
        }
        $caption = $this->getCaption($id);
        $newId = $this->create($dest_id, $this->getUniqueName($dest_id, $source_element['name']), $caption, true);
        if ($newId !== false) {
            $properties = $source_element;
            unset($properties['id']);
            unset($properties['parent-id']);
            unset($properties['name']);
            $properties['position'] = $position;
            $this->setProperties($newId, $properties);
            $visibility = $this->getVisibility($id);
            if ($visibility !== false) {
                $this->setVisibility($newId, $visibility);
            }
            $dest_folder = $this->getPageBaseFolder($newId);
            FileUtils::copyFolder($source_folder, $dest_folder);
            $parameters = array(
                'pageId' => $newId,
                'sourcePageId' => $id,
                'destinationId' => $dest_id,
                'position' => $position,
            );
            $data = null;
            Plugins::call(Plugins::COPY_PAGE, $parameters, $data);
        }
        return ($newId);
    }

    public function getUniqueName($parent_id, $preferred_name)
    {
        $preferred_name = $this->normalizeName($preferred_name);
        $pos_of_hyphen = UTF8String::strrpos($preferred_name, '-');
        if ($pos_of_hyphen !== false) {
            if ($pos_of_hyphen > 0) {
                $number_part = UTF8String::substr($preferred_name, $pos_of_hyphen + 1);
                if (is_numeric($number_part)) {
                    $preferred_name = UTF8String::substr($preferred_name, 0, $pos_of_hyphen);
                }
            }
        }
        if ($this->isValidName($preferred_name)) {
            if (!$this->nameExists($parent_id, $preferred_name)) {
                return ($preferred_name);
            } else {
                $existing = Db::get('SELECT id, name FROM [prefix]pages WHERE `name` LIKE :pattern',
                    array(':pattern' => $preferred_name . '-%'));
                $highest_number = 0;
                if ($existing !== false) {
                    if (count($existing) > 0) {
                        foreach ($existing as $element) {
                            $number_part = UTF8String::substr($element['name'], UTF8String::strlen($preferred_name . '-'));
                            if (is_numeric($number_part)) {
                                if ((int)$number_part > $highest_number) {
                                    $highest_number = (int)$number_part;
                                }
                            }
                        }
                    }
                }
                if ($highest_number < 2) {
                    $highest_number = 2;
                } else {
                    $highest_number++;
                }
                if (!$this->nameExists($parent_id, $preferred_name . '-' . $highest_number)) {
                    return ($preferred_name . '-' . $highest_number);
                }
            }
        }
        return (md5(uniqid(rand(0, 99999), true)));
    }

    public function sanitizePositions($parent_id)
    {
        $elements = $this->getChildren($parent_id, false);
        if ($elements !== false) {
            $counter = 0;
            foreach ($elements as $element) {
                if ($element['position'] != $counter) {
                    $this->setProperties($element['id'], array('position' => $counter));
                }
                $counter++;
            }
        }
    }

    public function splicePositions($parent_id, $pos, $count)
    {
        $elements = $this->getChildren($parent_id, false);
        if ($elements !== false) {
            $new_pos = 0;
            foreach ($elements as $key => $element) {
                if ($new_pos < $pos) {
                    $this->setProperties($element['id'], array('position' => $new_pos));
                } else {
                    $this->setProperties($element['id'], array('position' => $new_pos + $count));
                }
                $new_pos++;
            }
        }
    }

    public function publish($id)
    {
        $properties = $this->getProperties($id);
        if ($properties === false) {
            return (false);
        }
        $res = true;
        if ($properties['status'] != self::STATUS_PUBLISHED) {
            $published_folder = $this->getPagePublishedFolder($id, $properties);
            $edit_folder = $this->getPageEditFolder($id, $properties);
            if (file_exists($edit_folder)) {
                if (file_exists($published_folder)) {
                    FileUtils::deleteFolder($published_folder);
                }
                FileUtils::rename($edit_folder, $published_folder);
            }
            $new_properties = array(
                'status' => self::STATUS_PUBLISHED,
                'last-publish-date' => time()
            );
            $res = $this->setProperties($id, $new_properties);
        }
        $parameters = array(
            'pageId' => $id
        );
        $data = null;
        Plugins::call(Plugins::PUBLISH_PAGE, $parameters, $data);
        return ($res);
    }

    public function getData($id, $only_published = false, $ignore_plugins = false)
    {

        $properties = $this->getProperties($id);
        if ($properties === false) {
            return (false);
        }

        if (($properties['status'] == self::STATUS_PUBLISHED) || ($only_published)) {
            $folder = $this->getPagePublishedFolder($id, $properties);
            $url = $this->getPageFilesPublishedUrl($id, $properties);
        } else {
            $folder = $this->getPageEditFolder($id, $properties);
            $url = $this->getPageFilesEditUrl($id, $properties);
        }

        $page_files_folder = $folder . self::PAGE_FILES_FOLDER_NAME;
        $page_files_url = $url;
        if (!file_exists($page_files_folder)) {
            FileUtils::createFolderRecursive($page_files_folder);
        }

        $file = $folder . self::DATA_FILE_NAME;
        if (file_exists($file)) {
            $data = @file_get_contents($file);
            if ($data !== false) {

                $decoded_data = json_decode($data, true);

                if (!$ignore_plugins) {
                    $parameters = array(
                        'pageId' => $id,
                        'pageFiles' => $page_files_folder . '/',
                        'pageFilesUrl' => $page_files_url . '/'
                    );
                    Plugins::call(Plugins::BEFORE_LOAD_PAGE_DATA_FIELDS, $parameters, $decoded_data);
                    $this->applyPluginsToDataFields(Plugins::LOAD_PAGE_DATA_FIELD, $parameters, $decoded_data,
                        $properties);
                    Plugins::call(Plugins::AFTER_LOAD_PAGE_DATA_FIELDS, $parameters, $decoded_data);
                }

                $encoded_data = json_encode($decoded_data);

                return ($encoded_data);
            } else {
                return (false);
            }
        } else {
            return (json_encode(array()));
        }
    }

    public function setData($id, $data)
    {
        if ($data === null) {
            return (false);
        }

        $properties = $this->getProperties($id);
        if ($properties === false) {
            return (false);
        }

        $folder = $this->getPageEditFolder($id, $properties);
        $url = $this->getPageFilesEditUrl($id, $properties);
        if (!is_dir($folder)) {
            $published_folder = $this->getPagePublishedFolder($id, $properties);
            if (is_dir($published_folder)) {
                if (!FileUtils::copyFolder($published_folder, $folder)) {
                    return (false);
                }
            } else {
                if (!FileUtils::createFolderRecursive($folder)) {
                    return (false);
                }
            }
        }

        $page_files_folder = $folder . self::PAGE_FILES_FOLDER_NAME;
        $page_files_url = $url /* . self::PAGE_FILES_FOLDER_NAME */
        ;
        if (!file_exists($page_files_folder)) {
            FileUtils::createFolderRecursive($page_files_folder);
        }

        $decoded_data = json_decode($data, true);
        $parameters = array(
            'pageId' => $id,
            'pageFiles' => $page_files_folder . '/',
            'pageFilesUrl' => $page_files_url . '/'
        );
        Plugins::call(Plugins::BEFORE_SAVE_PAGE_DATA_FIELDS, $parameters, $decoded_data);
        $this->applyPluginsToDataFields(Plugins::SAVE_PAGE_DATA_FIELD, $parameters, $decoded_data, $properties);
        Plugins::call(Plugins::AFTER_SAVE_PAGE_DATA_FIELDS, $parameters, $decoded_data);
        $encoded_data = json_encode($decoded_data);


        $file = $folder . self::DATA_FILE_NAME;
        if (!FileUtils::createFile($file)) {
            return (false);
        }
        if (@file_put_contents($file, $encoded_data) === false) {
            return (false);
        }

        if ($properties['status'] == self::STATUS_PUBLISHED) {
            $new_properties = array(
                'status' => self::STATUS_EDIT
            );
            $this->setProperties($id, $new_properties);
        }

        return (true);
    }

    protected function applyPluginsToDataField($hook, $parameters, $field, &$data, $languages)
    {
        if ($field['type'] == 'array') {
            if (is_array($data)) {
                $field_parameters = array(
                    'arrayId' => $field['id'],
                );
                $field_parameters = array_merge($parameters, $field_parameters);
                foreach ($data as $row_key => $row_data) {
                    foreach ($field['parameters']['fields'] as $array_field) {
                        if (isset($data[$row_key][$array_field['id']])) {
                            $changed_data = $data[$row_key][$array_field['id']];
                            $this->applyPluginsToDataField($hook, $field_parameters, $array_field, $changed_data,
                                $languages);
                            $data[$row_key][$array_field['id']] = $changed_data;
                        }
                    }
                }
            }
        } else {
            $untranslatable = false;
            if (isset($field['untranslatable'])) {
                $untranslatable = $field['untranslatable'];
            }
            if (!$untranslatable) {
                foreach ($languages as $language_id => $language) {
                    if (isset($data[$language_id])) {
                        if (isset($field['parameters'])) {
                            $fieldParameters = $field['parameters'];
                        } else {
                            $fieldParameters = array();
                        }
                        $field_parameters = array(
                            'fieldId' => $field['id'],
                            'fieldType' => $field['type'],
                            'fieldParameters' => $fieldParameters,
                            'languageId' => $language_id
                        );
                        $field_parameters = array_merge($parameters, $field_parameters);
                        $changed_data = $data[$language_id];
                        Plugins::call($hook, $field_parameters, $changed_data);
                        $data[$language_id] = $changed_data;
                    }
                }
            } else {
                if (isset($data)) {
                    if (isset($field['parameters'])) {
                        $fieldParameters = $field['parameters'];
                    } else {
                        $fieldParameters = array();
                    }
                    $field_parameters = array(
                        'fieldId' => $field['id'],
                        'fieldType' => $field['type'],
                        'fieldUntranslatable' => true,
                        'fieldParameters' => $fieldParameters
                    );
                    $field_parameters = array_merge($parameters, $field_parameters);
                    $changed_data = $data;
                    Plugins::call($hook, $field_parameters, $changed_data);
                    $data = $changed_data;
                }
            }
        }
    }

    public function applyPluginsToDataFields($hook, $parameters, &$data = null, $properties = null)
    {
        if (($properties === null) || ($properties === false)) {
            return (false);
        }

        $pageStructures = DataStructure::pagesArray();
        if (!isset($pageStructures[$properties['template-id']]['structure'])) {
            return (false);
        }

        $page = $pageStructures[$properties['template-id']]['structure'];
        $elements = DataStructure::elementsArray();
        $languages = Config::get()->languages->list;

        if (!is_array($parameters)) {
            $parameters = array();
        }

        foreach ($page as $block_id => $block) {

            // Datenblock
            if ($block['type'] == 'datablock') {
                if (isset($data[$block_id])) {
                    if (isset($block['fields'])) {
                        foreach ($block['fields'] as $field) {
                            if (isset($data[$block_id][$field['id']])) {
                                $field_parameters = array(
                                    'blockId' => $block_id
                                );
                                $field_parameters = array_merge($parameters, $field_parameters);
                                $this->applyPluginsToDataField($hook, $field_parameters, $field,
                                    $data[$block_id][$field['id']], $languages);
                            }
                        }
                    }
                }
            }

            // Container
            if ($block['type'] == 'container') {
                if (isset($data[$block_id])) {
                    if (is_array($data[$block_id])) {
                        foreach ($data[$block_id] as $container_element_key => $container_element) {
                            if (isset($data[$block_id][$container_element_key]['content'])) {
                                if (isset($elements[$data[$block_id][$container_element_key]['elementId']]['structure'])) {
                                    $element_structure = $elements[$data[$block_id][$container_element_key]['elementId']]['structure'];
                                    foreach ($element_structure as $field) {
                                        if (isset($data[$block_id][$container_element_key]['content'][$field['id']])) {
                                            $field_parameters = array(
                                                'blockId' => $block_id,
                                                'elementId' => $data[$block_id][$container_element_key]['elementId']
                                            );
                                            $field_parameters = array_merge($parameters, $field_parameters);
                                            $this->applyPluginsToDataField($hook, $field_parameters, $field,
                                                $data[$block_id][$container_element_key]['content'][$field['id']],
                                                $languages);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }

        return (true);
    }

    public function isGlobalElementsPageAvailable()
    {
        $global_elements = Db::get(
            "SELECT * FROM [prefix]pages WHERE `template-id` = :templateId",
            array(":templateId" => self::GLOBAL_ELEMENTS)
        );
        if ($global_elements !== false) {
            if (count($global_elements) > 0) {
                return (true);
            }
        }
        return (false);
    }

    public function isGlobalElementsPageTemplateAvailable()
    {
        $templates = DataStructure::pages();
        if (isset($templates[self::GLOBAL_ELEMENTS])) {
            return (true);
        }
        return (false);
    }

    public function createGlobalElementsPage()
    {
        if ($this->isGlobalElementsPageTemplateAvailable()) {
            if (!$this->isGlobalElementsPageAvailable()) {
                $id = $this->create(self::ROOT_ID, self::GLOBAL_ELEMENTS_PAGE_NAME, self::GLOBAL_ELEMENTS_PAGE_NAME);
                if ($id !== false) {
                    $properties = array(
                        'template-id' => self::GLOBAL_ELEMENTS,
                        'active' => 0,
                        'cachable' => 0
                    );
                    $this->setProperties($id, $properties);
                }
                return ($id);
            }
        }
        return (false);
    }

    public function getGlobalElementsPageId()
    {
        $global_elements = Db::get(
            "SELECT * FROM [prefix]pages WHERE `template-id` = :templateId",
            array(":templateId" => self::GLOBAL_ELEMENTS)
        );
        if ($global_elements !== false) {
            if (count($global_elements) > 0) {
                return ($global_elements[0]['id']);
            }
        }
        return (false);

    }

}
