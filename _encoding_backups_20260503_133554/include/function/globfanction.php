<?php
define("USER_EVENT", "events_users");
define("POKE_EVENT", "events_pokemon");

function arrayTime($t) {
    $mas = array(1 => array(1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60));
    return in_array($t, $mas[1]);
}

function arrayTimeOnline($t) {
    $mas = array(1 => range(2, 60, 2));
    return in_array($t, $mas[1]);
}

function datWeekday($dat) {
    return in_array(date('l'), explode(",", $dat));
}

function translitRus($string) {
    $converter = array(
        'Р В°'=>'a','Р В±'=>'b','Р Р†'=>'v','Р С–'=>'g','Р Т‘'=>'d','Р Вµ'=>'e','РЎвЂ'=>'e','Р В¶'=>'zh','Р В·'=>'z',
        'Р С‘'=>'i','Р в„–'=>'y','Р С”'=>'k','Р В»'=>'l','Р С'=>'m','Р Р…'=>'n','Р С•'=>'o','Р С—'=>'p','РЎР‚'=>'r',
        'РЎРѓ'=>'s','РЎвЂљ'=>'t','РЎС“'=>'u','РЎвЂћ'=>'f','РЎвЂ¦'=>'h','РЎвЂ '=>'c','РЎвЂЎ'=>'ch','РЎв‚¬'=>'sh','РЎвЂ°'=>'sch',
        'РЎРЉ'=>"'",'РЎвЂ№'=>'y','РЎР‰'=>"'",'РЎРЊ'=>'e','РЎР‹'=>'yu','РЎРЏ'=>'ya',
        'Р С’'=>'A','Р вЂ'=>'B','Р вЂ™'=>'V','Р вЂњ'=>'G','Р вЂќ'=>'D','Р вЂў'=>'E','Р Рѓ'=>'E','Р вЂ“'=>'Zh','Р вЂ”'=>'Z',
        'Р В'=>'I','Р в„ў'=>'Y','Р С™'=>'K','Р вЂє'=>'L','Р Сљ'=>'M','Р Сњ'=>'N','Р С›'=>'O','Р Сџ'=>'P','Р В '=>'R',
        'Р РЋ'=>'S','Р Сћ'=>'T','Р Р€'=>'U','Р В¤'=>'F','Р Тђ'=>'H','Р В¦'=>'C','Р В§'=>'Ch','Р РЃ'=>'Sh','Р В©'=>'Sch',
        'Р В¬'=>"'",'Р В«'=>'Y','Р Р„'=>"'",'Р В­'=>'E','Р В®'=>'Yu','Р Р‡'=>'Ya',
    );
    return strtr($string, $converter);
}

function obr_txt($text) {
    return trim(htmlspecialchars(stripslashes($text)));
}

function obr_chis($chs) {
    return trim(htmlspecialchars(stripslashes(abs(ceil($chs)))));
}

function rang_a($rang, $rang_b = 0) {
    if ($rang >= 0)       $rang_a = "Р СњР С•Р Р†Р С‘РЎвЂЎР С•Р С”";
    if ($rang >= 250)     $rang_a = "Р СњР В°РЎвЂЎР С‘Р Р…Р В°РЎР‹РЎвЂ°Р С‘Р в„–";
    if ($rang >= 8000)    $rang_a = "Р РЋРЎвЂљРЎР‚Р В°Р Р…РЎРѓРЎвЂљР Р†РЎС“РЎР‹РЎвЂ°Р С‘Р в„–";
    if ($rang >= 110000)  $rang_a = "Р С›Р С—РЎвЂ№РЎвЂљР Р…РЎвЂ№Р в„–";
    if ($rang >= 180000)  $rang_a = "Р Р€Р В·Р Р…Р В°Р Р†Р В°Р ВµР СРЎвЂ№Р в„–";
    if ($rang >= 250000)  $rang_a = "Р вЂ™Р ВµР В»Р С‘Р С”Р С‘Р в„–";
    if ($rang >= 380000)  $rang_a = "Р СњР ВµР С—Р С•Р В±Р ВµР Т‘Р С‘Р СРЎвЂ№Р в„–";
    if ($rang >= 500000)  $rang_a = "Р вЂєР ВµР С–Р ВµР Р…Р Т‘Р В°РЎР‚Р Р…РЎвЂ№Р в„–";

    // Р вЂР ВµР В·Р С•Р С—Р В°РЎРѓР Р…Р В°РЎРЏ Р С—РЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В°:
    if (!empty($rang_b) && is_numeric($rang_b) && $rang_b > 100000 && $rang > 800000) {
        $rang_a = "";
    }

    return $rang_a;
}

function rang_b($rang, $rang_a = 0) {
   $rang_b = ""; // РІвЂ С’ Р ВР СњР ВР В¦Р ВР С’Р вЂєР ВР вЂ”Р С’Р В¦Р ВР Р‡
    if ($rang < -500)         $rang_b = "Р СњР ВµРЎС“Р Т‘Р В°РЎвЂЎР Р…Р С‘Р С”";
    if ($rang >= 250)         $rang_b = "Р СћРЎР‚Р ВµР Р…Р ВµРЎР‚";
    if ($rang >= 5000)        $rang_b = "Р СџР С•Р С”Р ВµРЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚";
    if ($rang >= 11000)       $rang_b = "Р СџРЎР‚Р С•РЎвЂћР С‘";
    if ($rang >= 20000)       $rang_b = "Р СџР С•Р С”Р ВµР С—РЎР‚Р С•РЎвЂћР С‘";
    if ($rang >= 30000)       $rang_b = "Р СљР В°РЎРѓРЎвЂљР ВµРЎР‚";
    if ($rang >= 50000)       $rang_b = "Р СџР С•Р С”Р ВµР СР В°РЎРѓРЎвЂљР ВµРЎР‚";
    if ($rang_a > 800000 && $rang > 100000) $rang_b = "League Of Pokemons";

    return $rang_b;
}

function level_exp($lvl) {
    return round(60 * exp(2 + $lvl / 10) - 50);
}

function lvl_polos($lvl, $exp) {
    $a = level_exp($lvl);
    $b = ($lvl == 1) ? 0 : level_exp($lvl - 1);
    $c = $a - $b;
    $d = $a - $exp;
    $e = $c / 100;
    $f = $d / $e;
    return 100 - $f;
}

function plus_item($cool, $idItems, $user = false, $newtime = false) {
    if ($idItems > 0) {
        if ($user == false) $user = $_SESSION['id'];
        if ($newtime != false) $timers = $newtime; else $timers = "not";

        $items = first('SELECT id, count FROM items_users WHERE user_id=%d AND item_id=%d', $user, $idItems);
        $itemsIsset = first('SELECT cools, times, id FROM items WHERE id=%d', $idItems);
        $ex = explode('.', $itemsIsset['times']);

        if ($itemsIsset['cools'] == 1 && ($ex[0] > 0 || $ex[1] > 0)) {
            $tm = time() + (60 * 60 * $ex[0] * $ex[1]);
            $dtIT = $tm;
        } else {
            $dtIT = "not";
        }

        if (!$items && $itemsIsset['cools'] == 0) {
            insert('items_users', array(
                'item_id' => $itemsIsset['id'],
                'user_id' => $user,
                'count' => $cool,
                'dattimer' => $dtIT
            ));
        } elseif ($itemsIsset['cools'] == 1) {
            insert('items_users', array(
                'item_id' => $itemsIsset['id'],
                'user_id' => $user,
                'count' => 1,
                'dattimer' => $dtIT,
                'timers' => $timers
            ));
        } else {
            $x = $items['count'] + $cool;
            update('items_users', array('count' => $x), 'id=' . (int)$items['id'] . ' AND user_id=' . (int)$user);
        }
    }
}

function formatnum($str) {
    $s = (string)$str;
    $retstr = '';
    $now = 0;
    for ($j = strlen($s) - 1; $j >= 0; $j--) {
        if ($now < 3) {
            $now++;
            $retstr = $s[$j] . $retstr;
        } else {
            $now = 1;
            $retstr = $s[$j] . '.' . $retstr;
        }
    }
    return $retstr;
}
?>
