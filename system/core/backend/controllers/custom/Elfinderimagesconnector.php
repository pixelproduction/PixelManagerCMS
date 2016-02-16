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

if (!Auth::isLoggedIn()) {
    Helpers::fatalError('Error: No valid user logged in.', true);
}

require_once(realpath(dirname(__FILE__) . '/../../../library/elfinder/elFinderConnector.class.php'));
require_once(realpath(dirname(__FILE__) . '/../../../library/elfinder/elFinder.class.php'));
require_once(realpath(dirname(__FILE__) . '/../../../library/elfinder/elFinderVolumeDriver.class.php'));
require_once(realpath(dirname(__FILE__) . '/../../../library/elfinder/elFinderVolumeLocalFileSystem.class.php'));

// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';


/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  UTF8String $attr attribute name (read|write|locked|hidden)
 * @param  UTF8String $path file path relative to volume root directory started with directory separator
 *
 * @return bool|null
 **/
function access($attr, $path, $data, $volume)
{
    return UTF8String::strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
        ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
        : null;                                    // else elFinder decide it itself
}

$opts = array(
    // 'debug' => true,
    'roots' => array(
        array(
            'driver' => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
            'path' => APPLICATION_ROOT . 'user-data/images/',         // path to files (REQUIRED)
            'alias' => Translate::get('Images'),
            'URL' => Config::get()->baseUrl . 'user-data/images/', // URL to files (REQUIRED)
            'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
            'uploadAllow' => array('image'),
            'uploadOrder' => array('allow', 'deny'),
            'mimeDetect' => 'internal',
            'mimefile' => APPLICATION_ROOT . 'core/library/elfinder/mime.types',
            'tmbSize' => Config::get()->elFinderThumbnailSize,
            'tmbCrop' => false,
        )
    )
);
if (isset($_GET['folder'])) {
    $opts['roots'][0]['startPath'] = APPLICATION_ROOT . 'user-data/images/' . $_GET['folder'];
}
if (Config::get()->fileUtils->useChmod) {
    $opts['roots'][0]['dirMode'] = Config::get()->fileUtils->directoryMode;
    $opts['roots'][0]['fileMode'] = Config::get()->fileUtils->fileMode;
}

// Ab hier sollte kein Fehler mehr auftauchen, weil stammt ja nicht von mir... :-)
// Und sonst zickt E_STRICT, (wenn in der Config gesetzt) rum...
error_reporting(0);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
