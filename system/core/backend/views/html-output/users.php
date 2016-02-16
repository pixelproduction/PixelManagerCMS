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
    <title>Benutzer</title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="users" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body class="pixelmanager-iframe">

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content">


        <form action="#" name="users-form" id="users-form" method="post">
            <table id="user-table" class="table table-bordered table-striped table-condensed">
                <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php echo Translate::get('Name'); ?></th>
                    <th><?php echo Translate::get('Account type'); ?></th>
                    <th><?php echo Translate::get('Login'); ?></th>
                    <th><?php echo Translate::get('User groups'); ?></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </form>

    </div>

    <div class="pixelmanager-iframe-buttons">
        <div class="btn-toolbar">
            <button id="btn_add_new_user" class="btn btn-default btn-sm pull-left"><span
                    class="glyphicon glyphicon-user"></span> <?php echo Translate::get('New user'); ?></button>
            <button id="btn_delete_selected_users" class="btn btn-default btn-sm pull-left"><span
                    class="glyphicon glyphicon-remove-sign"></span> <?php echo Translate::get('Delete'); ?></button>
            <button id="btn_refresh" class="btn btn-default btn-sm pull-left"><span
                    class="glyphicon glyphicon-refresh"></span></button>
            <button id="btn_close" class="btn btn-default btn-sm"><span
                    class="glyphicon glyphicon-remove"></span> <?php echo Translate::get('_BTN_CLOSE_'); ?></button>
        </div>
    </div>

</div>

<div class="modal fade" id="edit-user">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Translate::get('User data'); ?></h3>
            </div>
            <div class="modal-body">
                <div class="pixelmanager-error-container"></div>
                <form id="edit-user-form" method="post" action="#" class="form-horizontal" autocomplete="off">
                    <input type="hidden" name="action" id="action" value="create">
                    <input type="hidden" name="user-id" id="user-id" value="">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"
                                   for="screenname"><?php echo Translate::get('Name'); ?></label>

                            <div class="col-sm-9"><input name="screenname" id="screenname" class="form-control"
                                                         type="text"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"
                                   for="login"><?php echo Translate::get('Login'); ?></label>

                            <div class="col-sm-9"><input name="login" id="login" class="form-control" type="text"
                                                         autocomplete="off"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"
                                   for="password"><?php echo Translate::get('Password'); ?></label>

                            <div class="col-sm-9"><input name="password" id="password" class="form-control"
                                                         type="password" autocomplete="off">

                                <p class="help-block"
                                   id="password-notice"><?php echo Translate::get('Enter a new password here, or leave blank if you don\'t want to change the old password'); ?></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"
                                   for="privileges"><?php echo Translate::get('Account type'); ?></label>

                            <div class="col-sm-9">
                                <select name="privileges" id="privileges" class="form-control">
                                    <option
                                        value="<?php echo Auth::PRIVILEGES_USER; ?>"><?php echo Translate::get('User'); ?></option>
                                    <option
                                        value="<?php echo Auth::PRIVILEGES_ADMIN; ?>"><?php echo Translate::get('Administrator'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo Translate::get('User groups'); ?></label>

                            <div class="col-sm-9">
                                <table id="user-groups-table"
                                       class="table table-bordered table-striped table-condensed">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th><?php echo Translate::get('Name'); ?></th>
                                        <th><?php echo Translate::get('Level'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-primary"
                   id="btn_edit_user_ok"><?php echo Translate::get('_BTN_SAVE_'); ?></a>
                <a href="javascript:;" class="btn btn-default"
                   id="btn_edit_user_cancel"><?php echo Translate::get('_BTN_CANCEL_'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-users">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Translate::get('Delete selected users'); ?></h3>
            </div>
            <div class="modal-body">
                <p>
                    <strong><?php echo Translate::get('Do you really want to delete the selected users? This can\'t be undone!'); ?></strong>
                </p>
            </div>
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-danger"
                   id="btn_delete_users_ok"><?php echo Translate::get('Delete'); ?></a>
                <a href="javascript:;" class="btn btn-default"
                   id="btn_delete_users_cancel"><?php echo Translate::get('Cancel'); ?></a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
