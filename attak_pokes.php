<?php
session_start();
if($_SESSION['id']!=1)die('Упс! Страница доступна только Администратору.');
$pass = '/*-/*-/*-';
if (empty($_SESSION['sessionAttac'])){
    if (!empty($_POST['password']) && $_POST['password'] == $pass){
        $_SESSION['sessionAttac'] = true;
        die("<script>location.href=location.href;</script>");
    }
  print ' 
    <p style="margin-top: 100px; font-weight:bold; color:#000;" align=center valign=middle>Введите пароль:
       <table align=center>
       <form action="" method="POST">
       <input type="password" name="password" value="" style="color:#000;font-size:15px;font-weight:bold;border: 2px solid #000;">
       <input type="submit" value="OK" style="color:#000;font-weight:bold;border: 2px solid #000;font-size:15px;">
       </form></table>p>';
}else{
require_once ("include/function/config.php");
require_once ("include/function/db3.php");
$db = db($config);
require_once('include/function/globfanction.php');

function logeAttak($text){
  $t =  date("Y-m-d, H:i:s");
  $GLOBALS['logs'] = fopen('log/attak_loges.txt', 'a');
  fwrite($GLOBALS['logs'], $t." | ".$text." \r\n");
  fclose($GLOBALS['logs']);
  $r = true;                                               
}          
if(!empty($_POST['attaktittle']) AND !empty($_POST['names']) AND !empty($_POST['effect'])){
    $name     = obr_txt($_POST['names']);
    $tit      = obr_txt($_POST['attaktittle']);
    $eff      = obr_txt($_POST['effect']);
    if(empty($tit) || empty($name) || empty($eff)){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Что-то не хватает!</span>";
        die("<script>location.href=location.href;</script>");
    }                           
    $num_rows = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
    $at = $num_rows['atac_id'];
    if(!$num_rows){
        $num_rows2 = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
        $at = $num_rows2['atac_id'];
        if(!$num_rows2){
            $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Атака: ".$name." не найдена в базе!</span>";
             die("<script>location.href=location.href;</script>");
        }
    }
    $i = update('attac_power',array('atac_tittle'=>$tit, 'tittle_effect'=>$eff),'atac_id='.(int)$at);
    $txt = 'У атаки: '.$name.' успешно изменено описание на: '.$tit.', а эффект на: '.$eff;
    if($i) logeAttak($txt);
    if($i) $_SESSION['atc'] = "<span style='color:green; font-weight:bold;'>У атаки: $name, успешно изменено описание и эффект. Проверка: <a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".$at."','atk','width=726,height=260,scrollbars=yes');return true;><img src='/img/other/inf.png'/></a></span>";
      else $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Возникла ошибка.</span>";
}
?>
<TITLE>Добавление атак</TITLE>
<style type="text/css">
        body, html {
 margin: 5px;
 position: relative;
 color: #000000; 
 background-color: #696969;
}
        INPUT,TEXTAREA
        {
            background-color: Ivory;
            padding: 2px;
            font:14pt Tahoma;
            BORDER: #000 2px solid;
            color: #000;
        }

        SELECT
        {
            border-style:none;
            font:14pt Tahoma;
            color: #000000;
        }
        #txt2{
          color: green;
          font:12pt Tahoma;
          font-weight:bold;
          text-align: center;
        }

</style>
<?php
if(!empty($_GET['attac'])){
  $num_rows = select('SELECT atac_id,atac_name,atac_tittle,tittle_effect FROM attac_power',$name);
   print "<table cellspacing='2' border='1' cellpadding='4'>
             <tr>
               <td>ID</td>
               <td>Название</td>
              <td width='42%'>Описание</td>
              <td width='42%'>Эффект</td>
             </tr>";
  foreach($num_rows  as $numrows) 
   print "
             <tr>
               <td>".$numrows['atac_id']."</td>
               <td><span style='color:#000; padding: 2px; font-weight:bold; border:2px solid #000; background: #e8ffce;'>".$numrows['atac_name']."</span></td>
               <td><span style='color:#fff0f5; font-weight:bold;'>".$numrows['atac_tittle']."</span></td>
               <td><span style='color:#fff0f5; font-weight:bold;'>".$numrows['tittle_effect']."</span></td>
             </tr>
     ";
  print "</table>";
}else{
?>
<center><h1>Изменение описания атак.</h1></center>
<center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc'])) print $_SESSION['atc']; ?>
        <table align='center'  width="900">
          <form method='post' action=''>
             <tr>
              <td>
                <div id='txt2'>Название атаки:</div>
                <input type='text' name='names' style="width:100%;"></font>
               </td>
             </tr>
             <tr>
              <td align='center' colspan=2>
                <div id='txt2'>Корректное описание:</div>
                <textarea id="sendTexts" name="attaktittle" style="width:100%; height:85px;" maxlength="5000"></textarea>
               </td>
             </tr>
             <tr>
              <td align='center' colspan=2>
                <div id='txt2'>Эффект:</div>
                <textarea id="sendTexts" name="effect" style="width:100%; height:85px;" maxlength="5000"></textarea>
               </td>
             </tr>
             <tr>
              <td align='center' colspan=2><br>
                <input type='submit' name='Submit' value='Добавить'>
                <input type='reset' value='Оброс'>
               </td>
             </tr>
          </form>
        </table>
    </div>
<?php
if(!empty($_SESSION['atc'])) unset($_SESSION['atc']);   
}}
?>