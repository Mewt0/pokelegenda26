<?php
$pass = 'pok345';
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
       </form></table></p>';
}else{          
if(!empty($_POST['lvl']) AND !empty($_POST['names']) AND !empty($_POST['baseid'])){
    $name     = obr_txt($_POST['names']);
    $lvl      = obr_chis($_POST['lvl']);
    $baseid   = obr_chis($_POST['baseid']);
    if(empty($name) || empty($lvl) || empty($baseid)){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Чегото не хватает!</span>";
        die("<script>location.href=location.href;</script>");
    }                           
    if($lvl > 100){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Уровень привышает допустимое значение!</span>";
        die("<script>location.href=location.href;</script>");
    }
    $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
    $at = $num_rows['atac_id'];
    if(!$num_rows){
        $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
        $at = $num_rows2['atac_id'];
        if(!$num_rows2){
            $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Атака: ".$name." не найдена в базе!</span>";
             die("<script>location.href=location.href;</script>");
        }
    }
    $a = first('SELECT * FROM attac_poke WHERE atac_id=%d AND poke_base_id=%d AND atc_lvl=%d',$at, $baseid, $lvl);
    if($a){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Покемон под номером: #".$baseid." уже знает эту атаку на этом уровне!</span>";
        die("<script>location.href=location.href;</script>");
    }
    $i = insert('attac_poke',array(
                'atac_id'=>$at,
                'poke_base_id'=>$baseid,
                'atc_lvl'=>$lvl));
    $_SESSION['base_is'] = $baseid;
    if($i) $_SESSION['atc'] = "<span style='color:green; font-weight:bold;'>Атака: $name, успешно добавлена к покемону: #".$baseid."</span>";
      else $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Возникла ошибка.</span>";
}
if(!empty($_POST['namesEG']) AND !empty($_POST['baseidEG'])){
    $name     = obr_txt($_POST['namesEG']);
    $baseid   = obr_chis($_POST['baseidEG']);
    if(empty($name) || empty($baseid)){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Чегото не хватает!</span>";
        die("<script>location.href=location.href;</script>");
    }                           
    $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
    $at = $num_rows['atac_id'];
    if(!$num_rows){
        $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
        $at = $num_rows2['atac_id'];
        if(!$num_rows2){
            $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Атака: ".$name." не найдена в базе!</span>";
             die("<script>location.href=location.href;</script>");
        }
    }
    $a = first('SELECT * FROM attac_egg WHERE atac_id=%d AND poke_base_id=%d',$at, $baseid);
    if($a){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Покемон под номером: #".$baseid." уже знает эту яйцевую атаку!</span>";
        die("<script>location.href=location.href;</script>");
    }
    $i = insert('attac_egg',array(
                'atac_id'=>$at,
                'poke_base_id'=>$baseid));
    $_SESSION['base_is'] = $baseid;
    if($i) $_SESSION['atc'] = "<span style='color:green; font-weight:bold;'>Яйцевая Атака: $name, успешно добавлена к покемону: #".$baseid."</span>";
      else $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Возникла ошибка.</span>";
}
if(!empty($_POST['lvldel']) AND !empty($_POST['namesdel']) AND !empty($_POST['baseiddel'])){
        $name     = obr_txt($_POST['namesdel']);
        $lvl      = obr_chis($_POST['lvldel']);
        $baseid   = obr_chis($_POST['baseiddel']);
        if(empty($name) || empty($lvl) || empty($baseid)){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Чегото не хватает!</span>";
            die("<script>location.href=location.href;</script>");
        } 
        if($lvl > 100){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Уровень привышает допустимое значение!</span>";
            die("<script>location.href=location.href;</script>");
        }
        $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
        $at = $num_rows['atac_id'];
        if(!$num_rows){
            $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
            $at = $num_rows2['atac_id'];
            if(!$num_rows2){
                $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Атака: ".$name." не найдена в базе!</span>";
                 die("<script>location.href=location.href;</script>");
            }
        }
        $a = first('SELECT * FROM attac_poke WHERE atac_id=%d AND poke_base_id=%d AND atc_lvl=%d',$at, $baseid, $lvl);
        if(!$a){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Покемон под номером: #".$baseid." не знает эту атаку на этом уровне!</span>";
            die("<script>location.href=location.href;</script>");
        }
        $i = delete('attac_poke','atac_id='.(int)$at.' AND  poke_base_id='.(int)$baseid.' AND atc_lvl='.(int)$lvl);
        if($i) $_SESSION['atc2'] = "<span style='color:green; font-weight:bold;'>Атака: $name, успешно удалена у покемона: #".$baseid."</span>";
          else $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Возникла ошибка.</span>";
}
if(!empty($_POST['namesdelEG']) AND !empty($_POST['baseiddelEG'])){
        $name     = obr_txt($_POST['namesdelEG']);
        $baseid   = obr_chis($_POST['baseiddelEG']);
        if(empty($name) || empty($baseid)){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Чегото не хватает!</span>";
            die("<script>location.href=location.href;</script>");
        } 
        $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
        $at = $num_rows['atac_id'];
        if(!$num_rows){
            $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
            $at = $num_rows2['atac_id'];
            if(!$num_rows2){
                $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Атака: ".$name." не найдена в базе!</span>";
                 die("<script>location.href=location.href;</script>");
            }
        }
        $a = first('SELECT * FROM attac_egg WHERE atac_id=%d AND poke_base_id=%d',$at, $baseid);
        if(!$a){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Покемон под номером: #".$baseid." не знает эту яйцевую атаку!</span>";
            die("<script>location.href=location.href;</script>");
        }
        $i = delete('attac_egg','atac_id='.(int)$at.' AND  poke_base_id='.(int)$baseid);
        if($i) $_SESSION['atc2'] = "<span style='color:green; font-weight:bold;'>Яйцевая Атака: $name, успешно удалена у покемона: #".$baseid."</span>";
          else $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Возникла ошибка.</span>";
}
?>
<TITLE>Добавление атак</TITLE>
<style type="text/css">
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
if(empty($_GET['eggs'])){
?>
<center><h1>Добавить атаку покемону.</h1></center>
<center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc'])) print $_SESSION['atc']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Уровень:</div>
                <input type='text' name='lvl' size='20' value='' >
              </td>
              <td>
                <div id='txt2'>Название атаки:</div>
                <input type='text' name='names' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Номер покемона:</div>
                <input type='text' name='baseid' size='20' value='<?php print (!empty($_SESSION['base_is'])?$_SESSION['base_is']:''); ?>'>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=3><br>
                <input type='submit' name='Submit' value='Добавить'>
                <input type='reset' value='Сброс'>
              </td>
            </tr>
          </form>
        </table>
    </div>
<br><br>
<center><h1>Удалить атаку покемону.</h1></center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc2'])) print $_SESSION['atc2']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Уровень:</div>
                <input type='text' name='lvldel' size='20' value=''>
              </td>
              <td>
                <div id='txt2'>Название атаки:</div>
                <input type='text' name='namesdel' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Номер покемона:</div>
                <input type='text' name='baseiddel' size='20' value=''>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=3><br>
                <input type='submit' name='Submit' value='Удалить'>
                <input type='reset' value='Сброс'>
              </td>
            </tr>
          </form>
        </table>
    </div>
</center>
<?php
}else{
?>
<center><h1>Добавить яйцевую атаку покемону.</h1></center>
<center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc'])) print $_SESSION['atc']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Название атаки:</div>
                <input type='text' name='namesEG' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Номер покемона:</div>
                <input type='text' name='baseidEG' size='20' value='<?php print (!empty($_SESSION['base_is'])?$_SESSION['base_is']:''); ?>'>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=2><br>
                <input type='submit' name='Submit' value='Добавить'>
                <input type='reset' value='Сброс'>
              </td>
            </tr>
          </form>
        </table>
    </div>
<br><br>
<center><h1>Удалить яйцевую атаку покемону.</h1></center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc2'])) print $_SESSION['atc2']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Название атаки:</div>
                <input type='text' name='namesdelEG' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Номер покемона:</div>
                <input type='text' name='baseiddelEG' size='20' value=''>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=2><br>
                <input type='submit' name='Submit' value='Удалить'>
                <input type='reset' value='Сброс'>
              </td>
            </tr>
          </form>
        </table>
    </div>
</center>
<?php
} 
unset($_SESSION['atc']);  
unset($_SESSION['atc2']);  
}
?>