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

final class StyleSheetTokenizer extends ArrayIterator
{
    private $tokens = array();
    private $tokenCount = 0;

    private $holdIdent = array();
    private $holdStyle = array();
    //Unicode daten
    private $unicodeStream = array();

    private $dataLen = 0;
    private $offset = 0;

    public function __construct()
    {
    }

    /* Garbage collector */
    private function gc()
    {
        $this->unicodeStream = null;
    }

    /*
    *	Daten auspacken und in
    */
    public function tokenize(&$fileStream)
    {
        //echo $fileStream;

        $binData = unpack("C*", $fileStream . "\n");

        //Concert to Unicode Data
        $this->unicodeData($binData);
        //Reset the offset pointer
        $this->resetStream();
        //w3c preprocessing of unicode CSS streams
        $this->preprocessingStream();
        //Reset the offset pointer
        $this->resetStream();
        //Starting the Tokenloop
        $this->tokenLoop();
    }

    private function preprocessingStream()
    {
        while ($this->offset < $this->dataLen) {
            /*
            *	look for carriage return and form feed
            */
            if (
                ($this->unicodeStream[$this->offset] == 0x0D) ||
                ($this->unicodeStream[$this->offset] == 0x0C)
            ) {
                /*
                *	if the next char is a linefeed then unset the carriage return
                *	otherwise change the char to a linefeed
                */
                if ($this->unicodeStream[$this->offset + 1] == 0x0A) {
                    unset($this->unicodeStream[$this->offset]);
                } else {
                    $this->unicodeStream[$this->offset] = 0x0A;
                }
                //Nullbyte to Unicode replacement char
            } elseif ($this->unicodeStream[$this->offset] == 0x00) {
                $this->unicodeStream[$this->offset] = 0xEFBFBD;
            }
            $this->offset++;
        }
        $this->unicodeStream = array_values($this->unicodeStream);
    }

    //Unicode convertierung
    private function unicodeData(&$dataStream)
    {

        $streamLen = count($dataStream);
        $offset = 1;
        $cleanCount = 0;
        while ($offset < $streamLen) {
            //if the char is outside of the ascii table then turn it to a unicode char
            if ($dataStream[$offset] > 0x7F) {
                $word = hexdec(dechex($dataStream[$offset]) . dechex($dataStream[$offset + 1]));
                $this->unicodeStream[$cleanCount] = $word;
                $offset++;
            } else {
                $this->unicodeStream[$cleanCount] = $dataStream[$offset];
            }
            $offset++;
            $cleanCount++;
        }
    }

    /*
    *	sets the offset pointer to 0
    */
    private function resetStream()
    {
        $this->offset = 0;
        $this->dataLen = count($this->unicodeStream);
    }

    /*
    *	start the toke loop to tokenize the css stream
    */
    private function tokenLoop()
    {
        if ($this->dataLen > 0) {
            while ($this->offset < $this->dataLen) {

                if ($this->isWhitespace()) {
                    $this->whitespaceToken();
                }
                if ($this->isComment()) {
                    $this->commentToken();
                }
                if ($this->isUnicode()) {
                    do {
                        $this->holdIdent[] = trim($this->identToken());
                    } while ($this->isComma(true));
                    if ($this->isOpenBrace(true)) {
                        do {
                            $holdString = $this->identToken();
                            if ($this->isSemicolon()) {
                                $this->offset++;
                            }
                            $this->holdStyle[] = trim($holdString);
                        } while (!$this->isCloseBrace(true));
                    }
                    $this->flushData();
                }
                /*Unendlichen loop verhindern */
                if ($this->preventError()) {

                    Helpers::fatalError("StyleSheetTokenizer Error");
                }
            }
        }
        $this->gc();
    }

    private function flushData()
    {
        $this->tokens[$this->tokenCount]["node"] = $this->holdIdent;
        $this->tokens[$this->tokenCount]["style"] = $this->holdStyle;
        $this->holdIdent = array();
        $this->holdStyle = array();
        $this->tokenCount++;
    }

    private function isComment()
    {
        if ($this->offset + 1 < $this->dataLen) {
            if (
                ($this->unicodeStream[$this->offset] == 0x2F) &&
                ($this->unicodeStream[$this->offset + 1] == 0x2A)
            ) {
                return true;
            }
        }
        return false;

    }

    private function commentToken()
    {
        do {
            $this->offset++;
        } while (
        !(($this->unicodeStream[$this->offset] == 0x2A) &&
            ($this->unicodeStream[$this->offset + 1] == 0x2F))
        );
        $this->offset = $this->offset + 2;
    }

    private function fetchToken()
    {
        return chr($this->binData[$this->offset]);
    }

    /*private function findAtToken()
    {
        if($this->binData[$this->offset] == 0x40)
        {
            do {
                $this->offset++;
            } while(!(($this->binData[$this->offset] == 0x3B) || ($this->binData[$this->offset]== 0x7B)));
            $this->offset++;
        }
    }*/
    private function isUnicode()
    {
        if (
            ($this->unicodeStream[$this->offset] == 0x23) ||
            ($this->unicodeStream[$this->offset] == 0x2E) ||
            (($this->unicodeStream[$this->offset] >= 0x30) && ($this->unicodeStream[$this->offset] <= 0x39)) ||
            (($this->unicodeStream[$this->offset] >= 0x41) && ($this->unicodeStream[$this->offset] <= 0x5A)) ||
            (($this->unicodeStream[$this->offset] >= 0x61) && ($this->unicodeStream[$this->offset] <= 0x7A)) ||
            ($this->unicodeStream[$this->offset] == 0x2D) ||
            ($this->unicodeStream[$this->offset] == 0x5F) ||
            ($this->unicodeStream[$this->offset] == 0x2A) ||
            ($this->unicodeStream[$this->offset] > 0x7F)
        ) {
            return true;
        }
        return false;
    }

    private function isPercent()
    {
        if ($this->unicodeStream[$this->offset] == 0x25) {
            return true;
        }
        return false;
    }

    private function isSlash()
    {
        if ($this->unicodeStream[$this->offset] == 0x2F) {
            return true;
        }
        return false;
    }

    private function isOpenBrace($iterate = false)
    {
        if ($this->unicodeStream[$this->offset] == 0x7B) {
            if ($iterate) {
                $this->offset++;
            }
            return true;
        }
        return false;
    }

    private function isRoundBrace()
    {
        if (
            ($this->unicodeStream[$this->offset] == 0x28) ||
            ($this->unicodeStream[$this->offset] == 0x29)
        ) {
            return true;
        }
        return false;
    }

    private function isCloseBrace($iterate = false)
    {
        if ($this->unicodeStream[$this->offset] == 0x7D) {
            if ($iterate) {
                $this->offset++;
            }
            return true;
        }
        return false;
    }

    private function identToken()
    {
        $string = "";
        while (
            $this->isUnicode() ||
            $this->isWhitespace() ||
            $this->isColon() ||
            $this->isRoundBrace() ||
            $this->isQuote() ||
            $this->isSlash() ||
            $this->isPercent()
        ) {
            $string .= chr($this->unicodeStream[$this->offset]);
            $this->offset++;
        }
        return $string;
    }

    private function isSemicolon()
    {
        if ($this->unicodeStream[$this->offset] == 0x3B) {
            return true;
        }
        return false;
    }

    private function isQuote()
    {
        if (
            ($this->unicodeStream[$this->offset] == 0x22) ||
            ($this->unicodeStream[$this->offset] == 0x27)
        ) {
            return true;
        }
        return false;
    }

    private function isComma($iterate = false)
    {
        if ($this->unicodeStream[$this->offset] == 0x2C) {
            if ($iterate) {
                $this->offset++;
            }
            return true;
        }
        return false;
    }

    private function isWhitespace()
    {
        //TODO:newline prüfen
        if (
            ($this->unicodeStream[$this->offset] == 0x0A) ||
            ($this->unicodeStream[$this->offset] == 0x20) ||
            ($this->unicodeStream[$this->offset] == 0x0D) ||
            ($this->unicodeStream[$this->offset] == 0x09)
        ) {
            return true;
        }
        return false;
    }

    /*
    *	Loop until the upcoming char isn't a whitespace anymore
    */
    private function whitespaceToken()
    {
        while ($this->isWhitespace()) {
            $this->offset++;
        }
    }

    private function isColon()
    {
        if ($this->unicodeStream[$this->offset] == 0x3A) {
            return true;
        }
        return false;
    }

    private function preventError()
    {
        if ($this->offset < $this->dataLen) {
            if (
                ($this->isUnicode()) ||
                ($this->isPercent()) ||
                ($this->isSlash()) ||
                ($this->isOpenBrace()) ||
                ($this->isCloseBrace()) ||
                ($this->isRoundBrace()) ||
                ($this->isSemicolon()) ||
                ($this->isQuote()) ||
                ($this->isWhitespace()) ||
                ($this->isComma()) ||
                ($this->isComment())
            ) {
                return false;
            }
            return true;
        }
    }
    /*
    *	überladenen metoden
    */
    /* Overloading ArrayIterator */
    public function count()
    {
        return count($this->tokens);
    }

    /* Overloading ArrayIterator */
    public function next()
    {
        next($this->tokens);
    }

    /* Overloading ArrayIterator */
    public function current()
    {
        return current($this->tokens);
    }

    /* Overloading ArrayIterator */
    public function valid()
    {
        return (current($this->tokens) !== false);
    }

    /* Overloading ArrayIterator */
    public function rewind()
    {
        reset($this->tokens);
    }

    /* Overloading ArrayIterator */
    public function key()
    {
        return key($this->tokens);
    }

    /* Overloading ArrayAccess */
    public function offsetSet($offset, $value)
    {
        if ($offset == "") {
            $this->tokens[] = $value;
        } else {
            $this->tokens[$offset] = $value;
        }
    }

    /* Overloading ArrayAccess */
    public function offsetGet($offset)
    {
        return isset($this->tokens[$offset]) ? $this->tokens[$offset] : null;
    }

    /* Overloading ArrayAccess */
    public function offsetUnset($offset)
    {
        unset($this->tokens[$offset]);
    }

    /* Overloading ArrayAccess */
    public function offsetExists($offset)
    {
        return isset($this->tokens[$offset]);
    }

    /*
    *	Ja das ist mein ernst
    */
    public function appendTo($data, $to)
    {
        $hold = null;
        foreach ($to as $path) {
            if ($hold == null) {
                $hold = &$this->tokens[$path];
            } else {
                $hold = &$hold[$path];
            }
        }
        $hold[] = $data;
    }

    public function replaceAt($data, $to)
    {
        $hold = null;
        foreach ($to as $path) {
            if ($hold == null) {
                $hold = &$this->tokens[$path];
            } else {
                $hold = &$hold[$path];
            }
        }
        $hold = $data;
    }

    public function append($data)
    {
        $this->tokens[] = $data;
    }
}
