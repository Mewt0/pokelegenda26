<?php
declare(strict_types=1);

/** Что именно испорчено в названиях квестов: показываем текст и байты. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

foreach ($db->query('SELECT id, title, description FROM quest_definitions ORDER BY id LIMIT 10') as $row) {
    $title = (string) $row['title'];
    $head = bin2hex(mb_substr($title, 0, 3));
    echo "#{$row['id']}  [{$head}]  {$title}\n";
    if (preg_match('/Р[°-я]/u', $title)) {
        // Испорченный фрагмент: показываем, что получится после «раскодирования» cp1251 -> utf8.
        $fixed = @iconv('UTF-8', 'Windows-1251', $title);
        echo '     -> возможно: ' . ($fixed === false ? '(не декодируется)' : $fixed) . "\n";
    }
}

echo "\nстрок с признаком порчи в quest_definitions: ";
echo (int) $db->query("SELECT COUNT(*) FROM quest_definitions WHERE title REGEXP 'Р[°-я]'")->fetchColumn() . "\n";
echo "строк с признаком порчи в quest_steps.title: ";
echo (int) $db->query("SELECT COUNT(*) FROM quest_steps WHERE title REGEXP 'Р[°-я]'")->fetchColumn() . "\n";
echo "строк с признаком порчи в quest_steps.description: ";
echo (int) $db->query("SELECT COUNT(*) FROM quest_steps WHERE description REGEXP 'Р[°-я]'")->fetchColumn() . "\n";
