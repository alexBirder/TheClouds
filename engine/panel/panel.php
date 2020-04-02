<?php

// -------
ini_set("display_errors", 0);

// -------
require_once('login.php');

// -------
define("ADMIN_MODE", true);

// -------
if (isset($_GET['run']) && function_exists($_GET['run'])) { logout(); }
function logout(){
    global $CONF;
    setcookie('login', '', time() - 3600, '/', $CONF['cookie_domain']);
    setcookie('password', '', time() - 3600, '/', $CONF['cookie_domain']);
    setcookie('name', '', time() - 3600, '/', $CONF['cookie_domain']);
    unset($_COOKIE['login']);
    unset($_COOKIE['password']);
    unset($_COOKIE['name']);
    header('Location: /panel.php');
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=1260">
    <title>Административная панель</title>
    <link rel="stylesheet" href="/engine/panel/css/default.css" type="text/css" />
    <link rel="stylesheet" href="/engine/panel/css/font/flaticon.css" type="text/css" />
    <link rel="shortcut icon" type="image/x-icon" href="/engine/panel/img/logo.png">
    <script type="text/javascript" src="/engine/panel/js/model.jquery.js"></script>
    <script language="JavaScript" src="/engine/modules/Tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="/engine/panel/js/model.select.js"></script>
    <script type="text/javascript" src="/engine/panel/js/model.date.js"></script>
    <script type="text/javascript" src="/engine/panel/js/model.ajax.js"></script>
    <script type="text/javascript" src="/engine/panel/js/model.sort.js"></script>
    <script type="text/javascript" src="/engine/panel/js/model.translit.js"></script>
    <script type="text/javascript" src="/engine/panel/js/model.notify.js"></script>
    <script type="text/javascript" src="/engine/panel/js/model.common.js"></script>
</head>
<body>

<section class="containerCMS">
    <div class="containerCMS--left">
        <div class="containerCMS--logotype">
            <a href="/panel.php" class="logotype--image"><img src="/engine/panel/img/logo.png" alt=""></a>
            <a href="/panel.php" class="logotype--title">mCMS <b>rev. 1.0</b></a>
        </div>
        <div class="containerCMS--information">Система управления сайтом от компании <a href="http://mcdesign.ua">mc design</a>.</div>
        <div class="containerCMS--center">
            <a href="/" target="_blank" data-tooltip="Перейти на сайт"><i class="flaticon-unlink"></i></a>
            <a href="?module=settings&action=1" data-tooltip="Администраторы"><i class="flaticon-user"></i></a>
            <a href="?module=settings&action=2" data-tooltip="История"><i class="flaticon-clock"></i></a>
            <a href="?module=settings&action=3" data-tooltip="Настройки"><i class="flaticon-settings"></i></a>
            <a href="?run=logout" data-tooltip="Выйти"><i class="flaticon-logout"></i></a>
        </div>
        <div class="containerCMS--menu" id="menuReplace">
            <?php if(isset($_COOKIE['login'])){ $servicePtr = null; foreach($ALL_MODS as $k => $v) if($ALL_MODS[$k]->PrintMenu() && $module) $servicePtr = &$ALL_MODS[$k]; } else { printf("<script>$('#menuReplace').load(location.href+' #menuReplace>*','');</script>"); } ?>
        </div>
        <div class="containerCMS--copyrights">mCMS &copy; 2019<br />Все права защищены.</div>
    </div>
    <div class="containerCMS--right">
        <div class="containerCMS--body">
            <div class="containerCMS--body--content">
                <div class="containerCMS--header">
                    <?php if($module) {} else { print('<div class="containerCMS--header--left"><div class="containerCMS--header--status">Главная страница</div><div class="containerCMS--header--title">Добро пожаловать в административную панель!</div></div><div class="containerCMS--header--right"><a href="/" class="button buttonSmall buttonRed" target="_blank">Перейти на сайт</a></div>'); } ?>
                    <div class="containerCMS--header--left">
                        <div class="containerCMS--header--status"><?php if($module) $servicePtr->PrintNavigationString(); ?></div>
                        <div class="containerCMS--header--title"><?php if($module) $servicePtr->PrintTitle(); ?></div>
                    </div>
                    <div class="containerCMS--header--right"><?php if($module) $servicePtr->PrintSubMenu(); ?></div>
                </div>
                <div class="containerCMS--content">
                    <?php if($module) { ob_start(); $servicePtr->execute(); $ob_contents = ob_get_contents(); ob_end_clean(); $API->ShowMessages(); print($ob_contents); } else { $API->MainBlock(); } ?>
                </div>
            </div>
        </div>
    </div>
</section>

</body>
</html>

<?php $DB->close(); ?>