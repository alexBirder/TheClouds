<!-- BEGIN: base -->
<!DOCTYPE html>
<html lang="{LANG}">
<head>
    <!-- Тип контента на странице -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">

    <!-- Мета данные сайта и страниц -->
    <title>{DOC_TITLE}</title>
    <meta name="description" content="{DESCRIPTION}">
    <meta name="keywords" content="{KEYWORDS}">

    <!-- Подключение шаблонных объектов -->
    <link rel="shortcut icon" type="image/x-icon" href="{FAVICON}">
    <link href="https://fonts.googleapis.com/css?family=Istok+Web:400,700&display=swap&subset=cyrillic-ext" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{STYLES_DIR}/icons/flaticon.css">
    <link rel="stylesheet" type="text/css" href="{STYLES_DIR}/default.engine.css">
    <link rel="stylesheet" type="text/css" href="{STYLES_DIR}/default.styles.css">
    <link rel="stylesheet" type="text/css" href="{STYLES_DIR}/default.slider.css">
    <link rel="stylesheet" type="text/css" href="{STYLES_DIR}/default.fancy.css">
    <link rel="stylesheet" type="text/css" href="{STYLES_DIR}/default.notify.css">
    <link rel="stylesheet" type="text/css" href="{STYLES_DIR}/default.select.css">

    <!-- Подключение шаблонных объектов -->
    <script src="{JAVASCRIPT_DIR}/jquery.model.js"></script>
    <script src="{JAVASCRIPT_DIR}/slider.model.js"></script>
    <script src="{JAVASCRIPT_DIR}/fancy.model.js"></script>
    <script src="{JAVASCRIPT_DIR}/mask.model.js"></script>
    <script src="{JAVASCRIPT_DIR}/select.model.js"></script>
    <script src="{JAVASCRIPT_DIR}/notify.model.js"></script>
    <script src="{JAVASCRIPT_DIR}/common.model.js"></script>
</head>
<body {MAIN}>

<header class="containerHeader">
    <div class="containerHeader--logotype"><a href="/" class="containerHeader--logo"></a></div>
    <div class="containerHeader--menu">
        <a href="/{LANG}/page/about.html">О компании</a>
        <a href="/{LANG}/page/tarify.html">Тарифы</a>
        <a href="/{LANG}/page/cooperation.html">Сотрудничество</a>
        <a href="/{LANG}/page/contacts.html">Контакты</a>
    </div>
    <div class="containerHeader--socials"><a href="#"><i class="flaticon-facebook-logo-button"></i></a></div>
    <div class="containerHeader--languages">
        <div class="languages--button">
            <div class="languages--button--name">RU</div>
            <div class="languages--button--icon"><span><i class="flaticon-right-arrow"></i></span></div>
        </div>
    </div>
    <div class="containerHeader--links">
        <div class="links--icon"><i class="flaticon-support"></i></div>
        <div class="links--name">Онлайн<br />поддержка</div>
    </div>
    <a href="https://panel.theclouds.pro/clientarea.php" class="containerHeader--links">
        <div class="links--icon"><i class="flaticon-login"></i></div>
        <div class="links--name">Панель<br />управления</div>
    </a>
</header>
{FILE {CURRENT_TEMPLATE}}
<div class="containerMain--bar">
    <div class="containerMain--bar--title">Почему выбирают нас?<small>Потому что мы предлагаем максимально качественные услуги по отличным ценам!</small></div>
    <div class="containerMain--bar--content">
        <div class="containerWrapper">
            <div class="sliderFooter">
                <div class="containerMain--bar--element"><b>100% Moneyback</b>Если у нас Ваши сайты не стали работать быстрее — возвращаем 100% оплаты! </div>
                <div class="containerMain--bar--element"><b>Мгновенная активация сервера</b>Через 1 минуту после оплаты Ваш сервер готов к работе</div>
                <div class="containerMain--bar--element"><b>UpTime 99,98%</b>Наши серверы всегда работают без простоев</div>
                <div class="containerMain--bar--element"><b>Техподдержка 24/7</b>Время ожидания ответа техподдержки менее 15 минут в любое время суток!</div>
                <div class="containerMain--bar--element"><b>Защита от DDoS</b>Все наши серверы снабжены защитой от DDoS-атак до 100 Гбит/с. Мы можем блокировать все виды DDoS-атак и гарантируем полную защиту серверов.</div>
            </div>
        </div>
    </div>
</div>

{FILE {COOKIES}}
{SCRIPTS}

</body>
</html>
<!-- END: base -->