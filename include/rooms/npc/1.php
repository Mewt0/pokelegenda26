<?
if($_GET['npc']==1){
    $quest_isset_const = 1;
    if($_GET['do_npc']==2){  
        include ("1_2.php"); 
        exit;
    }
    elseif($_GET['do_npc']==1){  
        query('UPDATE pok_user SET hp_my=hp_max WHERE users=%d AND active=1',$_SESSION['id']);
        $p_online = select('SELECT id FROM pok_user WHERE users=%d AND active=1',$_SESSION['id']);
        foreach($p_online as $p_online_res){
          query('UPDATE attac_my_poke SET a_pp_min=a_pp_max, b_pp_min=b_pp_max, c_pp_min=c_pp_max, d_pp_min=d_pp_max WHERE pok_id=%d',$p_online_res['id']);
        }
        $about = "Ваши покемоны полностью вылечены, Вы можете продолжать свой путь.";
        $name = "Сестра Джой";
        $pers = "<a href='game.php?go=char'>Спасибо!<sup>[Уйти]</sup></a>" ;
    }elseif($_GET['do_npc'] == 'pc'){
        $name = "Сестра Джой";
        $about = "Здравствуйте, добро пожаловать в наш покецентр, чем я могу Вам помочь?";
        $pers = "<a href='game.php?go=char&npc=1&do_npc=1'>Вылечите, пожалуйста, моих покемонов</a><a href='game.php?go=char&npc=1&do_npc=2'>Питомник</a><a href='game.php?go=char'>Уйти</a>" ;
    } 
}
  else
  {
    echo "<script>location.href='game.php?go=char';</script>"; 
    exit;
  }

?>