<?php
declare(strict_types=1);
/**
 * img_gen.php РІР‚вЂќ Р вЂњР ВµР Р…Р ВµРЎР‚Р В°РЎвЂљР С•РЎР‚ CAPTCHA-Р С‘Р В·Р С•Р В±РЎР‚Р В°Р В¶Р ВµР Р…Р С‘РЎРЏ
 * PHP 8.x: РЎС“Р В±РЎР‚Р В°Р Р… Р С”Р С•РЎР‚Р С•РЎвЂљР С”Р С‘Р в„– РЎвЂљР ВµР С– <?php, Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р В° РЎРѓРЎвЂљРЎР‚Р С•Р С–Р В°РЎРЏ РЎвЂљР С‘Р С—Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ,
 *          Р В±Р ВµР В·Р С•Р С—Р В°РЎРѓР Р…Р В°РЎРЏ Р С•Р В±РЎР‚Р В°Р В±Р С•РЎвЂљР С”Р В° Р Р†РЎвЂ¦Р С•Р Т‘Р Р…РЎвЂ№РЎвЂ¦ Р Т‘Р В°Р Р…Р Р…РЎвЂ№РЎвЂ¦
 */

declare(strict_types=1);

const IMG_WIDTH       = 250;
const IMG_HEIGHT      = 80;
const FONT_SIZE       = 35;
const CHAR_COUNT      = 6;
const BG_CHAR_COUNT   = 15;
const FONT_PATH       = __DIR__ . '/fonts/cour2.ttf';

// Р В Р В°Р В·РЎР‚Р ВµРЎв‚¬РЎвЂР Р…Р Р…РЎвЂ№Р Вµ РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№ Р Т‘Р В»РЎРЏ CAPTCHA (РЎвЂљР С•Р В»РЎРЉР С”Р С• РЎвЂЎР С‘РЎРѓРЎвЂљРЎвЂ№Р в„– РЎРѓР С—Р С‘РЎРѓР С•Р С” Р С‘Р В· GET)
$rawLetters = (string)filter_input(INPUT_GET, 'let', FILTER_DEFAULT) ?: '';

// Р В¤Р С‘Р В»РЎРЉРЎвЂљРЎР‚РЎС“Р ВµР С: Р С•РЎРѓРЎвЂљР В°Р Р†Р В»РЎРЏР ВµР С РЎвЂљР С•Р В»РЎРЉР С”Р С• Р В±РЎС“Р С”Р Р†РЎвЂ№ Р С‘ РЎвЂ Р С‘РЎвЂћРЎР‚РЎвЂ№
$letters = preg_replace('/[^a-zA-Z0-9]/', '', $rawLetters);

if (mb_strlen($letters) < CHAR_COUNT) {
    http_response_code(400);
    exit('Invalid input');
}

// РІвЂќР‚РІвЂќР‚ Р РЋР С•Р В·Р Т‘Р В°Р Р…Р С‘Р Вµ Р С‘Р В·Р С•Р В±РЎР‚Р В°Р В¶Р ВµР Р…Р С‘РЎРЏ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

$src = imagecreatetruecolor(IMG_WIDTH, IMG_HEIGHT)
    ?: throw new RuntimeException('imagecreatetruecolor failed');

// Р вЂР ВµР В»РЎвЂ№Р в„– РЎвЂћР С•Р Р…
$fon = imagecolorallocate($src, 255, 255, 255);
imagefill($src, 0, 0, $fon);

// Р В¤Р С•Р Р…Р С•Р Р†РЎвЂ№Р Вµ РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№ (РЎРѓР В»РЎС“РЎвЂЎР В°Р в„–Р Р…РЎвЂ№Р Вµ, Р С—Р С•Р В»РЎС“Р С—РЎР‚Р С•Р В·РЎР‚Р В°РЎвЂЎР Р…РЎвЂ№Р Вµ)
$lettersArray = mb_str_split($letters);
$lettersCount = count($lettersArray);

for ($i = 0; $i < BG_CHAR_COUNT; $i++) {
    $color  = imagecolorallocatealpha($src, rand(0, 100), rand(0, 100), rand(0, 100), 100);
    $letter = $lettersArray[array_rand($lettersArray)];
    imagettftext(
        $src, FONT_SIZE, rand(0, 45),
        rand((int)(IMG_WIDTH * 0.1), (int)(IMG_WIDTH * 0.9)),
        rand((int)(IMG_HEIGHT * 0.2), IMG_HEIGHT),
        $color, FONT_PATH, $letter
    );
}

// Р С›РЎРѓР Р…Р С•Р Р†Р Р…РЎвЂ№Р Вµ РЎРѓР С‘Р СР Р†Р С•Р В»РЎвЂ№
for ($i = 0; $i < CHAR_COUNT; $i++) {
    $color  = imagecolorallocatealpha($src, 0, 0, 0, rand(20, 40));
    $letter = $lettersArray[$i] ?? $lettersArray[0];
    $size   = rand(FONT_SIZE - 3, FONT_SIZE);
    $x      = $i * FONT_SIZE + 25;
    $y      = (int)((IMG_HEIGHT * 2) / 3) + rand(0, 5);
    imagettftext($src, $size, 15, $x, $y, $color, FONT_PATH, $letter);
}

// РІвЂќР‚РІвЂќР‚ Р вЂ™РЎвЂ№Р Р†Р С•Р Т‘ РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚РІвЂќР‚

header('Content-Type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');
imagegif($src);
imagedestroy($src);
