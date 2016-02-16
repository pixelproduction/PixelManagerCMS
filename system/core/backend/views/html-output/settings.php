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

function printPagetreeOptions(&$pagetree, $pages, $preselectedParentId, $indent = '')
{
    foreach ($pagetree as $page) {
        print('<option value="' . $page['id'] . '"' . (($page['id'] == $preselectedParentId) ? ' selected' : '') . '>' . $indent . Helpers::htmlEntities($pages->getAnyCaption($page['id'])) . '</option>');
        if (isset($page['children'])) {
            if ($page['children'] !== false) {
                if (count($page['children']) > 0) {
                    printPagetreeOptions($page['children'], $pages, $preselectedParentId, $indent . '&nbsp;-&nbsp;');
                }
            }
        }
    }
}

$languages = Config::get()->languages->list;
$pagetree = $var->pagetree;
$pages = new Pages();

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
    <title>Einstellungen</title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="settings" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content">

        <div class="pixelmanager-error-container"></div>
        <form id="user-account-form" method="post" action="#" class="form-horizontal" autocomplete="off">
            <fieldset>
                <div class="pixelmanager-input-block">
                    <div class="pixelmanager-input-block-headline">
                        <h2><?php echo Translate::get('Home page'); ?></h2>

                        <h3><?php echo Translate::get('The entry point of the website.'); ?></h3>
                    </div>
                    <?php
                    foreach ($languages as $language_id => $language) {
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="start-page-<?php echo $language_id; ?>"><?php echo $language['name']; ?></label>

                            <div class="col-sm-10">
                                <select class="form-control input-xlarge" name="start-page-<?php echo $language_id; ?>"
                                        id="start-page-<?php echo $language_id; ?>">
                                    <option value="0">[<?php echo Translate::get('Automatic'); ?>]</option>
                                    <?php
                                    $saved_start_page = 0;
                                    if (isset($var->settings['startPages'][$language_id])) {
                                        $saved_start_page = $var->settings['startPages'][$language_id];
                                    }
                                    printPagetreeOptions($pagetree, $pages, $saved_start_page);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="pixelmanager-input-block">
                    <div class="pixelmanager-input-block-headline">
                        <h2><?php echo Translate::get('404 error page'); ?></h2>

                        <h3><?php echo Translate::get('Wich page to show when the requested one was not found.'); ?></h3>
                    </div>
                    <?php
                    foreach ($languages as $language_id => $language) {
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="error-page-<?php echo $language_id; ?>"><?php echo $language['name']; ?></label>

                            <div class="col-sm-10">
                                <select class="form-control input-xlarge" name="error-page-<?php echo $language_id; ?>"
                                        id="error-page-<?php echo $language_id; ?>">
                                    <option value="0">[<?php echo Translate::get('Automatic'); ?>]</option>
                                    <?php
                                    $saved_error_page = 0;
                                    if (isset($var->settings['errorPages'][$language_id])) {
                                        $saved_error_page = $var->settings['errorPages'][$language_id];
                                    }
                                    printPagetreeOptions($pagetree, $pages, $saved_error_page);
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="pixelmanager-input-block">
                    <div class="pixelmanager-input-block-headline">
                        <h2><?php echo Translate::get('Caching'); ?></h2>

                        <h3><?php echo Translate::get('Controls if and how the cache is used (not to be confused with the browser cache!).'); ?></h3>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo Translate::get('Use cache'); ?></label>

                        <div class="col-sm-10">
                            <label class="checkbox"><input type="checkbox" id="use-cache" name="use-cache"
                                                           value="1" <?php echo((Settings::get('useCache',
                                    false)) ? 'checked' : ''); ?>> <?php echo Translate::get('Yes, use the internal caching system'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"
                               for="cache-lifetime"><?php echo Translate::get('Cache lifetime'); ?></label>

                        <div class="col-sm-10">
                            <div class="input-group input-large"><input class="form-control" id="cache-lifetime"
                                                                        name="cache-lifetime" type="text"
                                                                        value="<?php echo Settings::get('cacheLifetime',
                                                                            0); ?>"><span
                                    class="input-group-addon"><?php echo Translate::get('seconds'); ?></span></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="clear_cache">&nbsp;</label>

                        <div class="col-sm-10">
                            <button type="button" id="clear_cache"
                                    class="btn btn-inverse"><?php echo Translate::get('Clear cache now'); ?></button>
                        </div>
                    </div>
                </div>
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
