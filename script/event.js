function defPosition(event) { 
    var x = 0
    var y = 0;
    if (document.attachEvent != null) {
        x = window.event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
        y = window.event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    } else if (!document.attachEvent && document.addEventListener) {
        x = event.clientX + window.scrollX;
        y = event.clientY + window.scrollY;
    } else {
        //...
    }
 return {x:x, y:y};
}
function tip(event, txt){
   if (txt != 0){
      document.getElementById('divTip').style.left=defPosition(event).x+15;
      document.getElementById('divTip').style.top=defPosition(event).y+10;
      document.getElementById('divTip').innerHTML=txt;
      document.getElementById('divTip').style.visibility='visible';
    } 
  else 
      document.getElementById('divTip').style.visibility='hidden';
}

function insidePresents(tip, out){
  if(tip == 1){
   document.getElementById("insidePresentsDivOver").style.display='block';
   document.getElementById("insidePresents").style.display='block';
   if(!out) document.getElementById('insidePresents').innerHTML=document.getElementById('insidePresent').innerHTML;
   else document.getElementById('insidePresents').innerHTML=document.getElementById('insideAward').innerHTML;
  } else {
   $("#insidePresents").animate({opacity: 'hide'}, "slow");
   $("#insidePresentsDivOver").animate({opacity: 'hide'}, "slow");   
  } 
}
