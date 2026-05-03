<?php
  $harakter['1'] = "Веселый"; 
  $harakter['2'] = "Выносливый";
  $harakter['3'] = "Застенчивый";
  $harakter['4'] = "Кроткий";
  $harakter['5'] = "Мирный";
  $harakter['6'] = "Мягкий";
  $harakter['7'] = "Наглый";
  $harakter['8'] = "Наивный";
  $harakter['9'] = "Нахальный";
  $harakter['10'] = "Нежный";
  $harakter['11'] = "Непослушный";
  $harakter['12'] = "Непреклонный";
  $harakter['13'] = "Обычный";
  $harakter['14'] = "Одинокий";
  $harakter['15'] = "Озорной";
  $harakter['16'] = "Осторожный";
  $harakter['17'] = "Поспешный";
  $harakter['18'] = "Причудливый";
  $harakter['19'] = "Распущенный";
  $harakter['20'] = "Робкий";
  $harakter['21'] = "Серьезный";
  $harakter['22'] = "Скромный";
  $harakter['23'] = "Смелый";
  $harakter['24'] = "Спокойный";
  $harakter['25'] = "Стремительный";
  $harakter['26'] = "Тихий";

  function haracter_pokes($id) {
      global $harakter;
      return isset($harakter[$id]) ? $harakter[$id] : 'UNDEFINED';
  }
?>
?>