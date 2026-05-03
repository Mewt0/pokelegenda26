<?php
  $st_pokes = first('SELECT names,tips,basenum,lvl FROM pok_user WHERE users=%d AND active=1 AND startepoke=1',$_SESSION['id']);
  $ava = users_conect('avatars');
  if($ava < 10) $ava = '00'.$ava; elseif($ava < 100 && $ava > 9) $ava = '0'.$ava; else $ava = $ava;
?>
<style>
div.events{
  background: #727272;
  background: -moz-linear-gradient(top,  #727272 1%, #bcbcbc 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,#727272), color-stop(100%,#bcbcbc));
  background: -webkit-linear-gradient(top,  #727272 1%,#bcbcbc 100%);
  background: -o-linear-gradient(top,  #727272 1%,#bcbcbc 100%);
  background: -ms-linear-gradient(top,  #727272 1%,#bcbcbc 100%);
  background: linear-gradient(to bottom,  #727272 1%,#bcbcbc 100%);
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#727272', endColorstr='#bcbcbc',GradientType=0 );
  position: absolute;
  top: 2px;
  left: -189px;
  width: 800px;
  height: 450px;
  border: 2px solid #000;
  z-index: 650;
  -moz-border-radiuss: 10px;
  -webkit-border-radius: 10px;
  border-radiuss: 10px;
  text-align:center;
  -moz-box-shadow: 0 0 30px #fff;
  -webkit-box-shadow: 0 0 30px #fff; 
  box-shadow: 0 0 30px #fff; 
}
#events .spans{
 position: relative;
 color: #ffa500;
 font-size: 40px;
 font-family: Geneva, 'Comic Sans', cursive ;
 text-shadow: #000 1px 2px 3px;  
                            
}
#events .eventstar{
  position: absolute;
  left: 325px;
  top: 47px;
}
#events .textEventstar{
  position: absolute;
  font-size: 50px;
  left: 369px;
  top: 87px;
}
#events .days{
  position: absolute;
  left: 200px;
  top: 92px;
}
#events .eventitem{
  position: absolute;
  left: 250px;
  top: 245px;
}
#events .dayEvent{
 color: #f984e5;
 text-align:left;
 font-size: 40px;
 position: relative;
 text-shadow: #000 1px 0px, #000 1px 1px, #000 0px 1px, #000 -1px 1px, #000 -1px 0px, #000 -1px -1px, #000 0px -1px, #000 1px -1px, #000 0 0 3px, #000 0 0 3px, #000 0 0 3px, #000 0 0 3px, #000 0 0 3px, #000 0 0 3px, #000 0 0 3px, #000 0 0 3px;
}
#events .exit{
 position: absolute;
 top: 5px;
 right: 5px;
 color: #000;
 cursor: pointer;
 z-index: 651;
}
#events .exit:hover {
 text-decoration:underline;
}
</style>
<h2>
Приветствуем Вас, тренер:    <?php print $_SESSION['login'];?>! 
</h2>
<table width=100% style="position: relative;">
 <tr>                                                             
  <td width=220 valign=top align=center>
	  <img alt='Аватар: <? echo $_SESSION['login'];?>' src='img/ava/<? echo $ava;?>.png' class = 'avatarUsers' width='215' height='410'>
  </td>
  <td valign=top style='font-size:14px;position: relative;'>
    
   <?php if(!empty($_SESSION['prizeUsers']) && $_SESSION['prizeUsers']['dat'] > 0) { 
            $day = ($_SESSION['prizeUsers']['dat']<10?'0'.$_SESSION['prizeUsers']['dat']:$_SESSION['prizeUsers']['dat'])
   ?> 
    <div class="events" id="events">
     <div id="exit" class="exit" onclick="window.document.getElementById('events').style.display='none';">[Закрыть]</div>
      <span class="spans">Ежедневный бонус ^_^</span>
     <br>
      <div align="left" class="dayEvent">
       <span class="days">День:</span> 
        <div class="eventstar"><img src="css/img/eventstar.png"></div>
        <span class="textEventstar"><?=$day?></span>
       
      </div>
      <br>
      <div class="eventitem">
        <span class="spans" style="font-size:25px;">Сегодня Вы получаете: </span> <br>
        <?php
          print '<table align="left" style="position: absolute; left:50px;"><tr>';
          $col = $_SESSION['prizeUsers']['cool'];
          $ite = $_SESSION['prizeUsers']['item']; 
          if($ite > 0){
            echo '<td>
                   <span style="font-size:15px;font-weight:bold;color:#000;border: 2px solid #000; background: #808080; padding-right: 2px;padding-left: 2px;padding-bottom:2px; padding-top:10px;">
                    <img src="/img/items/'.$ite.'.png" width="24" /></span>
                  </td>';
          }  
            echo ' <td align="left"><span style="font-size:15px;font-weight:bold;color:#000;"> '.$col.'</span></td></tr>';
          print '</table>';
        ?>
      </div> 
    </div>
   <?php unset($_SESSION['prizeUsers']); } ?> 
    
    <br>&nbsp;
        <table width=350>
          <?php
            if($st_pokes){
          ?>
          <tr>
            <td colspan='3' style='text-align:center; font-size:15px; color: #000; font-weight:bold;'>
              ВАШ СТАРТОВЫЙ ПОКЕМОН:
            </td>
          </tr>
          <tr>
            <td colspan='3' style='text-align:center; font-size:15px; color: gold; font-weight:bold;'>
              <?php print '#'.$st_pokes['names'].', '.$st_pokes['lvl'].' - lvl.'; ?>
            </td>
          </tr>
          <tr>
            <td style='font-size:12px; font-weight:bold; text-align:center;'> 
              <?php
                echo "<img src='pok/".$st_pokes['tips']."/".$st_pokes['basenum'].".png' class='startPokeImg'>";
              ?>
            </td>
            <td style='font-size:12px; font-weight:bold; text-align:center;'> 
            </td>
          </tr>
          <?php
            }else{
              print '<tr><td align="center"><center><b style="font-size:18px;color:#000;text-align:center">У вас нет стартового покемона.</b></center></td></tr>';
            }
          ?>
        </table>
    </td>
 </tr>
</table>
