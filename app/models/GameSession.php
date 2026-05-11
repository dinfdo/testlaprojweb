<?php
class GameSession extends Model {
    public function count(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM quiz_sessions')->fetchColumn();
    }

    public function averageScore(): float {
        return round((float)$this->db->query('SELECT AVG(score) FROM quiz_sessions')->fetchColumn(), 1);
    }

    public function getRecentByUser(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare(
            'SELECT qs.*, l.title AS level_name
             FROM quiz_sessions qs
             JOIN levels l ON qs.level_id = l.id
             WHERE qs.user_id = ?
             ORDER BY qs.created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getLeaderboard(int $limit = 20): array {
        $stmt = $this->db->prepare(
            'SELECT u.username,
                    COUNT(DISTINCT up.level_id)  AS levels_completed,
                    COALESCE(MAX(qs.score), 0)   AS best_score,
                    COALESCE(AVG(qs.score), 0)   AS avg_score
             FROM users u
             LEFT JOIN user_progress up ON up.user_id = u.id AND up.completed = 1
             LEFT JOIN quiz_sessions qs ON qs.user_id = u.id
             GROUP BY u.id, u.username
             ORDER BY levels_completed DESC, avg_score DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getProgressByUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT l.id, l.title, l.level_number, l.min_score_required,
                    COALESCE(up.best_score, 0)  AS best_score,
                    COALESCE(up.completed, 0)   AS completed
             FROM levels l
             LEFT JOIN user_progress up ON up.level_id = l.id AND up.user_id = ?
             ORDER BY l.level_number'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function countLearnedComponents(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM learned_components WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}
