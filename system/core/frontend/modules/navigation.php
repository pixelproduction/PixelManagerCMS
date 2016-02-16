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

class NavigationFrontendModule extends FrontendModule
{

    protected $pages = null;

    public function __construct()
    {
        $this->config = array(
            'classActive' => 'active',
            'classTrail' => 'trail',
            'classSubpages' => 'subpages',
            'classFirst' => 'first',
            'classLast' => 'last'
        );
        $this->pages = new Pages();
    }

    public function output($params, $smarty)
    {
        $output = '';
        $level = $this->getIntParam($params, 'level', 0);
        $depth = $this->getIntParam($params, 'depth', 0);
        $page_id = $this->getIntParam($params, 'page', 0);
        $language_id = $this->getStringParam($params, 'language', Config::get()->languages->standard);
        $only_active = $this->getBoolParam($params, 'onlyactive', true);
        $show_parent_in_submenu = $this->getBoolParam($params, 'showparents', false);
        $root_unique_id = $this->getStringParam($params, 'root-unique-id', null);
        $root_page_id = $this->getIntParam($params, 'root-page-id', null);

        $path = $this->pages->getPath($page_id);
        if ($path === false) {
            return ('');
        }
        $path[] = $page_id;

        if (($root_page_id !== null) || ($root_unique_id !== null)) {

            if ($root_unique_id !== null) {
                $parent_id = $this->pages->getPageIdByUniqueId($root_unique_id);
                if ($parent_id === false) {
                    return ('');
                }
            } else {
                $parent_id = $root_page_id;
                $test_parent_element = $this->pages->getProperties($parent_id);
                if ($test_parent_element === false) {
                    return ('');
                }
            }

        } else {

            if (count($path) < $level) {
                return ('');
            }
            $parent_id = Pages::ROOT_ID;
            if ($level > 0) {
                $parent_id = $path[$level - 1];
            }

        }

        $output = $this->getNavigation($parent_id, $path, $language_id, $depth, $only_active, $show_parent_in_submenu);

        return ($output);
    }

    protected function getNavigation($parent_id, $path, $language_id, $depth, $only_active, $show_parent_in_submenu)
    {
        $output = '';
        $elements = $this->pages->getChildren($parent_id, false);
        if ($elements !== false) {
            if ($show_parent_in_submenu) {
                if ($parent_id != Pages::ROOT_ID) {
                    $parent_properties = $this->pages->getProperties($parent_id);
                    if ($parent_properties !== false) {
                        $parent_properties['do_not_traverse'] = true;
                        array_unshift($elements, $parent_properties);
                    }
                }
            }
            if (count($elements) > 0) {
                $counter = 0;
                foreach ($elements as $element) {
                    if ($this->pages->showPageInNavigation($element['id'], $language_id, $element)) {

                        // Unterseiten
                        $show_subpages = true;
                        if (isset($element['do_not_traverse'])) {
                            if ($element['do_not_traverse']) {
                                $show_subpages = false;
                            }
                        }
                        if ($only_active) {
                            if (!in_array($element['id'], $path)) {
                                $show_subpages = false;
                            }
                        }
                        $subpages = '';
                        if ($show_subpages) {
                            if ($depth != 0) {
                                $new_depth = $depth - 1;
                            } else {
                                $new_depth = 0;
                            }
                            if (($depth > 1) || ($depth == 0)) {
                                $subpages = $this->getNavigation($element['id'], $path, $language_id, $new_depth,
                                    $only_active, $show_parent_in_submenu);
                            }
                        }

                        // CSS-Klassen
                        $classes = array();
                        if ($subpages != '') {
                            $classes[] = $this->config['classSubpages'];
                        }
                        if ($element['id'] == $path[count($path) - 1]) {
                            $classes[] = $this->config['classActive'];
                        } else {
                            if (in_array($element['id'], $path)) {
                                $classes[] = $this->config['classTrail'];
                            }
                        }
                        if ($counter == 0) {
                            $classes[] = $this->config['classFirst'];
                        }
                        if ($counter >= (count($elements) - 1)) {
                            $classes[] = $this->config['classLast'];
                        }
                        if (count($classes) > 0) {
                            $classes = ' class="' . implode(' ', $classes) . '"';
                        } else {
                            $classes = '';
                        }


                        // Link
                        if ($this->pages->isPageLink($element['id'], $element)) {
                            if ($this->pages->isPageLinkTranslated($element['id'], $element)) {
                                $href = $this->pages->getTranslatedPageLinkUrl($element['id'], $language_id);
                            } else {
                                $href = $this->pages->getPageLinkUrl($element['id'], $element);
                            }
                            $target = (($this->pages->getPageLinkNewWindow($element['id'], $element)) ? '_blank' : '');
                        } else {
                            $href = $this->pages->getPageUrl($element['id'], $language_id, $element);
                            $target = '';
                        }

                        // Link-Titel
                        $caption = $this->pages->getAnyCaption($element['id'], $language_id);

                        // Ausgabe
                        $output .= '<li' . $classes . '><a href="' . $href . '"' . (($target != '') ? ' target="' . $target . '"' : '') . '>' . $caption . '</a>' . $subpages . '</li>';

                        $counter++;
                    }
                }
            }
        }
        if ($output != '') {
            $output = '<ul>' . $output . '</ul>';
        }
        return ($output);
    }

}
