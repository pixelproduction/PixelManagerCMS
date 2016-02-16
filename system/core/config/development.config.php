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

// Please do not make changes which are specific to a project here.
// Custom settings belong in /system/custom/config/development.config.php

return [
    'haltOnDebugError' => true,
    'phpErrorReporting' => [
        'displayErrors' => 1,
        'errorReportingValue' => (E_ALL | E_STRICT),
        'logErrors' => 0,
        'errorLogFile' => ''
    ],
    'cacheActivated' => false
];
