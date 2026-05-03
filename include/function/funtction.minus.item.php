<?php
// Безопасная реализация minus_item и plus_item с защитой от дублирования

if (!function_exists('minus_item')) {
    function minus_item($count, $item_id, $user_item_id = null) {
        if (!$count || !$item_id) return false;

        if ($user_item_id) {
            // Если указан конкретный ID строки из items_users — обновляем по нему
            $exist = first('SELECT id, count FROM items_users WHERE id=%d AND user_id=%d', $user_item_id, $_SESSION['id']);
        } else {
            // Стандартный способ
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
