<?php
declare(strict_types=1);
$config = array(
  'base' => 'http://pokelegenda.ru/',
  'server' => 'localhost',
  'user' => 'pokelege_fh7904el_base',
  'pass' => '^&@^#A?]',
  'db' => 'pokelege_fh7904el_base',
  'run' => 1,
  'techwork' => '1',
  'time_techwork' => '<b>Р СџРЎР‚Р С‘Р В±Р В»Р С‘Р В·Р С‘РЎвЂљР ВµР В»РЎРЉР Р…Р С•Р Вµ Р Р†РЎР‚Р ВµР СРЎРЏ:</b> <i>20 - 30 Р СР С‘Р Р…РЎС“РЎвЂљ</i>. <br><b>Р СџРЎР‚Р С‘РЎвЂЎР С‘Р Р…Р В°</b>: <i>Р СњР ВµР С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р Вµ Р С—Р С•Р С—РЎР‚Р В°Р Р†Р С”Р С‘!</i>.'
);

// Р С›Р В±РЎР‰РЎРЏР Р†Р В»РЎРЏР ВµР С Р С”Р С•Р Р…РЎРѓРЎвЂљР В°Р Р…РЎвЂљРЎС“ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р ВµРЎРѓР В»Р С‘ Р ВµРЎвЂ°РЎвЂ Р Р…Р Вµ Р С•Р В±РЎР‰РЎРЏР Р†Р В»Р ВµР Р…Р В°
if (!defined('SESSION_BROWSER_SIGN_SECRET')) {
    define('SESSION_BROWSER_SIGN_SECRET',  '@w434253254s9');
}

// Р С›Р В±РЎР‰РЎРЏР Р†Р В»РЎРЏР ВµР С РЎвЂћРЎС“Р Р…Р С”РЎвЂ Р С‘РЎР‹ РЎвЂљР С•Р В»РЎРЉР С”Р С• Р ВµРЎРѓР В»Р С‘ Р С•Р Р…Р В° Р ВµРЎвЂ°РЎвЂ Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ
if (!function_exists('getBrowserSign')) {
    function getBrowserSign(){
        $rawSign = SESSION_BROWSER_SIGN_SECRET;
        $params = explode('.', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $rawSign .= ($params[0] ?? '0') . ($params[1] ?? '0');
        $signParts = array('HTTP_USER_AGENT');
        foreach($signParts as $signPart){
            $rawSign .= '::' . (isset($_SERVER[$signPart]) ? $_SERVER[$signPart] : 'none');
        }
        return md5($rawSign);
    }
}

// Р С›РЎвЂљР С”Р В»РЎР‹РЎвЂЎР В°Р ВµР С РЎвЂљР ВµРЎвЂ¦РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂ№ Р Т‘Р В»РЎРЏ Р В°Р Т‘Р СР С‘Р Р…Р С•Р Р†
if (!empty($_SESSION['id']) && in_array($_SESSION['id'], array(1, 2, 6))) {
    $config['techwork'] = 0;
}

date_default_timezone_set('Europe/Moscow');
?>
