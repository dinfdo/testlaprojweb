<?php
class DashboardService {
    private GameSession $sessionModel;

    public function __construct() {
        $this->sessionModel = new GameSession();
    }

    public function getProgressData(int $userId): array {
        $levels     = $this->sessionModel->getProgressByUser($userId);
        $sessions   = $this->sessionModel->getRecentByUser($userId);
        $learned    = $this->sessionModel->countLearnedComponents($userId);
        $completed  = count(array_filter($levels, fn($l) => $l['completed']));
        $average    = $levels
            ? round(array_sum(array_column($levels, 'best_score')) / count($levels))
            : 0;

        return [
            'levels'              => $levels,
            'recent_sessions'     => $sessions,
            'learned_components'  => $learned,
            'completed_levels'    => $completed,
            'best_average'        => $average,
        ];
    }
}
