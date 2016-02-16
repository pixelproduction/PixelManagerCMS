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

class SyncController extends DataExchangeController
{
    public function fetchAction()
    {
        $config = Config::get();
        $syncConfig = $config->sync;

        if (!$syncConfig) {
            $this->error(self::RESULT_ERROR_CUSTOM, 'Keine Sync-Konfiguration vorhanden');
            return;
        }

        $dbname = $config->database->name;
        $dbuser = $config->database->user;
        $dbpass = $config->database->password;
        $dbhost = $config->database->host;

        $sshuser = $syncConfig->user;
        $sshpass = $syncConfig->password;
        $sshserver = $syncConfig->server;
        $sshfolder = $syncConfig->folder;

        $errors = $this->validateConfig($dbname, $dbuser, $dbpass, $dbhost, $sshuser, $sshpass, $sshserver, $sshfolder);

        if (count($errors) > 0) {
            $this->error(self::RESULT_ERROR_CUSTOM, implode('<br>', $errors));
            return;
        }

        ob_start();
        $command = APPLICATION_ROOT . 'system/core/backend/sync.sh';
        $command .= ' -dbname ' . $dbname;
        $command .= ' -dbuser ' . $dbuser;
        $command .= ' -dbpass ' . $dbpass;
        $command .= ' -dbhost ' . $dbhost;
        $command .= ' -sshuser ' . $sshuser;
        $command .= ' -sshpass ' . $sshpass;
        $command .= ' -sshserver ' . $sshserver;
        $command .= ' -sshfolder ' . $sshfolder;
        system($command);
        $result = trim(ob_get_contents());
        ob_end_clean();

        if ($result != "ok") {
            $this->error(self::RESULT_ERROR_CUSTOM, $result);
            return;
        }

        $this->success();
    }

    protected function validateConfig($dbname, $dbuser, $dbpass, $dbhost, $sshuser, $sshpass, $sshserver, $sshfolder)
    {
        $errors = array();

        if (!$dbname) {
            $errors[] = 'Kein DB-Name konfiguriert';
        }

        if (!$dbuser) {
            $errors[] = 'Kein DB-User konfiguriert';
        }

        if (!$dbpass) {
            $errors[] = 'Kein DB-Passwort konfiguriert';
        }

        if (!$dbhost) {
            $errors[] = 'Kein DB-Host konfiguriert';
        }

        if (!$sshuser) {
            $errors[] = 'Kein SSH-User konfiguriert';
        }

        if (!$sshpass) {
            $errors[] = 'Kein SSH-Passwort konfiguriert';
        }

        if (!$sshserver) {
            $errors[] = 'Kein SSH-Server konfiguriert';
        }

        if (!$sshfolder) {
            $errors[] = 'Kein SSH-Folder konfiguriert';
        }

        return $errors;
    }
}
