<?php
function char_work_js($value) {
  return str_replace(array('\\', "'", "\r", "\n"), array('\\\\', "\\'", '', '\n'), (string)$value);
}

function char_work_error($message) {
  $message = char_work_js($message);
  die("<script type=\"text/javascript\">if(parent && parent.mess_error){parent.mess_error('".$message."','block');}window.location='about:blank';</script>");
}

function char_work_open($route) {
  $route = char_work_js($route);
  die("<script type=\"text/javascript\">if(parent && parent.loc){parent.loc('".$route."');}else{window.location='game.php?go=".$route."';}</script>");
}

function char_work_success($title) {
  $title = char_work_js($title);
  die("<script type=\"text/javascript\">if(parent && parent.mess_chat){parent.mess_chat('<b><font color=#000>->".$title."</font></b><br>');}if(parent && parent._location){parent._location.location='game.php?go=char';}else{window.location='game.php?go=char';}</script>");
}

if(!empty($_GET['loc']) || !empty($_GET['do'])){
  if(!empty($_GET['loc'])){
    if(users_conect_dop('arest') > time() && $myrow['buildmy'] != 2)
      update('users',array('buildmy'=>2, 'groups'=>7, 'police'=>0, 'moderation'=>0),'id='.(int)$_SESSION['id']);

    if($myrow['pvp'] == 1) char_work_open('fight_pvp');
    if($myrow['pve'] == 1) char_work_open('fight_pve');
    if($myrow['trade'] > 0) char_work_open('char&trade=true&tradeid='.$myrow['trade']);

    require_once ('include/data.world.php');
    require_once ('include/loc.world.php');

    $getLoc = obr_chis((int)$_GET['loc']);
    $errorMessage = 'В данную локацию проход запрещен.';

    if(empty($dataLoc[$getLoc])) char_work_error($errorMessage);
    if(empty($dataLoc[$myrow['buildmy']]) || !is_array($dataLoc[$myrow['buildmy']]) || !in_array($getLoc,$dataLoc[$myrow['buildmy']])) {
      char_work_error($errorMessage);
    }

    $baseLoc = first('SELECT id,title FROM build WHERE id=%d',$getLoc);
    if(empty($baseLoc['id'])) {
      char_work_error($errorMessage);
    }

    $filesLoc = "include/rooms/".$baseLoc['id'].".php";
    if(!file_exists($filesLoc)) char_work_error($errorMessage);

    if(!empty($go_loc_data[$baseLoc['id']]) && is_array($go_loc_data[$baseLoc['id']])){
      if($go_loc_data[$baseLoc['id']]['build'] == $myrow['buildmy']){
        require_once ('include/proverka.loc.php');
        $errorMessLoc = loces_go($go_loc_data[$baseLoc['id']]['1'], $go_loc_data[$baseLoc['id']]['2'], $go_loc_data[$baseLoc['id']]['3']);
        if($errorMessLoc['res'] == false){
          char_work_error('['.$errorMessLoc['mes'].']');
        }
      }
    }

    update('users',array('buildmy'=>$baseLoc['id']),'id='.(int)$_SESSION['id']);
    char_work_success($baseLoc['title']);
  }
  elseif(!empty($_GET['do'])){
    if($_GET['do'] == 1 && $myrow['buildmy'] == 2 && $myrow['groups'] == 7){
      if(users_conect_dop('arest') < time())
        update('users',array('buildmy'=>1, 'groups'=>6, 'police'=>0, 'moderation'=>0),'id='.(int)$_SESSION['id']);
      else
        char_work_error('Время вашего ареста еще не истекло.');

      char_work_success('Алабастия');
    }
  }
}

die("<script type=\"text/javascript\">window.location='about:blank';</script>");
?>
