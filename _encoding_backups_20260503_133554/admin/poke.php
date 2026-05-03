<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../include/function/config.php';

$mselect_poke_base = select('SELECT id,title FROM poke_base ORDER BY id ASC');
$mselect_poke_har  = select('SELECT id_har FROM har ORDER BY id_har ASC');

function har_ter($id_h){
  $harakter = array(
    '1'=>"Р вЂ™Р ВµРЎРѓР ВµР В»РЎвЂ№Р в„–",'2'=>"Р вЂ™РЎвЂ№Р Р…Р С•РЎРѓР В»Р С‘Р Р†РЎвЂ№Р в„–",'3'=>"Р вЂ”Р В°РЎРѓРЎвЂљР ВµР Р…РЎвЂЎР С‘Р Р†РЎвЂ№Р в„–",'4'=>"Р С™РЎР‚Р С•РЎвЂљР С”Р С‘Р в„–",'5'=>"Р СљР С‘РЎР‚Р Р…РЎвЂ№Р в„–",
    '6'=>"Р СљРЎРЏР С–Р С”Р С‘Р в„–",'7'=>"Р СњР В°Р С–Р В»РЎвЂ№Р в„–",'8'=>"Р СњР В°Р С‘Р Р†Р Р…РЎвЂ№Р в„–",'9'=>"Р СњР В°РЎвЂ¦Р В°Р В»РЎРЉР Р…РЎвЂ№Р в„–",'10'=>"Р СњР ВµР В¶Р Р…РЎвЂ№Р в„–",
    '11'=>"Р СњР ВµР С—Р С•РЎРѓР В»РЎС“РЎв‚¬Р Р…РЎвЂ№Р в„–",'12'=>"Р СњР ВµР С—РЎР‚Р ВµР С”Р В»Р С•Р Р…Р Р…РЎвЂ№Р в„–",'13'=>"Р С›Р В±РЎвЂ№РЎвЂЎР Р…РЎвЂ№Р в„–",'14'=>"Р С›Р Т‘Р С‘Р Р…Р С•Р С”Р С‘Р в„–",
    '15'=>"Р С›Р В·Р С•РЎР‚Р Р…Р С•Р в„–",'16'=>"Р С›РЎРѓРЎвЂљР С•РЎР‚Р С•Р В¶Р Р…РЎвЂ№Р в„–",'17'=>"Р СџР С•РЎРѓР С—Р ВµРЎв‚¬Р Р…РЎвЂ№Р в„–",'18'=>"Р СџРЎР‚Р С‘РЎвЂЎРЎС“Р Т‘Р В»Р С‘Р Р†РЎвЂ№Р в„–",
    '19'=>"Р В Р В°РЎРѓР С—РЎС“РЎвЂ°Р ВµР Р…Р Р…РЎвЂ№Р в„–",'20'=>"Р В Р С•Р В±Р С”Р С‘Р в„–",'21'=>"Р РЋР ВµРЎР‚РЎРЉР ВµР В·Р Р…РЎвЂ№Р в„–",'22'=>"Р РЋР С”РЎР‚Р С•Р СР Р…РЎвЂ№Р в„–",
    '23'=>"Р РЋР СР ВµР В»РЎвЂ№Р в„–",'24'=>"Р РЋР С—Р С•Р С”Р С•Р в„–Р Р…РЎвЂ№Р в„–",'25'=>"Р РЋРЎвЂљРЎР‚Р ВµР СР С‘РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р в„–",'26'=>"Р СћР С‘РЎвЂ¦Р С‘Р в„–"
  );
  return $harakter[$id_h];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
 <link rel="stylesheet" href="admin-poke.css">
  <meta charset="UTF-8">
  <title>Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
    }
    .container { max-width: 1200px; margin: 0 auto; }
    .header { text-align: center; margin-bottom: 30px; }
    .card { background: rgba(0,0,0,0.6); padding: 20px; margin-bottom: 20px; border-radius: 10px; }
    .card-title { font-size: 1.6em; margin-bottom: 15px; text-align: center; color: #ffcc00; }
    label { display: block; margin-top: 10px; color: #ffcc00; }
    input[type="text"], select {
      width: 100%; padding: 10px; margin-top: 5px;
      border: 1px solid #4d94ff; background: #1e1e3c;
      color: #fff; border-radius: 5px;
    }
    input[type="submit"], button {
      margin-top: 15px;
      padding: 10px 25px;
      background: linear-gradient(135deg, #ffcc00, #ff9900);
      border: none; border-radius: 5px;
      font-weight: bold; color: #000;
      cursor: pointer;
    }
    table { width: 100%; margin-top: 20px; border-collapse: collapse; }
    th, td {
      padding: 10px; text-align: center;
      border: 1px solid #4d94ff;
      background-color: rgba(0, 0, 0, 0.5);
    }
    th { background: #ffcc00; color: #000; }
    .message { background: green; padding: 10px; text-align: center; margin: 10px 0; border-radius: 5px; }
    a { color: #ffcc00; text-decoration: none; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1><i class="fas fa-dragon"></i> Р С’Р Т‘Р СР С‘Р Р…Р С‘РЎРѓРЎвЂљРЎР‚Р С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р†</h1>
    <p>Р Р€Р С—РЎР‚Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°Р СР С‘ Р С‘ Р С‘РЎвЂ¦ Р Р†Р В»Р В°Р Т‘Р ВµР В»РЎРЉРЎвЂ Р В°Р СР С‘</p>
  </div>
<?php
// Р вЂ™Р ВР вЂќР С’Р В§Р С’ Р СџР С›Р С™Р вЂўР СљР С›Р СњР С’
if (isset($_POST['base_id'], $_POST['user'], $_POST['har'], $_POST['gen'], $_POST['spar'], $_POST['shiny'])) {
  $base_id = $_POST['base_id']; 
  $user_poke = $_POST['user']; 
  $har_ter = $_POST['har']; 
  $gen = $_POST['gen'];  
  $sparka = $_POST['spar']; 
  $tipes = $_POST['shiny']; 

  if(!$base_id || !$user_poke || !$har_ter || !$gen || !$sparka || !$tipes) {
    echo "<div class='message'>Р В§Р ВµР С–Р С•-РЎвЂљР С• Р Р…Р Вµ РЎвЂ¦Р Р†Р В°РЎвЂљР В°Р ВµРЎвЂљ.</div>";
  } else {
    $pr_poke  = first('SELECT * FROM poke_base WHERE id=%d',$base_id);
    $pr_hara  = first('SELECT id_har FROM har WHERE id_har=%d',$har_ter);
    $pr_usera = first('SELECT id,login FROM users WHERE id=%d AND groups<>7 AND groups<>10',$user_poke);

    $pr_sparki = ($sparka == 1 || $sparka == 2);
    $pr_gen = in_array($gen, array(1,2,3,4,5,6,7));

    if($pr_poke && $pr_hara && $pr_usera && $pr_sparki && $pr_gen) {
      $sparka_res = ($sparka == 1) ? 0 : 1;
      $gen_map = array(1=>28, 2=>32, 3=>38, 4=>41, 5=>45, 6=>50, 7=>15);
      $gen_res = $gen_map[$gen];
      $tip_sh = $tipes == 2 ? 'shine' : 'normal';
      $dop_name_2 = $tipes == 2 ? '<span class="pokesShiny">'.$pr_poke['title'].' - <b>Shiny</b></span>' : $pr_poke['title'];

      $lvl = 1; $ev = 0; $sex = mt_rand(1,2); $iv = $gen_res;
      $har = first('SELECT * FROM har WHERE id_har=%d',$har_ter);

      $hp    = (($iv+($pr_poke['hp']*2)+($ev/4)+100)*($lvl/100))+10;
      $atk   = ((($iv+($pr_poke['atk']*2)+($ev/4))*($lvl/100))+5)*$har['atk'];
      $def   = ((($iv+($pr_poke['def']*2)+($ev/4))*($lvl/100))+5)*$har['def'];
      $satk  = ((($iv+($pr_poke['satk']*2)+($ev/4))*($lvl/100))+5)*$har['satk'];
      $sdef  = ((($iv+($pr_poke['sdef']*2)+($ev/4))*($lvl/100))+5)*$har['sdef'];
      $speed = ((($iv+($pr_poke['speed']*2)+($ev/4))*($lvl/100))+5)*$har['speed'];

      $startyes = ($user_poke == 2) ? 1 : 0;
      if ($user_poke == 2) $sparka_res = 1;

      $ides = insert('pok_user', array(
        'basenum'=>$base_id, 'names'=>$dop_name_2, 'users'=>$user_poke, 'har'=>$har['id_har'], 'lvl'=>$lvl,
        'evcount'=>0, 'sex'=>$sex, 'hp_my'=>$hp, 'hp_max'=>$hp, 'atk'=>$atk, 'def'=>$def,
        'satk'=>$satk, 'sdef'=>$sdef, 'speed'=>$speed, 'hp_ev'=>'0', 'atk_ev'=>'0',
        'def_ev'=>'0', 'satk_ev'=>'0', 'sdef_ev'=>'0', 'speed_ev'=>'0', 'hp_iv'=>$iv,
        'atk_iv'=>$iv, 'def_iv'=>$iv, 'satk_iv'=>$iv, 'sdef_iv'=>$iv, 'speed_iv'=>$iv,
        'exp'=>0, 'active'=>0, 'startepoke'=>0, 'startone'=>$startyes,
        'reproduction'=>$sparka_res, 'happy'=>0, 'datemay'=>date('Y-m-d H:i:s'),
        'usersone'=>$_SESSION['id'], 'tips'=>$tip_sh
      ));

      if ($ides) {
        $messa = "<b style='color: gold'>Р СџР С•Р С”Р ВµР СР С•Р Р… #".$dop_name_2." РЎС“РЎРѓР С—Р ВµРЎв‚¬Р Р…Р С• Р Р†РЎвЂ№Р Т‘Р В°Р Р… Р С‘Р С–РЎР‚Р С•Р С”РЎС“ ".$pr_usera['login']."!</b>";
        $log = date('Y-m-d')." | ".$_SESSION['login']." | Р вЂ™РЎвЂ№Р Т‘Р В°Р В» ".$pr_usera['login']."(".$user_poke.") | Р Сџ: ".$pr_poke['title']." | ID: ".$ides;
        plus_user_pok($log);
      } else {
        $messa = "Р С›РЎв‚¬Р С‘Р В±Р С”Р В° Р С—РЎР‚Р С‘ Р Р†РЎРѓРЎвЂљР В°Р Р†Р С”Р Вµ Р Р† Р вЂР вЂќ!";
      }

      echo "<div class='message'>{$messa}</div>";
    }
  }
}
?>

<div class="card">
  <h2 class="card-title">Р СџРЎР‚Р С‘РЎРѓР Р†Р С•Р С‘РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎР‹</h2>
  <form method="post" action="">
    <label>Р СџР С•Р С”Р ВµР СР С•Р Р…:</label>
    <select name="base_id">
      <?php foreach($mselect_poke_base as $row): ?>
        <option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
      <?php endforeach; ?>
    </select>

    <label>ID Р С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ:</label>
    <input type="text" name="user">

    <label>Р ТђР В°РЎР‚Р В°Р С”РЎвЂљР ВµРЎР‚:</label>
    <select name="har">
      <?php foreach($mselect_poke_har as $row): ?>
        <option value="<?php echo $row['id_har']; ?>"><?php echo har_ter($row['id_har']); ?></option>
      <?php endforeach; ?>
    </select>

    <label>Р вЂњР ВµР Р…РЎвЂ№:</label>
    <select name="gen">
      <option value="1">28</option>
      <option value="2">32</option>
      <option value="3">38</option>
      <option value="4">41</option>
      <option value="5">45</option>
      <option value="6">50</option>
      <option value="7">15</option>
    </select>

    <label>Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ:</label>
    <select name="spar">
      <option value="1">Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•</option>
      <option value="2">Р СњР ВµР Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•</option>
    </select>

    <label>Р СћР С‘Р С—:</label>
    <select name="shiny">
      <option value="1">Р С›Р В±РЎвЂ№РЎвЂЎР Р…РЎвЂ№Р в„–</option>
      <option value="2">Shiny</option>
    </select>

    <input type="submit" value="Р РЋР С•Р В·Р Т‘Р В°РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°">
  </form>
</div>
<div class="card">
  <h2 class="card-title">Р СџР ВµРЎР‚Р ВµР Т‘Р В°РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р С—Р С• ID</h2>
  <form method="post" action="">
    <label>ID Р СџР С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљР ВµР В»РЎРЏ:</label>
    <input type="text" name="id_users">
    
    <label>ID Р СџР С•Р С”Р ВµР СР С•Р Р…Р В°:</label>
    <input type="text" name="id_poke_up">

    <input type="submit" value="Р СџР ВµРЎР‚Р ВµР Т‘Р В°РЎвЂљРЎРЉ">
  </form>
</div>

<div class="card">
  <h2 class="card-title">Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В° Р С‘Р В· Р В±Р В°Р В·РЎвЂ№</h2>
  <form method="post" action="">
    <label>ID Р СџР С•Р С”Р ВµР СР С•Р Р…Р В°:</label>
    <input type="text" name="DeletId">

    <input type="submit" value="Р Р€Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ">
  </form>
</div>

<div class="card">
  <h2 class="card-title">Р СџР С•Р С‘РЎРѓР С” Р С—Р С• Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚Р В°Р С</h2>
  <form action="" method="POST" name="forms" id="forms">
    <label>ID Р вЂ™Р В»Р В°Р Т‘Р ВµР В»РЎРЉРЎвЂ Р В°:</label>
    <input type="text" name="users" value="FALSE" onclick="if(this.value=='FALSE')this.value='';" onblur="if(this.value=='')this.value='FALSE';">

    <label>Р вЂР В°Р В·Р С•Р Р†РЎвЂ№Р в„– Р Р…Р С•Р СР ВµРЎР‚ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</label>
    <input type="text" name="base" value="FALSE" onclick="if(this.value=='FALSE')this.value='';" onblur="if(this.value=='')this.value='FALSE';">

    <label>ID Р СџР С•Р С”Р ВµР СР С•Р Р…Р В°:</label>
    <input type="text" name="idpoke" value="FALSE" onclick="if(this.value=='FALSE')this.value='';" onblur="if(this.value=='')this.value='FALSE';">

    <label>Р Р€РЎР‚Р С•Р Р†Р ВµР Р…РЎРЉ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°:</label>
    <input type="text" name="lvl" value="FALSE" onclick="if(this.value=='FALSE')this.value='';" onblur="if(this.value=='')this.value='FALSE';">

    <button type="button" onclick="location.href='/game.php?go=admingo&do=pok&users='+forms['users'].value+'&base='+forms['base'].value+'&idpoke='+forms['idpoke'].value+'&lvl='+forms['lvl'].value;">Р СњР В°Р в„–РЎвЂљР С‘</button>
  </form>
</div>

<div class="card">
  <h2 class="card-title">Р СћР В°Р В±Р В»Р С‘РЎвЂ Р В° Р С—Р С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р С‘ Р С‘РЎвЂ¦ Р Р†Р В»Р В°Р Т‘Р ВµР В»РЎРЉРЎвЂ Р ВµР Р†</h2>
  <table class="pokemon-table">
    <tr>
      <th>ID</th>
      <th>Р вЂР В°Р В·Р С•Р Р†РЎвЂ№Р в„– ID</th>
      <th>Р ВР СРЎРЏ Р С—Р С•Р С”Р ВµР СР С•Р Р…Р В°</th>
      <th>Lvl</th>
      <th>Р вЂ™Р В»Р В°Р Т‘Р ВµР В»Р ВµРЎвЂ </th>
      <th>Р В Р В°Р В·Р Р†Р ВµР Т‘Р ВµР Р…Р С‘Р Вµ</th>
    </tr>
<?php
$get = '/game.php?go=admingo&do=pok'; 
if (!empty($_GET['users']) || !empty($_GET['base']) || !empty($_GET['idpoke']) || !empty($_GET['lvl'])) {
  $dopZ = ' ';
  $get .= '&users='.$_GET['users'].'&base='.$_GET['base'].'&idpoke='.$_GET['idpoke'].'&lvl='.$_GET['lvl'];
  if($_GET['users'] != 'FALSE') $dopZ .= ' AND users='.obr_chis($_GET['users']);
  if($_GET['base']  != 'FALSE') $dopZ .= ' AND basenum='.obr_chis($_GET['base']);
  if($_GET['idpoke']!= 'FALSE') $dopZ .= ' AND id='.obr_chis($_GET['idpoke']);
  if($_GET['lvl']   != 'FALSE') $dopZ .= ' AND lvl='.obr_chis($_GET['lvl']);

  include('itemsinpage.class2.php');
  $totalpages = first('SELECT COUNT(*) as Total FROM pok_user WHERE id>1 '.$dopZ);
  $itemsinpage = new Itemsinpage($totalpages['Total']);
  $query = select('SELECT * FROM pok_user WHERE id>1 '.$dopZ.' ORDER BY basenum ASC LIMIT %d,%d',
    $itemsinpage->get('Start'),$itemsinpage->get('Limit'));
} else {
  $query = select('SELECT * FROM pok_user ORDER BY id DESC LIMIT 100'); 
}

if(!empty($dopZ)) echo '<p><a href="/game.php?go=admingo&do=pok" class="reset-link">Р РЋР В±РЎР‚Р С•РЎРѓР С‘РЎвЂљРЎРЉ Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№ Р С—Р С•Р С‘РЎРѓР С”Р В°</a></p>';

if($query){
  foreach($query as $info){
    $base = str_pad($info['basenum'], 3, "0", STR_PAD_LEFT);
    echo "<tr>";
    echo "<td><a href='/game.php?go=admingo&do=info_pokes&id_pokem={$info['id']}' target='_blank'>{$info['id']}</a></td>";
    echo "<td>#{$base}</td>";
    echo "<td><a href='javascript:' onClick=\"window.open('/game.php?go=pokedex&id={$info['basenum']}','pokedex','width=550,height=550,scrollbars=yes');return true;\"><i class='fas fa-book pokedex-icon'></i></a> {$info['names']}</td>";
    echo "<td>{$info['lvl']}</td>";
    echo "<td>".color_group_users($info['users'])."</td>";
    echo "<td>".($info['reproduction'] > 0 ? "<span style='color:#ff6666;'>Р СњР ВµР Т‘Р С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•</span>" : "<span style='color:#66ff66;'>Р вЂќР С•РЎРѓРЎвЂљРЎС“Р С—Р Р…Р С•</span>")."</td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='6'>Р СџР С•Р С”Р ВµР СР С•Р Р…Р С•Р Р† Р Р…Р Вµ Р Р…Р В°Р в„–Р Т‘Р ВµР Р…Р С•!</td></tr>";
}
?>
  </table>
<?php
if(!empty($itemsinpage)){
  $data = $itemsinpage->SmartyArr();
  echo "<div class='pagination'>";
  for($i=0;$i<sizeof($data['Count']);$i++){
    $page = $data['Count'][$i][1];
    $label = $data['Count'][$i][0];
    if($page != $_GET['page']){
      echo "<a class='page-link' href='".$get."&page=".$page."'>".$label."</a>";
    } else {
      echo "<span class='page-link active'>".$label."</span>";
    }
  }
  echo "</div>";
}
?>
</div>

</div> <!-- container -->
</body>
</html>
