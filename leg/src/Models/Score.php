<?php
declare(strict_types=1);

namespace LeG\Models;

use LeG\Core\Database;

class Score
{
    public static function add(int $userId, int $gameId, int $score, int $time, int $difficulty): int
    {
        $db = Database::pdo();
        $st = $db->prepare(
            "INSERT INTO scores (user_id, game_id, score, time_seconds, difficulty, played_at)
             VALUES (?,?,?,?,?,datetime('now','localtime'))"
        );
        $st->execute([$userId, $gameId, $score, $time, $difficulty]);
        $id = (int)$db->lastInsertId();

        $up = $db->prepare(
            "INSERT INTO user_progress (user_id, game_id, best_score, best_difficulty, plays, last_played)
             VALUES (?,?,?,?,1,datetime('now','localtime'))
             ON CONFLICT(user_id, game_id) DO UPDATE SET
                best_score = MAX(best_score, excluded.best_score),
                best_difficulty = MAX(best_difficulty, excluded.best_difficulty),
                plays = plays + 1,
                last_played = datetime('now','localtime')"
        );
        $up->execute([$userId, $gameId, $score, $difficulty]);

        return $id;
    }

    public static function leaderboardGlobal(int $limit = 20, ?string $period = null): array
    {
        $periodFilter = '';
        if ($period === 'week') {
            $periodFilter = "AND EXISTS (
                SELECT 1 FROM scores s
                WHERE s.user_id = p.user_id
                  AND s.played_at >= datetime('now', '-7 days'))";
        } elseif ($period === 'month') {
            $periodFilter = "AND EXISTS (
                SELECT 1 FROM scores s
                WHERE s.user_id = p.user_id
                  AND s.played_at >= datetime('now', '-30 days'))";
        }

        $st = Database::pdo()->prepare(
            "SELECT u.username, u.profile_icon,
                    SUM(p.best_score)  AS total_score,
                    SUM(p.plays)       AS plays,
                    MAX(p.last_played) AS last_played
             FROM user_progress p
             JOIN users u ON u.id = p.user_id
             WHERE u.is_admin = 0 $periodFilter
             GROUP BY u.id
             ORDER BY total_score DESC, plays ASC
             LIMIT ?"
        );
        $st->bindValue(1, $limit, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function leaderboardByGame(int $gameId, int $limit = 20, ?string $period = null): array
    {
        $where = "s.game_id = ? AND u.is_admin = 0";
        if ($period === 'week')  $where .= " AND s.played_at >= datetime('now', '-7 days')";
        if ($period === 'month') $where .= " AND s.played_at >= datetime('now', '-30 days')";
        $st = Database::pdo()->prepare(
            "SELECT u.username, u.profile_icon,
                    MAX(s.score) AS score, s.time_seconds, s.difficulty, s.played_at
             FROM scores s
             JOIN users u ON u.id = s.user_id
             WHERE $where
             GROUP BY u.id
             ORDER BY score DESC, s.time_seconds ASC
             LIMIT ?"
        );
        $st->bindValue(1, $gameId, \PDO::PARAM_INT);
        $st->bindValue(2, $limit, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function userProgress(int $userId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT g.id AS game_id, g.slug, g.name, g.icon,
                    COALESCE(p.best_score, 0) AS best_score,
                    COALESCE(p.best_difficulty, 0) AS best_difficulty,
                    COALESCE(p.plays, 0) AS plays,
                    p.last_played
             FROM games g
             LEFT JOIN user_progress p ON p.game_id = g.id AND p.user_id = ?
             ORDER BY g.id'
        );
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    public static function allScoresWithUser(): array
    {
        return Database::pdo()->query(
            'SELECT s.id, u.username, g.name AS game, s.score, s.time_seconds, s.difficulty, s.played_at
             FROM scores s
             JOIN users u ON u.id = s.user_id
             JOIN games g ON g.id = s.game_id
             ORDER BY s.played_at DESC'
        )->fetchAll();
    }
}
