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

class SyncController extends ApiController
{
    public function exportAction()
    {
        set_time_limit(0);

        $config = Config::get();

        $dbname = $config->database->name;
        $dbuser = $config->database->user;
        $dbpass = $config->database->password;
        $dbhost = $config->database->host;

        $outputDir = APPLICATION_ROOT;
        $sqlSubDir = '__sql';

        if (!file_exists($outputDir . $sqlSubDir) && !mkdir($outputDir . $sqlSubDir)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            die('Could not create output directory');
        }

        // dump database
        $dbFilename = $sqlSubDir . '/' . $dbname . '.sql';
        $command = "mysqldump -h $dbhost -u $dbuser -p$dbpass --add-drop-table --databases $dbname > $outputDir$dbFilename";
        system($command);

        // create zip file
        $zipFilename = 'export_' . date('YmdHis') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFilename,
                file_exists($zipFilename) ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true
        ) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            die('Could not create zip file');
        }

        $this->zip($zip, array(
            $dbFilename => $outputDir . $dbFilename,
            $outputDir . 'user-data/',
        ));

        $zip->close();

        // encrypt zip
        $encryptedFilename = $zipFilename . '.enc';
        $command = "openssl enc -aes-256-cbc -pass pass:" . $config->sync->secret . " < $outputDir$zipFilename > $outputDir$encryptedFilename";
        system($command);

        // send file contents
        header('Content-Length: ' . filesize($outputDir . $encryptedFilename));
        $this->sendFileContents($outputDir . $encryptedFilename);

        // clean up
        FileUtils::deleteFolder($outputDir . $sqlSubDir);
        FileUtils::deleteFile($outputDir . $zipFilename);
        FileUtils::deleteFile($outputDir . $encryptedFilename);

        die();
    }

    protected function sendFileContents($filename)
    {
        $chunksize = 1 * (1024 * 1024); // how many bytes per chunk

        $handle = fopen($filename, 'rb');

        if (false === $handle) {
            return false;
        }

        while (!feof($handle)) {
            echo fread($handle, $chunksize);
            ob_flush();
            flush();
        }

        fclose($handle);

        return true;
    }

    protected function zip(ZipArchive $zip, $files = array(), $relPath = '')
    {
        // add trailing path delimiter
        $relPath = $relPath ? rtrim($relPath, '/') . '/' : '';

        foreach ($files as $localName => $file) {
            if (is_numeric($localName)) {
                $localName = basename($file);
            }

            if (is_dir($file)) {
                $newRelPath = $relPath . $localName;
                $zip->addEmptyDir($newRelPath);

                $handle = opendir($file);
                $file = rtrim($file, '/') . '/';
                $dirContents = array();

                while (false !== $f = readdir($handle)) {
                    if (($f == '.') || ($f == '..')) {
                        continue;
                    }

                    $dirContents[] = $file . $f;
                }

                closedir($handle);

                $this->zip($zip, $dirContents, $newRelPath);
                continue;
            }

            $zip->addFile($file, $relPath . $localName);
        }
    }
}
