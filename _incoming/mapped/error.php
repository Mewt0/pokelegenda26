<?php
declare(strict_types=1);
/**
 * error.php РІР‚вЂќ Р С›Р В±РЎР‚Р В°Р В±Р С•РЎвЂљРЎвЂЎР С‘Р С” Р С•РЎв‚¬Р С‘Р В±Р С•Р С”
 * PHP 8.x: Р В·Р В°Р СР ВµР Р…РЎвЂР Р… РЎРѓРЎвЂљР В°РЎР‚РЎвЂ№Р в„– error_handler Р Р…Р В° РЎРѓР С•Р Р†РЎР‚Р ВµР СР ВµР Р…Р Р…РЎвЂ№Р в„– РЎРѓ Р С—Р С•Р Т‘Р Т‘Р ВµРЎР‚Р В¶Р С”Р С•Р в„– Throwable
 */

declare(strict_types=1);

set_error_handler('ErrorHandler');
error_reporting(E_ALL);

function ErrorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
{
    $escapeFuncs = ['__callStatic', 'call_user_func_array', 'ErrorHandler', 'mysqli_fetch_assoc'];
    $list = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $dir = dirname($_SERVER['SCRIPT_FILENAME'] ?? '');

    foreach ($list as $i => $frame) {
        if (in_array($frame['function'] ?? '', $escapeFuncs, true)) {
            array_splice($list, $i, 1);
        } elseif (isset($frame['file'])) {
            $list[$i]['file'] = str_replace($dir, '', $frame['file']);
        }
    }

    // PHP 8: mysqli Р Р…Р Вµ Р С‘Р СР ВµР ВµРЎвЂљ Р С–Р В»Р С•Р В±Р В°Р В»РЎРЉР Р…Р С•Р в„– Р С•РЎв‚¬Р С‘Р В±Р С”Р С‘, Р С‘РЎРѓР С—Р С•Р В»РЎРЉР В·РЎС“Р ВµР С Р С—Р С•РЎРѓР В»Р ВµР Т‘Р Р…Р ВµР Вµ Р С‘РЎРѓР С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘Р Вµ Р ВµРЎРѓР В»Р С‘ Р ВµРЎРѓРЎвЂљРЎРЉ
    $dbError = '';

    echo '<div style="margin:30px;font-size:16px;">';
    echo '<h3 style="margin:5px 0 0 0;">' . htmlspecialchars($errstr) . $dbError . '</h3>';
    echo '<div>' . htmlspecialchars($errfile) . ' [' . $errline . ']</div>';

    foreach ($list as $i => $frame) {
        echo '<hr>';
        echo '<h3 style="margin:5px 0 0 0;">Backtrace #' . ($i + 1) . '</h3>';
        if (!empty($frame['file'])) {
            echo '<div><b>File:</b> <a href="file:///' . htmlspecialchars($frame['file']) . '">'
                . htmlspecialchars($frame['file']) . '</a></div>';
        }
        if (!empty($frame['class'])) {
            echo '<div><b>Class:</b> <span style="color:#550000">' . htmlspecialchars($frame['class']) . '</span></div>';
        }
        if (!empty($frame['line'])) {
            echo '<div><b>Line:</b> ' . (int)$frame['line'] . '</div>';
        }
        if (!empty($frame['function'])) {
            echo '<div><b>Function:</b> ' . htmlspecialchars($frame['function']) . '</div>';
        }
    }

    echo '</div>';
    die();

    return true;
}

// Р СћР В°Р С”Р В¶Р Вµ Р С—Р ВµРЎР‚Р ВµРЎвЂ¦Р Р†Р В°РЎвЂљРЎвЂ№Р Р†Р В°Р ВµР С Р Р…Р ВµР С—Р С•Р в„–Р СР В°Р Р…Р Р…РЎвЂ№Р Вµ Р С‘РЎРѓР С”Р В»РЎР‹РЎвЂЎР ВµР Р…Р С‘РЎРЏ
set_exception_handler(function (Throwable $e): void {
    ErrorHandler(
        E_ERROR,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
});
