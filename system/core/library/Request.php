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

final class Request
{
    private static $instance = null;
    private static $request_secure = false;
    private static $request_uri = '';
    private static $request_path = array();
    private static $request_parameters = array();
    private static $front_end = true;
    private static $parsed = false;

    private function __construct()
    {
        self::parse();
    }

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

    public static function parse()
    {
        if (!self::$parsed) {

            // Request String speichern
            self::$request_uri = $_SERVER['REQUEST_URI'];

            // Prüfen, ob HTTPs verwendet wird
            self::$request_secure = false;
            if (isset($_SERVER['HTTPS'])) {
                if ($_SERVER['HTTPS'] != '') {
                    self::$request_secure = true;
                }
            }

            // Den Teil, der von interesse ist, extrahieren
            // Wenn die baseUrl z.B. http://www.pixelproduction.de/cms/ lautet,
            // und der entsprechende Request-URI /cms/admin/bla/,
            // dann schneiden wir das /cms am Anfang heraus, da uns ja nur das /admin/bla/ interessiert...
            // So kann man das CMS in einem beliebigen Unter-Verzeichnis auf dem Server installieren
            // und alles funktioniert trotzdem :-)
            $base_request_string = self::extractRequestUri(Config::get()->baseUrl);
            if (UTF8String::substr(self::$request_uri, 0, UTF8String::strlen($base_request_string)) != $base_request_string) {
                die('Fehlerhafter REQUEST_URI oder fehlerhafte Konfiguration. Bitte wenden Sie sich an den Administrator dieser Website.');
            } else {
                self::$request_uri = UTF8String::substr(self::$request_uri, UTF8String::strlen($base_request_string),
                    UTF8String::strlen(self::$request_uri));
            }

            // Die URI in ihre bestandteile (Pfad, Parameter, Anker) zerlegen
            // Laut PHP Doku muss es eine absolute, vollständige URL sein, ist aber egal, weil wir Protokoll und Server sowieso ignorieren,
            // also nehmen wir einfach irgeneinen Server an...
            $url = parse_url('http://www.pixelproduction.de/' . self::$request_uri);

            // Den Pfad auslesen und in seine Teile zerlegen und in Array ablegen
            self::$request_path = array();
            if (isset($url['path'])) {
                $url_path = trim($url['path'], '/');
                if ($url_path != '') {
                    self::$request_path = explode('/', $url_path);
                }
            }

            // ggf. die GET-Parameter in Array ablegen
            self::$request_parameters = array();
            if (isset($url['query'])) {
                $query = explode('&', $url['query']);
                if (is_array($query)) {
                    foreach ($query as $parameter) {
                        $key_value_pair = explode('=', $parameter);
                        if (is_array($key_value_pair)) {
                            if (isset($key_value_pair[0])) {
                                self::$request_parameters[$key_value_pair[0]] = '';
                                if (isset($key_value_pair[1])) {
                                    self::$request_parameters[$key_value_pair[0]] = $key_value_pair[1];
                                }
                            }
                        }
                    }
                }
            }

            // Herausfinden, ob das Frontend oder das Backend angefordert wird
            self::$front_end = true;
            if (count(self::$request_path) > 0) {
                // Wenn der erste Teil nach der Basis-URL "admin" ist, dann geht's zum Back-End, ansonsten zum Front-End
                $firstPathSegment = strtolower(self::$request_path[0]);

                self::$front_end = !in_array($firstPathSegment, array('admin', 'pm:api'));

                if ($firstPathSegment == 'admin') {
                    // Das "admin" braucht man nicht als ersten Teil, da man ja dann sowieso weiß, dass man sich im Backend befindet...
                    array_splice(self::$request_path, 0, 1);
                }

                if ($firstPathSegment == 'pm:api') {
                    self::$request_path[0] = 'api';
                }
            }

            self::$parsed = true;
        }
    }

    public static function isFrontend()
    {
        self::parse();
        return (self::$front_end);
    }

    public static function path()
    {
        self::parse();
        return (self::$request_path);
    }

    public static function parameters()
    {
        self::parse();
        return (self::$request_parameters);
    }

    public static function isSecure()
    {
        self::parse();
        return (self::$request_secure);
    }

    public static function extractRequestUri($url)
    {
        $res = '';
        $url = @parse_url($url);
        if (is_array($url)) {
            $res = $url['path'];
            if (isset($url['query'])) {
                if ($url['query'] != '') {
                    $res = $res . '?' . $url['query'];
                }
            }
            if (isset($url['anchor'])) {
                if ($url['anchor'] != '') {
                    $res = $res . '?' . $url['anchor'];
                }
            }
        }
        return ($res);
    }

    public static function getParam($key, $std_value = null)
    {
        // ggf. könnte man hier noch einen Mechanismus einbauen, um Hacker-Attacken zu erkennen und abzufangen ...
        if (isset($_GET[$key])) {
            return ($_GET[$key]);
        } else {
            return ($std_value);
        }
    }

    public static function postParam($key, $std_value = null)
    {
        // ggf. könnte man hier noch einen Mechanismus einbauen, um Hacker-Attacken zu erkennen und abzufangen ...
        if (isset($_POST[$key])) {
            return ($_POST[$key]);
        } else {
            return ($std_value);
        }
    }
    
}
