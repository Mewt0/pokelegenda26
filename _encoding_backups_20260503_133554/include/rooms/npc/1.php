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
        $about = "Р вЂ™Р В°РЎв‚¬Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎвЂ№ Р С—Р С•Р В»Р Р…Р С•РЎРѓРЎвЂљРЎРЉРЎР‹ Р Р†РЎвЂ№Р В»Р ВµРЎвЂЎР ВµР Р…РЎвЂ№, Р вЂ™РЎвЂ№ Р СР С•Р В¶Р ВµРЎвЂљР Вµ Р С—РЎР‚Р С•Р Т‘Р С•Р В»Р В¶Р В°РЎвЂљРЎРЉ РЎРѓР Р†Р С•Р в„– Р С—РЎС“РЎвЂљРЎРЉ.";
        $name = "Р РЋР ВµРЎРѓРЎвЂљРЎР‚Р В° Р вЂќР В¶Р С•Р в„–";
        $pers = "<a href='game.php?go=char'>Р РЋР С—Р В°РЎРѓР С‘Р В±Р С•!<sup>[Р Р€Р в„–РЎвЂљР С‘]</sup></a>" ;
    }elseif($_GET['do_npc'] == 'pc'){
        $name = "Р РЋР ВµРЎРѓРЎвЂљРЎР‚Р В° Р вЂќР В¶Р С•Р в„–";
        $about = "Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, Р Т‘Р С•Р В±РЎР‚Р С• Р С—Р С•Р В¶Р В°Р В»Р С•Р Р†Р В°РЎвЂљРЎРЉ Р Р† Р Р…Р В°РЎв‚¬ Р С—Р С•Р С”Р ВµРЎвЂ Р ВµР Р…РЎвЂљРЎР‚, РЎвЂЎР ВµР С РЎРЏ Р СР С•Р С–РЎС“ Р вЂ™Р В°Р С Р С—Р С•Р СР С•РЎвЂЎРЎРЉ?";
        $pers = "<a href='game.php?go=char&npc=1&do_npc=1'>Р вЂ™РЎвЂ№Р В»Р ВµРЎвЂЎР С‘РЎвЂљР Вµ, Р С—Р С•Р В¶Р В°Р В»РЎС“Р в„–РЎРѓРЎвЂљР В°, Р СР С•Р С‘РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</a><a href='game.php?go=char&npc=1&do_npc=2'>Р СџР С‘РЎвЂљР С•Р СР Р…Р С‘Р С”</a><a href='game.php?go=char'>Р Р€Р в„–РЎвЂљР С‘</a>" ;
    } 
}
  else
  {
    echo "<script>location.href='game.php?go=char';</script>"; 
    exit;
  }

?>