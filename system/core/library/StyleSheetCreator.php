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

class StyleSheetCreator
{
    private $tokenizer = null;

    public function __construct(StyleSheetTokenizer $tk)
    {
        $this->tokenizer = $tk;
    }
    /*
    *	Creates the String for the stylesheet
    */
    /*
    *	Create Style V2
    */
    public function createStyle(&$styles)
    {
        $globalStyle = Config::get()->globalStyle;
        $lang_id = Config::get()->languages->standard;
        if ($globalStyle->use && (count($styles) > 0)) {
            $fileStream = FileUtils::readFile($globalStyle->path);
            $this->tokenizer->tokenize($fileStream);
            foreach ($styles as $styleKey => $style) {
                if ($style[$lang_id]["color"] != "") {

                    if (($tokenKey = $this->nodeHeadExists($style[$lang_id]["node"])) !== false) {
                        if ($this->nodeStyleEqual($style[$lang_id]["style"], $tokenKey)) {
                            //Werte überprüfen
                            $this->newStyleValues($style[$lang_id]["color"], $tokenKey);
                        } else {
                            //Werte überprüfen und neuen hinzufügen
                            $this->addNewStyles($style[$lang_id], $tokenKey);
                        }

                    } else {
                        //find singleNodes or nodes that are nearly the same
                        $this->tokenizer->append($this->createFullNode($style[$lang_id]));
                    }
                }
            }
            FileUtils::writeToFile($globalStyle->path, $this->createStyleSheet());
        }
    }

    /*
    *	creates the file with the stylesheet string
    */
    private function createStyleSheet()
    {
        $styleSheet = "";
        foreach ($this->tokenizer as $key => $token) {
            $length = count($token["node"]) - 1;
            foreach ($token["node"] as $k => $node) {
                $styleSheet .= $node;
                if ($k != $length) {
                    $styleSheet .= ",";
                }
            }
            $styleSheet .= "{";
            foreach ($token["style"] as $style) {
                $styleSheet .= $style . ";";
            }
            $styleSheet .= "}";
        }
        return $styleSheet;
    }

    /*
    *	Version 2
    */
    private function nodeHeadExists($find)
    {
        foreach ($this->tokenizer as $nodeKey => $node) {
            if ($this->nodeHeadEqual($find, $node["node"])) {
                return $nodeKey;
            }
        }
        return false;
    }

    private function nodeHeadEqual($word, $compare, $counter = 0)
    {
        if (count(array_diff($word, $compare)) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /*
    */
    private function nodeStyleEqual($find, $key)
    {
        $hold = array();
        foreach ($this->tokenizer[$key]["style"] as $node) {
            $nodeString = new UTF8String($node);
            $name = $nodeString->split(":");
            foreach ($find as $f) {
                if ($f == $name[0]) {
                    $hold[] = $name[0];
                }
            }
        }
        if (count($hold) == count($this->tokenizer[$key]["style"])) {
            if (count(array_diff($hold, $find)) > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function newStyleValues($value, $key)
    {
        foreach ($this->tokenizer[$key]["style"] as $valKey => $node) {
            $nodeString = new UTF8String($node);
            $nodeVal = $nodeString->split(":");
            $this->tokenizer->replaceAt($nodeVal[0] . ":" . $value, array($key, "style", $valKey));
        }
    }

    private function addNewStyles(&$values, $key)
    {
        $hold = array();
        foreach ($this->tokenizer[$key]["style"] as $valKey => $node) {
            $nodeString = new UTF8String($node);
            $nodeVal = $nodeString->split(":");
            foreach ($values["style"] as $f) {
                if ($f == $nodeVal[0]) {
                    $this->tokenizer->replaceAt($nodeVal[0] . ":" . $values["color"], array($key, "style", $valKey));
                    $hold[] = $nodeVal[0];
                }
            }
        }

        if (count($arrDiff = array_diff($values["style"], $hold)) > 0) {
            foreach ($arrDiff as $newValue) {
                $this->tokenizer->appendTo($newValue . ":" . $values["color"], array($key, "style"));
            }
        }

    }

    private function createFullNode(&$data)
    {
        $nodeArray = array();
        foreach ($data["node"] as $n) {
            $nodeArray[] = $n;
        }
        $styleNode = array();
        foreach ($data["style"] as $s) {
            $styleNode[] = $s . ":" . $data["color"];
        }
        $node = array("node" => $nodeArray, "style" => $styleNode);
        return $node;
    }
}
