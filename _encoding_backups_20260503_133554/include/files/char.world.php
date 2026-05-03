<?php
require_once ("include/config.room.php");
if(users_conect_dop('arest') >= time() && $myrow['buildmy'] != 2)
    update('users',array('buildmy'=>2, 'groups'=>7, 'police'=>0, 'moderation'=>0),'id='.(int)$_SESSION['id']);
if(!empty($_SESSION['QUEST_MY_ISSET'])) unset($_SESSION['QUEST_MY_ISSET']);
if($myrow['pvp'] == 1 || $myrow['pve'] == 1){
    $zBatl = first('SELECT id FROM battles WHERE id=%d',$myrow['battleid']);
    if(empty($zBatl['id'])){
        $idB = $myrow['battleid'];
        delete('battle_log','battle_id='.(int)$idB);
        delete('statpokemonbatle','battleid='.(int)$idB);
        delete('bttle_status','buttleid='.(int)$idB);
        delete('battle_dop','battleid='.(int)$idB);
        delete('bettle_attac','battleid='.(int)$idB);
        delete('attacers_battle','battleid='.(int)$idB);
        delete('pok_pve','users='.(int)$_SESSION['id']);
        update('users',array('pvp'=>0,'pve'=>0),'id='.(int)$_SESSION['id']);
    }
}
if($myrow['trade'] > 0){
    $zBatl = first('SELECT id FROM tradesusers WHERE id=%d',$myrow['trade']);
    if(empty($zBatl['id'])){
        delete('tradesusers','userone='.(int)$_SESSION['id']);
        delete('tradesusers','usertwo='.(int)$_SESSION['id']);
        delete('tradesobject','userid='.(int)$_SESSION['id']);
        delete('tradesobject','userto='.(int)$_SESSION['id']);
        update('users',array('trade'=>0),'id='.(int)$_SESSION['id']);
        $myrow['trade'] = false;
    }
}
if($myrow['trade'] > 0 && (empty($_GET['trade']) || empty($_GET['tradeid']))) die("<script>parent.loc('char&trade=true&tradeid=".$myrow['trade']."');</script>Р РЋР ВµР в„–РЎвЂЎР В°РЎРѓ Р вЂ™РЎвЂ№ Р Р…Р Вµ Р СР С•Р В¶Р С‘РЎвЂљР Вµ Р С—Р ВµРЎР‚Р ВµР Т‘Р Р†Р С‘Р С–Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р С—Р С• Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎРЏР С.");
if($myrow['pvp'] == 1) die("<script>parent.loc('fight_pvp');</script>Р РЋР ВµР в„–РЎвЂЎР В°РЎРѓ Р вЂ™РЎвЂ№ Р Р…Р Вµ Р СР С•Р В¶Р С‘РЎвЂљР Вµ Р С—Р ВµРЎР‚Р ВµР Т‘Р Р†Р С‘Р С–Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р С—Р С• Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎРЏР С.");
if($myrow['pve'] == 1) die("<script>parent.loc('fight_pve');</script>Р РЋР ВµР в„–РЎвЂЎР В°РЎРѓ Р вЂ™РЎвЂ№ Р Р…Р Вµ Р СР С•Р В¶Р С‘РЎвЂљР Вµ Р С—Р ВµРЎР‚Р ВµР Т‘Р Р†Р С‘Р С–Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р С—Р С• Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎРЏР С.");

if($myrow['buildmy'] == 40){

}else{
    if (!empty($_GET['newpok'])) require_once ("include/files/sparka.poke.php");
    if (!empty($_GET['trade']) && isset($_GET['tradeid'])) require_once ("include/files/trade.users.php");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
    <link REL="Stylesheet" HREF="css/room.css" TYPE="text/css">
</head>
<body>
<div class="infoError" id="infoError"></div>
<div class="error" id="error">
    <span id="mesError"></span>
    <div id="exit" class="exit" onclick="parent.mess_error('false','none');">[Р вЂ”Р В°Р С”РЎР‚РЎвЂ№РЎвЂљРЎРЉ]</div>
</div>
<?php
/** require_once ("include/razvedenie_pok.php");  Р Р…Р Вµ Р Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С• **/
$name = false;
$about = false;
$pers = false;
$move = false;
$quest_isset_const = false;
$kraft_isset_const = false;
$img_r = false;
$tipesLoc = false;
$filesLoc = "include/rooms/".$myrow['buildmy'].".php";
if(!(file_exists($filesLoc))) die("Р С›РЎв‚¬Р С‘Р В±Р С”Р В° Р С—РЎР‚Р С‘ Р С—Р ВµРЎР‚Р ВµРЎвЂ¦Р С•Р Т‘Р Вµ Р Р…Р В° Р В»Р С•Р С”Р В°РЎвЂ Р С‘РЎР‹, Р С—Р С•Р В¶Р В°Р В»РЎС“Р в„–РЎРѓРЎвЂљР В° РЎРѓР С•Р С•Р В±РЎвЂ°Р С‘РЎвЂљР Вµ Р В°Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂљР С•РЎР‚РЎС“.");
include($filesLoc);
if(!$kraft_isset_const && !$quest_isset_const){
    $locBase = first('SELECT tipe FROM build WHERE id=%d',$myrow['buildmy']);
    if($locBase['tipe'] == 1 && !$quest_isset_const && !$kraft_isset_const) $tipesLoc = '<sup style="color:#7fc7ff;">[Р вЂ™Р С•Р Т‘Р Р…Р В°РЎРЏ]</sup>';
}


/*
<img src="img/event2.png" style="position:fixed; z-index:1000; top:0px; right:0px;" width=135>
<img src="img/event1.png" style="position:fixed; z-index:1000; top:0px; left:0px;" width=135>
*/
?>
<script type='text/javascript' src='/script/jquery.js'></script>
<script type="text/javascript">
    var timeB = false;
    function check_battle(id){
        if(timeB) {clearTimeout(timeB); timeB = false;}   
        parent._usersonline.document.getElementById('battle_view').style.display = "none";
        $("#html_content_location").load('/game.php?go=map&gets=true', {view_battle: true, load_battle: id});
    }
    function close_battle(){
        if(timeB) {clearTimeout(timeB); timeB = false;}
        $("#html_content_location").html('');
        $("#content-location").fadeOut("slow");
    }
</script>
<div id="content-location" class="content-location">
    <div class="close_content_location" onclick="close_battle();" title="Р вЂ”Р В°Р С”РЎР‚РЎвЂ№РЎвЂљРЎРЉ Р С•Р С”Р Р…Р С•"></div>
    <div class="html_content_location" id="html_content_location" style="margin: 10px; height: 95%; overflow-x:hidden; overflow-y: auto;"></div>
</div>
<div class="loc">
    <div class = "nameLOC" align="center">
        <b><? echo $name.$tipesLoc; ?></b>
    </div>
    <table width=100%>
        <tr>
            <?php
            if(!$kraft_isset_const){
            ?>
            <td valign=top width=21% align="center">
                <div class = "about">  <?php if(!empty($img_r)) { ?> <?php echo $img_r; ?> <?php }?> </div>
            </td>
            <td valign=top>
                <?php
                }else{
                ?>
            <td valign=top colspan=2>
                <?php
                }
                ?>
                <div class="<?php echo ($quest_isset_const?"about2":"about");?>">
                    <font color=#000000><?php print $about; ?></font>
                </div>
            </td>
        </tr>
        <?php
        if($quest_isset_const != "1"){
        ?>
        <tr>
            <td colspan=2>
                <div class = "namePERS"   align=center >
                    <?php print $pers; ?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div class = "namehrefLOC" align=center >
                    <?php print $move; ?>
                </div>
            </td>
        </tr>
    </table>
    <?php
    }else{
        ?>
        </table>
        <div class = "namePersTo">
            <table width=100%>
                <tr>
                    <td valign=top width=21%>&nbsp;</td>
                    <td>
                        <? echo $pers; ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php
    }
    ?>
    </center>
</div>
</body>
</html>