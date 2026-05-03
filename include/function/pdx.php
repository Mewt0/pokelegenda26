<?php
//...
function evolutionPdx($idmay){
  if($idmay > 493) $img_r = "<img src='pok/anim/".$idmay.".png'>"; else  $img_r = "<img src='pok/anim/".$idmay.".gif'>"; 
  $arr = array();
  $arr['43'] = '<span class="active"><img src="pok/anim/43.gif"> #043 Oddish</span> + 21-lvl -> <a href="/game.php?go=pokedex&id=44"><img src="pok/anim/44.gif">#044 Gloom</a> -><br> + <img src="/img/items/43.png" title="Лиственный камень"> Лиственный камень -> <a href="/game.php?go=pokedex&id=45"><img src="pok/anim/45.gif">#045 Vileplume</a><br> + <img src="/img/evolitem/6.png" title="Солнечный камень"> Солнечный камень -> <a href="/game.php?go=pokedex&id=182"><img src="pok/anim/182.gif">#182 Bellossom</a>';
  $arr['44'] = '<a href="/game.php?go=pokedex&id=43"><img src="pok/anim/43.gif"> #043 Oddish</a> + 21-lvl -> <span class="active"><img src="pok/anim/44.gif">#044 Gloom</span> -><br> + <img src="/img/items/43.png" title="Лиственный камень"> Лиственный камень -> <a href="/game.php?go=pokedex&id=45"><img src="pok/anim/45.gif">#045 Vileplume</a><br> + <img src="/img/evolitem/6.png" title="Солнечный камень"> Солнечный камень -> <a href="/game.php?go=pokedex&id=182"><img src="pok/anim/182.gif">#182 Bellossom</a>';
  $arr['45'] = '<a href="/game.php?go=pokedex&id=43"><img src="pok/anim/43.gif"> #043 Oddish</a> + 21-lvl -> <a href="/game.php?go=pokedex&id=44"><img src="pok/anim/44.gif">#044 Gloom</a> -><br> + <img src="/img/items/43.png" title="Лиственный камень"> Лиственный камень -> <span class="active"><img src="pok/anim/45.gif">#045 Vileplume</span><br> + <img src="/img/evolitem/6.png" title="Солнечный камень"> Солнечный камень -> <a href="/game.php?go=pokedex&id=182"><img src="pok/anim/182.gif">#182 Bellossom</a>';
  
  $arr['60'] = '<span class="active"><img src="pok/anim/60.gif">#060 Poliwag</span> + 25-lvl -> <a href="/game.php?go=pokedex&id=61"><img src="pok/anim/61.gif">#061 Poliwhirl</a> -> <br> + <img src="/img/items/42.png" title="Водный камень"> Водный камень -> <a href="/game.php?go=pokedex&id=62"><img src="pok/anim/62.gif">#062 Poliwrath</a> <br> +  <img src="/img/evolitem/7.png" title="Корона"> Корона + Разведение -> <a href="/game.php?go=pokedex&id=186"><img src="pok/anim/186.gif">#186 Politoed</a> ';
  $arr['61'] = '<a href="/game.php?go=pokedex&id=60"><img src="pok/anim/60.gif">#060 Poliwag</a> + 25-lvl -> <span class="active"><img src="pok/anim/61.gif">#061 Poliwhirl</span> -> <br> + <img src="/img/items/42.png" title="Водный камень"> Водный камень -> <a href="/game.php?go=pokedex&id=62"><img src="pok/anim/62.gif">#062 Poliwrath</a> <br> +  <img src="/img/evolitem/7.png" title="Корона"> Корона + Разведение -> <a href="/game.php?go=pokedex&id=186"><img src="pok/anim/186.gif">#186 Politoed</a> ';
  $arr['62'] = '<a href="/game.php?go=pokedex&id=60"><img src="pok/anim/60.gif">#060 Poliwag</a> + 25-lvl -> <a href="/game.php?go=pokedex&id=61"><img src="pok/anim/61.gif">#061 Poliwhirl</a> -> <br> + <img src="/img/items/42.png" title="Водный камень"> Водный камень -> <span class="active"><img src="pok/anim/62.gif">#062 Poliwrath</span> <br> +  <img src="/img/evolitem/7.png" title="Корона"> Корона + Разведение -> <a href="/game.php?go=pokedex&id=186"><img src="pok/anim/186.gif">#186 Politoed</a> ';
  
  $arr['182'] = '<a href="/game.php?go=pokedex&id=43"><img src="pok/anim/43.gif"> #043 Oddish</a> + 21-lvl -> <a href="/game.php?go=pokedex&id=44"><img src="pok/anim/44.gif">#044 Gloom</a> -><br> + <img src="/img/items/43.png" title="Лиственный камень"> Лиственный камень -> <a href="/game.php?go=pokedex&id=45"><img src="pok/anim/45.gif">#045 Vileplume</a><br> + <img src="/img/evolitem/6.png" title="Солнечный камень"> Солнечный камень -> <span class="active"><img src="pok/anim/182.gif">#182 Bellossom</span>';
  
  $arr['186'] = '<a href="/game.php?go=pokedex&id=60"><img src="pok/anim/60.gif">#060 Poliwag</a> + 25-lvl -> <a href="/game.php?go=pokedex&id=61"><img src="pok/anim/61.gif">#061 Poliwhirl</a> -> <br> + <img src="/img/items/42.png" title="Водный камень"> Водный камень -> <a href="/game.php?go=pokedex&id=62"><img src="pok/anim/62.gif">#062 Poliwrath</a> <br> +  <img src="/img/evolitem/7.png" title="Корона"> Корона + Разведение -> <span class="active"><img src="pok/anim/186.gif">#186 Politoed</span> ';

  $arr['193'] = '<span class="active"><img src="pok/anim/193.gif">#193 Yanma</span> -> 50% Счастья + Ancient Power -> <a href="/game.php?go=pokedex&id=469"><img src="pok/anim/469.gif">#469 Yanmega</a>';
  
  $arr['220'] = '<span class="active"><img src="pok/anim/220.gif">#220 Swinub</span> + 33-lvl -> <a href="/game.php?go=pokedex&id=221"><img src="pok/anim/221.gif">#221 Piloswine</a> -> 50% Счастья + Ancient Power -> <a href="/game.php?go=pokedex&id=473"><img src="pok/anim/473.gif">#473 Mamoswine</a>';
  $arr['221'] = '<a href="/game.php?go=pokedex&id=220"><img src="pok/anim/220.gif">#220 Swinub</a> + 33-lvl -> <span class="active"><img src="pok/anim/221.gif">#221 Piloswine</span> -> 50% Счастья + Ancient Power -> <a href="/game.php?go=pokedex&id=473"><img src="pok/anim/473.gif">#473 Mamoswine</a>';

  $arr['315'] = '<a href="/game.php?go=pokedex&id=406"><img src="pok/anim/406.gif">#406 Budew</a> -> 100% Счастья -> <span class="active"><img src="pok/anim/315.gif">#315 Roselia</span> + <img src="/img/evolitem/8.png" title="Светящийся камень"> Светящийся камень -> <a href="/game.php?go=pokedex&id=407"><img src="pok/anim/407.gif">#407 Roserade</a>';

  $arr['406'] = '<span class="active"><img src="pok/anim/406.gif">#406 Budew</span> -> 100% Счастья -> <a href="/game.php?go=pokedex&id=315"><img src="pok/anim/315.gif">#315 Roselia</a> + <img src="/img/evolitem/8.png" title="Светящийся камень"> Светящийся камень -> <a href="/game.php?go=pokedex&id=407"><img src="pok/anim/407.gif">#407 Roserade</a>';
  $arr['407'] = '<a href="/game.php?go=pokedex&id=406"><img src="pok/anim/406.gif">#406 Budew</a> -> 100% Счастья -> <a href="/game.php?go=pokedex&id=315"><img src="pok/anim/315.gif">#315 Roselia</a> + <img src="/img/evolitem/8.png" title="Светящийся камень"> Светящийся камень -> <span class="active"><img src="pok/anim/407.gif">#407 Roserade</span>';

  $arr['469'] = '<a href="/game.php?go=pokedex&id=193"><img src="pok/anim/193.gif">#193 Yanma</a> -> 50% Счастья + Ancient Power -> <span class="active"><img src="pok/anim/469.gif">#469 Yanmega</span>';

  $arr['473'] = '<a href="/game.php?go=pokedex&id=220"><img src="pok/anim/220.gif">#220 Swinub</a> + 33-lvl -> <a href="/game.php?go=pokedex&id=221"><img src="pok/anim/221.gif">#221 Piloswine</a> -> 50% Счастья + Ancient Power -> <span class="active"><img src="pok/anim/473.gif">#473 Mamoswine</span>';

  $arr['511'] = '<span class="active"><img src="pok/anim/511.png"> #511 Pansage</span> + <img src="/img/items/43.png" title="Лиственный камень"> Лиственный камень -> <a href="/game.php?go=pokedex&id=512"><img src="pok/anim/512.png"> #512 Simisage</a>';
  $arr['512'] = '<a href="/game.php?go=pokedex&id=511"><img src="pok/anim/511.png"> #511 Pansage</a> + <img src="/img/items/43.png" title="Лиственный камень"> Лиственный камень -> <span class="active"><img src="pok/anim/512.png"> #512 Simisage</span>';
  
  $arr['513'] = '<span class="active"><img src="pok/anim/513.png"> #513 Pansear</span> + <img src="/img/items/41.png" title="Огненный камень"> Огненный камень -> <a href="/game.php?go=pokedex&id=514"><img src="pok/anim/514.png"> #512 Simisage</a>';
  $arr['514'] = '<a href="/game.php?go=pokedex&id=513"><img src="pok/anim/513.png"> #513 Pansear</a> + <img src="/img/items/41.png" title="Огненный камень"> Лиственный камень -> <span class="active"><img src="pok/anim/514.png"> #514 Simisear</span>';

  $arr['515'] = '<span class="active"><img src="pok/anim/515.png"> #515 Panpour</span> + <img src="/img/items/31.png" title="Водный камень"> Водный камень -> <a href="/game.php?go=pokedex&id=516"><img src="pok/anim/516.png"> #516 Simipour</a>';
  $arr['516'] = '<a href="/game.php?go=pokedex&id=515"><img src="pok/anim/515.png"> #513 Panpour</a> + <img src="/img/items/31.png" title="Водный камень"> Водный камень -> <span class="active"><img src="pok/anim/516.png"> #516 Simipour</span>';

  $arr['527'] = '<span class="active"><img src="pok/anim/527.png">#527 Woobat</span> -> 100% Счастья -> <a href="/game.php?go=pokedex&id=528"><img src="pok/anim/528.png">#528 Swoobat</a>';
  $arr['528'] = '<a href="/game.php?go=pokedex&id=527"><img src="pok/anim/527.png">#527 Woobat</a> -> 100% Счастья -> <a href="/game.php?go=pokedex&id=528"><img src="pok/anim/528.png"></span>';
  
  $arr['133'] = '<span class="active"><img src="pok/anim/133.gif"> #133 Eevee</span> + <img src="/img/items/41.png" title="Огненный камень"> Огненный камень -> <a href="/game.php?go=pokedex&id=136"><img src="pok/anim/136.gif">#136 Flareon</a><br>+ <img src="/img/evolitem/3.png" title="Водный камень"> Водный камень -> <a href="/game.php?go=pokedex&id=134"><img src="pok/anim/134.gif">#134 Vaporeon</a>+ <img src="/img/evolitem/1.png" title="Громовой камень"> Громовой камень -> <a href="/game.php?go=pokedex&id=135"><img src="pok/anim/135.gif">#135 Jolteon</a>';
  
  if(!empty($arr[$idmay])) return "<b>".$arr[$idmay]."</b>";
  else return false;
}
?>