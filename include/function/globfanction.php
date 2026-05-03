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
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z',
        'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
        'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch',
        'ь'=>"'",'ы'=>'y','ъ'=>"'",'э'=>'e','ю'=>'yu','я'=>'ya',
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'E','Ж'=>'Zh','З'=>'Z',
        'И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O','П'=>'P','Р'=>'R',
        'С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'C','Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch',
        'Ь'=>"'",'Ы'=>'Y','Ъ'=>"'",'Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
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
    if ($rang >= 0)       $rang_a = "Новичок";
    if ($rang >= 250)     $rang_a = "Начинающий";
    if ($rang >= 8000)    $rang_a = "Странствующий";
    if ($rang >= 110000)  $rang_a = "Опытный";
    if ($rang >= 180000)  $rang_a = "Узнаваемый";
    if ($rang >= 250000)  $rang_a = "Великий";
    if ($rang >= 380000)  $rang_a = "Непобедимый";
    if ($rang >= 500000)  $rang_a = "Легендарный";

    // Безопасная проверка:
    if (!empty($rang_b) && is_numeric($rang_b) && $rang_b > 100000 && $rang > 800000) {
        $rang_a = "";
    }

    return $rang_a;
}

function rang_b($rang, $rang_a = 0) {
   $rang_b = ""; // ← ИНИЦИАЛИЗАЦИЯ
    if ($rang < -500)         $rang_b = "Неудачник";
    if ($rang >= 250)         $rang_b = "Тренер";
    if ($rang >= 5000)        $rang_b = "Покетренер";
    if ($rang >= 11000)       $rang_b = "Профи";
    if ($rang >= 20000)       $rang_b = "Покепрофи";
    if ($rang >= 30000)       $rang_b = "Мастер";
    if ($rang >= 50000)       $rang_b = "Покемастер";
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
