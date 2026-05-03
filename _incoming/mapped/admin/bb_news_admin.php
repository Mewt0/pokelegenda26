<?php
if($_GET['tip'] != "edit" && $_POST) {    
      if (!empty($_POST['author']))  $author = $_POST['author']; else $author = $_SESSION['login']; 
      if (!empty($_POST['news']))    $news = $_POST['news']; else $news = false;
      if (!empty($_POST['link']))    $link = $_POST['link']; else $link = false;
      if (!empty($_POST['subject'])) $subject = $_POST['subject']; else $subject = false; 
      if (!empty($_POST['opis']))    $opis = $_POST['opis']; else $opis = false;
  if (empty($author) OR empty($news) OR empty($subject)) {
    die("<script>alert('Р вЂ™РЎвЂ№ Р Р†Р Р†Р ВµР В»Р С‘ Р Р…Р Вµ Р Р†РЎРѓРЎР‹ Р С‘Р Р…РЎвЂћР С•РЎР‚Р СР В°РЎвЂ Р С‘РЎР‹, Р Р†Р ВµРЎР‚Р Р…Р С‘РЎвЂљР ВµРЎРѓРЎРЉ Р Р…Р В°Р В·Р В°Р Т‘ Р С‘ Р В·Р В°Р С—Р С•Р В»Р Р…Р С‘РЎвЂљР Вµ Р Р†РЎРѓР Вµ Р С—Р С•Р В»РЎРЏ!'); location.href='game.php?go=admingo&do=news';</script>"); 
  } 
    insert('news',array(
       'subject'=>$subject,
       'news'=>$news,
       'date'=>MYSQL_NOW,
       'author'=>$author,
       'link'=>$link,
       'opis'=>$opis));
  die("<script>alert('Р СњР С•Р Р†Р С•РЎРѓРЎвЂљРЎРЉ Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В°!'); location.href='game.php?go=admingo&do=news';</script>"); 
}

$knoki_news ='
<input style="background: brown;" title="Р С™РЎР‚Р В°РЎРѓР Р…РЎвЂ№Р в„–"  value="Р С™РЎР‚" onclick="simpletagfont()" class="codeNapoff" type="button">
<input style="background: #CD950C;" title="Р вЂ“Р С‘РЎР‚Р Р…РЎвЂ№Р в„–"  value="<B>" onclick="simpletagb()" class="codeNapoff"  type="button">
<input style="background: #CD950C;" title="Р С™РЎС“РЎР‚РЎРѓР С‘Р Р†РЎвЂ№Р в„–"  value="<I>" onclick="simpletagi()" class="codeNapoff"  type="button">
';

if(isset($_GET['dil_id'])){ 
  $id = $_GET['dil_id'];
  $result_del_news = delete('news','id='.(int)$id);
  if($result_del_news == true) die("<script>alert('Р СњР С•Р Р†Р С•РЎРѓРЎвЂљРЎРЉ РЎС“Р Т‘Р В°Р В»Р ВµР Р…Р В°!'); location.href='game.php?go=admingo&do=news';</script>");
    else die("<script>alert('Р СџРЎР‚Р С•Р С‘Р В·Р С•РЎв‚¬Р В»Р В° Р С•РЎв‚¬Р С‘Р В±Р С”Р В°!'); location.href='game.php?go=admingo&do=news';</script>"); 
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
    <form method='post' name='form1' action='game.php?go=admingo&do=news&id_mess=<?php echo $id_mess; ?>&tip=edit'>		
    </td></tr>
    <tr><td>
    <font face='Arial, Helvetica, sans-serif'><div id=txt2><b>Р СћР ВµР СР В°(Р С•Р С–Р В»Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ):</b></div> 
		<input type='text' name='subject' size='30' value='<?php echo $row['subject']; ?>'> 
    </td><td>
    <div id=txt2><b>Р С’Р Р†РЎвЂљР С•РЎР‚:</b></div> 
    <input type='text' name='author' value='<?php echo $row['author']; ?>' id='localSearchInput'><br></td></tr>
    <tr><td>
    <?php echo "$knoki_news<br>";?>
    <div id=txt2><b>Р СћР ВµР С”РЎРѓРЎвЂљ:</b></div>
    <textarea name='news' rows=8 cols=75><?php echo $row['news']; ?></textarea><br></td>
    <td><div id=txt2><b>Р РЋРЎРѓРЎвЂ№Р В»Р С”Р В°:</b></div>
    <input type='text' name='link' size='30' value = '<?php echo $row['link']; ?>'></font><br><br>
    <div id=txt2><b>Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р С‘:</b></div><input type='text' name='opis' size='30' value = '<?php echo $row['opis']; ?>'></td></tr>
    <tr><td align='center' colspan=2> <br>
    <input type='submit' name='Submit' value='Р ВР В·Р СР ВµР Р…Р С‘РЎвЂљРЎРЉ'>
    </form><input type='submit'  value='Р С›РЎвЂљР СР ВµР Р…Р В°' onclick="location.href='game.php?go=admingo&do=news';"><br>
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
  if(!$subject OR !$news OR !$author OR !$id_msg) die("<script>alert('Р В§Р ВµР С–Р С•РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ...');location.href='game.php?go=admingo&do=news';</script>");
    update('news',array('subject'=>$subject, 'news'=>$news, 'author'=>$author, 'link'=>$link, 'opis'=>$opis),'id='.(int)$id_msg);
  die("<script>alert('Р СњР С•Р Р†Р С•РЎРѓРЎвЂљРЎРЉ Р С‘Р В·Р СР ВµР Р…Р ВµР Р…Р В°');location.href='game.php?go=admingo&do=news';</script>"); 
} 
}else{                      
print "    
    <table align='center' bgcolor=\"#363636\" style=\"border-radius:10;\" >
    <tr><td colspan=2>
    <form method='post' name='form1' id='form1' action=''>
		</td></tr>
    <tr><td>
    <font face='Arial, Helvetica, sans-serif'><div id=txt2><b>Р СћР ВµР СР В°(Р С•Р С–Р В»Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ):</b></div> 
		<input type='text' name='subject' size='30' value='Re..'> 
    </td><td>
    <div id=txt2><b>Р С’Р Р†РЎвЂљР С•РЎР‚:</b></div> 
    <input type='text' name='author' value='".$login."' id='localSearchInput'><br></td></tr>
    <tr><td>".$knoki_news."<br>
    <div id=txt2><b>Р СћР ВµР С”РЎРѓРЎвЂљ:</b></div>
    <textarea name='news' rows=8 cols=75></textarea><br></td>
    <td><div id=txt2><b>Р РЋРЎРѓРЎвЂ№Р В»Р С”Р В°:</b></div>
    <input type='text' name='link' size='30' ></font><br><br>
    <div id=txt2><b>Р С›Р С—Р С‘РЎРѓР В°Р Р…Р С‘Р Вµ РЎРѓРЎРѓРЎвЂ№Р В»Р С”Р С‘:</b></div><input type='text' name='opis' size='30' value='Р СџР С•Р Т‘РЎР‚Р С•Р В±Р Р…Р ВµР Вµ...' onclick=\"if(this.value=='Р СџР С•Р Т‘РЎР‚Р С•Р В±Р Р…Р ВµР Вµ...')this.value='';\" onblur=\"if(this.value=='')this.value='Р СџР С•Р Т‘РЎР‚Р С•Р В±Р Р…Р ВµР Вµ...';\"></td></tr>
    <tr><td align='center' colspan=2> <br>
    <input type='submit' name='Submit' value='Р вЂќР С•Р В±Р В°Р Р†Р С‘РЎвЂљРЎРЉ'>
    <input type='reset' name='Submit2' value='Р РЋР В±РЎР‚Р С•РЎРѓ'>
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
  Р В§Р С‘РЎРѓР В»Р С•: <b><font color="<?php echo $bb_news_date_fn; ?> "> <?php echo $row['date']; ?></font> </b>
  Р С’Р Р†РЎвЂљРЎР‚Р С•РЎР‚: <b><font color="<?php echo $bb_news_author_fn; ?>"> <?php echo $row['author']; ?></font> </b><br>
  Р СћР ВµР СР В°: <b><font color="<?php echo $bb_news_subject_fn; ?>"><?php echo $row['subject']; ?></font></b><br>
  <a href=game.php?go=admingo&do=news&dil_id=<?php echo $row['id'] ?>>Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ</a> | <a href=game.php?go=admingo&do=news&id_mess=<?php echo $row['id'] ?>&tip=viewedit>Р В Р ВµР Т‘Р В°Р С”РЎвЂљР С‘РЎР‚Р С•Р Р†Р В°РЎвЂљРЎРЉ</a></font>
  </div>
  </tr></td> 
<?php 
} print '</table>'; 
$data = $itemsinpage->SmartyArr();
?>
<table>
	<tr>
		<td>Р РЋРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В°
		<?php for($i=0,$n=count($data['Count']);$i<$n;$i++): ?>
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