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

/**
 *   Enthält alle Methoden für den User login
 */

final class Auth
{
    /**
     *   TODO: Prüfen was PRIVILEGES_USER tut
     */
    const PRIVILEGES_USER = 0;
    /**
     *   TODO: Prüfen was PRIVILEGES_ADMIN tut
     */
    const PRIVILEGES_ADMIN = 1;

    private static $instance = null;
    private static $password_hash = null;

    private function __construct()
    {
    }

    /**
     *   Singleton
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    /**
     *   Prüft ob der Benutzer eingeloggt ist
     *
     * @return bool
     */
    public static function isLoggedIn()
    {
        if (isset($_SESSION['pixelmanager']['user']['login'])) {
            if ($_SESSION['pixelmanager']['user']['login'] != '') {
                return (true);
            }
        }
        return (false);
    }

    /**
     *   Prüft ob der Benutzer Admin rechte besitzt
     *
     * @return bool
     */
    public static function isAdmin()
    {
        if (self::isLoggedIn()) {
            if ($_SESSION['pixelmanager']['user']['privileges'] >= self::PRIVILEGES_ADMIN) {
                return (true);
            }
        }
        return (false);
    }

    /**
     *   Holt den Namen des Benutzers aus der Session
     *
     * @return UTF8String screenname
     */
    public static function getScreenName()
    {
        if (self::isLoggedIn()) {
            return ($_SESSION['pixelmanager']['user']['screenname']);
        }
        return ('');
    }

    /**
     *   Holt den login Namen aus der Session
     *
     * @return UTF8String login
     */
    public static function getLoginName()
    {
        if (self::isLoggedIn()) {
            return ($_SESSION['pixelmanager']['user']['login']);
        }
        return ('');
    }

    /**
     *   Holt die UserId aus der Session
     *
     * @return int id
     */
    public static function getUserId()
    {
        if (self::isLoggedIn()) {
            return ($_SESSION['pixelmanager']['user']['id']);
        }
        return (false);
    }

    /**
     *   Holt die eingestellte Sprache des Users
     *
     * @return UTF8String preferred-language
     */
    public static function getUserPreferredLanguage()
    {
        if (self::isLoggedIn()) {
            return ($_SESSION['pixelmanager']['user']['preferred-language']);
        } else {
            return (false);
        }
    }

    /**
     *   Loggt den User im CMS ein
     *
     * @param UTF8String $login
     * @param UTF8String $password
     *
     * @return bool
     */
    public static function login($login, $password)
    {
        if (($login != '') && ($password != '')) {
            $db = Db::getPDO();
            $sql = "SELECT `id`, `privileges`, `screenname`, `login`, `password`, `preferred-language` FROM " . Db::tablePrefix() . "users WHERE login = :username";
            $statement = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $statement->execute(array(':username' => $login));
            if ($statement->rowCount() == 1) {
                $user = $statement->fetch(PDO::FETCH_ASSOC);
                $hash = self::getPasswordHash();
                if ($hash->CheckPassword($password, $user['password'])) {
                    $_SESSION['pixelmanager']['user']['login'] = $user["login"];
                    $_SESSION['pixelmanager']['user']['screenname'] = $user["screenname"];
                    $_SESSION['pixelmanager']['user']['id'] = $user["id"];
                    $_SESSION['pixelmanager']['user']['privileges'] = $user["privileges"];
                    $preferred_language = $user["preferred-language"];
                    if ($preferred_language === null) {
                        $preferred_language = Config::get()->backendLanguages->standard;
                    }
                    $_SESSION['pixelmanager']['user']['preferred-language'] = $preferred_language;
                    return (true);
                }
            }
        }

        /**
         * Um das Erraten eines Passworts per Brute-Force-Attacke
         * ein wenig zu erschweren, 1,5 - 3 Sekunden warten, wenn das Login fehlschlägt.
         * Einen menschlichen Benutzer wird das nicht so arg stören,
         * aber so sind deutlich weniger Versuche pro Stunde / Tag möglich.
         * Nützt nur bedingt viel, aber ist extrem leicht zu implementieren
         * und nützt zumindest ein bisschen was.
         *
         * Für weiteführende Infos siehe: https://www.owasp.org/index.php/Blocking_Brute_Force_Attacks
         *
         * Die komplexeren Lösungen sollte man für zukünftige Versionen im Hinterkopf behalten,
         * für den Anfang sollte das mal genügen... Soooo interessant sollte unser CMS für die
         * meisten Hacker nicht sein. Falls aber doch irgendwan ein großer Kunde mit sensiblen Daten dabei sein sollte,
         * sollten wir es besser nicht mehr dabei belassen...
         */
        usleep(rand(1500000, 3000000));

        return (false);
    }

    /**
     *   Loggt den User aus
     */
    public static function logout()
    {
        unset($_SESSION['pixelmanager']);
    }

    /**
     *   Erstellt den Passwort Hash für das CMS
     *
     * @return UTF8String Hash
     */
    public static function getPasswordHash()
    {
        if (self::$password_hash == null) {
            self::$password_hash = new PasswordHash(Config::get()->PasswordHash->IterationCount,
                Config::get()->PasswordHash->PortableHashes);
        }
        return (self::$password_hash);
    }

}
