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

class FrontendModule implements FrontendModuleInterface
{
    protected $config = array();

    /**
     * @var Template
     */
    protected $template;

    public function init($config)
    {
        $this->config = Helpers::mergeRecursive($this->config, $config);
    }

    protected function getIntParam(&$params, $index, $std_value = 0)
    {
        $value = null;
        if (isset($params[$index])) {
            if (is_numeric($params[$index])) {
                $value = (int)$params[$index];
            }
        }
        if ($value === null) {
            $value = $std_value;
        }
        return ($value);
    }

    protected function getStringParam(&$params, $index, $std_value = '')
    {
        $value = null;
        if (isset($params[$index])) {
            $value = strval($params[$index]);
        }
        if ($value === null) {
            $value = $std_value;
        }
        return ($value);

    }

    protected function getBoolParam(&$params, $index, $std_value = false)
    {
        $value = null;
        if (isset($params[$index])) {
            if (is_bool($params[$index])) {
                $value = $params[$index];
            } else {
                if (is_numeric($params[$index])) {
                    $value = (int)$params[$index];
                    $value = ($value != 0);
                } else {
                    if ($value === 'true') {
                        $value = true;
                    } else {
                        $value = false;
                    }
                }
            }
        }
        if ($value === null) {
            $value = $std_value;
        }
        return ($value);
    }

    public function output($params, $smarty)
    {
        return ('');
    }

    /**
     * Gibt eine Smarty-Instanz zum rendern von Modul-Templates zur�ck
     *
     * @author Steffen Riedel <riedel@pixelproduction.de>
     * @return Template
     */
    public function getTemplate()
    {
        if (null !== $this->template) {
            return $this->template;
        }

        $template = new Template();

        // override template dir
        $template->setTemplateDir(array(
            'modules' => APPLICATION_ROOT . 'system/custom/frontend/templates/modules/',
        ));

        return $this->template = $template;
    }

    /**
     * Rendert ein Modul-Template
     *
     * @author Steffen Riedel <riedel@pixelproduction.de>
     *
     * @param UTF8String $templateName Der Name des zu rendernden Templates
     * @param array      $data         [optional] Assoziatives Array mit Template-Variablen
     *
     * @return UTF8String
     * @throws Exception
     * @throws SmartyException
     */
    public function renderTemplate($templateName, $data = array())
    {
        $template = $this->getTemplate();
        $template->assign($data);
        return $template->fetch('file:[modules]' . $templateName . '.tpl');
    }
}
