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

?><!doctype html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo Translate::get('Page content'); ?></title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">
    <?php echo $var->additionalStylesheets; ?>

    <style>
        <?php

            function hex2rgb($hex) {
                $hex = str_replace("#", "", $hex);
                if(strlen($hex) == 3) {
                    $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
                } else {
                    $r = hexdec(substr($hex,0,2));
                    $g = hexdec(substr($hex,2,2));
                    $b = hexdec(substr($hex,4,2));
                }
                $rgb = array($r, $g, $b);
                return $rgb;
            }

            function getColorForGradient($color, $opacity) {
                $color = trim($color);
                if (substr($color, 0, 1) == '#') {
                    $rgb = hex2rgb($color);
                    $color = 'rgba(' . implode(',', $rgb) . ',' . number_format($opacity, 2, '.', '') . ')';
                }
                return($color);
            }

            if (isset($var->pageDataStructure)) {
                foreach($var->pageDataStructure as $blockId => $blockStructure) {
                    if (isset($blockStructure['colors']['blockHeaderBackground'])) {
                        print(
                            '#' . $blockId . '-header.pagecontent-block-header {' .
                                'background: ' . $blockStructure['colors']['blockHeaderBackground'] . ';' .
                                'background: linear-gradient(to bottom,  ' . getColorForGradient($blockStructure['colors']['blockHeaderBackground'], 0.7) . ' 0%, ' . getColorForGradient($blockStructure['colors']['blockHeaderBackground'], 1) . ' 100%);' .
                            '}' .
                            "\n"
                        );
                    }
                    if (isset($blockStructure['colors']['blockHeaderText'])) {
                        print(
                            '#' . $blockId . '-header.pagecontent-block-header {' .
                                'color: ' . $blockStructure['colors']['blockHeaderText'] . ';' .
                            '}'.
                            "\n"
                        );
                    }
                    if (isset($blockStructure['colors']['elementHeaderBackground'])) {
                        print(
                            '#' . $blockId . '-container .pagecontent-container-element-header {' .
                                'background: ' . $blockStructure['colors']['elementHeaderBackground'] . ';' .
                                'background: linear-gradient(to bottom,  ' . getColorForGradient($blockStructure['colors']['elementHeaderBackground'], 0.7) . ' 0%, ' . getColorForGradient($blockStructure['colors']['elementHeaderBackground'], 1) . ' 100%);' .
                            '}'.
                            "\n"
                        );
                    }
                    if (isset($blockStructure['colors']['elementHeaderText'])) {
                        print(
                            '#' . $blockId . '-container .pagecontent-container-element-header {' .
                                'color: ' . $blockStructure['colors']['elementHeaderText'] . ';' .
                            '}'.
                            "\n"
                        );
                    }
                }
            }
        ?>
    </style>

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="pagecontent" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>
<div class="pixelmanager-iframe-wrapper">
    <div class="pixelmanager-iframe-content">
        <div class="pixelmanager-error-container"></div>
        <input type="hidden" name="pageId" id="pageId" value="<?php echo $var->pageId; ?>">

        <div id="content"></div>
    </div>
    <div class="pixelmanager-iframe-buttons">
        <div class="btn-toolbar">
            <div class="btn-group pull-left">
                <button class="btn btn-default btn-sm show-tooltip-btn-group" id="btn_action_outline"
                        title="<?php echo Translate::get('Outline'); ?>"><span class="glyphicon glyphicon-tasks"></span>
                </button>
                <button class="btn btn-default btn-sm show-tooltip-btn-group" id="btn_action_expand_all"
                        title="<?php echo Translate::get('Expand all'); ?>"><span
                        class="glyphicon glyphicon-chevron-down"></span></button>
                <button class="btn btn-default btn-sm show-tooltip-btn-group" id="btn_action_collapse_all"
                        title="<?php echo Translate::get('Collapse all'); ?>"><span
                        class="glyphicon glyphicon-chevron-up"></span></button>
            </div>
            <?php if (!$var->isGlobalElementsPage) { ?>
                <button id="btn_preview" class="btn btn-sm btn-inverse" disabled><span
                        class="glyphicon glyphicon-eye-open icon-white"></span> <?php echo Translate::get('Save and preview'); ?>
                </button>
            <?php } ?>
            <button id="btn_save" class="btn btn-sm btn-success" disabled><span
                    class="glyphicon glyphicon-ok icon-white"></span> <?php echo Translate::get('Save and close'); ?>
            </button>
            <button id="btn_close" class="btn btn-sm btn-danger"><span
                    class="glyphicon glyphicon-remove icon-white"></span> <?php echo Translate::get('_BTN_CANCEL_'); ?>
            </button>
        </div>
    </div>
</div>
</body>
</html>
