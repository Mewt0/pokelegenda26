<?php
if(isset($_SESSION['id']) && isset($_SESSION['login']))
  die('<script type=\'text/javascript\'> window.location.href=\'..\';</script>'); 
$pass_register = '15sR29Fgd8EdcD';
$reg_passed = false;
if (!isset($_SESSION['admin_reg']) && $reg_passed != false) {
  if(!$_SESSION['popitka_rig_pass'] && $_SESSION['popitka_rig_pass'] != 'timeOut') $_SESSION['popitka_rig_pass'] = 3;
  if(isset($_POST['passwordadm']) && $_POST['passwordadm'] != ''){
  if(isset($_POST['passwordadm']) && $_POST['passwordadm'] == $pass_register && $_SESSION['popitka_rig_pass'] > 0){
    $_SESSION['admin_reg'] = true;
    $_SESSION['popitka_rig_pass'] = 3;
    print '<script type=\'text/javascript\'>alert("Р Р€РЎРѓР С—Р ВµРЎв‚¬Р Р…Р В°РЎРЏ Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ!");hrefs();</script>';
    exit;
  }else{
    $_SESSION['popitka_rig_pass'] = $_SESSION['popitka_rig_pass'] - 1;
    $txt = 'Р СњР Вµ Р Р†Р ВµРЎР‚Р Р…РЎвЂ№Р в„– Р С—Р В°РЎР‚Р С•Р В»РЎРЉ, Р С—Р С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ Р ВµРЎвЂ°Р Вµ РЎР‚Р В°Р В·. Р С›РЎРѓРЎвЂљР В°Р В»Р С•РЎРѓРЎРЉ Р С—Р С•Р С—РЎвЂ№РЎвЂљР С•Р С”: '.$_SESSION['popitka_rig_pass'].' РЎв‚¬РЎвЂљ.';
    if($_SESSION['popitka_rig_pass'] <= 0) $_SESSION['popitka_rig_pass'] = 'timeOut';
    if($_SESSION['popitka_rig_pass'] == 'timeOut') $txt = 'Р Р€Р Р†РЎвЂ№, Р Р†Р В°РЎв‚¬Р С‘ Р С—Р С•Р С—РЎвЂ№РЎвЂљР С”Р С‘ Р С‘РЎРѓРЎвЂЎР ВµРЎР‚Р С—Р В°Р Р…РЎвЂ№! Р СџР С•Р С—РЎР‚Р С•Р В±РЎС“Р в„–РЎвЂљР Вµ РЎРѓР Р…Р С•Р Р†Р В° РЎвЂЎР ВµРЎР‚Р ВµР В· Р Р…Р ВµР С”Р С•РЎвЂљР С•РЎР‚Р С•Р Вµ Р Р†РЎР‚Р ВµР СРЎРЏ.';
    print '<p class="regAutError" align="center" valign="middle">
              <b>
                '.$txt.'
              </b>
           </p>';     
  }}

 print ' 
  <p class="regAutWindow" align="center" valign="middle">
    <b>
            Р В Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎРЏ Р В·Р В°РЎвЂ°Р С‘РЎвЂ°Р ВµР Р…Р В° Р С—Р В°РЎР‚Р С•Р В»Р ВµР С Р Р…Р В° Р Р†РЎР‚Р ВµР СРЎРЏ РЎвЂљР ВµРЎРѓРЎвЂљР С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘РЎРЏ! 
        <br>Р С™Р В°Р С” РЎвЂљР С•Р В»РЎРЉР С”Р С• РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂ№ Р С—Р С• РЎвЂљР ВµРЎРѓРЎвЂљР С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘РЎР‹ Р В±РЎС“Р Т‘РЎС“РЎвЂљ Р С•Р С”Р С•Р Р…РЎвЂЎР ВµР Р…РЎвЂ№, Р СРЎвЂ№ Р С•РЎвЂљР С”РЎР‚Р С•Р ВµР С РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎР‹ Р С‘ РЎРѓР С•Р С•Р В±РЎвЂ°Р С‘Р С Р вЂ™Р В°Р С Р С•Р В± РЎРЊРЎвЂљР С•Р С.
    </b>
  </p>
    <br>
    <center>
          <form action="" method="POST">
            <span style="font-weight:bold;color:#f7f21a;">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЉ:</span> <input type="password" name="passwordadm" value="" style="border:2px solid #000;font-weight:bold;font-size:15px">
            <input type="submit" value="OK" style="background:#000;color:gold;font-weight:bold;font-size:15px;">
          </form>
    </center>
  ';                                          
}else{
  if(!empty($_GET['severeg']) && $_GET['severeg'] == 'seve') require_once("include/files/registrationseve.php");
    else {

/*$string='=____====ssssss';
if (preg_match("/(-)\\1/",$string)) echo "yes"; else echo "no";
exit;
for($n=0;$n!=strlen($login)-2;$n++){
if($login[$n]==$login[$n+1] && $login[$n]==$login[$n+2]){
echo 'Р ВµРЎРѓРЎвЂљРЎРЉ Р С—Р С•Р Р†РЎвЂљР С•РЎР‚Р ВµР Р…Р С‘Р Вµ';
exit;
}}*/
;
?>
<style>
.errorMes{
  display:none;
  border:2px solid #000;
  font-weight:bold;
  color:#f754e1;
  padding:4px;
}
#regBatt{
  background: #ffcece;
  color:#000;
  font-weight:bold;
  font-size:13px; 
  border:2px solid #ff4040;
  padding:4px;
}
</style>
<script>
function prov(){
 var ret = false;
 if(document.getElementById("error_login").style.display == 'none' 
 && document.getElementById("error_password").style.display == 'none' 
 && document.getElementById("error_passwordTwo").style.display == 'none'
 && document.getElementById("error_email").style.display == 'none' 
 && document.getElementById("error_answer").style.display == 'none' 
 && document.regform.law.checked == true
 && document.regform.digits.value != '') {
    $("#regBatt").css({'border' : '2px solid #83c954',   'background': '#e8ffce'});
    ret = true;
 }
return ret;
}
function feedbackSubmit(){
   var userMail = document.regform.email.value;
   if (document.regform.login.value=="") {
      alert("Р вЂ”Р В°Р С—Р С•Р В»Р Р…Р С‘РЎвЂљР Вµ Р Р…Р С‘Р С” Р С—Р ВµРЎР‚РЎРѓР С•Р Р…Р В°Р В¶Р В°!");
      document.regform.login.focus();
      return false;
   }
   if (document.regform.login.value.length<3 || document.regform.login.value.length>16) {
      alert("Р вЂєР С•Р С–Р С‘Р Р… Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ 3-РЎвЂ¦ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ 16-РЎвЂљР С‘!");
      document.regform.login.focus();
      return false;
   }
   if (document.regform.password.value=="") {
      alert("Р СџР С•Р В»Р Вµ Р С—Р В°РЎР‚Р С•Р В»РЎРЏ Р Р…Р Вµ Р Т‘Р С•Р В»Р В¶Р Р…Р С• Р С•РЎРѓРЎвЂљР В°Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ Р С—РЎС“РЎРѓРЎвЂљРЎвЂ№Р СР С‘!");
      document.regform.password.focus();
      return false;
   }
   if (document.regform.password.value.length<6 || document.regform.password.value.length>16) {
      alert("Р СџР В°РЎР‚Р С•Р В»РЎРЉ Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ 6-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ 16-РЎвЂљР С‘!");
      document.regform.password.focus();
      return false;
   }
   if (document.regform.password.value != document.regform.passwordTwo.value) {
      alert("Р СџР В°РЎР‚Р С•Р В»Р С‘ Р Р…Р Вµ РЎРѓР С•Р Р†Р С—Р В°Р Т‘Р В°РЎР‹РЎвЂљ!");
      document.regform.password.focus();
      return false;
   }
   if (userMail =='') {
    alert("Р СџР С•Р В¶Р В°Р В»РЎС“Р в„–РЎРѓРЎвЂљР В° Р В·Р В°Р С—Р С•Р В»Р Р…Р С‘РЎвЂљР Вµ Р С—Р С•Р В»Р Вµ E-Mail");
    document.regform.email.focus();
    return false
	} 
  if ((userMail.indexOf("@") == -1) || (userMail.indexOf(".") == -1)) {
      alert("Р СњР ВµР С—РЎР‚Р В°Р Р†Р С‘Р В»РЎРЉР Р…Р С• Р В·Р В°Р С—Р С•Р В»Р Р…Р ВµР Р…Р С• Р С—Р С•Р В»Р Вµ e-mail!");
      document.regform.email.focus();
      return false;
  }  
	if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(userMail))) { 
    alert('Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р С—РЎР‚Р В°Р Р†Р С‘Р В»РЎРЉР Р…Р С•Р Вµ Р Р…Р В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р С—Р С•РЎвЂЎРЎвЂљР С•Р Р†Р С•Р С–Р С• РЎРЏРЎвЂ°Р С‘Р С”Р В°!');
    document.regform.email.focus();  
    return false  
	}  
  if (document.regform.law.checked==false){
      alert("Р вЂ™Р В°Р С Р Р…РЎС“Р В¶Р Р…Р С• Р С•Р В·Р Р…Р В°Р С”Р С•Р СР С‘РЎвЂљРЎРЉРЎРѓРЎРЏ РЎРѓ Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°Р СР С‘ Р С‘Р С–РЎР‚РЎвЂ№!");
      return false;
  }
  if (prov()==false){
      alert("Р вЂ”Р В°Р С—Р С•Р В»Р Р…Р С‘РЎвЂљР Вµ Р Р†РЎРѓР Вµ Р Р†Р В°Р В¶Р Р…РЎвЂ№Р Вµ РЎвЂћР С•РЎР‚Р СРЎвЂ№!");
      return false;
  }
  document.regform.submit(); 
}

function styleForm(id,txt,num){
  if(num == 1){
	  $("#reg"+id).css({'border' : '2px solid #83c954',   'background': '#e8ffce'});
    $("#error_"+id).html('').css({'display' : 'none'});
  }
  else if(num == 2){
    $("#reg"+id).css({'border' : '2px solid #ff2400',   'background': '#ffcece'});
    $("#error_"+id).html(txt).css({'display' : 'block'}); 
  }
  else if(num == 3){
    $("#reg"+id).css({'border' : '2px solid #ff2400',   'background': '#ffcece'});
    $("#error_"+id).html('').css({'display' : 'none'});
  }
}
function regProv(values){
  if(values == 'login' || values == 'email')
  {
    switch (values) {
          case 'email':
			      var val=document.regform.email.value;
			    break;
			    case 'login':
			      var val=document.regform.login.value;
			    break;
	   }
  $.ajax({  
    type: "GET",  
    url: "index.php",          
    data: "go=provReg&zap_"+values+"="+escape(val),   
    success: function(txt){
      var otv=txt.substr(0,2);
        if (txt.length==2) txt='';
          else txt=txt.substr(2,txt.length);
        if(otv == 'OK'){
          styleForm(values,'-',1);
        }else{
          if(txt != '') {
            styleForm(values,txt,2);
          }else{ 
            styleForm(values,'-',3);
          }
        }                      
    }  
  });}
  else if(values == 'password'){
    var val=document.regform.password.value;
    if(val = '') styleForm(values,'-',3);
    else if (document.regform.password.value.length<6 || document.regform.password.value.length>16){
      var txt = 'Р СџР В°РЎР‚Р С•Р В»РЎРЉ Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ 6-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ 16-РЎвЂљР С‘!';
      styleForm(values,txt,2);
      document.regform.password.focus();
    }
    else styleForm(values,'-',1);
    document.regform.passwordTwo.value = '';
  }                                   
 else if(values == 'passwordTwo'){
    var val=document.regform.passwordTwo.value;
    if(val = '') styleForm(values,'-',3);
    else if (document.regform.passwordTwo.value != document.regform.password.value){
      var txt = 'Р СџР В°РЎР‚Р С•Р В»Р С‘ Р Р…Р Вµ РЎРѓР С•Р Р†Р С—Р В°Р Т‘Р В°РЎР‹РЎвЂљ!';
      styleForm(values,txt,2);
      document.regform.passwordTwo.focus();
    }
    else styleForm(values,'-',1);
  }
else if(values == 'answer'){
   var val=document.regform.answer.value;
     if(val = '') styleForm(values,'-',3);
      else if (document.regform.answer.value.length<5 || document.regform.answer.value.length>50){
       var txt = 'Р РЋР ВµР С”РЎР‚Р ВµРЎвЂљР Р…РЎвЂ№Р в„– Р С•РЎвЂљР Р†Р ВµРЎвЂљ Р Т‘Р С•Р В»Р В¶Р ВµР Р… РЎРѓР С•Р Т‘Р ВµРЎР‚Р В¶Р В°РЎвЂљРЎРЉ Р Р…Р Вµ Р СР ВµР Р…Р ВµР Вµ 5-РЎвЂљР С‘ РЎРѓР С‘Р СР Р†Р С•Р В»Р С•Р Р† Р С‘ Р Р…Р Вµ Р В±Р С•Р В»Р ВµР Вµ 50-РЎвЂљР С‘!';
      styleForm(values,txt,2);
      document.regform.answer.focus();
    }
    else styleForm(values,'-',1);
}
else if(values == 'digits'){
  var val=document.regform.digits.value;
     if(val = '') styleForm(values,'-',3);
      else if(document.regform.digits.value.length<4) styleForm(values,'-',2);
        else styleForm(values,'-',1);
}
prov();
}
</script>
<div align=center>                       
<span style="color:gold;font-weight:bold;font-size:30px;"> Р В Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р В°РЎвЂ Р С‘РЎРЏ </span> <br><br>
<form name=regform action="index.php?go=reg&severeg=seve" method=POST onSubmit="feedbackSubmit()">
<TABLE  cellPadding=0 border=0>
  <TR>
    <TD align="left">
      <font color="#FF0000">*</font>
      <span style="color:gold;font-weight:bold;">Р вЂєР С•Р С–Р С‘Р Р…:</span>
    </TD>
    <TD align="left">
      <input id="reglogin" name="login" type="text" size="20" maxlength="16" onChange="regProv('login')">
    </TD>
   </TR>
   <TR>
    <TD align="left" colspan=2>
      <div id="error_login" class="errorMes"> </div>
    </TD>
  </TR>
  <TR>
    <TD align="left">
      <font color="#FF0000">*</font>
      <span style="color:gold;font-weight:bold;">Р СџР В°РЎР‚Р С•Р В»РЎРЉ:</span>
    </TD>
    <TD align="left">
      <input id="regpassword" type="password" name="password" size="20" maxlength="16" onChange="regProv('password')">
    </TD>
  </TR>
   <TR>
    <TD align="left" colspan=2>
      <div id="error_password" class="errorMes"> </div>
    </TD>
  </TR>
  <TR>
    <TD align="left">
      <font color="#FF0000">*</font>
      <span style="color:gold;font-weight:bold;">Р СџР В°РЎР‚Р С•Р В»РЎРЉ Р ВµРЎвЂ°Р Вµ РЎР‚Р В°Р В·:</span>
    </TD>
    <TD align="left">
      <input id="regpasswordTwo" type="password" name="passwordTwo" size="20" maxlength="16" onChange="regProv('passwordTwo')">
    </TD>
  </TR>
  <TR>
    <TD align="left" colspan=2>
      <div id="error_passwordTwo" class="errorMes"> </div>
    </TD>
  </TR>
  <TR>
    <TD align="left">
      <font color="#FF0000">*</font>
      <span style="color:gold;font-weight:bold;">E-Mail:</span>
    </TD>
    <TD align="left">
      <input id="regemail" name="email" type="text" size="20" onChange="regProv('email')">
    </TD>
  </TR>
  <TR>
    <TD align="left" colspan=2>
      <div id="error_email" class="errorMes"> </div>
    </TD>
  </TR>
  <TR>
    <TD align="left">
      <font color="Gold">*</font>
      <span style="color:gold;font-weight:bold;">Р ВР Р…РЎвЂћР С• Р С• РЎРѓР ВµР В±Р Вµ:</span>
    </TD>
    <TD align="left">
      <input id="reginfo" name="info" type="text" size="20" maxlength="100">
    </TD>
  </TR>
  <TR>
    <TD align="left" colspan=2>
      <div id="error_info" class="errorMes"> </div>
    </TD>
  </TR>
  <TR>
    <TD align="left">          
      <font color="#FF0000">*</font>
      <span style="color:gold;font-weight:bold;">Р РЋР ВµР С”РЎР‚Р ВµРЎвЂљР Р…РЎвЂ№Р в„– Р Р†Р С•Р С—РЎР‚Р С•РЎРѓ:</span>
    </TD>
    <TD style="PADDING-BOTTOM: 10px" align="left">
      <select name="question">
        <option value="1">Р СњР С•Р СР ВµРЎР‚ Р С—Р В°РЎРѓР С—Р С•РЎР‚РЎвЂљР В°</option>
        <option value="2">Р вЂєРЎР‹Р В±Р С‘Р СРЎвЂ№Р в„– Р Р…Р В°Р С—Р С‘РЎвЂљР С•Р С”</option>
        <option value="3">Р С™Р В»Р С‘РЎвЂЎР С”Р В° Р Т‘Р С•Р СР В°РЎв‚¬Р Р…Р ВµР С–Р С• Р В¶Р С‘Р Р†Р С•РЎвЂљР Р…Р С•Р С–Р С•</option>
		    <option value="4">Р вЂќР ВµР Р†Р С‘РЎвЂЎРЎРЉРЎРЏ РЎвЂћР В°Р СР С‘Р В»Р С‘РЎРЏ Р СР В°РЎвЂљР ВµРЎР‚Р С‘</option>
		    <option value="5">Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р В»РЎР‹Р В±Р С‘Р СР С•Р С–Р С• РЎвЂћР С‘Р В»РЎРЉР СР В°</option>
		    <option value="6">Р СњР В°Р В·Р Р†Р В°Р Р…Р С‘Р Вµ Р В»Р С‘Р В±Р С‘Р СР С•Р в„– Р С”Р Р…Р С‘Р С–Р С‘</option>
		    <option value="7">Р вЂєРЎР‹Р В±Р С‘Р СР В°РЎРЏ Р С”Р С•Р СР С—РЎРЉРЎР‹РЎвЂљР ВµРЎР‚Р Р…Р В°РЎРЏ Р С‘Р С–РЎР‚Р В°</option>
      </select>
    </TD>
  </TR>
  <TR>
    <TD align="left" colspan=2>
      <div id="error_question" class="errorMes"> </div>
    </TD>
  </TR>
  <TR>
    <TD style="PADDING-BOTTOM: 10px" align="left">
      <font color="#FF0000">*</font>
      <span style="color:gold;font-weight:bold;">Р РЋР ВµР С”РЎР‚Р ВµРЎвЂљР Р…РЎвЂ№Р в„– Р С•РЎвЂљР Р†Р ВµРЎвЂљ:</span>
    </TD>
    <TD style="PADDING-BOTTOM: 10px" align="left">
      <input id="reganswer" name="answer" size="30" maxlength="50" onChange="regProv('answer')">
    </TD>
  </TR>
  <TR>
    <TD align="left" colspan=2>
      <div id="error_answer" class="errorMes"> </div>
    </TD>
  </TR>
  <TR>
    <TD align="left">
        <font color="#FF0000">*</font>           
        <span style="color:gold;font-weight:bold;">Р вЂ™Р В°РЎв‚¬ Р С—Р С•Р В»:</span>
    </TD>
    <TD style="PADDING-BOTTOM: 10px" align="left">
      <input TYPE="radio" NAME="gender" value="Р СљРЎС“Р В¶РЎРѓР С”Р С•Р в„–" style="cursor:hand" ID=A1 checked>
        <LABEL FOR=A1><span style="color:#00ffff;"> Р СљРЎС“Р В¶РЎРѓР С”Р С•Р в„– </span></LABEL>
      <input TYPE="radio" NAME="gender" value="Р вЂ“Р ВµР Р…РЎРѓР С”Р С‘Р в„–" style="cursor:hand" ID=A2>
        <LABEL FOR=A2><span style="color:#f754e1;"> Р вЂ“Р ВµР Р…РЎРѓР С”Р С‘Р в„– </span></LABEL>
    </TD>
    <TD style="PADDING-BOTTOM: 10px" align="left">&nbsp;</TD>
  </TR>
  <TR>
    <TD align="left">
      <font color="Gold">*</font>          
      <span style="color:gold;font-weight:bold;"> Р вЂќР В°РЎвЂљР В° РЎР‚Р С•Р В¶Р Т‘Р ВµР Р…Р С‘РЎРЏ:</span>
    </TD>
    <TD style="PADDING-BOTTOM: 10px" align="left">
    <select size="1" name="day">
          <option value="1">01</option>
          <option value="2">02</option>
          <option value="3">03</option>
          <option value="4">04</option>
          <option value="5">05</option>
          <option value="6">06</option>
          <option value="7">07</option>
          <option value="8">08</option>
          <option value="9">09</option>
          <option value="10">10</option>
          <option value="11">11</option>
          <option value="12">12</option>
          <option value="13">13</option>
          <option value="14">14</option>
          <option value="15">15</option>
          <option value="16">16</option>
          <option value="17">17</option>
          <option value="18">18</option>
          <option value="19">19</option>
          <option value="20">20</option>
          <option value="21">21</option>
          <option value="22">22</option>
          <option value="23">23</option>
          <option value="24">24</option>
          <option value="25">25</option>
          <option value="26">26</option>
          <option value="27">27</option>
          <option value="28">28</option>
          <option value="29">29</option>
          <option value="30">30</option>
          <option value="31">31</option>
        </select>
        <select size="1" name="month">
          <option value="01">РЎРЏР Р…Р Р†Р В°РЎР‚РЎРЉ</option>
          <option value="02">РЎвЂћР ВµР Р†РЎР‚Р В°Р В»РЎРЉ</option>
          <option value="03">Р СР В°РЎР‚РЎвЂљ</option>
          <option value="04">Р В°Р С—РЎР‚Р ВµР В»РЎРЉ</option>
          <option value="05">Р СР В°Р в„–</option>
          <option value="06">Р С‘РЎР‹Р Р…РЎРЉ</option>
          <option value="07">Р С‘РЎР‹Р В»РЎРЉ</option>
          <option value="08">Р В°Р Р†Р С–РЎС“РЎРѓРЎвЂљ</option>
          <option value="09">РЎРѓР ВµР Р…РЎвЂљРЎРЏР В±РЎР‚РЎРЉ</option>
          <option value="10">Р С•Р С”РЎвЂљРЎРЏР В±РЎР‚РЎРЉ</option>
          <option value="11">Р Р…Р С•РЎРЏР В±РЎР‚РЎРЉ</option>
          <option value="12">Р Т‘Р ВµР С”Р В°Р В±РЎР‚РЎРЉ</option>
        </select>&nbsp;
        <select size="1" name="year">
          <option value="1960">1960</option>
          <option value="1961">1961</option>
          <option value="1962">1962</option>
          <option value="1963">1963</option>
          <option value="1964">1964</option>
          <option value="1965">1965</option>
          <option value="1966">1966</option>
          <option value="1967">1967</option>
          <option value="1968">1968</option>
          <option value="1969">1969</option>
          <option value="1970">1970</option>
          <option value="1971">1971</option>
          <option value="1972">1972</option>
          <option value="1973">1973</option>
          <option value="1974">1974</option>
          <option value="1975">1975</option>
          <option value="1976">1976</option>
          <option value="1977">1977</option>
          <option value="1978">1978</option>
          <option value="1979">1979</option>
          <option value="1980">1980</option>
          <option value="1981">1981</option>
          <option value="1982">1982</option>
          <option value="1983">1983</option>
          <option value="1984">1984</option>
          <option value="1985">1985</option>
          <option value="1986">1986</option>
          <option value="1987">1987</option>
          <option value="1988">1988</option>
          <option value="1989">1989</option>
          <option value="1990">1990</option>
          <option value="1991">1991</option>
          <option value="1992">1992</option>
          <option value="1993">1993</option>
          <option value="1994">1994</option>
          <option value="1995">1995</option>
          <option value="1996">1996</option>
          <option value="1997">1997</option>
          <option value="1998">1998</option>
          <option value="1999">1999</option>
          <option value="2000">2000</option>
          <option value="2001">2001</option>
          <option value="2002">2002</option>
          <option value="2003">2003</option>
          <option value="2004">2004</option>
          <option value="2005">2005</option>
          <option value="2006">2006</option>
          <option value="2007">2007</option>
          <option value="2008">2008</option>
          <option value="2009">2009</option>
          <option value="2010">2010</option>
          <option value="2011">2011</option>
          <option value="2012" selected>2012</option>  
        </select>
    </TD>
  </TR>
</TABLE>
<br> 
<TABLE width=515 cellPadding=0 border=0 >
  <TR>
    <TD width="567"  align=center>       
      <br>
        <INPUT id="regcheckbox" TYPE="checkbox" ID=A4 NAME="law" style="cursor:hand" onChange="regProv('checkbox')">
        <font color="#FF0000">*</font>
          <span style="color: gold;">
            Р Р‡ Р С•Р В·Р Р…Р В°Р С”Р С•Р СР С‘Р В»РЎРѓРЎРЏ РЎРѓ <a href="/index.php?go=rule">Р С—РЎР‚Р В°Р Р†Р С‘Р В»Р В°Р СР С‘</a> Р С‘Р С–РЎР‚РЎвЂ№ League Of Pokemons 
          </span>
      <br>
      <br>
          <?php 
              $code = ''.mt_rand(1000,9999);
              $md5code = md5($code);
              echo "<img src=index.php?go=reg&code=$code><br>";
          ?>
          <font color="#FF0000">*</font>
            <span style="color: gold;">Р вЂ™Р Р†Р ВµР Т‘Р С‘РЎвЂљР Вµ Р С”Р С•Р Т‘ РЎРѓ Р С”Р В°РЎР‚РЎвЂљР С‘Р Р…Р С”Р С‘:</span>
        <input id="regdigits" type="text" name="digits" size=10 maxlength=40 onChange="regProv('digits')"> 
        <input type="hidden" name="check" class=inup size="20" value="<?php echo $md5code ?>">
    </TD>
  </TR>
  <TR align=center>
    <TD width="72">          
      <INPUT id="regBatt" name="regsubmit" type="button" value="Р вЂ”Р В°РЎР‚Р ВµР С–Р С‘РЎРѓРЎвЂљРЎР‚Р С‘РЎР‚Р С•Р Р†Р В°РЎвЂљРЎРЉРЎРѓРЎРЏ" onMouseOver="regProv('default')" onClick="feedbackSubmit()">
    </TD>
  </TR>
    
</TABLE>
</FORM>
</div>
<?php
}}
?>
