CREATE TABLE IF NOT EXISTS `settings_users` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `enabled` set('y','n') default 'y',
  `services` varchar(255),
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings_history` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `user` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `date` timestamp default current_timestamp,
  `action` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings_project` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `project_scripts` text NULL,
  `project_name` varchar (255) NULL,
  `project_url` varchar (255) NULL,
  `project_recaptcha` varchar (255) NULL,
  `project_bread` varchar (255) NULL,
  `project_favicon` varchar (255) NULL,
  `project_email` varchar (255) NULL,
  `project_email_reply` varchar (255) NULL,
  `project_status` set('y','n') default 'n',
  `policy_socials` set('y','n') default 'n',
  `policy_cookie` set('y','n') default 'n',
  `policy_confidence` set('y','n') default 'n',
  `change_menu` set('y','n') default 'n',
  `change_adblock` set('y','n') default 'n',
  `change_template` set('y','n') default 'n',
  `change_gzip` set('y','n') default 'n',
  `change_minify` set('y','n') default 'n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings_titles` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar (1000) NOT NULL,
  `description` varchar (1000) NOT NULL,
  `keywords` varchar (1000) NOT NULL,
  `lang` varchar (2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings_words` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `word` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings_words_translate` (
  `id` smallint(5) unsigned NOT NULL,
  `lang` char(2) NOT NULL,
  `word` varchar(255) NOT NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `settings_users` (`login`, `password`, `name`, `services`) VALUES ('admin', '$argon2id$v=19$m=255,t=1,p=1$c29tZXNhbHQ$gqzcyIVSwcVh2l6MeACNryRNyVhvyEg8vB61v730C2M', 'Разработчик', '');
INSERT INTO `settings_project` (`project_name`, `project_status`, `project_bread`) VALUES ('Новый проект', 'n', 'Главная');

CREATE TABLE IF NOT EXISTS `settings_menu` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `parent` smallint(5) unsigned NOT NULL,
  `level` tinyint(3) unsigned default '0',
  `title` varchar(255) NULL,
  `url` varchar(255) NULL,
  `enabled` set('y','n') default 'y',
  `sort` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings_menu_translate` (
  `id` smallint(5) unsigned NOT NULL,
  `lang` char(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  UNIQUE KEY `id_lang` (`id`, `lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;