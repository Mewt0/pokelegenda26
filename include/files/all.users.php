<?php
$echoTxt = false;
$urlPoisk = false;
$strUsers = false;
$totalpages = false;
$ok = false;

include('include/function/itemsinpage.allusers.php');

if(empty($_GET['online']) && empty($_GET['tipes']) && empty($_GET['TrenName'])){
  $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE activation=1 AND (groups != 7 AND groups != 10)');
  $itemsinpage = new Itemsinpage($totalpages['Total']);
  $qUsers = select('SELECT id,login,groups,rang FROM users WHERE activation=1 AND (groups != 7 AND groups != 10) ORDER BY groups,id ASC LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
  if(!$qUsers) $echoTxt .= "<center><h2><span style=\"color:#e32636;\">В данной категории тренеров не существует.</span></h2></center>";  else $strUsers = true;
  foreach($qUsers as $echoUser){
      $echoTxt .= '<table  width="680" align="center">';
      $echoTxt .= '<tr class="trAllUsers" onclick="window.open(\'page.php?id='.$echoUser['id'].'\', \'info\', \'width=900,height=580,scrollbars=yes\'); return false;">
                  <td style="padding:1px;" align="left">
                    <b><span style="color:'.colorsUsers($echoUser['groups']).'; font-size:12px;">'.$echoUser['login'].'</span></b>
                   </td>
                  <td width="50%" align="left" style="padding:1px;"> 
                    <span style="color:#000; font-size:12px;">'.rang_group($echoUser['id'],$echoUser['groups'],$echoUser['rang']).'</span>
                   </td>
                  </tr>';
     $echoTxt .= '</table>';
  }
}
if (isset($_GET['tipes']) && preg_match("|^[0-9_-]+$|i", $_GET['tipes']) && empty($_GET['TrenName'])){ 
    $echoTxt = false;
    $urlPoisk = "&tipes=".$_GET['tipes'];
    $tipes = $_GET['tipes'];
    if($tipes == 1){
      $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE activation=1 AND online=1');
      $itemsinpage = new Itemsinpage($totalpages['Total']);
      $qUsers = select('SELECT id,login,groups,rang FROM users WHERE activation=1 AND online=1 ORDER BY groups,login ASC LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      if(!$qUsers) $echoTxt .= "<center><h2><span style=\"color:#e32636;\">В данной категории тренеров не существует.</span></h2></center>";  
        else{$strUsers = true; $ok = true;}
    }
    elseif($tipes == 2){
      $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE groups=10 OR groups=7');
      $itemsinpage = new Itemsinpage($totalpages['Total']);
      $qUsers = select('SELECT id,login,groups,rang FROM users WHERE groups=10 OR groups=7 ORDER BY groups,login ASC LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      if(!$qUsers) $echoTxt .= "<center><h2><span style=\"color:#e32636;\">В данной категории тренеров не существует.</span></h2></center>";  
        else{$strUsers = true; $ok = true;}
    }
    elseif($tipes == 3){
      $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10');
      $itemsinpage = new Itemsinpage($totalpages['Total']);
      $qUsers = select('SELECT id,login,groups,rang FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10 ORDER BY count_poke DESC, id ASC LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      if(!$qUsers) $echoTxt .= "<center><h2><span style=\"color:#e32636;\">В данной категории тренеров не существует.</span></h2></center>";  
        else{$strUsers = true; $ok = true;}
    }
    elseif($tipes == 4){
      $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10');
      $itemsinpage = new Itemsinpage($totalpages['Total']);
      $qUsers = select('SELECT id,login,groups,rang FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10 ORDER BY rang_b DESC, id ASC LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      if(!$qUsers) $echoTxt .= "<center><h2><span style=\"color:#e32636;\">В данной категории тренеров не существует.</span></h2></center>";  
        else{$strUsers = true; $ok = true;}
    }
    elseif($tipes == 5){
      $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10');
      $itemsinpage = new Itemsinpage($totalpages['Total']);
      $qUsers = select('SELECT id,login,groups,rang FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10 ORDER BY count_poke_s DESC, id ASC LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      if(!$qUsers) $echoTxt .= "<center><h2><span style=\"color:#e32636;\">В данной категории тренеров не существует.</span></h2></center>";  
        else{$strUsers = true; $ok = true;}
    }
    elseif($tipes == 6){
      $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10');
      $itemsinpage = new Itemsinpage($totalpages['Total']);
      $qUsers = select('SELECT id,login,groups,rang FROM users WHERE activation=1 AND id!=3 AND groups!=1 AND groups!=7 AND groups!=10 ORDER BY rang_a DESC, id ASC LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
      if(!$qUsers) $echoTxt .= "<center><h2><span style=\"color:#e32636;\">В данной категории тренеров не существует.</span></h2></center>";  
        else{$strUsers = true; $ok = true;}
    }
    else
    {
      die("<script>location.href='game.php?go=users'</script>");
    }
    if($ok == true){
      foreach($qUsers as $echoUser){
          $echoTxt .= '<table  width="680" align="center">';
          $echoTxt .= '<tr class="trAllUsers" onclick="window.open(\'page.php?id='.$echoUser['id'].'\', \'info\', \'width=900,height=580,scrollbars=yes\'); return false;">
                    <td style="padding:1px;" align="left">
                      <b><span style="color:'.colorsUsers($echoUser['groups']).'; font-size:12px;">'.$echoUser['login'].'</span></b>
                     </td>
                    <td width="50%" align="left" style="padding:1px;"> 
                      <span style="color:#000; font-size:12px;">'.rang_group($echoUser['id'],$echoUser['groups'],$echoUser['rang']).'</span>
                     </td>
                    </tr>';
          $echoTxt .= '</table>';
      }
    }
}
if(isset($_GET['TrenName']) && isset($_GET['search']) && preg_match("|^[a-z_-]+$|i", $_GET['search'])){
  $echoTxt = false;
  $urlPoisk = "&TrenName=true&search=".$_GET['search'];
  $search = $_GET['search'];
  $search = str_replace('"','',$search);
  $search = str_replace("'",'',$search);
  $tr_name = mysql_escape_string(translitRus(obr_txt($search)));
  $totalpages = first('SELECT COUNT(*) as Total FROM users WHERE login LIKE "%'.$tr_name.'%"');
  $itemsinpage = new Itemsinpage($totalpages['Total']);
  $getSt = $itemsinpage->get('Start');
  $getLm = $itemsinpage->get('Limit');
  $qUsers = select('SELECT id,login,groups,rang FROM users WHERE login LIKE "%'.$tr_name.'%" ORDER BY groups,id ASC LIMIT '.$getSt.','.$getLm);
  if(!$qUsers){ 
    $echoTxt .= "<center><h2><span style=\"color:#e32636;\">Такого тренера не существует.</span></h2></center>"; 
  }else{
   $strUsers = true;
   foreach($qUsers  as $echoUser){
    $echoTxt .= '<table  width="680" align="center">';
    $echoTxt .= '<tr class="trAllUsers" onclick="window.open(\'page.php?id='.$echoUser['id'].'\', \'info\', \'width=900,height=580,scrollbars=yes\'); return false;">
                  <td style="padding:1px;" align="left">
                    <b><span style="color:'.colorsUsers($echoUser['groups']).'; font-size:12px;">'.$echoUser['login'].'</span></b>
                   </td>
                  <td width="50%" align="left" style="padding:1px;"> 
                    <span style="color:#000; font-size:12px;">'.rang_group($echoUser['id'],$echoUser['groups'],$echoUser['rang']).'</span>
                   </td>
                  </tr>';
    $echoTxt .= '</table>';
   }
  }         
}
?>
<div id="allUsrs" align="center">          
    <center><h2 style="color:#FFF;">Поиск тренера:</h2></center><br>
    <span style="color:#fff;font-weight:bold;">Введите имя: </span><input id='allUsers' name='TrenName' type='text' value=''>
    <input id='allUsersSub' type='submit' name='submit' value='Поиск' onclick="window.location='/game.php?go=users&TrenName=true&search='+document.getElementById('allUsers').value;">
  <br>
  <h1 style="color:#FFF;">Тренеры:</h1>
    <a href="/game.php?go=users">Все</a> | <a href="/game.php?go=users&tipes=1">Только онлайн</a> |  <a href="/game.php?go=users&tipes=3">Топ покедексу</a> | <a href="/game.php?go=users&tipes=5">Топ шинидексу</a> | <a href="/game.php?go=users&tipes=4">Топ рангу(PvP)</a>  | <a href="/game.php?go=users&tipes=6">Топ рангу(PvE)</a>  |  <a href="/game.php?go=users&tipes=2">Заключенных</a> </center> <br>
  <table width="680" align="center">
    <tr>
      <td>
        <span style="font-size:24px; color:#000; font-weight:bold;"><b><i>Имя</i></b></span>
       </td>
      <td width="50%" align="center">
        <span style="font-size:24px; color:#000; font-weight:bold;"><b><i>Ранг</i></b></span>
       </td>
    </tr>
  </table>
  <?php
   print $echoTxt;
    if($totalpages){
    $data = $itemsinpage->SmartyArr();
    if($data && $strUsers == true){
  ?>
  <tr>
  	<td>
  		<td style="font-size:24px; color:#fff; font-weight:bold;">Страница
  		<?php for($i=0,$n=sizeof($data['Count']);$i<$n;$i++): ?>
  			<?php if($data['Count'][$i][1]!=$_GET['page']): ?>
  			<a href="game.php?go=users<?php print $urlPoisk; ?>&page=<?php print $data['Count'][$i][1];?>" style="font-size:24px;font-weight:bold;"><?php print $data['Count'][$i][0];?></a>
  			<?php else: ?>
  			<?php print '<span style="color:#000;">'.$data['Count'][$i][0].'</span>';?>
  			<?php endif; ?>
  		<?php endfor; ?>
  		</td>
  	</tr>
  	
  </table>
  <?php
    }}
  ?>
</div>