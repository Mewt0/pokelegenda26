<style type="text/css">
.subSend{       
  background: #c0c0c0;
  font-size:17px;
  font-weight:bold;
  border: 2px solid #505050;
  padding: 4px;
  height:32px;
}
.subSend:hover{
  background: #808080;
  font-size:17px;
  font-weight:bold;
  border: 2px solid #505050;
}
.pane {
	position: relative;
	margin: 4px; 
}
.pane .delete {
	position: absolute;
	top: 8px;
	right: 10px;
	cursor: pointer;
}
.pane .btOtv {
	position: absolute;
	top: 8px;
	right: 30px;
	cursor: pointer;
}
.pane .otvets{
	position: relative;
	  display: none;
}
.pane .otvets .hrOtv{
 background: #99958c;
}

#sends {
	width: 680px;
}
#sends h3 {
	background: #505050;		    
    background: -moz-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#505050), color-stop(50%,#808080), color-stop(100%,#505050));
    background: -webkit-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: -o-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: -ms-linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
    background: linear-gradient(top, #505050 0%, #808080 50%, #505050 100%);
	padding: 7px 15px;
	text-align: left;
	font: bold 120%/100% Arial, Helvetica, sans-serif;
	border: solid 1px #c4c4c4;
	border-bottom: none;
	cursor: pointer;
	color: #000;
}
#sends h3:hover {
		background: #808080;		    
    background: -moz-linear-gradient(top, #bbbbbb 0%, #505050 50%, #bbbbbb 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#bbbbbb), color-stop(50%,#505050), color-stop(100%,#bbbbbb));
    background: -webkit-linear-gradient(top, #bbbbbb 0%, #505050 50%, #bbbbbb 100%);
    background: -o-linear-gradient(top, #bbbbbb 0%, #505050 50%, #bbbbbb 100%);
    background: -ms-linear-gradient(top, #bbbbbb 0%, #505050 50%, #bbbbbb 100%);
    background: linear-gradient(top, #bbbbbb 0%, #505050 50%, #bbbbbb 100%);
}
#sends h3.active {
	background-position: right 5px;
}
#sends .pid {
	background: #bbbbbb;
	color: #000;
	font-size:14px;
	text-align: left;
  font-weight:bold;
	padding:  10px 15px 20px;
	border-left: solid 1px #c4c4c4;
	border-right: solid 1px #c4c4c4;
}
.butStr{
 background-color:#e9e7e7; 
 border:2px solid black;
 width:24px;
 height:24px;
}
.butStrYes{
 background-color:#000;
 color: #fff;
 border:2px solid #fff;
 width:24px;
 height:24px;
}
.butStr:hover{
	background-color: #c0c0c0;
  cursor: pointer;
}
INPUT:hover{
   border:0;
}
.butStrYes:hover{
  cursor: pointer;
}

.pid{
    display: none;
}
</style>
<script>
function upSends(id){
if($("#send-"+id+" .sendsStatus").html() == "Не прочитано"){
  $.ajax({type: "GET", url: "game.php", data: "postGo=sends&sendUp="+escape(id),success: 
        function(txt){
          if(txt == 'OK'){ $("#send-"+id+" .sendsStatus").html(translate_messages('ok_messages')); }else{ alert('Error');};
        }  
  });
 } 
}
function effectSlow(sub,id){
  $(sub+"-"+id).slideToggle("slow");
  $(sub+"-"+id).toggleClass("active");
  if($("#send-"+id+" .otvets").css('display') != 'none') $("#send-"+id+" .otvets").slideToggle("slow").toggleClass("active");
  if($("#send-"+id+" .sendsStatus").html() == "Не прочитано") upSends(id);
  return false;
}
function otvet(id){                           
  if($("#pid-"+id).css('display') == 'none'){
    $("#pid-"+id).slideToggle("slow").toggleClass("active");
    if($("#send-"+id+" .sendsStatus").html() == "Не прочитано") upSends(id);
  }else{
    $("#send-"+id+" .otvets").slideToggle("slow").toggleClass("active");
  }  
}
function autoload(st,txt){
  if(st == 1) $("#cert").html('<img src="/css/img/load.gif" alt="Загрузка" border="0"/>');
  else $("#cert").html(txt);
}
function sendGo(id){
 autoload(1,'-');
 $.post("game.php?postGo=sends",  
	  {                                
        act: "send",                    
        users: $("#send-"+id+" .otvets #sendPolucId").val(), 
        text:  $("#send-"+id+" .otvets #sendTexts").val(),
        subj:  $("#send-"+id+" .otvets #sendSubj").val() 
    }, otvet(id), autoload(2,translate_messages('go_mes')));
$("#send-"+id+" .otvets #sendTexts").val("");
return false;
}
function loadSends(strn){
$("#cert").html('<img src="/css/img/load.gif" alt="Загрузка" border="0"/>');
  if(strn == false) var s = 1; else var s = strn; 
  $.ajax({  
    type: "GET",  
    url: "game.php",          
    data: "postGo=sends&sendZap=true&sendZapStr="+escape(s),   
    success: function(txt){
      var tip=txt.substr(0,2);
      if (txt.length==1) {txt='';} else {txt=txt.substr(2,txt.length);}
      if(tip == 'OK'){
       $("#sends").html(''+txt+'');
       $("#cert").html('');
      }else{
       if(txt != 'NOT'){
        loadSends(txt);
        $("#cert").html('');
       }
       else{
       $("#sends").html(translate_messages('no_mes'));
       $("#cert").html('');
       }
      };
    }  

  });
}
function dels(id,strn){
  $.ajax({  
    type: "GET",  
    url: "game.php",
    data: "postGo=sends&sendDel="+escape(id),   
    success: function(txt){
      if(txt == 'OK'){
       $("#send-"+id).animate({ opacity: 'hide' }, "slow");
       $("#cert").html(translate_messages('del_mes'));
       setTimeout("loadSends("+strn+");",1500);
      }else{
       $("#cert").html(translate_messages('e_del_mes'));
      };
    }  

  }); 
}
loadSends(1); 
</script>
<div align="center">
<div style="color:#000"><b> Состояние: <span id='cert' style="color:gold;"></span>.</b>
<br>
</div>
<div id='sends'>
</div>
</div>