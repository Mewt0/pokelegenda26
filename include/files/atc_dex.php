<?php
// Правильные пути для подключения /include/files/ -> /include/function/
require_once __DIR__ . '/../function/config.php';
require_once __DIR__ . '/../function/db3.php';
require_once __DIR__ . '/../function/globfanction.php';

if (!headers_sent()) {
    header('Content-Type: text/html; charset=windows-1251');
}

if (!empty($_GET['id'])) {
$id_atk_get = $_GET['id'];

// Берём всю информацию об атаке как есть, а tittle_effect конвертируем на стороне MySQL из UTF-8 -> cp1251
$ataka_info = first("
  SELECT 
    atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy, atac_goal, atac_tittle,
    CONVERT(CAST(CONVERT(CAST(tittle_effect AS BINARY) USING utf8) AS BINARY) USING cp1251) AS tittle_effect
  FROM attac_power
  WHERE atac_id=%d
", $id_atk_get);
if(!$ataka_info){ exit;}

if (!function_exists('atktip')) {
function atktip($tipe_pok){
if($tipe_pok == "Grass")    { $tips_return = "Травяная"; }
if($tipe_pok == "Fire")     { $tips_return = "Огненная";  }
if($tipe_pok == "Water")    { $tips_return = "Водная";    }
if($tipe_pok == "Bug")      { $tips_return = "Насекомая"; }
if($tipe_pok == "Flying")   { $tips_return = "Летающая";  }
if($tipe_pok == "Normal")   { $tips_return = "Нормальная";}
if($tipe_pok == "Poison")   { $tips_return = "Ядовитая";  }
if($tipe_pok == "Electric") { $tips_return = "Электрическая";}
if($tipe_pok == "Ground")   { $tips_return = "Земляная";  }
if($tipe_pok == "Fighting") { $tips_return = "Боевая";    }
if($tipe_pok == "Psychic")  { $tips_return = "Психическая";}
if($tipe_pok == "Rock")     { $tips_return = "Каменная";  }
if($tipe_pok == "Ice")      { $tips_return = "Ледяная";   }
if($tipe_pok == "Dragon")   { $tips_return = "Дракон";    }
if($tipe_pok == "Steel")    { $tips_return = "Стальная";  }
if($tipe_pok == "Dark")     { $tips_return = "Тёмная";    }
if($tipe_pok == "Ghost")    { $tips_return = "Призрачная";}
return $tips_return;}
}

if (!function_exists('atkcat')) {
function atkcat($tipe_atk){
      if($tipe_atk == 1)   $tipe_atk = "Физическая";    
  elseif($tipe_atk == 2)   $tipe_atk = "Специальная";   
    else $tipe_atk = "Статус";         
 return $tipe_atk;
} 
}

if (!function_exists('ceill')) {
function ceill($ceil){
if($ceil == "1")   { $ceil = "Противник";    }
if($ceil == "2")   { $ceil = "На себя";    }
if($ceil == "3")   { $ceil = "Вся вражеская команда";    }
if($ceil == "4")   { $ceil = "Вся команда пользователя";    }
if($ceil == "5")   { $ceil = "Весь поле";    }
return $ceil;
}
}

print '<link rel="stylesheet" type="text/css" HREF="css/prof.css">
<TITLE>League-Of-Pokemons -> Информация об атаке: '.$ataka_info['atac_name'].'</TITLE>
<style>
div.Inf{
    background: #505050;
    background: -moz-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#505050), color-stop(50%,#808080), color-stop(100%,#505050));
    background: -webkit-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: -o-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: -ms-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    border:1px solid #99958c;
    font-weight:bold;
    padding:  10px;
    margin: 2px;
    color: #fff;
    overflow: auto;
    filter:alpha(opacity=90); 
    opacity:0.9; 
    -moz-opacity:0.9;
    -moz--webkit-border-radius: 5px;
    -webkit--webkit-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px; 
    width:650px;
}
</style>';

print '<center><div class="Inf" align="left"><center><h2>Атака: '.$ataka_info['atac_name'].'.</h2></center>';
print '<span style="color:#000">Категория:</span> '.atkcat($ataka_info['atac_categori']).'.<br>';
print '<span style="color:#000">Цель: </span>'.ceill($ataka_info['atac_goal']).'.<br>';
print '<span style="color:#000">Тип: </span>'.atktip($ataka_info['atac_tip']).'.<br>';
print '<span style="color:#000">PP: </span>'.$ataka_info['atac_pp'].'.<br>';
print '<span style="color:#000">Точность: </span>'.$ataka_info['atac_accuracy'].'%.<br>';
print '<span style="color:#000">Сила: </span>'.($ataka_info['atac_power']<=0?'-':$ataka_info['atac_power']).'.<br>';
print '<span style="color:#000">Описание: </span>'.$ataka_info['atac_tittle'].'<br>';
print '<span style="color:#000">Эффект: </span>'.$ataka_info['tittle_effect'].' '; 
print '</div></center>'; 
}

?>