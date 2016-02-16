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
    [
        'type' => 'array',
        'id' => 'images',
        'caption' => 'Bilder',
        'parameters' => [
            'columns' => [
                [
                    'caption' => 'Bild',
                    'fieldId' => 'image'
                ],
                [
                    'caption' => 'Alternativ Text',
                    'fieldId' => 'alt'
                ],
                [
                    'caption' => 'Titel',
                    'fieldId' => 'title'
                ],
            ],
            'fields' => [
                [
                    'type' => 'image',
                    'id' => 'image',
                    'caption' => 'Bild',
                    'parameters' => [
                        'editable' => true,
                        'maxWidth' => 200,
                        'maxHeight' => 200,
                        'additionalSizes' => [
                            [
                                'id' => 'popup',
                                'maxWidth' => 1000,
                                'maxHeight' => 1000
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'singleLineText',
                    'id' => 'alt',
                    'caption' => 'Alternativ Text',
                ],
                [
                    'type' => 'singleLineText',
                    'id' => 'title',
                    'caption' => 'Titel',
                ],
            ],
        ]
    ],
];
