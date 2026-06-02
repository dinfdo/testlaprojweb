<?php
declare(strict_types=1);

namespace LeG\Core;

class Response
{
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $message, int $status = 400, array $extra = []): void
    {
        self::json(array_merge(['error' => $message], $extra), $status);
    }

    public static function xml(string $xml, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo $xml;
        exit;
    }

    public static function csv(string $csv, string $filename = 'export.csv'): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
        exit;
    }
}
