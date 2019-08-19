CREATE TABLE IF NOT EXISTS `adwords_items` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `intro` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `image` varchar(255) default NULL,
  `position` set('top','bottom', 'right', 'left') default 'top',
  `enabled` set('y','n') default 'y',
  `sort` smallint(5) unsigned DEFAULT 0,
  PRIMARY KEY  (`id`),
  KEY (`sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `adwords_items_translate` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NULL,
  `intro` text NOT NULL,
  `url` varchar(255) NULL,
  `file` varchar(255) default NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
