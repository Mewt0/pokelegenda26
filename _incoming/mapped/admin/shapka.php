<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Р СџР В°Р Р…Р ВµР В»РЎРЉ Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚Р В°</title>
    <link rel="stylesheet" href="css/stylepl.css" type="text/css">
    <link rel="shortcut icon" href="favicon.ico">
    <script type="text/javascript" src="/script/jquery.js"></script>
    <script type="text/javascript" src="/script/md5.js"></script>
    <script type="text/javascript" src="/script/textJs.js"></script>
    <script type="text/javascript" src="/script/autr.js"></script>
    <style>
        /* Р С›РЎРѓР Р…Р С•Р Р†Р Р…РЎвЂ№Р Вµ РЎРѓРЎвЂљР С‘Р В»Р С‘ */
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            color: #000; /* Р вЂ™Р ВµРЎРѓРЎРЉ РЎвЂљР ВµР С”РЎРѓРЎвЂљ РЎвЂЎР ВµРЎР‚Р Р…РЎвЂ№Р в„– */
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
            color: #000; /* Р В§Р ВµРЎР‚Р Р…РЎвЂ№Р в„– РЎвЂљР ВµР С”РЎРѓРЎвЂљ */
            outline: none;
        }

        /* Р РЋРЎвЂљР С‘Р В»РЎРЉ Р Т‘Р В»РЎРЏ РЎвЂЎР ВµРЎР‚Р Р…РЎвЂ№РЎвЂ¦ Р С”Р Р…Р С•Р С—Р С•Р С” */
        .btInp {
            padding: 8px 12px;
            background: #000; /* Р В§Р ВµРЎР‚Р Р…РЎвЂ№Р в„– РЎвЂћР С•Р Р… */
            border: 1px solid #333; /* Р СћР С•Р Р…Р С”Р В°РЎРЏ Р С–РЎР‚Р В°Р Р…Р С‘РЎвЂ Р В° */
            border-radius: 5px;
            color: #fff; /* Р вЂР ВµР В»РЎвЂ№Р в„– РЎвЂљР ВµР С”РЎРѓРЎвЂљ */
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-transform: uppercase;
        }

        .btInp:hover {
            background: #333; /* Р СћР ВµР СР Р…Р С•-РЎРѓР ВµРЎР‚РЎвЂ№Р в„– Р С—РЎР‚Р С‘ Р Р…Р В°Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р С‘ */
            border-color: #555;
            transform: translateY(-2px); /* Р вЂєР ВµР С–Р С”Р С‘Р в„– Р С—Р С•Р Т‘РЎР‰Р ВµР С */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Р СћР ВµР Р…РЎРЉ Р С—РЎР‚Р С‘ Р Р…Р В°Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р С‘ */
        }

        .btInp:active {
            transform: translateY(0); /* Р РЋР В±РЎР‚Р В°РЎРѓРЎвЂ№Р Р†Р В°Р ВµР С Р С—Р С•Р Т‘РЎР‰Р ВµР С Р С—РЎР‚Р С‘ Р Р…Р В°Р В¶Р В°РЎвЂљР С‘Р С‘ */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Р Р€Р СР ВµР Р…РЎРЉРЎв‚¬Р В°Р ВµР С РЎвЂљР ВµР Р…РЎРЉ */
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
            color: #000; /* Р В§Р ВµРЎР‚Р Р…РЎвЂ№Р в„– РЎвЂљР ВµР С”РЎРѓРЎвЂљ */
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
            background: #fff; /* Р вЂР ВµР В»РЎвЂ№Р в„– РЎвЂћР С•Р Р… Р Р†РЎвЂ№Р С—Р В°Р Т‘Р В°РЎР‹РЎвЂ°Р С‘РЎвЂ¦ РЎРѓР С—Р С‘РЎРѓР С”Р С•Р Р† */
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
            color: #000; /* Р В§Р ВµРЎР‚Р Р…РЎвЂ№Р в„– РЎвЂљР ВµР С”РЎРѓРЎвЂљ Р Р† Р Р†РЎвЂ№Р С—Р В°Р Т‘Р В°РЎР‹РЎвЂ°Р ВµР С РЎРѓР С—Р С‘РЎРѓР С”Р Вµ */
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

        /* Р СџРЎР‚Р С‘Р Р†Р ВµРЎвЂљРЎРѓРЎвЂљР Р†Р С‘Р Вµ Р Р† Р В°Р Т‘Р СР С‘Р Р…Р С”Р Вµ */
        .welcome-message {
            color: #000; /* Р В§Р ВµРЎР‚Р Р…РЎвЂ№Р в„– РЎвЂљР ВµР С”РЎРѓРЎвЂљ */
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Р Р€РЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С”Р В° РЎвЂЎР ВµРЎР‚Р Р…Р С•Р С–Р С• РЎвЂљР ВµР С”РЎРѓРЎвЂљР В° Р Т‘Р В»РЎРЏ Р Р†РЎРѓР ВµРЎвЂ¦ РЎРЊР В»Р ВµР СР ВµР Р…РЎвЂљР С•Р Р† */
        h1, h2, h3, h4, h5, h6,
        p, span, label, td, th,
        li, a, input, select, textarea {
            color: #000; /* Р В§Р ВµРЎР‚Р Р…РЎвЂ№Р в„– РЎвЂљР ВµР С”РЎРѓРЎвЂљ Р Р†Р ВµР В·Р Т‘Р Вµ */
        }

        /* Р вЂќР В»РЎРЏ Р Р†РЎвЂ№Р Т‘Р ВµР В»Р ВµР Р…Р Р…РЎвЂ№РЎвЂ¦ РЎРЊР В»Р ВµР СР ВµР Р…РЎвЂљР С•Р Р† */
        .shadowtext, .shadowtext_tw {
            text-shadow: 1px 1px 2px black, 0 0 1em #DAA520;
            color: gold; /* Р вЂ”Р С•Р В»Р С•РЎвЂљР С•Р в„– Р С”Р В°Р С” Р В°Р С”РЎвЂ Р ВµР Р…РЎвЂљ */
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
                        Р вЂєР С•Р С–Р С‘Р Р…: <input id="login" class="formAut" name="LOGIN" type="text" size="16" maxlength="16" value="">
                        Р СџР В°РЎР‚Р С•Р В»РЎРЉ: <input id="pass" class="formAut" name="PASSWORD" type="password" size="16" maxlength="16" value="">
                        <input id="autoChec" class="checAut" name="AUTO" type="checkbox"> Р вЂ”Р В°Р С—Р С•Р СР Р…Р С‘РЎвЂљРЎРЉ
                        <button id="confirmOk" type="button" class="btInp" onclick="autGo();">Р вЂ™Р С•Р в„–РЎвЂљР С‘</button>
                        <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=reg';">Р В Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎРЏ</button>
                        <button id="confirmOk" type="button" class="btInp" onclick="window.location.href='index.php?go=sendpass';">Р вЂ™Р С•РЎРѓРЎРѓРЎвЂљР В°Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРЉ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ</button>
                    </form>
                <?php } else {
                    $alm = formatnum(coolseitems(2, $_SESSION['id']));
                    echo '<div class="welcome-message">Р вЂќР С•Р В±РЎР‚Р С• Р С—Р С•Р В¶Р В°Р В»Р С•Р Р†Р В°РЎвЂљРЎРЉ, ' . $_SESSION['login'] . '!</div>
                    | <button type="button" class="btInp" onclick="window.open(\'/game\',\'mir\');">Р вЂ™ Р С‘Р С–РЎР‚РЎС“</button>
                    | Р СњР В° Р Р†Р В°РЎв‚¬Р ВµР С РЎРѓРЎвЂЎР ВµРЎвЂљРЎС“: <button type="button" class="btInp" onclick="location.href=\'/game.php?go=diamond_shop\';">' . ($alm ? $alm : '0') . ' - Р В°Р В»Р СР В°Р В·Р С•Р Р†</button>
                    | Р вЂ™Р В°РЎв‚¬ РЎРѓРЎвЂљР В°РЎвЂљРЎС“РЎРѓ: ' . textGroup(users_conect('groups')) . '
                    | <button type="button" class="btInp" onclick="location.href=\'/index.php?go=exits\';">Р вЂ™РЎвЂ№Р в„–РЎвЂљР С‘</button>';
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
                                                <li class="top"><a href="/game.php?go=admingo&do=news" class="top_link"><span class="down" style="color:red">Р СњР С•Р Р†Р С•РЎРѓРЎвЂљР С‘ Р С‘ Р С•Р С—Р С•Р Р†Р ВµРЎвЂ°Р ВµР Р…Р С‘РЎРЏ</span></a></li>
                                                <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Р СљР В°Р С–Р В°Р В·Р С‘Р Р…</span></a>
                                                    <ul class="sub">
                                                        <li><a href="/game.php?go=admingo&do=pok">Р СџРЎР‚Р С‘Р С•Р В±РЎР‚Р ВµРЎвЂљР ВµР Р…Р С‘Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</a></li>
                                                        <li><a href="/game.php?go=admingo&do=attak_pokes">Р СџРЎР‚Р С‘Р С•Р В±РЎР‚Р ВµРЎвЂљР ВµР Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”</a></li>
                                                        <li><a href="/game.php?go=admingo&do=attak_pokes&eggs=true">Р СџРЎР‚Р С‘Р С•Р В±РЎР‚Р ВµРЎвЂљР ВµР Р…Р С‘Р Вµ РЎРЏР С‘РЎвЂ  РЎРѓ Р В°РЎвЂљР В°Р С”Р В°Р СР С‘</a></li>
                                                    </ul>
                                                </li>
                                                <li class="top"><a href="/game.php?go=admingo&do=alm" id="shop" class="top_link"><span class="down">Р РЋРЎвЂЎР ВµРЎвЂљ Р В°Р В»Р СР В°Р В·Р С•Р Р†</span></a></li>
                                                <li class="top"><a href="/game.php?go=admingo&do=gitem" id="shop" class="top_link"><span class="down">Р вЂ™РЎвЂ№Р Т‘Р В°РЎвЂЎР В° Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†</span></a></li>
                                                <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Р вЂєР С•Р С–Р С‘</span></a>
                                                    <ul class="sub">
                                                        <li><a href="/game.php?go=admingo&do=logs">Р вЂєР С•Р С–Р С‘ Р С•Р В±Р СР ВµР Р…Р В° Р С‘ Р С—РЎР‚Р С•Р Т‘Р В°Р В¶</a></li>
                                                        <li><a href="/game.php?go=admingo&do=log_alm">Р вЂєР С•Р С–Р С‘ Р С—Р С•Р С—Р С•Р В»Р Р…Р ВµР Р…Р С‘РЎРЏ Р В°Р В»Р СР В°Р В·Р С•Р Р†</a></li>
                                                    </ul>
                                                </li>
                                                <li class="top"><a href="#" id="shop" class="top_link"><span class="down">Р СћРЎС“РЎР‚Р Р…Р С‘РЎР‚РЎвЂ№</span></a>
                                                    <ul class="sub">
                                                        <li><a href="/game.php?go=admingo&do=info_tur">Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С• РЎвЂљРЎС“РЎР‚Р Р…Р С‘РЎР‚Р В°РЎвЂ¦</a></li>
                                                        <li><a href="/game.php?go=admingo&do=info_tur_user">Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С—Р С• РЎС“РЎвЂЎР В°РЎРѓРЎвЂљР Р…Р С‘Р С”Р В°Р С</a></li>
                                                        <li><a href="/game.php?go=admingo&do=medal">Р РЋР С—Р С‘РЎРѓР С”Р С‘ Р СР ВµР Т‘Р В°Р В»Р ВµР в„–</a></li>
                                                    </ul>
                                                </li>
                                                <li class="top"><a href="http://forum.pokelegenda.ru/" class="top_link" target="_blank"><span class="down"><strong>Р В¤Р С•РЎР‚РЎС“Р С</strong></span></a>
                                                    <ul class="sub">
                                                        <li><a href="http://forum.pokelegenda.ru/" target="_blank">Р В¤Р С•РЎР‚РЎС“Р С</a></li>
                                                        <li><a href=".." target="_blank">Р В Р ВµРЎРѓРЎС“РЎР‚РЎРѓРЎвЂ№</a></li>
                                                        <li><a href="/index.php?go=exits">Р вЂ™РЎвЂ№РЎвЂ¦Р С•Р Т‘</a></li>
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
                            <font color="brown"><b>Р вЂќР В»РЎРЏ Р С”Р С•РЎР‚РЎР‚Р ВµР С”РЎвЂљР Р…Р С•Р в„– РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂ№ РЎРѓР В°Р в„–РЎвЂљР В° Р Р†Р С”Р В»РЎР‹РЎвЂЎР С‘РЎвЂљР Вµ JavaScript Р Р† Р Р†Р В°РЎв‚¬Р ВµР С Р В±РЎР‚Р В°РЎС“Р В·Р ВµРЎР‚Р Вµ.</b></font><br>
                        </noscript>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
