<?php
declare(strict_types=1);

/**
 * Обновление картинок локаций: заменяет ссылки на изображение в легаси-комнате
 * (include/rooms/<id>.php) и в конфиге контента (config/location_content.php).
 *
 * Именно комната определяет, что видит игрок, поэтому картинку меняем там.
 *
 * Usage:
 *   php tools/apply_location_images.php            # показать, что будет изменено
 *   php tools/apply_location_images.php --apply    # применить
 */
require __DIR__ . '/../src/Support/Autoload.php';

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);

// id локации => новый файл в img/room (что подтверждено на листах ревью)
$mapping = [
    18 => '444.png',    // Пьютер — каменный город у гор с фонтаном
    101 => '333.png',   // Литтлруд — маленький городок в полях
    104 => '333.png',   // Олдэйл — маленький городок на перекрёстке
    29 => '1.jpg',      // Пригород — домик с красной крышей у дороги
    110 => '6.jpg',     // Лес Петалбурга — тёмный лес у воды
    2 => '2_3.jpg',     // Тюрьма Алабастии — разрушенный коридор
    23 => '33.jpg',     // Порт Церулина — причал и лодки
    27 => '49.jpg',     // Пляж — песок и зонтики
    44 => '18.png',     // Электростанция — промышленное здание
    100 => '31.png',    // Вертолёт — кабина управления
    80 => '52.png',     // Турнирная Арена — стадион с трибунами
    81 => '28.png',     // Электрический стадион
    82 => '4.png',      // Огненный стадион
    83 => '5.png',      // Травяной стадион
    84 => '16.png',     // Каменный стадион
    85 => '29.png',     // Водный стадион
    86 => '30.png',     // Психический стадион
];

$staging = 'C:/Temp/pchic-web/locimg-candidates';
$changed = 0;

foreach ($mapping as $locationId => $newFile) {
    // Проверяем, что файл существует: либо уже в проекте, либо в папке скачанных.
    $candidates = [
        $root . '/img/room/' . $newFile,
        $staging . '/' . $newFile,
    ];
    $source = null;
    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            $source = $candidate;
            break;
        }
    }
    if ($source === null) {
        echo "!! [$locationId] файла $newFile нет ни в проекте, ни в папке скачанных\n";
        continue;
    }

    // Скачанные фото надо положить в проект.
    $target = $root . '/img/room/' . $newFile;
    if ($source !== $target) {
        echo "[$locationId] скопировать $newFile в img/room\n";
        if ($apply) {
            copy($source, $target);
        }
    }

    $roomFile = $root . '/include/rooms/' . $locationId . '.php';
    if (is_file($roomFile)) {
        $raw = (string) file_get_contents($roomFile);
        // Меняем любую ссылку вида img/room/<что-то> внутри комнаты.
        $updated = preg_replace('#img/room/[A-Za-z0-9_ ().\-]+\.(?:png|jpg|jpeg|gif)#i', 'img/room/' . $newFile, $raw, 1, $count);
        if ($count > 0 && $updated !== $raw) {
            echo "[$locationId] комната: заменить картинку на $newFile\n";
            if ($apply) {
                file_put_contents($roomFile, $updated);
            }
            $changed++;
        } elseif ($count === 0) {
            echo "[$locationId] комната: ссылки на картинку нет — нужна отдельная правка шаблона\n";
        }
    } else {
        echo "[$locationId] комнаты нет\n";
    }

    // Главное: игроку картинку отдаёт конфиг контента (LocationStateService::imageFor
    // берёт именно его), поэтому без правки конфига замена в комнате не видна.
    $configPatched = patchConfigImage($root . '/config/location_content.php', $locationId, '/img/room/' . $newFile, $apply);
    echo $configPatched
        ? "[$locationId] конфиг: image -> /img/room/$newFile\n"
        : "[$locationId] конфиг: блок локации или ключ image не найден\n";
}

/**
 * Заменяет 'image' => '...' внутри блока конкретной локации в конфиге контента.
 * Возвращает true, если строка найдена.
 */
function patchConfigImage(string $path, int $locationId, string $newImage, bool $apply): bool
{
    $lines = explode("\n", (string) file_get_contents($path));
    $inside = false;
    $patched = false;
    $insertAfter = null;

    foreach ($lines as $index => $line) {
        if (preg_match('/^\s*' . $locationId . '\s*=>/', $line)) {
            $inside = true;
            continue;
        }
        if ($inside && preg_match('/^\s*\d+\s*=>/', $line)) {
            break; // начался блок следующей локации
        }
        if (!$inside) {
            continue;
        }
        if (preg_match("/^(\s*'image'\s*=>\s*)(.*?)(,?\s*)$/", $line, $matches)) {
            $lines[$index] = $matches[1] . "'" . $newImage . "'" . ($matches[3] === '' ? ',' : $matches[3]);
            $patched = true;
            break;
        }
        // Ключа image в блоке нет (бывает у локаций без картинки): запомним, куда вставить.
        if (preg_match("/^\s*'(about|name)'\s*=>/", $line)) {
            $insertAfter = $index;
        }
    }

    if (!$patched && $insertAfter !== null) {
        array_splice($lines, $insertAfter + 1, 0, "    'image' => '" . $newImage . "',");
        $patched = true;
    }

    if ($patched && $apply) {
        file_put_contents($path, implode("\n", $lines));
    }

    return $patched;
}

echo $apply ? "применено: $changed\n" : "это предпросмотр, ничего не изменено (добавь --apply)\n";
