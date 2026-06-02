<?php
declare(strict_types=1);

namespace LeG\Models;

use LeG\Core\Database;

class Game
{
    public static function all(): array
    {
        return Database::pdo()->query(
            'SELECT id, slug, name, kind, description, icon, max_difficulty FROM games ORDER BY id'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, slug, name, kind, description, icon, max_difficulty FROM games WHERE id = ?'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }
}
