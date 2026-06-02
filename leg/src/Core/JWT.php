<?php
declare(strict_types=1);

namespace LeG\Core;

class JWT
{
    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string
    {
        $r = strlen($data) % 4;
        if ($r) $data .= str_repeat('=', 4 - $r);
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function secret(): string
    {
        return $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'change_me';
    }

    public static function encode(array $payload, int $ttl = null): string
    {
        $ttl ??= (int)($_ENV['JWT_TTL_SECONDS'] ?? getenv('JWT_TTL_SECONDS') ?: 86400);
        $now = time();
        $payload = array_merge($payload, ['iat' => $now, 'exp' => $now + $ttl]);
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];

        $h = self::b64(json_encode($header));
        $p = self::b64(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $sig = hash_hmac('sha256', "$h.$p", self::secret(), true);
        return "$h.$p." . self::b64($sig);
    }

    public static function decode(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;
        [$h, $p, $s] = $parts;
        $expected = self::b64(hash_hmac('sha256', "$h.$p", self::secret(), true));
        if (!hash_equals($expected, $s)) return null;
        $payload = json_decode(self::b64d($p), true);
        if (!is_array($payload)) return null;
        if (isset($payload['exp']) && time() > (int)$payload['exp']) return null;
        return $payload;
    }

    public static function issueRefresh(int $userId): string
    {
        return self::encode(['sub' => $userId, 'type' => 'refresh'], 30 * 86400);
    }

    public static function verifyRefresh(string $token): array
    {
        $p = self::decode($token);
        if (!$p || ($p['type'] ?? '') !== 'refresh') {
            throw new \RuntimeException('Refresh token invalid sau expirat.', 401);
        }
        return $p;
    }

    public static function fromHeader(): ?array
    {
        $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!$h) $h = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!$h && function_exists('getallheaders')) {
            foreach (getallheaders() as $k => $v) {
                if (strcasecmp($k, 'Authorization') === 0) { $h = $v; break; }
            }
        }
        if (!$h || stripos($h, 'Bearer ') !== 0) return null;
        $payload = self::decode(trim(substr($h, 7)));
        if ($payload && ($payload['type'] ?? 'access') === 'refresh') return null;
        return $payload;
    }
}
