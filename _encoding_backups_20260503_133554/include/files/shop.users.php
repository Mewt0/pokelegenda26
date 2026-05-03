<?php
//require_once __DIR__ . '/../function/function.items.php';



function minus_item($count, $item_id) {
    if(!$count || !$item_id) return false;

    $exist = first('SELECT id, count FROM items_users WHERE user_id=%d AND item_id=%d', $_SESSION['id'], $item_id);
    if($exist){
        $new_count = $exist['count'] - $count;
        if($new_count > 0){
            update('items_users', array('count' => $new_count), 'id='.(int)$exist['id']);
        } else {
            delete('items_users', 'id='.(int)$exist['id']);
        }
    }

    return true;
}



if($_SESSION['id'] == 3){
  echo "<center><b style='color:gold; font-size: 15px;'>Р С’Р В»Р СР В°Р В·Р Р…РЎвЂ№Р в„– Р СР В°Р С–Р В°Р В·Р С‘Р Р… Р Р†РЎР‚Р ВµР СР ВµР Р…Р Р…Р С• Р Р…Р Вµ РЎР‚Р В°Р В±Р С•РЎвЂљР В°Р ВµРЎвЂљ. Р СџРЎР‚Р С‘Р Р…Р С•РЎРѓР С‘Р С РЎРѓР Р†Р С•Р С‘ Р С‘Р В·Р Р†Р С‘Р Р…Р ВµР Р…Р С‘РЎРЏ Р В·Р В° Р Т‘Р С•РЎРѓРЎвЂљР В°Р Р†Р В»Р ВµР Р…Р Р…РЎвЂ№Р в„– Р Р…Р ВµРЎС“Р Т‘Р С•Р В±РЎРѓРЎвЂљР Р†Р В°.</b></center>";
}else{
$error_mess = false;
function no_href_alm(){
  die("<script>location.href='game.php?go=diamond_shop';</script>");
}
function log_diamand($a,$b,$c,$d){
 insert('log_alm',array('user_id'=>$a,'item_id'=>$b,'cena'=>$c,'date'=>$d));
}
if(coolseitems(2,$_SESSION['id']) > 0){ 
  $text_cool = '
      <font color="black" size="3" style="font-family: Georgia, Times New Roman, Times, fantasy ;">
        Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С• Р Р†Р В°РЎв‚¬Р С‘РЎвЂ¦ Р В°Р В»Р СР В°Р В·Р С•Р Р† РЎРѓР С•РЎРѓРЎвЂљР В°Р Р†Р В»РЎРЏР ВµРЎвЂљ: 
        <b>
          <i> '.formatnum(coolseitems(2,$_SESSION['id'])).' РЎв‚¬РЎвЂљ. </i>
        </b>   
      </font>'; 
}else{ 
  $text_cool = '
      <font color="black" size="3" style="font-family: Georgia, Times New Roman, Times, fantasy ;">
        <b>
          Р Р€ Р вЂ™Р В°РЎРѓ Р Р…Р ВµРЎвЂљ Р В°Р В»Р СР В°Р В·Р С•Р Р† РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р С—РЎР‚Р ВµР С•Р В±РЎР‚Р ВµРЎРѓРЎвЂљР С‘ РЎвЂљР С•Р Р†Р В°РЎР‚.
        </b>   
      </font>';
}
if (isset($_GET['ok'])) $error_mess = "<br><h4><font color=gold><b><center>Р вЂ™РЎвЂ№ РЎС“Р Т‘Р В°РЎвЂЎР Р…Р С• Р С—РЎР‚Р С‘Р С•Р В±РЎР‚Р ВµР В»Р С‘ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ</center></b></font></h4>";
if (isset($_GET['shop'])){
  include ("include/files/shop.php");
  include ("include/files/bottom.php");
  //include ("include/function/function.items.php"); 
 exit;
}

if (isset($_GET['id_lot']) && !empty($_GET['id_lot'])) {
  $id_lota = obr_chis($_GET['id_lot']);
  if (empty($id_lota)) no_href_alm();
  $proverka_lota = first('SELECT * FROM diamond_shop WHERE id_item=%d',$id_lota);
  if(empty($proverka_lota)) no_href_alm();
  if(provitems('2',$proverka_lota['cena_item'])){                  
    if($proverka_lota['cool_item']>0){              
        $cool_don_it = $proverka_lota['cool_item'] - 1;
        minus_item($proverka_lota['cena_item'],2);
        plus_item(1,$proverka_lota['id_tip_item']);
        log_diamand($_SESSION['id'],$proverka_lota['id_tip_item'],$proverka_lota['cena_item'],time());
        update('diamond_shop',array('cool_item'=>$cool_don_it),'id_item='.(int)$proverka_lota['id_item']);
        echo "<script>location.href='game.php?go=diamond_shop&ok';</script>";
        exit;
    }else{
        $error_mess='
        <br><h4><font color=gold><b><center>
        Р вЂ™ Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ Р Р† Р СР В°Р С–Р В°Р В·Р С‘Р Р…Р Вµ Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘Р В»РЎРѓРЎРЏ Р С’Р в„–РЎвЂљР ВµР С, Р С”Р С•РЎвЂљР С•РЎР‚РЎвЂ№Р в„– Р вЂ™РЎвЂ№ РЎвЂ¦Р С•РЎвЂљР С‘РЎвЂљР Вµ Р С”РЎС“Р С—Р С‘РЎвЂљРЎРЉ.<br> Р вЂќР С•Р В¶Р Т‘Р С‘РЎвЂљР ВµРЎРѓРЎРЉ РЎРѓР В»Р ВµР Т‘РЎС“РЎР‹РЎвЂ°Р ВµР в„– Р С—Р С•РЎРѓРЎвЂљР В°Р Р†Р С”Р С‘ РЎвЂљР С•Р Р†Р В°РЎР‚Р В°.
        </center></b></font></h4>';
    }
  }else{
    $error_mess='<br><h2><font color=gold><b><center>Р Р€ Р Р†Р В°РЎРѓ Р Р…Р ВµР Т‘Р С•РЎРѓРЎвЂљР В°РЎвЂљР С•РЎвЂЎР Р…Р С• Р В°Р В»Р СР В°Р В·Р С•Р Р† РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ Р С”РЎС“Р С—Р С‘РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•РЎвЂљ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ.</center></b></font></h1>';
  } 
}
$item_spisok = select('SELECT ds.id_item, ds.id_tip_item, ds.invize, ds.cena_item, ds.cool_item, i.name, i.tittle FROM diamond_shop ds Inner Join items i ON ds.id_tip_item=i.id ORDER BY tipes_categori,id_item ASC');
?>
<style>
.gran { 

    border-color: green; 
    border-style: solid; 
    padding: 5 5 5 5px; 
   }
.almButton{
  background-color: #000; 
  border-color: #bfbfbf; 
  border-style: solid;
  font-weight:bold;
  padding: 2px;
  color: #FFF;
}
   .gran_tw { 
    width: 80px;
    border-color: #B8860B; 
    border-style: groove;
    border-width: 7px;
    padding: 5 5 5 5px; 
   }
    .gran_tr { 
    width: 80%;
    height: 60px;
    border-color: #B8860B; 
    border-style: groove;
    border-width: 7px;
    padding: 5 5 5 5px; 
   }
    .gran_fo { 
    width: 50px;
    height: 50px;
    border-color: #2E8B57; 
    border-style: groove;
    border-width: 7px;
    padding: 5 10 5 10px; 
   }
    .gran_fi { 
    width: 80%;
    height: 50px;
    border-color: #2E8B57; 
    border-style: groove;
    border-width: 7px;
    padding: 2 2 2 2px; 
   }
   .shadowtext {
    text-shadow: Black 1px 1px 2px, #DAA520 0 0 1em; 
    color: gold; 
    font-size: 3em; 
    border-color: green; 
    border-style: solid; 
    padding: 5 5 5 5px;
   }
    .shadowtext_tw {
    text-shadow: Black 1px 1px 2px, #DAA520 0 0 1em; 
    color: gold; 
    font-size: 3em;  
    padding: 5 5 5 5px;
   }
</style>
<div align="center">
<table width=80% align=center>
  <tr>
    <td>
      <div class="shadowtext_tw">
        <center><b>Р С’Р В»Р СР В°Р В·Р Р…РЎвЂ№Р в„– Р СљР В°Р С–Р В°Р В·Р С‘Р Р…</b></center>
      </div>
    </td>
  </tr>
</table>
<table width=80%>
  <tr> 
    <td width=16%>
      <div class="gran_tw" align=center>
        <img src="img/alm1.png" width="45"> 
      </div>
    </td>
    <td align=left>
      <div class="gran_tr" align=center><br><br>
        <?php echo $text_cool;?>
      <br>
      </div>
    </td>
  </tr> 
</table>

<?php 
echo $error_mess;
?>
 <br>
  <h1>Р РЋР С—Р С‘РЎРѓР С•Р С” Р СџРЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†:</h1>
  <br>
<table width=80%>
  <tr>
    <td width=32>
      <font color=Snow><b>Р РЋР С—РЎР‚Р В°Р в„–РЎвЂљ</b></font>
    </td>
    <td width=350>
      <b><font color=Snow><center>Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ</center></font></b>
    </td>
    <td width=100 align="left">
      <font color=Snow><b>Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С•</b></font>
    </td>
    <td width=100 colspan=2 align="center">
      <font color=Snow><b>Р В¦Р ВµР Р…Р В° Р В·Р В° 1 РЎв‚¬РЎвЂљ.</b></font>
    </td>
  </tr>
</table>                                                                                              
<?php
foreach($item_spisok as $vivod_items_shop){
    $ci = $vivod_items_shop['cena_item'];
    $data2 = array(1=>array(1,21,31,41,51,61,71,81,91,101,121,131,141));
    if(in_array($ci,$data2[1])) $col_txt = "Р С’Р В»Р СР В°Р В·"; 
      else $col_txt = "Р С’Р В»Р СР В°Р В·Р С•Р Р†"; 
    if($ci>1 AND $ci<5) $col_txt = "Р С’Р В»Р СР В°Р В·Р В°";
    if($vivod_items_shop['invize'] == 0) $colse = $vivod_items_shop['cool_item']." РЎв‚¬РЎвЂљ."; else $colse = "--"; 
    if($vivod_items_shop['cool_item']>0) $preobr = "<input class=\"almButton\" type=\"submit\" name=\"submit\" value=\"Р СџРЎР‚Р С‘Р С•Р В±РЎР‚Р ВµРЎРѓРЎвЂљР С‘\" onclick=\"location.href='game.php?go=diamond_shop&id_lot=".$vivod_items_shop['id_item']."';\">";
      else $preobr = '<font color=gold>Р СћР С•Р Р†Р В°РЎР‚ Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘Р В»РЎРѓРЎРЏ</font>';
        echo "
            <table width='80%''>
            <tr >
       			<td width='32'><img src=img/items/".$vivod_items_shop['id_tip_item'].".png width='24'></td>
       			<td width='350'>
       			  <span><b><font color='gold'>".$vivod_items_shop['name']."</font></b></span>
       			  <br><b><font color='#afeeee'><i>".$vivod_items_shop['tittle']."</i></font></b>
       			</td>
       			<td width=100><font color='gold'><b>".$colse."</b></font></td>
       			<td width=100><font color='gold'><b>".$vivod_items_shop['cena_item']." ".$col_txt."</b></font></td>
       		  <td width=100><center>".$preobr."</center></td></tr>
       		  </table> <br>
             ";
}
print '</div>';
}
?>
<BR><center>
<table>
<tr>
<td>
<font color=black>
<b>1) Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎРЏ РЎРѓР В°Р в„–РЎвЂљР В°: league-of-pokemons.ru Р Р…Р Вµ Р Р…Р ВµРЎРѓР ВµРЎвЂљ Р С•РЎвЂљР Р†Р ВµРЎвЂљРЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…Р С•РЎРѓРЎвЂљР С‘ Р В·Р В° Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘РЎРЏ Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ, Р С•Р С—Р В»Р В°РЎвЂљР В° Р С—РЎР‚Р С•Р С‘Р В·Р Р†Р С•Р Т‘Р С‘РЎвЂљРЎРѓРЎРЏ Р Т‘Р С•Р В±РЎР‚Р С•Р Р†Р С•Р В»РЎРЉР Р…Р С• Р С‘ Р Р…Р Вµ РЎРЏР Р†Р В»РЎРЏР ВµРЎвЂљРЎРѓРЎРЏ Р Р…Р В°Р Р†РЎРЏР В·Р В°Р Р…Р Р…Р С•Р в„– Р С‘Р В»Р С‘ Р Р…Р ВµР С•Р В±РЎвЂ¦Р С•Р Т‘Р С‘Р СР С•Р в„– Р С—Р В»Р В°РЎвЂљР С•Р в„–. <br> 
</b></font>
</td>
</tr>
<tr>
<td>
<font color=black>
<b>2) Р В¦Р ВµР Р…Р В° Р В°Р В»Р СР В°Р В·Р С•Р Р† РЎС“Р С”Р В°Р В·Р В°Р Р…Р В° Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р Вµ Р С•Р С—Р В»Р В°РЎвЂљРЎвЂ№ Р С‘ Р СР С•Р В¶Р ВµРЎвЂљ Р В±РЎвЂ№РЎвЂљРЎРЉ Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р В° Р Р† Р С•Р Т‘Р Р…Р С•РЎРѓРЎвЂљР С•РЎР‚Р С•Р Р…Р Р…Р ВµР С Р С—Р С•РЎР‚РЎРЏР Т‘Р С”Р Вµ Р С—Р С• РЎР‚Р ВµРЎв‚¬Р ВµР Р…Р С‘РЎР‹ Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚Р В°.  </b> <br>
</font>
</td>
</tr>
<tr>
<td>
<font color=black>
<b>3) Р вЂ”Р В° Р Р…Р ВµР С”Р С•РЎР‚РЎР‚Р ВµР С”РЎвЂљР Р…РЎС“РЎР‹ Р С•Р С—Р В»Р В°РЎвЂљРЎС“, Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЉ Р Р…Р ВµРЎРѓР ВµРЎвЂљ РЎРѓР Р†Р С•РЎР‹ Р С•РЎвЂљР Р†Р ВµРЎвЂљРЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…Р С•РЎРѓРЎвЂљРЎРЉ Р С‘ Р СР С•Р В¶Р ВµРЎвЂљ Р В±РЎвЂ№РЎвЂљРЎРЉ Р Р…Р В°Р С”Р В°Р В·Р В°Р Р… Р Р†Р С—Р В»Р С•РЎвЂљРЎРЉ Р Т‘Р С• Р С—Р ВµРЎР‚Р СР В°Р Р…Р ВµР Р…РЎвЂљР Р…Р С•Р С–Р С• Р В±Р В°Р Р…Р В°. </b>
</font>
</td>
</tr>
</table>
<br>

<font color=black size=3>
Р С›Р С—Р В»Р В°РЎвЂљРЎС“ РЎвЂЎР ВµРЎР‚Р ВµР В· <b>webmoney</b> Р СР С•Р В¶Р Р…Р С• Р С—РЎР‚Р С•Р Р†Р ВµРЎРѓРЎвЂљР С‘ РЎвЂЎР ВµРЎР‚Р ВµР В· Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚Р В°: Tacos - РЎРѓР Р†РЎРЏР В·Р В°РЎвЂљРЎРѓРЎРЏ РЎРѓ Р Р…Р С‘Р С Р Р† Р В»РЎРѓ(Р Р† Р С‘Р С–РЎР‚Р Вµ, Р В»Р С‘Р В±Р С• Р Р…Р В° РЎвЂћР С•РЎР‚РЎС“Р СР Вµ). <br> Р В Р В°РЎРѓРЎвЂ Р ВµР Р…Р С”Р С‘ РЎвЂљР В°Р С”Р С•Р Р†РЎвЂ№: 10 РЎР‚РЎС“Р В±. = 1 Р В°Р В»Р СР В°Р В·, 4 Р С–РЎР‚Р С‘Р Р†Р Р…РЎвЂ№ = 1 Р В°Р В»Р СР В°Р В·.
</font></center>