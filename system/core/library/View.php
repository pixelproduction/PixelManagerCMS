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

class View
{
    protected $template_filename = null;
    protected $variables = array();
    protected $output_deactivated = false;
    protected $mime_type = 'text/html';

    public function getTemplateOutput()
    {
        $var = new RecursiveArrayObject($this->variables);
        $arrayVar = $this->variables;
        if (is_file($this->template_filename)) {
            ob_start();
            include($this->template_filename);
            return (ob_get_clean());
        } else {
            return (false);
        }
    }

    public function output()
    {
        if (!$this->output_deactivated) {
            $this->outputHeader();
            print($this->getTemplateOutput());
        }
    }

    public function outputHeader()
    {
        if (!headers_sent()) {
            // Diese Klasse ist in erster Linie f�rs Backend gedacht und dort wollen wir definitv kein Caching...
            header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
            header("Content-type: " . $this->mime_type . "; utf-8"); // MIME-Type und Encoding senden
        }
    }

    public function assign($var, $value)
    {
        $this->variables[$var] = $value;
    }

    public function getTemplateVar($var)
    {
        if (isset($this->variables[$var])) {
            return ($this->variables[$var]);
        } else {
            return (null);
        }
    }

    public function assignTemplate($filename)
    {
        if (is_file($filename)) {
            $this->template_filename = $filename;
        }
    }

    public function deactivate()
    {
        $this->$output_deactivated = true;
    }

    public function activate()
    {
        $this->$output_deactivated = false;
    }

    public function setMimeType($mime_type)
    {
        $this->mime_type = $mime_type;
    }

    public function getMimeType()
    {
        return ($this->mime_type);
    }
    
}
