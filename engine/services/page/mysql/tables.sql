CREATE TABLE IF NOT EXISTS `page_items` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `parent` smallint(5) unsigned NOT NULL,
  `level` tinyint(3) unsigned default '0',
  `url` varchar(128) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  `bg` varchar(255) default NULL,
  `sort` smallint(5) unsigned default NULL,
  `islink` set('y','n') default 'n',
  `menu` set('y','n') default 'y',
  `enabled` set('y','n') default 'y',
  `template` set('default','wide') default 'default',
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `page_items_translate` (
  `id` smallint(5) unsigned NOT NULL,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `meta_title` varchar(1000) default NULL,
  `meta_description` varchar(1000) default NULL,
  `meta_keywords` varchar(1000) default NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;