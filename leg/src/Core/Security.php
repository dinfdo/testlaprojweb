<?php
declare(strict_types=1);

namespace LeG\Core;

class Security
{
    public static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function cleanString($v, int $max = 255): string
    {
        $s = is_string($v) ? $v : (string)$v;
        $s = preg_replace('/[\x00-\x1F\x7F]/u', '', $s) ?? '';
        return mb_substr(trim($s), 0, $max);
    }

    public static function jsonBody(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function int($v, int $default = 0): int
    {
        return is_numeric($v) ? (int)$v : $default;
    }
}
