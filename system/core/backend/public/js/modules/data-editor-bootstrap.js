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

define(
    (function () {

        "use strict";

        var dependencies = [
            "require",
            "modules/translate",
            "jquery",
            "plugins/jquery-ui-bootstrap-no-conflict",
            "plugins/data-editor-plugins-async-scripts-loaded",
            "plugins/data-editor"
        ];
        for (var i = 0; i < parent.pixelmanagerGlobal.dataEditorPlugins.length; i++) {
            dependencies.push(parent.pixelmanagerGlobal.dataEditorPlugins[i]);
        }
        return (dependencies);
    })(),
    function (require, translate, $) {

        "use strict";

        for (var i = 0; i < parent.pixelmanagerGlobal.dataEditorPlugins.length; i++) {
            require(parent.pixelmanagerGlobal.dataEditorPlugins[i]);
        }

        function getLanguages(languagesConfig) {
            var languages = null;
            var counter = 0;
            if (typeof(languagesConfig) != 'undefined') {
                if (languagesConfig != null) {
                    if (typeof(languagesConfig.languages) != 'undefined') {
                        if (languagesConfig.languages != null) {
                            languages = [];
                            for (var langId in languagesConfig.languages) {
                                languages[counter] = langId;
                                counter++;
                            }
                        }
                    }
                }
            }
            return (languages);
        }

        function getStandardLanguage(languagesConfig) {
            if (typeof(languagesConfig) != 'undefined') {
                if (languagesConfig != null) {
                    if (typeof(languagesConfig.standardLanguage) != 'undefined') {
                        if (languagesConfig.standardLanguage != null) {
                            return (languagesConfig.standardLanguage);
                        }
                    }
                }
            }
            return (null);
        }

        function getPreferredLanguageSubstitutes(languagesConfig) {
            if (typeof(languagesConfig) != 'undefined') {
                if (languagesConfig != null) {
                    if (typeof(languagesConfig.preferredLanguageSubstitutes) != 'undefined') {
                        if (languagesConfig.preferredLanguageSubstitutes != null) {
                            return (languagesConfig.preferredLanguageSubstitutes);
                        }
                    }
                }
            }
            return (null);
        }

        function getLanguageIcons(languagesConfig) {
            if (typeof(languagesConfig) != 'undefined') {
                if (languagesConfig != null) {
                    if (typeof(languagesConfig.languageIcons) != 'undefined') {
                        if (languagesConfig.languageIcons != null) {
                            return (languagesConfig.languageIcons);
                        }
                    }
                }
            }
            return (null);
        }

        function getActiveLanguage(languagesConfig) {
            if (typeof(languagesConfig) != 'undefined') {
                if (languagesConfig != null) {
                    if (typeof(languagesConfig.activeLanguage) != 'undefined') {
                        if (languagesConfig.activeLanguage != null) {
                            return (languagesConfig.activeLanguage);
                        }
                    }
                }
            }
            return (null);
        }

        function getActiveSecondaryLanguage(languagesConfig) {
            if (typeof(languagesConfig) != 'undefined') {
                if (languagesConfig != null) {
                    if (typeof(languagesConfig.activeSecondaryLanguage) != 'undefined') {
                        if (languagesConfig.activeSecondaryLanguage != null) {
                            return (languagesConfig.activeSecondaryLanguage);
                        }
                    }
                }
            }
            return (null);
        }

        function loadLanguagesConfigFromPixelmanagerGlobal() {
            return {
                languages: parent.pixelmanagerGlobal.languages,
                standardLanguage: parent.pixelmanagerGlobal.standardLanguage,
                preferredLanguageSubstitutes: parent.pixelmanagerGlobal.preferredLanguageSubstitutes,
                languageIcons: parent.pixelmanagerGlobal.languageIcons,
                activeLanguage: parent.pixelmanagerGlobal.activeLanguage,
                activeSecondaryLanguage: parent.pixelmanagerGlobal.activeSecondaryLanguage
            };
        }

        return {

            createInstance: function (selector, content, structure, languagesConfig) {
                if (typeof(languagesConfig) == 'undefined') {
                    var languagesConfig = loadLanguagesConfigFromPixelmanagerGlobal();
                }
                $(selector).dataEditor({
                    'content': content,
                    'structure': structure,
                    'languages': getLanguages(languagesConfig),
                    'standardLanguage': getStandardLanguage(languagesConfig),
                    'preferredLanguageSubstitutes': getPreferredLanguageSubstitutes(languagesConfig),
                    'languageIcons': getLanguageIcons(languagesConfig),
                    'activeLanguage': getActiveLanguage(languagesConfig),
                    'activeSecondaryLanguage': getActiveSecondaryLanguage(languagesConfig),
                    'buttonAddTranslation': '<button class="data-editor-add-translation btn btn-default btn-xs" title="' + translate.get('Add a translation for the selected language') + '"><span class="glyphicon glyphicon-plus"></span></button>',
                    'buttonRemoveTranslation': '<button class="data-editor-remove-translation btn btn-default btn-xs" title="' + translate.get('Remove this translation') + '"><span class="glyphicon glyphicon-minus"></span></button>',
                    'helpTextPrepend': '<span class="data-editor-help-button"><span class="glyphicon glyphicon-question-sign show-tooltip" title="',
                    'helpTextAppend': '"></span></span>',
                    /*				'helpTextPrepend' : '<span class="data-editor-help-button"><span class="glyphicon glyphicon-question-sign"></span><span class="data-editor-help-message">',
                     'helpTextAppend' : '</span></span>',*/
                    'noTranslationAvailablePlaceholderText': translate.get('No translation defined yet'),
                    'arrays': {
                        'tableClasses': 'table table-striped table-bordered table-condensed',
                        'emptyContent': translate.get('No content yet'),
                        'subEditorHeadlineNew': translate.get('Insert new row'),
                        'subEditorHeadlineEdit': translate.get('Edit row'),
                        'buttons': '<div class="pagecontent-data-editor-array-new-button-container clearfix">' +
                        '<button class="btn btn-default btn-xs data-editor-array-add-row pull-left"><span class="glyphicon glyphicon-plus"></span>' + translate.get('Add new') + '</button>' +
                        '<div class="btn-group pull-left">' +
                        '<a class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" href="#">' +
                        '<span class="glyphicon glyphicon-wrench"></span> ' +
                        translate.get('Edit') +
                        ' <span class="caret"></span>' +
                        '</a>' +
                        '<ul class="dropdown-menu">' +
                        '<li><a href="javascript:;" class="data-editor-array-select-all">' + translate.get('Select all') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-select-none">' + translate.get('Select none') + '</a></li>' +
                        '<li class="divider"></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-selected-up">' + translate.get('Move selected up') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-selected-down">' + translate.get('Move slected down') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-selected-first">' + translate.get('Move selected to top') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-selected-last">' + translate.get('Move selected to bottom') + '</a></li>' +
                        '<li class="divider"></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-selected-delete">' + translate.get('Delete selected') + '</a></li>' +
                        '</ul>' +
                        '</div>' +
                        '<label><input type="checkbox" class="data-editor-array-insert-at-top"> ' + translate.get('Insert at top') + '</label>' +
                        '</div>',
                        'rowButtons': '<div class="data-editor-array-row-buttons">' +
                        '<div class="btn-group pull-right">' +
                        '<a class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" href="#">' +
                        '<span class="glyphicon glyphicon-wrench"></span>' +
                        '</a>' +
                        '<ul class="dropdown-menu">' +
                        '<li><a href="javascript:;" class="data-editor-array-row-button-up">' + translate.get('Move up') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-row-button-down">' + translate.get('Move down') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-row-button-first">' + translate.get('Move to top') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-row-button-last">' + translate.get('Move to bottom') + '</a></li>' +
                        '<li class="divider"></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-row-button-delete">' + translate.get('Delete') + '</a></li>' +
                        '</ul>' +
                        '</div>' +
                        '</div>',
                        'buttonsOrdered': '<div class="pagecontent-data-editor-array-new-button-container clearfix">' +
                        '<button class="btn btn-xs btn-default data-editor-array-add-row pull-left"><span class="glyphicon glyphicon-plus"></span>' + translate.get('Add new') + '</button>' +
                        '<div class="btn-group pull-left">' +
                        '<a class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" href="#">' +
                        '<span class="glyphicon glyphicon-wrench"></span> ' +
                        translate.get('Edit') +
                        ' <span class="caret"></span>' +
                        '</a>' +
                        '<ul class="dropdown-menu">' +
                        '<li><a href="javascript:;" class="data-editor-array-select-all">' + translate.get('Select all') + '</a></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-select-none">' + translate.get('Select none') + '</a></li>' +
                        '<li class="divider"></li>' +
                        '<li><a href="javascript:;" class="data-editor-array-selected-delete">' + translate.get('Delete selected') + '</a></li>' +
                        '</ul>' +
                        '</div>' +
                        '<input type="hidden" class="data-editor-array-insert-at-top" value="">' +
                        '</div>',
                        'rowButtonsOrdered': '<div class="data-editor-array-row-buttons">' +
                        '<div class="btn-group pull-right">' +
                        '<a class="btn btn-xs btn-default data-editor-array-row-button-delete" href="#" title="' + translate.get('Delete') + '">' +
                        '<span class="glyphicon glyphicon-remove"></span>' +
                        '</a>' +
                        '</div>' +
                        '</div>',
                        'subEditorButtons': '<div class="btn-toolbar">' +
                        '<button class="data-editor-array-ok btn btn-sm btn-primary"><span class="glyphicon glyphicon-ok icon-white"></span> ' + translate.get('Apply') + '</button> ' +
                        '<button class="data-editor-array-cancel btn btn-sm btn-default"><span class="glyphicon glyphicon-remove"></span> ' + translate.get('Cancel') + '</button>' +
                        '</div>'
                    }
                });
            }

        };

    });