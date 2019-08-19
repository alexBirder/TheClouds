CREATE TABLE IF NOT EXISTS `news_issues` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `parent` smallint(5) unsigned NOT NULL,
  `level` tinyint(3) unsigned default '0',
  `url` varchar(128) NOT NULL,
  `title` varchar(255) NOT NULL,
  `sort` smallint(5) unsigned default NULL,
  `enabled` set('y','n') default 'y',
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news_issues_translate` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news_items` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `parent` smallint(5) unsigned NULL,
  `issue` smallint(5) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `intro` text NOT NULL,
  `text` mediumtext NOT NULL,
  `author` varchar(255) default NULL,
  `source` varchar(255) default NULL,
  `link` varchar(255) default NULL,
  `prev` char(32) default NULL,
  `full` char(32) default NULL,
  `images` set('0','1','2','3') default '3',
  `date` date NOT NULL,
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  `sort` smallint(5) unsigned default NULL,
  `enabled` set('y','n') default 'y',
  `main_list` set('y','n') default 'n',
  `main_top` set('y','n') default 'n',
  PRIMARY KEY  (`id`),
  KEY (`issue`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news_items_translate` (
  `id` smallint(5) unsigned NOT NULL,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `intro` text NOT NULL,
  `text` mediumtext NOT NULL,
  `author` varchar(255) NULL,
  `source` varchar(255) NULL,
  `link` varchar(255) NULL,
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;