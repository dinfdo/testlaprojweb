<?php
function get_db(): PDO {
    $host   = getenv('DB_HOST')   ?: '127.0.0.1';
    $port   = getenv('DB_PORT')   ?: '5432';
    $dbname = getenv('DB_NAME')   ?: 'LeG_DB';
    $user   = getenv('DB_USER')   ?: 'postgres';
    $pass   = getenv('DB_PASS')   ?: 'STUDENT';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}
