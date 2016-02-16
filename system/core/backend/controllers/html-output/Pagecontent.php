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

class PagecontentController extends HtmlOutputController
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

    public function editAction()
    {
        $pageId = Request::getParam('pageId');

        if ($pageId === false) {
            $this->doesNotExist();
            return;
        }

        $properties = $this->pages->getProperties($pageId);
        if ($properties === false) {
            $this->doesNotExist();
            return;
        }

        if (!$this->helpers->canAccessPage($pageId, Acl::ACTION_EDIT)) {
            $this->accessDenied();
            return;
        }

        $this->view->assign('pageId', $pageId);

        $pages = DataStructure::pagesArray();
        if (isset($pages[$properties['template-id']])) {
            $this->view->assign('pageDataStructure', $pages[$properties['template-id']]['structure']);
            /*Helpers::dump($pages[$properties['template-id']]['structure']);
            die();*/
        }

        $isGlobalElementsPage = false;
        if ($properties['template-id'] == Pages::GLOBAL_ELEMENTS) {
            $isGlobalElementsPage = true;
        }
        $this->view->assign('isGlobalElementsPage', $isGlobalElementsPage);

    }

}
