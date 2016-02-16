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

?>
<script>
    var require = {
        baseUrl: "<?php echo $var->publicUrl; ?>js",
        paths: {
            'php-js': 'libs/php',
            'json2': 'libs/json2',
            'fix-console': 'libs/fix-console',
            'jquery': 'libs/jquery',
            'jquery-migrate': 'plugins/jquery-migrate',
            'elfinder': "../elfinder/js/elfinder.min",
            'elfinder-i18n-de': "../elfinder/js/i18n/elfinder.de"
        },
        shim: {
            'jquery': {
                deps: ['fix-console', 'json2', 'php-js'],
                exports: 'jQuery'
            },
            'jquery-migrate': ['jquery'],
            'plugins/jquery-ui': ['jquery'],
            'plugins/bootstrap': ['jquery'],
            'plugins/jquery-ui-bootstrap-no-conflict': ['jquery'],
            'plugins/cookie': ['jquery', 'jquery-migrate'],
            'plugins/data-editor-plugins-async-scripts-loaded': ['jquery', 'jquery-migrate'],
            'plugins/data-editor': ['jquery', 'jquery-migrate'],
            'plugins/hotkeys': ['jquery', 'jquery-migrate'],
            'plugins/jcrop': ['jquery', 'jquery-migrate'],
            'plugins/jstree': ['jquery', 'jquery-migrate'],
            'plugins/placeholder': ['jquery', 'jquery-migrate'],
            'elfinder': ['jquery', 'jquery-migrate'],
            'elfinder-i18n-de': {
                deps: ['jquery', 'jquery-migrate', 'elfinder']
            },
            'plugins/data-tables': ['jquery', 'jquery-migrate'],
            'plugins/data-tables-bootstrap': ['jquery', 'jquery-migrate', 'plugins/data-tables'],
            'plugins/data-tables-locale-sort': ['jquery', 'jquery-migrate', 'plugins/data-tables']
            <?php
                $loadDataEditorPlugins = array();
                $config = Config::getArray();
                $dataEditorPlugins = $config['dataEditorPlugins'];
                foreach($dataEditorPlugins as $key => $plugin) {
                    if (is_array($plugin)) {
                        echo ", '" . $var->baseUrl . $plugin['file'] . "': ['jquery', 'jquery-migrate']";
                        if (isset($plugin['additionalJavaScript'])) {
                            if (is_array($plugin['additionalJavaScript'])) {
                                foreach($plugin['additionalJavaScript'] as $add_js) {
                                    echo ", '" . $var->baseUrl . $add_js . "': ['jquery', 'jquery-migrate']";
                                }
                            } else {
                                   echo ", '" . $var->baseUrl . $plugin['additionalJavaScript'] . "': ['jquery', 'jquery-migrate']";
                            }
                        }
                    } else {
                        echo ", '" . $var->baseUrl . $plugin . "': ['jquery', 'jquery-migrate']";
                    }
                }
            ?>
        }
    };
</script>