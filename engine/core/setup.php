<?php

error_reporting(E_ALL ^ E_DEPRECATED); // E_ALL ^ E_WARNING
setlocale(LC_ALL, $CONF['locale']);
mb_internal_encoding("UTF-8");
date_default_timezone_set('Europe/Kiev');

if (isset($_REQUEST[session_name()])){
    if (!preg_match('/^([a-zA-Z0-9])+$/', $_REQUEST[session_name()])){
        session_id(md5(rand(0, 99999) . time() . rand(0, 9999)));
    }
}

session_set_cookie_params(0, '/', $CONF['cookie_domain'], false, true);
ini_set('session.save_path',getcwd(). '/uploadfiles/cache/sessions');
session_start();

define('ROOT', $_SERVER['DOCUMENT_ROOT']);
define('SITE_ID', 'UID_ST');
define('BASKET_ID', 'ST_BASKET_BASKET');
define('SLUGS_STACK', SITE_ID . '_CATALOGUE_SLUGS');

function __autoload($class_name){
    global $CONF;

    if(preg_match('/^T(\w+)$/i', $class_name)){
        $file = preg_replace('/^T(\w+)$/i', '\\1', $class_name) . '.class.php';
    }
    elseif(preg_match('/^I(\w+)$/i', $class_name)){
        $file = preg_replace('/^I(\w+)$/i', '\\1', $class_name) . '.interface.php';
    }

    if(empty($file) == false){
        $path = sprintf('%s%s/%s', ROOT, $CONF['classes_dir'], strtolower($file));
        if(file_exists($path) && is_file($path)){
            require_once($path);
        }
    }
}

$MODS[1] = array('path' => 'settings', 'name' => 'Настройки');
$MODS[2] = array('path' => 'page', 'name' => 'Статические страницы');
$MODS[3] = array('path' => 'news', 'name' => 'Новости и публикации');
$MODS[4] = array('path' => 'adwords', 'name' => 'Рекламные баннеры');
$MODS[5] = array('path' => 'photos', 'name' => 'Фотографии и альбомы');
$MODS[6] = array('path' => 'paginator', 'name' => 'Пагинатор');
$MODS[7] = array('path' => 'forms', 'name' => 'Формы и обратная связь');