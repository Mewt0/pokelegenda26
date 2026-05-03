<?
$quest_isset_const = 1;
$kraft_isset_const = 1;

if(!empty($_POST['items'])){ 
 $items = obr_chis($_POST['items']);
  if($items <= 0 OR !$items){   
    $_SESSION['mess_kraft'] = "Ошибка!";
    die ("<script>window.location.href='/game.php?go=char&quest_npc=3&do=3';</script>");  
  }
 include ("kraft_1_obrab.php");
}

$name  = "Изготовление предметов";
$about = "
<div style=\"width: 100%; height:100%; overflow: auto;\" align=\"left\"> 
 ".(!empty($_SESSION['mess_kraft'])?'<b style="color:green;"><center>'.$_SESSION['mess_kraft'].'</center></b>':'')."
  <table width='100%' border='1'>
   <tr>       			
    <td width='65px' align='center'>
     <span style='font-weight:bold;color:black;'>Спрайт</span>
    </td>
    <td width='250px' align='center'>
     <span style='font-weight:bold;color:black;'>Название</span>
    </td>
    <td align='center'>
     <span style='font-weight:bold;color:black;'>Требуемые предметы и их кол - во</span>
    </td>
    <td align='center'>
     <span style='font-weight:bold;color:black;'>---</span>
    </td>
   </tr>
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/40.png' width='24' alt='Громовой камень' title='Громовой камень'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Громовой камень. <br> Шанс: <i style='color:#ffdb58;'>Средний</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Растворитель: <b style='color:#551A8B;'>х1</b>.<br>
       Маленький осколок Громового камня: <b style='color:#551A8B;'>x5</b>.<br>
       Средний осколок Громового камня: <b style='color:#551A8B;'>x3</b>.<br>
       Огромный осколок Громового камня: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='1'>
         <input type=\"submit\" value=\"Изготовить\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/41.png' width='24' alt='Огненный камень' title='Огненный камень'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Огненный камень. <br> Шанс: <i style='color:#ffdb58;'>Средний</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Растворитель: <b style='color:#551A8B;'>х1</b>.<br>
       Маленький осколок Огненного камня: <b style='color:#551A8B;'>x5</b>.<br>
       Средний осколок Огненного камня: <b style='color:#551A8B;'>x3</b>.<br>
       Огромный осколок Огненного камня: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='2'>
         <input type=\"submit\" value=\"Изготовить\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/42.png' width='24' alt='Водный камень' title='Водный камень'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Водный камень. <br> Шанс: <i style='color:#ffdb58;'>Средний</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Растворитель: <b style='color:#551A8B;'>х1</b>.<br>
       Маленький осколок Водного камня: <b style='color:#551A8B;'>x5</b>.<br>
       Средний осколок Водного камня: <b style='color:#551A8B;'>x3</b>.<br>
       Огромный осколок Водного камня: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='3'>
         <input type=\"submit\" value=\"Изготовить\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/43.png' width='24' alt='Лиственный камень' title='Лиственный камень'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Лиственный камень. <br> Шанс: <i style='color:#ffdb58;'>Средний</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Растворитель: <b style='color:#551A8B;'>х1</b>.<br>
       Маленький осколок Лиственного камня: <b style='color:#551A8B;'>x5</b>.<br>
       Средний осколок Лиственного камня: <b style='color:#551A8B;'>x3</b>.<br>
       Огромный осколок Лиственного камня: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='4'>
         <input type=\"submit\" value=\"Изготовить\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
   
   <tr>
    <td width='65px' align='center'> 
      <img src='img/items/44.png' width='24' alt='Лунный камень' title='Лунный камень'>
    </td>
    <td width='250px'>
     <span style='font-weight:bold;color:black;'> 
        Лунный камень. <br> Шанс: <i style='color:#ffdb58;'>Средний</i>.
     </span>
    </td>
    <td>
     <span style='font-weight:bold;color:black;'> 
       Растворитель: <b style='color:#551A8B;'>х1</b>.<br>
       Маленький осколок Лунного камня: <b style='color:#551A8B;'>x5</b>.<br>
       Средний осколок Лунного камня: <b style='color:#551A8B;'>x3</b>.<br>
       Огромный осколок Лунного камня: <b style='color:#551A8B;'>x1</b>.<br>
     </span>
    </td>
    <td align='center'>
      <br>
      <form action='' method='POST'>
         <input type=\"hidden\" name=\"items\" value='5'>
         <input type=\"submit\" value=\"Изготовить\" width=\"15\" height=\"15\" name=\"submit\"/ style=\"font-size:13px;font-weight:bold;color:#fff;border: 4px double #fff;background: #000;\">
      </form>
    </td>
   </tr>
  </table>
</div>";
$pers = "
    <a href='game.php?go=char&quest_npc=3&do=1'>Назад</a> 
    <a href='game.php?go=char'>Уйти</a>";
if(!empty($_SESSION['mess_kraft'])) unset($_SESSION['mess_kraft']);
?>