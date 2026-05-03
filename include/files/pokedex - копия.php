<?php
$poke_id = $_GET['id'];
if(!$poke_id || $poke_id <= 0) die('Нет значения.');
$poke_info = first('SELECT * FROM poke_base pb
                    inner join pokemon p
                    on pb.id=p.id
                    WHERE pb.id=%d',$poke_id);
if(!$poke_info) die('К сожалению такого покемона не существует.');
require_once ("include/function/pdx.php");
if($xz = first('SELECT id FROM pokebuild WHERE baseid=%d AND poimka=1',$poke_info['id'])) $zy = '<div id="isset_pokemon">Можно поймать в игре.</div>';
else $zy = '<div id="isset_pokemon">На данный момент не ловится нигде.</div>';
function tip_pokedex($tipe_pok){
    $tips_return = false;
    if($tipe_pok == "Grass")    $tips_return = "Травяной";
    if($tipe_pok == "Fire")     $tips_return = "Огненный";
    if($tipe_pok == "Water")    $tips_return = "Водный";
    if($tipe_pok == "Bug")      $tips_return = "Насекомое";
    if($tipe_pok == "Flying")   $tips_return = "Летающий";
    if($tipe_pok == "Normal")   $tips_return = "Нормальный";
    if($tipe_pok == "Poison")   $tips_return = "Ядовитый";
    if($tipe_pok == "Electric") $tips_return = "Электрический";
    if($tipe_pok == "Ground")   $tips_return = "Землянной";
    if($tipe_pok == "Fighting") $tips_return = "Боевой";
    if($tipe_pok == "Psychic")  $tips_return = "Психический";
    if($tipe_pok == "Rock")     $tips_return = "Каменный";
    if($tipe_pok == "Ice")      $tips_return = "Ледяной";
    if($tipe_pok == "Dragon")   $tips_return = "Дракон";
    if($tipe_pok == "Steel")    $tips_return = "Стальной";
    if($tipe_pok == "Dark")     $tips_return = "Тёмный";
    if($tipe_pok == "Ghost")    $tips_return = "Призрак";
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
                            <b>Хп</b> : <?php echo $poke_info['hp'];?> <br />
                            <b>Атака</b> : <?php echo $poke_info['atk'];?> <br />
                            <b>Защита</b> : <?php echo $poke_info['def'];?> <br />
                            <b>Скорость</b> : <?php echo $poke_info['speed'];?> <br />
                            <b>Спец. атака</b> : <?php echo $poke_info['satk'];?> <br />
                            <b>Спец. защита</b> : <?php echo $poke_info['sdef'];?> <br />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border-bottom: 1px solid black;">
                            <?php
                            if(!$text1 = evolutionPdx($poke_info['id'])){
                                if($poke_info['id'] > 720) $img_r = "png"; else  $img_r = "gif";
                                $res_evol = first('SELECT * FROM poke_base WHERE id=%d',$poke_info['evolution_type']);
                                if(!$res_evol) { $text1 = "<b>НЕТ</b>"; } else {
                                    $res_evol_two = first('SELECT * FROM poke_base WHERE id=%d',$res_evol['evolution_type']);
                                    if(!$res_evol_two){ $text2=""; } else { $text2 = $res_evol['evolution_lvl']."-lvl  => <a style='color:#000000;' href=game.php?go=pokedex&id=".$res_evol_two['id']."><img src=pok/anim/".$res_evol_two['id'].".".$img_r."> #".$res_evol_two['title']."</a> "; }
                                    $text1 = "<a style='color:#000000;' href=game.php?go=pokedex&id=".$poke_info['id']."> <img src=pok/anim/".$poke_info['id'].".".$img_r."> #".$poke_info['title']."</a>  ".$poke_info['evolution_lvl']."-lvl => <a style='color:#000000;' href=game.php?go=pokedex&id=".$res_evol['id']."><img src=pok/anim/".$res_evol['id'].".".$img_r."> #".$res_evol['title']."</a> ".$text2;
                                }
                            }
                            ?>
                            Эволюция : <? echo $text1;?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php
                            $at = select('SELECT a.atc_lvl, ap.atac_name, ap.atac_id FROM attac_poke a INNER JOIN attac_power ap ON a.atac_id=ap.atac_id WHERE a.poke_base_id=%d ORDER BY atc_lvl ASC',$poke_info['id']);
                            ?>
                            Атаки:<br>
                            <?php
                            if(!$at){
                                echo "На данный момент у этого покемона нет атак.";
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
                            Яйцевые атаки:<br>
                            <?php
                            if(!$at2){
                                echo "У этого покемона нет яйцевых атак.";
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