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
    <title>Über...</title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="about" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content pixelmanager-iframe-content-without-buttons">

        <h2>Pixelmanager CMS</h2>
        <h3>Community Edition</h3>
        <?php
        $config = Config::getArray();
        $version = $config['version'];
        ?>
        <p>
            <?php echo(Translate::get('Core version') . ': ' . $version['main'] . '.' . $version['sub'] . '.' . $version['features'] . '.' . $version['bugfixes']); ?>
            <br>
            Copyright <?php echo date('Y'); ?> by PixelProduction (<a href="http://www.pixelproduction.de" target="_blank">http://www.pixelproduction.de</a>)
        </p>

        <p>&nbsp;</p>

        <p><img src="<?php echo $var->publicUrl; ?>img/about-pixelproduction-logo.png" alt="PixelProduction"></p>

        <p>&nbsp;</p>

        <h3><?php echo(Translate::get('This software is Open Source (licensed under GPL 3.0)')); ?></h3>

        <p>This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.</p>
        <p>This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.</p>
        <p>You should have received a copy of the GNU General Public License along with this program.  If not, see <a href="http://www.gnu.org/licenses/" target="_blank">http://www.gnu.org/licenses/</a>.</p>

        <p>&nbsp;</p>

        <h3><?php echo Translate::get('Pixelmanager CMS uses the following open source software'); ?>:</h3>

        <p>
            <strong>Bootstrap</strong> (<a href="http://getbootstrap.com/" target="_blank">http://getbootstrap.com/</a>)<br>
            <strong>elFinder</strong> (<a href="http://elfinder.org" target="_blank">http://elfinder.org</a>)<br>
            <strong>HTML5 Boilerplate</strong> (<a href="http://html5boilerplate.com" target="_blank">http://html5boilerplate.com</a>)<br>
            <strong>JSON2</strong> (<a href="https://github.com/douglascrockford/JSON-js" target="_blank">https://github.com/douglascrockford/JSON-js</a>)<br>
            <strong>jQuery</strong> (<a href="http://jquery.com" target="_blank">http://jquery.com</a>)<br>
            <strong>jQuery UI</strong> (<a href="http://jqueryui.com" target="_blank">http://jqueryui.com</a>)<br>
            <strong>jsTree</strong> (<a href="http://www.jstree.com" target="_blank">http://www.jstree.com</a>)<br>
            <strong>RequireJS</strong> (<a href="http://requirejs.org" target="_blank">http://requirejs.org</a>)<br>
            <strong>Smarty Template Engine</strong> (<a href="http://www.smarty.net" target="_blank">http://www.smarty.net</a>)<br>
            <strong>TinyMCE</strong> (<a href="http://www.tinymce.com" target="_blank">http://www.tinymce.com</a>)<br>
            <strong>Portable PHP password hashing framework</strong> (<a href="http://www.openwall.com/phpass"
                                                                         target="_blank">http://www.openwall.com/phpass</a>)<br>
            <strong>WideImage</strong> (<a href="http://wideimage.sourceforge.net" target="_blank">http://wideimage.sourceforge.net</a>)<br>
        </p>

    </div>

</div>

</body>
</html>
