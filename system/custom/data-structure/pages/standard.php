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

return [

    'head' => [
        'type' => 'datablock',
        'caption' => 'Allgemeines (Seiten-Titel, Kurzbeschreibung, Kopfbild)',
        'closed' => false,
        'fields' => [
            [
                'type' => 'singleLineText',
                'id' => 'title',
                'caption' => 'Seiten-Titel',
                'parameters' => [],
                'help' => 'Der Titel oben in der Titelzeile des Browsers',
            ],
            [
                'type' => 'singleLineText',
                'id' => 'metaDescription',
                'caption' => 'Kurzbeschreibung',
                'parameters' => [],
                'help' => 'Der Inhalt für das description-Metatag (relevant für Suchmaschinen]',
            ],
        ]
    ],

    'content' => [
        'type' => 'container',
        'caption' => 'Inhalt',
        'parameters' => [
            'allowElements' => [
                'text',
                'textImage',
                'imageGallery',
            ]
        ]
    ],

];
