function fixEvent(event) {
    var x = y = 0;
    if (document.attachEvent != null) {
      x = window.event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
      y = window.event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    } 
    else if (!document.attachEvent && document.addEventListener) {
      x = event.clientX + window.scrollX;
      y = event.clientY + window.scrollY;
    } 
    else { 
      //... 
    }
    return {x:x, y:y};
}
function convert_to_time(secs)
{
	secs = parseInt(secs);	
	hh = secs / 3600;	
	hh = parseInt(hh);	
	mmt = secs - (hh * 3600);	
	mm = mmt / 60;	
	mm = parseInt(mm);	
	ss = mmt - (mm * 60);	
		
	if (hh > 23)	
	{	
     dd = hh / 24;	  
	   dd = parseInt(dd);	
	   hh = hh - (dd * 24);	
	} else { dd = 0; }	
		
	if (ss < 10) { ss = "0"+ss; }	
	if (mm < 10) { mm = "0"+mm; }	
	if (hh < 10) { hh = "0"+hh; }	
	if (dd == 0) { return (hh+" ч. "+mm+" мин. "+ss+" сек. "); }	
	else {	
		if (dd > 1) { return (dd+" д. "+hh+" ч. "+mm+" мин. "+ss+" сек. "); }
		else { return (dd+" д. "+hh+" ч. "+mm+" мин. "+ss+" сек. "); }
	}	
}

function drdrop(event, text, countdown) {
  if (text) {
    document.getElementById('divDragan').style.left=fixEvent(event).x+(text.length>60?-100:0);
    document.getElementById('divDragan').style.top=fixEvent(event).y+15;
    document.getElementById('divDragan').innerHTML=text;
    document.getElementById('divDragan').style.visibility='visible';
  } 
  else 
    document.getElementById('divDragan').style.visibility='hidden'; 
}

function cc(countdown){
if (countdown < 0){ 	
		document.getElementById('toplez').innerHTML = "<br>Эффект окончен!";
	}else{	
		document.getElementById('toplez').innerHTML = convert_to_time(countdown);
	}
	countdowns = countdown-1;
  setTimeout('cc(countdowns);', 1000);
}  