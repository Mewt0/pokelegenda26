<?php
$poke_id = $_GET['id'];
if(!$poke_id || $poke_id <= 0) die('Р СњР ВµРЎвЂљ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘РЎРЏ.');
$poke_info = first('SELECT * FROM poke_base pb
                    inner join pokemon p
                    on pb.id=p.id
                    WHERE pb.id=%d',$poke_id);
if(!$poke_info) die('Р С™ РЎРѓР С•Р В¶Р В°Р В»Р ВµР Р…Р С‘РЎР‹ РЎвЂљР В°Р С”Р С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ.');

// Р ВР В·Р СР ВµР Р…Р ВµР Р…Р С• Р Р…Р В° PNG РЎвЂћР С•РЎР‚Р СР В°РЎвЂљ
$poke_img_format = "png";     // Р Т‘Р В»РЎРЏ /pok/normal/ Р С‘ /pok/shine/
$anim_img_format = "gif";     // Р Т‘Р В»РЎРЏ /pok/anim/


// Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° РЎв‚¬Р В°Р в„–Р Р…Р С‘-РЎР‚Р ВµР В¶Р С‘Р СР В°
$shiny = isset($_GET['shiny']) && $_GET['shiny'] == '1' ? true : false;

require_once ("include/function/pdx.php");
function tip_pok_dex($tip, $sub){
    $tips_return = false;
    if($tip == "Grass")    $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/9.png\');" title="Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р в„–"></div>'; 
    if($tip == "Fire")     $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/6.png\');" title="Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„–"></div>'; 
    if($tip == "Water")    $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/17.png\');" title="Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„–"></div>'; 
    if($tip == "Bug")      $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/1.png\');" title="Р СњР В°РЎРѓР ВµР С”Р С•Р СР С•Р Вµ"></div>'; 
    if($tip == "Flying")   $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/7.png\');" title="Р вЂєР ВµРЎвЂљР В°РЎР‹РЎвЂ°Р С‘Р в„–"></div>'; 
    if($tip == "Normal")   $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/12.png\');" title="Р СњР С•РЎР‚Р СР В°Р В»РЎРЉР Р…РЎвЂ№Р в„–"></div>'; 
    if($tip == "Poison")   $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/13.png\');" title="Р Р‡Р Т‘Р С•Р Р†Р С‘РЎвЂљРЎвЂ№Р в„–"></div>'; 
    if($tip == "Electric") $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/4.png\');" title="Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„–"></div>'; 
    if($tip == "Ground")   $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/10.png\');" title="Р вЂ”Р ВµР СР В»РЎРЏР Р…Р Р…Р С•Р в„–"></div>'; 
    if($tip == "Fighting") $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/5.png\');" title="Р вЂР С•Р ВµР Р†Р С•Р в„–"></div>'; 
    if($tip == "Psychic")  $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/14.png\');" title="Р СџРЎРѓР С‘РЎвЂ¦Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„–"></div>'; 
    if($tip == "Rock")     $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/15.png\');" title="Р С™Р В°Р СР ВµР Р…Р Р…РЎвЂ№Р в„–"></div>'; 
    if($tip == "Ice")      $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/11.png\');" title="Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р в„–"></div>'; 
    if($tip == "Dragon")   $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/3.png\');" title="Р вЂќРЎР‚Р В°Р С”Р С•Р Р…"></div>'; 
    if($tip == "Steel")    $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/16.png\');" title="Р РЋРЎвЂљР В°Р В»РЎРЉР Р…Р С•Р в„–"></div>'; 
    if($tip == "Dark")     $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/2.png\');" title="Р СћРЎвЂР СР Р…РЎвЂ№Р в„–"></div>'; 
    if($tip == "Ghost")    $tips_return = '<div class="pokeTip_'.$sub.'" style="background-image: url(\'/pok/tip/8.png\');" title="Р СџРЎР‚Р С‘Р В·РЎР‚Р В°Р С”"></div>'; 
    return $tips_return;
}

function tip_description($tip){
    $descriptions = [
        "Grass"     => "Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р вЂ™Р С•Р Т‘Р Р…Р С•Р С–Р С•, Р вЂ”Р ВµР СР Р…Р С•Р С–Р С• Р С‘ Р С™Р В°Р СР ВµР Р…Р Р…Р С•Р С–Р С•.",
        "Fire"      => "Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р С–Р С•, Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р С–Р С•, Р СњР В°РЎРѓР ВµР С”Р С•Р СР С•Р С–Р С• Р С‘ Р РЋРЎвЂљР В°Р В»РЎРЉР Р…Р С•Р С–Р С•.",
        "Water"     => "Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р С›Р С–Р Р…Р ВµР Р…Р Р…Р С•Р С–Р С•, Р С™Р В°Р СР ВµР Р…Р Р…Р С•Р С–Р С• Р С‘ Р вЂ”Р ВµР СР Р…Р С•Р С–Р С•.",
        "Bug"       => "Р СњР В°РЎРѓР ВµР С”Р С•Р СРЎвЂ№Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р С–Р С•, Р СџРЎРѓР С‘РЎвЂ¦Р С‘РЎвЂЎР ВµРЎРѓР С”Р С•Р С–Р С• Р С‘ Р СћРЎвЂР СР Р…Р С•Р С–Р С•.",
        "Flying"    => "Р вЂєР ВµРЎвЂљР В°РЎР‹РЎвЂ°Р С‘Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р С–Р С•, Р СњР В°РЎРѓР ВµР С”Р С•Р СР С•Р С–Р С• Р С‘ Р вЂР С•Р ВµР Р†Р С•Р С–Р С•.",
        "Normal"    => "Р СњР С•РЎР‚Р СР В°Р В»РЎРЉР Р…РЎвЂ№Р в„– РЎвЂљР С‘Р С— Р Р…Р Вµ Р С‘Р СР ВµР ВµРЎвЂљ Р С•РЎРѓР С•Р В±РЎвЂ№РЎвЂ¦ Р С—РЎР‚Р ВµР С‘Р СРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†, Р Р…Р С• РЎС“РЎРѓРЎвЂљР С•Р в„–РЎвЂЎР С‘Р Р†.",
        "Poison"    => "Р Р‡Р Т‘Р С•Р Р†Р С‘РЎвЂљРЎвЂ№Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р С–Р С• Р С‘ Р В¤Р ВµР в„–РЎРѓР С”Р С•Р С–Р С•.",
        "Electric"  => "Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р вЂ™Р С•Р Т‘Р Р…Р С•Р С–Р С• Р С‘ Р вЂєР ВµРЎвЂљР В°РЎР‹РЎвЂ°Р ВµР С–Р С•.",
        "Ground"    => "Р вЂ”Р ВµР СР Р…Р С•Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р С›Р С–Р Р…Р ВµР Р…Р Р…Р С•Р С–Р С•, Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С‘РЎвЂЎР ВµРЎРѓР С”Р С•Р С–Р С•, Р Р‡Р Т‘Р С•Р Р†Р С‘РЎвЂљР С•Р С–Р С•, Р С™Р В°Р СР ВµР Р…Р Р…Р С•Р С–Р С• Р С‘ Р РЋРЎвЂљР В°Р В»РЎРЉР Р…Р С•Р С–Р С•.",
        "Fighting"  => "Р вЂР С•Р ВµР Р†Р С•Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р СњР С•РЎР‚Р СР В°Р В»РЎРЉР Р…Р С•Р С–Р С•, Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р С–Р С•, Р СћРЎвЂР СР Р…Р С•Р С–Р С•, Р РЋРЎвЂљР В°Р В»РЎРЉР Р…Р С•Р С–Р С• Р С‘ Р С™Р В°Р СР ВµР Р…Р Р…Р С•Р С–Р С•.",
        "Psychic"   => "Р СџРЎРѓР С‘РЎвЂ¦Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р вЂР С•Р ВµР Р†Р С•Р С–Р С• Р С‘ Р Р‡Р Т‘Р С•Р Р†Р С‘РЎвЂљР С•Р С–Р С•.",
        "Rock"      => "Р С™Р В°Р СР ВµР Р…Р Р…РЎвЂ№Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р вЂєР ВµРЎвЂљРЎС“РЎвЂЎР ВµР С–Р С•, Р С›Р С–Р Р…Р ВµР Р…Р Р…Р С•Р С–Р С•, Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р С–Р С• Р С‘ Р СњР В°РЎРѓР ВµР С”Р С•Р СР С•Р С–Р С•.",
        "Ice"       => "Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р вЂєР ВµРЎвЂљРЎС“РЎвЂЎР ВµР С–Р С•, Р вЂ”Р ВµР СР Р…Р С•Р С–Р С•, Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р С–Р С• Р С‘ Р вЂќРЎР‚Р В°Р С”Р С•Р Р…РЎРЉР ВµР С–Р С•.",
        "Dragon"    => "Р вЂќРЎР‚Р В°Р С”Р С•Р Р…Р С‘Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р Т‘РЎР‚РЎС“Р С–Р С‘РЎвЂ¦ Р вЂќРЎР‚Р В°Р С”Р С•Р Р…Р С•Р Р†.",
        "Steel"     => "Р РЋРЎвЂљР В°Р В»РЎРЉР Р…Р С•Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р С–Р С•, Р С™Р В°Р СР ВµР Р…Р Р…Р С•Р С–Р С• Р С‘ Р В¤Р ВµР в„–РЎРѓР С”Р С•Р С–Р С•.",
        "Dark"      => "Р СћРЎвЂР СР Р…РЎвЂ№Р в„– РЎвЂљР С‘Р С— РЎРѓР С‘Р В»РЎвЂР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р СџРЎРѓР С‘РЎвЂ¦Р С‘РЎвЂЎР ВµРЎРѓР С”Р С•Р С–Р С• Р С‘ Р СџРЎР‚Р С‘Р В·РЎР‚Р В°РЎвЂЎР Р…Р С•Р С–Р С•.",
        "Ghost"     => "Р СџРЎР‚Р С‘Р В·РЎР‚Р В°РЎвЂЎР Р…РЎвЂ№Р в„– РЎвЂљР С‘Р С— РЎРЊРЎвЂћРЎвЂћР ВµР С”РЎвЂљР С‘Р Р†Р ВµР Р… Р С—РЎР‚Р С•РЎвЂљР С‘Р Р† Р СџРЎРѓР С‘РЎвЂ¦Р С‘РЎвЂЎР ВµРЎРѓР С”Р С•Р С–Р С• Р С‘ Р СџРЎР‚Р С‘Р В·РЎР‚Р В°РЎвЂЎР Р…Р С•Р С–Р С•."
    ];
    return isset($descriptions[$tip]) ? $descriptions[$tip] : "Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ РЎвЂљР С‘Р С—Р В° Р С•РЎвЂљРЎРѓРЎС“РЎвЂљРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ.";
}


function tip_name_rus($tip){
    $names = [
        "Grass" => "Р СћРЎР‚Р В°Р Р†РЎРЏР Р…Р С•Р в„–",
        "Fire" => "Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„–",
        "Water" => "Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„–",
        "Bug" => "Р СњР В°РЎРѓР ВµР С”Р С•Р СР С•Р Вµ",
        "Flying" => "Р вЂєР ВµРЎвЂљР В°РЎР‹РЎвЂ°Р С‘Р в„–",
        "Normal" => "Р СњР С•РЎР‚Р СР В°Р В»РЎРЉР Р…РЎвЂ№Р в„–",
        "Poison" => "Р Р‡Р Т‘Р С•Р Р†Р С‘РЎвЂљРЎвЂ№Р в„–",
        "Electric" => "Р В­Р В»Р ВµР С”РЎвЂљРЎР‚Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„–",
        "Ground" => "Р вЂ”Р ВµР СР Р…Р С•Р в„–",
        "Fighting" => "Р вЂР С•Р ВµР Р†Р С•Р в„–",
        "Psychic" => "Р СџРЎРѓР С‘РЎвЂ¦Р С‘РЎвЂЎР ВµРЎРѓР С”Р С‘Р в„–",
        "Rock" => "Р С™Р В°Р СР ВµР Р…Р Р…РЎвЂ№Р в„–",
        "Ice" => "Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р в„–",
        "Dragon" => "Р вЂќРЎР‚Р В°Р С”Р С•Р Р…",
        "Steel" => "Р РЋРЎвЂљР В°Р В»РЎРЉР Р…Р С•Р в„–",
        "Dark" => "Р СћРЎвЂР СР Р…РЎвЂ№Р в„–",
        "Ghost" => "Р СџРЎР‚Р С‘Р В·РЎР‚Р В°РЎвЂЎР Р…РЎвЂ№Р в„–"
    ];
    return isset($names[$tip]) ? $names[$tip] : "Р СњР ВµР С‘Р В·Р Р†Р ВµРЎРѓРЎвЂљР Р…РЎвЂ№Р в„–";
}


if(!$text1 = evolutionPdx($poke_info['id'])){
    $res_evol = first('SELECT * FROM poke_base WHERE id=%d',$poke_info['evolution_type']);
    if(!$res_evol) { 
        $text1 = "<b>Р В­Р Р†Р С•Р В»РЎР‹РЎвЂ Р С‘Р С‘ Р Р…Р ВµРЎвЂљ, Р В»Р С‘Р В±Р С• Р Р…Р Вµ Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В°.</b>"; 
    } else {
        $res_evol_two = first('SELECT * FROM poke_base WHERE id=%d',$res_evol['evolution_type']);
        if(!$res_evol_two){ 
            $text2 = ""; 
        } else { 
            // Р ВРЎРѓР С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С•: Р В·Р В°Р СР ВµР Р…Р С‘Р В»Р С‘ $global_poke_img Р Р…Р В° $anim_img_format
            $text2 = $res_evol['evolution_lvl']."-lvl  => <a style='color:#000000;' href=game.php?go=pokedex&id=".$res_evol_two['id']."&shiny=".($shiny?'1':'0')."><img src=pok/anim/".$res_evol_two['id'].".".$anim_img_format."> #".$res_evol_two['title']."</a> "; 
        }
        $text1 = "<a style='color:#000000;' href=game.php?go=pokedex&id=".$poke_info['id']."&shiny=".($shiny?'1':'0')."> <img src='pok/anim/".$poke_info['id'].".".$anim_img_format."'> #".$poke_info['title']."</a>  ".$poke_info['evolution_lvl']."-lvl => <a style='color:#000000;' href=game.php?go=pokedex&id=".$res_evol['id']."&shiny=".($shiny?'1':'0')."><img src='pok/anim/".$res_evol['id'].".".$anim_img_format."'> #".$res_evol['title']."</a> ".$text2;
    }
}

$at = select('SELECT a.atc_lvl, ap.atac_name, ap.atac_id FROM attac_poke a
              INNER JOIN attac_power ap ON a.atac_id=ap.atac_id
              WHERE a.poke_base_id=%d
              ORDER BY atc_lvl ASC',$poke_info['id']);
$at_1 = '';
$at_2 = '';
$at_3 = '';
if(!$at){
    $at_1 = "Р СњР В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ РЎС“ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р ВµРЎвЂљ Р В°РЎвЂљР В°Р С”.";
}else{
    foreach($at as $atcs){
        $at_1 .= '<div class="attack_button">'.$atcs['atc_lvl'].'-lvl <a href="javascript:window.open(\'/game.php?go=atk&id='.$atcs['atac_id'].'\',\'atk\',\'width=726,height=260,scrollbars=yes\');return true;"><img src="img/other/inf.png" alt="I"></a> '.$atcs['atac_name'].'</div>';
    }
}
$at2 = select('SELECT ap.atac_name, ap.atac_id FROM attac_egg a INNER JOIN attac_power ap ON a.atac_id=ap.atac_id WHERE a.poke_base_id=%d ORDER BY a.atac_id ASC',$poke_info['id']);
if(!$at2){
    $at_2 = "Р Р€ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р ВµРЎвЂљ РЎРЏР в„–РЎвЂ Р ВµР Р†РЎвЂ№РЎвЂ¦ Р В°РЎвЂљР В°Р С”.";
}else {
    $a = 1;
    foreach($at2 as $atcs2){
        $at_2 .= '<div class="attack_button">'.$a++.': <a href="javascript:window.open(\'/game.php?go=atk&id='.$atcs2['atac_id'].'\',\'atk\',\'width=726,height=260,scrollbars=yes\');return true;"><img src="img/other/inf.png" alt="I"></a> '.$atcs2['atac_name'].'</div>';
    }
}
$at3 = select('SELECT pb.id, b.title FROM pokebuild pb
                      INNER JOIN build b
                      ON pb.building=b.id
                      WHERE pb.baseid=%d AND pb.poimka=1',$poke_info['id']);
if(!$at3){
    $at_3 = 'Р СњР В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ Р С•Р В±Р С‘РЎвЂљР В°Р Р…Р С‘Р Вµ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р ВµР С‘Р В·Р Р†Р ВµРЎРѓРЎвЂљР Р…Р С•.';
}else {
    $a = 1;
    foreach($at3 as $atcs3){
        $at_3 .= '<div class="attack_button">'.$a++.': '.$atcs3['title'].'</div>';
    }
}
$search_add = '';
if(!empty($_GET['search'])){
    $text = $_GET['search'];
    if($text[0]==='#') $text = substr($txt, 1);
    $text = stripslashes($text);
    $text = htmlspecialchars($text);
    $text = str_replace('"',"&quot;",$text);
    $text = str_replace("'","&#39;",$text);
    $search = select('SELECT id,title FROM poke_base WHERE id LIKE "%'.mysql_real_escape_string($text).'%" OR title LIKE "%'.mysql_real_escape_string($text).'%"');
    if($search){
        $a = 1;
        $id_s = '';
        foreach($search as $search_go){
            $id_s = $search_go['id'];
            $search_add .= '<div class="attack_button poke" onclick="location.href=\'/game.php?go=pokedex&id='.$search_go['id'].'&shiny='.($shiny?'1':'0').'\'">'.$a++.': #'.$search_go['title'].'</div>';
        }
        if($a == 2 && $id_s)  header("Location: /game.php?go=pokedex&id=".$id_s."&shiny=".($shiny?'1':'0'));
        $search_add = '<div class="search_isset">'.$search_add.'</div>';
    }else{
        $search_add = '<div class="search_error" onclick="this.style.display=\'none\';">Р СџР С•Р С”Р ВµР СР С•Р Р… Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р….</div>';
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
    <style type="text/css">
        body, html{
            background: #6e6e6e;
            font-family: verdana,serif;
            font-size: 12px;
            color: #000;
            min-width: 500px;
            margin: 0;
            padding: 0;
        }
        .content{
            position: relative;
            width: 96%;
            background-color: #aeaeae;
            margin-left: 2%;
            margin-right: 2%;
            margin-top: 1.3%;
            border-radius: 6px;
            padding: 10px;
            box-sizing: border-box;
        }
        .pokImg{
            width: 200px;
            height: 200px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 6px;
            position: relative;
            float: left;
            margin-right: 20px;
        }
        .pokeTip_a{
            position: absolute;
            width: 45px;
            height: 45px;
            left: 3px;
            bottom: -20px;
        }
        .pokeTip_b{
            position: absolute;
            width: 45px;
            height: 45px;
            left: 50px;
            bottom: -20px;
        }
        .stat{
            font-size: 18px;
            margin-bottom: 20px;
        }
        .pokName{
            position: absolute;
            top: 0;
            left: 0;
            font-size: 20px;
            font-weight: bold;
            font-family: "Courier New", Courier, monospace;
            padding: 2px;
            color: #000;
            background-color: #b4b0a7;
            opacity: 0.8;
            border-radius: 5px 0 6px 0;
        }
						.typeBadge {
		display: inline-block;
		padding: 3px 8px;
		margin: 2px 4px 2px 0;
		font-size: 13px;
		font-weight: bold;
		border-radius: 12px;
		color: #fff;
		cursor: default;
		font-family: "Courier New", Courier, monospace;
		box-shadow: 0 0 2px #000;
	}
	.typeBadge:hover {
		opacity: 0.85;
	}
	
	.typeBadgeBox {
    position: absolute;
    top: 25px;
    left: 0;
}

.typeBadge:hover .typeTooltip {
    display: block;
}

.typeTooltip {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    width: 190px;
    background-color: #eaeaea;
    color: #000;
    font-size: 11px;
    font-family: "Courier New", Courier, monospace;
    padding: 5px;
    border-radius: 5px;
    box-shadow: 0 0 2px #000;
    z-index: 10;
    white-space: normal;
}


	.type-Grass     { background-color: #78C850; }
	.type-Fire      { background-color: #F08030; }
	.type-Water     { background-color: #6890F0; }
	.type-Bug       { background-color: #A8B820; }
	.type-Flying    { background-color: #A890F0; }
	.type-Normal    { background-color: #A8A878; }
	.type-Poison    { background-color: #A040A0; }
	.type-Electric  { background-color: #F8D030; color: #000; }
	.type-Ground    { background-color: #E0C068; color: #000; }
	.type-Fighting  { background-color: #C03028; }
	.type-Psychic   { background-color: #F85888; }
	.type-Rock      { background-color: #B8A038; }
	.type-Ice       { background-color: #98D8D8; color: #000; }
	.type-Dragon    { background-color: #7038F8; }
	.type-Steel     { background-color: #B8B8D0; color: #000; }
	.type-Dark      { background-color: #705848; }
	.type-Ghost     { background-color: #705898; }

        .pokEvol{
            font-weight: bold;
            font-size: 16px;
            font-family: "Courier New", Courier, monospace;
            margin-bottom: 20px;
            clear: both;
        }
        .content_attack{
            text-align: center;
            margin-top: 20px;
        }
        .attack_title{
            padding: 3px;
            margin: 2px;
            font-size: 18px;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            border: 1px solid #000;
            color: #000;
            border-radius: 3px;
            box-shadow: 0 0 1px 1px #727272;
            background-color: #999;
            display: inline-block;
            cursor: pointer;
        }
        .content_attack_title{
            text-align: left;
            padding: 15px;
            font-size: 15px;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            margin-top: 10px;
            background-color: #999;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }
        .tab{
            display: none;
            visibility: hidden;
        }
        .attack_button{
            padding: 2px;
            margin: 3px;
            font-size: 18px;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            border: 1px solid #000;
            color: #000;
            border-radius: 3px;
            box-shadow: 0 0 1px 1px #727272;
            background-color: #999;
            text-align: left;
        }
        .poke{
            cursor: pointer;
            height: 18px;
            font-size: 13px !important;
        }
        .poke:hover{
            border-color: #ffffff;
            box-shadow: 0 0 1px 1px #ffffff;
        }
        .select{
            border-color: #e6da29;
            box-shadow: 0 0 1px 1px #e6da29;
        }
        .search{
            position: absolute;
            top: 10px;
            right: 10px;
            width: 200px;
        }
        .search_ico{
            background: url("/img/other/search.png") no-repeat;
            position: absolute;
            top: 4px;
            right: 6px;
            width: 17px;
            height: 17px;
            cursor: pointer;
        }
        .search input{
            width: 100%;
            height: 25px;
            background-color: #6e6e6e;
            border: 1px solid #aeaeae;
            border-radius: 0 5px 0 5px;
            color: #c7c9cf;
            font-size: 16px;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
            padding: 3px 25px 3px 3px;
        }
        .search_error{
            position: absolute;
            top: 24px;
            left: 0;
            width: 100%;
            min-height: 22px;
            border-top: 1px solid #525252;
            border-bottom: 1px solid #525252;
            color: #000;
            background-color: #ddbfd3;
            font-size: 14px;
            padding-top: 3px;
            padding-bottom: 3px;
            text-align: center;
            font-family: "Courier New", Courier, monospace;
            display: block;
            cursor: pointer;
        }
        .search_isset{
            position: absolute;
            top: 24px;
            width: 100%;
            max-height: 150px;
            z-index: 1;
            overflow-y: auto;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
    <script type='text/javascript' src='script/jquery.js'></script>
    <script type="text/javascript">
        $(function(){
            $('.attack_title').click(function(){
                $('span.attack_title').removeClass("select");
                $(this).addClass("select");
                $('#content_attack_title').animate({"scrollTop":0},1);
            });
            
            // Р СџРЎР‚Р С•Р Р†Р ВµРЎР‚Р С”Р В° РЎв‚¬Р В°Р в„–Р Р…Р С‘-РЎР‚Р ВµР В¶Р С‘Р СР В° Р С—РЎР‚Р С‘ Р В·Р В°Р С–РЎР‚РЎС“Р В·Р С”Р Вµ
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('shiny') === '1') {
                var img = document.querySelector('.pokImg');
                var id = img.getAttribute('data-id');
                var ext = img.getAttribute('data-ext');
                img.style.backgroundImage = "url('/pok/shine/" + id + "." + ext + "')";
            }
        });
        
        function checkKey(e, el) {
            return e.keyCode === el;
        }
        
        function toggleShiny(element) {
            var currentBg = element.style.backgroundImage;
            var id = element.getAttribute('data-id');
            var ext = element.getAttribute('data-ext');
            
            if (currentBg.indexOf('shine') !== -1) {
                element.style.backgroundImage = "url('/pok/normal/" + id + "." + ext + "')";
                updateShinyParam(0);
            } else {
                element.style.backgroundImage = "url('/pok/shine/" + id + "." + ext + "')";
                updateShinyParam(1);
            }
        }
        
        function updateShinyParam(value) {
            var url = window.location.href;
            var params = new URLSearchParams(window.location.search);
            params.set('shiny', value);
            
            if (window.history.replaceState) {
                var newUrl = window.location.pathname + '?' + params.toString();
                window.history.replaceState(null, '', newUrl);
            }
        }
        
        function searchWithShiny(query) {
            var shiny = new URLSearchParams(window.location.search).get('shiny') || '0';
            location.href = '/game.php?go=pokedex&id=<?=$poke_info['id'];?>&search=' + encodeURIComponent(query) + '&shiny=' + shiny;
        }
    </script>
</head>
<body>
    <div class="content clearfix">
        <div class="pokImg" 
     style="background-image: url('/pok/<?= $shiny ? 'shine' : 'normal'; ?>/<?=$poke_info['id'];?>.<?=$poke_img_format;?>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;"
     data-id="<?=$poke_info['id'];?>" 
     data-ext="<?=$poke_img_format;?>"
     onclick="toggleShiny(this)">
     
    <div class="pokName">#<?=$poke_info['title'];?></div>
/*
 <div style="position:absolute; top:25px; left:0;">
    <?php if(!empty($poke_info['Element'])): ?>
        <span class="typeBadge type-<?=$poke_info['Element'];?>">
            <?=tip_name_rus($poke_info['Element']);?>
            <div class="typeTooltip">
                <b><?=tip_name_rus($poke_info['Element']);?>:</b> <?=tip_description($poke_info['Element']);?>
            </div>
        </span>
    <?php endif; ?>

    <?php if(!empty($poke_info['SubElement'])): ?>
        <span class="typeBadge type-<?=$poke_info['SubElement'];?>">
            <?=tip_name_rus($poke_info['SubElement']);?>
            <div class="typeTooltip">
                <b><?=tip_name_rus($poke_info['SubElement']);?>:</b> <?=tip_description($poke_info['SubElement']);?>
            </div>
        </span>
    <?php endif; ?>
</div>
*/

<div style="position:absolute; top:55px; left:0; width:190px; font-size:11px; font-family:'Courier New', Courier, monospace; background-color:#eaeaea; padding:5px; border-radius:5px; box-shadow:0 0 2px #000;">
    <?php if(!empty($poke_info['Element'])): ?>
        <b><?=tip_name_rus($poke_info['Element']);?>:</b> <?=tip_description($poke_info['Element']);?><br/>
    <?php endif; ?>
    <?php if(!empty($poke_info['SubElement'])): ?>
        <b><?=tip_name_rus($poke_info['SubElement']);?>:</b> <?=tip_description($poke_info['SubElement']);?>
    <?php endif; ?>
</div>



    <?=tip_pok_dex($poke_info['Element'],'a').' '.tip_pok_dex($poke_info['SubElement'],'b');?>
</div>

        
        <div class="stat">
            <span style="font-weight: bold; color: #000">Р ТђР С—: <?=$poke_info['hp'];?> </span><br/>
            <span style="font-weight: bold; color: #000">Р С’РЎвЂљР В°Р С”Р В°: <?=$poke_info['atk'];?></span> <br/>
            <span style="font-weight: bold; color: #000">Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°: <?=$poke_info['def'];?> </span><br/>
            <span style="font-weight: bold; color: #000">Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ: <?=$poke_info['speed'];?> </span><br/>
            <span style="font-weight: bold; color: #000">Р РЋР С—Р ВµРЎвЂ . Р В°РЎвЂљР В°Р С”Р В°: <?=$poke_info['satk'];?> </span><br/>
            <span style="font-weight: bold; color: #000">Р РЋР С—Р ВµРЎвЂ . Р В·Р В°РЎвЂ°Р С‘РЎвЂљР В°: <?=$poke_info['sdef'];?> </span><br/>
        </div>
        
        <div class="pokEvol">
            Р В­Р Р†Р С•Р В»РЎР‹РЎвЂ Р С‘РЎРЏ: <?=$text1;?>
        </div>
        
        <div class="content_attack">
            <span class="attack_title select" onclick="document.getElementById('content_attack_title').innerHTML=document.getElementById('tab_1').innerHTML;">Р С’РЎвЂљР В°Р С”Р С‘</span>
            <span class="attack_title" onclick="document.getElementById('content_attack_title').innerHTML=document.getElementById('tab_2').innerHTML;">Р Р‡Р в„–РЎвЂ Р ВµР Р†РЎвЂ№Р Вµ</span>
            <span class="attack_title" onclick="document.getElementById('content_attack_title').innerHTML=document.getElementById('tab_3').innerHTML;">Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•РЎРѓРЎвЂљРЎРЉ</span>
            <div class="content_attack_title" id="content_attack_title">
               <?=$at_1;?>
            </div>
            <div class="tab" id="tab_1"><?=$at_1;?></div>
            <div class="tab" id="tab_2"><?=$at_2;?></div>
            <div class="tab" id="tab_3"><?=$at_3;?></div>
        </div>
        
        <div class="search" id="search">
            <label>
                <input id="search_input" value="Р СџР С•Р С‘РЎРѓР С” Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°" maxlength="50" 
                       onkeydown="if(checkKey(event,13)) searchWithShiny(this.value);" 
                       onclick="if(this.value==='Р СџР С•Р С‘РЎРѓР С” Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°') this.value='';" 
                       onblur="if(this.value=='') this.value='Р СџР С•Р С‘РЎРѓР С” Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°';"/>
            </label>
            <div class="search_ico" onclick="searchWithShiny(document.getElementById('search_input').value)"> </div>
            <?=$search_add;?>
        </div>
    </div>
</body>
</html>