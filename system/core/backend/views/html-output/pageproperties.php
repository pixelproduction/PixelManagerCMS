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
    <title><?php echo Translate::get('Page properties'); ?></title>
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="pageproperties" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="pixelmanager-iframe-wrapper">

    <div class="pixelmanager-iframe-content">

        <form id="createpage_form" method="post" action="#">
            <input type="hidden" name="action" value="<?php echo $var->action; ?>">
            <?php
            if ($var->action == 'edit') {
                if ($var->batchEdit) {
                    ?>
                    <input type="hidden" name="batchEdit" value="1">
                    <?php
                    foreach ($var->pageIdList as $id) {
                        ?>
                        <input type="hidden" name="pageId[]" value="<?php echo $id; ?>">
                        <?php
                    }
                } else {
                    ?>
                    <input type="hidden" name="pageId" value="<?php echo $var->pageId; ?>">
                    <?php
                }
            }
            ?>

            <div class="pixelmanager-error-container"></div>

            <?php if (($var->batchEdit) || ($var->containsSubpages)) { ?>
                <div class="well">
                    <?php
                    if ($var->batchEdit) {
                        echo Translate::get('You have selected more than one page. Please note that the current settings of the selected pages are not displayed, but standard settings have been assumed.');
                    } else {
                        echo Translate::get('The selected page contains subpages. If you would like to apply the settings to the subpages as well, check the box below.');
                    }
                    ?>
                    <br>
                    <br>

                    <div class="pixelmanager-checkbox-group">
                        <label><input type="checkbox" name="recursive"
                                      value="1"> <?php echo Translate::get('Apply the settings to all the subpages as well'); ?>
                        </label>
                    </div>
                </div>
            <?php } ?>

            <div class="tabbable">
                <ul class="nav nav-tabs" id="settings-tab">
                    <?php if (!$var->batchEdit) { ?>
                        <li class="active"><a href="#tab-root"
                                              data-toggle="tab"><?php echo Translate::get('General settings'); ?></a>
                        </li><?php } ?>
                    <li<?php if ($var->batchEdit) { ?> class="active"<?php } ?>><a href="#tab-visibility"
                                                                                   data-toggle="tab"><?php echo Translate::get('Visibility'); ?></a>
                    </li>
                    <?php if (Auth::isAdmin()) { ?>
                        <li><a href="#tab-access-control"
                               data-toggle="tab"><?php echo Translate::get('Access control'); ?></a></li><?php } ?>
                    <li><a href="#tab-miscellaneous"
                           data-toggle="tab"><?php echo Translate::get('Miscellaneous'); ?></a></li>
                </ul>

                <div class="tab-content">

                    <?php if (!$var->batchEdit) { ?>
                        <div class="tab-pane active" id="tab-root">
                            <div class="pixelmanager-input-block">
                                <div class="pixelmanager-input-block-headline">
                                    <h2><?php echo Translate::get('URL / folder name'); ?></h2>

                                    <h3><?php echo Translate::get('The partial URL used to access this page (used for the folder name on the server as well)'); ?></h3>
                                </div>
                                <div class="control-group">
                                    <input id="name" name="name" class="form-control" type="text"
                                           value="<?php echo $var->properties['name']; ?>">
                                </div>
                            </div>

                            <?php if (Config::get()->allowPageAliases === true) { ?>
                                <div class="pixelmanager-input-block">
                                    <div class="pixelmanager-input-block-headline">
                                        <h2><?php echo Translate::get('Alias URLs'); ?></h2>

                                        <h3><?php echo Translate::get('The URL can be localized as well. If an alias for a language is set, the page can only be reached by this alias. If the field is left empty for a language, the page can be reached by the URL set above.'); ?></h3>
                                    </div>
                                    <?php
                                    $languages = Config::get()->languages->list;
                                    foreach ($languages as $key => $language) {
                                        ?>
                                        <div class="control-group control-group-array-element">
                                            <div class="input-group">
                                                <input id="alias_<?php echo $key; ?>" name="alias[<?php echo $key; ?>]"
                                                       class="form-control" type="text"
                                                       value="<?php echo $var->alias[$key]; ?>">
                                                <span class="input-group-addon"><?php echo $language['name']; ?></span>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            <?php } ?>

                            <div class="pixelmanager-input-block">
                                <div class="pixelmanager-input-block-headline">
                                    <h2><?php echo Translate::get('Page title'); ?></h2>

                                    <h3><?php echo Translate::get('The title displayed in the navigation'); ?></h3>
                                </div>
                                <?php
                                $languages = Config::get()->languages->list;
                                foreach ($languages as $key => $language) {
                                    ?>
                                    <div class="control-group control-group-array-element">
                                        <div class="input-group">
                                            <input id="caption_<?php echo $key; ?>" name="caption[<?php echo $key; ?>]"
                                                   class="form-control" type="text"
                                                   value="<?php echo $var->caption[$key]; ?>">
                                            <span class="input-group-addon"><?php echo $language['name']; ?></span>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>

                            <?php if ($var->action == 'create') { ?>

                                <div class="pixelmanager-input-block">
                                    <div class="pixelmanager-input-block-headline">
                                        <h2><?php echo Translate::get('Parent page'); ?></h2>

                                        <h3><?php echo Translate::get('The new page will be displayed as a subpage of this page'); ?></h3>
                                    </div>
                                    <div class="control-group">
                                        <select name="parent-id" id="parent-id" class="form-control">
                                            <option value="<?php echo Pages::ROOT_ID; ?>">
                                                [<?php echo Translate::get('Root'); ?>]
                                            </option>
                                            <?php
                                            $preselectedParentId = Request::getParam('parentId', Pages::ROOT_ID);
                                            $pagetree = $var->pagetree;
                                            $pages = new Pages();
                                            function printPagetreeOptions(
                                                &$pagetree,
                                                $pages,
                                                $preselectedParentId,
                                                $indent = ''
                                            ) {
                                                foreach ($pagetree as $page) {
                                                    print('<option value="' . $page['id'] . '"' . (($page['id'] == $preselectedParentId) ? ' selected' : '') . '>' . $indent . Helpers::htmlEntities($pages->getAnyCaption($page['id'])) . '</option>');
                                                    if (isset($page['children'])) {
                                                        if ($page['children'] !== false) {
                                                            if (count($page['children']) > 0) {
                                                                printPagetreeOptions($page['children'], $pages,
                                                                    $preselectedParentId, $indent . '&nbsp;-&nbsp;');
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            printPagetreeOptions($pagetree, $pages, $preselectedParentId);
                                            ?>
                                        </select>
                                    </div>
                                </div>

                            <?php } ?>

                            <div class="pixelmanager-input-block">
                                <div class="pixelmanager-input-block-headline">
                                    <h2><?php echo Translate::get('Page type'); ?></h2>

                                    <h3><?php echo Translate::get('Used template'); ?></h3>
                                </div>
                                <div class="control-group">
                                    <select name="template-id" id="template-id"
                                            class="form-control<?php if ($var->action != 'create') { ?> edit-template-id"
                                            data-saved-value="<?php if ($var->properties['template-id'] == null) {
                                                echo 'NULL';
                                            } else {
                                                echo $var->properties['template-id'];
                                            } ?>"<?php } ?>">
                                    <?php
                                    $counter = 0;
                                    $templates = DataStructure::pagesArray();
                                    foreach ($templates as $template_id => $template) {
                                        if ($template_id != Pages::GLOBAL_ELEMENTS) {
                                            ?>
                                            <option
                                                value="<?php echo $template_id; ?>"<?php if ($template_id == $var->properties['template-id']) {
                                                echo ' selected';
                                            } ?>><?php echo $template['name']; ?></option>
                                            <?php
                                            $counter++;
                                        }
                                    }
                                    ?>
                                    <option
                                        value="NULL"<?php if (($var->properties['template-id'] == null) && ($var->action != 'create')) {
                                        echo ' selected';
                                    } ?>>* <?php echo Translate::get('Link / Redirection'); ?> *
                                    </option>
                                    </select>
                                </div>
                            </div>

                            <div id="link-properties" class="pixelmanager-input-block">
                                <div class="pixelmanager-input-block-headline">
                                    <h2><?php echo Translate::get('Link / Redirection'); ?></h2>

                                    <h3><?php echo Translate::get('The URL to wich this menu item redirects to'); ?></h3>
                                </div>

                                <div class="pixelmanager-checkbox-group">
                                    <label><input type="checkbox" id="link-translated" name="link-translated"
                                                  value="1"<?php echo(($var->properties['link-translated'] > 0) ? ' checked' : ''); ?>> <?php echo Translate::get('Language specific URLs'); ?>
                                    </label>
                                    <label><input type="checkbox" id="link-new-window" name="link-new-window"
                                                  value="1"<?php echo(($var->properties['link-new-window'] > 0) ? ' checked' : ''); ?>> <?php echo Translate::get('Open in new window'); ?>
                                    </label>
                                </div>

                                <div class="pixelmanager-link-properties-language-specific-url">
                                    <?php
                                    $languages = Config::get()->languages->list;
                                    foreach ($languages as $key => $language) {
                                        ?>
                                        <div class="control-group control-group-array-element">
                                            <div class="input-group">
                                                <input id="translated-link-urls-<?php echo $key; ?>"
                                                       name="translated-link-urls[<?php echo $key; ?>]"
                                                       class="form-control" type="text"
                                                       value="<?php echo $var->translatedLinkUrls[$key]; ?>">
                                                <span class="input-group-addon"><?php echo $language['name']; ?></span>
														<span class="input-group-btn">
															<button class="btn btn-default btn-get-link-to-page"
                                                                    data-language-id="<?php echo $key; ?>"
                                                                    type="button"><span
                                                                    class="glyphicon glyphicon-share"></span></button>
															<button class="btn btn-default btn-get-download"
                                                                    data-language-id="<?php echo $key; ?>"
                                                                    type="button"><span
                                                                    class="glyphicon glyphicon-folder-open"></span>
                                                            </button>
														</span>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <div class="pixelmanager-link-properties-url">
                                    <div class="control-group">
                                        <div class="input-group">
                                            <input id="link-url" name="link-url" class="form-control" type="text"
                                                   value="<?php echo $var->properties['link-url']; ?>">
												<span class="input-group-btn">
													<button class="btn btn-default btn-get-link-to-page"
                                                            data-language-id="" type="button"><span
                                                            class="glyphicon glyphicon-share"></span> <?php echo Translate::get('Link to page'); ?>
                                                    </button>
													<button class="btn btn-default btn-get-download" data-language-id=""
                                                            type="button"><span
                                                            class="glyphicon glyphicon-folder-open"></span> <?php echo Translate::get('Download'); ?>
                                                    </button>
												</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($var->containsSubpages) { ?>
                                <div class="well">
                                    <?php echo Translate::get('The settings above only apply to the selected page, not the subpages'); ?>
                                </div>
                            <?php } ?>
                        </div>

                    <?php } ?>

                    <div class="tab-pane<?php if ($var->batchEdit) { ?> active<?php } ?>" id="tab-visibility">
                        <div class="pixelmanager-input-block">
                            <div class="pixelmanager-input-block-headline">
                                <h2><?php echo Translate::get('Visibility'); ?></h2>

                                <h3><?php echo Translate::get('Controls the visibility of the page in the navigation'); ?></h3>
                            </div>
                            <div class="pixelmanager-radio-group">
                                <label><input type="radio" name="visibility" id="visibility_always"
                                              value="<?php echo Pages::VISIBILITY_ALWAYS; ?>"<?php if ($var->properties['visibility'] == Pages::VISIBILITY_ALWAYS) {
                                        echo ' checked';
                                    } ?>> <?php echo Translate::get('visible in all languages'); ?></label><br>
                                <label><input type="radio" name="visibility" id="visibility_never"
                                              value="<?php echo Pages::VISIBILITY_NEVER; ?>"<?php if ($var->properties['visibility'] == Pages::VISIBILITY_NEVER) {
                                        echo ' checked';
                                    } ?>> <?php echo Translate::get('not visible in any language'); ?></label><br>
                                <label><input type="radio" name="visibility" id="visibility_select"
                                              value="<?php echo Pages::VISIBILITY_SELECT; ?>"<?php if ($var->properties['visibility'] == Pages::VISIBILITY_SELECT) {
                                        echo ' checked';
                                    } ?>> <?php echo Translate::get('visible in the following languages'); ?></label>
                            </div>
                            <div class="pixelmanager-checkbox-group pixelmanager-input-group-indent">
                                <?php
                                $languages = Config::get()->languages->list;
                                foreach ($languages as $key => $language) {
                                    ?>
                                    <label><input id="visible-in[<?php echo $key; ?>]"
                                                  name="visible-in[<?php echo $key; ?>]" type="checkbox"
                                                  value="1"<?php if ($var->visibility[$key] == 1) {
                                            echo ' checked';
                                        } ?>> <?php echo $language['name']; ?></label><br>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php if (($var->batchEdit) || ($var->containsSubpages)) { ?>
                            <div class="well pixelmanager-radio-group">
                                <label><input type="radio" name="applyVisibility" value="1"
                                              checked> <?php echo Translate::get('Yes, apply the visibility settings'); ?>
                                </label><br>
                                <label><input type="radio" name="applyVisibility"
                                              value="0"> <?php echo Translate::get('No, don\'t change the visiblity settings'); ?>
                                </label>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="applyVisibility" value="1">
                        <?php } ?>
                    </div>

                    <?php if (Auth::isAdmin()) { ?>
                        <div class="tab-pane" id="tab-access-control">
                            <div class="pixelmanager-input-block">
                                <div class="pixelmanager-input-block-headline">
                                    <h2><?php echo Translate::get('Access control'); ?></h2>

                                    <h3><?php echo Translate::get('Controls who is allowed to edit this page'); ?></h3>
                                </div>
                                <div class="pixelmanager-checkbox-group">
                                    <label><input type="checkbox" name="inherit-acl-resource"
                                                  value="1"<?php echo(($var->inheritAcl) ? ' checked' : ''); ?>> <?php echo Translate::get('Inherit from parent page'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="pixelmanager-input-block">
                                <div class="pixelmanager-radio-group">
                                    <label><input type="radio" name="user-groups-mode"
                                                  value="<?php echo Acl::RESOURCE_SUPERUSER_ONLY; ?>"<?php if ($var->aclResource['user-groups-mode'] == Acl::RESOURCE_SUPERUSER_ONLY) {
                                            echo ' checked';
                                        } ?>> <?php echo Translate::get('Only administrators can access this page'); ?>
                                    </label><br>
                                    <label><input type="radio" name="user-groups-mode"
                                                  value="<?php echo Acl::RESOURCE_ALL_USERS; ?>"<?php if ($var->aclResource['user-groups-mode'] == Acl::RESOURCE_ALL_USERS) {
                                            echo ' checked';
                                        } ?>> <?php echo Translate::get('All users can access this page'); ?>
                                    </label><br>
                                    <label><input type="radio" name="user-groups-mode"
                                                  value="<?php echo Acl::RESOURCE_USER_WHITELIST; ?>"<?php if ($var->aclResource['user-groups-mode'] == Acl::RESOURCE_USER_WHITELIST) {
                                            echo ' checked';
                                        } ?>> <?php echo Translate::get('Only user in the following groups can access this page'); ?>
                                    </label><br>
                                </div>
                                <br>
                                <table class="table table-striped table-bordered table-condensed">
                                    <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th><?php echo Translate::get('User group name'); ?></th>
                                        <th><?php echo Translate::get('Level'); ?></th>
                                    </tr>
                                    </thead>
                                    <?php
                                    $user_groups = new UserGroups();
                                    $groups = $user_groups->getAll(UserGroups::SORT_ORDER_LEVEL);
                                    $selected_groups = $var->aclUserGroups->getArrayCopy();
                                    if (is_array($groups) && (count($groups) > 0)) {
                                        foreach ($groups as $group) {
                                            ?>
                                            <tr>
                                                <td><input id="user-groups[<?php echo $group['id']; ?>]"
                                                           name="user-groups[<?php echo $group['id']; ?>]"
                                                           type="checkbox"
                                                           value="<?php echo $group['id']; ?>"<?php if (in_array($group['id'],
                                                        $selected_groups)) {
                                                        echo ' checked';
                                                    } ?>></td>
                                                <td><?php echo $group['name']; ?></td>
                                                <td><?php echo $group['level']; ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>

                                </table>
                            </div>
                            <?php if (($var->batchEdit) || ($var->containsSubpages)) { ?>
                                <div class="well pixelmanager-radio-group">
                                    <label><input type="radio" name="applyAcl" value="1"
                                                  checked> <?php echo Translate::get('Yes, apply the access control settings'); ?>
                                    </label><br>
                                    <label><input type="radio" name="applyAcl"
                                                  value="0"> <?php echo Translate::get('No, don\'t change the access control settings'); ?>
                                    </label>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="applyAcl" value="1">
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <div class="tab-pane" id="tab-miscellaneous">
                        <div class="pixelmanager-input-block">
                            <div class="pixelmanager-input-block-headline">
                                <h2><?php echo Translate::get('Miscellaneous'); ?></h2>

                                <h3><?php echo Translate::get('Further options'); ?></h3>
                            </div>
                            <div class="pixelmanager-checkbox-group">
                                <label><input id="active" name="active" type="checkbox"
                                              value="1"<?php if ($var->properties['active'] == 1) {
                                        echo ' checked';
                                    } ?>> <?php echo Translate::get('Active (can be displayed linked)'); ?></label><br>
                                <label><input id="cachable" name="cachable" type="checkbox"
                                              value="1"<?php if ($var->properties['cachable'] == 1) {
                                        echo ' checked';
                                    } ?>> <?php echo Translate::get('Cachable'); ?></label><br>
                            </div>
                        </div>
                        <?php if (!$var->batchEdit) { ?>
                            <div class="pixelmanager-input-block">
                                <div class="pixelmanager-input-block-headline">
                                    <h2><?php echo Translate::get('Unique Id'); ?></h2>

                                    <h3><?php echo Translate::get('A uniqe character string, to identify this page (please change only if you REALLY know what you do)'); ?></h3>
                                </div>
                                <div class="control-group">
                                    <input id="unique-id" name="unique-id" class="form-control" type="text"
                                           value="<?php echo $var->properties['unique-id']; ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (($var->batchEdit) || ($var->containsSubpages)) { ?>
                            <div class="well pixelmanager-radio-group">
                                <label><input type="radio" name="applyMiscellaneous" value="1"
                                              checked> <?php echo Translate::get('Yes, apply the miscellaneous settings'); ?>
                                </label><br>
                                <label><input type="radio" name="applyMiscellaneous"
                                              value="0"> <?php echo Translate::get('No, don\'t change the miscellaneous settings'); ?>
                                </label>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="applyMiscellaneous" value="1">
                        <?php } ?>
                    </div>

                </div>
            </div>

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
