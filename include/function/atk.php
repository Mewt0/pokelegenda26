<?php
if (!empty($_GET['atk']) && ($_GET['atk'] == 1 || $_GET['atk'] == 2 || $_GET['atk'] == 3 || $_GET['atk'] == 4)) {

  function id_atc($x, $id) {
    if ($x == '1') $b = 'a_id';
    elseif ($x == '2') $b = 'b_id';
    elseif ($x == '3') $b = 'c_id';
    elseif ($x == '4') $b = 'd_id';
    else $b = 'a_id';

    $a = first('SELECT ' . $b . ' FROM attac_my_poke apu inner join attac_power apw on apu.' . $b . '=apw.atac_id where apu.pok_id=%d AND apu.' . $b . '>0', $id);
    if (!$a) $a[$b] = false;
    return $a[$b];
  }

  if ($_GET['atk'] == 1) $chis = 1;
  elseif ($_GET['atk'] == 2) $chis = 2;
  elseif ($_GET['atk'] == 3) $chis = 3;
  elseif ($_GET['atk'] == 4) $chis = 4;

  $timer = 120;

  if (!isset($compulsionone)) $compulsionone = 0;
  if (!isset($compulsiontwo)) $compulsiontwo = 0;

  if ($usersbattlone == $_SESSION['id'] && $attacOne == 0 && poke_info($pokeTwo, 'hp_my') > 0 && $compulsionone == 0) {
    if (poke_info($pokeOne, 'hp_my') > 0) {
      $pokeAt = obrid($pokeOne);
      $pokeAt = $pokeAt['id'];
      $ataka = id_atc($chis, $pokeAt);
      $pp = atac_pp($chis, $pokeAt);
      if (!empty($ataka) && $pp > 0) {
        update('battles', array('attac_1' => $ataka, 'time_2' => $timer), 'id=' . (int)$pve_id);
        $Data_new = "";
        update('battles', array('dates' => $Data_new), 'id=' . (int)$pve_id);
        no_href_locs();
      } else {
        no_href_locs();
      }
    } else {
      no_href_locs();
    }
  } elseif ($usersbattltwo == $_SESSION['id'] && $attacTwo == 0 && poke_info($pokeOne, 'hp_my') > 0 && $compulsiontwo == 0) {
    if (poke_info($pokeTwo, 'hp_my') > 0) {
      $pokeAt = obrid($pokeTwo);
      $pokeAt = $pokeAt['id'];
      $ataka = id_atc($chis, $pokeAt);
      $pp = atac_pp($chis, $pokeAt);
      if (!empty($ataka) && $pp > 0) {
        update('battles', array('attac_2' => $ataka, 'time_1' => $timer), 'id=' . (int)$pve_id);
        $Data_new = "";
        update('battles', array('dates' => $Data_new), 'id=' . (int)$pve_id);
        no_href_locs();
      } else {
        no_href_locs();
      }
    } else {
      no_href_locs();
    }
  } else {
    no_href_locs();
  }
}
?>
