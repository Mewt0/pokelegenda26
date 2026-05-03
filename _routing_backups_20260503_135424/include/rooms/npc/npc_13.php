<?php
    $person = obr_chis($_GET['quest_npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Миссис Джафит";
    $href = '/game.php?go=char&quest_npc='.$person.'&do=';
function rand_gen($arr){
  $vall = array_rand($arr,1);
 return $arr[$vall];
}
if(!empty($do)){
  if(empty($q8)){  
    switch ($do){                  
      case 1:
            $about   = "Здравствуй! Извини, но мой магазин закрыт. Теперь, пожалуй, навсегда.";
            $pers    = '<a href="'.$href.'2">Простите, что лезу в Ваши дела, но что у Вас произошло?</a>';
            $pers   .= '<a href="/game.php?go=char">Понятно, до свидания.</a>';
      break;
      case 2:
            $about   = "<sup>[Прослезившись отвечает]</sup>Вчера вечером, перед самым закрытием, в мой магазин зашли двое очень странных посетителей. 
                        Сперва они распрашивали меня о моих покемонах-помощниках, которые помогали мне в работе с созданием букетов. 
                        А потом они достали покеболы и вызвали своих покемонов. 
                        После чего начали атаковать без какого-либо предупреждения. 
                        Весь магазин был в черном дыме я ничего не видела, что там происходит. 
                        Заметила лишь только Мяута, который забирал из кассы все деньги. 
                        После такого как весь дым выветрился я увидела свой магазин и была в ужасе, все покемоны были украдены, а магазин полностю разгромлен. 
                        Теперь у меня нет ни покемонов, ни финансов на восстановление магазина. 
                        <sup>[Плачет отвечая очень не внятно]</sup>Пожалуй, я закрываю свои магазин на всегда...";
            $pers    = '<a href="'.$href.'3">А как же полиция? Вы обращались туда?</a>';
            $pers   .= '<a href="/game.php?go=char">Очень грустная история, простите, что ничем не могу Вам помочь, но мне пора.</a>';
      break;
      case 3:
          $about  = "Полиция взялась за расследование, но неизвестно когда оно закончится и закончится ли вообще... 
                     А за это время я потеряю всех своих клиентов.";
          $pers   = '<a href="'.$href.'4">Может быть я смогу Вам помочь чем-либо?</a>';
          $pers  .= '<a href="/game.php?go=char">Очень грустная история, простите, что ничем не могу Вам помочь, но мне пора.</a>';
      break;
      case 4:
          $about  = "Спасибо тебе за заботу, но ты не представляешь себе, сколько всего потребуется чтобы восстановить магазин. 
                     Да мне и отблагодарить нечем, осталось лишь только какое-то яйцо, а что за яйцо я и сама не знаю. 
                     Но если ты сможешь помочь мне, то я отдам тебе его.";
          $pers   = '<a href="'.$href.'5">Вы меня заинтриговали. И так, чем я могу помочь?</a>';
          $pers  .= '<a href="/game.php?go=char">Увы, но я не смогу Вам помочь, я расчитывал на более лучшую награду.</a>';
      break;
      case 5:
          $about  = "Я очень рада, что ты проявил долю милосердия и согласился помочь мне. 
                     Для начала Понадобиться восстановить команду помощников. 
                     И первым, кого бы я хотела чтобы ты мне принес, это: #043 Oddish - с характером: Осторожный.
                     Что бы он с аккуратностю мог украшать букеты.";
          $pers   = '<a href="'.$href.'6" target="_chat_two">Хорошо, как только поймаю Одиша я вернусь к Вам.</a>';
      break;
      case 6:
        if(empty($q8)) insert('quest',array('quest_id'=>8, 'user_id'=>$_SESSION['id'], 'process'=>3));
        die("<script>parent._location.location.href='game.php?go=char';</script>");
      break;
      default:
         die("<script>location.href='game.php?go=char';</script>");
   }
  }elseif($q8['process'] == 3){
    switch ($do){                  
      case 1:
            $about   = "Ты уже принес мне: #043 Oddish - с характером: Осторожный?";
            $pers    = '<a href="'.$href.'2">Да, конечно, вот возьмите.</a>';
            $pers   .= '<a href="/game.php?go=char">Извините, но пока что нет.</a>';
      break;
      case 2:
           $countPq = qCountPoke(1,' AND har=16 AND basenum=43');
           $countP  = qCountPoke(2);
           if($countPq && $countP){ 
            if(delete('pok_user','har=16 AND basenum=43 AND active=1 AND users='.(int)$_SESSION['id'].' LIMIT 1')){
              quest_update(8, 6, 0);
              plus_item(1, 18);
              $about   = "Спасибо! Я тут нашла Сушеный клевер, думаю он тебе будет нужнее. Вот возьми его за работу.<br>
                          Теперь мне потребуется: #012 Butterfree - 50 уровня и женского пола, что бы она могла опылять своей пыльцой мои цветы в оранжерее.";
              $pers    = '<a href="/game.php?go=char">Хорошо, я скоро буду.</a>';
            }else{
              $name    = "<span style='color:brown; font-weight:bold;'>Pokelegenda</span>";
              $about   = 'Cистемная ошибка, попробуйте снова.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';              
            }
           }else{
             if(!$countPq){
              $about   = 'Зачем ты меня обманываешь? Мне итак плохо...';
              $pers    = '<a href="/game.php?go=char">Извините, я пошел его ловить.</a>';
             }
             if(!$countP && $countPq){
              $about   = 'С собой у тебя должно быть больше одного покемона.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';
             }
           } 
      break;
    }
  }elseif($q8['process'] == 6){
    switch ($do){                  
      case 1:
            $about   = "Ты уже принес мне: #012 Butterfree - 50 lvl с женсим полом?";
            $pers    = '<a href="'.$href.'2">Да, конечно, вот возьмите.</a>';
            $pers   .= '<a href="/game.php?go=char">Извините, но пока что нет.</a>';
      break;
      case 2:
           $countPq = qCountPoke(1,' AND sex=2 AND basenum=12 AND lvl=50');
           $countP  = qCountPoke(2);
           if($countPq && $countP){ 
            if(delete('pok_user','sex=2 AND basenum=12 AND lvl=50 AND active=1 AND users='.(int)$_SESSION['id'].' LIMIT 1')){
              quest_update(8, 12, 0);
              plus_item(5, 9);
              $about   = "Спасибо тебе! В кармане у меня завалялось немного конфет. Вот возьми их за работу.<br>
                          Тот одиши, что ты принес в прошлый раз, отлично справляется со своей работой, а так же мы с ним прибрались в магазине. 
                          Ну ладно, что - то я заговорилась... <br>
                          Теперь мне потребуется: #061 Poliwhirl, что бы он мог поливать цветы и следить за их состоянием.";
              $pers    = '<a href="/game.php?go=char">Хорошо, как только я поймаю его, сразу же приду к вам.</a>';
            }else{
              $name    = "<span style='color:brown; font-weight:bold;'>Pokelegenda</span>";
              $about   = 'Cистемная ошибка, попробуйте снова.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';              
            }
           }else{
             if(!$countPq){
              $about   = 'Зачем ты меня обманываешь? Мне итак плохо...';
              $pers    = '<a href="/game.php?go=char">Извините, я скоро принесу ее.</a>';
             }
             if(!$countP && $countPq){
              $about   = 'С собой у тебя должно быть больше одного покемона.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';
             }
           } 
      break;
    }
  }elseif($q8['process'] == 12){
    switch ($do){                  
      case 1:
            $about   = "Ты уже принес мне: #061 Poliwhirl?";
            $pers    = '<a href="'.$href.'2">Да, конечно, вот возьмите.</a>';
            $pers   .= '<a href="/game.php?go=char">Извините, но пока что нет.</a>';
      break;
      case 2:
           $countPq = qCountPoke(1,' AND basenum=61');
           $countP  = qCountPoke(2);
           if($countPq && $countP){ 
            if(delete('pok_user','basenum=61 AND active=1 AND users='.(int)$_SESSION['id'].' LIMIT 1')){
              quest_update(8, 18, 0);
              plus_item(20, 15);
              $about   = "Как же я благодарна тебе, ты столько уже сделал для меня. 
                          Пыльца Батерфри обладает не только свойством распыления, но и свойством усыпления. 
                          И мне удалось сделать из ее пыльцы анти-спрей, который может мгновенно разбудить.  
                          Вот, возьми и себе немного.<br>
                          Теперь мне потребуется последний покемон, пожалуй, мой самый любимый: #070 Weepinbell -  45 уровня с характером: Спокойный, его способности безграничны для моего магазина, он может дать силы и энергию цветам и еще много чего, но это уже личная тайна.";
              $pers    = '<a href="/game.php?go=char">Хорошо, как только я поймаю его, сразу же приду к вам.</a>';
            }else{
              $name    = "<span style='color:brown; font-weight:bold;'>Pokelegenda</span>";
              $about   = 'Cистемная ошибка, попробуйте снова.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';              
            }
           }else{
             if(!$countPq){
              $about   = 'Зачем ты меня обманываешь? Мне итак плохо...';
              $pers    = '<a href="/game.php?go=char">Извините, я скоро принесу ее.</a>';
             }
             if(!$countP && $countPq){
              $about   = 'С собой у тебя должно быть больше одного покемона.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';
             }
           } 
      break;
    }
  }elseif($q8['process'] == 18){
    switch ($do){                  
      case 1:
            $about   = "Ты уже принес мне: #070 Weepinbell 45 - lvl с характером: Спокойный?";
            $pers    = '<a href="'.$href.'2">Да, конечно, вот возьмите.</a>';
            $pers   .= '<a href="/game.php?go=char">Извините, но пока что нет.</a>';
      break;
      case 2:
           $countPq = qCountPoke(1,' AND basenum=70 AND har=24 AND lvl=45');
           $countP  = qCountPoke(2);
           if($countPq && $countP){ 
            $pokeEgg = array('37','86','113');
            $gen     = array('10','11','12','15','11','10','20','18');
            $vall    = array_rand($pokeEgg,1);
            $timSpark = time() +(60*60*24*rand(2,5));
            $sostox = delete('pok_user','basenum=70 AND har=24 AND lvl=45 AND active=1 AND users='.(int)$_SESSION['id'].' LIMIT 1');
            $sostoy = insert('eggs',array('base_id_egg'=>$pokeEgg[$vall], 
                                           'users_egg'=>$_SESSION['id'], 
                                           'dtime'=>$timSpark,
                                           'hp_iv'=>rand_gen($gen),
                                           'atk_iv'=>rand_gen($gen),
                                           'def_iv'=>rand_gen($gen),
                                           'sdef_iv'=>rand_gen($gen),
                                           'satk_iv'=>rand_gen($gen),
                                           'speed_iv'=>rand_gen($gen),
                                           'tips'=>'normal'));
            if($sostoy > 0){
              quest_update(8, 20, 1);
              questRangUp(4);
              $about   = "Хороший ты тренер, спасибо тебе за все, что ты сделал. 
                          Мой магазин откроется очень скоро и я снова смогу продавать свои букеты.
                          Ну чтож, как и обещала возьми в награду вот это загадочное яйцо. И еще раз спасибо за помощь. 
                          Теперь я буду очень занята, до встречи.";
              $pers    = '<a href="/game.php?go=char">Спасибо и Вам, до свидания.</a>';
            }else{
              $name    = "<span style='color:brown; font-weight:bold;'>Pokelegenda</span>";
              $about   = 'Cистемная ошибка, попробуйте снова.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';              
            }
           }else{
             if(!$countPq){
              $about   = 'Зачем ты меня обманываешь? Мне итак плохо...';
              $pers    = '<a href="/game.php?go=char">Извините, я скоро принесу его.</a>';
             }
             if(!$countP && $countPq){
              $about   = 'С собой у тебя должно быть больше одного покемона.';
              $pers    = '<a href="/game.php?go=char">Хорошо.</a>';
             }
           } 
      break;
    }
  }










}else{
  die("<script>location.href='game.php?go=char';</script>");
}
?>