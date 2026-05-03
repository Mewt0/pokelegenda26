<?php
// Р вЂР ВµР В·Р С•Р С—Р В°РЎРѓР Р…Р В°РЎРЏ РЎР‚Р ВµР В°Р В»Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ minus_item Р С‘ plus_item РЎРѓ Р В·Р В°РЎвЂ°Р С‘РЎвЂљР С•Р в„– Р С•РЎвЂљ Р Т‘РЎС“Р В±Р В»Р С‘РЎР‚Р С•Р Р†Р В°Р Р…Р С‘РЎРЏ

if (!function_exists('minus_item')) {
    function minus_item($count, $item_id, $user_item_id = null) {
        if (!$count || !$item_id) return false;

        if ($user_item_id) {
            // Р вЂўРЎРѓР В»Р С‘ РЎС“Р С”Р В°Р В·Р В°Р Р… Р С”Р С•Р Р…Р С”РЎР‚Р ВµРЎвЂљР Р…РЎвЂ№Р в„– ID РЎРѓРЎвЂљРЎР‚Р С•Р С”Р С‘ Р С‘Р В· items_users РІР‚вЂќ Р С•Р В±Р Р…Р С•Р Р†Р В»РЎРЏР ВµР С Р С—Р С• Р Р…Р ВµР СРЎС“
            $exist = first('SELECT id, count FROM items_users WHERE id=%d AND user_id=%d', $user_item_id, $_SESSION['id']);
        } else {
            // Р РЋРЎвЂљР В°Р Р…Р Т‘Р В°РЎР‚РЎвЂљР Р…РЎвЂ№Р в„– РЎРѓР С—Р С•РЎРѓР С•Р В±
            $exist = first('SELECT id, count FROM items_users WHERE user_id=%d AND item_id=%d', $_SESSION['id'], $item_id);
        }

        if ($exist) {
            $new_count = $exist['count'] - $count;
            if ($new_count > 0) {
                update('items_users', array('count' => $new_count), 'id=' . (int)$exist['id']);
            } else {
                delete('items_users', 'id=' . (int)$exist['id']);
            }
            return true;
        }
        return false;
    }
}

if (!function_exists('plus_item')) {
    function plus_item($count, $item_id) {
        if (!$count || !$item_id) return false;

        $exist = first('SELECT id, count FROM items_users WHERE user_id=%d AND item_id=%d', $_SESSION['id'], $item_id);
        if ($exist) {
            $new_count = $exist['count'] + $count;
            update('items_users', array('count' => $new_count), 'id=' . (int)$exist['id']);
        } else {
            insert('items_users', array(
                'user_id' => $_SESSION['id'],
                'item_id' => $item_id,
                'count' => $count
            ));
        }
        return true;
    }
}

if (!function_exists('coolseitems')) {
    function coolseitems($item_id, $user_id) {
        $item = first('SELECT count FROM items_users WHERE user_id=%d AND item_id=%d', $user_id, $item_id);
        return $item ? $item['count'] : 0;
    }
}

if (!function_exists('provitems')) {
    function provitems($item_id, $required_count) {
        return coolseitems($item_id, $_SESSION['id']) >= $required_count;
    }
}
?>
