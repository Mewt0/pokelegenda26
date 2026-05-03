<?php
 if($myrow['buildmy'] == 41 || $myrow['buildmy'] == 42){
  if(!empty($_SESSION['arhiology'])){
    if(empty($_SESSION['autArx'])) $_SESSION['autArx'] = time();
    $explo =  time()-$_SESSION['autArx'];
    if($explo >= 15){
      $_SESSION['autArx'] = time();
      $okZapAr = (provitems(23,1)?true:false);
    }else{
      $okZapAr = false;
    }
    if($okZapAr == true){
      $timAr = time()-$_SESSION['arhiology'];
      $okArx = false;
      $randomArx  = rand(0,5000);
      if($timAr > 50 && $randomArx > 4980){
       $okArx = true;
       $items = randArr('24,24,25',true); 
       $cools = rand(1,2); 
      }
      if($timAr > 500 && $randomArx > 4996){
       $okArx = true;
       $items = randArr('24,25,29',true);
       $cools = rand(1,3);
      }
      if($timAr > 1200 && $randomArx > 4996){
       $okArx = true;
       $items = randArr('24,25,27,32,29,33,38',true);
       $cools = rand(1,3);
      }
      if($timAr > 2500 && $randomArx > 4996){
       $okArx = true;
       $items = randArr('25,27,28,31,29,33,34',true);
       $cools = rand(1,3);
      }
      if($timAr > 5000 && $randomArx > 4997){
       $okArx = true;
       $items = randArr('26,27,28,31,32,33,38',true);
       $cools = rand(1,4);      
      }
      if($timAr > 8000 && $randomArx > 4997){
       $okArx = true;
       $items = randArr('24,25,26,31,32,35,37',true);
       $cools = rand(2,4);
      }
      if($timAr > 10000 && $randomArx > 4998){
       $okArx = true;
       $items = rand(24,26);
       $items = randArr('24,25,26,27,28,29,30,34,36',true);
       $cools = rand(2,5);      
      }       
      
      if($okArx == true){
       plus_item($cools,$items);
       $arxText = 'Р вЂ™РЎвЂ№ Р Р…Р В°РЎв‚¬Р В»Р С‘: <i>'.infoItems($items,'name').'</i> Р Р† Р С”Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р Вµ: '.$cools.' РЎв‚¬РЎвЂљ.';
       print '<script>parent.mess_error(\''.$arxText.'\',\'block\');</script>';
      }
   }
  }else{
     $_SESSION['arhiology'] = false;
  }
 }else{
   if(!empty($_SESSION['arhiology'])) unset($_SESSION['arhiology']);
   if(!empty($_SESSION['autArx'])) unset($_SESSION['autArx']);
 }
?>