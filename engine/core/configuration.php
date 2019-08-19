<?php

$CONF		= array();
$MODS		= array();
$ALL_MODS	= array();

//------------------------------------------------------------------------------

$CONF['db_host']		= "localhost";
$CONF['db_user']		= "theclouds_web";
$CONF['db_password']	= "Aldofaitev";
$CONF['db_name']		= "theclouds_web";

//------------------------------------------------------------------------------

$CONF['langs']			= array();
$CONF['langs']['ru']	= array('name' => 'Русский', 'main' => 1);
$CONF['langs']['ua']	= array('name' => 'Украинский', 'main' => 0);
$CONF['langs']['en']	= array('name' => 'Английский', 'main' => 0);

//------------------------------------------------------------------------------

$CONF['classes_dir']	= "/engine/classes";
$CONF['modules_dir']	= "/engine/modules";
$CONF['services_dir']	= "/engine/services";
$CONF['template_dir']	= "/templates/html";
$CONF['template_mir']	= "/templates/html/modules/";
$CONF['styles_dir']		= "/templates/css";
$CONF['images_dir']		= "/templates/img";
$CONF['javascript_dir']	= "/templates/js";
$CONF['upload_dir']		= "/uploadfiles";

//------------------------------------------------------------------------------

$CONF['locale']			= 'ru_RU.UTF8';
$CONF['locale_st']		= 'utf8';
$CONF['cookies_tpl']	= 'module_cookies.tpl';
$CONF['socials_tpl']	= 'module_socials.tpl';
$CONF['main_tpl']		= 'global_main.tpl';
$CONF['default_tpl']	= 'global_default.tpl';
$CONF['base_tpl']		= 'base';
$CONF['nav_separator']	= ' › ';
$CONF['cookie_domain']	= '.' . preg_replace('/www\./i', '', $_SERVER['SERVER_NAME']);