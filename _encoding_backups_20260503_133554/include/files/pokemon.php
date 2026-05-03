<?php
require_once("include/files/pokemon.har.php");
if (!empty($_GET['stat']) && !empty($_GET['pok']) && !empty($_GET['val'])){
    require_once ("include/plus.php");
}
if(!empty($_POST)){
 if(users_conect('pve')>0  OR users_conect('pvp')>0) 
    die("<script>alert('Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С Р С‘Р В·РЎС“РЎвЂЎР В°РЎвЂљРЎРЉ Р В°РЎвЂљР В°Р С”Р С‘, Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘РЎвЂљР Вµ Р В±Р С•Р в„–.');location.href='game.php?go=pokemon';</script>"); 
    require_once ("include/smena.php");
}
if(!empty($_GET['pok']) && !empty($_GET['del'])){
    if (users_conect('pve')>0  OR users_conect('pvp')>0){
      die("<script>alert('Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С Р С•РЎвЂљР С—РЎС“РЎРѓРЎвЂљР С‘РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°, Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘РЎвЂљР Вµ Р В±Р С•Р в„–.');location.href='game.php?go=pokemon';</script>");        
    }else{                 
       $pokes = $_GET['pok'];
       $delt = $_GET['del'];
       $p = first('SELECT id,startone FROM pok_user WHERE users=%d AND active=1 AND startepoke=0 AND id=%d',$_SESSION['id'],$pokes);
         if(!$p)die("<script>location.href='game.php?go=pokemon';</script>");
         if($p['startone']>0)die("<script>alert('Р В­РЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р С•РЎвЂљР С—РЎС“РЎРѓРЎвЂљР С‘РЎвЂљРЎРЉ Р Р…Р ВµР В»РЎРЉР В·РЎРЏ.');location.href='game.php?go=pokemon';</script>"); 
        delete('pok_user','users='.(int)$_SESSION['id'].' AND  id='.(int)$p['id'].' AND active=1 AND startepoke=0');
        die("<script>location.href='game.php?go=pokemon';</script>");
    }
}
if (!empty($_GET['pok']) && !empty($_GET['start'])){ 
  if (users_conect('pve')>0  OR users_conect('pvp')>0){
   die("<script>alert('Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С РЎРѓР СР ВµР Р…Р С‘РЎвЂљРЎРЉ РЎРѓРЎвЂљР В°РЎвЂљР С•Р Р†Р С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°, Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘РЎвЂљР Вµ Р В±Р С•Р в„–.');location.href='game.php?go=pokemon';</script>");
  }                
    $pokes = $_GET['pok'];
    $start = $_GET['start'];
    $start1 = "0";
    $p = first('SELECT id FROM pok_user WHERE users=%d AND active=1 AND startepoke=0 AND id=%d',$_SESSION['id'],$pokes);
    if(!$p) die ("<script>location.href='game.php?go=pokemon';</script>"); 
      update('pok_user',array('startepoke'=>0),'users='.(int)$_SESSION['id'].' AND active=1 AND startepoke=1');
      update('pok_user',array('startepoke'=>1),'users='.(int)$_SESSION['id'].' AND id='.(int)$p['id'].' AND active=1 AND startepoke=0');
    die ("<script>location.href='game.php?go=pokemon';</script>");
}
if(!empty($_GET['nodress']) && !empty($_GET['pID'])){
  $pok = $_GET['pID'];                       
  if(users_conect('pve')>0 OR users_conect('pvp')>0)
    die("<script>alert('Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С РЎРѓР Р…РЎРЏРЎвЂљРЎРЉ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ РЎРѓ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°, Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘РЎвЂљР Вµ Р В±Р С•Р в„–.');location.href='game.php?go=pokemon';</script>");
    $p = first('SELECT id FROM pok_user WHERE id=%d AND users=%d AND active=1',$pok,$_SESSION['id']);
    if($p) $itemPokes = first('SELECT * FROM items_poke WHERE id_poke=%d',$p['id']);
    if(!$pokk OR !$itemPokes) die ("<script>location.href='game.php?go=pokemon';</script>");
    delete('items_poke','id_poke='.(int)$p['id'].' AND id_items='.(int)$itemPokes['id_items']);
    if($itemPokes['datetime'] == "not") plus_item(1,$itemPokes['id_items']);
    die ("<script>location.href='game.php?go=pokemon';</script>");
} 

$enrg = users_conect_dop('energi_opit');
if($enrg > 100) $enrg = 100; 
$myPokemonUp = select('SELECT id,lvl
                      FROM pok_user 
                      WHERE users=%d AND active=1 
                      ORDER BY startepoke DESC, basenum ASC',$_SESSION['id']);
foreach($myPokemonUp as $myPokemonUpRes){ 
   levels_go($myPokemonUpRes['id']);
   evolution_pokes($myPokemonUpRes['id']);
   stat_updates($myPokemonUpRes['id'],$myPokemonUpRes['lvl']);
}
$my_pokemon = select('SELECT *
                      FROM pok_user 
                      WHERE users=%d AND active=1 
                      ORDER BY startepoke DESC, basenum ASC',$_SESSION['id']);

?>
<SCRIPT language="JavaScript">
function info(id, noom) {
  document.getElementById('inf'+noom).style.display = "none";
  document.getElementById('stat'+noom).style.display = "none";
  document.getElementById('atc'+noom).style.display = "none";
  document.getElementById('baf'+noom).style.display = "none";
  document.getElementById(id).style.display = "block";

}
function delPoke(id){
 if(confirm('Р вЂ™РЎвЂ№ РЎС“Р Р†Р ВµРЎР‚Р ВµР Р…РЎвЂ№, РЎвЂЎРЎвЂљР С• РЎвЂ¦Р С•РЎвЂљР С‘РЎвЂљР Вµ Р С•РЎвЂљР С—РЎС“РЎРѓРЎвЂљР С‘РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•Р С–Р С• Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°?') == true){
  window.location.href='game.php?go=pokemon&pok='+id+'&del=1';
 }else{
  return false;
 }
}
</SCRIPT>
<center>
  <span class="nameEnergy">
    <b>Р В­Р Р…Р ВµРЎР‚Р С–Р С‘РЎРЏ</b>
  </span>
  <DIV align=center style="width:<?php echo $enrg;?>%;background:#7df9ff;height:10;font-size:9;color:Black;">
    <b><?php echo  $enrg;?>% </b>
  </DIV>
  <span class="nameEnergy">                                
    Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• Р С•РЎвЂЎР С”Р С•Р Р† РЎРЊР Р…Р ВµРЎР‚Р С–Р С‘Р С‘: <b class="EnergCount" style="color:#7df9ff;padding-left:3px;padding-right:3px;"> <?php echo users_conect_dop('energi_cool');?></b>
 </span> 
</center>
<br>
<hr color="#000">
<br>
<?php
foreach($my_pokemon as $varsPokes){ 
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
  $itemsMy = "<a href=game.php?go=pokemon&nodress=1&pID='.$idPokemon.'>".itemsPokemon($idPokemon)."</a>";
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
    if($myEv > 0 && $hpEv < 255) $hpPlus = "<input id='evHp".$idPokemon."' name='evHp".$idPokemon."' type='text' value='0' style='width:25px; height: 11px; font-size: 9px;' maxlength='3'> <a href='javascript:'  onclick=\"if(document.getElementById('evHp".$idPokemon."').value > 0 && document.getElementById('evHp".$idPokemon."').value < 256)location.href='game.php?go=pokemon&pok=".$idPokemon."&stat=hp&val='+document.getElementById('evHp".$idPokemon."').value;\">".$aEvPlus."</a>"; else $hpPlus = "";
    if($myEv > 0 && $atEv < 255) $atPlus = "<input id='evAt".$idPokemon."' name='evAt".$idPokemon."' type='text' value='0' style='width:25px; height: 11px; font-size: 9px;' maxlength='3'> <a href='javascript:'  onclick=\"if(document.getElementById('evAt".$idPokemon."').value > 0 && document.getElementById('evAt".$idPokemon."').value < 256)location.href='game.php?go=pokemon&pok=".$idPokemon."&stat=at&val='+document.getElementById('evAt".$idPokemon."').value;\">".$aEvPlus."</a>"; else $atPlus = "";
    if($myEv > 0 && $dfEv < 255) $dfPlus = "<input id='evDf".$idPokemon."' name='evDf".$idPokemon."' type='text' value='0' style='width:25px; height: 11px; font-size: 9px;' maxlength='3'> <a href='javascript:'  onclick=\"if(document.getElementById('evDf".$idPokemon."').value > 0 && document.getElementById('evDf".$idPokemon."').value < 256)location.href='game.php?go=pokemon&pok=".$idPokemon."&stat=df&val='+document.getElementById('evDf".$idPokemon."').value;\">".$aEvPlus."</a>"; else $dfPlus = "";
    if($myEv > 0 && $saEv < 255) $saPlus = "<input id='evSa".$idPokemon."' name='evSa".$idPokemon."' type='text' value='0' style='width:25px; height: 11px; font-size: 9px;' maxlength='3'> <a href='javascript:'  onclick=\"if(document.getElementById('evSa".$idPokemon."').value > 0 && document.getElementById('evSa".$idPokemon."').value < 256)location.href='game.php?go=pokemon&pok=".$idPokemon."&stat=sa&val='+document.getElementById('evSa".$idPokemon."').value;\">".$aEvPlus."</a>"; else $saPlus = "";
    if($myEv > 0 && $sdEv < 255) $sdPlus = "<input id='evSd".$idPokemon."' name='evSd".$idPokemon."' type='text' value='0' style='width:25px; height: 11px; font-size: 9px;' maxlength='3'> <a href='javascript:'  onclick=\"if(document.getElementById('evSd".$idPokemon."').value > 0 && document.getElementById('evSd".$idPokemon."').value < 256)location.href='game.php?go=pokemon&pok=".$idPokemon."&stat=sd&val='+document.getElementById('evSd".$idPokemon."').value;\">".$aEvPlus."</a>"; else $sdPlus = "";
    if($myEv > 0 && $spEv < 255) $spPlus = "<input id='evSp".$idPokemon."' name='evSp".$idPokemon."' type='text' value='0' style='width:25px; height: 11px; font-size: 9px;' maxlength='3'> <a href='javascript:'  onclick=\"if(document.getElementById('evSp".$idPokemon."').value > 0 && document.getElementById('evSp".$idPokemon."').value < 256)location.href='game.php?go=pokemon&pok=".$idPokemon."&stat=sp&val='+document.getElementById('evSp".$idPokemon."').value;\">".$aEvPlus."</a>"; else $spPlus = "";
    
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
    
    $attacIset = select('SELECT apw.atac_id, apw.atac_name, ap.atc_lvl FROM attac_poke ap inner join attac_power apw on ap.atac_id=apw.atac_id where ap.poke_base_id=%d AND %d>=ap.atc_lvl',$baseId,$lvlPoke);
    $attacTextReturn = "";
    foreach($attacIset as $attacIsetRes){
      $attacTextReturn .= '<option value="'.$attacIsetRes['atac_id'].'">'.$attacIsetRes['atac_name'].' - '.$attacIsetRes['atc_lvl'].'-lvl</option>';
    }
    
    $textDel = "<font color='gold'>Р РЋРЎвЂљР В°РЎР‚РЎвЂљР С•Р Р†РЎвЂ№Р в„– Р С—Р С•Р С”Р ВµР СР С•Р Р….</font>";
    if($pokeStart != 1) $textStart = "<a href='game.php?go=pokemon&pok=".$idPokemon."&start=1'>Р РЋР Т‘Р ВµР В»Р В°РЎвЂљРЎРЉ РЎРѓРЎвЂљР В°РЎР‚РЎвЂљР С•Р Р†РЎвЂ№Р С</a>"; else  $textStart = "";
    if($pokeStart != 1) $textDel = "<span style='cursor:pointer;' onclick=\"delPoke(".$idPokemon.")\">Р С›РЎвЂљР С—РЎС“РЎРѓРЎвЂљР С‘РЎвЂљРЎРЉ</a>";
  print '
  <table width="800">
  <tr>
   <td align="left" width="280">
    <table width="255">
      <tr>
        <td align="center">
            <b>
              <div class="'.($pokeStart==1?$classSt:$classNo).'"">
                <a href=javascript: onClick="win1=window.open(\'game.php?go=pokedex&id='.$baseId.'\',\'pokedex\',\'width=550,height=550,scrollbars=yes\');return true;"><img src=img/other/pokedex.png></a> 
                #'.$namePoke.' '.$lvlPoke.'-lvl 
              </div>
            </b>
        </td>
      </tr>
      <tr>
        <td align="center">
              <img src=\'pok/'.$tipPoke.'/'.$baseId.'.png\' style=width: 250px; height: 190px;">
        </td>
      </tr> 
      <tr>
        <td align="left">
              <DIV style="width:'.$hpProcent.'%;background:'.($hpProcent<20?'red':'#3caa3c').';height:12;font-size:9;color:Black;"><b>'.$hpPoke.'</b></DIV>
        </td>
      </tr>
      <tr>
        <td align="left">      
              <DIV align=center style="width:'.$expPolos.'%;background:#4169e1;height:5;font-size:9;color:Black;"><b>'.round($expPolos).'%</b></DIV>
        </td>
      </tr>
      <tr>
        <td align="left">       
              <DIV align=center style="width:'.$happyPoke.'%;background:#DAA520;height:10;font-size:9;color:Black;"><b>'.$happyPoke.'%</b></DIV>
        </td>
      </tr>
    </table>
   </td>
   <td style="overflow:hidden;">
        <div style="width: 474px; text-align:center; font:11px Tahoma; color: #fff;" class="pokeDivInf">
          <a href=javascript: onclick="info(\'inf'.$idPokemon.'\','.$idPokemon.')">Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ</a>   
			    <a href=javascript: onclick="info(\'stat'.$idPokemon.'\','.$idPokemon.')">Р РЋРЎвЂљР В°РЎвЂљРЎвЂ№</a>
          <a href=javascript: onclick="info(\'atc'.$idPokemon.'\','.$idPokemon.')">Р С’РЎвЂљР В°Р С”Р С‘</a>
          <a href=javascript: onclick="info(\'baf'.$idPokemon.'\','.$idPokemon.')">Р Р€РЎРѓР С‘Р В»Р ВµР Р…Р С‘РЎРЏ</a>
        </div>
        <div style="height:250; overflow:hidden;margin: 0 0 0 0;" >
          <div id="stat'.$idPokemon.'" class="pokeDiv" style="display:block;top:0;height:215px;">
            <center><b>Р РЋРЎвЂљР В°РЎвЂљРЎвЂ№:</b></center>
            <table cellspacing="0" width="80%">
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
                <td><b><span style="color:#000;">Р РЋР С—Р ВµРЎвЂ .Р вЂ”Р В°РЎвЂ°Р С‘РЎвЂљР В°:</span></b></td>
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
          <div id="inf'.$idPokemon.'" class="pokeDiv" style="top:-200;height:215px;">
            <center><b>Р ВР Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎРЏ:</b></center>
              <b><span style="color:#000;">Р СџР С•Р В»:             </span></b><i><b>'.$sexPokemon.'.</b></i><br>
				      <b><span style="color:#000;">Р ТђР В°РЎР‚Р В°Р С”РЎвЂљР ВµРЎР‚:        </span></b><i><b>'.$harPoke.'.</b></i><br>
				      <b><span style="color:#000;">Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ:      </span></b><i><b>'.$reproduction.'.</b></i><br>
              <b><span style="color:#000;">Р СџР С•Р в„–Р СР В°Р Р… РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р С•Р С: </span></span></b>'.color_group_users($varsPokes['usersone']).'<br>  
				      <b><span style="color:#000;">Р вЂќР В°РЎвЂљР В° Р С—Р С•Р С‘Р СР С”Р С‘:     </span></b><i><b>'.$varsPokes['datemay'].'.</b></i><br>
				      <b><span style="color:#000;">ID:              </span></b><i><b>'.$idPokemon.'.</b></i><br>
              '.sexPoke($idPokemon,$varsPokes['sex']).'   
          </div>
          <div id="atc'.$idPokemon.'" class="pokeDiv" style="top:-400;height:215px;">
            <center><b>Р вЂ™РЎвЂ№РЎС“РЎвЂЎР ВµР Р…Р Р…РЎвЂ№Р Вµ Р В°РЎвЂљР В°Р С”Р С‘:</b></center>
            <table cellspacing="0" width="80%"" align="center">
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
            <table cellspacing="0" width="80%">
              <tr>
                <td>
                  <form action="" method="POST">
                    <select size="1" name="smena1"> 
                      '.$attacTextReturn.'
                    </select>
                </td>
                <td> 
                    <input name="id_pok" type="hidden" value="'.$idPokemon.'">
                    <input type="submit" name="submit" value="Р ВР В·РЎС“РЎвЂЎР С‘РЎвЂљРЎРЉ"> 
                  </form> 
                </td>
                <td>
                  <form action="" method="POST">
                    <select size="1" name="smena2">
                       '.$attacTextReturn.'
                    </select>
                 </td>
                 <td> 
                    <input name="id_pok" type="hidden" value="'.$idPokemon.'">
                    <input type="submit" name="submit" value="Р ВР В·РЎС“РЎвЂЎР С‘РЎвЂљРЎРЉ"> 
                  </form> 
                </td>
              </tr>
              <tr>
                <td>
                  <form action="" method="POST">
                    <select size="1" name="smena3">
                       '.$attacTextReturn.'
                    </select>
                </td>
                <td>  
                    <input name="id_pok" type="hidden" value="'.$idPokemon.'">
                    <input type="submit" name="submit" value="Р ВР В·РЎС“РЎвЂЎР С‘РЎвЂљРЎРЉ"> 
                  </form> 
                </td>
                <td>
                  <form action="" method="POST">
                    <select size="1" name="smena4">
                       '.$attacTextReturn.'
                    </select>
                </td>
                <td> 
                    <input name="id_pok" type="hidden" value="'.$idPokemon.'">
                    <input type="submit" name="submit" value="Р ВР В·РЎС“РЎвЂЎР С‘РЎвЂљРЎРЉ"> 
                  </form> 
                </td>
              </tr>
            </table>
          </div>
          <div id="baf'.$idPokemon.'" class="pokeDiv" style="top:-600;height:215px;">
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
  ';
   

}  
?>
