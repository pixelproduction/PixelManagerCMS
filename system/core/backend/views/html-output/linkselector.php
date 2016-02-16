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
    <title>Link einfügen</title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="linkselector" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content pixelmanager-link-selector">

        <div id="page-tree"></div>

    </div>

    <div class="pixelmanager-iframe-buttons pixelmanager-iframe-buttons-left-aligned">
        <div class="btn-toolbar">
            <button id="btn_select_page" class="btn btn-primary btn-sm"><span
                    class="glyphicon glyphicon-share icon-white"></span> <?php echo Translate::get('Select page'); ?>
            </button>
            <button id="btn_expand_all"
                    class="btn btn-default btn-sm"><?php echo Translate::get('Expand all'); ?></button>
            <button id="btn_close_all"
                    class="btn btn-default btn-sm"><?php echo Translate::get('Close all'); ?></button>
				<span class="pixelmanager-link-selector-language">
					<?php echo Translate::get('Target language'); ?>
                    <select id="create-link-to-language-id">
                        <?php
                        foreach (Config::get()->languages->list as $key => $language) {
                            echo '<option value="' . $key . '">' . $language["name"] . '</option>';
                        }
                        ?>
                    </select>
				</span>
        </div>
    </div>

</div>

</body>
</html>
