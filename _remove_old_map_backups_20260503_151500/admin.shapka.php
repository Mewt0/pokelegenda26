<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="css/stylepl.css" type="text/css">
    <link rel="shortcut icon" href="favicon.ico">
    <script type="text/javascript" src="/script/jquery.js"></script>
    <script type="text/javascript" src="/script/md5.js"></script>
    <script type="text/javascript" src="/script/textJs.js"></script>
    <script type="text/javascript" src="/script/autr.js"></script>
    <style>
        /* Основные стили */
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            color: #000; /* Весь текст черный */
            margin: 0;
            padding: 0;
        }

        .inside {
            padding: 20px;
        }

        #bar {
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #autorizeDiv {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .formAut {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            color: #000; /* Черный текст */
            outline: none;
        }

        /* Стиль для черных кнопок */
        .btInp {
            padding: 8px 12px;
            background: #000; /* Черный фон */
            border: 1px solid #333; /* Тонкая граница */
            border-radius: 5px;
            color: #fff; /* Белый текст */
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-transform: uppercase;
        }

        .btInp:hover {
            background: #333; /* Темно-серый при наведении */
            border-color: #555;
            transform: translateY(-2px); /* Легкий подъем */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Тень при наведении */
        }

        .btInp:active {
            transform: translateY(0); /* Сбрасываем подъем при нажатии */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Уменьшаем тень */
        }

        .uptabl {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .menu-container {
            padding: 10px;
        }

        .menu {
            display: flex;
            justify-content: center;
        }

        .hmenu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .hmenu li {
            position: relative;
            display: inline-block;
            margin: 0 10px;
        }

        .hmenu li a {
            text-decoration: none;
            color: #000; /* Черный текст */
            padding: 10px 15px;
            display: block;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .hmenu li a:hover {
            background: #007BFF;
            color: #fff;
        }

        .hmenu li ul.sub {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff; /* Белый фон выпадающих списков */
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .hmenu li:hover ul.sub {
            display: block;
        }

        .hmenu li ul.sub li {
            display: block;
            margin: 5px 0;
        }

        .hmenu li ul.sub li a {
            padding: 8px 12px;
            background: #fff;
            border-radius: 5px;
            color: #000; /* Черный текст в выпадающем списке */
        }

        .hmenu li ul.sub li a:hover {
            background: #007BFF;
            color: #fff;
        }

        .midtabl {
            margin: 20px 0;
        }

        .news {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #autMes, #autherror {
            display: none;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            text-align: center;
        }

        #autMes {
            background: #28a745;
            color: #fff;
        }

        #autherror {
            background: #dc3545;
            color: #fff;
        }

        /* Приветствие в админке */
        .welcome-message {
            color: #000; /* Черный текст */
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Установка черного текста для всех элементов */
        h1, h2, h3, h4, h5, h6,
        p, span, label, td, th,
        li, a, input, select, textarea {
            color: #000; /* Черный текст везде */
        }

        /* Для выделенных элементов */
        .shadowtext, .shadowtext_tw {
            text-shadow: 1px 1px 2px black, 0 0 1em #DAA520;
            color: gold; /* Золотой как акцент */
            font-size: 3em;
            padding: 5px;
        }
    </style>
    <script type="text/javascript">
        function hrefs() {
            window.location.href = window.location.href;
        }
    </script>
</head>
<body>
    <div class="inside">
        <div id="bar">
            <div id="autorizeDiv">
                <?php if (!$autorize) { ?>
                    <form name="formAut" id="formAut" action="" method="post">
                        Логин: <input id="login" class="formAut" name="LOGIN" type="text" size="16" maxlength="16" value="">
                        Пароль: <input id="pass" class="formAut" name="PASSWORD" type="password" size="16" maxlength="16" value="">
                        <input id="autoChec" class="checAut" name="AUTO" type="checkbox"> Запомнить
                        <button id="confirmOk" type="button" class="btInp" onclick="autGo();">Войти</button>
                        <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=reg';">Регистрация</button>
                        <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=sendpass';">Восстановить пароль</button>
                    </form>
                <?php } else {
                    $alm = formatnum(coolseitems(2, $_SESSION['id']));
                    echo '<div class="welcome-message">Добро пожаловать, ' . $_SESSION['login'] . '!</div>
                    | <button type="button" class="btInp" onclick="window.open(\'/game.php?go=map\',\'mir\');">В игру</button>
                    | На вашем счету: <button type="button" class="btInp" onclick="location.href=\'/game.php?go=diamond_shop\';">' . ($alm ? $alm : '0') . ' - алмазов</button>
                    | Ваш статус: ' . textGroup(users_conect('groups')) . '
                    | <button type="button" class="btInp" onclick="location.href=\'/index.php?go=exits\';">Выйти</button>';
                } ?>
            </div>
            <div id="autMes" style="display:none;"></div>
            <div id="autherror" style="display:none;"></div>
        </div>

        <div class="uptabl"></div>
        <table class="uptabl" cellspacing="0">
            <tbody>
                <tr>
                    <td class="shapka-body"></td>
                </tr>
                <tr class="menu-container">
                    <td class="menu-container">
                        <div class="menu" id="menu">
                            <table class="hmenu">
                                <tbody>
                                    <tr class="hmenu">
                                        <td class="hmenu-left"></td>
                                        <td class="hmenu">
                                            <ul id="nav">
                                                <li class="top"><a href="/game.php?go=admingo&do=news" class="top_link"><span class="down" style="color:red">Новости и оповещения</span></a></li>
                                                <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Магазин</span></a>
                                                    <ul class="sub">
                                                        <li><a href="/game.php?go=admingo&do=pok">Приобретение покемонов</a></li>
                                                        <li><a href="/game.php?go=admingo&do=attak_pokes">Приобретение атак</a></li>
                                                        <li><a href="/game.php?go=admingo&do=attak_pokes&eggs=true">Приобретение яиц с атаками</a></li>
                                                    </ul>
                                                </li>
                                                <li class="top"><a href="/game.php?go=admingo&do=alm" id="shop" class="top_link"><span class="down">Счет алмазов</span></a></li>
                                                <li class="top"><a href="/game.php?go=admingo&do=gitem" id="shop" class="top_link"><span class="down">Выдача предметов</span></a></li>
                                                <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Логи</span></a>
                                                    <ul class="sub">
                                                        <li><a href="/game.php?go=admingo&do=logs">Логи обмена и продаж</a></li>
                                                        <li><a href="/game.php?go=admingo&do=log_alm">Логи пополнения алмазов</a></li>
                                                    </ul>
                                                </li>
                                                <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Турниры</span></a>
                                                    <ul class="sub">
                                                        <li><a href="/game.php?go=admingo&do=info_tur">Информация о турнирах</a></li>
                                                        <li><a href="/game.php?go=admingo&do=info_tur_user">Информация по участникам</a></li>
                                                        <li><a href="/game.php?go=admingo&do=medal">Списки медалей</a></li>
                                                    </ul>
                                                </li>
                                                <li class="top"><a href="http://forum.pokelegenda.ru/" class="top_link" target="_blank"><span class="down"><strong>Форум</strong></span></a>
                                                    <ul class="sub">
                                                        <li><a href="http://forum.pokelegenda.ru/" target="_blank">Форум</a></li>
                                                        <li><a href=".." target="_blank">Ресурсы</a></li>
                                                        <li><a href="/index.php?go=exits">Выход</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                            <td class="hmenu-right"></td>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="midtabl" cellpadding="0" cellspacing="6">
            <tbody>
                <tr>
                    <td class="news" align="center">
                        <noscript>
                            <font color="brown"><b>Для корректной работы сайта включите JavaScript в вашем браузере.</b></font><br>
                        </noscript>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
