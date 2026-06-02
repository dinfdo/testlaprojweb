<?php
declare(strict_types=1);

namespace LeG\Services;

class ImportService
{
    public static function fromCsv(string $csv): array
    {
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, $csv);
        rewind($fh);
        $headers = null;
        $rows    = [];
        while (($line = fgetcsv($fh)) !== false) {
            if ($headers === null) { $headers = $line; continue; }
            if (count($line) !== count($headers)) continue;
            $rows[] = array_combine($headers, $line);
        }
        fclose($fh);
        return $rows;
    }

    public static function fromJson(string $json): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) return [];
        if (isset($data['components']) && is_array($data['components'])) {
            return $data['components'];
        }
        return isset($data[0]) ? $data : [];
    }
}
