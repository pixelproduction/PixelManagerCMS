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
    <title>Bilder</title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>elfinder/css/elfinder.min.css">

    <?php
    $tmb_custom_size = intval(Config::get()->elFinderThumbnailSize);
    $tmb_original_size = 48;

    if ($tmb_custom_size != $tmb_original_size) {
        $tmb_size_diff = $tmb_custom_size - $tmb_original_size;
        ?>
        <style>
            .elfinder-cwd-icon {
                margin-top: <?php echo round($tmb_size_diff / 2); ?>px;
            }

            .elfinder-cwd-icon-image {
                margin-top: 0;
                background-image: url("<?php echo $var->publicUrl; ?>img/elfinder-image-icon.png");
                background-repeat: no-repeat;
                height: <?php echo $tmb_custom_size; ?>px;
                width: <?php echo $tmb_custom_size; ?>px;
            }

            .elfinder-cwd-view-icons .elfinder-cwd-file-wrapper {
                height: <?php echo $tmb_custom_size + 4; ?>px;
                width: <?php echo $tmb_custom_size + 4; ?>px;
            }

            .elfinder-cwd-view-icons .elfinder-cwd-file {
                height: <?php echo 80 + $tmb_size_diff; ?>px;
                width: <?php echo 120 + $tmb_size_diff; ?>px;
            }
        </style>
        <?php
    }
    ?>

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="images" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content pixelmanager-iframe-content-without-buttons">

        <div id="elfinder"></div>

    </div>

</div>

</body>
</html>
