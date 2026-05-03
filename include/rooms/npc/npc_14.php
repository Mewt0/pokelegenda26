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
            $about = '<b>[Огромная птица, с голубым окрасом, величественно сидела на вершине самой высокой горы. 
             Артикуно спокойно смотрел куда-то в даль и просто не замечал Вас. 
             Нужно каким-то образом поймать этого покемона и отнести его на то самое озеро, где Вы повстречали Айрена. 
             Простым покеболлом его не поймать, поэтому Вам придется отыскать особенный покеболл, который мог бы поймать Артикуно и задержать его внутри на некоторое время.]</b>';  
            $pers    = '<a href="'.$href.'2"  target="_chat_two">*Вы решили не беспокоить Артикуно раньше времени и ушли искать покеболл для его поимки* <sup>[Уйти]</sup></a>';
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
          
            $about = '<b>[Вот и настало время для поимки Артикуно. 
                          Медленно подходя к птице, Вы стараетесь не нашуметь. 
                          Вот еще чуть-чуть и Вы окажетесь совсем близко с легендой. 
                          Но внезапно Вы оступились и чуть было не упали. 
                          Из-за этого Артикуно услышал Вас и что-то громко прокричал на своем языке. 
                          Вы заметили, что птица агрессивно настроена и боя уже не избежать.]</b>';  
            $pers    = '<a href="'.$href.'2"  target="_chat_two">*Вы потянулись рукой в свою сумку, что бы призвать одного из своих покемонов...* <sup>[Бой с легендой начался]</sup></a>';
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