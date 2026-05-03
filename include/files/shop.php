<style>
.gran { 

    border-color: green; 
    border-style: solid; 
    padding: 5 5 5 5px; 
   }
   .gran_tw { 
    width: 80px;
    border-color: #B8860B; 
    border-style: groove;
    border-width: 7px;
    padding: 5 5 5 5px; 
   }
    .gran_tr { 
    width: 94%;
    height: 80px;
    color: black;
    border-color: #B8860B; 
    border-style: groove;
    border-width: 7px;
    padding: 5 5 5 5px; 
   }
   .gran_tr2 { 
    height: 80px;
    color: black;
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
<center>
<?php
if (isset($_GET['ru']) OR isset($_GET['ua']) OR isset($_GET['kz']) OR isset($_GET['by']) OR isset($_GET['am'])) {
if(isset($_GET['ru'])) {
echo "
<table width=80% align=center>
<tr>
<td><center>
<div class=\"shadowtext_tw\">
<center><b>Приобретение Алмазов - Россия</b></center>
</div>
</td>
</tr>
</table>

<table width=80%>
<tr> 
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr\" align=center >
<b>2 Алмаза</b>  <br>  
Отправить смс на номер: <b>7201</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font><br> <br>
<b>Стоимость: 30 руб.</b>
</div>
</td>
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr\" align=center >
<b>5 Алмазов</b>  <br>  
Отправить смс на номер: <b>7202</b> <br> <br> 
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font> <br> <br>
<b>Стоимость: 62 руб.</b>
</div>
</td>
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr\" align=center >
<b>8 Алмазов</b>  <br> 
Отправить смс на номер: <b>3352</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font>  <br> <br>
<b>Стоимость: 90 руб.</b>
</div>
</td>
</tr>
<tr> 
<td colspan=3 align=center> <br>
<a href=game.php?go=diamond_shop&shop><b><< Назад </b></a>
</td> 
</tr> 
</table>";
}
elseif(isset($_GET['ua'])) {
echo "
<table width=80% align=center>
<tr>
<td><center>
<div class=\"shadowtext_tw\">
<center><b>Приобретение Алмазов - Украина</b></center>
</div>
</td>
</tr>
</table>

<table width=80%>
<tr> 
<td style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>5 Алмаза</b>  <br>  
Отправить смс на номер: <b>2855</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font><br> <br>
<b>Стоимость: 23 uah.</b>
</div>
</td>
<td style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>12 Алмазов</b>  <br>  
Отправить смс на номер: <b>3855</b> <br> <br> 
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font> <br> <br>
<b>Стоимость: 50 uah.</b>
</div>
</tr>
<tr> 
<td colspan=3 align=center> <br>
<a href=game.php?go=diamond_shop&shop><b><< Назад </b></a>
</td> 
</tr> 
</table>";
}
elseif(isset($_GET['kz'])) {
echo "
<table width=80% align=center>
<tr>
<td><center>
<div class=\"shadowtext_tw\">
<center><b>Приобретение Алмазов - Казахстан</b></center>
</div>
</td>
</tr>
</table>

<table width=80%>
<tr> 
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>4 Алмазов</b>  <br>  
Отправить смс на номер: <b>7132</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font><br> <br>
<b>Стоимость: 265 kzt.</b>
</div>
</td>
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>7 Алмазов</b>  <br>  
Отправить смс на номер: <b>7122</b> <br> <br> 
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font> <br> <br>
<b>Стоимость: 480 kzt.</b>

</div>
</td>
<tr></tr> 
<td colspan=3 align=center> <br>
<a href=game.php?go=diamond_shop&shop><b><< Назад </b></a>
</td> 
</tr>
</table>";
}

elseif(isset($_GET['by'])) {
echo "
<table width=80% align=center>
<tr>
<td><center>
<div class=\"shadowtext_tw\">
<center><b>Приобретение Алмазов - Белоруссия</b></center>
</div>
</td>
</tr>
</table>

<table width=80%>
<tr> 
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>4 Алмаз</b>  <br>  
Отправить смс на номер: <b>3336</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font><br> <br>
<b>Стоимость: 15900 byr</b>
</div>
</td>
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>7 Алмазов</b>  <br> 
Отправить смс на номер: <b>3339</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font>  <br> <br>
<b>Стоимость: 26900 byr.</b>
</div>
</td>
</tr>
<tr> 
<td colspan=3 align=center> <br>
<a href=game.php?go=diamond_shop&shop><b><< Назад </b></a>
</td> 
</tr> 
</table>";}

elseif(isset($_GET['am'])) {
echo "
<table width=80% align=center>
<tr>
<td><center>
<div class=\"shadowtext_tw\">
<center><b>Приобретение Алмазов - Армения</b></center>
</div>
</td>
</tr>
</table>

<table width=80%>
<tr> 
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>2 Алмазов</b>  <br>  
Отправить смс на номер: <b>5009</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font><br> <br>
<b>Стоимость: 1250 AMD.</b>
</div>
</td>
<td width=20% style='cursor:pointer' onmouseover='this.bgColor=\"#B8860B\"' onmouseout='this.bgColor=\"\"'>
<div class=\"gran_tr2\" align=center >
<b>3 Алмазов</b>  <br> 
Отправить смс на номер: <b>7122</b> <br>  <br>
<font size = 2>С текстом: <b> 79174+$_SESSION[id]  </b></font>  <br> <br>
<b>Стоимость: 1666 AMD.</b>
</div>
</td>
</tr>
<tr> 
<td colspan=3 align=center> <br>
<a href=game.php?go=diamond_shop&shop><b><< Назад </b></a>
</td> 
</tr> 
</table>";}

echo "<BR>
<center><table>
<tr>
<td>
<font color=black>
<b>1) Администрация сайта: Pokelegenda.ru не несет ответственности за действия пользователя, оплата производится добровольно и не является навязанной или необходимой платой. <br> 
</b></font>
</td>
</tr>
<tr>
<td>
<font color=black>
<b>2) Цена алмазов указана на странице оплаты и может быть изменена в одностороннем порядке по решению администратора.   <br>
</b></font>
</td>
</tr>
<tr>
<td>
<font color=black>
<b>3) За некорректную оплату, пользователь несет свою ответственность и может быть наказан вплоть до перманентного бана.</b>
</font>
</td>
</tr>
</table>
<br>
<font color=black size=3>
Оплату через <b>webmoney</b> можно провести через Адмнистратора: Makasimka - связатся с ним в лс(в игре, либо на форуме). <br> Расценки таковы: 10 руб. = 1 алмаз, 4 гривны = 1 алмаз.

</font></center>";
} else {


?>
<table width=80% align=center>
<tr>
<td><center>
<div class="shadowtext_tw">
<center><b>Приобретение Алмазов</b></center>
</div>
</td>
</tr>
</table>
<table width=80%>
<tr> 
<td width=16% style='cursor:pointer' onmouseover='this.bgColor="#B8860B"' onmouseout='this.bgColor=""'>
<div class="gran_tr" align=center onclick="location.href='game.php?go=diamond_shop&shop&ru';">
<br>
<b>Россия</b>  <br>
<img src=img/2_ru.png >
</div>
</td>
<td width=16% style='cursor:pointer' onmouseover='this.bgColor="#B8860B"' onmouseout='this.bgColor=""'>
<div class="gran_tr" align=center onclick="location.href='game.php?go=diamond_shop&shop&ua';">
<br>
<b>Украина</b>  <br>
<img src=img/2_ua.png >
</div>
</td>
<td width=16% style='cursor:pointer' onmouseover='this.bgColor="#B8860B"' onmouseout='this.bgColor=""'>
<div class="gran_tr" align=center onclick="location.href='game.php?go=diamond_shop&shop&kz';">
<br>
 <b>Казахстан</b>  <br>
 <img src=img/2_kz.png >
</div>
</td>
</tr>


<tr>
<td width=16% style='cursor:pointer' onmouseover='this.bgColor="#B8860B"' onmouseout='this.bgColor=""'>
<div class="gran_tr" align=center onclick="location.href='game.php?go=diamond_shop&shop&by';">
<br>
 <b>Белоруссия</b>  <br>
 <img src=img/2_by.png >
</div>
</td>

<td width=16% style='cursor:pointer' onmouseover='this.bgColor="#B8860B"' onmouseout='this.bgColor=""'>
<div class="gran_tr" align=center onclick="location.href='game.php?go=diamond_shop&shop&am';">
<br>
 <b>Армения</b>  <br>
 <img src=img/2_am.png >
</div>
</td>
</tr>



<tr> 
<td colspan=3 align=center> <br>
<a  href=game.php?go=diamond_shop><b><< Назад </b></a>
</td> 
</tr> 
</table>
<BR><center>
<table>
<tr>
<td>
<font color=black>
<b>1) Администрация сайта: Pokelegenda.ru не несет ответственности за действия пользователя, оплата производится добровольно и не является навязанной или необходимой платой. <br> 
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
Оплату через <b>webmoney</b> можно провести через Адмнистратора: Makasimka - связатся с ним в лс(в игре, либо на форуме). <br> Расценки таковы: 10 руб. = 1 алмаз, 4 гривны = 1 алмаз.
</font></center>
<?php }?>