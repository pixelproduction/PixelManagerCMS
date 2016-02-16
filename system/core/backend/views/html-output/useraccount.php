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
    <title><?php echo Translate::get('User account'); ?></title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="useraccount" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content">

        <div class="pixelmanager-error-container"></div>
        <form id="user-account-form" method="post" action="#" class="form-vertical" autocomplete="off">
            <fieldset>
                <div class="pixelmanager-input-block">
                    <div class="pixelmanager-input-block-headline">
                        <h2><?php echo Translate::get('Screen name'); ?></h2>

                        <h3><?php echo Translate::get('Your name, displayed at the top right of the screen.'); ?></h3>
                    </div>
                    <div class="control-group">
                        <input name="screenname" id="screenname" class="form-control input-xlarge" type="text"
                               value="<?php echo Helpers::htmlEntities($var->account->screenname); ?>">
                    </div>
                </div>
                <div class="pixelmanager-input-block">
                    <div class="pixelmanager-input-block-headline">
                        <h2><?php echo Translate::get('Login'); ?></h2>

                        <h3><?php echo Translate::get('The name used to login into the system. Must be uniqe.'); ?></h3>
                    </div>
                    <div class="control-group">
                        <input name="login" id="login" class="form-control input-xlarge" type="text" autocomplete="off"
                               value="<?php echo Helpers::htmlEntities($var->account->login); ?>">
                    </div>
                </div>
                <div class="pixelmanager-input-block">
                    <div class="pixelmanager-input-block-headline">
                        <h2><?php echo Translate::get('Password'); ?></h2>

                        <h3><?php echo Translate::get('The password used to log into the system.<br><em>Only fill out this field if you want to set a new password.</em>'); ?></h3>
                    </div>
                    <div class="control-group">
                        <input name="password" id="password" class="form-control input-xlarge" type="password"
                               autocomplete="off">
                    </div>
                </div>
                <div class="pixelmanager-input-block">
                    <div class="pixelmanager-input-block-headline">
                        <h2><?php echo Translate::get('Preferred language'); ?></h2>

                        <h3><?php echo Translate::get('The language the screen texts are displayed in.'); ?></h3>
                    </div>
                    <div class="control-group">
                        <select name="preferred-language" id="preferred-language" class="form-control input-xlarge">
                            <?php
                            $languages = Config::get()->backendLanguages->list;
                            $preferred = $var->account['preferred-language'];
                            if ($preferred === null) {
                                $preferred = Config::get()->backendLanguages->standard;
                            }
                            foreach ($languages as $key => $language) {
                                ?>
                                <option
                                    value="<?php echo $key; ?>"<?php echo(($key == $preferred) ? ' selected' : ''); ?>><?php echo $language['name']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div
                    class="well"><?php echo Translate::get('The changes will take effect the next time you log in.'); ?></div>
            </fieldset>
        </form>

    </div>

    <div class="pixelmanager-iframe-buttons">
        <div class="btn-toolbar">
            <button id="btn_save" class="btn btn-sm btn-success"><span
                    class="glyphicon glyphicon-ok icon-white"></span> <?php echo Translate::get('_BTN_SAVE_'); ?>
            </button>
            <button id="btn_close" class="btn btn-sm btn-danger"><span
                    class="glyphicon glyphicon-remove icon-white"></span> <?php echo Translate::get('_BTN_CANCEL_'); ?>
            </button>
        </div>
    </div>

</div>

</body>
</html>
