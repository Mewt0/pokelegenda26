<?php
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Р вЂќР С•РЎР‚Р С•Р С–Р В° 1";
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
      //print '<script>alert("Р вЂ™РЎвЂ№ Р Р†РЎвЂ№Р В±РЎР‚Р В°Р В»Р С‘: '.$_POST['pokes'].'");</script>';   
      die("<script>parent._location.location.href='game.php?go=char'; setTimeout(\"parent.mess_error('*Р вЂ™ РЎвЂљРЎР‚Р В°Р Р†Р Вµ Р вЂ™РЎвЂ№ Р В·Р В°Р СР ВµРЎвЂљР С‘Р В»Р С‘ РЎРѓР С—РЎР‚РЎРЏРЎвЂљР В°Р Р†РЎв‚¬Р ВµР С–Р С•РЎРѓРЎРЏ ".$_POST['pokes'].". Р СџР С•Р Т‘Р С”РЎР‚Р В°Р Т‘РЎвЂ№Р Р†Р В°РЎРЏРЎРѓРЎРЉ Р С” Р С—Р С•Р С”Р ВµР СР С•Р Р…РЎС“, Р вЂ™РЎвЂ№ Р СР ВµР Т‘Р В»Р ВµР Р…Р Р…Р С• Р Т‘Р С•РЎРѓРЎвЂљР В°Р ВµРЎвЂљР Вµ Р С—РЎС“РЎРѓРЎвЂљР С•Р в„– Р С—Р С•Р С”Р ВµР В±Р С•Р В». Р вЂўРЎвЂ°Р Вµ Р Р…Р ВµРЎРѓР С”Р С•Р В»РЎРЉР С”Р С• РЎРѓР ВµР С”РЎС“Р Р…Р Т‘ Р С‘ Р С—Р С•Р С”Р ВµР В±Р С•Р В» РЎС“Р В¶Р Вµ Р В»Р ВµРЎвЂљР С‘РЎвЂљ Р Р† РЎРѓРЎвЂљР С•РЎР‚Р С•Р Р…РЎС“ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°. Р РЃР В°РЎР‚Р С‘Р С” РЎРѓРЎвЂљРЎС“Р С”Р Р…РЎС“Р В»РЎРѓРЎРЏ Р С• РЎвЂљР ВµР В»Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р С‘ Р’В«Р С—Р С•Р С–Р В»Р С•РЎвЂљР С‘Р В»Р’В» Р ВµР С–Р С• Р Р†Р С•Р Р†Р Р…РЎС“РЎвЂљРЎР‚РЎРЉ. Р СњР ВµР С”Р С•РЎвЂљР С•РЎР‚Р С•Р Вµ Р Р†РЎР‚Р ВµР СРЎРЏ Р С—Р С•Р С”Р ВµР В±Р С•Р В» Р С”Р В°РЎвЂЎР В°Р В»РЎРѓРЎРЏ Р С‘Р В· РЎРѓРЎвЂљР С•РЎР‚Р С•Р Р…РЎвЂ№ Р Р† РЎРѓРЎвЂљР С•РЎР‚Р С•Р Р…РЎС“, Р Р…Р С• Р С—Р С•РЎРѓР В»Р Вµ Р С—Р С•РЎРѓР В»РЎвЂ№РЎв‚¬Р В°Р В»РЎРѓРЎРЏ РЎвЂ°Р ВµР В»РЎвЂЎР С•Р С” Р С‘ РЎв‚¬Р В°РЎР‚Р С‘Р С” РЎС“Р В¶Р Вµ РЎРѓР С—Р С•Р С”Р С•Р в„–Р Р…Р С• Р В»Р ВµР В¶Р В°Р В» Р Р…Р В° РЎвЂљРЎР‚Р В°Р Р†Р С”Р Вµ. Р СџР С•Р Т‘Р С•Р В±РЎР‚Р В°Р Р† Р С—Р С•Р С”Р ВµР В±Р С•Р В», Р вЂ™РЎвЂ№ РЎвЂљРЎС“РЎвЂљ Р В¶Р Вµ Р С—Р С•Р В±Р ВµР В¶Р В°Р В»Р С‘ Р С•Р В±РЎР‚Р В°РЎвЂљР Р…Р С• Р С” Р С—РЎР‚Р С•РЎвЂћР ВµРЎРѓРЎРѓР С•РЎР‚РЎС“.*','block');\",1000);</script>"); 
     }else{  
      print '<script>alert("Р С›РЎв‚¬Р С‘Р В±Р С”Р В°, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В°.");</script>'; 
     }
  }
  $quest_isset_const = 1;
  $about = '<script>
                function valpoke(val){ window.document.getElementById(\'vib\').innerHTML="<b>Р вЂ™РЎвЂ№ Р Р†РЎвЂ№Р В±РЎР‚Р В°Р В»Р С‘: "+val+"</b>"; window.document.getElementById(\'pokeName\').value=val;}
                function confirmPok(){ if(window.document.getElementById(\'pokeName\').value == false) alert("Р вЂ™РЎвЂ№Р В±Р ВµРЎР‚Р С‘РЎвЂљР Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°!"); else { if (confirm("Р вЂ™РЎвЂ№ Р С—Р С•Р Т‘РЎвЂљР Р†Р ВµРЎР‚Р В¶Р Т‘Р В°Р ВµРЎвЂљР Вµ РЎвЂЎРЎвЂљР С• Р Р†РЎвЂ№Р В±Р С‘РЎР‚Р В°Р ВµРЎвЂљР Вµ: "+window.document.getElementById(\'pokeName\').value+"?")) { window.document.getElementById(\'formPoke\').submit();  return true; } else { return false; } }}
            </script>
            <table border="1" align="center">
              <form action="" target="_location_two" method="POST" name="formPoke" id="formPoke">
              <tr>
                <td colspan="15" align="center"> 
                 <span id="vib"><b>Р вЂ™РЎвЂ№Р В±Р ВµРЎР‚Р С‘РЎвЂљР Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</b></span>
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
                   <input style="border:2px solid #000;margin:3px;font-weight:bold;" onclick="confirmPok();" type="button" name="button" value="Р СџР С•Р Т‘РЎвЂљР Р†Р ВµРЎР‚Р Т‘Р С‘РЎвЂљРЎРЉ">
                </td>
              </tr>
              </form>
            </table>';
  $pers  = '<a href="/game.php?go=char">Р Р€Р в„–РЎвЂљР С‘</a>';
}

?>