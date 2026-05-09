<?php
for ($port = 3300; $port <= 3310; $port++) {
    $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
    if ($fp) {
        echo "Port $port is OPEN\n";
        fclose($fp);
    }
}
