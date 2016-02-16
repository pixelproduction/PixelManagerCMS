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

    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/libs/jquery-ui/jquery-ui.css">
    <link rel="stylesheet" href="<?php echo $var->publicUrl; ?>css/style.css">

    <script src="<?php echo $var->publicUrl; ?>js/libs/modernizr.js"></script>
    <script>
        pixelmanagerGlobal = {
            baseUrl: '<?php echo addslashes($var->baseUrl); ?>',
            userLoginName: '<?php echo addslashes(Auth::getLoginName()); ?>',
            backendLanguage: '<?php echo addslashes($var->backendLanguage); ?>',
            backendTranslation: <?php echo json_encode($var->backendTranslation); ?>,
            activeLanguage: '<?php echo addslashes(Config::get()->languages->standard); ?>',
            languages:
            <?php
                $languages = array();
                foreach(Config::get()->languages->list as $key => $language) {
                    $languages[$key] = $language['name'];
                }
                echo json_encode($languages);
            ?>
            ,
            languagesInDevelopment:
            <?php
                $languages = array();
                foreach(Config::get()->languages->list as $key => $language) {
                    $in_development = false;
                    if (isset($language['inDevelopment'])) {
                        if ($language['inDevelopment'] === true) {
                            $in_development = true;
                        }
                    }
                    $languages[$key] = $in_development;
                }
                echo json_encode($languages);
            ?>
            ,
            languageIcons:
            <?php
                $languages = array();
                foreach(Config::get()->languages->list as $key => $language) {
                    $icon = '';
                    if (isset($language['icon'])) {
                        if (trim($language['icon']) != '') {
                            $icon = $var->publicUrl . 'img/flags/' . $language['icon'];
                        }
                    }
                    $languages[$key] = $icon;
                }
                echo json_encode($languages);
            ?>
            ,
            standardLanguage: '<?php echo Config::get()->languages->standard; ?>',
            preferredLanguageSubstitutes:
            <?php
                $language_substitutes = array();
                foreach(Config::get()->languages->list as $key => $language) {
                    $language_substitutes[$key] = $language['preferredSubstitutes']->getArrayCopy();
                }
                echo json_encode($language_substitutes);
            ?>
            ,
            dataEditorPlugins:
            <?php
                $loadDataEditorPlugins = array();
                $config = Config::getArray();
                $dataEditorPlugins = $config['dataEditorPlugins'];
                foreach($dataEditorPlugins as $key => $plugin) {
                    if (is_array($plugin)) {
                        $loadDataEditorPlugins[] = $var->baseUrl . $plugin['file'];
                        if (isset($plugin['additionalJavaScript'])) {
                            if (is_array($plugin['additionalJavaScript'])) {
                                foreach($plugin['additionalJavaScript'] as $add_js) {
                                    $loadDataEditorPlugins[] = $var->baseUrl . $add_js;
                                }
                            } else {
                                $loadDataEditorPlugins[] = $var->baseUrl . $plugin['additionalJavaScript'];
                            }
                        }
                    } else {
                        $loadDataEditorPlugins[] = $var->baseUrl . $plugin;
                    }
                }
                echo json_encode($loadDataEditorPlugins);
            ?>,
            globalElementsPageId: '<?php echo $var->globalElementsPageId; ?>'
        };
    </script>
    <?php include(realpath(dirname(__FILE__) . "/require-js-config.inc.php")); ?>
    <script data-main="main" src="<?php echo $var->publicUrl; ?>js/libs/require.js"></script>
</head>

<body>

<div class="container pixelmanager-main-container">

    <input type="hidden" id="ajax-callback-element" value="">

    <nav class="navbar navbar-inverse" role="navigation">
        <div class="navbar-header">
            <?php if (Config::get()->backendBranding->linkToAboutPage) { ?>
                <a class="navbar-brand" href="#" id="btn_about" title="<?php echo Translate::get('About...'); ?>"><img
                        src="<?php echo $var->baseUrl . Config::get()->backendBranding->logo; ?>"
                        alt="<?php echo Config::get()->backendBranding->companyName; ?>"></a>
            <?php } else { ?>
                <span class="navbar-brand"><img
                        src="<?php echo $var->baseUrl . Config::get()->backendBranding->logo; ?>"
                        alt="<?php echo Config::get()->backendBranding->companyName; ?>"></span>
            <?php } ?>
        </div>
        <ul class="nav navbar-nav">
            <?php

            $config = Config::getArray();
            if ($config['showBackendModulesMenu'] === true) {
                if (isset($config['backendModules'])) {
                    if (is_array($config['backendModules'])) {
                        if (count($config['backendModules']) > 0) {
                            ?>
                            <li class="dropdown"><a href="javascript:;" class="dropdown-toggle"
                                                    data-toggle="dropdown"><?php echo Translate::get('Modules'); ?><b
                                        class="caret"></b></a>
                                <ul id="modules-menu" class="dropdown-menu"></ul>
                            </li>
                            <?php
                        }
                    }
                }
            };
            ?>
            <li><a href="javascript:;" id="menu_images"><?php echo Translate::get('Images'); ?></a></li>
            <li><a href="javascript:;" id="menu_downloads"><?php echo Translate::get('Downloads'); ?></a></li>
            <?php if (Auth::isAdmin()) { ?>
                <li class="dropdown"><a href="#" class="dropdown-toggle"
                                        data-toggle="dropdown"><?php echo Translate::get('Options'); ?><b
                            class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:;" id="menu_users"><?php echo Translate::get('Users'); ?></a></li>
                        <li><a href="javascript:;"
                               id="menu_user_groups"><?php echo Translate::get('User groups'); ?></a></li>
                        <!-- <li><a href="javascript:;" id="menu_acl"><?php echo Translate::get('Access control'); ?></a></li> -->
                        <?php if ($var->useGlobalElementsPage) { ?>
                            <li class="divider"></li>
                            <li><a href="javascript:;"
                                   id="menu_global_elements"><?php echo Translate::get('Global elements'); ?></a></li>
                        <?php } ?>
                        <li class="divider"></li>
                        <li><a href="javascript:;" id="menu_settings"><?php echo Translate::get('Settings'); ?></a></li>
                    </ul>
                </li>
            <?php } ?>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <?php if (APPLICATION_CONTAINER == 'vagrant'): ?>
                <li>
                    <a href="javascript:void(0);" id="menu_synchronize"><span
                            class="glyphicon glyphicon-refresh"></span> <?php echo Translate::get('Synchronize'); ?></a>
                </li>
            <?php endif ?>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                        class="glyphicon glyphicon-globe"></span> <span
                        id="active-language-name"><?php echo Config::get()->languages->list[Config::get()->languages->standard]["name"]; ?></span>
                    <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li class="dropdown-header"><?php echo Translate::get('Input language'); ?></li>
                    <?php
                    foreach (Config::get()->languages->list as $key => $language) {
                        $icon_html = '';
                        if (isset($language['icon'])) {
                            if (trim($language['icon']) != '') {
                                $icon_html = '<img src="' . $var->publicUrl . 'img/flags/' . $language['icon'] . '" alt="">';
                            }
                        }
                        echo '<li class="main-language"><a href="javascript:;" data-language="' . $key . '" id="btn_language_' . $key . '"><input type="radio" name="rdo_language_' . $key . '"> ' . $language["name"] . $icon_html . '</a></li>';
                    }
                    ?>
                    <li class="divider"></li>
                    <li class="dropdown-header"><?php echo Translate::get('Secondary language'); ?></li>
                    <li class="secondary-language"><a href="javascript:;" data-language="__none__"
                                                      id="btn_secondary_language_none"><input type="radio"
                                                                                              name="rdo_secondary_language_none"> <?php echo Translate::get('None'); ?>
                        </a></li>
                    <?php
                    foreach (Config::get()->languages->list as $key => $language) {
                        $icon_html = '';
                        if (isset($language['icon'])) {
                            if (trim($language['icon']) != '') {
                                $icon_html = '<img src="' . $var->publicUrl . 'img/flags/' . $language['icon'] . '" alt="">';
                            }
                        }
                        echo '<li class="secondary-language"><a href="javascript:;" data-language="' . $key . '" id="btn_secondary_language_' . $key . '"><input type="radio" name="rdo_secondary_language_' . $key . '"> ' . $language["name"] . $icon_html . '</a></li>';
                    }
                    ?>
                </ul>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span
                        class="glyphicon glyphicon-user"></span> <?php echo Auth::getScreenName(); ?> <b
                        class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="javascript:;" id="menu_user_account"><?php echo Translate::get('Settings'); ?></a></li>
                    <li class="divider"></li>
                    <li>
                        <a href="<?php echo $var->baseUrl; ?>admin/html-output/logout"><?php echo Translate::get('Log out'); ?></a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>


    <div class="pixelmanager-main-left-column" style="display: none;">
        <?php
        $config = Config::getArray();
        if ($config['showBackendModulesTab'] === true) {
        ?>
        <div id="pages-modules-tab">
            <ul>
                <li><a href="#pixelmanager-main-left-pages"><?php echo Translate::get('Site structure'); ?></a></li>
                <li><a href="#pixelmanager-main-left-modules"><?php echo Translate::get('Modules'); ?></a></li>
            </ul>

            <div id="pixelmanager-main-left-modules"
                 class="pixelmanager-main-left-column-content-wrapper pixelmanager-main-left-column-content-wrapper-tab">
                <div class="pixelmanager-main-left-modules-content-wrapper"></div>
            </div>

            <?php
            }
            ?>
            <div id="pixelmanager-main-left-pages"
                 class="pixelmanager-main-left-column-content-wrapper <?php if ($config['showBackendModulesTab'] === true) { ?> pixelmanager-main-left-column-content-wrapper-tab <?php } ?>">
                <div id="page-tree"></div>
                <div id="page-tree-buttons">
                    <div class="btn-toolbar">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-default" id="btn_add_page"><span
                                    class="glyphicon glyphicon-file"></span> <?php echo Translate::get('Add page'); ?>
                            </button>
                        </div>
                        <div class="btn-group dropup" id="page-tree-edit-menu-container">
                            <a class="btn btn-sm dropdown-toggle btn-default" data-toggle="dropdown" href="#"
                               id="page-tree-edit-menu-button"><span
                                    class="glyphicon glyphicon-wrench"></span> <?php echo Translate::get('Edit'); ?>
                                <span class="caret"></span></a>
                            <ul class="dropdown-menu" id="page-tree-edit-menu">
                                <li><a href="#" id="btn_action_open"><?php echo Translate::get('Open'); ?></a></li>
                                <li><a href="#" id="btn_action_rename"><?php echo Translate::get('Rename'); ?></a></li>
                                <li class="divider"></li>
                                <li><a href="#" id="btn_action_publish"><?php echo Translate::get('Publish'); ?></a>
                                </li>
                                <li class="divider"></li>
                                <li><a href="#" id="btn_action_copy"><?php echo Translate::get('Copy'); ?></a></li>
                                <li><a href="#" id="btn_action_cut"><?php echo Translate::get('Cut'); ?></a></li>
                                <li><a href="#" id="btn_action_paste"><?php echo Translate::get('Paste'); ?></a></li>
                                <li class="divider"></li>
                                <li><a href="#" id="btn_action_delete"><?php echo Translate::get('Delete'); ?></a></li>
                                <li class="divider"></li>
                                <li><a href="#"
                                       id="btn_action_expand_all"><?php echo Translate::get('Expand all'); ?></a></li>
                                <li><a href="#"
                                       id="btn_action_collapse_all"><?php echo Translate::get('Collapse all'); ?></a>
                                </li>
                                <li class="divider"></li>
                                <li><a href="#"
                                       id="btn_action_properties"><?php echo Translate::get('Properties'); ?></a></li>
                            </ul>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default btn-sm show-tooltip-btn-group" id="btn_refresh"
                                    title="<?php echo Translate::get('Reload'); ?>"><span
                                    class="glyphicon glyphicon-refresh"></span></button>
                            <button class="btn btn-default btn-sm show-tooltip-btn-group" id="btn_info"
                                    data-toggle="button" title="<?php echo Translate::get('Show page information'); ?>">
                                <span class="glyphicon glyphicon-info-sign"></span></button>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $config = Config::getArray();
            if ($config['showBackendModulesTab'] === true) {
            ?>
        </div>
    <?php
    }
    ?>

    </div>
    <div class="pixelmanager-main-right-column">

        <div id="tabs">
            <ul>
            </ul>
        </div>

        <div class="pixelmanager-main-resize-overlay"></div>

    </div>
</div>

<div class="pixelmanager-main-synchronize">
    <div class="pixelmanager-main-synchronize-message">
        <div class="pixelmanager-main-synchronize-message-wrapper">
            <p class="pixelmanager-main-synchronize-message-icon"></p>

            <p class="pixelmanager-main-synchronize-message-text">
                <?php echo Translate::get('Synchronizing with the server,<br>please be patient'); ?>
            </p>
        </div>
    </div>
    <input type="hidden" id="pixelmanager-main-synchronize-error-request-id" value="">

    <div class="modal fade" id="pixelmanager-main-synchronize-error">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?php echo Translate::get('An error occured'); ?></h3>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-default"
                       id="btn_error_retry"><?php echo Translate::get('Retry'); ?></a>
                    <a href="javascript:;" class="btn btn-default"
                       id="btn_error_dismiss"><?php echo Translate::get('Cancel'); ?></a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="pixelmanager-main-synchronize-login">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?php echo Translate::get('You have been logged out, please log in again'); ?></h3>
                </div>
                <div class="modal-body">
                    <form action="#" class=".form-horizontal">
                        <label><?php echo Translate::get('Login'); ?></label>
                        <input type="text" disabled value="<?php echo Helpers::htmlEntities(Auth::getLoginName()); ?>">
                        <label><?php echo Translate::get('Password'); ?></label>
                        <input type="password" id="pixelmanager-main-synchronize-login-password">
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-default"
                       id="btn_login_retry"><?php echo Translate::get('Login'); ?></a>
                    <a href="javascript:;" class="btn btn-default"
                       id="btn_login_dismiss"><?php echo Translate::get('Cancel'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pixelmanager-main-pagetree-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo Translate::get('Delete selected page(s)'); ?></h3>
            </div>
            <div class="modal-body">
                <div class="pixelmanager-error-container"></div>
                <p>
                    <strong><?php echo Translate::get('Do you really want to delete the selected page(s)? This can\'t be undone!'); ?></strong>
                </p>

                <p id="pixelmanager-main-pagetree-delete-subpages">
                    <?php echo Translate::get('The selected pages contain <span id="pixelmanager-main-pagetree-delete-subpages-count">[unkown]</span> subpage(s), wich will be deleted as well.'); ?>
                    <br><br>
                    <label><input type="checkbox" name="delete-subpages"
                                  value="1"> <?php echo Translate::get('Yes, delete the selected pages with all subpages'); ?>
                    </label>
                </p>
            </div>
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-danger"
                   id="btn_pagtree_delete_ok"><?php echo Translate::get('Delete'); ?></a>
                <a href="javascript:;" class="btn btn-default"
                   id="btn_pagtree_delete_cancel"><?php echo Translate::get('Cancel'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pixelmanager-main-pagetree-publish">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="pixelmanager-main-pagetree-publish-form" method="post" action="#">
                <div class="modal-header">
                    <h3><?php echo Translate::get('Publish selected page(s)'); ?></h3>
                </div>
                <div class="modal-body">
                    <?php echo Translate::get('Do you really want to publish the selected page(s)? This can not be undone.'); ?>
                </div>
                <div class="modal-footer">
                    <label><input type="checkbox" name="publish-recursive"
                                  value="1"> <?php echo Translate::get('Publish the subpages as well'); ?></label>
                    <a href="javascript:;" class="btn btn-primary"
                       id="btn_pagtree_publish_ok"><?php echo Translate::get('Publish'); ?></a>
                    <a href="javascript:;" class="btn btn-default"
                       id="btn_pagtree_publish_cancel"><?php echo Translate::get('Cancel'); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (APPLICATION_CONTAINER == 'vagrant'): ?>
    <div class="modal fade" id="pixelmanager-main-sync-confirm">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="#">
                    <div class="modal-header">
                        <h3><?php echo Translate::get('Sync page data'); ?></h3>
                    </div>
                    <div class="modal-body">
                        <?php echo Translate::get('Do you really want to download the live data? This can not be undone.'); ?>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:;"
                           class="btn btn-primary btn-ok"><?php echo Translate::get('Synchronize'); ?></a>
                        <a href="javascript:;"
                           class="btn btn-default btn-cancel"><?php echo Translate::get('Cancel'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif ?>

<div id="page-tree-info">
    <span id="page-tree-info-icon"></span>

    <div id="page-tree-info-content"></div>
</div>

<div id="modal-popup-container"></div>

</body>
</html>
