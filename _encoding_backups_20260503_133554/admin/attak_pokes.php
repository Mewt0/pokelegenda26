<?php
$pass = 'pok345';
if (empty($_SESSION['sessionAttac'])){
    if (!empty($_POST['password']) && $_POST['password'] == $pass){
        $_SESSION['sessionAttac'] = true;
        die("<script>location.href=location.href;</script>");
    }
  print ' 
    <p style="margin-top: 100px; font-weight:bold; color:#000;" align=center valign=middle>Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ:
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
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р В§Р ВµР С–Р С•РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ!</span>";
        die("<script>location.href=location.href;</script>");
    }                           
    if($lvl > 100){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ Р С—РЎР‚Р С‘Р Р†РЎвЂ№РЎв‚¬Р В°Р ВµРЎвЂљ Р Т‘Р С•Р С—РЎС“РЎРѓРЎвЂљР С‘Р СР С•Р Вµ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘Р Вµ!</span>";
        die("<script>location.href=location.href;</script>");
    }
    $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
    $at = $num_rows['atac_id'];
    if(!$num_rows){
        $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
        $at = $num_rows2['atac_id'];
        if(!$num_rows2){
            $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р С’РЎвЂљР В°Р С”Р В°: ".$name." Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…Р В° Р Р† Р В±Р В°Р В·Р Вµ!</span>";
             die("<script>location.href=location.href;</script>");
        }
    }
    $a = first('SELECT * FROM attac_poke WHERE atac_id=%d AND poke_base_id=%d AND atc_lvl=%d',$at, $baseid, $lvl);
    if($a){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р СџР С•Р С”Р ВµР СР С•Р Р… Р С—Р С•Р Т‘ Р Р…Р С•Р СР ВµРЎР‚Р С•Р С: #".$baseid." РЎС“Р В¶Р Вµ Р В·Р Р…Р В°Р ВµРЎвЂљ РЎРЊРЎвЂљРЎС“ Р В°РЎвЂљР В°Р С”РЎС“ Р Р…Р В° РЎРЊРЎвЂљР С•Р С РЎС“РЎР‚Р С•Р Р†Р Р…Р Вµ!</span>";
        die("<script>location.href=location.href;</script>");
    }
    $i = insert('attac_poke',array(
                'atac_id'=>$at,
                'poke_base_id'=>$baseid,
                'atc_lvl'=>$lvl));
    $_SESSION['base_is'] = $baseid;
    if($i) $_SESSION['atc'] = "<span style='color:green; font-weight:bold;'>Р С’РЎвЂљР В°Р С”Р В°: $name, РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В° Р С” Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎС“: #".$baseid."</span>";
      else $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р вЂ™Р С•Р В·Р Р…Р С‘Р С”Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°.</span>";
}
if(!empty($_POST['namesEG']) AND !empty($_POST['baseidEG'])){
    $name     = obr_txt($_POST['namesEG']);
    $baseid   = obr_chis($_POST['baseidEG']);
    if(empty($name) || empty($baseid)){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р В§Р ВµР С–Р С•РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ!</span>";
        die("<script>location.href=location.href;</script>");
    }                           
    $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
    $at = $num_rows['atac_id'];
    if(!$num_rows){
        $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
        $at = $num_rows2['atac_id'];
        if(!$num_rows2){
            $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р С’РЎвЂљР В°Р С”Р В°: ".$name." Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…Р В° Р Р† Р В±Р В°Р В·Р Вµ!</span>";
             die("<script>location.href=location.href;</script>");
        }
    }
    $a = first('SELECT * FROM attac_egg WHERE atac_id=%d AND poke_base_id=%d',$at, $baseid);
    if($a){
        $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р СџР С•Р С”Р ВµР СР С•Р Р… Р С—Р С•Р Т‘ Р Р…Р С•Р СР ВµРЎР‚Р С•Р С: #".$baseid." РЎС“Р В¶Р Вµ Р В·Р Р…Р В°Р ВµРЎвЂљ РЎРЊРЎвЂљРЎС“ РЎРЏР в„–РЎвЂ Р ВµР Р†РЎС“РЎР‹ Р В°РЎвЂљР В°Р С”РЎС“!</span>";
        die("<script>location.href=location.href;</script>");
    }
    $i = insert('attac_egg',array(
                'atac_id'=>$at,
                'poke_base_id'=>$baseid));
    $_SESSION['base_is'] = $baseid;
    if($i) $_SESSION['atc'] = "<span style='color:green; font-weight:bold;'>Р Р‡Р в„–РЎвЂ Р ВµР Р†Р В°РЎРЏ Р С’РЎвЂљР В°Р С”Р В°: $name, РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В° Р С” Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎС“: #".$baseid."</span>";
      else $_SESSION['atc'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р вЂ™Р С•Р В·Р Р…Р С‘Р С”Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°.</span>";
}
if(!empty($_POST['lvldel']) AND !empty($_POST['namesdel']) AND !empty($_POST['baseiddel'])){
        $name     = obr_txt($_POST['namesdel']);
        $lvl      = obr_chis($_POST['lvldel']);
        $baseid   = obr_chis($_POST['baseiddel']);
        if(empty($name) || empty($lvl) || empty($baseid)){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р В§Р ВµР С–Р С•РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ!</span>";
            die("<script>location.href=location.href;</script>");
        } 
        if($lvl > 100){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ Р С—РЎР‚Р С‘Р Р†РЎвЂ№РЎв‚¬Р В°Р ВµРЎвЂљ Р Т‘Р С•Р С—РЎС“РЎРѓРЎвЂљР С‘Р СР С•Р Вµ Р В·Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘Р Вµ!</span>";
            die("<script>location.href=location.href;</script>");
        }
        $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
        $at = $num_rows['atac_id'];
        if(!$num_rows){
            $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
            $at = $num_rows2['atac_id'];
            if(!$num_rows2){
                $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р С’РЎвЂљР В°Р С”Р В°: ".$name." Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…Р В° Р Р† Р В±Р В°Р В·Р Вµ!</span>";
                 die("<script>location.href=location.href;</script>");
            }
        }
        $a = first('SELECT * FROM attac_poke WHERE atac_id=%d AND poke_base_id=%d AND atc_lvl=%d',$at, $baseid, $lvl);
        if(!$a){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р СџР С•Р С”Р ВµР СР С•Р Р… Р С—Р С•Р Т‘ Р Р…Р С•Р СР ВµРЎР‚Р С•Р С: #".$baseid." Р Р…Р Вµ Р В·Р Р…Р В°Р ВµРЎвЂљ РЎРЊРЎвЂљРЎС“ Р В°РЎвЂљР В°Р С”РЎС“ Р Р…Р В° РЎРЊРЎвЂљР С•Р С РЎС“РЎР‚Р С•Р Р†Р Р…Р Вµ!</span>";
            die("<script>location.href=location.href;</script>");
        }
        $i = delete('attac_poke','atac_id='.(int)$at.' AND  poke_base_id='.(int)$baseid.' AND atc_lvl='.(int)$lvl);
        if($i) $_SESSION['atc2'] = "<span style='color:green; font-weight:bold;'>Р С’РЎвЂљР В°Р С”Р В°: $name, РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• РЎС“Р Т‘Р В°Р В»Р ВµР Р…Р В° РЎС“ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°: #".$baseid."</span>";
          else $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р вЂ™Р С•Р В·Р Р…Р С‘Р С”Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°.</span>";
}
if(!empty($_POST['namesdelEG']) AND !empty($_POST['baseiddelEG'])){
        $name     = obr_txt($_POST['namesdelEG']);
        $baseid   = obr_chis($_POST['baseiddelEG']);
        if(empty($name) || empty($baseid)){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р В§Р ВµР С–Р С•РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ!</span>";
            die("<script>location.href=location.href;</script>");
        } 
        $num_rows = first('SELECT atac_id FROM attac_power where MATCH (atac_name) AGAINST ("%s")',$name);
        $at = $num_rows['atac_id'];
        if(!$num_rows){
            $num_rows2 = first('SELECT atac_id FROM attac_power where atac_name="%s"',$name);
            $at = $num_rows2['atac_id'];
            if(!$num_rows2){
                $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р С’РЎвЂљР В°Р С”Р В°: ".$name." Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…Р В° Р Р† Р В±Р В°Р В·Р Вµ!</span>";
                 die("<script>location.href=location.href;</script>");
            }
        }
        $a = first('SELECT * FROM attac_egg WHERE atac_id=%d AND poke_base_id=%d',$at, $baseid);
        if(!$a){
            $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р СџР С•Р С”Р ВµР СР С•Р Р… Р С—Р С•Р Т‘ Р Р…Р С•Р СР ВµРЎР‚Р С•Р С: #".$baseid." Р Р…Р Вµ Р В·Р Р…Р В°Р ВµРЎвЂљ РЎРЊРЎвЂљРЎС“ РЎРЏР в„–РЎвЂ Р ВµР Р†РЎС“РЎР‹ Р В°РЎвЂљР В°Р С”РЎС“!</span>";
            die("<script>location.href=location.href;</script>");
        }
        $i = delete('attac_egg','atac_id='.(int)$at.' AND  poke_base_id='.(int)$baseid);
        if($i) $_SESSION['atc2'] = "<span style='color:green; font-weight:bold;'>Р Р‡Р в„–РЎвЂ Р ВµР Р†Р В°РЎРЏ Р С’РЎвЂљР В°Р С”Р В°: $name, РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• РЎС“Р Т‘Р В°Р В»Р ВµР Р…Р В° РЎС“ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°: #".$baseid."</span>";
          else $_SESSION['atc2'] = "<span style='color:#000; font-weight:bold;border:2px solid #000; background: #ffcece;'>Р вЂ™Р С•Р В·Р Р…Р С‘Р С”Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°.</span>";
}
?>
<TITLE>Р вЂќР С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”</TITLE>
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
<center><h1>Р вЂќР С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ Р В°РЎвЂљР В°Р С”РЎС“ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎС“.</h1></center>
<center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc'])) print $_SESSION['atc']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ:</div>
                <input type='text' name='lvl' size='20' value='' >
              </td>
              <td>
                <div id='txt2'>Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:</div>
                <input type='text' name='names' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Р СњР С•Р СР ВµРЎР‚ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</div>
                <input type='text' name='baseid' size='20' value='<?php print (!empty($_SESSION['base_is'])?$_SESSION['base_is']:''); ?>'>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=3><br>
                <input type='submit' name='Submit' value='Р вЂќР С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ'>
                <input type='reset' value='Р РЋР В±РЎР‚Р С•РЎРѓ'>
              </td>
            </tr>
          </form>
        </table>
    </div>
<br><br>
<center><h1>Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ Р В°РЎвЂљР В°Р С”РЎС“ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎС“.</h1></center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc2'])) print $_SESSION['atc2']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ:</div>
                <input type='text' name='lvldel' size='20' value=''>
              </td>
              <td>
                <div id='txt2'>Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:</div>
                <input type='text' name='namesdel' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Р СњР С•Р СР ВµРЎР‚ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</div>
                <input type='text' name='baseiddel' size='20' value=''>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=3><br>
                <input type='submit' name='Submit' value='Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ'>
                <input type='reset' value='Р РЋР В±РЎР‚Р С•РЎРѓ'>
              </td>
            </tr>
          </form>
        </table>
    </div>
</center>
<?php
}else{
?>
<center><h1>Р вЂќР С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ РЎРЏР в„–РЎвЂ Р ВµР Р†РЎС“РЎР‹ Р В°РЎвЂљР В°Р С”РЎС“ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎС“.</h1></center>
<center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc'])) print $_SESSION['atc']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:</div>
                <input type='text' name='namesEG' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Р СњР С•Р СР ВµРЎР‚ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</div>
                <input type='text' name='baseidEG' size='20' value='<?php print (!empty($_SESSION['base_is'])?$_SESSION['base_is']:''); ?>'>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=2><br>
                <input type='submit' name='Submit' value='Р вЂќР С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ'>
                <input type='reset' value='Р РЋР В±РЎР‚Р С•РЎРѓ'>
              </td>
            </tr>
          </form>
        </table>
    </div>
<br><br>
<center><h1>Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ РЎРЏР в„–РЎвЂ Р ВµР Р†РЎС“РЎР‹ Р В°РЎвЂљР В°Р С”РЎС“ Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎС“.</h1></center>
    <div style='background:#4F4F4F;width:70%' align=center>
        <?php if(!empty($_SESSION['atc2'])) print $_SESSION['atc2']; ?>
        <table align='center'>
          <form method='post' action=''>
            <tr>
              <td>
                <div id='txt2'>Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:</div>
                <input type='text' name='namesdelEG' size='20' ></font><br></td>
              <td>
                <div id='txt2'>Р СњР С•Р СР ВµРЎР‚ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</div>
                <input type='text' name='baseiddelEG' size='20' value=''>
              </td>
            </tr>
            <tr>
              <td align='center' colspan=2><br>
                <input type='submit' name='Submit' value='Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ'>
                <input type='reset' value='Р РЋР В±РЎР‚Р С•РЎРѓ'>
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