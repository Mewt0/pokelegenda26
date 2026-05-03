<?
if (!empty($_POST['id_pok']) && (!empty($_POST['smena1']) OR !empty($_POST['smena2']) OR !empty($_POST['smena3']) OR !empty($_POST['smena4']))) {             
  if(!empty($_POST['smena1'])){
    $id_atac = obr_chis($_POST['smena1']);
    $ac = 'a_id';
    $bc = 'a_pp_min';
    $dc = 'a_pp_max';
  }
  elseif(!empty($_POST['smena2'])){
    $id_atac = obr_chis($_POST['smena2']);
    $ac = 'b_id';
    $bc = 'b_pp_min';
    $dc = 'b_pp_max';
  }
  elseif(!empty($_POST['smena3'])){
    $id_atac = obr_chis($_POST['smena3']);
    $ac = 'c_id';
    $bc = 'c_pp_min';
    $dc = 'c_pp_max';
  }
  elseif(!empty($_POST['smena4'])){
    $id_atac = obr_chis($_POST['smena4']);
    $ac = 'd_id';
    $bc = 'd_pp_min';
    $dc = 'd_pp_max';
  }
  $id_poke_atk = obr_chis($_POST['id_pok']);
  if(empty($id_atac) || empty($id_poke_atk) || $id_atac <= 0) die("<script>location.href='game.php?go=pokemon';</script>"); 
    
  $pokemon_new_atc = first('SELECT id,lvl,basenum,users FROM pok_user WHERE id=%d AND users=%d AND active=1',$id_poke_atk,$_SESSION['id']);
  if($_SESSION['id'] != $pokemon_new_atc['users']) die("<script>location.href='game.php?go=pokemon';</script>");
    
  $new_atc = first('SELECT atac_id,poke_base_id,atc_lvl FROM attac_poke WHERE atac_id=%d AND poke_base_id=%d',$id_atac,$pokemon_new_atc['basenum']);
  
  if(empty($new_atc['atac_id']))die("<script>location.href='game.php?go=pokemon';</script>");
  if($new_atc['poke_base_id'] != $pokemon_new_atc['basenum']) die("<script>location.href='game.php?go=pokemon';</script>");
  if($pokemon_new_atc['lvl'] < $new_atc['atc_lvl']) die("<script>location.href='game.php?go=pokemon';</script>");
      
      $a = first('SELECT a_id,b_id,c_id,d_id FROM attac_my_poke WHERE pok_id=%d',$pokemon_new_atc['id']);
      $ap = first('SELECT atac_pp FROM attac_power WHERE atac_id=%d',$new_atc['atac_id']); 
      $pp = $ap['atac_pp'];
      $new = $new_atc['atac_id'];
      $pok = $pokemon_new_atc['id']; 
      if($pp <= 0){$pp = 15;} 
      if(!$a){
         insert('attac_my_poke',array(
                'pok_id'=>$pok,
                $ac=>$new,
                $bc=>$pp,
                $dc=>$pp));
      }else{
          if($a['a_id'] == $new || $a['b_id'] == $new || $a['c_id'] == $new || $a['d_id'] == $new)  die("<script>alert('Данная атака уже существует у этого покемона.');location.href='game.php?go=pokemon';</script>"); 
          update('attac_my_poke',array($ac=>$new, $bc=>$pp, $dc=>$pp),'pok_id='.(int)$pok);
          if($a[$ac] != 0) die("<script>alert('Атака успешно изменена.');location.href='game.php?go=pokemon';</script>");
      }
  die("<script>alert('Атака успешно изучена.');location.href='game.php?go=pokemon';</script>");
}
?>