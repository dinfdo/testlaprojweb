<?php
declare(strict_types=1);

namespace LeG\Services;

class ExportService
{

    public static function toCsv(array $rows): string
    {
        if (empty($rows)) return "";
        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, array_keys($rows[0]));
        foreach ($rows as $r) fputcsv($fh, array_values($r));
        rewind($fh);
        return stream_get_contents($fh) ?: '';
    }
}
