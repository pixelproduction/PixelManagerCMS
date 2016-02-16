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

class MergeLanguagesModifier extends DataModifier
{
    protected $languages;
    protected $standard_language;

    function __construct()
    {
        parent::__construct();
        $config = Config::getArray();
        $this->languages = $config['languages']['list'];
        $this->standard_language = $config['languages']['standard'];
    }

    public function modifyField(&$data, $structure, $parameters)
    {
        $untranslatable = false;
        if (isset($structure['untranslatable'])) {
            $untranslatable = $structure['untranslatable'];
        }
        if (!$untranslatable) {
            $merged = null;
            if (isset($parameters['languageId'])) {
                $preferred_language_id = $parameters['languageId'];
            } else {
                $preferred_language_id = $this->standard_language;
            }
            if (isset($data[$preferred_language_id])) {
                $merged = $data[$preferred_language_id];
            } else {
                if (isset($this->languages[$preferred_language_id])) {
                    $substitutes = $this->languages[$preferred_language_id]['preferredSubstitutes'];
                    $substituted = false;
                    foreach ($substitutes as $substitute) {
                        if (isset($data[$substitute])) {
                            $merged = $data[$substitute];
                            $substituted = true;
                            break;
                        }
                    }
                    if (!$substituted) {
                        $merged = null;
                    }
                } else {
                    $merged = null;
                }
            }
            $data = $merged;
        }
    }

}
