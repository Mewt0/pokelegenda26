<?php
function char_work_is_ajax() {
  return (defined('AJAX') && AJAX) || (!empty($_GET['ajax']) && $_GET['ajax'] == '1');
}

function char_work_script_string($value) {
  return str_replace(array('\\', "'", "\r", "\n"), array('\\\\', "\\'", '', '\n'), (string)$value);
}

function char_work_finish($ok, $message, $redirect, $chat) {
  if (char_work_is_ajax()) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
      'ok' => (bool)$ok,
      'message' => (string)$message,
      'redirect' => (string)$redirect,
      'chat' => (string)$chat,
    ));
    exit;
  }

  $messageJs = char_work_script_string($message);
  $redirectJs = char_work_script_string($redirect);
  $chatJs = char_work_script_string($chat);

  echo "<script type=\"text/javascript\">
(function(){
  var p = window.parent || window;
  if ('".$messageJs."' && p.mess_error) p.mess_error('".$messageJs."', 'block');
  if ('".$chatJs."' && p.mess_chat) p.mess_chat('".$chatJs."');
  if (p._location) p._location.location = '".$redirectJs."';
  else window.location.href = 'game.php?go=map';
})();
</script>";
  exit;
}

function char_work_error($message) {
  char_work_finish(false, $message, 'game.php?go=char', '');
}

function char_work_success($title) {
  char_work_finish(true, '', 'game.php?go=char', '<b><font color=#000>->'.$title.'</font></b><br>');
}

if(!empty($_GET['loc']) || !empty($_GET['do'])){
  if(!empty($_GET['loc'])){
    if(users_conect_dop('arest') > time() && $myrow['buildmy'] != 2)
      update('users',array('buildmy'=>2, 'groups'=>7, 'police'=>0, 'moderation'=>0),'id='.(int)$_SESSION['id']);

    if($myrow['pvp'] == 1) char_work_finish(true, '', 'game.php?go=fight_pvp', '');
    if($myrow['pve'] == 1) char_work_finish(true, '', 'game.php?go=fight_pve', '');
    if($myrow['trade'] > 0) char_work_finish(true, '', 'game.php?go=char&trade=true&tradeid='.$myrow['trade'], '');

    require_once ('include/data.world.php');
    require_once ('include/loc.world.php');

    $getLoc = (!empty($_GET['loc'])?(int)$_GET['loc']:$myrow['buildmy']);
    $getLoc = obr_chis($getLoc);
    $errorMessage = 'В данной локации произошла загрузка ошибки.';

    if(empty($dataLoc[$getLoc])) char_work_error($errorMessage);

    $filesLoc = "include/rooms/".$getLoc.".php";
    if(!file_exists($filesLoc)) char_work_error($errorMessage);

    $baseLoc = first('SELECT id,title FROM build WHERE id=%d',$getLoc);
    if(empty($baseLoc['id'])) {
      $baseLoc = array('id' => $getLoc, 'title' => 'Локация #'.$getLoc);
    }

    if(empty($dataLoc[$myrow['buildmy']]) || !is_array($dataLoc[$myrow['buildmy']]) || !in_array($getLoc,$dataLoc[$myrow['buildmy']])) {
      char_work_error($errorMessage);
    }

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
        char_work_error('Время ареста ещё не истекло.');

      char_work_finish(true, '', 'game.php?go=char', '<b><font color=#000>->Освобождаемся</font></b><br>');
    }
  }

  char_work_error('В данной локации произошла загрузка ошибки.');
}
?>
