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

    <title>Pixelmanager</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="login" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="container-fluid pixelmanager-login-container">

    <div class="pixelmanager-login<?php if (isset($var->error)) {
        echo ' pixelmanager-login-error';
    } ?>">
        <form id="login-form" action="<?php echo $var->moduleUrl; ?>authenticate" method="post">
            <?php if (isset($var->error)) { ?>
                <div class="alert alert-danger">Login fehlgeschlagen</div>
            <?php } ?>
            <p><input class="form-control" type="text" name="login" id="login" value="" placeholder="Benutzername"></p>

            <p><input class="form-control" type="password" name="password" id="password" value=""
                      placeholder="Passwort"></p>

            <p id="language-container">
                <select class="form-control" id="language" name="language">
                    <option value="">Bevorzugte Sprache</option>
                    <?php foreach (Config::get()->backendLanguages->list as $key => $language) {
                        echo '<option value="' . $key . '">' . $language['name'] . '</option>';
                    } ?>
                </select>
            </p>
            <p>
                <button class="btn btn-primary" type="submit">Login</button>
            </p>
        </form>
    </div>

</div>

</body>
</html>
