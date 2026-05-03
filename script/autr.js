function hideAutError(num)
  {
    if(num == 1){
      document.getElementById('autMes').innerHTML = '<b>Подождите, идет обработка данных...</b>';
      document.getElementById('autMes').style.display = 'block';
      document.getElementById('autorizeDiv').style.display = 'none';
    }else{
      document.getElementById('autMes').innerHTML = '';     
      document.getElementById('autMes').style.display = 'none';
      document.getElementById('autorizeDiv').style.display = 'block';
    }    
  }             
function enterText(ms)
{      
  document.getElementById('autherror').innerHTML = ms;
  document.getElementById('autherror').style.display = 'block';
  hideAutError(2);  
}

function provPostAut(){
  var login = $("input[name='LOGIN']").val();
  var  pass = $("input[name='PASSWORD']").val();
  if(login == '') str = 'noFormLogin';
   else if(pass == '') str = 'noFormPassw';
    else if(login.length<3) str = 'erFormLogin';
     else if(pass.length<6) str = 'erFormPassw';
      else str = 'yes'; 
 return str;     
}

function autGo() {
	hideAutError(1);
  if(provPostAut() == 'yes'){  
    document.getElementById('formAut').submit();
    return true;
  }else{
    enterText(translate_messages(provPostAut()));
    return false;
  } 
}
