<?php
function funcItemEventDay($day){
    if ($day <= 0) return false;

    $dates = (int)$day;
    $a = array();

    switch ($day){
      case 1:
        $a  = array(1=>array('id'=>1, 'cool'=>100), 2=>array('id'=>3, 'cool'=>1));
       break;
      case 2:
        $a  = array(1=>array('id'=>1, 'cool'=>500), 2=>array('id'=>3, 'cool'=>2));
       break;
      case 3:
        $a  = array(1=>array('id'=>1, 'cool'=>1000), 2=>array('id'=>3, 'cool'=>4), 3=>array('id'=>6, 'cool'=>1));
       break;
      case 4: // 4 день
        $a  = array(1=>array('id'=>1, 'cool'=>1000), 2=>array('id'=>15, 'cool'=>2), 3=>array('id'=>6, 'cool'=>2));
       break;
      case 5: // 5 день
        $a  = array(1=>array('id'=>15, 'cool'=>1),
                    2=>array('id'=>3, 'cool'=>3),
                    3=>array('id'=>6, 'cool'=>1),
                    4=>array('id'=>7, 'cool'=>1));
       break;
      case 6: // 6 день
        $a  = array(1=>array('id'=>15, 'cool'=>2), // Энергетики
                    2=>array('id'=>3, 'cool'=>rand(2,4)), // Покеболы
                    3=>array('id'=>15, 'cool'=>rand(1,2)), // Энергетики
                    4=>array('id'=>7, 'cool'=>1)); // Зел. Конфета
       break;
      case 7: // 7 день
        $a  = array(1=>array('id'=>7, 'cool'=>2), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>rand(3,4)), // Покеболы
                    3=>array('id'=>15, 'cool'=>rand(2,3)), // Энергетики
                    4=>array('id'=>9, 'cool'=>1)); // Фиол. Конфета
       break;
      case 8: // 8 день
        $a  = array(1=>array('id'=>7, 'cool'=>2), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>4), // Покеболы
                    3=>array('id'=>6, 'cool'=>2), // Жел. Конфеты
                    4=>array('id'=>9, 'cool'=>1)); // Фиол. Конфеты
       break;
      case 9:
        $a  = array(1=>array('id'=>7, 'cool'=>2), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>6, 'cool'=>3), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>3), // Энергетики
                    5=>array('id'=>9, 'cool'=>1)); // Фиол. Конфеты
       break;
      case 10:
        $a  = array(1=>array('id'=>7, 'cool'=>3), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>6, 'cool'=>3), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>3), // Энергетики
                    5=>array('id'=>9, 'cool'=>2), // Фиол. Конфеты
                    6=>array('id'=>10, 'cool'=>1)); // Роз. Конфеты
       break;
      case 11:
        $a  = array(1=>array('id'=>7, 'cool'=>3), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(2,3)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>rand(2,4)), // Энергетики
                    5=>array('id'=>9, 'cool'=>rand(1,2)), // Фиол. Конфеты
                    6=>array('id'=>10, 'cool'=>1)); // Роз. Конфеты
       break;
      case 12:
        $a  = array(1=>array('id'=>7, 'cool'=>3), // Монеты
                    2=>array('id'=>3, 'cool'=>rand(4,5)), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(2,4)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>rand(2,4)), // Энергетики
                    5=>array('id'=>9, 'cool'=>rand(1,3)), // Фиол. Конфеты
                    6=>array('id'=>10, 'cool'=>1)); // Роз. Конфеты
       break;
      case 13:
        $a  = array(1=>array('id'=>7, 'cool'=>3), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>6, 'cool'=>3), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>3), // Энергетики
                    5=>array('id'=>9, 'cool'=>3), // Фиол. Конфеты
                    6=>array('id'=>10, 'cool'=>1)); // Роз. Конфеты
       break;
      case 14:
        $a  = array(1=>array('id'=>7, 'cool'=>3), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>6, 'cool'=>4), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>3), // Энергетики
                    5=>array('id'=>9, 'cool'=>3), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1)); // Суш. клевер
       break;
      case 15:
        $a  = array(1=>array('id'=>7, 'cool'=>3), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>6, 'cool'=>4), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>3), // Энергетики
                    5=>array('id'=>9, 'cool'=>3), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>19, 'cool'=>1), // Клевер
                    8=>array('id'=>20, 'cool'=>1)); // Свеж. Клевер
       break;
      case 16:
        $a  = array(1=>array('id'=>7, 'cool'=>4), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>rand(5,6)), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(4,5)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>rand(3,5)), // Энергетики
                    5=>array('id'=>9, 'cool'=>rand(3,4)), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>19, 'cool'=>1)); // Клевер
       break;
      case 17:
      case 18:
      case 19:
      case 20:
        $a  = array(1=>array('id'=>7, 'cool'=>4), // Монеты
                    2=>array('id'=>3, 'cool'=>rand(4,5)), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(3,5)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>rand(3,5)), // Энергетики
                    5=>array('id'=>9, 'cool'=>rand(3,4)), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>10,  'cool'=>1), // Роз. конфета
                    8=>array('id'=>19, 'cool'=>1)); // Клевер
       break;
      case 21:
      case 22:
      case 23:
      case 24:
      case 25:
      case 26:
      case 27:
      case 28:
      case 29:
        $a  = array(1=>array('id'=>7, 'cool'=>5), // Монеты
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>6, 'cool'=>5), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>5), // Энергетики
                    5=>array('id'=>9, 'cool'=>5), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>10,  'cool'=>1), // Роз. Конфета
                    8=>array('id'=>19, 'cool'=>1)); // Клевер
       break;
      case 30:
        $a  = array(1=>array('id'=>1, 'cool'=>12000), // Монеты
                    2=>array('id'=>3, 'cool'=>rand(5,10)), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(5,7)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>rand(4,6)), // Энергетики
                    5=>array('id'=>9, 'cool'=>rand(3,5)), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>19, 'cool'=>1)); // Клевер
       break;
      case 31:
        $a  = array(1=>array('id'=>27, 'cool'=>'poke', 'namepok'=>'#027 Sandshrew'),
                    2=>array('id'=>52, 'cool'=>'poke', 'namepok'=>'#052 Meowth'),
                    3=>array('id'=>16, 'cool'=>'poke', 'namepok'=>'#016 Pidgey'));
       break;
      case 32:
      case 33:
      case 34:
      case 35:
      case 36:
      case 37:
      case 38:
      case 39:
      case 40:
      case 41:
      case 42:
      case 43:
      case 44:
      case 45:
      case 46:
      case 47:
      case 48:
      case 49:
        $a  = array(1=>array('id'=>7, 'cool'=>5), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>rand(5,6)), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(3,5)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>rand(3,5)), // Энергетики
                    5=>array('id'=>9, 'cool'=>rand(3,4)), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>19, 'cool'=>1), // Клевер
                    8=>array('id'=>10, 'cool'=>1)); // Роз. конф.
       break;
      case 50:
      case 51:
      case 52:
      case 53:
        $a  = array(1=>array('id'=>7, 'cool'=>5), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>5), // Покеболы
                    3=>array('id'=>61, 'cool'=>1), // Небольшой мешочек с добавками
                    4=>array('id'=>15, 'cool'=>5), // Энергетики
                    5=>array('id'=>7, 'cool'=>5), // Зел. Конфеты
                    6=>array('id'=>68, 'cool'=>1), // Смайлы
                    7=>array('id'=>65, 'cool'=>1), // Дудочка
                    8=>array('id'=>62, 'cool'=>1), // Мешочек с добавками
                    9=>array('id'=>23, 'cool'=>1), // Кирка
                    10=>array('id'=>18, 'cool'=>1), // Суш. Клевер
                    11=>array('id'=>19, 'cool'=>1), // Клевер
                    12=>array('id'=>20, 'cool'=>1)); // Свеж. Клевер
       break;

      // Продолжение в части 2/2...
      default:
        $a = array();
       break;
    }
    switch ($day){
      case 54:
      case 55:
      case 56:
      case 57:
      case 58:
      case 59:
      case 60:
      case 61:
      case 62:
      case 63:
        $a  = array(1=>array('id'=>7, 'cool'=>5), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>6), // Покеболы
                    3=>array('id'=>6, 'cool'=>6), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>4), // Энергетики
                    5=>array('id'=>9, 'cool'=>4), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>19, 'cool'=>1), // Клевер
                    8=>array('id'=>10, 'cool'=>1)); // Роз. конф.
       break;
      case 64:
      case 65:
      case 66:
      case 67:
      case 68:
      case 69:
      case 70:
      case 71:
      case 72:
      case 73:
      case 74:
      case 75:
      case 76:
      case 77:
        $a  = array(1=>array('id'=>7, 'cool'=>rand(5,6)), // Зел. Конфета
                    2=>array('id'=>3, 'cool'=>rand(5,6)), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(5,6)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>rand(4,5)), // Энергетики
                    5=>array('id'=>9, 'cool'=>rand(4,5)), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>19, 'cool'=>1), // Клевер
                    8=>array('id'=>10, 'cool'=>1)); // Роз. конф.
       break;
      case 78:
      case 79:
      case 80:
      case 81:
      case 82:
      case 83:
      case 84:
      case 85:
      case 86:
      case 87:
      case 88:
      case 89:
        $a  = array(1=>array('id'=>8, 'cool'=>rand(5,6)), // Голуб. Конфета
                    2=>array('id'=>3, 'cool'=>rand(6,7)), // Покеболы
                    3=>array('id'=>6, 'cool'=>rand(5,6)), // Жел. Конфеты
                    4=>array('id'=>15, 'cool'=>5), // Энергетики
                    5=>array('id'=>9, 'cool'=>5), // Фиол. Конфеты
                    6=>array('id'=>18, 'cool'=>1), // Суш. клевер
                    7=>array('id'=>19, 'cool'=>1), // Клевер
                    8=>array('id'=>10, 'cool'=>1), // Роз. конф.
                    9=>array('id'=>23, 'cool'=>1), // Кирка
                    10=>array('id'=>20, 'cool'=>1), // Свежий клевер
                    11=>array('id'=>7, 'cool'=>6)); // Зел. Конфета
       break;
      case 90:
        $a  = array(1=>array('id'=>127, 'cool'=>'poke', 'namepok'=>'#127 Pinsir'),
                    2=>array('id'=>90, 'cool'=>'poke', 'namepok'=>'#090 Shellder'),
                    3=>array('id'=>331, 'cool'=>'poke', 'namepok'=>'#331 Cacnea'));
       break;
      default:
        // уже есть default в части 1, здесь на всякий случай не трогаем
       break;
    }

    if (!empty($a)) {
        // Случайный элемент из набора наград (PHP 5.4 совместимо)
        $it = $a[array_rand($a)];

        if (isset($it['cool']) && $it['cool'] === 'poke') {
            // Награда — покемон
            plus_pokes($_SESSION['id'], $it['id'], 1, 1, 1, 'normal', false, 25);
            $txt = 'Покемона: <br> <span style="color:green;">'.$it['namepok'].' 1-lvl</span>.';
            $it['id'] = false; // для вывода id предмета как false
        } else {
            // Награда — предмет/валюта
            $itemsIsset = first('SELECT name FROM items WHERE id=%d', $it['id']);
            if ($itemsIsset) {
                plus_item($it['cool'], $it['id']);
                $txt = $itemsIsset['name'].': x'.formatnum($it['cool']).'.';
            } else {
                return false;
            }
        }
        return array('dat' => $dates, 'item' => $it['id'], 'cool' => $txt);
    }

    return false;
}
?>
