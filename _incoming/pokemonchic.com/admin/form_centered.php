<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Р вЂ™РЎвЂ№Р Т‘Р В°РЎвЂЎР В° Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    html, body {
      height: 100%;
      width: 100%;
    }
    body {
      background: linear-gradient(135deg, #1a2a6c, #b21f1f);
      color: #fff;
      padding: 0;
    }
    .form-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      width: 100%;
      padding: 20px;
    }
    .form-block {
      background-color: #1a0e0e;
      border: 2px solid #ffcc00;
      border-radius: 16px;
      padding: 30px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.7);
    }
    .form-block h2 {
      text-align: center;
      color: #ffcc00;
      font-size: 1.8rem;
      margin-bottom: 20px;
      border-bottom: 2px solid #ffcc00;
      padding-bottom: 10px;
    }
    .form-block label {
      display: block;
      margin-bottom: 8px;
      color: #ffcc00;
      font-weight: bold;
      font-size: 1rem;
    }
    .form-block input[type="number"] {
      width: 100%;
      padding: 14px;
      border-radius: 10px;
      background-color: #1d1d3c;
      color: white;
      font-size: 1rem;
      border: 2px solid #4d94ff;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    .form-block input[type="number"]:focus {
      border-color: #ffcc00;
      outline: none;
      box-shadow: 0 0 10px rgba(255, 204, 0, 0.4);
    }
    .form-block button {
      width: 100%;
      padding: 14px;
      font-size: 1.1rem;
      font-weight: bold;
      text-transform: uppercase;
      background: linear-gradient(135deg, #ffcc00, #ff9900);
      color: #1a1a2e;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(255, 204, 0, 0.4);
    }
    .form-block button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(255, 204, 0, 0.6);
    }
    .alert-message {
      background: rgba(0, 100, 0, 0.7);
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 10px;
      text-align: center;
      font-weight: bold;
      border: 2px solid #33cc33;
      color: #ffffff;
    }
    .alert-error {
      background: rgba(100, 0, 0, 0.7);
      border-color: #cc3333;
      color: #ffffff;
    }
  </style>
</head>
<body>
  <div class="form-wrapper">
    <div class="form-block">
      <h2>Р вЂ™РЎвЂ№Р Т‘Р В°РЎвЂЎР В° Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљР С•Р Р†</h2>
      <?php if ($message): ?>
        <div class="alert-message <?php echo $type === 'success' ? '' : 'alert-error'; ?>">
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
        <button type="submit">Р вЂ™РЎвЂ№Р Т‘Р В°РЎвЂљРЎРЉ Р С—РЎР‚Р ВµР Т‘Р СР ВµРЎвЂљ</button>
      </form>
    </div>
  </div>
</body>
</html>