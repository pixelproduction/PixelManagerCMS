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

class PagepropertiesController extends HtmlOutputController
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
        $this->editAction();
    }

    public function createAction()
    {
        $caption = array();
        $alias = array();
        $visibility = array();
        $translated_link_urls = array();
        $languages = Config::get()->languages->list;
        foreach ($languages as $key => $language) {
            $caption[$key] = '';
            $alias[$key] = '';
            $visibility[$key] = 0;
            $translated_link_urls[$key] = '';
        }
        $properties = array(
            'parent-id' => null,
            'name' => '',
            'template-id' => null,
            'visibility' => Pages::VISIBILITY_ALWAYS,
            'active' => 1,
            'status' => Pages::STATUS_NEW,
            'position' => 0,
            'creation-date' => null,
            'last-change-date' => null,
            'last-publish-date' => null,
            'creation-user-id' => null,
            'creation-user-name' => null,
            'last-change-user-id' => null,
            'last-change-user-name' => null,
            'cachable' => 1,
            'link-translated' => 0,
            'link-url' => null,
            'link-new-window' => 0,
            'unique-id' => null
        );
        $aclResource = array(
            'group' => Acl::RESOURCE_GROUP_PAGES,
            'resource-id' => null,
            'description' => null,
            'user-groups-mode' => Acl::RESOURCE_SUPERUSER_ONLY
        );
        $aclUserGroups = array();
        $this->view->assign('action', 'create');
        $this->view->assign('pageId', -1);
        $this->view->assign('pageIdList', array());
        $this->view->assign('caption', $caption);
        $this->view->assign('alias', $alias);
        $this->view->assign('visibility', $visibility);
        $this->view->assign('translatedLinkUrls', $translated_link_urls);
        $this->view->assign('properties', $properties);
        $this->view->assign('inheritAcl', true);
        $this->view->assign('aclResource', $aclResource);
        $this->view->assign('aclUserGroups', $aclUserGroups);
        $this->view->assign('pagetree', $this->pages->getChildren());
        $this->view->assign('batchEdit', false);
        $this->view->assign('containsSubpages', false);
    }

    public function editAction()
    {
        $pageId = Request::getParam('pageId', -1);
        if (is_array($pageId)) {
            $containsSubpages = false;
            if (count($pageId) > 0) {
                foreach ($pageId as $id) {
                    $properties = $this->pages->getProperties($id);
                    if ($properties === false) {
                        $this->doesNotExist();
                        return;
                    }
                    if (!$this->helpers->canAccessPage($id, Acl::ACTION_EDIT)) {
                        $this->accessDenied();
                        return;
                    }
                    $children = $this->pages->getChildren($id, false);
                    if ($children !== false) {
                        if (count($children) > 0) {
                            $containsSubpages = true;
                            break;
                        }
                    }
                }
            }
            $this->view->assign('batchEdit', true);
            $this->view->assign('pageIdList', $pageId);
            $caption = array();
            $alias = array();
            $visibility = array();
            $translated_link_urls = array();
            $languages = Config::get()->languages->list;
            foreach ($languages as $key => $language) {
                $caption[$key] = '';
                $alias[$key] = '';
                $visibility[$key] = 0;
                $translated_link_urls[$key] = '';
            }
            $properties = array(
                'visibility' => Pages::VISIBILITY_ALWAYS,
                'active' => 1,
                'cachable' => 1,
                'translated-link-url' => $translated_link_url,
            );
            $aclResource = array(
                'group' => Acl::RESOURCE_GROUP_PAGES,
                'resource-id' => null,
                'description' => null,
                'user-groups-mode' => Acl::RESOURCE_SUPERUSER_ONLY
            );
            $aclUserGroups = array();
            $this->view->assign('action', 'edit');
            $this->view->assign('caption', $caption);
            $this->view->assign('alias', $alias);
            $this->view->assign('visibility', $visibility);
            $this->view->assign('translatedLinkUrls', $translated_link_urls);
            $this->view->assign('properties', $properties);
            $this->view->assign('inheritAcl', true);
            $this->view->assign('aclResource', $aclResource);
            $this->view->assign('aclUserGroups', $aclUserGroups);
            $this->view->assign('containsSubpages', $containsSubpages);
        } else {
            if ($pageId > -1) {
                $properties = $this->pages->getProperties($pageId);
                if ($properties === false) {
                    $this->doesNotExist();
                    return;
                }
                if (!$this->helpers->canAccessPage($pageId, Acl::ACTION_EDIT)) {
                    $this->accessDenied();
                    return;
                }
                $this->view->assign('action', 'edit');
                $this->view->assign('batchEdit', false);
                $this->view->assign('pageId', $pageId);
                $this->view->assign('caption', $this->pages->getCaption($pageId));
                $this->view->assign('alias', $this->pages->getPageAliases($pageId));
                $this->view->assign('visibility', $this->pages->getVisibility($pageId));
                $this->view->assign('translatedLinkUrls', $this->pages->getTranslatedLinkUrls($pageId));
                $this->view->assign('properties', $properties);
                $aclResource = Acl::getResourceData(Acl::RESOURCE_GROUP_PAGES, $pageId);
                if ($aclResource === false) {
                    $this->view->assign('inheritAcl', true);
                    $aclResource = array(
                        'group' => Acl::RESOURCE_GROUP_PAGES,
                        'resource-id' => null,
                        'description' => null,
                        'user-groups-mode' => Acl::RESOURCE_SUPERUSER_ONLY
                    );
                    $aclUserGroups = array();
                } else {
                    $this->view->assign('inheritAcl', false);
                    $aclUserGroups = Acl::getUserGroups(Acl::RESOURCE_GROUP_PAGES, $pageId);
                }
                $this->view->assign('aclResource', $aclResource);
                $this->view->assign('aclUserGroups', $aclUserGroups);
                $containsSubpages = false;
                $children = $this->pages->getChildren($pageId, false);
                if ($children !== false) {
                    if (count($children) > 0) {
                        $containsSubpages = true;
                    }
                }
                $this->view->assign('containsSubpages', $containsSubpages);
            } else {
                $this->createAction();
            }
        }
    }

}
