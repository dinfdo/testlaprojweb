<?php
declare(strict_types=1);

namespace LeG\Models;

use LeG\Core\Database;

class Component
{
    public static function all(): array
    {
        $rows = Database::pdo()->query(
            'SELECT id, slug, name, category, short_desc, description, icon, specs FROM components ORDER BY category, name'
        )->fetchAll();
        return array_map([self::class, 'hydrate'], $rows);
    }

    public static function byCategory(string $cat): array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, slug, name, category, short_desc, description, icon, specs
             FROM components WHERE category = ? ORDER BY name'
        );
        $st->execute([$cat]);
        return array_map([self::class, 'hydrate'], $st->fetchAll());
    }

    public static function find(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, slug, name, category, short_desc, description, icon, specs
             FROM components WHERE id = ?'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ? self::hydrate($r) : null;
    }

    public static function create(array $data): int
    {
        $db = Database::pdo();
        $st = $db->prepare(
            'INSERT INTO components (slug, name, category, short_desc, description, icon, specs)
             VALUES (?,?,?,?,?,?,?)'
        );
        $st->execute([
            $data['slug'], $data['name'], $data['category'],
            $data['short_desc'], $data['description'], $data['icon'] ?? '',
            json_encode($data['specs'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);
        return (int)$db->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $st = Database::pdo()->prepare('DELETE FROM components WHERE id = ?');
        $st->execute([$id]);
    }

    private static function hydrate(array $r): array
    {
        $r['specs'] = $r['specs'] ? json_decode($r['specs'], true) : [];
        return $r;
    }
}
