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

final class Helpers
{

    private static $instance = null;

    private function __construct()
    {
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

    public static function htmlEntities($text)
    {
        return (htmlentities($text, ENT_QUOTES, 'utf-8'));
    }

    public static function dump(&$variable)
    {
        print('<pre>');
        var_dump($variable);
        print('</pre>');
    }

    public static function debugStringBacktrace()
    {
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();
        return $trace;
    }

    public static function fatalError($message, $return_404_header = false)
    {
        if ($return_404_header && (!headers_sent())) {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        }
        die('<p><strong>' . $message . '</strong><br><br><pre>' . self::debugStringBacktrace() . '</pre></p>');
    }

    public static function debugError($message)
    {
        $config = Config::get();
        if (isset($config['haltOnDebugError'])) {
            if ($config['haltOnDebugError']) {
                die('<p><strong>' . $message . '</strong><br><br><pre>' . self::debugStringBacktrace() . '</pre></p>');
            }
        } else {
            // Hier k�nnte man noch �ber eine Log-Datei nachdenken, in der die Meldung dann landet...
        }
    }

    public static function redirect($url, $response_code = false)
    {
        if ($response_code !== false) {
            header('Location: ' . $url, true, $response_code);
        } else {
            header('Location: ' . $url);
        }
        exit();
    }

    public static function getCompleteUrl($uri, $protocol = null)
    {
        if ($protocol === null) {
            $protocol = Config::get()->standardProtocolForAbsoluteUrls;
        }
        $url = $uri;
        // Nur wenn Protokoll nicht angegeben ist...
        if (UTF8String::strpos($url, '://') === false) {
            // ggf. Server-Namen erg�nzen
            if (UTF8String::substr($url, 0, 1) == '/') {
                if ($_SERVER['HTTP_HOST'] !== '') {
                    $url = self::htmlEntities($_SERVER['HTTP_HOST']) . $url;
                } else {
                    $url = self::htmlEntities($_SERVER['SERVER_NAME']) . $url;
                }
            }
            // Protokoll erg�nzen
            $url = $protocol . $url;
        }
        return ($url);
    }

    public static function mergeRecursive()
    {
        if (func_num_args() < 2) {
            return;
        }
        $arrays = func_get_args();
        $merged = array();
        while ($arrays) {
            $array = array_shift($arrays);
            if (!is_array($array)) {
                return;
            }
            if (!$array) {
                continue;
            }
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                        $merged[$key] = self::mergeRecursive($merged[$key], $value);
                    } else {
                        $merged[$key] = $value;
                    }
                } else {
                    $merged[] = $value;
                }
            }
        }
        return $merged;
    }

    public static function isTrue($val)
    {
        $boolval = is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool)$val;
        return $boolval === null ? false : $boolval;
    }
}
