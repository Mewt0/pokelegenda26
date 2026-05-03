<?php
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Дорога 1";
    $href = '/game.php?go=char&quest_npc='.$pers.'&do=';
if($qx01){
  if(!empty($_POST['pokes']) && !empty($_POST['pokeName']) && $qx01 == true){
     $poke = substr($_POST['pokes'],  1, 3);
     if($poke == '001' || $poke == '004' || $poke == '007' 
     || $poke == '152' || $poke == '155' || $poke == '158'
     || $poke == '252' || $poke == '255' || $poke == '258'
     || $poke == '387' || $poke == '390' || $poke == '393'
     || $poke == '495' || $poke == '498' || $poke == '501'
	 || $poke == '650' || $poke == '653' || $poke == '656') $pokeMy = $poke; else $pokeMy = 001;
     if($pokeMy > 0){
      update('quest',array('pers'=>$pokeMy, 'process'=>4),'quest_id=1 AND user_id='.(int)$_SESSION['id']);
      //print '<script>alert("Вы выбрали: '.$_POST['pokes'].'");</script>';   
      die("<script>parent._location.location.href='game.php?go=char'; setTimeout(\"parent.mess_error('*В траве Вы заметили спрятавшегося ".$_POST['pokes'].". Подкрадываясь к покемону, Вы медленно достаете пустой покебол. Еще несколько секунд и покебол уже летит в сторону покемона. Шарик стукнулся о тело покемона и «поглотил» его вовнутрь. Некоторое время покебол качался из стороны в сторону, но после послышался щелчок и шарик уже спокойно лежал на травке. Подобрав покебол, Вы тут же побежали обратно к профессору.*','block');\",1000);</script>"); 
     }else{  
      print '<script>alert("Ошибка, попробуйте снова.");</script>'; 
     }
  }
  $quest_isset_const = 1;
  $about = '<script>
                function valpoke(val){ window.document.getElementById(\'vib\').innerHTML="<b>Вы выбрали: "+val+"</b>"; window.document.getElementById(\'pokeName\').value=val;}
                function confirmPok(){ if(window.document.getElementById(\'pokeName\').value == false) alert("Выберите покемона!"); else { if (confirm("Вы подтверждаете что выбираете: "+window.document.getElementById(\'pokeName\').value+"?")) { window.document.getElementById(\'formPoke\').submit();  return true; } else { return false; } }}
            </script>
            <table border="1" align="center">
              <form action="" target="_location_two" method="POST" name="formPoke" id="formPoke">
              <tr>
                <td colspan="15" align="center"> 
                 <span id="vib"><b>Выберите покемона:</b></span>
                 <input name="pokeName" id="pokeName" type="hidden" value=""/>
                </td>
              </tr>
              <tr>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#001 Bulbasaur" style="cursor:hand" id="A1" onclick="valpoke(this.value)" checked> 
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#004 Charmander" style="cursor:hand"  onclick="valpoke(this.value)" id="A2"> 
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#007 Squirtle" style="cursor:hand"  onclick="valpoke(this.value)" id="A3"> 
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#152 Chikorita" style="cursor:hand"  onclick="valpoke(this.value)"  id="A4">
                </td>
                <td width="60" align="center"> 
                  <input type="radio" name="pokes" value="#155 Cyndaquil" style="cursor:hand"  onclick="valpoke(this.value)"  id="A5">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#158 Totodile" style="cursor:hand"  onclick="valpoke(this.value)" id="A6">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#252 Treecko" style="cursor:hand"  onclick="valpoke(this.value)" id="A7">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#255 Torchic" style="cursor:hand"  onclick="valpoke(this.value)" id="A8">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#258 Mudkip" style="cursor:hand"  onclick="valpoke(this.value)" id="A9">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#387 Turtwig" style="cursor:hand"  onclick="valpoke(this.value)" id="A10">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#390 Chimchar" style="cursor:hand"  onclick="valpoke(this.value)" id="A11">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#393 Piplup" style="cursor:hand" onclick="valpoke(this.value)"  id="A12">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#495 Snivy" style="cursor:hand"  onclick="valpoke(this.value)" id="A13">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#498 Tepig" style="cursor:hand"  onclick="valpoke(this.value)" id="A14">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#501 Oshawott" style="cursor:hand"  onclick="valpoke(this.value)" id="A15">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#650 Chespin" style="cursor:hand"  onclick="valpoke(this.value)" id="A16">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#653 Fennekin" style="cursor:hand"  onclick="valpoke(this.value)" id="A17">
                </td>
                <td width="60" align="center">
                  <input type="radio" name="pokes" value="#656 Froakie" style="cursor:hand"  onclick="valpoke(this.value)" id="A18">
                </td>
              <tr>
              <tr>
                <td width="60" align="center">
                  <LABEL FOR="A1"> 
                    <img src="pok/spriteanim/001.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A2"> 
                    <img src="pok/spriteanim/004.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A3"> 
                    <img src="pok/spriteanim/007.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A4"> 
                    <img src="pok/spriteanim/152.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center"> 
                  <LABEL FOR="A5"> 
                    <img src="pok/spriteanim/155.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A6"> 
                    <img src="pok/spriteanim/158.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A7"> 
                    <img src="pok/spriteanim/252.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A8"> 
                    <img src="pok/spriteanim/255.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A9"> 
                    <img src="pok/spriteanim/258.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A10"> 
                    <img src="pok/spriteanim/387.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A11"> 
                    <img src="pok/spriteanim/390.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A12"> 
                    <img src="pok/spriteanim/393.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A13"> 
                    <img src="pok/spriteanim/495.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A14"> 
                    <img src="pok/spriteanim/498.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A15"> 
                    <img src="pok/spriteanim/501.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A16"> 
                    <img src="pok/spriteanim/650.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A17"> 
                    <img src="pok/spriteanim/653.gif"> 
                  </LABEL>
                </td>
                <td width="60" align="center">
                  <LABEL FOR="A18"> 
                    <img src="pok/spriteanim/656.gif"> 
                  </LABEL>
                </td>
              </tr>                                                                                    
              <tr>
                <td colspan="15" align="center">
                   <input style="border:2px solid #000;margin:3px;font-weight:bold;" onclick="confirmPok();" type="button" name="button" value="Подтвердить">
                </td>
              </tr>
              </form>
            </table>';
  $pers  = '<a href="/game.php?go=char">Уйти</a>';
}

?>