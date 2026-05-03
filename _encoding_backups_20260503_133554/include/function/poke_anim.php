<?php
$yummy = array("league17.ru", 
                "league17",
                "league_17", 
                "Р В»Р С‘Р С–Р В°17", 
                "Р В»Р С‘Р С–Р В° 17", 
                "Р В»Р С‘Р С–Р В°_17",
                "Р вЂєР С‘Р С–Р В°_17",
                "Р вЂєР ВР С–Р В°_17",
                "Р вЂєР ВР вЂњР В°_17",
                "Р вЂєР ВР вЂњР С’_17",
                "Р вЂєР ВР С–Р С’_17",
                "Р вЂєР С‘Р вЂњР С’_17",
                "Р В»Р С‘Р вЂњР С’_17",
                "Р В»Р ВР вЂњР С’_17",
                "Р В»Р ВР С–Р С’_17",
                "Р В»Р ВР С–Р В°_17",
                "Р вЂєР С‘Р С–Р В° 17",
                "Р вЂєР ВР С–Р В° 17",
                "Р вЂєР ВР вЂњР В° 17",
                "Р вЂєР ВР вЂњР С’ 17",
                "Р вЂєР ВР С–Р С’ 17",
                "Р вЂєР С‘Р вЂњР С’ 17",
                "Р В»Р С‘Р вЂњР С’ 17",
                "Р В»Р ВР вЂњР С’ 17",
                "Р В»Р ВР С–Р С’ 17",
                "Р В»Р ВР С–Р В° 17",
                "Р вЂєР С‘Р С–Р В°-17",
                "Р вЂєР ВР С–Р В°-17",
                "Р вЂєР ВР вЂњР В°-17",
                "Р вЂєР ВР вЂњР С’-17",
                "Р вЂєР ВР С–Р С’-17",
                "Р вЂєР С‘Р вЂњР С’-17",
                "Р В»Р С‘Р вЂњР С’-17",
                "Р В»Р ВР вЂњР С’-17",
                "Р В»Р ВР С–Р С’-17",
                "Р В»Р ВР С–Р В°-17",
                "Р вЂєР С‘Р С–Р В°17",
                "Р вЂєР ВР С–Р В°17",
                "Р вЂєР ВР вЂњР В°17",
                "Р вЂєР ВР вЂњР С’17",
                "Р вЂєР ВР С–Р С’17",
                "Р вЂєР С‘Р вЂњР С’17",
                "Р В»Р С‘Р вЂњР С’17",
                "Р В»Р ВР вЂњР С’17",
                "Р В»Р ВР С–Р С’17",
                "Р В»Р ВР С–Р В°17",
                "League17.ru",
                "LEague17.ru",
                "LEAgue17.ru",
                "LEAGue17.ru",
                "LEAGUe17.ru",
                "LEAGUE17.ru",
                "League17",
                "LEague17",
                "LEAgue17",
                "LEAGue17",
                "LEAGUe17",
                "LEAGUE17",
                "L-E-A-G-U-E-1-7",
                "L-E-A-G-U-E",
                "pokess", "Pokess", "POkess", "POKess", "POKEss", "POKESs", "POKESS", "p o k e s s",  "p okess", "p o kess", "p o k ess", "p o k e ss", "p o k e s s",
                "pokess", "PokesS", "pOkess", "poKess", "pokEss", "pokeSs", "pokesS", "pokes s", "poke s s", "pok e s s", "po k e s s", "p okes s",
                "pokess", "PoKesS", "PokEsS", "POkesS", "POkEsS", "POkeSS", "POkESS",
                "pokess", "pOkEsS", "POkESS", "poKEsS", "PoKeSS", "poKeSS", "poKeSs",
                "pokess", "pOkeSs", "pOkESs", "pOKESs", "pOKESS", "poKESS", "pokESS", "pokeSS", "pokesS"
                );


$text = str_replace($yummy, "******", $text);

$matoff = array(
    "http://pokess.ru", "POKEss",  "17Р В»Р С‘Р С–",
    "pokess.ru",        "POKESs",  "17Р вЂєР ВР вЂњ",
    "pokess.RU",        "POKESS",  "17Р вЂєР С‘Р вЂњ",
    "pokess",           "POKEsS",  "17Р вЂєР С‘Р С–",
    "Pokess",           "POKesS",  "17Р В»Р ВР вЂњ",
    "POkess",           "POkesS",  "17Р В»Р С‘Р вЂњ",
    "POKess",           "PokesS",  "17Р В»Р ВР С–",
    "pokeword",        "Pokeword", "POkeword", "POKeword", "POKEword", "POKEWord",  "POKEWOrd", "POKEWORd", "POKEWORD",
    "POKEWOrD", "POKEWoRD", "POKEwORD", "POKeWORD", "POkEWORD", "PoKEWORD", "pOKEWORD", "POKEWOrd", "POKEWord", "POKEword",
    "POKeword", "POkeword", "Pokeword", "PokeworD", "PokeWorD", "PoKeWorD", "PoKeWoRD", "PoKEWoRD", "POkEWoRD"


);
$text = str_replace($matoff, "<s>***</s>", $text);



$text = str_replace(":)","<img src=/img/smile/smile.gif border=0>",$text);
$text = str_replace(":(","<img src=/img/smile/sad.gif border=0>",$text);
$text = str_replace("T_T","<img src=/img/smile/cray.gif border=0>",$text);
$text = str_replace(":roflmao:","<img src=/img/smile/roflmao.gif border=0>",$text);
$text = str_replace(":SEARCH:","<img src=/img/smile/search.gif border=0>",$text);
$text = str_replace(":acute:","<img src=/img/smile/acute.gif border=0>",$text);
$text = str_replace(":D","<img src=/img/smile/biggrin.gif border=0>",$text);
$text = str_replace(":rofl:","<img src=/img/smile/rofl.gif border=0>",$text);
$text = str_replace(":blushinh:","<img src=/img/smile/blush2.gif border=0>",$text);
$text = str_replace(":angel:","<img src=/img/smile/angel.gif border=0>",$text);
$text = str_replace(":flirt:","<img src=/img/smile/flirt.gif border=0>",$text);
$text = str_replace(":crazy:","<img src=/img/smile/crazy.gif border=0>",$text);
$text = str_replace(":dance:","<img src=/img/smile/dance.gif border=0>",$text);
$text = str_replace(":B","<img src=/img/smile/dirol.gif border=0>",$text);
$text = str_replace(":dntknw:","<img src=/img/smile/dntknw.gif border=0>",$text);
$text = str_replace(":drinks:","<img src=/img/smile/drinks.gif border=0>",$text);
$text = str_replace(":diablo:","<img src=/img/smile/diablo.gif border=0>",$text);
$text = str_replace(":hi:","<img src=/img/smile/aiwan-hi.gif border=0>",$text);
$text = str_replace(":clapping:","<img src=/img/smile/clapping.gif border=0>",$text);


$users_smile_time = users_conect_dop('smiletime');
if($users_smile_time != 'not'){
  if($users_smile_time <= time()){
    update('usersunictable',array('smile'=>0, 'smiletime'=>'not'),'id='.(int)$_SESSION['id']);
  }
}
$users_smiles = users_conect_dop('smile'); 
  
if($users_smiles == 2){
    $text = str_replace(":063:","<img src=/img/smile/063.gif border=0 width = 32/>",$text);
    $text = str_replace(":064:","<img src=/img/smile/064.gif border=0 width = 32/>",$text);
    $text = str_replace(":065:","<img src=/img/smile/065.gif border=0 width = 32/>",$text);
    $text = str_replace(":066:","<img src=/img/smile/066.gif border=0 width = 32/>",$text);
    $text = str_replace(":067:","<img src=/img/smile/067.gif border=0 width = 32/>",$text);
    $text = str_replace(":068:","<img src=/img/smile/068.gif border=0 width = 32/>",$text);
    $text = str_replace(":069:","<img src=/img/smile/069.gif border=0 width = 32/>",$text);
    $text = str_replace(":070:","<img src=/img/smile/070.gif border=0 width = 32/>",$text);
    $text = str_replace(":071:","<img src=/img/smile/071.gif border=0 width = 32/>",$text);
    $text = str_replace(":072:","<img src=/img/smile/072.gif border=0 width = 32/>",$text);
    $text = str_replace(":150:","<img src=/img/smile/150.png border=0 width = 32/>",$text);
    $text = str_replace(":151:","<img src=/img/smile/151.png border=0 width = 32/>",$text);
    $text = str_replace(":152:","<img src=/img/smile/152.png border=0 width = 32/>",$text);
    $text = str_replace(":153:","<img src=/img/smile/153.png border=0 width = 32/>",$text);
    $text = str_replace(":154:","<img src=/img/smile/154.png border=0 width = 32/>",$text);
    $text = str_replace(":155:","<img src=/img/smile/155.png border=0 width = 32/>",$text);
    $text = str_replace(":156:","<img src=/img/smile/156.png border=0 width = 32/>",$text);
    $text = str_replace(":157:","<img src=/img/smile/157.png border=0 width = 32/>",$text);
    $text = str_replace(":158:","<img src=/img/smile/158.png border=0 width = 32/>",$text);
    $text = str_replace(":159:","<img src=/img/smile/159.png border=0 width = 32/>",$text);    
    $text = str_replace(":160:","<img src=/img/smile/160.png border=0 width = 32/>",$text);
    $text = str_replace(":161:","<img src=/img/smile/161.png border=0 width = 32/>",$text);
    $text = str_replace(":162:","<img src=/img/smile/162.png border=0 width = 32/>",$text);
    $text = str_replace(":163:","<img src=/img/smile/163.png border=0 width = 32/>",$text);
    $text = str_replace(":164:","<img src=/img/smile/164.png border=0 width = 32/>",$text);
    $text = str_replace(":165:","<img src=/img/smile/165.png border=0 width = 32/>",$text);
    $text = str_replace(":166:","<img src=/img/smile/166.png border=0 width = 32/>",$text);
    $text = str_replace(":167:","<img src=/img/smile/167.png border=0 width = 32/>",$text);
    $text = str_replace(":168:","<img src=/img/smile/168.png border=0 width = 32/>",$text);
    $text = str_replace(":169:","<img src=/img/smile/169.png border=0 width = 32/>",$text);
    $text = str_replace(":170:","<img src=/img/smile/170.png border=0 width = 32/>",$text);
    $text = str_replace(":171:","<img src=/img/smile/171.png border=0 width = 32/>",$text);
    $text = str_replace(":172:","<img src=/img/smile/172.png border=0 width = 32/>",$text);
    $text = str_replace(":173:","<img src=/img/smile/173.png border=0 width = 32/>",$text);
    $text = str_replace(":174:","<img src=/img/smile/174.png border=0 width = 32/>",$text);
    $text = str_replace(":175:","<img src=/img/smile/175.png border=0 width = 32/>",$text);
    $text = str_replace(":176:","<img src=/img/smile/176.png border=0 width = 32/>",$text);
    $text = str_replace(":177:","<img src=/img/smile/177.png border=0 width = 32/>",$text);
    $text = str_replace(":178:","<img src=/img/smile/178.png border=0 width = 32/>",$text);
    $text = str_replace(":179:","<img src=/img/smile/179.png border=0 width = 32/>",$text);
    $text = str_replace(":180:","<img src=/img/smile/180.png border=0 width = 32/>",$text);
    $text = str_replace(":181:","<img src=/img/smile/181.png border=0 width = 32/>",$text);
    $text = str_replace(":182:","<img src=/img/smile/182.png border=0 width = 32/>",$text);
    $text = str_replace(":183:","<img src=/img/smile/183.png border=0 width = 32/>",$text);
    $text = str_replace(":184:","<img src=/img/smile/184.png border=0 width = 32/>",$text);
    $text = str_replace(":185:","<img src=/img/smile/185.png border=0 width = 32/>",$text);
    $text = str_replace(":186:","<img src=/img/smile/186.png border=0 width = 32/>",$text);
    $text = str_replace(":187:","<img src=/img/smile/187.png border=0 width = 32/>",$text);
    $text = str_replace(":188:","<img src=/img/smile/188.png border=0 width = 32/>",$text);
    $text = str_replace(":189:","<img src=/img/smile/189.png border=0 width = 32/>",$text);
    $text = str_replace(":190:","<img src=/img/smile/190.png border=0 width = 32/>",$text);
    $text = str_replace(":191:","<img src=/img/smile/191.png border=0 width = 32/>",$text);
    $text = str_replace(":192:","<img src=/img/smile/192.png border=0 width = 32/>",$text);
    $text = str_replace(":193:","<img src=/img/smile/193.png border=0 width = 32/>",$text);
    $text = str_replace(":194:","<img src=/img/smile/194.png border=0 width = 32/>",$text);
    $text = str_replace(":195:","<img src=/img/smile/195.png border=0 width = 32/>",$text);
    $text = str_replace(":196:","<img src=/img/smile/196.png border=0 width = 32/>",$text);
    $text = str_replace(":197:","<img src=/img/smile/197.png border=0 width = 32/>",$text);
    $text = str_replace(":198:","<img src=/img/smile/198.png border=0 width = 32/>",$text);
    $text = str_replace(":199:","<img src=/img/smile/199.png border=0 width = 32/>",$text);
    $text = str_replace(":200:","<img src=/img/smile/200.png border=0 width = 32/>",$text);
    $text = str_replace(":201:","<img src=/img/smile/201.png border=0 width = 32/>",$text);
    $text = str_replace(":202:","<img src=/img/smile/202.png border=0 width = 32/>",$text);
    $text = str_replace(":203:","<img src=/img/smile/203.png border=0 width = 32/>",$text);
    $text = str_replace(":204:","<img src=/img/smile/204.png border=0 width = 32/>",$text);
    $text = str_replace(":205:","<img src=/img/smile/205.png border=0 width = 32/>",$text);
    $text = str_replace(":206:","<img src=/img/smile/206.png border=0 width = 32/>",$text);
    $text = str_replace(":207:","<img src=/img/smile/207.png border=0 width = 32/>",$text);
    $text = str_replace(":208:","<img src=/img/smile/208.png border=0 width = 32/>",$text);
    $text = str_replace(":209:","<img src=/img/smile/209.png border=0 width = 32/>",$text);
    $text = str_replace(":210:","<img src=/img/smile/210.png border=0 width = 32/>",$text);
    $text = str_replace(":211:","<img src=/img/smile/211.png border=0 width = 32/>",$text);
    $text = str_replace(":212:","<img src=/img/smile/212.png border=0 width = 32/>",$text);
    $text = str_replace(":213:","<img src=/img/smile/213.png border=0 width = 32/>",$text);    
}

if($users_smiles == 1 || $users_smiles == 2){
    $text = str_replace(":002:","<img src=/img/smile/002.gif border=0 />",$text);
    $text = str_replace(":003:","<img src=/img/smile/003.gif border=0 />",$text);
    $text = str_replace(":004:","<img src=/img/smile/004.gif border=0 />",$text);
    $text = str_replace(":005:","<img src=/img/smile/005.gif border=0 />",$text);
    $text = str_replace(":006:","<img src=/img/smile/006.gif border=0 />",$text);
    $text = str_replace(":007:","<img src=/img/smile/007.gif border=0 />",$text);
    $text = str_replace(":008:","<img src=/img/smile/008.gif border=0 />",$text);
    $text = str_replace(":009:","<img src=/img/smile/009.gif border=0 />",$text);
    $text = str_replace(":010:","<img src=/img/smile/010.gif border=0 />",$text);
    $text = str_replace(":011:","<img src=/img/smile/011.gif border=0 />",$text);
    $text = str_replace(":012:","<img src=/img/smile/012.gif border=0 />",$text);
    $text = str_replace(":013:","<img src=/img/smile/013.gif border=0 />",$text);
    $text = str_replace(":014:","<img src=/img/smile/014.gif border=0 />",$text);
    $text = str_replace(":015:","<img src=/img/smile/015.gif border=0 />",$text);
    $text = str_replace(":016:","<img src=/img/smile/016.gif border=0 />",$text);
    $text = str_replace(":017:","<img src=/img/smile/017.gif border=0 />",$text);
    $text = str_replace(":018:","<img src=/img/smile/018.gif border=0 />",$text);
    $text = str_replace(":019:","<img src=/img/smile/019.gif border=0 />",$text);
    $text = str_replace(":020:","<img src=/img/smile/020.gif border=0 />",$text);
    $text = str_replace(":021:","<img src=/img/smile/021.gif border=0 />",$text);
    $text = str_replace(":022:","<img src=/img/smile/022.gif border=0 />",$text);
    $text = str_replace(":023:","<img src=/img/smile/023.gif border=0 />",$text);
    $text = str_replace(":024:","<img src=/img/smile/024.gif border=0 />",$text);
    $text = str_replace(":025:","<img src=/img/smile/025.gif border=0 />",$text);
    $text = str_replace(":026:","<img src=/img/smile/026.gif border=0 />",$text);
    $text = str_replace(":027:","<img src=/img/smile/027.gif border=0 />",$text);
    $text = str_replace(":028:","<img src=/img/smile/028.gif border=0 />",$text);
    $text = str_replace(":029:","<img src=/img/smile/029.gif border=0 />",$text);
    $text = str_replace(":030:","<img src=/img/smile/030.gif border=0 />",$text);
    $text = str_replace(":031:","<img src=/img/smile/031.gif border=0 />",$text);
    $text = str_replace(":032:","<img src=/img/smile/032.gif border=0 />",$text);
    $text = str_replace(":033:","<img src=/img/smile/033.gif border=0 />",$text);
    $text = str_replace(":034:","<img src=/img/smile/034.gif border=0 />",$text);
    $text = str_replace(":035:","<img src=/img/smile/035.gif border=0 />",$text);
    $text = str_replace(":036:","<img src=/img/smile/036.gif border=0 />",$text);
    $text = str_replace(":037:","<img src=/img/smile/037.gif border=0 />",$text);
    $text = str_replace(":038:","<img src=/img/smile/038.gif border=0 />",$text);
    $text = str_replace(":039:","<img src=/img/smile/039.gif border=0 />",$text);
    $text = str_replace(":040:","<img src=/img/smile/040.gif border=0 />",$text);
    $text = str_replace(":041:","<img src=/img/smile/041.gif border=0 />",$text);
    $text = str_replace(":042:","<img src=/img/smile/042.gif border=0 />",$text);
    $text = str_replace(":043:","<img src=/img/smile/043.gif border=0 />",$text);
    $text = str_replace(":044:","<img src=/img/smile/044.gif border=0 />",$text);
    $text = str_replace(":045:","<img src=/img/smile/045.gif border=0 />",$text);
    $text = str_replace(":046:","<img src=/img/smile/046.gif border=0 />",$text);
    $text = str_replace(":047:","<img src=/img/smile/047.gif border=0 />",$text);
    $text = str_replace(":048:","<img src=/img/smile/048.gif border=0 />",$text);
    $text = str_replace(":049:","<img src=/img/smile/049.gif border=0 />",$text);
    $text = str_replace(":050:","<img src=/img/smile/050.gif border=0 />",$text);
    $text = str_replace(":051:","<img src=/img/smile/051.gif border=0 />",$text);
    $text = str_replace(":052:","<img src=/img/smile/052.gif border=0 />",$text);
    $text = str_replace(":053:","<img src=/img/smile/053.gif border=0 />",$text);
    $text = str_replace(":054:","<img src=/img/smile/054.gif border=0 />",$text);
    $text = str_replace(":055:","<img src=/img/smile/055.gif border=0 />",$text);
    $text = str_replace(":056:","<img src=/img/smile/056.gif border=0 />",$text);
    $text = str_replace(":057:","<img src=/img/smile/057.gif border=0 />",$text);
    $text = str_replace(":058:","<img src=/img/smile/058.gif border=0 />",$text);
    $text = str_replace(":059:","<img src=/img/smile/059.gif border=0 />",$text);
    $text = str_replace(":060:","<img src=/img/smile/060.gif border=0 />",$text);
    $text = str_replace(":061:","<img src=/img/smile/061.gif border=0 />",$text);
    $text = str_replace(":062:","<img src=/img/smile/062.gif border=0 />",$text);
    $text = str_replace(":096:","<img src=/img/smile/096.gif border=0 />",$text);
    $text = str_replace(":097:","<img src=/img/smile/097.gif border=0 />",$text);
    $text = str_replace(":098:","<img src=/img/smile/098.gif border=0 />",$text);
    $text = str_replace(":099:","<img src=/img/smile/099.gif border=0 />",$text);
    $text = str_replace(":100:","<img src=/img/smile/100.gif border=0 />",$text);
    $text = str_replace(":109:","<img src=/img/smile/109.gif border=0 />",$text);
    $text = str_replace(":110:","<img src=/img/smile/110.gif border=0 />",$text);
    $text = str_replace(":111:","<img src=/img/smile/111.gif border=0 />",$text);
    $text = str_replace(":112:","<img src=/img/smile/112.gif border=0 />",$text);
    $text = str_replace(":113:","<img src=/img/smile/113.gif border=0 />",$text);
    $text = str_replace(":bann:","<img src=/img/smile/bann.gif border=0 />",$text);
    $text = str_replace(":gunguns:","<img src=/img/smile/gun_guns.gif border=0 />",$text);
    $text = str_replace(":maninlove:","<img src=/img/smile/man_in_love.gif border=0 />",$text);
    $text = str_replace(":surrender:","<img src=/img/smile/surrender.gif border=0 />",$text);
    $text = str_replace(":telephone:","<img src=/img/smile/telephone.gif border=0 />",$text);
    $text = str_replace(":aggressive:","<img src=/img/smile/aggressive.gif border=0>",$text);
    $text = str_replace(":airkiss:","<img src=/img/smile/air_kiss.gif border=0>",$text);
    $text = str_replace(":angry:","<img src=/img/smile/angry2.gif border=0>",$text);
    $text = str_replace(":argue:","<img src=/img/smile/argue2.gif border=0>",$text);
    $text = str_replace(":bee:","<img src=/img/smile/beee.gif border=0>",$text);
    $text = str_replace(":blink:","<img src=/img/smile/blink.gif border=0>",$text);
    $text = str_replace(":boredom:","<img src=/img/smile/boredom.gif border=0>",$text);
    $text = str_replace(":friends:","<img src=/img/smile/friends.gif border=0>",$text);
    $text = str_replace(":bandana:","<img src=/img/smile/gun_bandana.gif border=0>",$text);
    $text = str_replace(":happy:","<img src=/img/smile/happy.gif border=0>",$text);
    $text = str_replace(":hunter:","<img src=/img/smile/hunter.gif border=0>",$text);
    $text = str_replace(":lazy3:","<img src=/img/smile/lazy3.gif border=0>",$text);
    $text = str_replace(":spiteful:","<img src=/img/smile/spiteful.gif border=0>",$text);
    $text = str_replace(":yahoo:","<img src=/img/smile/yahoo.gif border=0>",$text);
    $text = str_replace(":shout:","<img src=/img/smile/shout.gif border=0>",$text);
    $text = str_replace(":rolleyes:","<img src=/img/smile/rolleyes.gif border=0>",$text);
    $text = str_replace(":bandana:","<img src=/img/smile/gun_bandana.gif border=0>",$text);
    $text = str_replace(":haha:","<img src=/img/smile/haha.gif border=0>",$text);
    $text = str_replace(":nono:","<img src=/img/smile/nono.gif border=0>",$text);
    $text = str_replace(":nea:","<img src=/img/smile/nea.gif border=0>",$text);
    $text = str_replace(":newrussian:","<img src=/img/smile/new_russian.gif border=0>",$text);
    $text = str_replace(":nyam:","<img src=/img/smile/nyam.gif border=0>",$text);
    $text = str_replace(":ohmy:","<img src=/img/smile/ohmy.gif border=0>",$text);
    $text = str_replace(":pilot:","<img src=/img/smile/pilot.gif border=0>",$text);
    $text = str_replace(":read:","<img src=/img/smile/read.gif border=0>",$text);
    $text = str_replace(":secret:","<img src=/img/smile/secret.gif border=0>",$text);
    $text = str_replace(":snooks:","<img src=/img/smile/snooks.gif border=0>",$text);
    $text = str_replace(":sorry:","<img src=/img/smile/sorry.gif border=0>",$text);
    $text = str_replace(":stink:","<img src=/img/smile/stink.gif border=0>",$text);
    $text = str_replace(":stop:","<img src=/img/smile/stop.gif border=0>",$text);
    $text = str_replace(":superman:","<img src=/img/smile/superman.gif border=0>",$text);
    $text = str_replace(":threaten:","<img src=/img/smile/threaten.gif border=0>",$text);
    $text = str_replace(":swoon:","<img src=/img/smile/swoon.gif border=0>",$text);
    $text = str_replace(":tease:","<img src=/img/smile/tease.gif border=0>",$text);                                            
    $text = str_replace(":good:","<img src=/img/smile/good.gif border=0>",$text);
    $text = str_replace(":facepalm:","<img src=/img/smile/facepalm.gif border=0>",$text);
    
    
    $text = str_replace("#001","<img src=pok/anim/001.gif border=0>",$text);
    $text = str_replace("#002","<img src=pok/anim/002.gif border=0>",$text);
    $text = str_replace("#003","<img src=pok/anim/003.gif border=0>",$text);
    $text = str_replace("#004","<img src=pok/anim/004.gif border=0>",$text);
    $text = str_replace("#005","<img src=pok/anim/005.gif border=0>",$text);
    $text = str_replace("#006","<img src=pok/anim/006.gif border=0>",$text);
    $text = str_replace("#007","<img src=pok/anim/007.gif border=0>",$text);
    $text = str_replace("#008","<img src=pok/anim/008.gif border=0>",$text);
    $text = str_replace("#009","<img src=pok/anim/009.gif border=0>",$text);
    $text = str_replace("#010","<img src=pok/anim/010.gif border=0>",$text);
    $text = str_replace("#011","<img src=pok/anim/011.gif border=0>",$text);
    $text = str_replace("#012","<img src=pok/anim/012.gif border=0>",$text);
    $text = str_replace("#013","<img src=pok/anim/013.gif border=0>",$text);
    $text = str_replace("#014","<img src=pok/anim/014.gif border=0>",$text);
    $text = str_replace("#015","<img src=pok/anim/015.gif border=0>",$text);
    $text = str_replace("#016","<img src=pok/anim/016.gif border=0>",$text);
    $text = str_replace("#017","<img src=pok/anim/017.gif border=0>",$text);
    $text = str_replace("#018","<img src=pok/anim/018.gif border=0>",$text);
    $text = str_replace("#019","<img src=pok/anim/019.gif border=0>",$text);
    $text = str_replace("#020","<img src=pok/anim/020.gif border=0>",$text);
    $text = str_replace("#021","<img src=pok/anim/021.gif border=0>",$text);
    $text = str_replace("#022","<img src=pok/anim/022.gif border=0>",$text);
    $text = str_replace("#023","<img src=pok/anim/023.gif border=0>",$text);
    $text = str_replace("#024","<img src=pok/anim/024.gif border=0>",$text);
    $text = str_replace("#025","<img src=pok/anim/025.gif border=0>",$text);
    $text = str_replace("#026","<img src=pok/anim/026.gif border=0>",$text);
    $text = str_replace("#027","<img src=pok/anim/027.gif border=0>",$text);
    $text = str_replace("#028","<img src=pok/anim/028.gif border=0>",$text);
    $text = str_replace("#029","<img src=pok/anim/029.gif border=0>",$text);
    $text = str_replace("#030","<img src=pok/anim/030.gif border=0>",$text);
    $text = str_replace("#031","<img src=pok/anim/031.gif border=0>",$text);
    $text = str_replace("#032","<img src=pok/anim/032.gif border=0>",$text);
    $text = str_replace("#033","<img src=pok/anim/033.gif border=0>",$text);
    $text = str_replace("#034","<img src=pok/anim/034.gif border=0>",$text);
    $text = str_replace("#035","<img src=pok/anim/035.gif border=0>",$text);
    $text = str_replace("#036","<img src=pok/anim/036.gif border=0>",$text);
    $text = str_replace("#037","<img src=pok/anim/037.gif border=0>",$text);
    $text = str_replace("#038","<img src=pok/anim/038.gif border=0>",$text);
    $text = str_replace("#039","<img src=pok/anim/039.gif border=0>",$text);
    $text = str_replace("#040","<img src=pok/anim/040.gif border=0>",$text);
    $text = str_replace("#041","<img src=pok/anim/041.gif border=0>",$text);
    $text = str_replace("#042","<img src=pok/anim/042.gif border=0>",$text);
    $text = str_replace("#043","<img src=pok/anim/043.gif border=0>",$text);
    $text = str_replace("#044","<img src=pok/anim/044.gif border=0>",$text);
    $text = str_replace("#045","<img src=pok/anim/045.gif border=0>",$text);
    $text = str_replace("#046","<img src=pok/anim/046.gif border=0>",$text);
    $text = str_replace("#047","<img src=pok/anim/047.gif border=0>",$text);
    $text = str_replace("#048","<img src=pok/anim/048.gif border=0>",$text);
    $text = str_replace("#049","<img src=pok/anim/049.gif border=0>",$text);
    $text = str_replace("#050","<img src=pok/anim/050.gif border=0>",$text);
    $text = str_replace("#051","<img src=pok/anim/051.gif border=0>",$text);
    $text = str_replace("#052","<img src=pok/anim/052.gif border=0>",$text);
    $text = str_replace("#053","<img src=pok/anim/053.gif border=0>",$text);
    $text = str_replace("#054","<img src=pok/anim/054.gif border=0>",$text);
    $text = str_replace("#055","<img src=pok/anim/055.gif border=0>",$text);
    $text = str_replace("#056","<img src=pok/anim/056.gif border=0>",$text);
    $text = str_replace("#057","<img src=pok/anim/057.gif border=0>",$text);
    $text = str_replace("#058","<img src=pok/anim/058.gif border=0>",$text);
    $text = str_replace("#059","<img src=pok/anim/059.gif border=0>",$text);
    $text = str_replace("#060","<img src=pok/anim/060.gif border=0>",$text);
    $text = str_replace("#061","<img src=pok/anim/061.gif border=0>",$text);
    $text = str_replace("#062","<img src=pok/anim/062.gif border=0>",$text);
    $text = str_replace("#063","<img src=pok/anim/063.gif border=0>",$text);
    $text = str_replace("#064","<img src=pok/anim/064.gif border=0>",$text);
    $text = str_replace("#065","<img src=pok/anim/065.gif border=0>",$text);
    $text = str_replace("#066","<img src=pok/anim/066.gif border=0>",$text);
    $text = str_replace("#067","<img src=pok/anim/067.gif border=0>",$text);
    $text = str_replace("#068","<img src=pok/anim/068.gif border=0>",$text);
    $text = str_replace("#069","<img src=pok/anim/069.gif border=0>",$text);
    $text = str_replace("#070","<img src=pok/anim/070.gif border=0>",$text);
    $text = str_replace("#071","<img src=pok/anim/071.gif border=0>",$text);
    $text = str_replace("#072","<img src=pok/anim/072.gif border=0>",$text);
    $text = str_replace("#073","<img src=pok/anim/073.gif border=0>",$text);
    $text = str_replace("#074","<img src=pok/anim/074.gif border=0>",$text);
    $text = str_replace("#075","<img src=pok/anim/075.gif border=0>",$text);
    $text = str_replace("#076","<img src=pok/anim/076.gif border=0>",$text);
    $text = str_replace("#077","<img src=pok/anim/077.gif border=0>",$text);
    $text = str_replace("#078","<img src=pok/anim/078.gif border=0>",$text);
    $text = str_replace("#079","<img src=pok/anim/079.gif border=0>",$text);
    $text = str_replace("#080","<img src=pok/anim/080.gif border=0>",$text);
    $text = str_replace("#081","<img src=pok/anim/081.gif border=0>",$text);
    $text = str_replace("#082","<img src=pok/anim/082.gif border=0>",$text);
    $text = str_replace("#083","<img src=pok/anim/083.gif border=0>",$text);
    $text = str_replace("#084","<img src=pok/anim/084.gif border=0>",$text);
    $text = str_replace("#085","<img src=pok/anim/085.gif border=0>",$text);
    $text = str_replace("#086","<img src=pok/anim/086.gif border=0>",$text);
    $text = str_replace("#087","<img src=pok/anim/087.gif border=0>",$text);
    $text = str_replace("#088","<img src=pok/anim/088.gif border=0>",$text);
    $text = str_replace("#089","<img src=pok/anim/089.gif border=0>",$text);
    $text = str_replace("#090","<img src=pok/anim/090.gif border=0>",$text);
    $text = str_replace("#091","<img src=pok/anim/091.gif border=0>",$text);
    $text = str_replace("#092","<img src=pok/anim/092.gif border=0>",$text);
    $text = str_replace("#093","<img src=pok/anim/093.gif border=0>",$text);
    $text = str_replace("#094","<img src=pok/anim/094.gif border=0>",$text);
    $text = str_replace("#095","<img src=pok/anim/095.gif border=0>",$text);
    $text = str_replace("#096","<img src=pok/anim/096.gif border=0>",$text);
    $text = str_replace("#097","<img src=pok/anim/097.gif border=0>",$text);
    $text = str_replace("#098","<img src=pok/anim/098.gif border=0>",$text);
    $text = str_replace("#099","<img src=pok/anim/099.gif border=0>",$text);
    $text = str_replace("#100","<img src=pok/anim/100.gif border=0>",$text);
    $text = str_replace("#101","<img src=pok/anim/101.gif border=0>",$text);
    $text = str_replace("#102","<img src=pok/anim/102.gif border=0>",$text);
    $text = str_replace("#103","<img src=pok/anim/103.gif border=0>",$text);
    $text = str_replace("#104","<img src=pok/anim/104.gif border=0>",$text);
    $text = str_replace("#105","<img src=pok/anim/105.gif border=0>",$text);
    $text = str_replace("#106","<img src=pok/anim/106.gif border=0>",$text);
    $text = str_replace("#107","<img src=pok/anim/107.gif border=0>",$text);
    $text = str_replace("#108","<img src=pok/anim/108.gif border=0>",$text);
    $text = str_replace("#109","<img src=pok/anim/109.gif border=0>",$text);
    $text = str_replace("#110","<img src=pok/anim/110.gif border=0>",$text);
    $text = str_replace("#111","<img src=pok/anim/111.gif border=0>",$text);
    $text = str_replace("#112","<img src=pok/anim/112.gif border=0>",$text);
    $text = str_replace("#113","<img src=pok/anim/113.gif border=0>",$text);
    $text = str_replace("#114","<img src=pok/anim/114.gif border=0>",$text);
    $text = str_replace("#115","<img src=pok/anim/115.gif border=0>",$text);
    $text = str_replace("#116","<img src=pok/anim/116.gif border=0>",$text);
    $text = str_replace("#117","<img src=pok/anim/117.gif border=0>",$text);
    $text = str_replace("#118","<img src=pok/anim/118.gif border=0>",$text);
    $text = str_replace("#119","<img src=pok/anim/119.gif border=0>",$text);
    $text = str_replace("#120","<img src=pok/anim/120.gif border=0>",$text);
    $text = str_replace("#121","<img src=pok/anim/121.gif border=0>",$text);
    $text = str_replace("#122","<img src=pok/anim/122.gif border=0>",$text);
    $text = str_replace("#123","<img src=pok/anim/123.gif border=0>",$text);
    $text = str_replace("#124","<img src=pok/anim/124.gif border=0>",$text);
    $text = str_replace("#125","<img src=pok/anim/125.gif border=0>",$text);
    $text = str_replace("#126","<img src=pok/anim/126.gif border=0>",$text);
    $text = str_replace("#127","<img src=pok/anim/127.gif border=0>",$text);
    $text = str_replace("#128","<img src=pok/anim/128.gif border=0>",$text);
    $text = str_replace("#129","<img src=pok/anim/129.gif border=0>",$text);
    $text = str_replace("#130","<img src=pok/anim/130.gif border=0>",$text);
    $text = str_replace("#131","<img src=pok/anim/131.gif border=0>",$text);
    $text = str_replace("#132","<img src=pok/anim/132.gif border=0>",$text);
    $text = str_replace("#133","<img src=pok/anim/133.gif border=0>",$text);
    $text = str_replace("#134","<img src=pok/anim/134.gif border=0>",$text);
    $text = str_replace("#135","<img src=pok/anim/135.gif border=0>",$text);
    $text = str_replace("#136","<img src=pok/anim/136.gif border=0>",$text);
    $text = str_replace("#137","<img src=pok/anim/137.gif border=0>",$text);
    $text = str_replace("#138","<img src=pok/anim/138.gif border=0>",$text);
    $text = str_replace("#139","<img src=pok/anim/139.gif border=0>",$text);
    $text = str_replace("#140","<img src=pok/anim/140.gif border=0>",$text);
    $text = str_replace("#141","<img src=pok/anim/141.gif border=0>",$text);
    $text = str_replace("#142","<img src=pok/anim/142.gif border=0>",$text);
    $text = str_replace("#143","<img src=pok/anim/143.gif border=0>",$text);
    $text = str_replace("#144","<img src=pok/anim/144.gif border=0>",$text);
    $text = str_replace("#145","<img src=pok/anim/145.gif border=0>",$text);
    $text = str_replace("#146","<img src=pok/anim/146.gif border=0>",$text);
    $text = str_replace("#147","<img src=pok/anim/147.gif border=0>",$text);
    $text = str_replace("#148","<img src=pok/anim/148.gif border=0>",$text);
    $text = str_replace("#149","<img src=pok/anim/149.gif border=0>",$text);
    $text = str_replace("#150","<img src=pok/anim/150.gif border=0>",$text);
    $text = str_replace("#151","<img src=pok/anim/151.gif border=0>",$text);
    $text = str_replace("#152","<img src=pok/anim/152.gif border=0>",$text);
    $text = str_replace("#153","<img src=pok/anim/153.gif border=0>",$text);
    $text = str_replace("#154","<img src=pok/anim/154.gif border=0>",$text);
    $text = str_replace("#155","<img src=pok/anim/155.gif border=0>",$text);
    $text = str_replace("#156","<img src=pok/anim/156.gif border=0>",$text);
    $text = str_replace("#157","<img src=pok/anim/157.gif border=0>",$text);
    $text = str_replace("#158","<img src=pok/anim/158.gif border=0>",$text);
    $text = str_replace("#159","<img src=pok/anim/159.gif border=0>",$text);
    $text = str_replace("#160","<img src=pok/anim/160.gif border=0>",$text);
    $text = str_replace("#161","<img src=pok/anim/161.gif border=0>",$text);
    $text = str_replace("#162","<img src=pok/anim/162.gif border=0>",$text);
    $text = str_replace("#163","<img src=pok/anim/163.gif border=0>",$text);
    $text = str_replace("#164","<img src=pok/anim/164.gif border=0>",$text);
    $text = str_replace("#165","<img src=pok/anim/165.gif border=0>",$text);
    $text = str_replace("#166","<img src=pok/anim/166.gif border=0>",$text);
    $text = str_replace("#167","<img src=pok/anim/167.gif border=0>",$text);
    $text = str_replace("#168","<img src=pok/anim/168.gif border=0>",$text);
    $text = str_replace("#169","<img src=pok/anim/169.gif border=0>",$text);
    $text = str_replace("#170","<img src=pok/anim/170.gif border=0>",$text);
    $text = str_replace("#171","<img src=pok/anim/171.gif border=0>",$text);
    $text = str_replace("#172","<img src=pok/anim/172.gif border=0>",$text);
    $text = str_replace("#173","<img src=pok/anim/173.gif border=0>",$text);
    $text = str_replace("#174","<img src=pok/anim/174.gif border=0>",$text);
    $text = str_replace("#175","<img src=pok/anim/175.gif border=0>",$text);
    $text = str_replace("#176","<img src=pok/anim/176.gif border=0>",$text);
    $text = str_replace("#177","<img src=pok/anim/177.gif border=0>",$text);
    $text = str_replace("#178","<img src=pok/anim/178.gif border=0>",$text);
    $text = str_replace("#179","<img src=pok/anim/179.gif border=0>",$text);
    $text = str_replace("#180","<img src=pok/anim/180.gif border=0>",$text);
    $text = str_replace("#181","<img src=pok/anim/181.gif border=0>",$text);
    $text = str_replace("#182","<img src=pok/anim/182.gif border=0>",$text);
    $text = str_replace("#183","<img src=pok/anim/183.gif border=0>",$text);
    $text = str_replace("#184","<img src=pok/anim/184.gif border=0>",$text);
    $text = str_replace("#185","<img src=pok/anim/185.gif border=0>",$text);
    $text = str_replace("#186","<img src=pok/anim/186.gif border=0>",$text);
    $text = str_replace("#187","<img src=pok/anim/187.gif border=0>",$text);
    $text = str_replace("#188","<img src=pok/anim/188.gif border=0>",$text);
    $text = str_replace("#189","<img src=pok/anim/189.gif border=0>",$text);
    $text = str_replace("#190","<img src=pok/anim/190.gif border=0>",$text);
    $text = str_replace("#191","<img src=pok/anim/191.gif border=0>",$text);
    $text = str_replace("#192","<img src=pok/anim/192.gif border=0>",$text);
    $text = str_replace("#193","<img src=pok/anim/193.gif border=0>",$text);
    $text = str_replace("#194","<img src=pok/anim/194.gif border=0>",$text);
    $text = str_replace("#195","<img src=pok/anim/195.gif border=0>",$text);
    $text = str_replace("#196","<img src=pok/anim/196.gif border=0>",$text);
    $text = str_replace("#197","<img src=pok/anim/197.gif border=0>",$text);
    $text = str_replace("#198","<img src=pok/anim/198.gif border=0>",$text);
    $text = str_replace("#199","<img src=pok/anim/199.gif border=0>",$text);
    $text = str_replace("#200","<img src=pok/anim/200.gif border=0>",$text);
    $text = str_replace("#201","<img src=pok/anim/201.gif border=0>",$text);
    $text = str_replace("#202","<img src=pok/anim/202.gif border=0>",$text);
    $text = str_replace("#203","<img src=pok/anim/203.gif border=0>",$text);
    $text = str_replace("#204","<img src=pok/anim/204.gif border=0>",$text);
    $text = str_replace("#205","<img src=pok/anim/205.gif border=0>",$text);
    $text = str_replace("#206","<img src=pok/anim/206.gif border=0>",$text);
    $text = str_replace("#207","<img src=pok/anim/207.gif border=0>",$text);
    $text = str_replace("#208","<img src=pok/anim/208.gif border=0>",$text);
    $text = str_replace("#209","<img src=pok/anim/209.gif border=0>",$text);
    $text = str_replace("#210","<img src=pok/anim/210.gif border=0>",$text);
    $text = str_replace("#211","<img src=pok/anim/211.gif border=0>",$text);
    $text = str_replace("#212","<img src=pok/anim/212.gif border=0>",$text);
    $text = str_replace("#213","<img src=pok/anim/213.gif border=0>",$text);
    $text = str_replace("#214","<img src=pok/anim/214.gif border=0>",$text);
    $text = str_replace("#215","<img src=pok/anim/215.gif border=0>",$text);
    $text = str_replace("#216","<img src=pok/anim/216.gif border=0>",$text);
    $text = str_replace("#217","<img src=pok/anim/217.gif border=0>",$text);
    $text = str_replace("#218","<img src=pok/anim/218.gif border=0>",$text);
    $text = str_replace("#219","<img src=pok/anim/219.gif border=0>",$text);
    $text = str_replace("#220","<img src=pok/anim/220.gif border=0>",$text);
    $text = str_replace("#221","<img src=pok/anim/221.gif border=0>",$text);
    $text = str_replace("#222","<img src=pok/anim/222.gif border=0>",$text);
    $text = str_replace("#223","<img src=pok/anim/223.gif border=0>",$text);
    $text = str_replace("#224","<img src=pok/anim/224.gif border=0>",$text);
    $text = str_replace("#225","<img src=pok/anim/225.gif border=0>",$text);
    $text = str_replace("#226","<img src=pok/anim/226.gif border=0>",$text);
    $text = str_replace("#227","<img src=pok/anim/227.gif border=0>",$text);
    $text = str_replace("#228","<img src=pok/anim/228.gif border=0>",$text);
    $text = str_replace("#229","<img src=pok/anim/229.gif border=0>",$text);
    $text = str_replace("#230","<img src=pok/anim/230.gif border=0>",$text);
    $text = str_replace("#231","<img src=pok/anim/231.gif border=0>",$text);
    $text = str_replace("#232","<img src=pok/anim/232.gif border=0>",$text);
    $text = str_replace("#233","<img src=pok/anim/233.gif border=0>",$text);
    $text = str_replace("#234","<img src=pok/anim/234.gif border=0>",$text);
    $text = str_replace("#235","<img src=pok/anim/235.gif border=0>",$text);
    $text = str_replace("#236","<img src=pok/anim/236.gif border=0>",$text);
    $text = str_replace("#237","<img src=pok/anim/237.gif border=0>",$text);
    $text = str_replace("#238","<img src=pok/anim/238.gif border=0>",$text);
    $text = str_replace("#239","<img src=pok/anim/239.gif border=0>",$text);
    $text = str_replace("#240","<img src=pok/anim/240.gif border=0>",$text);
    $text = str_replace("#241","<img src=pok/anim/241.gif border=0>",$text);
    $text = str_replace("#242","<img src=pok/anim/242.gif border=0>",$text);
    $text = str_replace("#243","<img src=pok/anim/243.gif border=0>",$text);
    $text = str_replace("#244","<img src=pok/anim/244.gif border=0>",$text);
    $text = str_replace("#245","<img src=pok/anim/245.gif border=0>",$text);
    $text = str_replace("#246","<img src=pok/anim/246.gif border=0>",$text);
    $text = str_replace("#247","<img src=pok/anim/247.gif border=0>",$text);
    $text = str_replace("#248","<img src=pok/anim/248.gif border=0>",$text);
    $text = str_replace("#249","<img src=pok/anim/249.gif border=0>",$text);
    $text = str_replace("#250","<img src=pok/anim/250.gif border=0>",$text);
    $text = str_replace("#251","<img src=pok/anim/251.gif border=0>",$text);
    $text = str_replace("#252","<img src=pok/anim/252.gif border=0>",$text);
    $text = str_replace("#253","<img src=pok/anim/253.gif border=0>",$text);
    $text = str_replace("#254","<img src=pok/anim/254.gif border=0>",$text);
    $text = str_replace("#255","<img src=pok/anim/255.gif border=0>",$text);
    $text = str_replace("#256","<img src=pok/anim/256.gif border=0>",$text);
    $text = str_replace("#257","<img src=pok/anim/257.gif border=0>",$text);
    $text = str_replace("#258","<img src=pok/anim/258.gif border=0>",$text);
    $text = str_replace("#259","<img src=pok/anim/259.gif border=0>",$text);
    $text = str_replace("#260","<img src=pok/anim/260.gif border=0>",$text);
    $text = str_replace("#261","<img src=pok/anim/261.gif border=0>",$text);
    $text = str_replace("#262","<img src=pok/anim/262.gif border=0>",$text);
    $text = str_replace("#263","<img src=pok/anim/263.gif border=0>",$text);
    $text = str_replace("#264","<img src=pok/anim/264.gif border=0>",$text);
    $text = str_replace("#265","<img src=pok/anim/265.gif border=0>",$text);
    $text = str_replace("#266","<img src=pok/anim/266.gif border=0>",$text);
    $text = str_replace("#267","<img src=pok/anim/267.gif border=0>",$text);
    $text = str_replace("#268","<img src=pok/anim/268.gif border=0>",$text);
    $text = str_replace("#269","<img src=pok/anim/269.gif border=0>",$text);
    $text = str_replace("#270","<img src=pok/anim/270.gif border=0>",$text);
    $text = str_replace("#271","<img src=pok/anim/271.gif border=0>",$text);
    $text = str_replace("#272","<img src=pok/anim/272.gif border=0>",$text);
    $text = str_replace("#273","<img src=pok/anim/273.gif border=0>",$text);
    $text = str_replace("#274","<img src=pok/anim/274.gif border=0>",$text);
    $text = str_replace("#275","<img src=pok/anim/275.gif border=0>",$text);
    $text = str_replace("#276","<img src=pok/anim/276.gif border=0>",$text);
    $text = str_replace("#277","<img src=pok/anim/277.gif border=0>",$text);
    $text = str_replace("#278","<img src=pok/anim/278.gif border=0>",$text);
    $text = str_replace("#279","<img src=pok/anim/279.gif border=0>",$text);
    $text = str_replace("#280","<img src=pok/anim/280.gif border=0>",$text);
    $text = str_replace("#281","<img src=pok/anim/281.gif border=0>",$text);
    $text = str_replace("#282","<img src=pok/anim/282.gif border=0>",$text);
    $text = str_replace("#283","<img src=pok/anim/283.gif border=0>",$text);
    $text = str_replace("#284","<img src=pok/anim/284.gif border=0>",$text);
    $text = str_replace("#285","<img src=pok/anim/285.gif border=0>",$text);
    $text = str_replace("#286","<img src=pok/anim/286.gif border=0>",$text);
    $text = str_replace("#287","<img src=pok/anim/287.gif border=0>",$text);
    $text = str_replace("#288","<img src=pok/anim/288.gif border=0>",$text);
    $text = str_replace("#289","<img src=pok/anim/289.gif border=0>",$text);
    $text = str_replace("#290","<img src=pok/anim/290.gif border=0>",$text);
    $text = str_replace("#291","<img src=pok/anim/291.gif border=0>",$text);
    $text = str_replace("#292","<img src=pok/anim/292.gif border=0>",$text);
    $text = str_replace("#293","<img src=pok/anim/293.gif border=0>",$text);
    $text = str_replace("#294","<img src=pok/anim/294.gif border=0>",$text);
    $text = str_replace("#295","<img src=pok/anim/295.gif border=0>",$text);
    $text = str_replace("#296","<img src=pok/anim/296.gif border=0>",$text);
    $text = str_replace("#297","<img src=pok/anim/297.gif border=0>",$text);
    $text = str_replace("#298","<img src=pok/anim/298.gif border=0>",$text);
    $text = str_replace("#299","<img src=pok/anim/299.gif border=0>",$text);
    $text = str_replace("#300","<img src=pok/anim/300.gif border=0>",$text);
    $text = str_replace("#301","<img src=pok/anim/301.gif border=0>",$text);
    $text = str_replace("#302","<img src=pok/anim/302.gif border=0>",$text);
    $text = str_replace("#303","<img src=pok/anim/303.gif border=0>",$text);
    $text = str_replace("#304","<img src=pok/anim/304.gif border=0>",$text);
    $text = str_replace("#305","<img src=pok/anim/305.gif border=0>",$text);
    $text = str_replace("#306","<img src=pok/anim/306.gif border=0>",$text);
    $text = str_replace("#307","<img src=pok/anim/307.gif border=0>",$text);
    $text = str_replace("#308","<img src=pok/anim/308.gif border=0>",$text);
    $text = str_replace("#309","<img src=pok/anim/309.gif border=0>",$text);
    $text = str_replace("#310","<img src=pok/anim/310.gif border=0>",$text);
    $text = str_replace("#311","<img src=pok/anim/311.gif border=0>",$text);
    $text = str_replace("#312","<img src=pok/anim/312.gif border=0>",$text);
    $text = str_replace("#313","<img src=pok/anim/313.gif border=0>",$text);
    $text = str_replace("#314","<img src=pok/anim/314.gif border=0>",$text);
    $text = str_replace("#315","<img src=pok/anim/315.gif border=0>",$text);
    $text = str_replace("#316","<img src=pok/anim/316.gif border=0>",$text);
    $text = str_replace("#317","<img src=pok/anim/317.gif border=0>",$text);
    $text = str_replace("#318","<img src=pok/anim/318.gif border=0>",$text);
    $text = str_replace("#319","<img src=pok/anim/319.gif border=0>",$text);
    $text = str_replace("#320","<img src=pok/anim/320.gif border=0>",$text);
    $text = str_replace("#321","<img src=pok/anim/321.gif border=0>",$text);
    $text = str_replace("#322","<img src=pok/anim/322.gif border=0>",$text);
    $text = str_replace("#323","<img src=pok/anim/323.gif border=0>",$text);
    $text = str_replace("#324","<img src=pok/anim/324.gif border=0>",$text);
    $text = str_replace("#325","<img src=pok/anim/325.gif border=0>",$text);
    $text = str_replace("#326","<img src=pok/anim/326.gif border=0>",$text);
    $text = str_replace("#327","<img src=pok/anim/327.gif border=0>",$text);
    $text = str_replace("#328","<img src=pok/anim/328.gif border=0>",$text);
    $text = str_replace("#329","<img src=pok/anim/329.gif border=0>",$text);
    $text = str_replace("#330","<img src=pok/anim/330.gif border=0>",$text);
    $text = str_replace("#331","<img src=pok/anim/331.gif border=0>",$text);
    $text = str_replace("#332","<img src=pok/anim/332.gif border=0>",$text);
    $text = str_replace("#333","<img src=pok/anim/333.gif border=0>",$text);
    $text = str_replace("#334","<img src=pok/anim/334.gif border=0>",$text);
    $text = str_replace("#335","<img src=pok/anim/335.gif border=0>",$text);
    $text = str_replace("#336","<img src=pok/anim/336.gif border=0>",$text);
    $text = str_replace("#337","<img src=pok/anim/337.gif border=0>",$text);
    $text = str_replace("#338","<img src=pok/anim/338.gif border=0>",$text);
    $text = str_replace("#339","<img src=pok/anim/339.gif border=0>",$text);
    $text = str_replace("#340","<img src=pok/anim/340.gif border=0>",$text);
    $text = str_replace("#341","<img src=pok/anim/341.gif border=0>",$text);
    $text = str_replace("#342","<img src=pok/anim/342.gif border=0>",$text);
    $text = str_replace("#343","<img src=pok/anim/343.gif border=0>",$text);
    $text = str_replace("#344","<img src=pok/anim/344.gif border=0>",$text);
    $text = str_replace("#345","<img src=pok/anim/345.gif border=0>",$text);
    $text = str_replace("#346","<img src=pok/anim/346.gif border=0>",$text);
    $text = str_replace("#347","<img src=pok/anim/347.gif border=0>",$text);
    $text = str_replace("#348","<img src=pok/anim/348.gif border=0>",$text);
    $text = str_replace("#349","<img src=pok/anim/349.gif border=0>",$text);
    $text = str_replace("#350","<img src=pok/anim/350.gif border=0>",$text);
    $text = str_replace("#351","<img src=pok/anim/351.gif border=0>",$text);
    $text = str_replace("#352","<img src=pok/anim/352.gif border=0>",$text);
    $text = str_replace("#353","<img src=pok/anim/353.gif border=0>",$text);
    $text = str_replace("#354","<img src=pok/anim/354.gif border=0>",$text);
    $text = str_replace("#355","<img src=pok/anim/355.gif border=0>",$text);
    $text = str_replace("#356","<img src=pok/anim/356.gif border=0>",$text);
    $text = str_replace("#357","<img src=pok/anim/357.gif border=0>",$text);
    $text = str_replace("#358","<img src=pok/anim/358.gif border=0>",$text);
    $text = str_replace("#359","<img src=pok/anim/359.gif border=0>",$text);
    $text = str_replace("#360","<img src=pok/anim/360.gif border=0>",$text);
    $text = str_replace("#361","<img src=pok/anim/361.gif border=0>",$text);
    $text = str_replace("#362","<img src=pok/anim/362.gif border=0>",$text);
    $text = str_replace("#363","<img src=pok/anim/363.gif border=0>",$text);
    $text = str_replace("#364","<img src=pok/anim/364.gif border=0>",$text);
    $text = str_replace("#365","<img src=pok/anim/365.gif border=0>",$text);
    $text = str_replace("#366","<img src=pok/anim/366.gif border=0>",$text);
    $text = str_replace("#367","<img src=pok/anim/367.gif border=0>",$text);
    $text = str_replace("#368","<img src=pok/anim/368.gif border=0>",$text);
    $text = str_replace("#369","<img src=pok/anim/369.gif border=0>",$text);
    $text = str_replace("#370","<img src=pok/anim/370.gif border=0>",$text);
    $text = str_replace("#371","<img src=pok/anim/371.gif border=0>",$text);
    $text = str_replace("#372","<img src=pok/anim/372.gif border=0>",$text);
    $text = str_replace("#373","<img src=pok/anim/373.gif border=0>",$text);
    $text = str_replace("#374","<img src=pok/anim/374.gif border=0>",$text);
    $text = str_replace("#375","<img src=pok/anim/375.gif border=0>",$text);
    $text = str_replace("#376","<img src=pok/anim/376.gif border=0>",$text);
    $text = str_replace("#377","<img src=pok/anim/377.gif border=0>",$text);
    $text = str_replace("#378","<img src=pok/anim/378.gif border=0>",$text);
    $text = str_replace("#379","<img src=pok/anim/379.gif border=0>",$text);
    $text = str_replace("#380","<img src=pok/anim/380.gif border=0>",$text);
    $text = str_replace("#381","<img src=pok/anim/381.gif border=0>",$text);
    $text = str_replace("#382","<img src=pok/anim/382.gif border=0>",$text);
    $text = str_replace("#383","<img src=pok/anim/383.gif border=0>",$text);
    $text = str_replace("#384","<img src=pok/anim/384.gif border=0>",$text);
    $text = str_replace("#385","<img src=pok/anim/385.gif border=0>",$text);
    $text = str_replace("#386","<img src=pok/anim/386.gif border=0>",$text);
    $text = str_replace("#387","<img src=pok/anim/387.gif border=0>",$text);
    $text = str_replace("#388","<img src=pok/anim/388.gif border=0>",$text);
    $text = str_replace("#389","<img src=pok/anim/389.gif border=0>",$text);
    $text = str_replace("#390","<img src=pok/anim/390.gif border=0>",$text);
    $text = str_replace("#391","<img src=pok/anim/391.gif border=0>",$text);
    $text = str_replace("#392","<img src=pok/anim/392.gif border=0>",$text);
    $text = str_replace("#393","<img src=pok/anim/393.gif border=0>",$text);
    $text = str_replace("#394","<img src=pok/anim/394.gif border=0>",$text);
    $text = str_replace("#395","<img src=pok/anim/395.gif border=0>",$text);
    $text = str_replace("#396","<img src=pok/anim/396.gif border=0>",$text);
    $text = str_replace("#397","<img src=pok/anim/397.gif border=0>",$text);
    $text = str_replace("#398","<img src=pok/anim/398.gif border=0>",$text);
    $text = str_replace("#399","<img src=pok/anim/399.gif border=0>",$text);
    $text = str_replace("#400","<img src=pok/anim/400.gif border=0>",$text);
    $text = str_replace("#401","<img src=pok/anim/401.gif border=0>",$text);
    $text = str_replace("#402","<img src=pok/anim/402.gif border=0>",$text);
    $text = str_replace("#403","<img src=pok/anim/403.gif border=0>",$text);
    $text = str_replace("#404","<img src=pok/anim/404.gif border=0>",$text);
    $text = str_replace("#405","<img src=pok/anim/405.gif border=0>",$text);
    $text = str_replace("#406","<img src=pok/anim/406.gif border=0>",$text);
    $text = str_replace("#407","<img src=pok/anim/407.gif border=0>",$text);
    $text = str_replace("#408","<img src=pok/anim/408.gif border=0>",$text);
    $text = str_replace("#409","<img src=pok/anim/409.gif border=0>",$text);
    $text = str_replace("#410","<img src=pok/anim/410.gif border=0>",$text);
    $text = str_replace("#411","<img src=pok/anim/411.gif border=0>",$text);
    $text = str_replace("#412","<img src=pok/anim/412.gif border=0>",$text);
    $text = str_replace("#413","<img src=pok/anim/413.gif border=0>",$text);
    $text = str_replace("#414","<img src=pok/anim/414.gif border=0>",$text);
    $text = str_replace("#415","<img src=pok/anim/415.gif border=0>",$text);
    $text = str_replace("#416","<img src=pok/anim/416.gif border=0>",$text);
    $text = str_replace("#417","<img src=pok/anim/417.gif border=0>",$text);
    $text = str_replace("#418","<img src=pok/anim/418.gif border=0>",$text);
    $text = str_replace("#419","<img src=pok/anim/419.gif border=0>",$text);
    $text = str_replace("#420","<img src=pok/anim/420.gif border=0>",$text);
    $text = str_replace("#421","<img src=pok/anim/421.gif border=0>",$text);
    $text = str_replace("#422","<img src=pok/anim/422.gif border=0>",$text);
    $text = str_replace("#423","<img src=pok/anim/423.gif border=0>",$text);
    $text = str_replace("#424","<img src=pok/anim/424.gif border=0>",$text);
    $text = str_replace("#425","<img src=pok/anim/425.gif border=0>",$text);
    $text = str_replace("#426","<img src=pok/anim/426.gif border=0>",$text);
    $text = str_replace("#427","<img src=pok/anim/427.gif border=0>",$text);
    $text = str_replace("#428","<img src=pok/anim/428.gif border=0>",$text);
    $text = str_replace("#429","<img src=pok/anim/429.gif border=0>",$text);
    $text = str_replace("#430","<img src=pok/anim/430.gif border=0>",$text);
    $text = str_replace("#431","<img src=pok/anim/431.gif border=0>",$text);
    $text = str_replace("#432","<img src=pok/anim/432.gif border=0>",$text);
    $text = str_replace("#433","<img src=pok/anim/433.gif border=0>",$text);
    $text = str_replace("#434","<img src=pok/anim/434.gif border=0>",$text);
    $text = str_replace("#435","<img src=pok/anim/435.gif border=0>",$text);
    $text = str_replace("#436","<img src=pok/anim/436.gif border=0>",$text);
    $text = str_replace("#437","<img src=pok/anim/437.gif border=0>",$text);
    $text = str_replace("#438","<img src=pok/anim/438.gif border=0>",$text);
    $text = str_replace("#439","<img src=pok/anim/439.gif border=0>",$text);
    $text = str_replace("#440","<img src=pok/anim/440.gif border=0>",$text);
    $text = str_replace("#441","<img src=pok/anim/441.gif border=0>",$text);
    $text = str_replace("#442","<img src=pok/anim/442.gif border=0>",$text);
    $text = str_replace("#443","<img src=pok/anim/443.gif border=0>",$text);
    $text = str_replace("#444","<img src=pok/anim/444.gif border=0>",$text);
    $text = str_replace("#445","<img src=pok/anim/445.gif border=0>",$text);
    $text = str_replace("#446","<img src=pok/anim/446.gif border=0>",$text);
    $text = str_replace("#447","<img src=pok/anim/447.gif border=0>",$text);
    $text = str_replace("#448","<img src=pok/anim/448.gif border=0>",$text);
    $text = str_replace("#449","<img src=pok/anim/449.gif border=0>",$text);
    $text = str_replace("#450","<img src=pok/anim/450.gif border=0>",$text);
    $text = str_replace("#451","<img src=pok/anim/451.gif border=0>",$text);
    $text = str_replace("#452","<img src=pok/anim/452.gif border=0>",$text);
    $text = str_replace("#453","<img src=pok/anim/453.gif border=0>",$text);
    $text = str_replace("#454","<img src=pok/anim/454.gif border=0>",$text);
    $text = str_replace("#455","<img src=pok/anim/455.gif border=0>",$text);
    $text = str_replace("#456","<img src=pok/anim/456.gif border=0>",$text);
    $text = str_replace("#457","<img src=pok/anim/457.gif border=0>",$text);
    $text = str_replace("#458","<img src=pok/anim/458.gif border=0>",$text);
    $text = str_replace("#459","<img src=pok/anim/459.gif border=0>",$text);
    $text = str_replace("#460","<img src=pok/anim/460.gif border=0>",$text);
    $text = str_replace("#461","<img src=pok/anim/461.gif border=0>",$text);
    $text = str_replace("#462","<img src=pok/anim/462.gif border=0>",$text);
    $text = str_replace("#463","<img src=pok/anim/463.gif border=0>",$text);
    $text = str_replace("#464","<img src=pok/anim/464.gif border=0>",$text);
    $text = str_replace("#465","<img src=pok/anim/465.gif border=0>",$text);
    $text = str_replace("#466","<img src=pok/anim/466.gif border=0>",$text);
    $text = str_replace("#467","<img src=pok/anim/467.gif border=0>",$text);
    $text = str_replace("#468","<img src=pok/anim/468.gif border=0>",$text);
    $text = str_replace("#469","<img src=pok/anim/469.gif border=0>",$text);
    $text = str_replace("#470","<img src=pok/anim/470.gif border=0>",$text);
    $text = str_replace("#471","<img src=pok/anim/471.gif border=0>",$text);
    $text = str_replace("#472","<img src=pok/anim/472.gif border=0>",$text);
    $text = str_replace("#473","<img src=pok/anim/473.gif border=0>",$text);
    $text = str_replace("#474","<img src=pok/anim/474.gif border=0>",$text);
    $text = str_replace("#475","<img src=pok/anim/475.gif border=0>",$text);
    $text = str_replace("#476","<img src=pok/anim/476.gif border=0>",$text);
    $text = str_replace("#477","<img src=pok/anim/477.gif border=0>",$text);
    $text = str_replace("#478","<img src=pok/anim/478.gif border=0>",$text);
    $text = str_replace("#479","<img src=pok/anim/479.gif border=0>",$text);
    $text = str_replace("#480","<img src=pok/anim/480.gif border=0>",$text);
    $text = str_replace("#481","<img src=pok/anim/481.gif border=0>",$text);
    $text = str_replace("#482","<img src=pok/anim/482.gif border=0>",$text);
    $text = str_replace("#483","<img src=pok/anim/483.gif border=0>",$text);
    $text = str_replace("#484","<img src=pok/anim/484.gif border=0>",$text);
    $text = str_replace("#485","<img src=pok/anim/485.gif border=0>",$text);
    $text = str_replace("#486","<img src=pok/anim/486.gif border=0>",$text);
    $text = str_replace("#487","<img src=pok/anim/487.gif border=0>",$text);
    $text = str_replace("#488","<img src=pok/anim/488.gif border=0>",$text);
    $text = str_replace("#489","<img src=pok/anim/489.gif border=0>",$text);
    $text = str_replace("#490","<img src=pok/anim/490.gif border=0>",$text);
    $text = str_replace("#491","<img src=pok/anim/491.gif border=0>",$text);
    $text = str_replace("#492","<img src=pok/anim/492.gif border=0>",$text);
    $text = str_replace("#493","<img src=pok/anim/493.gif border=0>",$text);
    
    $text = str_replace("#494","<img src=pok/anim/494.png border=0>",$text);
    $text = str_replace("#495","<img src=pok/anim/495.png border=0>",$text);
    $text = str_replace("#496","<img src=pok/anim/496.png border=0>",$text);
    $text = str_replace("#497","<img src=pok/anim/497.png border=0>",$text);
    $text = str_replace("#498","<img src=pok/anim/498.png border=0>",$text);
    $text = str_replace("#499","<img src=pok/anim/499.png border=0>",$text);
    $text = str_replace("#500","<img src=pok/anim/500.png border=0>",$text);
    $text = str_replace("#501","<img src=pok/anim/501.png border=0>",$text);
    $text = str_replace("#502","<img src=pok/anim/502.png border=0>",$text);
    $text = str_replace("#503","<img src=pok/anim/503.png border=0>",$text);
    $text = str_replace("#504","<img src=pok/anim/504.png border=0>",$text);
    $text = str_replace("#505","<img src=pok/anim/505.png border=0>",$text);
    $text = str_replace("#506","<img src=pok/anim/506.png border=0>",$text);
    $text = str_replace("#507","<img src=pok/anim/507.png border=0>",$text);
    $text = str_replace("#508","<img src=pok/anim/508.png border=0>",$text);
    $text = str_replace("#509","<img src=pok/anim/509.png border=0>",$text);
    $text = str_replace("#510","<img src=pok/anim/510.png border=0>",$text);
    $text = str_replace("#511","<img src=pok/anim/511.png border=0>",$text);
    $text = str_replace("#512","<img src=pok/anim/512.png border=0>",$text);

} 
?>  