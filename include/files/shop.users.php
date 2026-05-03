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
  echo "<center><b style='color:gold; font-size: 15px;'>Алмазный магазин временно не работает. Приносим свои извинения за доставленный неудобства.</b></center>";
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
        Количество ваших алмазов составляет: 
        <b>
          <i> '.formatnum(coolseitems(2,$_SESSION['id'])).' шт. </i>
        </b>   
      </font>'; 
}else{ 
  $text_cool = '
      <font color="black" size="3" style="font-family: Georgia, Times New Roman, Times, fantasy ;">
        <b>
          У Вас нет алмазов чтобы преобрести товар.
        </b>   
      </font>';
}
if (isset($_GET['ok'])) $error_mess = "<br><h4><font color=gold><b><center>Вы удачно приобрели предмет</center></b></font></h4>";
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
        В данный момент в магазине закончился Айтем, который Вы хотите купить.<br> Дождитесь следующей поставки товара.
        </center></b></font></h4>';
    }
  }else{
    $error_mess='<br><h2><font color=gold><b><center>У вас недостаточно алмазов чтобы купить этот предмет.</center></b></font></h1>';
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
        <center><b>Алмазный Магазин</b></center>
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
  <h1>Список Предметов:</h1>
  <br>
<table width=80%>
  <tr>
    <td width=32>
      <font color=Snow><b>Спрайт</b></font>
    </td>
    <td width=350>
      <b><font color=Snow><center>Описание</center></font></b>
    </td>
    <td width=100 align="left">
      <font color=Snow><b>Количество</b></font>
    </td>
    <td width=100 colspan=2 align="center">
      <font color=Snow><b>Цена за 1 шт.</b></font>
    </td>
  </tr>
</table>                                                                                              
<?php
foreach($item_spisok as $vivod_items_shop){
    $ci = $vivod_items_shop['cena_item'];
    $data2 = array(1=>array(1,21,31,41,51,61,71,81,91,101,121,131,141));
    if(in_array($ci,$data2[1])) $col_txt = "Алмаз"; 
      else $col_txt = "Алмазов"; 
    if($ci>1 AND $ci<5) $col_txt = "Алмаза";
    if($vivod_items_shop['invize'] == 0) $colse = $vivod_items_shop['cool_item']." шт."; else $colse = "--"; 
    if($vivod_items_shop['cool_item']>0) $preobr = "<input class=\"almButton\" type=\"submit\" name=\"submit\" value=\"Приобрести\" onclick=\"location.href='game.php?go=diamond_shop&id_lot=".$vivod_items_shop['id_item']."';\">";
      else $preobr = '<font color=gold>Товар закончился</font>';
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
<b>1) Администрация сайта: league-of-pokemons.ru не несет ответственности за действия пользователя, оплата производится добровольно и не является навязанной или необходимой платой. <br> 
</b></font>
</td>
</tr>
<tr>
<td>
<font color=black>
<b>2) Цена алмазов указана на странице оплаты и может быть изменена в одностороннем порядке по решению администратора.  </b> <br>
</font>
</td>
</tr>
<tr>
<td>
<font color=black>
<b>3) За некорректную оплату, пользователь несет свою ответственность и может быть наказан вплоть до перманентного бана. </b>
</font>
</td>
</tr>
</table>
<br>

<font color=black size=3>
Оплату через <b>webmoney</b> можно провести через Администратора: Tacos - связатся с ним в лс(в игре, либо на форуме). <br> Расценки таковы: 10 руб. = 1 алмаз, 4 гривны = 1 алмаз.
</font></center>