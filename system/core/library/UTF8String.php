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
 * Author: Daniel Obieglo
 *   Date: 04.02.2015
 *
 * String class to make working with strings easier
 *
 * Update von Jan Sträter am 23.06.2015
 * - das Encoding ist nun festgelegt auf UTF-8
 * - Erweitert um Wrapper-Funktionen der wichtigsten PHP String Funktionen, die sofern verfügbar, die Multibyte-Version
 *   aufrufen. Diese Wrapper-Funktionen sind als PUIBLIC STATIC definiert, damit sie von überall aufgerufen
 *   werden können, ohne die Klasse instanziieren zu müssen. Ohne Instanz keine Member-Variablen, daher ist das Encoding nun der
 *   Einfachheit halber festgelegt. Das übrige System verwendet sowieso überall UTF-8.
 *
 */

class UTF8String
{
    private $orgString;
    private $iLength = 0;
    private $tokenCount = 1;
    private static $multibyte_functions_available = null;

    /**
     * Konstruktor initalisiert das Objekt mit dem string
     *
     * @param UTF8String $string
     */
    public function __construct($string)
    {
        $this->orgString = $string;
        $this->calcLength();
    }

    /**
     * Depreciated: Aus Kompatiblitätsgründen noch vorhanden, wird aber ignoriert, Encoding ist nun immer UTF-8
     *
     * @param UTF8String $enc
     */
    public function encoding($enc)
    {
        // $this->sEncoding = $enc;
    }

    /**
     * Checkt, ob die Multibyte-Bibliothek verfügbar ist und merkt es in $this->$multibyte_functions_available
     *
     * @return boolean
     */
    public static function isMultibyteAvailable()
    {
        if (static::$multibyte_functions_available === null) {
            if (function_exists('mb_strlen')) {
                static::$multibyte_functions_available = true;
            } else {
                static::$multibyte_functions_available = false;
            }
        }
        return (static::$multibyte_functions_available);
    }

    //! Magic method for the output
    public function __toString()
    {
        return $this->orgString;
    }

    /**
     * Gleich wie __toString()
     *
     * @see __toString()
     * @return UTF8String
     */
    public function toString()
    {
        return $this->orgString;
    }

    /**
     * call append with an method that changes the string like this:
     * $string->append((new String(" myString"))->toLower());
     *
     * @param UTF8String $string
     */
    public function append($string)
    {
        $this->orgString .= $string;
        $this->calcLength();
    }

    /**
     * adds a string on the beginning of the string
     *
     * @param UTF8String $string
     *
     * @return &String
     */
    public function prepend($string)
    {
        $newString = $string;
        $newString .= $this->orgString;
        $this->orgString = $newString;
        $this->calcLength();
        return $this;
    }

    /**
     * replaces a token in the string
     * $string = new String("Das %1 ein Test %2");
     * $string->arg("ist");
     * $string->arg((new String("GEIL"))->toLower());
     *
     * @param UTF8String $string
     *
     * @return &String
     */
    public function arg($string)
    {
        $foundCount = 0;
        $this->orgString = str_replace("%" . $this->tokenCount, $string, $this->orgString, $foundCount);
        if ($foundCount > 0) {
            $this->tokenCount++;
        }
        $this->calcLength();
        return $this;
    }

    /**
     * @param int $n
     *
     * @return &String
     */
    public function chop($n)
    {
        if ($n >= $this->iLength) {
            $this->orgString = "";
            return $this;
        }
        $this->orgString = static::substr($this->orgString, 0, $n * -1);
        $this->calcLength();
        return $this;
    }

    /**
     * @return &String
     */
    public function clear()
    {
        $this->orgString = "";
        $this->tokenCount = 1;
        $this->sEncoding = "UTF-8";
        $this->calcLength();
        return $this;
    }

    /**
     * compares the string
     *
     * @param UTF8String $string
     *
     * @return int
     */
    public function compare($string)
    {
        // Fieser Hack mit dem utf8_decode, aber weder strcoll, noch strcmp
        // kommen mit UTF-8 zurecht, und es existiert auch keine Multibyte-Version.
        // strcoll hat wenigstens noch den Vorteil, dass länderspezifische
        // Sortierregeln beachtet werden (sofern setlocale richtig angwendet wurde)
        return strcoll(utf8_decode($this->orgString), utf8_decode($string));
    }

    /**
     * contains
     *
     * @param UTF8String $string
     *
     * @return bool
     */
    public function contains($string)
    {
        if (static::strpos($this->orgString, $string) === false) {
            return false;
        }
        return true;
    }

    /**
     * fills the string with a char
     *
     * @param char $char
     * @param int  $n
     *
     * @return &String
     */
    public function fill($char, $n = -1)
    {
        if (static::strlen($char) == 1) {
            if (!($n > 0)) {
                for ($i = 0; $i < $this->iLength; ++$i) {
                    $this->orgString[$i] = $char;
                }
            } elseif ($n <= $this->iLength) {
                for ($i = 0; $i < $n; ++$i) {
                    $this->orgString[$i] = $char;
                }
            }
        }
        return $this;
    }

    /**
     * inserts a string on a specific position
     *
     * @param int        $pos
     * @param UTF8String $string
     *
     * @return &String
     */
    public function insert($pos, $string)
    {
        if (($pos >= 0) && ($pos <= $this->iLength)) {
            $new_string = "";
            for ($i = 0; $i < $this->iLength; ++$i) {
                if ($i == $pos) {
                    $new_string .= $string;
                }
                $new_string .= $this->orgString[$i];
            }
            $this->orgString = $new_string;
            $this->calcLength();
        }
        return $this;
    }

    /**
     * removes chars from the string
     *
     * @param int $pos
     * @param int $len
     *
     * @return &String
     */
    public function remove($pos, $len)
    {
        if ($pos >= 0 && $len <= $this->iLength) {
            $new_string = "";
            for ($i = 0; $i < $this->iLength; ++$i) {
                if (!($i >= $pos && $i < $pos + $len)) {
                    $new_string .= $this->orgString[$i];
                }
            }
            $this->orgString = $new_string;
            $this->calcLength();
        }
        return $this;
    }

    /**
     * @param UTF8String $string
     *
     * @return &String
     */
    public function removeString($string)
    {
        if ($this->contains($string)) {
            $pos = $this->indexOf($string);
            $this->remove($pos, static::strlen($string));
        }
        return $this;
    }

    /**
     * @param UTF8String $string
     *
     * @return int
     */
    public function indexOf($string)
    {
        return static::strpos($this->orgString, $string);
    }

    /**
     * splits a string
     *
     * @param UTF8String $char
     *
     * @return array
     */
    public function split($char)
    {
        return explode($char, $this->orgString);
    }

    /**
     * swaps a this string with another
     *
     * @param UTF8String $string
     */
    public function swap($string)
    {
        $this->clear();
        $this->__construct($string);
    }

    /**
     * cuts a string to the length of $n
     *
     * @param int $n
     *
     * @return &String
     */
    public function truncate($n)
    {
        if (is_numeric($n) && $n > 0) {
            $this->chop($this->iLength - $n);
        }
        return $this;
    }

    /**
     * Checks if the String is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        if ($this->iLength) {
            return false;
        }
        return true;
    }

    /**
     * @return int [length of String]
     */
    public function length()
    {
        return $this->iLength;
    }

    /**
     * replaces every char with its lower version
     *
     * @return &String
     */
    public function toLower()
    {
        $this->orgString = static::strtolower($this->orgString);
        return $this;
    }

    /**
     * replaces every char with its upper version
     *
     * @return &String
     */
    public function toUpper()
    {
        $this->orgString = static::strtoupper($this->orgString);
        return $this;
    }

    /**
     * private
     */
    private function calcLength()
    {
        $this->iLength = static::strlen($this->orgString);
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function strlen($string)
    {
        if (static::isMultibyteAvailable()) {
            return (mb_strlen($string, 'UTF-8'));
        } else {
            return (strlen(utf8_decode($string)));
        }
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function strpos($haystack, $needle, $offset = 0)
    {
        if (static::isMultibyteAvailable()) {
            return (mb_strpos($haystack, $needle, $offset, 'UTF-8'));
        } else {
            return (strpos(utf8_decode($haystack), utf8_decode($needle), $offset));
        }
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function strrpos($haystack, $needle, $offset = 0)
    {
        if (static::isMultibyteAvailable()) {
            return (mb_strrpos($haystack, $needle, $offset, 'UTF-8'));
        } else {
            return (strrpos(utf8_decode($haystack), utf8_decode($needle), $offset));
        }
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function substr($string, $start, $length = null)
    {
        if (static::isMultibyteAvailable()) {
            if ($length === null) {
                // Bugfix für PHP Versionen früher als 5.4.8:
                // Da würde $length = null als $length = 0 interpretiert werden,
                // was anders ist als bei der standard substr(),
                // die einfach die Länge bis zum Ende des Strings annimmt, wenn $length = null
                $length = static::strlen($string);
            }
            return (mb_substr($string, $start, $length, 'UTF-8'));
        } else {
            return (utf8_encode(substr(utf8_decode($string), $start, $length)));
        }
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function strtolower($string)
    {
        if (static::isMultibyteAvailable()) {
            return (mb_strtolower($string, 'UTF-8'));
        } else {
            return (utf8_encode(strtolower(utf8_decode($string))));
        }
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function strtoupper($string)
    {
        if (static::isMultibyteAvailable()) {
            return (mb_strtoupper($string, 'UTF-8'));
        } else {
            return (utf8_encode(strtoupper(utf8_decode($string))));
        }
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function stripos($haystack, $needle, $offset = 0)
    {
        if (static::isMultibyteAvailable()) {
            return (mb_stripos($haystack, $needle, $offset, 'UTF-8'));
        } else {
            return (stripos(utf8_decode($haystack), utf8_decode($needle), $offset));
        }
    }

    /**
     * Wrapper für die gleichnamige PHP String Funktion, ruft die Multibyte-Version auf, sofern verfügbar
     */
    public static function strripos($haystack, $needle, $offset = 0)
    {
        if (static::isMultibyteAvailable()) {
            return (mb_strripos($haystack, $needle, $offset, 'UTF-8'));
        } else {
            return (strripos(utf8_decode($haystack), utf8_decode($needle), $offset));
        }
    }

}
