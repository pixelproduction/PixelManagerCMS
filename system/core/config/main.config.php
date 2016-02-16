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

// Please do not make changes which are specific to a project here.
// Custom settings belong in /system/custom/config/main.config.php

return [

    // The following Version number relates to the core-system only.
    // Please DO NOT OVERWRITE in the custom-config.
    'version' => [
        'main'     => 1, // Major version number
        'sub'      => 2, // Minor version number
        'features' => 0, // number of added features since the last increment of the major or minor version number
        'bugfixes' => 0  // number of fixed bugs since the last increment of the major or minor version number
    ],
    'baseUrl' => '/',
    'autoLoader' => [
        'coreLibrary' => APPLICATION_ROOT . 'system/core/library/',
        'customLibrary' => [
            'path' => APPLICATION_ROOT . 'system/custom/library/',
            'subDir' => false,
            'except' => []
        ],
    ],
    'forceAdminHttps' => false,
    'standardProtocolForAbsoluteUrls' => 'http://',
    'PasswordHash' => [
        'IterationCount' => 8,
        'PortableHashes' => true
    ],
    'timezone' => 'Europe/Berlin',
    'omitStandardLanguageInPageUrl' => true,
    'allowPageAliases' => true,
    'pageLinkRedirectionResponseCode' => '301',
    'fileUtils' => [
        'useChmod' => true,
        'directoryMode' => 0777,
        'fileMode' => 0777
    ],
    'backendLanguages' => [
        'standard' => 'de',
        'list' => [
            'de' => [
                'name' => 'Deutsch',
                'locale' => 'de_DE',
                'translationServerside' => [APPLICATION_ROOT . 'system/core/backend/translation/german-serverside.php'],
                'translationClientside' => [APPLICATION_ROOT . 'system/core/backend/translation/german-clientside.php']
            ],
            'en' => [
                'name' => 'English',
                'locale' => 'en_US',
                'translationServerside' => [APPLICATION_ROOT . 'system/core/backend/translation/english-serverside.php'],
                'translationClientside' => [APPLICATION_ROOT . 'system/core/backend/translation/english-clientside.php']
            ]
        ]
    ],
    'frontendPlugins' => [
        'inheritData' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/InheritData.php',
            'className' => 'InheritDataPlugin'
        ],
        'dataEditorSingleLineText' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorSingleLineText.php',
            'className' => 'DataEditorSingleLineTextPlugin'
        ],
        'dataEditorMultiLineText' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorMultiLineText.php',
            'className' => 'DataEditorMultiLineTextPlugin'
        ],
        'dataEditorImage' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorImage.php',
            'className' => 'DataEditorImagePlugin'
        ],
        'dataEditorCheckbox' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorCheckbox.php',
            'className' => 'DataEditorCheckboxPlugin'
        ],
        'dataEditorCheckboxGroup' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorCheckboxGroup.php',
            'className' => 'DataEditorCheckboxGroupPlugin'
        ],
        'dataEditorDropdown' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorDropdown.php',
            'className' => 'DataEditorDropdownPlugin'
        ],
        'dataEditorRadioGroup' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorRadioGroup.php',
            'className' => 'DataEditorRadioGroupPlugin'
        ],
        'dataEditorDatePicker' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorDatePicker.php',
            'className' => 'DataEditorDatePickerPlugin'
        ],
        'dataEditorTinyMCE' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorTinyMCE.php',
            'className' => 'DataEditorTinyMCEPlugin'
        ],
        'dataEditorLink' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorLink.php',
            'className' => 'DataEditorLinkPlugin'
        ],
    ],
    'frontendModules' => [
        'navigation' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/modules/navigation.php',
            'className' => 'NavigationFrontendModule'
        ],
        'category' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/modules/category.php',
            'className' => 'CategoryFrontendModule'
        ],
        'breadcrumb' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/modules/breadcrumb.php',
            'className' => 'BreadcrumbFrontendModule'
        ],
    ],
    'dataModifiers' => [
        'mergeLanguages' => [
            'position' => 100,
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/data-modifiers/MergeLanguagesModifier.php',
            'className' => 'MergeLanguagesModifier'
        ],
        'sortArrays' => [
            'position' => 200,
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/data-modifiers/SortArraysModifier.php',
            'className' => 'SortArraysModifier'
        ],
        'dataEditorPlugins' => [
            'position' => 300,
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/data-modifiers/DataEditorPluginsModifier.php',
            'className' => 'DataEditorPluginsModifier'
        ],
    ],
    'backendPlugins' => [
        'dataEditorImageBackend' => [
            'classFile' => APPLICATION_ROOT . 'system/core/backend/plugins/DataEditorImageBackend.php',
            'className' => 'DataEditorImageBackendPlugin'
        ],
        'dataEditorColorPicker' => [
            'classFile' => APPLICATION_ROOT . 'system/core/frontend/plugins/DataEditorColorPicker.php',
            'className' => 'DataEditorColorPickerPlugin'
        ]
    ],
    'dataEditorPlugins' => [
        'hiddenData' => 'system/core/backend/public/js/plugins/data-editor-plugins/hiddenData.js',
        'infoText' => 'system/core/backend/public/js/plugins/data-editor-plugins/infoText.js',
        'singleLineText' => 'system/core/backend/public/js/plugins/data-editor-plugins/singleLineText.js',
        'multiLineText' => 'system/core/backend/public/js/plugins/data-editor-plugins/multiLineText.js',
        'image' => [
            'file' => 'system/core/backend/public/js/plugins/data-editor-plugins/image.js',
            'additionalJavaScript' => ['system/core/backend/public/js/plugins/jcrop.js'],
            'additionalCSS' => ['system/core/backend/public/css/libs/jcrop/jcrop.min.css']
        ],
        'checkbox' => 'system/core/backend/public/js/plugins/data-editor-plugins/checkbox.js',
        'checkboxGroup' => 'system/core/backend/public/js/plugins/data-editor-plugins/checkboxGroup.js',
        'radioGroup' => 'system/core/backend/public/js/plugins/data-editor-plugins/radioGroup.js',
        'dropdown' => 'system/core/backend/public/js/plugins/data-editor-plugins/dropdown.js',
        'datePicker' => [
            'file' => 'system/core/backend/public/js/plugins/data-editor-plugins/datePicker.js',
            'additionalJavaScript' => [
                'system/core/backend/public/js/plugins/bootstrap-datepicker.js',
                'system/core/backend/public/js/plugins/timepicker.js'
            ],
            'additionalCSS' => [
                'system/core/backend/public/css/libs/bootstrap-datepicker/datepicker3.css',
                'system/core/backend/public/css/libs/timepicker/timepicker.css'
            ]
        ],
        'tinyMCE' => [
            'file' => 'system/core/backend/public/js/plugins/data-editor-plugins/tinyMCE.js',
            'additionalJavaScript' => ['system/core/backend/public/tinymce/tinymce.min.js']
        ],
        'link' => 'system/core/backend/public/js/plugins/data-editor-plugins/link.js',
        'colorPicker' => [
            'file' => 'system/core/backend/public/js/plugins/data-editor-plugins/colorPicker.js',
            'additionalJavaScript' => ['system/core/backend/public/js/plugins/bootstrap-colorpicker.js'],
            'additionalCSS' => ['system/core/backend/public/css/libs/color-picker/bootstrap-colorpicker.min.css'],
        ],
        'assignDataTableRows' => 'system/core/backend/public/js/plugins/data-editor-plugins/assignDataTableRows.js',
    ],
    'backendModules' => [],
    'backendModulesTabGroups' => [],
    'showBackendModulesMenu' => false,
    'showBackendModulesTab' => false,
    'frontendRouter' => [
        'classFile' => APPLICATION_ROOT . 'system/core/library/StandardFrontendRouter.php',
        'className' => 'StandardFrontendRouter'
    ],
    'frontendController' => [
        'classFile' => APPLICATION_ROOT . 'system/core/library/StandardFrontendController.php',
        'className' => 'StandardFrontendController'
    ],
    'pageBuilder' => [
        'classFile' => APPLICATION_ROOT . 'system/core/library/StandardPageBuilder.php',
        'className' => 'StandardPageBuilder'
    ],
    'jpegQuality' => 85,
    'backendBranding' => [
        'logo' => 'system/core/backend/public/img/pixelproduction-logo.png',
        'companyName' => 'PixelProduction',
        'linkToAboutPage' => true,
    ],
    'elFinderThumbnailSize' => 48,
];
