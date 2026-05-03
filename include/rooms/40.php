<?
if(!empty($_GET['exits'])){
   $a = $_GET['exits'];
   if($a == 1){
    $b = first('SELECT id,locid FROM users_locvoz WHERE userid=%d AND tip="arena"',$_SESSION['id']);
    if(!$b){
        update('users',array('buildmy'=>1),'id='.(int)$_SESSION['id']);  
    }else{
        delete('users_locvoz','id='.(int)$b['id']);
        update('users',array('buildmy'=>$b['locid']),'id='.(int)$_SESSION['id']);        
    }
    die("<script>parent._location.location.href='game.php?go=char';</script>");
   }else{
     die("<script>parent._location.location.href='game.php?go=char';</script>");
   }
}

$turs = first('SELECT * FROM kurator_npc WHERE id=1');
        if($turs['stoimost'] <= 0) $summ_turs = "Бесплатно";
          else $summ_turs = formatnum($turs['stoimost'])." Монет";
        if($turs['nametur']) $name_turs = $turs['nametur'];
         else $name_turs = "--";
        if($turs['fond'] <= 0) $summ_fond = "<b>Накоплений нет.</b>";
          else $summ_fond = "<b>Сумма накоплений составляет: <b>".formatnum($turs['fond'])."</b> Монет.</b>";
        if($myrow['groups'] == 1) $admintur = $myrow['id']; 
          else $admintur = 1;

$pers = "<a href='/game.php?go=char&npc=1&do_npc=pc'>Покецентр</a>";
if($_SESSION['id'] == $turs['upr'] OR $_SESSION['id'] == $admintur){
  if(empty($_GET['npc'])) $pers .= " |  <a href='game.php?go=char&upr=1'>Панель управления</a>"; 
  if(!empty($_POST['names']) AND !empty($_POST['cenatur'])){
    $names   = obr_txt($_POST['names']);
    $cenatur = obr_chis($_POST['cenatur']); 
    if($cenatur <= 0 OR !$names) die("<script>location.href='game.php?go=char&upr=1';</script>");
    if(update('kurator_npc',array('nametur'=>$names, 'stoimost'=>$cenatur),'id=1')) 
      $_SESSION['uzmena'] = "<center><font color=gold>Вы удачно изменили информацию.</font></center>";
    die("<script>parent._location.location.href='game.php?go=char&upr=1';</script>");
  }
  
if(!empty($_GET['upr'])){
   $a = $_GET['upr'];
   if($a == 1){
    $quest_isset_const = 1;
    $name = "Панель управления";
    $about = "
    ".(empty($_SESSION['uzmena'])?'':'<b style="color:green">'.$_SESSION['uzmena'].'</b>')."
    <table>
    <form action='/game.php?go=char' method='POST' target=\"_chat_two\">
    <tr>
    <td>Установить название турнира:<br>
    <input type=\"text\" name=\"names\" value='' size=43%></td>
    <td><br>Текущее название: <b>".$name_turs.".</b></td>
    </tr>
    <tr>
    <td>Установить цену на турнир(без точек):<br>
    <input type=\"text\" name=\"cenatur\" value='' size=43%></td>
    <td><br>Текущая стоимость: <b>".$summ_turs.".</b></td>
    </tr>
    <tr>
    <td colspan=2 align=center>
    <input type=\"submit\" value=\"Готово\" width=\"55\" height=\"15\" name=\"submit\" />
    <br><br>
    </td>
    </tr>
    </form>
    </table>";
    $pers = "
      <a href='game.php?go=char&upr=2'>Посмотреть накопления.</a>
      <a href='game.php?go=char'>Перейти на локацию.</a>"; 
  }elseif($a == 2){
      $quest_isset_const = 1;
      $name = "Панель управления";
      $about = $summ_fond;
        $pers = "
        <a href='game.php?go=char&upr=3'>Забрать деньги.</a>
        <a href='game.php?go=char'>Перейти на локацию.</a>";
  }elseif($a == 3){
         $x11 = false;
      if($turs['fond'] > 0){
        if(update('kurator_npc',array('fond'=>0),'id=1')){
          plus_item($turs['fond'],1);        
          $t = "Удачно забарана оплата с арены юзером: ".$_SESSION['login']."(".$_SESSION['id'].") на сумму: ".$turs['fond'];        
          writeLoges($t,'df','arenaZarplata');
          $x11 = true;
        }
      }
      $quest_isset_const = 1;
      $name = "Панель управления";
      if($x11) $about = "Вы удачно забрали все накопленные деньги.";
        else $about = "Увы, но накоплений нет.";
        $pers = "
        <a href='game.php?go=char&upr=1'>Перейти в изначальную панель управления.</a>
        <a href='game.php?go=char'>Перейти на локацию.</a>";
  }else{
       die("<script>location.href='game.php?go=char';</script>");
  }
} 
}

if(!empty($_GET['npc']) && $_GET['npc'] == 1 && empty($_GET['upr'])){
        if ($_GET['npc'] == 1){
            include ("npc/1.php"); 
            $img_r = '<img src="img/room/1_1.png" width="250" height="150">';
        }else{
          die("<script>location.href='game.php?go=char';</script>");
        }
}else{
  if(!empty($_GET['upr']) && ($_SESSION['id'] == $turs['upr'] || $_SESSION['id'] == $admintur)){
   // not...
  }else{
    $name = "Турнирная Арена";
    $about = '
    Помещение с внушающим размером внутри похоже на куполообразное здание, в центре которого располагается огромная арена. 
    Сюда съезжаются многие известные и начинающие тренеры, чтобы проверить свои силы и научить своих покемонов новым потрясающим стратегиям и маневрам.
    ';
    $move = "<a href='game.php?go=char&exits=1' target=\"_chat_two\">Выход</a>";
    $img_r = '';
  } 
}
unset($_SESSION['uzmena']);
?>

