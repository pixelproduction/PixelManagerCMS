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

require_once(APPLICATION_ROOT . 'system/core/library/smarty/Smarty.class.php');

/**
 * Template-Klasse extended Smarty und setzt automatisch alle ben�tigten Verzeichnisse
 *
 * @author Steffen Riedel <riedel@pixelproduction.de>
 */

class Template extends Smarty
{
    /**
     * Constructor
     *
     * @author Steffen Riedel <riedel@pixelproduction.de>
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTemplateDir(
            array(
                'root' => APPLICATION_ROOT . 'system/custom/frontend/templates/',
                'pages' => APPLICATION_ROOT . 'system/custom/frontend/templates/pages/',
                'elements' => APPLICATION_ROOT . 'system/custom/frontend/templates/elements/',
            )
        );
        $this->setCompileDir(APPLICATION_ROOT . 'user-data/tmp/smarty_c/');
        $this->setPluginsDir(
            array(
                APPLICATION_ROOT . 'system/core/library/smarty/plugins/',
                APPLICATION_ROOT . 'system/core/frontend/plugins/smarty/',
                APPLICATION_ROOT . 'system/custom/frontend/plugins/smarty/',
            )
        );
        $this->error_reporting = (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
    }

    /**
     * Statischer Constructor f�r Method-Chaining
     *
     * @author Steffen Riedel <riedel@pixelproduction.de>
     * @return Template
     */
    public static function create()
    {
        return new self();
    }
}
