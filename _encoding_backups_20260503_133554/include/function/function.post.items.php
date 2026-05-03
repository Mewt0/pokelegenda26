<?php
include ("funtction.minus.item.php"); 

if (isset($_POST['add']) && isset($_POST['itID']) && $_POST['itID'] > 0) {
    if ($myrow['pve'] > 0 || $myrow['pvp'] > 0 || $myrow['trade'] > 0) {
        $_SESSION['TEXT_ITEMS_ERROR'] = "Р СџРЎР‚Р ВµР В¶Р Т‘Р Вµ РЎвЂЎР ВµР С РЎРѓР С•Р Р†Р ВµРЎР‚РЎв‚¬Р С‘РЎвЂљРЎРЉ Р С”Р В°Р С”Р С•Р Вµ-Р В»Р С‘Р В±Р С• Р Т‘Р ВµР в„–РЎРѓРЎвЂљР Р†Р С‘Р Вµ РЎРѓ Р С‘Р Р…Р Р†Р ВµР Р…РЎвЂљР В°РЎР‚Р ВµР С, Р вЂ™Р В°Р С Р Р…Р ВµР С•Р В±РЎвЂ¦Р С•Р Т‘Р С‘Р СР С• Р В·Р В°Р С”Р С•Р Р…РЎвЂЎР С‘РЎвЂљРЎРЉ Р В±Р С•Р в„–";
        die("<script>location.href='/game.php?go=items'</script>");
    }

    $tip    = $_POST['add'];
    $cools  = (int)$_POST['amount'];
    $itemId = (int)$_POST['itID'];
    $pokeId = (int)$_POST['pokes'];

    if ($tip == "use") {
        $upes = false;
        if (empty($itemId) || empty($pokeId)) die(no_href_item());

        $invent   = first('SELECT item_id,timers,user_id,count,dattimer FROM items_users WHERE id=%d AND user_id=%d', $itemId, $_SESSION['id']);
        $pokemon  = first('SELECT * FROM pok_user WHERE id=%d AND users=%d AND active=1', $pokeId, $_SESSION['id']);
        $issetInv = first('SELECT id,uses,category,dopolnen FROM items WHERE id=%d', $invent['item_id']);

        if ($issetInv['uses'] <= 0) die(no_href_item());
        if (empty($invent) || empty($pokemon) || empty($issetInv)) die(no_href_item());
        if (($invent['user_id'] != $_SESSION['id']) || ($pokemon['users'] != $_SESSION['id']) || $cools > $invent['count']) die(no_href_item());
        if ($invent['timers'] != "not" && $invent['timers'] > time()) { $_SESSION['TEXT_ITEMS_ERROR'] = "Р РЋР ВµР в„–РЎвЂЎР В°РЎРѓ Р Р†РЎвЂ№ Р Р…Р Вµ Р СР С•Р В¶Р ВµРЎвЂљР Вµ Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·Р С•Р Р†Р В°РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•РЎвЂљ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ"; die(no_href_item()); }
        if ($invent['dattimer'] != "not" && $invent['dattimer'] <= time()) { $_SESSION['TEXT_ITEMS_ERROR'] = "Р Р€ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР В° Р С‘РЎРѓРЎвЂљР ВµР С” РЎРѓРЎР‚Р С•Р С” Р С–Р С•Р Т‘Р Р…Р С•РЎРѓРЎвЂљР С‘"; die(no_href_item()); }

        if ($issetInv['category'] == 1) $upes = stimul($pokeId, $issetInv['id'], $pokemon['hp_my'], $pokemon['hp_max']);
        if ($issetInv['category'] == 2) $upes = podarok($issetInv['id']);
        if ($issetInv['category'] == 3) $upes = confetka($issetInv['dopolnen'], true, $pokeId);
        if ($issetInv['category'] == 4) $upes = itemHarPoke(2, 13, $pokeId);
        if ($issetInv['category'] == 5) $upes = itemLevelPoke($pokeId);
        if ($issetInv['category'] == 6) $upes = itemNormalStatus($pokeId, $issetInv['dopolnen']);
        if ($issetInv['category'] == 7) $upes = evolutionItems($pokeId, $issetInv['id']);
        if ($issetInv['category'] == 8) $upes = podarokNewUser($issetInv['id']);
        if ($issetInv['category'] == 9) $upes = shynyPoke($pokemon, $issetInv['dopolnen'], $issetInv['id']);
        if ($issetInv['category'] == 10) $upes = smyleChat($issetInv['id'], $issetInv['dopolnen']);

        if ($upes == true) minus_item($cools, $issetInv['id'], $itemId);
        die(no_href_item());
    }
    elseif ($tip == "dress") {
        if (empty($itemId) || empty($pokeId)) die(no_href_item());

        $invent   = first('SELECT item_id,user_id,count FROM items_users WHERE id=%d AND user_id=%d', $itemId, $_SESSION['id']);
        $pokemon  = first('SELECT id,users FROM pok_user WHERE id=%d AND users=%d AND active=1', $pokeId, $_SESSION['id']);
        $issetInv = first('SELECT id,dress,timesnapoke FROM items WHERE id=%d', $invent['item_id']);

        if (empty($invent) || empty($pokemon) || empty($issetInv) || $issetInv['dress'] <= 0) die(no_href_item());
        if (($invent['user_id'] != $_SESSION['id']) || ($pokemon['users'] != $_SESSION['id']) || $cools > $invent['count']) die(no_href_item());

        $x001 = "not";
        $poke_item = first('SELECT * FROM items_poke WHERE id_poke=%d', $pokemon['id']);

        if (!$poke_item) {
            if ($issetInv['timesnapoke'] > 0) $x001 = time() + (60 * 60 * 24 * $issetInv['timesnapoke']);
            insert('items_poke', array('id_poke' => $pokemon['id'], 'id_items' => $issetInv['id'], 'datetime' => $x001));
            minus_item($cools, $issetInv['id'], $itemId);
        } else {
            if ($poke_item['datetime'] == "not") plus_item(1, $poke_item['id_items']);
            if ($poke_item['datetime'] != "not") update('items_poke', array('datetime' => 'not'), 'id_poke=' . (int)$pokemon['id']);
            if ($issetInv['timesnapoke'] > 0) $x001 = time() + (60 * 60 * 24 * $issetInv['timesnapoke']);
            update('items_poke', array('id_items' => $issetInv['id'], 'datetime' => $x001), 'id_poke=' . (int)$pokemon['id']);
            minus_item($cools, $issetInv['id'], $itemId);
        }

        die(no_href_item());
    }
    elseif ($tip == "drop") {
        if (empty($cools) || empty($itemId)) die(no_href_item());
        $invent   = first('SELECT item_id,user_id,count FROM items_users WHERE id=%d AND user_id=%d', $itemId, $_SESSION['id']);
        $issetInv = first('SELECT id,delet FROM items WHERE id=%d', $invent['item_id']);

        if ($invent['user_id'] != $_SESSION['id'] || $cools > $invent['count']) die(no_href_item());
        if ($issetInv['delet'] != 0) { $_SESSION['TEXT_ITEMS_ERROR'] = "Р СњР ВµР В»РЎРЉР В·РЎРЏ РЎС“Р Т‘Р В°Р В»Р С‘РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•РЎвЂљ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ"; die(no_href_item()); }

        minus_item($cools, $issetInv['id'], $itemId);
        die(no_href_item());
    }
    elseif ($tip == "clan") {
        $_SESSION['TEXT_ITEMS_ERROR'] = "Р СњР В° Р Т‘Р В°Р Р…Р Р…РЎвЂ№Р в„– Р СР С•Р СР ВµР Р…РЎвЂљ РЎРЊРЎвЂљР В° РЎвЂћРЎС“Р Р…Р С”РЎвЂ Р С‘РЎРЏ Р Р…Р Вµ РЎР‚Р В°Р В±Р С•РЎвЂљР В°Р ВµРЎвЂљ";
        die(no_href_item());

        $cools = (int)$_POST['amount2'];
        if (empty($cools) || empty($itemId)) die(no_href_item());
        if (users_conect('clanid') <= 0) die(no_href_item());

        $invent   = first('SELECT item_id,user_id,count FROM items_users WHERE id=%d AND user_id=%d', $itemId, $_SESSION['id']);
        $issetInv = first('SELECT id,torg FROM items WHERE id=%d', $invent['item_id']);

        if ($invent['user_id'] != $_SESSION['id'] || $cools > $invent['count']) die(no_href_item());
        if ($issetInv['torg'] != 0) { $_SESSION['TEXT_ITEMS_ERROR'] = "Р СњР ВµР В»РЎРЉР В·РЎРЏ Р С—Р ВµРЎР‚Р ВµР Т‘Р В°РЎвЂљРЎРЉ/Р С—РЎР‚Р С•Р Т‘Р В°РЎвЂљРЎРЉ РЎРЊРЎвЂљР С•РЎвЂљ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ"; die(no_href_item()); }

        plus_item_clan($cools, $invent['id'], users_conect('clanid'));
        minus_item($cools, $issetInv['id'], $itemId);
        die(no_href_item());
    }

    die(no_href_item());
}
?>