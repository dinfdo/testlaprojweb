<?php
declare(strict_types=1);

date_default_timezone_set('UTC');
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$envFile = realpath(__DIR__ . '/../.env');
if ($envFile && is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $k = trim($k); $v = trim($v);
        if ($k !== '' && getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

LeG\Core\Database::init();
