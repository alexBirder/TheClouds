CREATE TABLE IF NOT EXISTS `photos_issues` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `parent` smallint(5) unsigned NOT NULL,
  `level` tinyint(3) unsigned default '0',
  `url` varchar(128) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `sort` smallint(5) unsigned default NULL,
  `enabled` set('y','n') default 'y',
  `main` set('y','n') default 'y',
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `photos_issues_translate` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NULL,
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `photos_items` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `issue` smallint(5) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `prev` varchar(32) default NULL,
  `full` varchar(32) default NULL,
  `sort` smallint(5) unsigned default NULL,
  `enabled` set('y','n') default 'y',
  PRIMARY KEY  (`id`),
  KEY `issue` (`issue`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `photos_items_translate` (
  `id` smallint(5) unsigned NOT NULL,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;