<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../include/function/config.php';
if (!isset($_SESSION)) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $count   = isset($_POST['count']) ? intval($_POST['count']) : 0;
    $item_id = 2;

    $conn = new mysqli($config['server'], $config['user'], $config['pass'], $config['db']);
    if ($conn->connect_error) {
        $_SESSION['message'] = 'Ошибка подключения: ' . $conn->connect_error;
        $_SESSION['message_type'] = 'error';
    } else {
        $res = $conn->query("SELECT * FROM items_users WHERE user_id = '$user_id' AND item_id = '$item_id'");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $new_count = $row['count'] + $count;
            $conn->query("UPDATE items_users SET count = '$new_count' WHERE id = '{$row['id']}'");
            $_SESSION['message'] = 'Алмазы обновлены.';
            $_SESSION['message_type'] = 'success';
        } else {
            $conn->query("INSERT INTO items_users (user_id, item_id, count) VALUES ('$user_id', '$item_id', '$count')");
            $_SESSION['message'] = 'Алмазы выданы.';
            $_SESSION['message_type'] = 'success';
        }
        $conn->close();
    }

    echo '<script>location.href="' . $_SERVER['REQUEST_URI'] . '";</script>';
    exit;
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$type    = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Выдача алмазов</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
      color: #fff;
      min-height: 100vh;
      padding: 20px;
      overflow-x: hidden;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .card {
      background: rgba(0, 0, 0, 0.7);
      border-radius: 15px;
      padding: 25px;
      margin: 50px auto;
      max-width: 500px;
      border: 2px solid #ffcc00;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
    }

    .card-title {
      font-size: 1.8rem;
      color: #ffcc00;
      margin-bottom: 20px;
      text-align: center;
      border-bottom: 2px solid #ffcc00;
      padding-bottom: 10px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #ffcc00;
      font-weight: bold;
    }

    input[type="number"] {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 2px solid #4d94ff;
      background: rgba(30, 30, 60, 0.8);
      color: white;
      font-size: 1rem;
      margin-bottom: 15px;
    }

    input[type="number"]:focus {
      border-color: #ffcc00;
      box-shadow: 0 0 10px rgba(255, 204, 0, 0.5);
      outline: none;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 12px 30px;
      background: linear-gradient(135deg, #ffcc00, #ff9900);
      color: #1a1a2e;
      border: none;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(255, 204, 0, 0.4);
    }

    .message {
      background: rgba(0, 100, 0, 0.7);
      padding: 15px;
      margin: 20px 0;
      border-radius: 10px;
      text-align: center;
      font-weight: bold;
      border: 2px solid #33cc33;
    }

    .message.error {
      background: rgba(100, 0, 0, 0.7);
      border-color: #cc3333;
    }
  </style>
  
</head>
<body>
  <div class="container">
    <div class="card">
      <h2 class="card-title">
  <i class="fas fa-dragon dragon-icon"></i> Выдача алмазов
</h2>

      <?php if ($message): ?>
        <div class="message <?php echo $type === 'error' ? 'error' : ''; ?>">
          <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <label>ID игрока:</label>
        <input type="number" name="user_id" required>

        <label>Количество алмазов:</label>
        <input type="number" name="count" value="100" required>

        <button type="submit" class="btn">Выдать алмазы</button>
      </form>
    </div>
  </div>
</body>
</html>
