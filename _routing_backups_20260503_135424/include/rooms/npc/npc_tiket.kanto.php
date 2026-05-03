<?php       
require_once $_SERVER['DOCUMENT_ROOT'] . '/include/function/function.post.items.php';  
    $person = obr_chis($_GET['npc']);
    $do   = obr_chis($_GET['do']);
    $quest_isset_const = 1;
    $name = "Бортпроводница Клэри";
    $href = '/game.php?go=char&npc='.$person.'&do=';
if(!empty($do)){
    switch ($do){                  
      case 1:
            $about   = "Здравствуй, тренер! Чем я могу тебе помочь?";
            $pers    = '<a href="'.$href.'2">Здравствуйте, у меня есть билет и я бы хотел(-а) пройти на теплоход.</a>';
            $pers   .= '<a href="'.$href.'3">Здравствуйте, я бы хотел(-а) узнать расписание отправки теплохода.</a>';
            $pers   .= '<a href="/game.php?go=char">Простите, я ошибся(-лась).</a>';
      break;
      case 2:
        $xd = datWeekday('Monday,Wednesday,Saturday,Sunday');
        $xt = false;
        $tim = date('H:i:s');
        $y = date('Y');
        $m = date('m'); 
        $d = date('d');
         if(datWeekday('Monday,Wednesday')) {          
              if($tim >= '11:30:00' && $tim < '12:00:00') $xt = date("Y-m-d H:i:s", mktime(17, 00, 00, $m, $d, $y));
          elseif($tim >= '17:30:00' && $tim < '18:00:00') $xt = date("Y-m-d H:i:s", mktime(23, 00, 00, $m, $d, $y));
          elseif($tim >= '22:51:00' && $tim < '23:59:00') $xt = date("Y-m-d H:i:s", mktime(03, 00, 00, $m, $d+1, $y));
         }elseif(datWeekday('Saturday,Sunday')){
              if($tim >= '08:30:00' && $tim < '09:00:00') $xt = date("Y-m-d H:i:s", mktime(14, 00, 00, $m, $d, $y));
          elseif($tim >= '14:30:00' && $tim < '15:00:00') $xt = date("Y-m-d H:i:s", mktime(20, 00, 00, $m, $d, $y)); 
          elseif($tim >= '19:30:00' && $tim < '20:00:00') $xt = date("Y-m-d H:i:s", mktime(01, 00, 00, $m, $d+1, $y)); 
         }
         
         if(!empty($xd) && !empty($xt)){
           if(provitems(21,1)){
            minus_item(1,21);
            $loce  = 25;            
            if(!first('SELECT * FROM users_locvoz WHERE userid=%d AND tip="tiket"',$_SESSION['id'])){
              insert('users_locvoz',array('userid'=>$_SESSION['id'], 'locid'=>$loce, 'tip'=>'tiket', 'dop'=>$xt));
            }else{
              update('users_locvoz',array('locid'=>$loce, 'dop'=>$xt),'userid='.(int)$_SESSION['id'].' AND tip="tiket"');
            }
            update('users',array('buildmy'=>24),'id='.(int)$_SESSION['id']);
            $about   = "Добро пожаловать на палубу!";
            $pers    = '<a href="/game.php?go=char">[Пройти дальше]</a>';     
           }else{
            $about   = "Незачем меня обманывать. Еще никому не удавалось пройти через меня без билета.";
            $pers    = '<a href="/game.php?go=char">Простите...<sup>[Уйти]</sup></a>';            
           }
         }else{
              $about   = "На сегодняшний день отправок не запланировано. Пожалуйста, ознакомьтесь с расписанием отправки теплохода.";
              $pers    = '<a href="'.$href.'3">Я бы хотел(-а) узнать расписание отправки теплохода.</a>';
              $pers   .= '<a href="/game.php?go=char">Хорошо, простите.<sup>[Уйти]</sup></a>';
            if(!$xd && $xt){
              $about   = "На сегодняшний день отправок не запланировано. Пожалуйста, ознакомьтесь с расписанием отправки теплохода.";
              $pers    = '<a href="'.$href.'3">Я бы хотел(-а) узнать расписание отправки теплохода.</a>';
              $pers   .= '<a href="/game.php?go=char">Хорошо, простите.<sup>[Уйти]</sup></a>';              
            }
            if($xd && !$xt){
              $about   = "На данный момент не проходит ни одной посадки. Пожалуйста ознакомьтесь с расписанием посадки на теплоход.";
              $pers    = '<a href="'.$href.'3">Я бы хотел(-а) узнать расписание посадки теплохода.</a>';
              $pers   .= '<a href="/game.php?go=char">Хорошо, простите.<sup>[Уйти]</sup></a>'; 
            }
         }
      break;
      case 3:
          $about  = "Конечно, вот: <br>
                     <table>
                      <tr>
                        <td width='150' align='center'><b>День отправки:</b></td> 
                        <td width='150' align='center'><b>Время посадки с:</b></td>
                        <td width='150' align='center'><b>Время отправки:</b></td> 
                        <td width='150' align='center'><b>Время прибытия:</b></td>
                      </tr>
                      <tr>
                        <td>Понедельник:</td> 
                        <td align='center'>11:30<br>17:30<br>21:30</td>
                        <td align='center'>12:00<br>18:00<br>22:00</td> 
                        <td align='center'>17:00<br>23:00<br>03:00</td>
                      </tr>
                      <tr>
                        <td>Среда:</td> 
                        <td align='center'>11:30<br>17:30<br>21:30</td>
                        <td align='center'>12:00<br>18:00<br>22:00</td> 
                        <td align='center'>17:00<br>23:00<br>03:00</td>
                      </tr>
                      <tr>
                        <td>Суббота:</td> 
                        <td align='center'>08:30<br>14:30<br>19:30</td>
                        <td align='center'>09:00<br>15:00<br>20:00</td> 
                        <td align='center'>14:00<br>20:00<br>01:00</td>
                      </tr>
                      <tr>
                        <td>Воскресенье:</td> 
                        <td align='center'>08:30<br>14:30<br>19:30</td>
                        <td align='center'>09:00<br>15:00<br>20:00</td> 
                        <td align='center'>14:00<br>20:00<br>01:00</td>
                      </tr>
                     </table>
                    ";
          $pers  = '<a href="/game.php?go=char">Спасибо. <sup>[Уйти]</sup></a>';
      break;
      default:
         die("<script>location.href='game.php?go=char';</script>");
   }
}else{
  die("<script>location.href='game.php?go=char';</script>");
}
?>