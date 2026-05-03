<?php
declare(strict_types=1);
/**
 * page.php РІР‚вЂќ Р В Р ВµР Т‘Р С‘РЎР‚Р ВµР С”РЎвЂљ Р Р…Р В° РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ РЎС“ РЎвЂљРЎР‚Р ВµР Р…Р ВµРЎР‚Р В°
 * PHP 8.x: Р Р†Р В°Р В»Р С‘Р Т‘Р В°РЎвЂ Р С‘РЎРЏ id, Р В±Р ВµР В·Р С•Р С—Р В°РЎРѓР Р…РЎвЂ№Р в„– РЎР‚Р ВµР Т‘Р С‘РЎР‚Р ВµР С”РЎвЂљ
 */

declare(strict_types=1);

$id = (int)filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id > 0) {
    header('Location: /game.php?go=trenInfo&id=' . $id, true, 302);
} else {
    header('Location: /game.php?go=start', true, 302);
}
exit;
