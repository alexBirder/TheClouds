CREATE TABLE IF NOT EXISTS `forms_items` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` smallint(5) NOT NULL,
  `areas` varchar(255) NULL,
  `enabled` set('y','n') default 'y',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forms_areas` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` smallint(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NULL,
  `type` set('text','checkbox','radio','area') default 'text',
  `required` set('y','n') default 'y',
  `enabled` set('y','n') default 'y',
  `sort` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forms_attached` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `form` smallint(5) NOT NULL,
  `area` smallint(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;