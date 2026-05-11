<?php
class LeaderboardService {
    private GameSession $sessionModel;

    public function __construct() {
        $this->sessionModel = new GameSession();
    }

    public function getTopUsers(int $limit = 20): array {
        return $this->sessionModel->getLeaderboard($limit);
    }
}
