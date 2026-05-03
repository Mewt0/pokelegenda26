<?php
if(!$view['id']){
    exit;
}
function obr_id($txt){
    $id = substr($txt, 4);
    return $id;
}

$id = $view['id'];
$color_stst = "#ED7388";

$x_title_1 = '';
$x_title_2 = '';

$user_1 = color_group_users($view['user_1']);
$user_2 = color_group_users($view['user_2']);

$battle_poke_1 =  first('SELECT * FROM pok_user WHERE id=%d',obr_id($view['poke_1']));
$battle_poke_stat_p_1 = array('attac','spattac','defend','spdefend','speed','acc','accuracy');
$battle_poke_stat_m_1 = array('attac','spattac','defend','spdefend','speed','acc','accuracy');
$battle_poke_stat_p_1 =  first('SELECT attac,spattac,defend,spdefend,speed,acc,accuracy FROM statpokemonbatle WHERE pokeid="%s" AND battleid=%d AND tip="plus"' ,$view['poke_1'],$id);
$battle_poke_stat_m_1 =  first('SELECT attac,spattac,defend,spdefend,speed,acc,accuracy FROM statpokemonbatle WHERE pokeid="%s" AND battleid=%d AND tip="minus"' ,$view['poke_1'],$id);
$battle_poke_status_1 = first('SELECT sts.tittle_status FROM bttle_status bsp inner join status sts on bsp.namber_st=sts.id_status WHERE bsp.buttleid=%d AND bsp.pokeid="%s"',$id,$view['poke_1']);
if(isset($battle_poke_status_1['tittle_status'])) $x_title_1 .= "<font color='#fff'><b>".$battle_poke_status_1['tittle_status']."</b></font><br>";
$def_st_my_pl  = $battle_poke_stat_p_1['defend'];
$sdef_st_my_pl = $battle_poke_stat_p_1['spdefend'];
$atk_st_my_pl  = $battle_poke_stat_p_1['attac'];
$satk_st_my_pl = $battle_poke_stat_p_1['spattac'];
$acc_st_my_pl  = $battle_poke_stat_p_1['acc'];
$spd_st_my_pl  = $battle_poke_stat_p_1['speed'];
$acr_st_my_pl  = $battle_poke_stat_p_1['accuracy'];
if($def_st_my_pl >0) $x_title_1 .= "<b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$def_st_my_pl."</b><br>";
if($sdef_st_my_pl>0) $x_title_1 .= "<b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$sdef_st_my_pl."</b><br>";
if($atk_st_my_pl >0) $x_title_1 .= "<b>Р С’РЎвЂљР В°Р С”Р В° + ".$atk_st_my_pl."</b><br>";
if($satk_st_my_pl>0) $x_title_1 .= "<b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° + ".$satk_st_my_pl."</b><br>";
if($acc_st_my_pl >0) $x_title_1 .= "<b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ + ".$acc_st_my_pl."</b><br>";
if($spd_st_my_pl >0) $x_title_1 .= "<b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ + ".$spd_st_my_pl."</b><br>";
if($acr_st_my_pl >0) $x_title_1 .= "<b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ + ".$acr_st_my_pl."</b><br>";
$def_st_my_min  = $battle_poke_stat_m_1['defend'];
$sdef_st_my_min = $battle_poke_stat_m_1['spdefend'];
$atk_st_my_min  = $battle_poke_stat_m_1['attac'];
$satk_st_my_min = $battle_poke_stat_m_1['spattac'];
$acc_st_my_min  = $battle_poke_stat_m_1['acc'];
$spd_st_my_min  = $battle_poke_stat_m_1['speed'];
$acr_st_my_min  = $battle_poke_stat_m_1['accuracy'];
if($def_st_my_min>0) $x_title_1 .= "<font color='".$color_stst."'><b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$def_st_my_min."</b></font><br>";
if($sdef_st_my_min>0)$x_title_1 .= "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$sdef_st_my_min."</b></font><br>";
if($atk_st_my_min>0) $x_title_1 .= "<font color='".$color_stst."'><b>Р С’РЎвЂљР В°Р С”Р В° - ".$atk_st_my_min."</b></font><br>";
if($satk_st_my_min>0)$x_title_1 .= "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° - ".$satk_st_my_min."</b></font><br>";
if($acc_st_my_min>0) $x_title_1 .=  "<font color='".$color_stst."'><b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ - ".$acc_st_my_min."</b></font><br>";
if($spd_st_my_min>0) $x_title_1 .=  "<font color='".$color_stst."'><b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ - ".$spd_st_my_min."</b></font><br>";
if($acr_st_my_min>0) $x_title_1 .=  "<font color='".$color_stst."'><b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ - ".$acr_st_my_min."</b></font><br>";
$x_title_1_echo = $x_title_1?'<div class="x">'.$x_title_1.'</div>':'';

$battle_poke_2 =  first('SELECT * FROM pok_user WHERE id=%d',obr_id($view['poke_2']));
$battle_poke_stat_p_2 = array('attac','spattac','defend','spdefend','speed','acc','accuracy');
$battle_poke_stat_m_2 = array('attac','spattac','defend','spdefend','speed','acc','accuracy');
$battle_poke_stat_p_2 =  first('SELECT attac,spattac,defend,spdefend,speed,acc,accuracy FROM statpokemonbatle WHERE pokeid="%s" AND battleid=%d AND tip="plus"' ,$view['poke_2'],$id);
$battle_poke_stat_m_2 =  first('SELECT attac,spattac,defend,spdefend,speed,acc,accuracy FROM statpokemonbatle WHERE pokeid="%s" AND battleid=%d AND tip="minus"' ,$view['poke_2'],$id);
$battle_poke_status_2 = first('SELECT sts.tittle_status FROM bttle_status bsp inner join status sts on bsp.namber_st=sts.id_status WHERE bsp.buttleid=%d AND bsp.pokeid="%s"',$id,$view['poke_2']);
if(isset($battle_poke_status_2['tittle_status'])) $x_title_2 .= "<font color='#fff'><b>".$battle_poke_status_2['tittle_status']."</b></font><br>";
$def_st_my_pl  = $battle_poke_stat_p_2['defend'];
$sdef_st_my_pl = $battle_poke_stat_p_2['spdefend'];
$atk_st_my_pl  = $battle_poke_stat_p_2['attac'];
$satk_st_my_pl = $battle_poke_stat_p_2['spattac'];
$acc_st_my_pl  = $battle_poke_stat_p_2['acc'];
$spd_st_my_pl  = $battle_poke_stat_p_2['speed'];
$acr_st_my_pl  = $battle_poke_stat_p_2['accuracy'];
if($def_st_my_pl >0) $x_title_2 .= "<b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$def_st_my_pl."</b><br>";
if($sdef_st_my_pl>0) $x_title_2 .= "<b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° + ".$sdef_st_my_pl."</b><br>";
if($atk_st_my_pl >0) $x_title_2 .= "<b>Р С’РЎвЂљР В°Р С”Р В° + ".$atk_st_my_pl."</b><br>";
if($satk_st_my_pl>0) $x_title_2 .= "<b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° + ".$satk_st_my_pl."</b><br>";
if($acc_st_my_pl >0) $x_title_2 .= "<b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ + ".$acc_st_my_pl."</b><br>";
if($spd_st_my_pl >0) $x_title_2 .= "<b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ + ".$spd_st_my_pl."</b><br>";
if($acr_st_my_pl >0) $x_title_2 .= "<b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ + ".$acr_st_my_pl."</b><br>";
$def_st_my_min  = $battle_poke_stat_m_2['defend'];
$sdef_st_my_min = $battle_poke_stat_m_2['spdefend'];
$atk_st_my_min  = $battle_poke_stat_m_2['attac'];
$satk_st_my_min = $battle_poke_stat_m_2['spattac'];
$acc_st_my_min  = $battle_poke_stat_m_2['acc'];
$spd_st_my_min  = $battle_poke_stat_m_2['speed'];
$acr_st_my_min  = $battle_poke_stat_m_2['accuracy'];
if($def_st_my_min>0) $x_title_2 .= "<font color='".$color_stst."'><b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$def_st_my_min."</b></font><br>";
if($sdef_st_my_min>0)$x_title_2 .= "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В° - ".$sdef_st_my_min."</b></font><br>";
if($atk_st_my_min>0) $x_title_2 .= "<font color='".$color_stst."'><b>Р С’РЎвЂљР В°Р С”Р В° - ".$atk_st_my_min."</b></font><br>";
if($satk_st_my_min>0)$x_title_2 .= "<font color='".$color_stst."'><b>Р РЋР С—Р ВµРЎвЂ . Р С’РЎвЂљР В°Р С”Р В° - ".$satk_st_my_min."</b></font><br>";
if($acc_st_my_min>0) $x_title_2 .=  "<font color='".$color_stst."'><b>Р вЂєР С•Р Р†Р С”Р С•РЎРѓРЎвЂљРЎРЉ - ".$acc_st_my_min."</b></font><br>";
if($spd_st_my_min>0) $x_title_2 .=  "<font color='".$color_stst."'><b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ - ".$spd_st_my_min."</b></font><br>";
if($acr_st_my_min>0) $x_title_2 .=  "<font color='".$color_stst."'><b>Р СћР С•РЎвЂЎР Р…Р С•РЎРѓРЎвЂљРЎРЉ - ".$acr_st_my_min."</b></font><br>";
$x_title_2_echo = $x_title_2?'<div class="x">'.$x_title_2.'</div>':'';

$img1 = "/pok/".$battle_poke_1['tips']."/".$battle_poke_1['basenum'].".".(!empty($global_poke_img)?$global_poke_img:'jpg');
$img2 = "/pok/".$battle_poke_2['tips']."/".$battle_poke_2['basenum'].".".(!empty($global_poke_img)?$global_poke_img:'jpg');

$hp1 = $battle_poke_1['hp_my']>0?($battle_poke_1['hp_my']/$battle_poke_1['hp_max'])*100:'';
$hp1 = ($hp1>100?100:$hp1);
$hp1 = ($hp1<=0?0:$hp1);

$hp2 = $battle_poke_2['hp_my']>0?($battle_poke_2['hp_my']/$battle_poke_2['hp_max'])*100:'';
$hp2 = ($hp2>100?100:$hp2);
$hp2 = ($hp2<=0?0:$hp2);


$h1 = array('pr'=>$hp1,'color'=>($hp1<=20?'#CD0000':'#008B45'));
$h2 = array('pr'=>$hp2,'color'=>($hp2<=20?'#CD0000':'#008B45'));

$battle_log = select('SELECT raund, demage FROM battle_log WHERE battle_id=%d ORDER BY id DESC',$id);
$ms_log = '';
foreach($battle_log as $log_go){
    $ms_log .=  '<tr>';
    $ms_log .=  '<td><font color="gold" size = "6"><b>'.$log_go['raund'].'</b></font><td>';
    $ms_log .=  '<td>'.$log_go['demage'].'<td>';
    $ms_log .=  '</tr><tr><td width="100%" colspan="3"><hr></td></tr>';
}

$count_poke_hp_1 = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$view['user_1']);
$count_poke_nhp_1  = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1 AND hp_my<=0',$view['user_1']);
$imgPokeCool_1 = '';
for($i=0; $i<$count_poke_hp_1['count']; $i++){
    $imgPokeCool_1 .= '<img src="/img/info/pokeball.png" width="42px">';
}
$count_pokeOf = $count_poke_hp_1['count']+$count_poke_nhp_1['count'];
for($i=$count_poke_hp_1['count']; $i<$count_pokeOf; $i++){
    $imgPokeCool_1 .= '<img src="/img/info/pokeball3.png" width="42px">';
}
$count_poke = $count_pokeOf;
for($i=$count_poke; $i<6; $i++){
    $imgPokeCool_1 .= '<img src="/img/info/pokeball2.png" width="42px">';
}
$count_poke_hp_2 = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1 AND hp_my>0',$view['user_2']);
$count_poke_nhp_2  = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d AND active=1 AND hp_my<=0',$view['user_2']);
$imgPokeCool_2 = '';
for($i=0; $i<$count_poke_hp_2['count']; $i++){
    $imgPokeCool_2 .= '<img src="/img/info/pokeball.png" width="42px">';
}
$count_pokeOf = $count_poke_hp_2['count']+$count_poke_nhp_2['count'];
for($i=$count_poke_hp_2['count']; $i<$count_pokeOf; $i++){
    $imgPokeCool_2 .= '<img src="/img/info/pokeball3.png" width="42px">';
}
$count_poke = $count_pokeOf;
for($i=$count_poke; $i<6; $i++){
    $imgPokeCool_2 .= '<img src="/img/info/pokeball2.png" width="42px">';
}
$pogoda_name = first('SELECT name_pogod FROM pogoda WHERE id_pog=%d',$view['id_pogodi']);
if(isset($pogoda_name['name_pogod'])) $pogoda_battle = "<font color='gold'><b> ".$pogoda_name['name_pogod']."</b></font>";
else $pogoda_battle = "<font color='gold'><b> Р С›Р В±РЎвЂ№РЎвЂЎР Р…Р В°РЎРЏ</b></font>";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
    <script type="text/javascript">
        timeB = setTimeout("check_battle(<?=$id;?>)", 8000);
        $("#content-location").fadeIn("slow");
    </script>
    <style type="text/css">
        body, html{
            font-family: verdana, serif;
            font-size: 12px;
            color: #000;
            min-width: 900px;
        }
        .name_pok{
            position: relative;
            background: #1C1C1C;
            width: 245px;
            height: 20px;
            border: 2px solid #A9A9A9;
            color: #FFFAFA;
            padding: 3px;
            border-radius: 8px 8px 0 0;
            -moz-border-radius: 8px 8px 0 0;
            -webkit-border-radius: 8px 8px 0 0;
        }
        .img_pok{
            position: relative;
            top:-2px;
            right: 2px;
            width: 250px;
            height: 190px;
        }
        img{

        }
        img:hover{
            box-shadow:0 0 5px #1C1C1C;
        }
        .users{
            position: relative;
            background:#CFCFCF;
            width: 245px;
            height: 20px;
            padding: 3px;
            border: 1px solid #1C1C1C;
            border-radius: 0 0 8px 8px;
            -moz-border-radius: 0 0 8px 8px;
            -webkit-border-radius: 0 0 8px 8px;
        }
        .x{
            position:absolute;
            top: 5px;
            left: 5px;
            padding: 3px;
            COLOR: #e4c927;
            FONT-SIZE: 12px;
            font-weight: bold;
            FONT-FAMILY: Tahoma, monospace;
            text-align:left;
            background: #636363;
            opacity: 0.8;
            z-index:10;
        }
    </style>
</head>
<body>
<table width="100%" height="100%">
    <tr>
        <td width="250px" valign="top" align="center">
            <div class="name_pok">
                    <span style="font-weight: bold; font-size: 15px;">
                        <a href="javascript:window.open('/game.php?go=pokedex&id=<?=$battle_poke_1['basenum'];?>','pokedex','width=550,height=550,scrollbars=yes');return true;">
                            <img src="/img/other/pokedex.png" alt="POKE DEX">
                        </a>
                        #<?=$battle_poke_1['names'];?> <?=$battle_poke_1['lvl'];?> - lvl
                    </span>
            </div>
            <div class="img_pok">
                <?=$x_title_1_echo;?>
                <img src="<?=$img1;?>" width="250" height="190" border="1" alt="<?=$battle_poke_1['names'];?>"/>
                <div style='width:<?=$h1['pr'];?>%; background:<?=$h1['color'];?>; height:14px; position: absolute; bottom: -1px; left: 1px'></div>
                <div style='width:100%; height:14px; position: absolute; bottom: 1px; z-index: 2; font:14px Tahoma; color: #1C1C1C;'><?=$battle_poke_1['hp_my'];?>/<?=$battle_poke_1['hp_max'];?></div>
            </div>
            <div class="users">
                <?=$user_1;?>
            </div>
            <?=$imgPokeCool_1;?>
        </td>
        <td>
            <div style="width: 100%; height: 100%; overflow-y: auto;">
                <div style="width: 100%; color: #000; font-weight: bold; text-align: center">
                    Р СџРЎР‚Р С•РЎРѓР СР С•РЎвЂљРЎР‚ Р В±Р С•РЎРЏ: <?=$user_1;?> VS. <?=$user_2;?><br>
                    <span style="color: #000; font-weight: bold;">Р В Р В°РЎС“Р Р…Р Т‘: <?=$view['raund'];?></span><br>
                    <span style="color: #000; font-weight: bold;">Р СџР С•Р С–Р С•Р Т‘Р В°:</span> <?=$pogoda_battle;?>
                </div>
                <table style="padding: 5px; margin: 10px;">
                    <?=$ms_log;?>
                </table>
            </div>
        </td>
        <td width="250px" valign="top" align="center">
            <div class="name_pok">
                    <span style="font-weight: bold; font-size: 15px;">
                        <a href="javascript:window.open('/game.php?go=pokedex&id=<?=$battle_poke_2['basenum'];?>','pokedex','width=550,height=550,scrollbars=yes');return true;">
                            <img src="/img/other/pokedex.png" alt="POKE DEX">
                        </a>
                        #<?=$battle_poke_2['names'];?> <?=$battle_poke_1['lvl'];?> - lvl
                    </span>
            </div>
            <div class="img_pok">
                <?=$x_title_2_echo;?>
                <img src="<?=$img2;?>" width="250" height="190" border="1" alt="<?=$battle_poke_2['names'];?>"/>
                <div style='width:<?=$h2['pr'];?>%; background:<?=$h2['color'];?>; height:14px; position: absolute; bottom: -1px; left: 1px'></div>
                <div style='width:100%; height:14px; position: absolute; bottom: 1px; z-index: 2; font:14px Tahoma; color: #1C1C1C;'><?=$battle_poke_2['hp_my'];?>/<?=$battle_poke_2['hp_max'];?></div>
            </div>
            <div class="users">
                <?=$user_2;?>
            </div>
            <?=$imgPokeCool_2;?>
        </td>
    </tr>
</table>
</body>
</html>