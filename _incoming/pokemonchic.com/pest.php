<?php
declare(strict_types=1);
/**
 * pest.php / post.php РІР‚вЂќ Р вЂќР ВµР СР С•-РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В° FancyBox
 * PHP 8.x: РЎС“Р В±РЎР‚Р В°Р Р…РЎвЂ№ Р Р…Р ВµР Р…Р С•РЎР‚Р СР В°РЎвЂљР С‘Р Р†Р Р…РЎвЂ№Р Вµ РЎРѓРЎвЂљРЎР‚Р С•Р С”Р С‘, DOCTYPE Р С•Р В±Р Р…Р С•Р Р†Р В»РЎвЂР Р… Р Т‘Р С• HTML5,
 *          Р В·Р В°Р С”Р С•Р СР СР ВµР Р…РЎвЂљР С‘РЎР‚Р С•Р Р†Р В°Р Р…Р Р…РЎвЂ№Р в„– dead-code (tooltip.js) РЎС“Р Т‘Р В°Р В»РЎвЂР Р…,
 *          Р Т‘Р Р†Р В° Р С‘Р Т‘Р ВµР Р…РЎвЂљР С‘РЎвЂЎР Р…РЎвЂ№РЎвЂ¦ РЎвЂћР В°Р в„–Р В»Р В° Р С•Р В±РЎР‰Р ВµР Т‘Р С‘Р Р…Р ВµР Р…РЎвЂ№ Р Р† Р С•Р Т‘Р С‘Р Р….
 */

declare(strict_types=1);

// Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂЎР С‘Р С” AJAX-Р В·Р В°Р С”РЎР‚РЎвЂ№РЎвЂљР С‘РЎРЏ Р С•Р С”Р Р…Р В°
if (isset($_GET['id'])) {
    echo '<div onclick="parent.$.fn.fancybox.close();">Р вЂ”Р В°Р С”РЎР‚РЎвЂ№РЎвЂљРЎРЉ</div>';
    exit;
}

if (isset($_GET['ids'])) {
    echo 'Р СџР В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚ ids Р С—Р С•Р В»РЎС“РЎвЂЎР ВµР Р….';
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FancyBox РІР‚вЂќ Р Т‘Р ВµР СР С•</title>
    <link rel="stylesheet" href="fancybox/jquery.fancybox.css">
    <script src="fancybox/jquery-1.3.2.min.js"></script>
    <script src="fancybox/jquery.easing.1.3.js"></script>
    <script src="fancybox/jquery.fancybox-1.2.1.pack.js"></script>
    <script>
    $(document).ready(function () {
        $('a.iframe').fancybox({
            frameWidth:  400,
            frameHeight: 450
        });
    });
    </script>
    <style>
        html, body { font: normal 12px Tahoma; color: #333; }
        a { outline: none; }

        #tooltip {
            background: #fff;
            border: 1px solid #666;
            color: #333;
            font: menu;
            margin: 0;
            padding: 3px 5px;
            position: absolute;
            visibility: hidden;
        }

        #wrap {
            width: 500px;
            margin: 50px auto;
        }

        img {
            border: 1px solid #ccc;
            padding: 2px;
            margin: 10px 5px 10px 0;
        }

        .green { color: #060; font-size: 14px; }
    </style>
</head>
<body>
<div id="wrap">
    <a class="iframe" href="/pest.php?id=1" title="Р С›РЎвЂљР С”РЎР‚РЎвЂ№РЎвЂљРЎРЉ Р С•Р С”Р Р…Р С•">Р С›РЎвЂљР С”РЎР‚РЎвЂ№РЎвЂљРЎРЉ Р Р† FancyBox</a>
</div>
</body>
</html>
