<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once dirname(__FILE__) . '/../include/function/config.php';
if (!isset($_SESSION)) session_start();

// Р С™Р В°РЎР‚РЎвЂљР В° Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†
$items_map = array(
    1 => '1 Р СљР С•Р Р…Р ВµРЎвЂљР В°', 2 => '2 Р С’Р В»Р СР В°Р В·', 3 => '3 Р СџР С•Р С”Р ВµР В±Р С•Р В»', 4 => '4 Р ВР С–РЎР‚РЎС“РЎв‚¬Р С”Р В° Р РЋР Р…Р С•РЎР‚Р В»Р С•Р С”РЎРѓР В°', 5 => '5 Р СџР С•РЎвЂЎРЎвЂљР С•Р Р†РЎвЂ№Р в„– Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С”',
    6 => '6 Р вЂ“Р ВµР В»РЎвЂљР В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°', 7 => '7 Р вЂ”Р ВµР В»Р ВµР Р…Р В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°', 8 => '8 Р вЂњР С•Р В»РЎС“Р В±Р В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°', 9 => '9 Р В¤Р С‘Р С•Р В»Р ВµРЎвЂљР С•Р Р†Р В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°',
    10 => '10 Р В Р С•Р В·Р С•Р Р†Р В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°', 11 => '11 Р С™РЎР‚Р В°РЎРѓР Р…Р В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°', 12 => '12 Р В§Р ВµРЎР‚Р Р…Р В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°',
    13 => '13 Р СџР ВµРЎР‚Р С• Pidgeotto', 14 => '14 Р СџР ВµРЎР‚Р С• Spearow', 15 => '15 Р В­Р Р…Р ВµРЎР‚Р С–Р ВµРЎвЂљР С‘Р С”', 16 => '16 Р РЋРЎвЂљР В°РЎР‚Р В°РЎРЏ РЎС“Р Т‘Р С•РЎвЂЎР С”Р В°',
    17 => '17 Р РЃР С•Р С”Р С•Р В»Р В°Р Т‘Р Р…Р В°РЎРЏ Р С”Р С•Р Р…РЎвЂћР ВµРЎвЂљР В°', 18 => '18 Р РЋРЎС“РЎв‚¬Р ВµР Р…РЎвЂ№Р в„– Р С”Р В»Р ВµР Р†Р ВµРЎР‚', 19 => '19 Р С™Р В»Р ВµР Р†Р ВµРЎР‚', 20 => '20 Р РЋР Р†Р ВµР В¶Р С‘Р в„– Р С”Р В»Р ВµР Р†Р ВµРЎР‚',
    21 => '21 Р вЂР С‘Р В»Р ВµРЎвЂљ Р С™Р В°Р Р…РЎвЂљР С•-Р вЂќР В¶Р С•РЎвЂљРЎвЂљР С•', 22 => '22 Р В¤Р С•Р Р…Р В°РЎР‚Р С‘Р С”', 23 => '23 Р С™Р В°Р СР ВµР Р…Р Р…Р В°РЎРЏ Р С™Р С‘РЎР‚Р С”Р В°',
    24 => '24 Р СљР В°Р В». Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂњРЎР‚Р С•Р С.', 25 => '25 Р СљР В°Р В». Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р С›Р С–Р Р….', 26 => '26 Р СљР В°Р В». Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂ™Р С•Р Т‘.',
    27 => '27 Р СљР В°Р В». Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєР С‘РЎРѓРЎвЂљР Р†.', 28 => '28 Р СљР В°Р В». Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєРЎС“Р Р…Р Р….', 29 => '29 Р РЋРЎР‚Р ВµР Т‘. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂњРЎР‚Р С•Р С.',
    30 => '30 Р РЋРЎР‚Р ВµР Т‘. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р С›Р С–Р Р….', 31 => '31 Р РЋРЎР‚Р ВµР Т‘. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂ™Р С•Р Т‘.', 32 => '32 Р РЋРЎР‚Р ВµР Т‘. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєР С‘РЎРѓРЎвЂљР Р†.',
    33 => '33 Р РЋРЎР‚Р ВµР Т‘. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєРЎС“Р Р…Р Р….', 34 => '34 Р С›Р С–РЎР‚Р С•Р СР Р…. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂњРЎР‚Р С•Р С.', 35 => '35 Р С›Р С–РЎР‚Р С•Р СР Р…. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р С›Р С–Р Р….',
    36 => '36 Р С›Р С–РЎР‚Р С•Р СР Р…. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂ™Р С•Р Т‘.', 37 => '37 Р С›Р С–РЎР‚Р С•Р СР Р…. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєР С‘РЎРѓРЎвЂљР Р†.', 38 => '38 Р С›Р С–РЎР‚Р С•Р СР Р…. Р С•РЎРѓР С”Р С•Р В»Р С•Р С” Р вЂєРЎС“Р Р…Р Р….',
    39 => '39 Р В Р В°РЎРѓРЎвЂљР Р†Р С•РЎР‚Р С‘РЎвЂљР ВµР В»РЎРЉ', 40 => '40 Р вЂњРЎР‚Р С•Р СР С•Р Р†Р С•Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ', 41 => '41 Р С›Р С–Р Р…Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ',
    42 => '42 Р вЂ™Р С•Р Т‘Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ', 43 => '43 Р вЂєР С‘РЎРѓРЎвЂљР Р†Р ВµР Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ', 44 => '44 Р вЂєРЎС“Р Р…Р Р…РЎвЂ№Р в„– Р С”Р В°Р СР ВµР Р…РЎРЉ',
    45 => '45 Р Р€Р Р…Р С‘Р С”Р В°Р В»РЎРЉР Р…РЎвЂ№Р в„– Р С—Р С•Р Т‘Р В°РЎР‚Р С•Р С”', 46 => '46 Р вЂєР ВµР Т‘РЎРЏР Р…Р С•Р в„– Р С—Р С•Р С”Р ВµР В±Р С•Р В»Р В»', 47 => '47 Р СџР С•Р С”Р ВµР В±Р С•Р В»Р В» РЎРѓ Articuno',
    48 => '48 Р СџРЎР‚Р В°Р В·Р Т‘Р Р…Р С‘РЎвЂЎР Р…РЎвЂ№Р в„– РЎв‚¬Р В°РЎР‚Р С‘Р С”', 49 => '49 Р РЋР ВµРЎР‚Р С—Р В°Р Р…РЎвЂљР С‘Р Р…Р С•Р Р†Р В°РЎРЏ Р В»Р ВµР Р…РЎвЂљР В°', 50 => '50 Р СџРЎР‚Р С‘Р С–Р В»Р В°РЎРѓР С‘РЎвЂљР ВµР В»РЎРЉР Р…РЎвЂ№Р в„– Р С”Р С•Р Р…Р Р†Р ВµРЎР‚РЎвЂљ',
    8500 => '8500 Р вЂќР В°Р СР В° Р В§Р ВµРЎР‚Р Р†Р С‘', 8501 => '8501 Р вЂќР В°Р СР В° Р вЂРЎС“Р В±Р С‘', 8502 => '8502 Р вЂќР В°Р СР В° Р СџР С‘Р С”Р С‘', 8503 => '8503 Р вЂќР В°Р СР В° Р С™РЎР‚Р ВµРЎРѓРЎвЂљР С‘',
    8504 => '8504 Р С™Р С•РЎР‚Р С•Р В»РЎРЉ Р В§Р ВµРЎР‚Р Р†Р С‘', 8505 => '8505 Р С™Р С•РЎР‚Р С•Р В»РЎРЉ Р вЂРЎС“Р В±Р С‘', 8506 => '8506 Р С™Р С•РЎР‚Р С•Р В»РЎРЉ Р СџР С‘Р С”Р С‘', 8507 => '8507 Р С™Р С•РЎР‚Р С•Р В»РЎРЉ Р С™РЎР‚Р ВµРЎРѓРЎвЂљР С‘',
    8508 => '8508 Р СћРЎС“Р В· Р В§Р ВµРЎР‚Р Р†Р С‘', 8509 => '8509 Р СћРЎС“Р В· Р вЂРЎС“Р В±Р С‘', 8510 => '8510 Р СћРЎС“Р В· Р СџР С‘Р С”Р С‘', 8511 => '8511 Р СћРЎС“Р В· Р С™РЎР‚Р ВµРЎРѓРЎвЂљР С‘',
    8532 => '8532 Р В§РЎвЂРЎР‚Р Р…РЎвЂ№Р в„– Р вЂќР В¶Р С•Р С”Р ВµРЎР‚', 8533 => '8533 Р С™РЎР‚Р В°РЎРѓР Р…РЎвЂ№Р в„– Р вЂќР В¶Р С•Р С”Р ВµРЎР‚'
);

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $count   = isset($_POST['count']) ? intval($_POST['count']) : 0;

    $conn = new mysqli($config['server'], $config['user'], $config['pass'], $config['db']);
    if ($conn->connect_error) {
        $message = 'Р С›РЎв‚¬Р С‘Р В±Р С”Р В° Р С—Р С•Р Т‘Р С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘РЎРЏ: ' . $conn->connect_error;
        $type = 'error';
    } elseif ($user_id <= 0 || $item_id <= 0 || $count <= 0) {
        $message = 'Р вЂ™РЎРѓР Вµ Р С—Р С•Р В»РЎРЏ Р Т‘Р С•Р В»Р В¶Р Р…РЎвЂ№ Р В±РЎвЂ№РЎвЂљРЎРЉ Р В·Р В°Р С—Р С•Р В»Р Р…Р ВµР Р…РЎвЂ№ Р С”Р С•РЎР‚РЎР‚Р ВµР С”РЎвЂљР Р…Р С•.';
        $type = 'error';
    } else {
        $admin = isset($_SESSION['login']) ? $_SESSION['login'] : 'Р Р…Р ВµР С‘Р В·Р Р†Р ВµРЎРѓРЎвЂљР Р…Р С•';
        $item_name = isset($items_map[$item_id]) ? $items_map[$item_id] : "ID $item_id";

        $res = $conn->query("SELECT id, count FROM items_users WHERE user_id = $user_id AND item_id = $item_id");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $new_count = $row['count'] + $count;

            $stmt = $conn->prepare("UPDATE items_users SET count = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_count, $row['id']);
            $stmt->execute();
            $stmt->close();

            $message = "? Р С›Р В±Р Р…Р С•Р Р†Р В»Р ВµР Р…Р С•: Р В±РЎвЂ№Р В»Р С• {$row['count']}, Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р С• $count > РЎвЂљР ВµР С—Р ВµРЎР‚РЎРЉ $new_count \"$item_name\" (UID $user_id)";
            $type = 'success';
        } else {
            $stmt = $conn->prepare("INSERT INTO items_users (user_id, item_id, count) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $item_id, $count);
            $stmt->execute();
            $stmt->close();

            $message = "?? Р вЂ™РЎвЂ№Р Т‘Р В°Р Р…Р С•: $count x \"$item_name\" Р С‘Р С–РЎР‚Р С•Р С”РЎС“ (UID $user_id)";
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
<title>Р вЂ™РЎвЂ№Р Т‘Р В°РЎвЂЎР В° Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†</title>
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
    <h2>Р вЂ™РЎвЂ№Р Т‘Р В°РЎвЂЎР В° Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†</h2>
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $type === 'success' ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label for="user_id">ID Р С‘Р С–РЎР‚Р С•Р С”Р В°</label>
        <input type="number" name="user_id" id="user_id" required>
        <label for="item_id">ID Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР В°</label>
        <input type="number" name="item_id" id="item_id" required>
        <label for="count">Р С™Р С•Р В»Р С‘РЎвЂЎР ВµРЎРѓРЎвЂљР Р†Р С•</label>
        <input type="number" name="count" id="count" value="1" required>
        <button type="submit">Р вЂ™РЎвЂ№Р Т‘Р В°РЎвЂљРЎРЉ</button>
    </form>
</div>
</body>
</html>
