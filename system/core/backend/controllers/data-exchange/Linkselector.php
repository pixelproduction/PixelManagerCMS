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

class LinkselectorController extends DataExchangeController
{

    public function defaultAction()
    {
        $this->getlinkAction();
    }

    public function getlinkAction()
    {
        // Parameter auslesen
        $link_to_page_id = Request::postParam('linkToPageId');
        $link_to_language_id = Request::postParam('linkToLanguageId');
        $link_to_anchor_name = Request::postParam('linkToAnchorName');

        // Parameter überprüfen
        if ((!is_numeric($link_to_page_id)) || ($link_to_language_id == '')) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $pages = new Pages();

        // Die absolute URL auslesen
        $absolute_url = $pages->getPageUrl($link_to_page_id, $link_to_language_id);

        // eine zum Stammverzeichnis des CMS relative URL daraus machen
        $base_url = Config::get()->baseUrl;
        $relative_url = UTF8String::substr($absolute_url, UTF8String::strlen($base_url), UTF8String::strlen($absolute_url));

        if ($link_to_anchor_name != '') {
            if ($relative_url != '') {
                $relative_url = rtrim($relative_url, '/') . '/';
            }
            $relative_url .= '#' . $link_to_anchor_name;
        }

        // Zurückgeben
        $this->success($relative_url);

    }

}
