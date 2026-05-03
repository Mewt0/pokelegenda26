<?php
    $person = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "#144 Articuno";
    $href = '/game.php?go=char&quest_npc='.$person.'&do=';

if(!empty($do)){
  if(!empty($q2x6)){  
   if(quest_process(2, 6)){ 
    switch ($do){                  
      case 1:
            $about = '<b>[Р С›Р С–РЎР‚Р С•Р СР Р…Р В°РЎРЏ Р С—РЎвЂљР С‘РЎвЂ Р В°, РЎРѓ Р С–Р С•Р В»РЎС“Р В±РЎвЂ№Р С Р С•Р С”РЎР‚Р В°РЎРѓР С•Р С, Р Р†Р ВµР В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…Р С• РЎРѓР С‘Р Т‘Р ВµР В»Р В° Р Р…Р В° Р Р†Р ВµРЎР‚РЎв‚¬Р С‘Р Р…Р Вµ РЎРѓР В°Р СР С•Р в„– Р Р†РЎвЂ№РЎРѓР С•Р С”Р С•Р в„– Р С–Р С•РЎР‚РЎвЂ№. 
             Р С’РЎР‚РЎвЂљР С‘Р С”РЎС“Р Р…Р С• РЎРѓР С—Р С•Р С”Р С•Р в„–Р Р…Р С• РЎРѓР СР С•РЎвЂљРЎР‚Р ВµР В» Р С”РЎС“Р Т‘Р В°-РЎвЂљР С• Р Р† Р Т‘Р В°Р В»РЎРЉ Р С‘ Р С—РЎР‚Р С•РЎРѓРЎвЂљР С• Р Р…Р Вµ Р В·Р В°Р СР ВµРЎвЂЎР В°Р В» Р вЂ™Р В°РЎРѓ. 
             Р СњРЎС“Р В¶Р Р…Р С• Р С”Р В°Р С”Р С‘Р С-РЎвЂљР С• Р С•Р В±РЎР‚Р В°Р В·Р С•Р С Р С—Р С•Р в„–Р СР В°РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р С‘ Р С•РЎвЂљР Р…Р ВµРЎРѓРЎвЂљР С‘ Р ВµР С–Р С• Р Р…Р В° РЎвЂљР С• РЎРѓР В°Р СР С•Р Вµ Р С•Р В·Р ВµРЎР‚Р С•, Р С–Р Т‘Р Вµ Р вЂ™РЎвЂ№ Р С—Р С•Р Р†РЎРѓРЎвЂљРЎР‚Р ВµРЎвЂЎР В°Р В»Р С‘ Р С’Р в„–РЎР‚Р ВµР Р…Р В°. 
             Р СџРЎР‚Р С•РЎРѓРЎвЂљРЎвЂ№Р С Р С—Р С•Р С”Р ВµР В±Р С•Р В»Р В»Р С•Р С Р ВµР С–Р С• Р Р…Р Вµ Р С—Р С•Р в„–Р СР В°РЎвЂљРЎРЉ, Р С—Р С•РЎРЊРЎвЂљР С•Р СРЎС“ Р вЂ™Р В°Р С Р С—РЎР‚Р С‘Р Т‘Р ВµРЎвЂљРЎРѓРЎРЏ Р С•РЎвЂљРЎвЂ№РЎРѓР С”Р В°РЎвЂљРЎРЉ Р С•РЎРѓР С•Р В±Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С—Р С•Р С”Р ВµР В±Р С•Р В»Р В», Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р в„– Р СР С•Р С– Р В±РЎвЂ№ Р С—Р С•Р в„–Р СР В°РЎвЂљРЎРЉ Р С’РЎР‚РЎвЂљР С‘Р С”РЎС“Р Р…Р С• Р С‘ Р В·Р В°Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ Р ВµР С–Р С• Р Р†Р Р…РЎС“РЎвЂљРЎР‚Р С‘ Р Р…Р В° Р Р…Р ВµР С”Р С•РЎвЂљР С•РЎР‚Р С•Р Вµ Р Р†РЎР‚Р ВµР СРЎРЏ.]</b>';  
            $pers    = '<a href="'.$href.'2"  target="_chat_two">*Р вЂ™РЎвЂ№ РЎР‚Р ВµРЎв‚¬Р С‘Р В»Р С‘ Р Р…Р Вµ Р В±Р ВµРЎРѓР С—Р С•Р С”Р С•Р С‘РЎвЂљРЎРЉ Р С’РЎР‚РЎвЂљР С‘Р С”РЎС“Р Р…Р С• РЎР‚Р В°Р Р…РЎРЉРЎв‚¬Р Вµ Р Р†РЎР‚Р ВµР СР ВµР Р…Р С‘ Р С‘ РЎС“РЎв‚¬Р В»Р С‘ Р С‘РЎРѓР С”Р В°РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР В±Р С•Р В»Р В» Р Т‘Р В»РЎРЏ Р ВµР С–Р С• Р С—Р С•Р С‘Р СР С”Р С‘* <sup>[Р Р€Р в„–РЎвЂљР С‘]</sup></a>';
      break;
      case 2:
        if(!empty($q2x6)){
         quest_update(2, 7, 0);
         insQuePoke(2,144,1);
        }
        die("<script>parent._location.location.href='game.php?go=char';</script>");
      break;
      default:
         die("<script>location.href='game.php?go=char';</script>");
    } 
   }elseif(quest_process(2, 7)){
    switch ($do){                  
      case 1:
        if(qCountPoke(1) && $myrow['pvp'] == 0 && $myrow['pve'] == 0 && $myrow['trade'] == 0){
             minus_item(1,46);
             $idNums  = 2000000000;
             $questupdate = 2;
             $lvlPok = 1000;
             $baseNum = 144;
             $iv = rand(50,100);  $iv2 = rand(50,100);  $iv3  = rand(50,100);
             $ev = rand(150,300); $ev2 = rand(150,300); $ev3  = rand(150,300);
             $sex = 1;
             $hp = pokeStatZap(1,$lvlPok,$iv ,$ev3,'hp'   ,$baseNum)+120000;
             $at = pokeStatZap(1,$lvlPok,$iv2,$ev ,'atk'  ,$baseNum);
             $df = pokeStatZap(1,$lvlPok,$iv ,$ev3,'def'  ,$baseNum);
             $sa = pokeStatZap(1,$lvlPok,$iv2,$ev ,'satk' ,$baseNum);
             $sd = pokeStatZap(1,$lvlPok,$iv ,$ev3,'sdef' ,$baseNum);
             $sp = pokeStatZap(1,$lvlPok,$iv3,$ev2,'speed',$baseNum);
             $tipes = 'normal';
             $names = pokeStatZap(2,'title','','','',$baseNum);
               $idPok = insert('pok_pve',array(
                               'users'=>$_SESSION['id'],
                               'basenum'=>$baseNum,
                               'names'=>$names,
                               'lvl'=>$lvlPok,
                               'sex'=>$sex,
                               'hp_my'=>$hp,
                               'hp_max'=>$hp,
                               'atk'=>$at,
                               'def'=>$df,
                               'satk'=>$sa,
                               'sdef'=>$sd,
                               'speed'=>$sp,
                               'hp_ev'=>$ev3,
                               'atk_ev'=>$ev,
                               'def_ev'=>$ev3,
                               'satk_ev'=>$ev,
                               'sdef_ev'=>$ev3,
                               'speed_ev'=>$ev2,
                               'hp_iv'=>$iv,
                               'atk_iv'=>$iv2,
                               'def_iv'=>$iv,
                               'satk_iv'=>$iv2,
                               'sdef_iv'=>$iv,
                               'speed_iv'=>$iv3,
                               'tips'=>$tipes,
                               'startepoke'=>$idNums,
                               'reproduction'=>$questupdate,
                               'poimka'=>0));
               
               $pokemon_start = first('SELECT id FROM pok_user WHERE users=%d AND active=1 AND startepoke=1 AND hp_my>0',$_SESSION['id']);
               $p_users = $pokemon_start['id'];
               if(empty($pokemon_start['id'])){
                  $pokemon_start = first('SELECT id, CEIL(RAND()*id) as chance FROM pok_user WHERE users=%d AND active=1 AND hp_my>0 ORDER BY chance DESC LIMIT 0,1',$_SESSION['id']);
                  $p_users = $pokemon_start['id'];
               }
               $time_pve = time() + 3600;
                $battl = insert('battles',array(
                       'user_1'=>$_SESSION['id'],
                       'user_2'=>$idPok,
                       'poke_1'=>'pvp_'.$p_users,
                       'poke_2'=>'pve_'.$idPok,
                       'batl_tip'=>'pve', 
                       'times'=>$time_pve));
               insert('statpokemonbatle',array('battleid'=>$battl,'pokeid'=>'pve_'.$idPok,'accuracy'=>6,'acc'=>6,'tip'=>'plus'));
               update('users',array('pve'=>1, 'battleid'=>$battl),'id='.(int)$_SESSION['id']);  
        }
          
            $about = '<b>[Р вЂ™Р С•РЎвЂљ Р С‘ Р Р…Р В°РЎРѓРЎвЂљР В°Р В»Р С• Р Р†РЎР‚Р ВµР СРЎРЏ Р Т‘Р В»РЎРЏ Р С—Р С•Р С‘Р СР С”Р С‘ Р С’РЎР‚РЎвЂљР С‘Р С”РЎС“Р Р…Р С•. 
                          Р СљР ВµР Т‘Р В»Р ВµР Р…Р Р…Р С• Р С—Р С•Р Т‘РЎвЂ¦Р С•Р Т‘РЎРЏ Р С” Р С—РЎвЂљР С‘РЎвЂ Р Вµ, Р вЂ™РЎвЂ№ РЎРѓРЎвЂљР В°РЎР‚Р В°Р ВµРЎвЂљР ВµРЎРѓРЎРЉ Р Р…Р Вµ Р Р…Р В°РЎв‚¬РЎС“Р СР ВµРЎвЂљРЎРЉ. 
                          Р вЂ™Р С•РЎвЂљ Р ВµРЎвЂ°Р Вµ РЎвЂЎРЎС“РЎвЂљРЎРЉ-РЎвЂЎРЎС“РЎвЂљРЎРЉ Р С‘ Р вЂ™РЎвЂ№ Р С•Р С”Р В°Р В¶Р ВµРЎвЂљР ВµРЎРѓРЎРЉ РЎРѓР С•Р Р†РЎРѓР ВµР С Р В±Р В»Р С‘Р В·Р С”Р С• РЎРѓ Р В»Р ВµР С–Р ВµР Р…Р Т‘Р С•Р в„–. 
                          Р СњР С• Р Р†Р Р…Р ВµР В·Р В°Р С—Р Р…Р С• Р вЂ™РЎвЂ№ Р С•РЎРѓРЎвЂљРЎС“Р С—Р С‘Р В»Р С‘РЎРѓРЎРЉ Р С‘ РЎвЂЎРЎС“РЎвЂљРЎРЉ Р В±РЎвЂ№Р В»Р С• Р Р…Р Вµ РЎС“Р С—Р В°Р В»Р С‘. 
                          Р ВР В·-Р В·Р В° РЎРЊРЎвЂљР С•Р С–Р С• Р С’РЎР‚РЎвЂљР С‘Р С”РЎС“Р Р…Р С• РЎС“РЎРѓР В»РЎвЂ№РЎв‚¬Р В°Р В» Р вЂ™Р В°РЎРѓ Р С‘ РЎвЂЎРЎвЂљР С•-РЎвЂљР С• Р С–РЎР‚Р С•Р СР С”Р С• Р С—РЎР‚Р С•Р С”РЎР‚Р С‘РЎвЂЎР В°Р В» Р Р…Р В° РЎРѓР Р†Р С•Р ВµР С РЎРЏР В·РЎвЂ№Р С”Р Вµ. 
                          Р вЂ™РЎвЂ№ Р В·Р В°Р СР ВµРЎвЂљР С‘Р В»Р С‘, РЎвЂЎРЎвЂљР С• Р С—РЎвЂљР С‘РЎвЂ Р В° Р В°Р С–РЎР‚Р ВµРЎРѓРЎРѓР С‘Р Р†Р Р…Р С• Р Р…Р В°РЎРѓРЎвЂљРЎР‚Р С•Р ВµР Р…Р В° Р С‘ Р В±Р С•РЎРЏ РЎС“Р В¶Р Вµ Р Р…Р Вµ Р С‘Р В·Р В±Р ВµР В¶Р В°РЎвЂљРЎРЉ.]</b>';  
            $pers    = '<a href="'.$href.'2"  target="_chat_two">*Р вЂ™РЎвЂ№ Р С—Р С•РЎвЂљРЎРЏР Р…РЎС“Р В»Р С‘РЎРѓРЎРЉ РЎР‚РЎС“Р С”Р С•Р в„– Р Р† РЎРѓР Р†Р С•РЎР‹ РЎРѓРЎС“Р СР С”РЎС“, РЎвЂЎРЎвЂљР С• Р В±РЎвЂ№ Р С—РЎР‚Р С‘Р В·Р Р†Р В°РЎвЂљРЎРЉ Р С•Р Т‘Р Р…Р С•Р С–Р С• Р С‘Р В· РЎРѓР Р†Р С•Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†...* <sup>[Р вЂР С•Р в„– РЎРѓ Р В»Р ВµР С–Р ВµР Р…Р Т‘Р С•Р в„– Р Р…Р В°РЎвЂЎР В°Р В»РЎРѓРЎРЏ]</sup></a>';
      break;
      case 2:
        if(!empty($q2x6)){
          print "<script>parent.loc('fight_pve');</script>";
        }
        die("<script>parent._location.location.href='game.php?go=char';</script>");
      break;
      default:
         die("<script>location.href='game.php?go=char';</script>");
    }   
   }else{
     die("<script>location.href='game.php?go=char';</script>");
   }
  }
}else{
  die("<script>location.href='game.php?go=char';</script>");
}
?>