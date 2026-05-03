<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once dirname(__FILE__) . '/../include/function/config.php';
if (!isset($_SESSION)) session_start();

// Карта предметов
$items_map = array(
    1 => '1 Монета', 2 => '2 Алмаз', 3 => '3 Покебол', 4 => '4 Игрушка Снорлокса', 5 => '5 Почтовый подарок',
    6 => '6 Желтая конфета', 7 => '7 Зеленая конфета', 8 => '8 Голубая конфета', 9 => '9 Фиолетовая конфета',
    10 => '10 Розовая конфета', 11 => '11 Красная конфета', 12 => '12 Черная конфета',
    13 => '13 Перо Pidgeotto', 14 => '14 Перо Spearow', 15 => '15 Энергетик', 16 => '16 Старая удочка',
    17 => '17 Шоколадная конфета', 18 => '18 Сушеный клевер', 19 => '19 Клевер', 20 => '20 Свежий клевер',
    21 => '21 Билет Канто-Джотто', 22 => '22 Фонарик', 23 => '23 Каменная Кирка',
    24 => '24 Мал. осколок Гром.', 25 => '25 Мал. осколок Огн.', 26 => '26 Мал. осколок Вод.',
    27 => '27 Мал. осколок Листв.', 28 => '28 Мал. осколок Лунн.', 29 => '29 Сред. осколок Гром.',
    30 => '30 Сред. осколок Огн.', 31 => '31 Сред. осколок Вод.', 32 => '32 Сред. осколок Листв.',
    33 => '33 Сред. осколок Лунн.', 34 => '34 Огромн. осколок Гром.', 35 => '35 Огромн. осколок Огн.',
    36 => '36 Огромн. осколок Вод.', 37 => '37 Огромн. осколок Листв.', 38 => '38 Огромн. осколок Лунн.',
    39 => '39 Растворитель', 40 => '40 Громовой камень', 41 => '41 Огненный камень',
    42 => '42 Водный камень', 43 => '43 Лиственный камень', 44 => '44 Лунный камень',
    45 => '45 Уникальный подарок', 46 => '46 Ледяной покеболл', 47 => '47 Покеболл с Articuno',
    48 => '48 Праздничный шарик', 49 => '49 Серпантиновая лента', 50 => '50 Пригласительный конверт',
    8500 => '8500 Дама Черви', 8501 => '8501 Дама Буби', 8502 => '8502 Дама Пики', 8503 => '8503 Дама Крести',
    8504 => '8504 Король Черви', 8505 => '8505 Король Буби', 8506 => '8506 Король Пики', 8507 => '8507 Король Крести',
    8508 => '8508 Туз Черви', 8509 => '8509 Туз Буби', 8510 => '8510 Туз Пики', 8511 => '8511 Туз Крести',
    8532 => '8532 Чёрный Джокер', 8533 => '8533 Красный Джокер'
);

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $count   = isset($_POST['count']) ? intval($_POST['count']) : 0;

    $conn = new mysqli($config['server'], $config['user'], $config['pass'], $config['db']);
    if ($conn->connect_error) {
        $message = 'Ошибка подключения: ' . $conn->connect_error;
        $type = 'error';
    } elseif ($user_id <= 0 || $item_id <= 0 || $count <= 0) {
        $message = 'Все поля должны быть заполнены корректно.';
        $type = 'error';
    } else {
        $admin = isset($_SESSION['login']) ? $_SESSION['login'] : 'неизвестно';
        $item_name = isset($items_map[$item_id]) ? $items_map[$item_id] : "ID $item_id";

        $res = $conn->query("SELECT id, count FROM items_users WHERE user_id = $user_id AND item_id = $item_id");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $new_count = $row['count'] + $count;

            $stmt = $conn->prepare("UPDATE items_users SET count = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_count, $row['id']);
            $stmt->execute();
            $stmt->close();

            $message = "? Обновлено: было {$row['count']}, добавлено $count > теперь $new_count \"$item_name\" (UID $user_id)";
            $type = 'success';
        } else {
            $stmt = $conn->prepare("INSERT INTO items_users (user_id, item_id, count) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $item_id, $count);
            $stmt->execute();
            $stmt->close();

            $message = "?? Выдано: $count x \"$item_name\" игроку (UID $user_id)";
            $type = 'success';
        }

        $stmt = $conn->prepare("INSERT INTO adml_items (user_id, item_id, count, admin_login) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $item_id, $count, $admin);
        $stmt->execute();
        $stmt->close();

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Выдача предметов</title>
<style>
body {
    background: linear-gradient(135deg, #1a2a6c, #b21f1f);
    font-family: Tahoma, sans-serif;
    color: #fff;
    padding: 0;
    margin: 0;
}
.wrapper {
    max-width: 500px;
    margin: 60px auto;
    background-color: #1a0e0e;
    padding: 30px;
    border-radius: 12px;
    border: 2px solid #ffcc00;
}
h2 {
    text-align: center;
    color: #ffcc00;
    margin-bottom: 20px;
}
label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}
input[type="number"] {
    width: 100%;
    padding: 10px;
    background: #2a2a2a;
    border: 1px solid #999;
    color: #fff;
    border-radius: 5px;
}
button {
    margin-top: 15px;
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #ffcc00, #ff9900);
    color: #000;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
button:hover {
    background: #ffd700;
}
.alert {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
}
.success {
    background-color: #2e7d32;
    border: 1px solid #66bb6a;
}
.error {
    background-color: #c62828;
    border: 1px solid #ef5350;
}
</style>
</head>
<body>
<div class="wrapper">
    <h2>Выдача предметов</h2>
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label for="user_id">ID игрока</label>
        <input type="number" name="user_id" id="user_id" required>
        <label for="item_id">ID предмета</label>
        <input type="number" name="item_id" id="item_id" required>
        <label for="count">Количество</label>
        <input type="number" name="count" id="count" value="1" required>
        <button type="submit">Выдать</button>
    </form>
</div>
</body>
</html>
