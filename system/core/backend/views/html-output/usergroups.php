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
    <title>Benutzergruppen</title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="usergroups" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body class="pixelmanager-iframe">

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content">


        <form action="#" name="user-groups-form" id="user-groups-form" method="post">
            <table id="user-groups-table" class="table table-bordered table-striped table-condensed">
                <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php echo Translate::get('Name'); ?></th>
                    <th><?php echo Translate::get('Level'); ?></th>
                    <th><?php echo Translate::get('Create'); ?></th>
                    <th><?php echo Translate::get('Edit'); ?></th>
                    <th><?php echo Translate::get('Publish'); ?></th>
                    <th><?php echo Translate::get('Delete'); ?></th>
                    <th><?php echo Translate::get('Modules'); ?></th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </form>

    </div>

    <div class="pixelmanager-iframe-buttons">
        <div class="btn-toolbar">
            <button id="btn_add_new_user_group" class="btn btn-default btn-sm pull-left"><span
                    class="glyphicon glyphicon-user"></span> <?php echo Translate::get('New user group'); ?></button>
            <button id="btn_delete_selected_user_groups" class="btn btn-default btn-sm pull-left"><span
                    class="glyphicon glyphicon-remove-sign"></span> <?php echo Translate::get('Delete'); ?></button>
            <button id="btn_refresh" class="btn btn-default btn-sm pull-left"><span
                    class="glyphicon glyphicon-refresh"></span></button>
            <button id="btn_close" class="btn btn-default btn-sm"><span
                    class="glyphicon glyphicon-remove"></span> <?php echo Translate::get('_BTN_CLOSE_'); ?></button>
        </div>
    </div>

</div>

<div class="modal fade" id="edit-user-group">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Translate::get('User group data'); ?></h3>
            </div>
            <div class="modal-body">
                <div class="pixelmanager-error-container"></div>
                <form id="edit-user-group-form" method="post" action="#" class="form-horizontal">
                    <input type="hidden" name="action" id="action" value="create">
                    <input type="hidden" name="user-group-id" id="user-group-id" value="">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"
                                   for="name"><?php echo Translate::get('Name'); ?></label>

                            <div class="col-sm-9"><input name="name" id="name" class="form-control" type="text"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"
                                   for="level"><?php echo Translate::get('Level'); ?></label>

                            <div class="col-sm-9"><input name="level" id="level" class="form-control input-mini"
                                                         type="text"></div>
                        </div>
                        <div class="form-group">
                            <label
                                class="col-sm-3 control-label"><?php echo Translate::get('Permitted actions'); ?></label>

                            <div class="col-sm-9">
                                <div class="pixelmanager-checkbox-group">
                                    <label><input type="checkbox" name="action-create" id="action-create"
                                                  value="1"> <?php echo Translate::get('Create'); ?></label><br>
                                    <label><input type="checkbox" name="action-edit" id="action-edit"
                                                  value="1"> <?php echo Translate::get('Edit'); ?></label><br>
                                    <label><input type="checkbox" name="action-publish" id="action-publish"
                                                  value="1"> <?php echo Translate::get('Publish'); ?></label><br>
                                    <label><input type="checkbox" name="action-delete" id="action-delete"
                                                  value="1"> <?php echo Translate::get('Delete'); ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo Translate::get('Modules'); ?></label>

                            <div class="col-sm-9">
                                <table id="modules-table" class="table table-bordered table-striped table-condensed">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th><?php echo Translate::get('Module name / Description'); ?></th>
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
                   id="btn_edit_user_group_ok"><?php echo Translate::get('_BTN_SAVE_'); ?></a>
                <a href="javascript:;" class="btn btn-default"
                   id="btn_edit_user_group_cancel"><?php echo Translate::get('_BTN_CANCEL_'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-user-groups">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Translate::get('Delete selected user groups'); ?></h3>
            </div>
            <div class="modal-body">
                <p>
                    <strong><?php echo Translate::get('Do you really want to delete the selected user groups? This can\'t be undone!'); ?></strong>
                </p>
            </div>
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-danger"
                   id="btn_delete_user_groups_ok"><?php echo Translate::get('Delete'); ?></a>
                <a href="javascript:;" class="btn btn-default"
                   id="btn_delete_user_groups_cancel"><?php echo Translate::get('Cancel'); ?></a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
