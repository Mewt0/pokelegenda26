<?php
function connectUser($zap){
  $a = first('SELECT '.$zap.' FROM users WHERE login="%s" AND password="%s" AND id=%d AND activation=1',$_SESSION['login'],$_SESSION['password'],$_SESSION['id']);
    if(!$a || !isset($a[$zap])) $c = false;
      else $c = $a[$zap];
  return $c;
}
function infoUsers($id,$zap){
  $a = first('SELECT '.$zap.' FROM users WHERE id=%d',$id);
    if(!$a || !isset($a[$zap])) $c = false;
      else $c = $a[$zap];
  return $c;
}
function color_group_users($id,$tip)
{
  $login = infoUsers($id, 'login');
  $group = infoUsers($id,'groups');
  $on    = infoUsers($id,'online');
    if($login !== false)
    {         
        if ($group == 1)  $color_gr = "#B22222"; //
    elseif ($group == 2)  $color_gr = "#ff7518"; //
    elseif ($group == 3)  $color_gr = "#000000"; //o
    elseif ($group == 4)  $color_gr = "#4b0082"; //
    elseif ($group == 5)  $color_gr = "#ffbf00"; //-
    elseif ($group == 6)  $color_gr = "#000000"; //
    elseif ($group == 7)  $color_gr = "#ffc0cb"; //
    elseif ($group == 8) $color_gr = "#66FF33"; //-
    elseif ($group == 10) $color_gr = "#7fc7ff"; //
    else                  $color_gr = "#000000"; //
      
      if($tip == 1){
        $obv = "<span style='color:".$color_gr.";font-weight:bold;'>".$login."</span>";
      }
      elseif($tip == 2){
        if($on == 1) $obv = "<span style='color:".$color_gr.";font-weight:bold;' onClick=win1=window.open('page.php?id=$id','info','width=900,height=580,scrollbars=yes');return true;>".$login."</span>";
          else $obv = "<span style='color:".$color_gr.";font-weight:bold;'  onClick=win1=window.open('page.php?id=$id','info','width=900,height=580,scrollbars=yes');return true;>".$login."</span>";
      }else{
        $obv = "";
      }
    }else{
       $obv = false;
    }
  return $obv;
}
?>