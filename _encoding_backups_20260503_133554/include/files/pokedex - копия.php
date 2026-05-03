<?php
$poke_id = $_GET['id'];
if(!$poke_id || $poke_id <= 0) die('Р СњР ВµРЎвЂљ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘РЎРЏ.');
$poke_info = first('SELECT * FROM poke_base pb
                    inner join pokemon p
                    on pb.id=p.id
                    WHERE pb.id=%d',$poke_id);
if(!$poke_info) die('Р С™ РЎРѓР С•Р В¶Р В°Р В»Р ВµР Р…Р С‘РЎР‹ РЎвЂљР В°Р С”Р С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ.');
require_once ("include/function/pdx.php");
if($xz = first('SELECT id FROM pokebuild WHERE baseid=%d AND poimka=1',$poke_info['id'])) $zy = '<div id="isset_pokemon">Р СљР С•Р В¶Р Р…Р С• Р С—Р С•Р в„–Р СР В°РЎвЂљРЎРЉ Р Р† Р С‘Р С–РЎР‚Р Вµ.</div>';
else $zy = '<div id="isset_pokemon">Р СњР В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ Р Р…Р Вµ Р В»Р С•Р Р†Р С‘РЎвЂљРЎРѓРЎРЏ Р Р…Р С‘Р С–Р Т‘Р Вµ.</div>';
function tip_pokedex($tipe_pok){
    $tips_return = false;
    if($tipe_pok == "Grass")    $tips_return = "Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р в„–";
    if($tipe_pok == "Fire")     $tips_return = "Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„–";
    if($tipe_pok == "Water")    $tips_return = "Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„–";
    if($tipe_pok == "Bug")      $tips_return = "Р СњР В°РЎРѓР ВµР С”Р С•Р СР С•Р Вµ";
    if($tipe_pok == "Flying")   $tips_return = "Р вЂєР ВµРЎвЂљР В°РЎР‹РЎвЂ°Р С‘Р в„–";
    if($tipe_pok == "Normal")   $tips_return = "Р СњР С•РЎР‚Р СР В°Р В»РЎРЉР Р…РЎвЂ№Р в„–";
    if($tipe_pok == "Poison")   $tips_return = "Р Р‡Р Т‘Р С•Р Р†Р С‘РЎвЂљРЎвЂ№Р в„–";
    if($tipe_pok == "Electric") $tips_return = "Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„–";
    if($tipe_pok == "Ground")   $tips_return = "Р вЂ”Р ВµР СР В»РЎРЏР Р…Р Р…Р С•Р в„–";
    if($tipe_pok == "Fighting") $tips_return = "Р вЂР С•Р ВµР Р†Р С•Р в„–";
    if($tipe_pok == "Psychic")  $tips_return = "Р СџРЎРѓР С‘РЎвЂ¦Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„–";
    if($tipe_pok == "Rock")     $tips_return = "Р С™Р В°Р СР ВµР Р…Р Р…РЎвЂ№Р в„–";
    if($tipe_pok == "Ice")      $tips_return = "Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р в„–";
    if($tipe_pok == "Dragon")   $tips_return = "Р вЂќРЎР‚Р В°Р С”Р С•Р Р…";
    if($tipe_pok == "Steel")    $tips_return = "Р РЋРЎвЂљР В°Р В»РЎРЉР Р…Р С•Р в„–";
    if($tipe_pok == "Dark")     $tips_return = "Р СћРЎвЂР СР Р…РЎвЂ№Р в„–";
    if($tipe_pok == "Ghost")    $tips_return = "Р СџРЎР‚Р С‘Р В·РЎР‚Р В°Р С”";
    return $tips_return;
}
?>
<TITLE>Pokedex: <?php echo "#".$poke_info['title'];?></TITLE>
<body bgcolor="#4F4F4F">
<style>
    .all {width:95%;}
    .active {color: #fff;}
    a{color:#000000; text-decoration:none;}
    a.active {color: #fff;}
    a.active:hover {text-decoration:underline; color:#fff5ee;}
    a:hover{text-decoration:underline; color:#505050;}
    .xtop, .xbottom { display: block; background: transparent; font-size: 1px; }
    .xb1, .xb2, .xb3, .xb4 { display: block; overflow: hidden; }
    .xb1, .xb2, .xb3 { height: 1px; }
    .xb2, .xb3, .xb4, .cont { background:  	#828282; border-left: 2px solid black; border-right: 2px solid black; }
    .xb1 { margin:0 5px; background: black; }
    .xb2 { margin:0 3px; border-width: 0 2px; }
    .xb3 { margin:0 2px; }
    .xb4 { height: 1px; margin: 0 1px; }
    .cont { padding: 1px 10px; }
    .bottom {width:100%;}
    .main_table {border-style: dotted; border-width: 1px; margin: 0px;padding: 0;}
    .table_data {border-style: dotted; border-width: 1px; }
    .table_data_head {text-align: center; background-color: #C1C1C1; color: white; }
    .table_data_tr{background-color: #f5f5ea; }
    .table_data_tr:hover{background: #CCCCFF;}
    .a_table{display: block;}
    #isset_pokemon{
        background: #bbbbbb;
        background: -moz-linear-gradient(top,  #f6f8f9 0%, #e5ebee 50%, #d7dee3 51%, #f5f7f9 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f6f8f9), color-stop(50%,#e5ebee), color-stop(51%,#d7dee3), color-stop(100%,#f5f7f9));
        background: -webkit-linear-gradient(top,  #f6f8f9 0%,#e5ebee 50%,#d7dee3 51%,#f5f7f9 100%);
        background: -o-linear-gradient(top,  #f6f8f9 0%,#e5ebee 50%,#d7dee3 51%,#f5f7f9 100%);
        background: -ms-linear-gradient(top,  #f6f8f9 0%,#e5ebee 50%,#d7dee3 51%,#f5f7f9 100%);
        background: linear-gradient(to bottom,  #f6f8f9 0%,#e5ebee 50%,#d7dee3 51%,#f5f7f9 100%);
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f6f8f9', endColorstr='#f5f7f9',GradientType=0 );
        position: relative;
        border: 2px solid;
        border-color: white;
        width: 99.8%;
        text-align: center;
        padding: 5px;
        cursor: pointer;
        font-weight: bold;
        font-size: 15px;
        padding-right: 10px;
        padding-bottom: 8px;
        top: -8px;
        left: -8px;
        -border-radius: 0px 0px 5px 0px;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 700;
    }
    #isset_pokemon:hover{
        filter: alpha(opacity=100);
        opacity: 1;
    }
</style>
<?=$zy;?>
<center>
    <div style="width: 95%;">
        <div class="all">
            <div class="top">
                <b class="xtop">
                    <b class="xb1"></b>
                    <b class="xb2"></b>
                    <b class="xb3"></b>
                    <b class="xb4"></b>
                </b>
            </div>
            <div class="cont">
                <table width="100%">
                    <tr>
                        <td colspan="4">
                            <center>
                                <b>
                                    <font color=" #363636">
                                        <h3><?php echo "#".$poke_info['title'];?></h3>
                                    </font>
                                </b>
                            </center>
                        </td>
                    </tr>
                    <tr>
                        <td width="260" align="left" valign="top" style="border-right: 1px solid black;">
                            <?php echo "<b>".tip_pokedex($poke_info['Element'])." ".tip_pokedex($poke_info['SubElement'])."</b>";?>
                            <img src="pok/normal/<?php echo $poke_info['id']; ?>.jpg">
                        </td>
                        <td  colspan="2" align="left" valign="top">
                            <b>Р ТђР С—</b> : <?php echo $poke_info['hp'];?> <br />
                            <b>Р С’РЎвЂљР В°Р С”Р В°</b> : <?php echo $poke_info['atk'];?> <br />
                            <b>Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°</b> : <?php echo $poke_info['def'];?> <br />
                            <b>Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ</b> : <?php echo $poke_info['speed'];?> <br />
                            <b>Р РЋР С—Р ВµРЎвЂ . Р В°РЎвЂљР В°Р С”Р В°</b> : <?php echo $poke_info['satk'];?> <br />
                            <b>Р РЋР С—Р ВµРЎвЂ . Р В·Р В°РЎвЂ°Р С‘РЎвЂљР В°</b> : <?php echo $poke_info['sdef'];?> <br />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border-bottom: 1px solid black;">
                            <?php
                            if(!$text1 = evolutionPdx($poke_info['id'])){
                                if($poke_info['id'] > 720) $img_r = "png"; else  $img_r = "gif";
                                $res_evol = first('SELECT * FROM poke_base WHERE id=%d',$poke_info['evolution_type']);
                                if(!$res_evol) { $text1 = "<b>Р СњР вЂўР Сћ</b>"; } else {
                                    $res_evol_two = first('SELECT * FROM poke_base WHERE id=%d',$res_evol['evolution_type']);
                                    if(!$res_evol_two){ $text2=""; } else { $text2 = $res_evol['evolution_lvl']."-lvl  => <a style='color:#000000;' href=game.php?go=pokedex&id=".$res_evol_two['id']."><img src=pok/anim/".$res_evol_two['id'].".".$img_r."> #".$res_evol_two['title']."</a> "; }
                                    $text1 = "<a style='color:#000000;' href=game.php?go=pokedex&id=".$poke_info['id']."> <img src=pok/anim/".$poke_info['id'].".".$img_r."> #".$poke_info['title']."</a>  ".$poke_info['evolution_lvl']."-lvl => <a style='color:#000000;' href=game.php?go=pokedex&id=".$res_evol['id']."><img src=pok/anim/".$res_evol['id'].".".$img_r."> #".$res_evol['title']."</a> ".$text2;
                                }
                            }
                            ?>
                            Р В­Р Р†Р С•Р В»РЎР‹РЎвЂ Р С‘РЎРЏ : <? echo $text1;?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php
                            $at = select('SELECT a.atc_lvl, ap.atac_name, ap.atac_id FROM attac_poke a INNER JOIN attac_power ap ON a.atac_id=ap.atac_id WHERE a.poke_base_id=%d ORDER BY atc_lvl ASC',$poke_info['id']);
                            ?>
                            Р С’РЎвЂљР В°Р С”Р С‘:<br>
                            <?php
                            if(!$at){
                                echo "Р СњР В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ РЎС“ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р ВµРЎвЂљ Р В°РЎвЂљР В°Р С”.";
                            }else{
                                echo "<table width='200'>";
                                foreach($at as $atcs){
                                    echo "
                      <tr>
                        <td width='50'>
                          ".$atcs['atc_lvl']."-lvl 
                        </td>
                        <td>
                          <a href=javascript: onClick=win1=window.open('game.php?go=atk&id=".$atcs['atac_id']."','atk','width=726,height=260,scrollbars=yes');return true;><img src=\"/img/other/inf.png\" ></a>
                            ".$atcs['atac_name']."
                        </td>
                      </tr>
                          ";
                                }
                                echo "</table>";
                            }
                            ?>
                        </td>
                        <td colspan="2">
                            <?php
                            $at2 = select('SELECT ap.atac_name, ap.atac_id FROM attac_egg a INNER JOIN attac_power ap ON a.atac_id=ap.atac_id WHERE a.poke_base_id=%d ORDER BY a.atac_id ASC',$poke_info['id']);
                            ?>
                            Р Р‡Р в„–РЎвЂ Р ВµР Р†РЎвЂ№Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:<br>
                            <?php
                            if(!$at2){
                                echo "Р Р€ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р ВµРЎвЂљ РЎРЏР в„–РЎвЂ Р ВµР Р†РЎвЂ№РЎвЂ¦ Р В°РЎвЂљР В°Р С”.";
                            }else {
                                echo "<table width='200'>";
                                $a = 1;
                                foreach($at2 as $atcs2){
                                    echo "
                      <tr>
                        <td width = 10>
                          ".$a++." 
                        </td>
                        <td>
                          <a href=javascript: onClick=win1=window.open('game.php?go=atk&id=".$atcs2['atac_id']."','atk','width=726,height=260,scrollbars=yes');return true;><img src=\"/img/other/inf.png\" ></a>
                          ".$atcs2['atac_name']."
                        </td>
                      </tr>
                          ";
                                }
                                echo "</table>";
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="bottom">
                <b class="xbottom">
                    <b class="xb4"></b>
                    <b class="xb3"></b>
                    <b class="xb2"></b>
                    <b class="xb1"></b>
                </b>
            </div>
        </div>
    </div>
</center>
</body>