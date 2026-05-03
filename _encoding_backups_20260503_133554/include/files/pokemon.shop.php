<?php
if($myrow['groups'] == 7 || $myrow['groups'] == 10) die("<script>alert('Р СџРЎР‚Р В°Р Р†Р С•Р Р…Р В°РЎР‚РЎС“РЎв‚¬Р С‘РЎвЂљР ВµР В»РЎРЏР С Р Р†РЎвЂ¦Р С•Р Т‘ Р В·Р В°Р С—РЎР‚Р ВµРЎвЂ°Р ВµР Р…!'); location.href='..';</script>");

$mBuild = first('SELECT b.town,t.townName FROM build b INNER JOIN towns t ON b.town=t.id WHERE b.id=%d',$myrow['buildmy']);
$my_Build = ($mBuild['town']?$mBuild['town']:1);
$nameTown = $mBuild['townName'];

if($my_Build == 2) die("<script>alert('Р вЂ™Р С• Р Р†РЎР‚Р ВµР СРЎРЏ Р С—Р ВµРЎР‚Р ВµР В»Р ВµРЎвЂљР В°/Р С—Р С•Р ВµР В·Р Т‘Р С”Р С‘ Р Р…Р ВµР Р†Р С•Р В·Р СР С•Р В¶Р Р…Р С• Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ РЎР‚РЎвЂ№Р Р…Р С”Р С•Р С.');</script><script>window.close();</script>");


function selectedDelet($id){
  $p = first('SELECT id_lot FROM rinok_poke WHERE id_lot=%d',$id);
  if(!empty($p['id_lot'])) $s = true; else $s = false;
 return $s;
}
$deletedPoke = select('SELECT id_lot,user_pok,id_poke,dateend FROM rinok_poke WHERE dateend<=%d LIMIT 0,20',time());
if($deletedPoke){
  foreach($deletedPoke as $deletedgo){ 
    $time = time();
    $fal  = false;
    if($deletedgo["dateend"] <= $time && $deletedgo["dateend"] != 'not'){
      if(selectedDelet($deletedgo["id_lot"]) == true){
        $pokemon  = $deletedgo["id_poke"];
        $userPok  = $deletedgo['user_pok'];
        $fal = true;
      }     
      delete('rinok_poke','id_lot='.(int)$deletedgo["id_lot"].' AND dateend<='.(int)$time);
      if($fal == true){
         update('pok_user',array('users'=>$userPok, 'active'=>0, 'startepoke'=>0),'users=3 AND id='.(int)$pokemon);
         $ps = first('SELECT names FROM pok_user WHERE id=%d',$pokemon); 
         $textSend = "Р СџР С•Р С”Р ВµР СР С•Р Р…: <span style='color:brown'>#".$ps['names']."</span> Р Р…Р Вµ Р В±РЎвЂ№Р В» Р С—РЎР‚Р С•Р Т‘Р В°Р Р… Р С‘ Р В±РЎвЂ№Р В» Р Р†Р С•Р В·Р Р†РЎР‚Р В°РЎвЂ°Р ВµР Р….";
         messSisyem($textSend,$userPok,'Р вЂ™Р С•Р В·Р Р†РЎР‚Р В°РЎвЂ°Р ВµР Р…Р С‘Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°');
      }
    } 
  }
}

if(!empty($_GET['idPokes'])){
?>                        
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>League Of Pokemons -> Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р Вµ #<?php print $_GET['idPokes'];?></title>
<meta http-equiv="Content-Type" content="text/html; Charset=Windows-1251" />
<link Rel="stylesheet" Href="css/stylepl.css" Type="text/css">
<style>
.styleinfo {
  background: #505050;
  background: -moz-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#505050), color-stop(50%,#808080), color-stop(100%,#505050));
  background: -webkit-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: -o-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: -ms-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  background: linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
  width: 100%;
  height: 110%;
}
</style>
<script type="text/javascript">
function info(id, noom){
  eval("document.all.inf"+noom+".style.display = \"none\";");
  eval("document.all.stat"+noom+".style.display = \"none\";");
  eval("document.all.atc"+noom+".style.display = \"none\";");
  eval("document.all.baf"+noom+".style.display = \"none\";");
  eval("document.all."+id+".style.display = \"block\"");
}
</script>
</head>
<body>
<?php 
  $pokeIds = obr_chis($_GET['idPokes']);
  $varsPokes = first("SELECT * FROM  pok_user WHERE id=%d AND users=3",$pokeIds);
  $varsPokesRinok = first("SELECT id_lot,dateend,user_pok FROM  rinok_poke WHERE id_poke=%d AND regions=%d",$pokeIds,$my_Build);
  if(!$varsPokes || empty($varsPokesRinok['id_lot'])) die('<h1>Р Р€Р Р†РЎвЂ№, Р Р…Р С• Р С‘Р Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С•Р В± РЎРЊРЎвЂљР С•Р С Р С—Р С•Р С”Р ВµР СР С•Р Р…Р Вµ РЎРѓР С”РЎР‚РЎвЂ№РЎвЂљР В°.</h1>');
  $myEv =  $varsPokes['evcount'];
  $aEvPlus = "<img src='img/plus.gif' border=0 width=14/>";
  $idPokemon =  $varsPokes['id'];
  $pokeStart =  $varsPokes['startepoke'];
  $sexPokemon =  $varsPokes['sex'];
  if($sexPokemon == 1) $sexPokemon = "<img src='img/sex1.png' border=0/> Р СљР В°Р В»РЎРЉРЎвЂЎР С‘Р С”"; else $sexPokemon = "<img src='img/sex2.png' border=0/> Р вЂќР ВµР Р†Р С•РЎвЂЎР С”Р В°"; 
  $reproduction = $varsPokes['reproduction'];
  if($reproduction == 1)  $reproduction = "Р СњР ВµР Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•"; else $reproduction = "Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•";
  $classSt = "startPoke";
  $classNo = "defaultPoke";
  $harPoke = haracter_pokes($varsPokes['har']);
  $itemsMy = itemsPokemon($idPokemon);
  $hpEv =  $varsPokes['hp_ev'];
  $atEv =  $varsPokes['atk_ev'];
  $dfEv =  $varsPokes['def_ev'];
  $saEv =  $varsPokes['satk_ev'];
  $sdEv =  $varsPokes['sdef_ev'];
  $spEv =  $varsPokes['speed_ev'];
  $baseId =  $varsPokes['basenum'];
  $namePoke =  $varsPokes['names'];
  $expPoke  =  $varsPokes['exp'];
  $lvlPoke  =  $varsPokes['lvl'];
  $tipPoke  =  $varsPokes['tips'];
  $hpPoke   =   $varsPokes['hp_my'];
  $maxHpPoke =  $varsPokes['hp_max'];
  $happyPoke =  $varsPokes['happy'];
  $hpProcent = ($hpPoke/$maxHpPoke)*100;
  if($hpProcent > 100) $hpProcent = 100;
  $expPolos = lvl_polos($lvlPoke,$expPoke);
  if($expPolos>100) $expPolos = 100;
  if($expPolos<0) $expPolos = 0;
    if($myEv > 0 && $hpEv < 255) $hpPlus = ""; else $hpPlus = "";
    if($myEv > 0 && $atEv < 255) $atPlus = ""; else $atPlus = "";
    if($myEv > 0 && $dfEv < 255) $dfPlus = ""; else $dfPlus = "";
    if($myEv > 0 && $saEv < 255) $saPlus = ""; else $saPlus = "";
    if($myEv > 0 && $sdEv < 255) $sdPlus = ""; else $sdPlus = "";
    if($myEv > 0 && $spEv < 255) $spPlus = ""; else $spPlus = "";    
    $one  = false; 
    $two  = false; 
    $thre = false; 
    $four = false;
    $pp1  = false; 
    $pp2  = false; 
    $pp3  = false;  
    $pp4  = false;
    $one  =  name_atc(1,$idPokemon);
    $two  =  name_atc(2,$idPokemon);
    $thre =  name_atc(3,$idPokemon);
    $four =  name_atc(4,$idPokemon);
    if($one)  $id_1  = "<a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".name_atc_pve(1,$idPokemon,'atac_id')."','atk','width=726,height=260,scrollbars=yes');return true;><img src=\"/img/other/inf.png\"/></a> ";
    if($two)  $id_2  = "<a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".name_atc_pve(2,$idPokemon,'atac_id')."','atk','width=726,height=260,scrollbars=yes');return true;><img src=\"/img/other/inf.png\"/></a> ";
    if($thre) $id_3  = "<a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".name_atc_pve(3,$idPokemon,'atac_id')."','atk','width=726,height=260,scrollbars=yes');return true;><img src=\"/img/other/inf.png\"/></a> ";
    if($four) $id_4  = "<a href=javascript: onClick=win1=window.open('/game.php?go=atk&id=".name_atc_pve(4,$idPokemon,'atac_id')."','atk','width=726,height=260,scrollbars=yes');return true;><img src=\"/img/other/inf.png\"/></a> ";
    if($one)  $one  =  $id_1.$one;
    if($two)  $two  =  $id_2.$two;
    if($thre) $thre =  $id_3.$thre;
    if($four) $four =  $id_4.$four;
    if($one)  $pp1  =  atac_pp(1,$idPokemon); else $one  = 'Р СњР ВµРЎвЂљ Р В°РЎвЂљР В°Р С”Р С‘';
    if($two)  $pp2  =  atac_pp(2,$idPokemon); else $two  = 'Р СњР ВµРЎвЂљ Р В°РЎвЂљР В°Р С”Р С‘';
    if($thre) $pp3  =  atac_pp(3,$idPokemon); else $thre = 'Р СњР ВµРЎвЂљ Р В°РЎвЂљР В°Р С”Р С‘';
    if($four) $pp4  =  atac_pp(4,$idPokemon); else $four = 'Р СњР ВµРЎвЂљ Р В°РЎвЂљР В°Р С”Р С‘';
    if(!atac_pp_isset(1,$idPokemon) && $one)  { $one  = '<s>'.$one.'</s>';  $pp1 = '<s>'.$pp1.'</s>';}
    if(!atac_pp_isset(2,$idPokemon) && $two)  { $two  = '<s>'.$two.'</s>';  $pp2 = '<s>'.$pp2.'</s>';}
    if(!atac_pp_isset(3,$idPokemon) && $thre) { $thre = '<s>'.$thre.'</s>'; $pp3 = '<s>'.$pp3.'</s>';}
    if(!atac_pp_isset(4,$idPokemon) && $four) { $four = '<s>'.$four.'</s>'; $pp4 = '<s>'.$pp4.'</s>';}        
    $textDel = "";
    if($pokeStart != 1) $textStart = ""; else  $textStart = "";
    if($pokeStart != 1) $textDel = "";
    $textStart =  timersOtshet($varsPokesRinok["dateend"],"<br>Р вЂќР С• Р С•Р С”Р С•Р Р…РЎвЂЎР В°Р Р…Р С‘РЎРЏ Р В»Р С•РЎвЂљР В° Р С•РЎРѓРЎвЂљР В°Р В»Р С•РЎРѓРЎРЉ: ", "Р РЋРЎР‚Р С•Р С” Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘РЎРЏ Р В»Р С•РЎвЂљР В° Р С‘РЎРѓРЎвЂљР ВµР С”");
    $textStart = "<b>".$textStart."</b>";  
    print '
  <div class = "styleinfo">
  <div align=center style="position:absolute; top:38px; left: 64px;">
  <center><b>Р СџРЎР‚Р С•Р Т‘Р В°Р Р†Р ВµРЎвЂ : </b> '.color_group_users($varsPokesRinok['user_pok']).'</center>
  <table width="100%">
  <tr>
   <td style="overflow:hidden;">
        <div style="width: 473px; text-align:center; font:11px Tahoma; color: #fff;" class="pokeDivInf">
          <a href=javascript: onclick="info(\'inf'.$idPokemon.'\','.$idPokemon.')">Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ</a>   
			    <a href=javascript: onclick="info(\'stat'.$idPokemon.'\','.$idPokemon.')">Р РЋРЎвЂљР В°РЎвЂљРЎвЂ№</a>
          <a href=javascript: onclick="info(\'atc'.$idPokemon.'\','.$idPokemon.')">Р С’РЎвЂљР В°Р С”Р С‘</a>
          <a href=javascript: onclick="info(\'baf'.$idPokemon.'\','.$idPokemon.')">Р Р€РЎРѓР С‘Р В»Р ВµР Р…Р С‘РЎРЏ</a>
        </div>
        <div style="height:290; overflow:hidden;margin: 0 0 0 0;" >
          <div id="stat'.$idPokemon.'" class="pokeDiv" style="display:block;top:0;height:200px;">
            <center><b>Р РЋРЎвЂљР В°РЎвЂљРЎвЂ№:</b></center>
            <table align="center"  width="100%">
              <tr>
                <td><b><span style="color:#000;">Р РЋРЎвЂљР В°РЎвЂљ:</span></b></td>
                <td><b><span style="color:#000;">Р вЂ”Р Р…Р В°РЎвЂЎР ВµР Р…Р С‘Р Вµ:</span></b></td>
                <td><b><span style="color:#000;">EV:</span></b></td>
                <td><span style="color:#000;">Р вЂњР ВµР Р…:</span></td>
              </tr>
              <tr>
                <td><b><span style="color:#000;">Р СњР В :</span></b></td>
                <td><b>'.$varsPokes['hp_max'].'</b></td>
                <td><b><span style="color:#0bda51;">'.$hpEv.'</span></b> '.$hpPlus.'</td>
                <td><span style="color:#ffff00;">'.$varsPokes['hp_iv'].'</span></td>
              </tr>
              <tr>
                <td colspan="4"><div class="trPokes"></div></td>
              </tr>
				      <tr>
                <td><b><span style="color:#000;">Р С’РЎвЂљР В°Р С”Р В°:</span></b></td>
                <td><b>'.$varsPokes['atk'].'</b></td>
                <td><b><span style="color:#0bda51;">'.$atEv.'</span></b> '.$atPlus.'</td>
                <td><span style="color:#ffff00;">'.$varsPokes['atk_iv'].'</span></td>
              </tr>
              <tr>
                <td colspan="4"><div class="trPokes"></div></td>
              </tr>
				      <tr>
                <td><b><span style="color:#000;">Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°:</span></b></td>
                <td><b>'.$varsPokes['def'].'</b></td>
                <td><b><span style="color:#0bda51;">'.$dfEv.'</span></b> '.$dfPlus.'</td>
                <td><span style="color:#ffff00;">'.$varsPokes['def_iv'].'</span></td>
              </tr>
              <tr>
                <td colspan="4"><div class="trPokes"></div></td>
              </tr>
				      <tr>
                <td><b><span style="color:#000;">Р РЋР С”Р С•РЎР‚Р С•РЎРѓРЎвЂљРЎРЉ:</span></b></td>
                <td><b>'.$varsPokes['speed'].'</b></td>
                <td><b><span style="color:#0bda51;">'.$spEv.'</span></b> '.$spPlus.'</td>
                <td><span style="color:#ffff00;">'.$varsPokes['speed_iv'].'</span></td>
              </tr>
              <tr>
                <td colspan="4"><div class="trPokes"></div></td>
              </tr>
				      <tr>
                <td><b><span style="color:#000;">Р РЋР С—Р ВµРЎвЂ .Р С’РЎвЂљР В°Р С”Р В°:</span></b></td>
                <td><b>'.$varsPokes['satk'].'</b></td>
                <td><b><span style="color:#0bda51;">'.$saEv.'</span></b> '.$saPlus.'</td>
                <td><span style="color:#ffff00;">'.$varsPokes['satk_iv'].'</span></td>
              </tr>
              <tr>
                <td colspan="4"><div class="trPokes"></div></td>
              </tr>
				      <tr>
                <td width="100"><b><span style="color:#000;">Р РЋР С—Р ВµРЎвЂ .Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°:</span></b></td>
                <td><b>'.$varsPokes['sdef'].'</b></td>
                <td><b><span style="color:#0bda51;">'.$sdEv.'</span></b> '.$sdPlus.'</td>
                <td><span style="color:#ffff00;">'.$varsPokes['sdef_iv'].'</span></td>
              </tr>
              <tr>
                <td colspan="4"><div class="trPokes"></div></td>
              </tr>
				      <tr>
                <td colspan="3"><b>&nbsp;<b><span style="color:gold;">Р С›РЎвЂЎР С”Р С‘ EV: </span><span style="color:#ff47ca;">'.$myEv.'</span></b> </b></td>
              </tr>
				      <tr>
                <td align=center colspan="2">
                  '.$textStart.'
                </td>
                <td colspan="2">
                  '.$textDel.'
                </td>
              </tr>      
            </table>         
          </div>
          <div id="inf'.$idPokemon.'" class="pokeDiv" style="top:-200; height:200px;"  align="center">
            <center><b>Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ:</b></center>
              <table align="center"  width="100%"><tr>
              <td><b><span style="color:#000;">Р ВР СРЎРЏ:</td><td></span></b><a href=javascript: onClick="win1=window.open(\'game.php?go=pokedex&id='.$baseId.'\',\'pokedex\',\'width=550,height=550,scrollbars=yes\');return true;"><img src=img/other/pokedex.png></a> <i><b>#'.$namePoke.'.</b></i></td>
              </tr><tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ:</td><td></span></b><i><b style="color:gold;">'.$lvlPoke.'-Lvl.</b></i></td>
              </tr><tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">Р РЋРЎвЂЎР В°РЎРѓРЎвЂљРЎРЉР Вµ:</td><td></span></b><i><b style="color:gold;">'.$happyPoke.'%.</b></i></td>
              </tr> <tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">Р СџР С•Р В»:</td><td></span></b><i><b style="color:gold;">'.$sexPokemon.'.</b></i></td>
				      </tr> <tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">Р ТђР В°РЎР‚Р В°Р С”РЎвЂљР ВµРЎР‚:</td><td></span></b><i><b style="color:gold;">'.$harPoke.'.</b></i></td>
				      </tr> <tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ:</td><td></span></b><i><b style="color:gold;">'.$reproduction.'.</b></i></td>
              </tr> <tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">Р СџР С•Р в„–Р СР В°Р Р… РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р С•Р С:</td><td></span></span></b>'.color_group_users($varsPokes['usersone']).'</td>  
				      </tr> <tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">Р вЂќР В°РЎвЂљР В° Р С—Р С•Р С‘Р СР С”Р С‘:</td><td></span></b><i><b  style="color:gold;">'.$varsPokes['datemay'].'.</b></i></td>
				      </tr> <tr><td colspan="2"><div class="trPokes"></div></td></tr><tr>
              <td><b><span style="color:#000;">ID:</td><td></span></b><i><b  style="color:gold;">'.$idPokemon.'.</b></i></td>
              </tr>
              </table> 
          </div>
          <div id="atc'.$idPokemon.'" class="pokeDiv" style="top:-400;height:200px;">
            <center><b>Р вЂ™РЎвЂ№РЎС“РЎвЂЎР ВµР Р…Р Р…РЎвЂ№Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:</b></center>
            <table align="center"  width="100%">
              <tr>
                <td align="center" width="20%">
                 <span style="font-size:12;color:Black;"><b>'.$one.'</b><br>'.$pp1.'<br> </span>
                </td>
                <td align="center" width="20%">
                 <span style="font-size:12;color:Black;"><b>'.$two.'</b><br>'.$pp2.'<br> </span>
                </td>
              </tr>
              <tr>
                <td align="center" width="20%">
                 <span style="font-size:12;color:Black;"><b>'.$thre.'</b><br>'.$pp3.'<br> </span>
                </td>
                <td align="center" width="20%">
                 <span style="font-size:12;color:Black;"><b>'.$four.'</b><br>'.$pp4.'<br> </span>
                </td>
              </tr>
            </table>
          </div>
          <div id="baf'.$idPokemon.'" class="pokeDiv" style="top:-600;height:200px;">
             <center><b>Р вЂќР С•Р С—Р С•Р В»Р Р…Р С‘РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р Вµ РЎС“РЎРѓР С‘Р В»Р ВµР Р…Р С‘РЎРЏ:</b></center>
            '.bafIssetPoke($idPokemon,'hp','plus').'
            '.bafIssetPoke($idPokemon,'hp','minus').'
            
            '.bafIssetPoke($idPokemon,'atc','plus').'
            '.bafIssetPoke($idPokemon,'atc','minus').'
            
            '.bafIssetPoke($idPokemon,'satc','plus').'
            '.bafIssetPoke($idPokemon,'satc','minus').'
            
            '.bafIssetPoke($idPokemon,'def','plus').'
            '.bafIssetPoke($idPokemon,'def','minus').'
            
            '.bafIssetPoke($idPokemon,'sdef','plus').'
            '.bafIssetPoke($idPokemon,'sdef','minus').'
            
            '.bafIssetPoke($idPokemon,'speed','plus').'
            '.bafIssetPoke($idPokemon,'speed','minus').'
          </div>
        </div>
   </td>
  </tr>
 </table>
 </div>  </div>
  ';
die('</body></html>');
}
function selectedPoke(){
  
}
function selectPokesLvl($tip,$a){
  if(!empty($a)) $baseGet = obr_chis($a); else $baseGet = "0";
  $option = false;
    for($i=1;$i<101;$i++){
          if($i == $tip) $selected = " selected "; else $selected = false; 
          $option .= '<option '.$selected.' value="'.$i.'">'.$i.'-lvl</option>';
    }
 return '<select size="1" id="pokeLvl" onchange="loadPokes(1,'.$baseGet.',this.value);"><option value="0">Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ</option>'.$option.'</select>';
}
function selectPokesBase($tip,$a){
  global $my_Build;
  if(!empty($a))  $lvlGet  = obr_chis($a); else $lvlGet = "0";
  $i = select('SELECT DISTINCT pb.id AS id, pb.title FROM rinok_poke rp 
               INNER JOIN pok_user pu 
               ON pu.id=rp.id_poke AND pu.users=3 
               INNER JOIN poke_base pb
               ON pu.basenum=pb.id
               WHERE pu.users=3 AND rp.regions=%d ORDER BY pb.id ASC',$my_Build);
  $option = false;
   if($i){
    foreach($i as $o){
       
          if($o['id'] == $tip) $selected = " selected "; else $selected = false; 
          $option .= '<option '.$selected.' value="'.$o['id'].'">#'.$o['title'].'</option>';
    }
  }
 return '<select size="1" id="pokeBase" onchange="loadPokes(1,this.value,'.$lvlGet.');"><option value="0">Р вЂ™РЎРѓР Вµ</option>'.$option.'</select>';
}


if(!empty($_GET['sendZapStr']) && ($_GET['sendZapStr'] > 0) && isset($_GET['basetip']) && isset($_GET['levels'])){
  Header('Content-Type: text/css;charset=Windows-1251');
  if(!$_GET['sendZapStr'])  die("ERROR");
  include('include/function/itemsinpage.rinokpoke.php');
  if(!empty($_GET['levels']))  $lvlGet  = obr_chis($_GET['levels']); else $lvlGet = "0";
  if(!empty($_GET['basetip'])) $baseGet = obr_chis($_GET['basetip']); else $baseGet = "0";
  $_GET['page'] = $_GET['sendZapStr']-1;
  $posa = 0;
  $print = 1;
  $pos = $posa*154;
  $itemsCount = first("SELECT COUNT(*) as count FROM rinok_poke WHERE regions=%d",$my_Build);  
  $zap = false; 
  if($lvlGet > 0 || $baseGet > 0){
      $escapedBaseGet = mysql_escape_string($baseGet);
      $escapedlvlGet  = mysql_escape_string($lvlGet);
     if($baseGet > 0) $zap  .= " AND pu.basenum=".$escapedBaseGet." "; 
     if($lvlGet  > 0) $zap  .= " AND pu.lvl=".$escapedlvlGet." ";    
        $itemsCount  = first("SELECT COUNT(*) as count 
                              FROM rinok_poke rp
                              Inner Join pok_user pu
                              ON pu.id=rp.id_poke
                              WHERE pu.users=3 AND rp.regions=%d ".$zap,$my_Build);
  }     
     $itemsinpage = new Itemsinpage($itemsCount['count']);
     $resultPoke = select('SELECT rp.maney, rp.id_lot, rp.user_id_to, rp.user_pok, pu.id, pu.names, pu.sex, pu.lvl, pu.basenum, pu.tips, pu.hp_iv, pu.atk_iv, pu.def_iv,	pu.satk_iv,	
                          pu.sdef_iv, pu.speed_iv, pu.reproduction, pu.evcount, pu.har,
                          pu.hp_ev,	pu.atk_ev,	pu.def_ev,	pu.satk_ev,	pu.sdef_ev,	pu.speed_ev
                            FROM rinok_poke rp
                            Inner Join pok_user pu 
                            ON pu.id=rp.id_poke
                            WHERE pu.users=3 AND rp.regions=%d '.$zap.'  
                            ORDER BY pu.basenum ASC, rp.maney ASC 
                            LIMIT %d,%d',$my_Build,$itemsinpage->get('Start'),$itemsinpage->get('Limit'));  
   $data = $itemsinpage->SmartyArr();
        $txtStran = false;
        $txtStran .= '<table width="700" style="font-weight:bold; font-size:15px;"><tr><td align="center"><small><a href="javascript:" onclick="loadPokes(1,'.$baseGet.','.$lvlGet.');">Р С›Р В±Р Р…Р С•Р Р†Р С‘РЎвЂљРЎРЉ</a></small><br>'.selectPokesBase($baseGet,$lvlGet).' - '.selectPokesLvl($lvlGet,$baseGet).'<td></tr><tr><td style="color:#000;font-weight:bold;">Р РЋРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В°: ';
        for($i=0,$n=sizeof($data['Count']);$i<$n;$i++):
        if($data['Count'][$i][1]!=$_GET['page']){    
          $st = $data['Count'][$i][1]+1;	   
          $txtStran .= '-<button class="butStr" onclick="loadPokes('.$st.','.$baseGet.','.$lvlGet.');">'.$st.'</button>';
        }else{
          $st2 = $data['Count'][$i][0];      
          $txtStran .= '-<button class="butStrYes" onclick="loadPokes('.$st2.','.$baseGet.','.$lvlGet.');">'.$st2.'</button>';    
        }
        endfor;
      $txtStran .='</td></tr></table>';
      print $txtStran;
     if(empty($resultPoke)) die("<b style='font-size:15px;color:gold;'>Р СџР С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Р…Р В° РЎР‚РЎвЂ№Р Р…Р С”Р Вµ Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…Р С•.</b>");
    foreach($resultPoke as $pokeEcho){                                       
      if($pokeEcho['sex'] == 1) { $className = "pokeNamesM"; $img = "1"; $bg = "42aaff"; } else { $className= "pokeNamesD"; $img = "2";  $bg = "ffc0cb";} 
      $genom = "HP: ".$pokeEcho['hp_iv']."|AT: ".$pokeEcho['atk_iv']."|DF: ".$pokeEcho['def_iv']."|SP: ".$pokeEcho['speed_iv']."|SA: ".$pokeEcho['satk_iv']."|SD: ".$pokeEcho['sdef_iv'];
      $ev    = "HP: ".$pokeEcho['hp_ev']."|AT: ".$pokeEcho['atk_ev']."|DF: ".$pokeEcho['def_ev']."|SP: ".$pokeEcho['speed_ev']."|SA: ".$pokeEcho['satk_ev']."|SD: ".$pokeEcho['sdef_ev'];
      $raz   = ($pokeEcho['reproduction']==1?'Р СњР ВµР Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•':'Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•');
      $baseId  = $pokeEcho['basenum'];
      $nameBlank  = $pokeEcho['names'];
       $nameBlank = str_replace('"','',$nameBlank);
       $nameBlank = str_replace("'","",$nameBlank);
    
      $txtPoke = '<center><iframe class=iframe name=Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р Вµ frameborder=0 src=/game.php?go=pokerinok&idPokes='.$pokeEcho['id'].'></iframe></center>';
      if($pokeEcho['user_pok'] != $_SESSION['id']) $hrefs = '<a href="/game.php?go=pokerinok&goLot='.$pokeEcho['id_lot'].'" title="Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ: #'.$nameBlank.'">Р С™РЎС“Р С—Р С‘РЎвЂљРЎРЉ</a>';
        else  $hrefs = '<a href="/game.php?go=pokerinok&goLot='.$pokeEcho['id_lot'].'&my=my" title="Р РЋР Р…РЎРЏРЎвЂљРЎРЉ РЎРѓ Р С—РЎР‚Р С•Р Т‘Р В°Р В¶Р С‘: #'.$nameBlank.'">Р РЋР Р…РЎРЏРЎвЂљРЎРЉ РЎРѓ Р С—РЎР‚Р С•Р Т‘Р В°Р В¶Р С‘</a>';
      if($pokeEcho['user_id_to'] != 'no') $pered = "<br>Р вЂќР В»РЎРЏ: ".color_group_users($pokeEcho['user_id_to']).".";  else $pered = "";
      print '<div class="pokesEcho">
              <span class="'.$className.'"><img src="/pok/anim/'.$baseId.'.gif" width="24"> #'.$pokeEcho['names'].' - '.$pokeEcho['lvl'].' Lvl (<img src="/img/sex'.$img.'.png" width="13">)</span>
              <br>
              <div align="left">
              Р В¦Р ВµР Р…Р В°: <span style="font-size:12px;color:#7df9ff;">'.formatnum($pokeEcho['maney']).' Р СљР С•Р Р…Р ВµРЎвЂљ</span>.<br>  
              EV: <span style="font-size:12px;color:#7df9ff;">'.$ev.'</span>.<br>
              EV(Р Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•): <span style="font-size:12px;color:#7df9ff;">'.$pokeEcho['evcount'].'</span>.<br> 
              Р вЂњР ВµР Р…РЎвЂ№: <span style="font-size:12px;color:#7df9ff;">'.$genom.'</span>.<br>
              Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ: <span style="font-size:12px;color:#7df9ff;">'.$raz.'</span>.<br>
              Р ТђР В°РЎР‚Р В°Р С”РЎвЂљР ВµРЎР‚: <span style="font-size:12px;color:#7df9ff;">'.haracter_pokes($pokeEcho['har']).'</span>.  
              '.$pered.'
              </div>                                
              '.$hrefs.' | <a href="javascript:" onClick=\'enter("'.$txtPoke.'","'.$bg.'");\' title="Р СџР С•Р Т‘РЎР‚Р С•Р В±Р Р…Р ВµР Вµ Р С•: #'.$nameBlank.'">Р СџР С•Р Т‘РЎР‚Р С•Р В±Р Р…Р ВµР Вµ...</a> 
             </div>';
      $posa  ++;
      $pos   ++;
      $print ++;                                         
    }
for ($k=$print; $k<=9; $k++) echo "<div class=\"pokesEcho\">&nbsp;</div>";  
die();
}

if(!empty($_GET['goLot'])){
    if($myrow['pve']>0  OR $myrow['pvp']>0  OR $myrow['trade']>0) die("<script>alert('Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С РЎРѓР С•Р Р†Р ВµРЎР‚РЎв‚¬Р С‘РЎвЂљРЎРЉ Р С”Р В°Р С”Р С•Р Вµ-Р В»Р С‘Р В±Р С• Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘Р Вµ РЎРѓ Р С—Р С•Р С”РЎС“Р С—Р С”Р С•Р в„–/Р С—РЎР‚Р С•Р Т‘Р В°Р В¶Р ВµР в„–, Р вЂ™Р В°Р С Р Р…Р ВµР С•Р В±РЎвЂ¦Р С•Р Т‘Р С‘Р СР С• Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘РЎвЂљРЎРЉ Р В±Р С•Р в„–/Р С•Р В±Р СР ВµР Р….'); location.href='/game.php?go=pokerinok'</script>");
    $couny_poke = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d and active=1',$_SESSION['id']);
    $soboi = $couny_poke['count'];
    $lot = obr_chis($_GET['goLot']);
    if($soboi > 5) die("<script>alert('Р РЋ РЎРѓР С•Р В±Р С•Р в„– Р Т‘Р С•Р В»Р В¶Р Р…Р С• Р В±РЎвЂ№РЎвЂљРЎРЉ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ 6-РЎвЂљР С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†. Р СњР В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ, РЎС“ Р Р†Р В°РЎРѓ РЎС“Р В¶Р Вµ 6 Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†.');location.href='game.php?go=pokerinok';</script>"); 
    $z = first('SELECT * FROM rinok_poke WHERE id_lot=%d AND regions=%d',$lot,$my_Build);
    if(empty($z['id_lot'])) die("<script>alert('Р вЂќР В°Р Р…Р Р…РЎвЂ№Р в„– Р В»Р С•РЎвЂљ Р В±РЎвЂ№Р В» Р С—РЎР‚Р С•Р Т‘Р В°Р Р…, Р С‘Р В»Р С‘ РЎРѓР Р…РЎРЏРЎвЂљ РЎРѓ Р С—РЎР‚Р С•Р Т‘Р В°Р В¶Р С‘.'); location.href='game.php?go=pokerinok';</script>"); 
  if(empty($_GET['my'])){
     if($z['user_pok'] == $_SESSION['id'])  die("<script>alert('Р ВР Р…РЎвЂљР ВµРЎР‚Р ВµРЎРѓР Р…Р С•, Р В·Р В°РЎвЂЎР ВµР С Р вЂ™Р В°Р С Р С—Р С•Р С”РЎС“Р С—Р В°РЎвЂљРЎРЉ РЎРѓР Р†Р С•Р ВµР С–Р С• Р В¶Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°?');location.href='game.php?go=pokerinok';</script>");
     if($z['user_id_to'] != "no" && $z['user_id_to'] != $_SESSION['id']) die("<script>alert('Р В­РЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р С”РЎС“Р С—Р С‘РЎвЂљРЎРЉ Р Р…Р ВµР В»РЎРЉР В·РЎРЏ, Р С•Р Р… Р С—РЎР‚Р С•Р Т‘Р В°Р ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р Вµ Р вЂ™Р В°Р С!');location.href='game.php?go=pokerinok';</script>"); 
     if(!provitems(1,$z['maney'])) die("<script>alert('Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р СР С•Р Р…Р ВµРЎвЂљ Р Т‘Р В»РЎРЏ Р С—Р С•Р С”РЎС“Р С—Р С”Р С‘ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°!');location.href='game.php?go=pokerinok';</script>");
     // Р вЂ™РЎвЂ№РЎвЂЎР С‘РЎвЂљР В°Р ВµР С Р СР С•Р Р…Р ВµРЎвЂљРЎвЂ№ РЎС“ Р С—Р С•Р С”РЎС“Р С—Р В°РЎвЂљР ВµР В»РЎРЏ
      minus_item($z['maney'],1);
    // Р вЂ™РЎвЂ№РЎвЂЎР С‘РЎвЂљР В°Р ВµР С Р Р…Р В°Р В»Р С•Р С– Р С•РЎвЂљ РЎРѓРЎС“Р СР СРЎвЂ№
      $nalog = round($z['maney']*0.05);
      $res_chen = $z['maney'] - $nalog;
      nalog_clanz($nalog);
    // Р СњР В°РЎвЂЎР С‘РЎРѓР В»РЎРЏР ВµР С Р СР С•Р Р…Р ВµРЎвЂљРЎвЂ№ Р С—РЎР‚Р С•Р Т‘Р С•Р Р†РЎвЂ РЎС“
      plus_item($res_chen,1,$z['user_pok']);
    // Р СљР ВµР Р…РЎРЏР С Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№ Р Р† Р В±Р В°Р В·Р Вµ  
      $poluchatel = $z['user_pok'];
      update('pok_user',array('users'=>$_SESSION['id'], 'active'=>1, 'startepoke'=>0),'users=3 AND id='.(int)$z['id_poke']);
      delete('rinok_poke','id_lot='.(int)$z['id_lot']);
    // Р вЂєР С•Р С–Р С‘РЎР‚РЎС“Р ВµР С
        $arrPoke = array('pokup'=>$_SESSION['id'], 'prodav'=>$poluchatel, 'pokeid'=>$z['id_poke'], 'cena'=>$res_chen);
        logGames('poke',$arrPoke);                                  
    // Р РЋР С•Р С•Р В±РЎвЂ°Р В°Р ВµР С Р С—РЎР‚Р С•Р Т‘Р С•Р Р†РЎвЂ РЎС“    
        $pn = first('SELECT names,lvl FROM pok_user WHERE id=%d',$z['id_poke']);
        $text_send = "Р вЂ”Р Т‘РЎР‚Р В°Р Р†РЎРѓРЎвЂљР Р†РЎС“Р в„–РЎвЂљР Вµ, Р вЂ™Р В°РЎв‚¬ Р С—Р С•Р С”Р ВµР СР С•Р Р…(#".$pn['names']." ".$pn['lvl']." - Lvl), Р В»Р С•РЎвЂљ Р С”Р С•РЎвЂљР С•РЎР‚Р С•Р С–Р С• РІвЂћвЂ“".$z['id_lot'].", Р В±РЎвЂ№Р В» РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р С—РЎР‚Р С•Р Т‘Р В°Р Р… Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎР‹: ".$_SESSION['login'].". Р вЂ™РЎвЂ№ Р С—Р С•Р В»РЎС“РЎвЂЎР С‘Р В»Р С‘ Р Т‘Р ВµР Р…Р С–Р С‘ Р В·Р В° Р вЂ™Р В°РЎв‚¬ РЎвЂљР С•Р Р†Р В°РЎР‚ Р Р† РЎРѓРЎС“Р СР СР Вµ: ".formatnum($res_chen)." Р СР С•Р Р…Р ВµРЎвЂљ, Р С‘Р В· Р Р…Р С‘РЎвЂ¦ Р Р†РЎвЂ№РЎвЂЎР В»Р С‘ Р Р…Р В°Р В»Р С•Р С– Р Р…Р В° РЎРѓРЎС“Р СР СРЎС“: ".formatnum($nalog)." Р СР С•Р Р…Р ВµРЎвЂљ.";
        messSisyem($text_send,$poluchatel,'Р СћР С•РЎР‚Р С–Р С•Р Р†РЎвЂ№Р Вµ Р С•Р С—Р ВµРЎР‚Р В°РЎвЂ Р С‘Р С‘');
    // Р С™Р С•Р Р…Р ВµРЎвЂ  РЎРѓР С”РЎР‚Р С‘Р С—РЎвЂљР В° 
     die("<script>alert('Р СџР С•Р С”Р ВµР СР С•Р Р… РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С”РЎС“Р С—Р В»Р ВµР Р…, РЎРѓР С—Р В°РЎРѓР С‘Р В±Р С• Р В·Р В° Р С—Р С•Р С”РЎС“Р С—Р С”РЎС“.');location.href='game.php?go=pokerinok';</script>"); 
  }else{
     if($z['user_pok'] != $_SESSION['id']) die("<script>location.href='game.php?go=pokerinok';</script>");
     update('pok_user',array('users'=>$_SESSION['id'], 'active'=>1, 'startepoke'=>0),'users=3 AND id='.(int)$z['id_poke']);
     delete('rinok_poke','id_lot='.(int)$z['id_lot']); 
     die("<script>alert('Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р В·Р В°Р В±РЎР‚Р В°Р В»Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°.');location.href='game.php?go=pokerinok';</script>");
  }
}
if($_POST){
  if($myrow['pve']>0  OR $myrow['pvp']>0  OR $myrow['trade']>0) die("<script>alert('Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С РЎРѓР С•Р Р†Р ВµРЎР‚РЎв‚¬Р С‘РЎвЂљРЎРЉ Р С”Р В°Р С”Р С•Р Вµ-Р В»Р С‘Р В±Р С• Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘Р Вµ РЎРѓ Р С—Р С•Р С”РЎС“Р С—Р С”Р С•Р в„–/Р С—РЎР‚Р С•Р Т‘Р В°Р В¶Р ВµР в„–, Р вЂ™Р В°Р С Р Р…Р ВµР С•Р В±РЎвЂ¦Р С•Р Т‘Р С‘Р СР С• Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘РЎвЂљРЎРЉ Р В±Р С•Р в„–/Р С•Р В±Р СР ВµР Р….'); location.href='/game.php?go=pokerinok'</script>");
  $couny_poke = first('SELECT COUNT(*) as count FROM pok_user WHERE users=%d and active=1',$_SESSION['id']);
  $soboi = $couny_poke['count']; 
if(!empty($_POST['maney']) && !empty($_POST['pokeid'])){
  $maney = obr_chis($_POST['maney']);       
  $poke  = obr_chis($_POST['pokeid']);  
  if(empty($maney) || empty($poke)) die("<script>location.href='game.php?go=pokerinok';</script>"); 
  if($soboi <= 1) die("<script>alert('Р Р€ Р вЂ™Р В°РЎРѓ Р Р† Р С”Р С•Р СР В°Р Р…Р Т‘Р Вµ Р Т‘Р С•Р В»Р В¶Р ВµР Р… Р С•Р В±РЎРЏР В·Р В°РЎвЂљР ВµР В»РЎРЉР Р…Р С• Р С•РЎРѓРЎвЂљР В°РЎвЂљРЎРЉРЎРѓРЎРЏ РЎвЂ¦Р С•РЎвЂљРЎРЏР В±РЎвЂ№ Р С•Р Т‘Р С‘Р Р… Р С—Р С•Р С”Р ВµР СР С•Р Р….'); location.href='game.php?go=pokerinok';</script>"); 
  $myPok = first('SELECT startone,users,id FROM pok_user WHERE users=%d AND active=1 AND id=%d',$_SESSION['id'],$poke);
  if($myPok['startone'] > 0) die("<script>alert('Р вЂ™РЎвЂ№ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљР Вµ Р С—РЎР‚Р С•Р Т‘Р В°РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°, Р С—РЎР‚Р С•Р Т‘Р В°Р В¶Р В° РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р В·Р В°Р С—РЎР‚Р ВµРЎвЂ°Р ВµР Р…Р В°.');location.href='game.php?go=pokerinok';</script>"); 
  if($myPok['users'] != $_SESSION['id']) die("<script>location.href='game.php?go=pokerinok';</script>"); 
  $countR = first('SELECT COUNT(*) as count FROM rinok_poke WHERE user_pok=%d AND regions=%d',$_SESSION['id'],$my_Build);
  if($countR['count'] >= 3){
    die("<script>alert('Р вЂ™РЎвЂ№ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљР Вµ Р Р†РЎвЂ№РЎРѓРЎвЂљР В°Р Р†Р В»РЎРЏРЎвЂљРЎРЉ Р Р…Р В° РЎР‚РЎвЂ№Р Р…Р С•Р С” Р В±Р С•Р В»РЎРЉРЎв‚¬Р Вµ 3-РЎвЂ¦ Р В°Р С”РЎвЂљР С‘Р Р†Р Р…РЎвЂ№РЎвЂ¦ Р В»Р С•РЎвЂљР С•Р Р†.');</script><script>location.href='game.php?go=pokerinok';</script>");
  }
  $user_to_id = "no";
  if(!empty($_POST['user_to'])){
   if(!preg_match("|^[a-z_-]+$|i", $_POST['user_to'])) die("<script>alert('Р СњР Вµ Р С”Р С•РЎР‚РЎР‚Р ВµР С”РЎвЂљР Р…РЎвЂ№Р в„– Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ.');location.href='game.php?go=pokerinok';</script>"); 
    $proverka = first('SELECT login,id FROM users WHERE login="%s"',$_POST['user_to']);
    if(empty($proverka['id'])) die("<script>alert('Р СћР В°Р С”Р С•Р С–Р С• Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ Р Р…Р Вµ РЎРѓРЎС“РЎвЂ°Р ВµРЎРѓРЎвЂљР Р†РЎС“Р ВµРЎвЂљ!'); location.href='game.php?go=pokerinok';</script>");
    $user_to_id = $proverka['id']; 
  }
  $my_time_rinok = time() + (60*60*24*1);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
  insert('rinok_poke',array('user_pok'=>$myPok['users'], 'maney'=>$maney, 'id_poke'=>$myPok['id'], 'user_id_to'=>$user_to_id, 'dateend'=>$my_time_rinok, 'regions'=>$my_Build));
  update('pok_user',array('users'=>3, 'active'=>0, 'startepoke'=>0),'users='.(int)$_SESSION['id'].' AND id='.(int)$myPok['id']);
  die("<script>alert('Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—Р С•Р В»Р С•Р В¶Р С‘Р В»Р С‘ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р Р…Р В° Р С—РЎР‚Р С•Р Т‘Р В°Р В¶РЎС“!'); location.href='game.php?go=pokerinok';</script>");
} 
}
?>
<html>
<head>
<title>Pokelegenda -> Р РЋР С—Р С‘РЎРѓР С•Р С” Р С—РЎР‚Р С•Р Т‘Р В°Р Р†Р В°Р ВµР СРЎвЂ№РЎвЂ¦ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</title>
<link rel="Stylesheet" type="text/css" href="css/pokemonrinok.css"/>
<script type="text/javascript" src="fancybox/jquery-1.3.2.min.js"></script>
<script type="text/javascript">
function exit(){
  $("#infoPokeOver").animate({ left: "99%", top: "99%", opacity: 'hide', display: "none" }, "slow");
  $("#infoPoke").animate({ left: "99%", top: "99%", opacity: 'hide'}, "slow"); 
  $("#infoPokeOver .b-cont").html(''); 
}
function enter(txt,bg){
  $("#infoPokeOver").animate({left: "0%", top: "0%", width: "100%", opacity: "0.7", display: "block"}, "slow");
  $("#infoPokeOver").css({"background-color": "#"+bg});
  //document.getElementById("infoPokeOver").style.backgroundColor="#00000";
  $("#infoPoke").animate({ left: "41%", top: "30%", width: "650px", opacity: 1}, "slow"); 
  $(".b-cont").html(txt); 
}

function pdex(baseId){
  window.open('game.php?go=pokedex&id='+baseId,'pokedex','width=550,height=550,scrollbars=yes');
}
function loadPokes(strn,valueBase,levels){
  if(strn      == false)  var strn = 1;
  if(valueBase == false)  var valueBase = 0;
  if(levels     == false) var levels = 0; 
  $.ajax({  
    type: "GET",  
    url: "game.php",          
    data: "go=pokerinok&sendZapStr="+strn+"&basetip="+valueBase+"&levels="+levels,   
    success: function(txt){
       $("#inv").html(txt);
       //$("#tableIT").animate({opacity: 'hide' }, "slow");
    }
  });
}                         
</script>
<style>
a:link {text-decoration:underline; color:#E4FAFD;}
a:active {text-decoration:underline; color:#E4FAFD;}
a:visited {text-decoration:underline; color:#E4FAFD;}
a:hover {text-decoration:none; color:#FFFFFF;}

</style>
</head>
<div id="infoPokeOver"></div>
<div id="infoPoke">
	<div class="b-wrapper" align="center">
		<div class="b-title">Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р Вµ:</div>
		<div class="b-cont" align="center"></div>
		<div class="exit" onClick="exit();">[Р вЂ”Р В°Р С”РЎР‚РЎвЂ№РЎвЂљРЎРЉ]</div>
	</div>
</div>
<?php
  $pokeMyAktive = select('SELECT id,names,lvl FROM pok_user WHERE users=%d AND active=1',$_SESSION['id']);
?>
<table align=center width=500 style = "background-image: url('/css/img/vichy.png');" border=2 cellpadding=7>
  <tr>
    <td align="center" colspan=3>
       <b style="color:green">Р В РЎвЂ№Р Р…Р С•Р С” РЎР‚Р ВµР С–Р С‘Р С•Р Р…Р В°: <?=$nameTown;?>.</b>
    </td>
  </tr>
  <tr>
  <form action='' method='POST'>
    <td border=0 width=250>
      <b><font color="#000">Р В¦Р ВµР Р…Р В°:</font></b><br>
        <input name='maney' type='text' maxlength="16"> 
      <br>
        <b><font color="#000">Р СџР ВµРЎР‚Р ВµР Т‘Р В°РЎвЂљРЎРЉ(login):</font></b> 
      <br>
        <input name='user_to' type='text' maxlength="16">
    </td>
    <td>  
      <select size='1' name='pokeid'>
        <?php foreach($pokeMyAktive as $pokeMyAktiveRow){ ?>
          <option value='<?php echo $pokeMyAktiveRow['id'] ?>'>#<?php echo $pokeMyAktiveRow['names']." ".$pokeMyAktiveRow['lvl']; ?> - lvl</option>
        <?php } ?> 
      </select>
    </td>
    <td> 
    <input type='submit' name='submit' value='Р СџР С•Р В»Р С•Р В¶Р С‘РЎвЂљРЎРЉ Р Р…Р В° Р С—РЎР‚Р С•Р Т‘Р В°Р В¶РЎС“'> <br>
    </td>
  </form>
  </tr>
  <tr> 
    <td align=center colspan=3> 
      <?php $maney2 = first('SELECT count FROM items_users WHERE user_id=%d AND item_id=1',$_SESSION['id']);?>
      <b><font color="#000">Р РЋР ВµР в„–РЎвЂЎР В°РЎРѓ РЎС“ Р Р†Р В°РЎРѓ: <?php echo formatnum($maney2['count']); ?> Р СoР Р…Р ВµРЎвЂљ.</font></b>
    </td>
  </tr> 
</table> 
<TABLE align="center" width="980">
  <TR>
    <TD align="center" width="980"  valign=top>
      <DIV ID="inv"> 

      </DIV>
    </TD>
  </TR>
</TABLE>
<script>
loadPokes(1,0,0);
</script>