<?php

global $DB, $CONF;
$ADMINS = $DB->sql2array("SELECT `login`, `password`, `name`, `services` FROM settings_users WHERE `enabled` = 'y'");

if(!empty($_POST['login']) && !empty($_POST['password'])){
    if (authorizationCheck($_POST['login'], $_POST['password'], $ADMINS)){
        setcookie('mc_login', $_POST['login'], time() + 9200, '/', $CONF['cookie_domain']);
        setcookie('mc_password', $_POST['password'], time() + 9200, '/', $CONF['cookie_domain']);
        return;
    } else { authorizationForm(); exit; }
} elseif(!empty($_COOKIE['mc_login']) && !empty($_COOKIE['mc_password'])){
    if (authorizationCheck($_COOKIE['mc_login'], $_COOKIE['mc_password'], $ADMINS)){
        return;
    } else { authorizationForm(); exit; }} else { authorizationForm(); exit;
}

function authorizationCheck($admin_login, $admin_password, $ALL){
    global $CONF;
    if(empty($admin_login) || empty($admin_password)) return false;
    foreach($ALL as $ADMIN) {
        if($ADMIN['login'] == $admin_login && $ADMIN['password'] == $admin_password){
            setcookie('name', $ADMIN['name'], time() + 9200, '/', $CONF['cookie_domain']);
            return true;
        }
    }
    return false;
}

function authorizationForm(){ ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Тип контента на странице -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=540">

    <!-- Мета данные сайта и страниц -->
    <title>Административная панель</title>
    <meta name="description" content="Административная панель">

    <!-- Подключение шаблонных объектов -->
    <link rel="shortcut icon" type="image/x-icon" href="/engine/panel/img/logo.png">
    <link rel="stylesheet" href="/engine/panel/css/default.css" type="text/css" />
</head>
<body class="loginPage">

<section class="loginContainer">
    <div class="loginContainer--title">
        <div class="title--logo"><img src="/engine/panel/img/logo.png" alt=""></div>
        <div class="title--text">Административная панель</div>
    </div>
    <div class="loginContainer--areas">
        <form method="post" action="">
            <label>
                <span>Введите ваш логин:</span>
                <input type="text" name="login" class="areasTable--input large" required>
            </label>
            <label>
                <span>Введите ваш пароль:</span>
                <input type="password" name="password" class="areasTable--input large" required>
            </label>
            <div class="label">
                <button type="submit" class="button buttonRed buttonLarge buttonBlock">Войти в административную часть</button>
            </div>
        </form>
    </div>
</section>

</body>
</html>
<?php } ?>
