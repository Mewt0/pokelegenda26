<?
if($_GET['tip'] != "edit" && $_POST) {    
      if (!empty($_POST['author']))  $author = $_POST['author']; else $author = $_SESSION['login']; 
      if (!empty($_POST['news']))    $news = $_POST['news']; else $news = false;
      if (!empty($_POST['link']))    $link = $_POST['link']; else $link = false;
      if (!empty($_POST['subject'])) $subject = $_POST['subject']; else $subject = false; 
      if (!empty($_POST['opis']))    $opis = $_POST['opis']; else $opis = false;
  if (empty($author) OR empty($news) OR empty($subject)) {
    die("<script>alert('Вы ввели не всю информацию, вернитесь назад и заполните все поля!'); location.href='game.php?go=admingo&do=news';</script>"); 
  } 
    insert('news',array(
       'subject'=>$subject,
       'news'=>$news,
       'date'=>MYSQL_NOW,
       'author'=>$author,
       'link'=>$link,
       'opis'=>$opis));
  die("<script>alert('Новость добавлена!'); location.href='game.php?go=admingo&do=news';</script>"); 
}

$knoki_news ='
<input style="background: brown;" title="Красный"  value="Кр" onclick="simpletagfont()" class="codeNapoff" type="button">
<input style="background: #CD950C;" title="Жирный"  value="<B>" onclick="simpletagb()" class="codeNapoff"  type="button">
<input style="background: #CD950C;" title="Курсивый"  value="<I>" onclick="simpletagi()" class="codeNapoff"  type="button">
';

if(isset($_GET['dil_id'])){ 
  $id = $_GET['dil_id'];
  $result_del_news = delete('news','id='.(int)$id);
  if($result_del_news == true) die("<script>alert('Новость удалена!'); location.href='game.php?go=admingo&do=news';</script>");
    else die("<script>alert('Произошла ошибка!'); location.href='game.php?go=admingo&do=news';</script>"); 
}
if(!empty($_GET['id_mess']) AND !empty($_GET['tip'])) {
  $id_mess = $_GET['id_mess'];
  $action = $_GET['tip'];
if($action == "viewedit"){
  $row = first('SELECT * FROM news WHERE id=%d',$id_mess);
  if(empty($row)) die("<script>location.href='game.php?go=admingo&do=news';</script>");
?>
   <table align='center' bgcolor="#363636" style="border-radius:10;" >
    <tr><td colspan=2>
    <form method='post' name='form1' action='game.php?go=admingo&do=news&id_mess=<? echo $id_mess; ?>&tip=edit'>		
    </td></tr>
    <tr><td>
    <font face='Arial, Helvetica, sans-serif'><div id=txt2><b>Тема(оглавление):</b></div> 
		<input type='text' name='subject' size='30' value='<?php echo $row['subject']; ?>'> 
    </td><td>
    <div id=txt2><b>Автор:</b></div> 
    <input type='text' name='author' value='<?php echo $row['author']; ?>' id='localSearchInput'><br></td></tr>
    <tr><td>
    <?php echo "$knoki_news<br>";?>
    <div id=txt2><b>Текст:</b></div>
    <textarea name='news' rows=8 cols=75><? echo $row['news']; ?></textarea><br></td>
    <td><div id=txt2><b>Ссылка:</b></div>
    <input type='text' name='link' size='30' value = '<?php echo $row['link']; ?>'></font><br><br>
    <div id=txt2><b>Описание ссылки:</b></div><input type='text' name='opis' size='30' value = '<?php echo $row['opis']; ?>'></td></tr>
    <tr><td align='center' colspan=2> <br>
    <input type='submit' name='Submit' value='Изменить'>
    </form><input type='submit'  value='Отмена' onclick="location.href='game.php?go=admingo&do=news';"><br>
    <br></td></tr></table>
<?php
} 
else if($action == "edit"){
  $subject = $_POST['subject'];
  $news    = $_POST['news'];
  $author  = $_POST['author'];
  $link    = $_POST['link'];
  $opis    = $_POST['opis'];
  $id_msg  = $_GET['id_mess'];
  if(!$subject OR !$news OR !$author OR !$id_msg) die("<script>alert('Чегото не хватает...');location.href='game.php?go=admingo&do=news';</script>");
    update('news',array('subject'=>$subject, 'news'=>$news, 'author'=>$author, 'link'=>$link, 'opis'=>$opis),'id='.(int)$id_msg);
  die("<script>alert('Новость изменена');location.href='game.php?go=admingo&do=news';</script>"); 
} 
}else{                      
print "    
    <table align='center' bgcolor=\"#363636\" style=\"border-radius:10;\" >
    <tr><td colspan=2>
    <form method='post' name='form1' id='form1' action=''>
		</td></tr>
    <tr><td>
    <font face='Arial, Helvetica, sans-serif'><div id=txt2><b>Тема(оглавление):</b></div> 
		<input type='text' name='subject' size='30' value='Re..'> 
    </td><td>
    <div id=txt2><b>Автор:</b></div> 
    <input type='text' name='author' value='".$login."' id='localSearchInput'><br></td></tr>
    <tr><td>".$knoki_news."<br>
    <div id=txt2><b>Текст:</b></div>
    <textarea name='news' rows=8 cols=75></textarea><br></td>
    <td><div id=txt2><b>Ссылка:</b></div>
    <input type='text' name='link' size='30' ></font><br><br>
    <div id=txt2><b>Описание ссылки:</b></div><input type='text' name='opis' size='30' value='Подробнее...' onclick=\"if(this.value=='Подробнее...')this.value='';\" onblur=\"if(this.value=='')this.value='Подробнее...';\"></td></tr>
    <tr><td align='center' colspan=2> <br>
    <input type='submit' name='Submit' value='Добавить'>
    <input type='reset' name='Submit2' value='Сброс'>
    </form><br><br></td></tr></table> 
"; 
include('itemsinpage.class2.php');
$totalpages = first('SELECT COUNT(*) as Total FROM news');
$itemsinpage = new Itemsinpage($totalpages['Total']);
$news = select('SELECT * FROM news ORDER BY id desc LIMIT %d,%d',$itemsinpage->get('Start'),$itemsinpage->get('Limit'));
print '<table align=center width=80%>';
foreach($news as $row){
?>
  <tr style='cursor:pointer' onmouseover='this.bgColor="#B8860B"' onmouseout='this.bgColor=""'><td >
  <div class="gran_tr" align=center>
  <font face="Arial, Helvetica, sans-serif" size="2">
  Число: <b><font color="<? echo $bb_news_date_fn; ?> "> <? echo $row['date']; ?></font> </b>
  Автрор: <b><font color="<? echo $bb_news_author_fn; ?>"> <? echo $row['author']; ?></font> </b><br>
  Тема: <b><font color="<? echo $bb_news_subject_fn; ?>"><? echo $row['subject']; ?></font></b><br>
  <a href=game.php?go=admingo&do=news&dil_id=<? echo $row['id'] ?>>Удалить</a> | <a href=game.php?go=admingo&do=news&id_mess=<? echo $row['id'] ?>&tip=viewedit>Редактировать</a></font>
  </div>
  </tr></td> 
<?php 
} print '</table>'; 
$data = $itemsinpage->SmartyArr();
?>
<table>
	<tr>
		<td>Страница
		<?php for($i=0,$n=sizeof($data['Count']);$i<$n;$i++): ?>
			<?php if($data['Count'][$i][1]!=$_GET['page']): ?>
			<a href="game.php?go=admingo&do=news<?php print $dat_ctrok; ?>&page=<?php print $data['Count'][$i][1];?>"><?php print $data['Count'][$i][0];?></a>
			<?php else: ?>
			<?php print $data['Count'][$i][0];?>
			<?php endif; ?>
		<?php endfor; ?>
		</td>
	</tr>
	
</table>
<?php  
}
?>