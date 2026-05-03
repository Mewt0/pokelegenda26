<?php
if ($myrow['moderation'] == 0)
{ 
  echo "<script>alert('Вход на эту страницу разрешен только Модераторам!'); location.href='..';</script>"; exit;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; Charset=Windows-1251">
<head> 
<style>
a:link, a:visited {color: #000; text-decoration:none}
a:hover, a:active {color: #000; text-decoration:underline}
body              {background-color: #757575;}
#name             {color: #A52A2A;}
#name2            {color: #0000FF;}
#name4            {color: #000080;}
#name5            {color: #00FFFF;}
#name7            {color: #A020F0;}
#name8            {color: #FF4500;}
#inp{
 position: fixed;
 top: 3px;
 right: 5px;
 color: #eee6a3;
 cursor: pointer;
 z-index: 3;
}
#inp:hover {
 background-color: #bbb;
 height:20px;
}
.nameLOC
{   position:relative;
    padding: 6 4 6 4px;
    overflow: hidden;
    border: 3px groove eee6a3;
-webkit-border-radius: 12px;
-webkit-box-shadow: 0px 0px 12px white;
text-shadow:  #6c8b77 1px 1px 2px,  white 0 0 1em; 
}
</style> 
<br>
<div class = "nameLOC" style="border: 3px groove #eee6a3;"> 
        <h2>Функция модераторов изменена, теперь вы пишите в чате так: <font color = 'brown'><b>/m ЛОГИН МИНУТЫ ПРИЧИНА</b></font> <br>
Пример: <font color = 'brown'><b> /m Tacos 1 Проверка </b></font></h2>
</div>
<div class = "nameLOC" style="font-size: 150%; line-height: 116%;">
        * <b>Пробелы учитывать обязательно!</b><br>
        * <b>Причина может быть не более 30-ти пробелов!</b><br>
        * <font color = 'brown'><b>Те, кто не усвоит новое правило, будут сняты с должности.</b></font>
</div>
<br>
<div class = "nameLOC" style="font-size: 150%;  color:#eee6a3; border: 3px groove #eee6a3;"> Модераторы имеют право выдавать молчу без предупреждения, если считают это необходимым, но аргументируя ее!</span></div></span></div>
<br /><div class="content">
<div style="text-align:center;">
  <span class = "nameLOC" style="font-size: 200%; line-height: 116%; font-weight: bold">
    <font color="#eee6a3"><b>Игроку запрещено:</b></font>
  </span>
</div><br />
<div class = "nameLOC" style="border: 3px groove black; font-weight: bold; text-align:left; line-height: 116%;">
  <span class = "nameLOC" style="font-size: 150%; color:#eee6a3;">I. Злоупотребление:</span></br></br>
    <div class = "nameLOC" style="font-size: 110%;text-align:left; ">
        I.1. Злоупотребление знаками, буквами, смайлами (более 3раз) - Молча до 30 минут</span><br />
        I.2. Злоупотребление рекламой товаров (более 1 сообщения в 2 минуты) - Молча до 20 минут</span><br />
        I.3. Злоупотребление заглавными буквами (капс) - Молча до 20 минут<br />
        I.4. Злоупотребление фразами (более 2 сообщений подряд) - Молча до 15 минут<br />
    </div>
</div><br />


<div class = "nameLOC" style="border: 3px groove black; font-weight: bold; text-align:left; line-height: 116%;">
  <span class = "nameLOC" style="font-size: 150%; color:#eee6a3;">II. Реклама:</span></br></br>
    <div class = "nameLOC" style="font-size: 110%;text-align:left; ">
        II.1. Реклама других проектов, связанных с покемонами (ссылка) - Молча от 100 минут<br />
        II.2. Реклама других проектов, связанных с покемонами (косвенно, по типу "Кто в ... играет") - Молча до 60 минут<br />
        II.3. Реклама порнографии, пропаганда наркотиков или алкоголя - Молча до 600 минут (10 часов);<br />
        II.4. Размещение [Сторонней ссылки] - Молча до 40 минут<br />
        II.5. Размещение ссылки на разрешенные ресурсы (ВКонтакте, Pixz и т.д.) на которых находится запрещенная информация - Молча до 60 минут<br />
    </div>
</div><br />


<div class = "nameLOC" style="border: 3px groove black; font-weight: bold; text-align:left; line-height: 116%;">
  <span class = "nameLOC" style="font-size: 150%; color:#eee6a3;">III. Конфликты::</span></br></br>
    <div class = "nameLOC" style="font-size: 110%;text-align:left; ">
       III.1. Слова и выражения, которые могут оскорбить игроков* - Молча до 40 минут<br />
       III.2. Мат или завуалированный мат** - Молча до 60 минут<br />
       III.3. Оскорбление Администрации, Наставников. Модераторов - Молча до 100 минут<br />
       III.4. Оскорбление родителей или родственников - Молча до 1440 минут (1 день)<br />
       III.5. Угрозы насилия, расправы в реальной жизни - Молча до 2880 минут (2 дня)<br />
       III.6. Проявления расизма, нацизма, национализма, экстремизма, оскорбления на религиозной почве - Молча до 1440 минут (1 день)<br />
       III.7. Провокация конфликта - Молча до 240 минут (4 часа)<br />
       III.8. Обсуждение действий Администрации*** - Молча до 40 минут<br />
    </div>
</div><br />


<div class = "nameLOC" style="border: 3px groove black; font-weight: bold; text-align:left; line-height: 116%;">
  <span class = "nameLOC" style="font-size: 150%; color:#eee6a3;">IV. Прочее:</span></br></br>
    <div class = "nameLOC" style="font-size: 110%;text-align:left; ">
       IV.1. Распространение паники или ложной информации - Молча до 30 минут
       IV.2. Организация договорного боя - Молча до 120 минут ****
       IV.3. Ложная выдача себя за представителя или саму Администрацию - Молча до 300 минут (5 часов) 
       IV.4. Переписка в чате на Иностранных языках - Молча до 15 минут
</div>
</div><br />

<br />

<div style="text-align:center;">
  <span class = "nameLOC" style="font-size: 200%; line-height: 116%; font-weight: bold">
    <font color="#eee6a3"><b>Примечания:</b></font>
  </span>
</div><br />
<div class = "nameLOC" style="border: 3px groove black; font-weight: bold; text-align:left; line-height: 116%;">
    <div class = "nameLOC" style="font-size: 110%;text-align:left; ">
* - Пункт III.1 - Оскорблением также считаются слова ущемляющие достоинства человека (дно, днище, нуб, задрот) и прочие<br />
** - Пункт III.2 - Завуалированный мат - попытка скрыть истинный смысл матного слова, с помощью замены тех или иных букв в этом слове. Пример: *мат офф* = Мля/Б*я, сокращение матерных слов, Пример: бло, пля, мля, пздц, епт, и т.д.<br />
*** - Пункт III.8 - Действия Администрации обычно обсуждаются на форуме, а не в чате<br />
**** - Пункт IV.2 - Договорный бой - бой по договоренности, создан для прокачки PVP ранга (ты проиграешь мне 10 раз, а я тебе заплачу) и т.д.<br />
    </div>
</div><br />

<br />

<div style="text-align:center;">
  <span class = "nameLOC" style="font-size: 200%; line-height: 116%; font-weight: bold">
    <font color="#eee6a3"><b>Выдавший молчу должен:</b></font>
  </span>
</div><br />
<div class = "nameLOC" style="border: 3px groove black; font-weight: bold; text-align:left; line-height: 116%;">
    <div class = "nameLOC" style="font-size: 110%;text-align:left; ">
1. Конкретно объяснить причину выдачи молчи или указать правило(пункт)<br />
2. Размер молчи не должен превышать размер молчи, установленной Администрацией<br />
3. При незначительном нарушении Игроком правил чата или последующем извинении Игрока в нарушении правил выдать предупреждение<br />
    </div>
</div><br />

<div class = "nameLOC" style="font-size: 150%;  color:brown; border: 3px groove brown;"> За превышение нарушений в чате, пользователь может быть забанен/арестован - на некоторое время. </span></div><br>





  <br>
  <center>
  <span class = "nameLOC" style="font-size: 120%; line-height: 116%; font-weight: bold; text-align; center;">
    <font color="#000"><b>By <a href="http://fh7904el.bget.ru/game.php?go=trenInfo&id=1" target=_blank>Tacos © 2014г.</b></font>
  </span>
   </center>
