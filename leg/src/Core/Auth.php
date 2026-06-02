<?php
declare(strict_types=1);

namespace LeG\Core;

class Auth
{
    public static function require(): array
    {
        $p = JWT::fromHeader();
        if (!$p) Response::error('Unauthorized', 401);
        return $p;
    }

    public static function requireAdmin(): array
    {
        $p = self::require();
        if (empty($p['admin'])) Response::error('Forbidden', 403);
        return $p;
    }

    public static function optional(): ?array
    {
        return JWT::fromHeader();
    }
}
