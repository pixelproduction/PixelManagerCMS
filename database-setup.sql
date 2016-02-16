-- PixelManager CMS (Community Edition)
-- Copyright (C) 2016 PixelProduction (http://www.pixelproduction.de)
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.

-- -----------------------------------------------------------
-- Table strucure
-- -----------------------------------------------------------

CREATE TABLE IF NOT EXISTS `pm_acl_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` text,
  `resource-id` text,
  `description` text,
  `user-groups-mode` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent-id` int(11) DEFAULT NULL,
  `name` text,
  `template-id` text,
  `visibility` tinyint(4) DEFAULT '0',
  `active` tinyint(4) DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `position` int(11) DEFAULT '0',
  `creation-date` int(11) DEFAULT NULL,
  `last-change-date` int(11) DEFAULT NULL,
  `last-publish-date` int(11) DEFAULT NULL,
  `creation-user-id` int(11) DEFAULT NULL,
  `creation-user-name` text,
  `last-change-user-id` int(11) DEFAULT NULL,
  `last-change-user-name` text,
  `cachable` tinyint(4) DEFAULT '0',
  `link-translated` tinyint(4) NOT NULL DEFAULT '0',
  `link-url` text,
  `link-new-window` tinyint(4) NOT NULL DEFAULT '0',
  `unique-id` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_page_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page-id` int(11) DEFAULT NULL,
  `language-id` text,
  `alias` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_page_cache` (
  `page-id` int(11) DEFAULT NULL,
  `language-id` text NOT NULL,
  `timestamp` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_page_captions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page-id` int(11) DEFAULT NULL,
  `language-id` text,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_page_translated_link_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page-id` int(11) DEFAULT NULL,
  `language-id` text,
  `link-url` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_page_visibility` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page-id` int(11) DEFAULT NULL,
  `language-id` text,
  `value` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_settings` (
  `json` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `privileges` tinyint(4) NOT NULL DEFAULT '0',
  `screenname` text NOT NULL,
  `login` text NOT NULL,
  `password` text NOT NULL,
  `preferred-language` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_users_to_user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user-id` int(11) DEFAULT NULL,
  `user-group-id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `level` tinyint(4) NOT NULL DEFAULT '0',
  `action-create` tinyint(4) NOT NULL DEFAULT '0',
  `action-edit` tinyint(4) NOT NULL DEFAULT '0',
  `action-publish` tinyint(4) NOT NULL DEFAULT '0',
  `action-delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pm_user_groups_to_acl_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user-group-id` int(11) DEFAULT NULL,
  `acl-resource-id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------------
-- Necessary inital data
-- -----------------------------------------------------------

INSERT INTO `pm_acl_resources` (`id`, `group`, `resource-id`, `description`, `user-groups-mode`) VALUES
(1, 'pages', '0', 'Root', 0);

INSERT INTO `pm_settings` (`json`) VALUES
('{"startPages":{"de":0},"errorPages":{"de":0},"useCache":false,"cacheLifetime":0}');

-- -----------------------------------------------------------
-- Initial user. Username: "demo", password: "demo"
-- -----------------------------------------------------------

INSERT INTO `pm_users` (`id`, `privileges`, `screenname`, `login`, `password`, `preferred-language`) VALUES
(1, 1, 'Administrator', 'demo', '$P$BFXYpC.ZbUn7UfCdpta0bWkQTyfd/J1', 'de');

-- -----------------------------------------------------------
-- Example content
-- -----------------------------------------------------------

INSERT INTO `pm_pages` (`id`, `parent-id`, `name`, `template-id`, `visibility`, `active`, `status`, `position`, `creation-date`, `last-change-date`, `last-publish-date`, `creation-user-id`, `creation-user-name`, `last-change-user-id`, `last-change-user-name`, `cachable`, `link-translated`, `link-url`, `link-new-window`, `unique-id`) VALUES
(1, 0, '__global-elements__', '__globalElements__', 0, 0, 2, 0, NULL, 1455618678, 1455618678, NULL, NULL, 1, 'Administrator', 0, 0, NULL, 0, NULL),
(2, 0, 'homepage', 'standard', 0, 1, 2, 1, 1455615285, 1455619419, 1455619422, 1, 'Administrator', 1, 'Administrator', 1, 0, '', 0, '');
