<?php
declare(strict_types=1);

namespace LeG\Controllers;

use LeG\Core\Response;
use LeG\Core\Security;
use LeG\Models\Score;
use LeG\Models\Game;
use LeG\Services\ExportService;

class LeaderboardController
{
    private function period(): ?string
    {
        $p = strtolower(Security::cleanString($_GET['period'] ?? '', 10));
        return in_array($p, ['week', 'month'], true) ? $p : null;
    }

    public function global(): void
    {
        Response::json([
            'leaderboard' => Score::leaderboardGlobal(20, $this->period()),
            'period'      => $this->period() ?: 'all',
        ]);
    }

    public function byGame(string $id): void
    {
        $gid = Security::int($id);
        Response::json([
            'leaderboard' => Score::leaderboardByGame($gid, 20, $this->period()),
            'period'      => $this->period() ?: 'all',
        ]);
    }

    public function exportGlobalCsv(): void
    {
        $rows = Score::leaderboardGlobal(1000, $this->period());
        Response::csv(ExportService::toCsv($rows), 'leaderboard_global.csv');
    }

    public function exportGlobalJson(): void
    {
        Response::json([
            'period'      => $this->period() ?: 'all',
            'leaderboard' => Score::leaderboardGlobal(1000, $this->period()),
        ]);
    }

    public function exportGameCsv(string $id): void
    {
        $gid = Security::int($id);
        $game = Game::find($gid);
        if (!$game) Response::error('Game not found', 404);
        $rows = Score::leaderboardByGame($gid, 1000, $this->period());
        $csv = ExportService::toCsv($rows);
        $name = 'leaderboard_' . preg_replace('/[^a-z0-9_-]/', '_', strtolower($game['slug'])) . '.csv';
        Response::csv($csv, $name);
    }

    public function exportGameJson(string $id): void
    {
        $gid = Security::int($id);
        $game = Game::find($gid);
        if (!$game) Response::error('Game not found', 404);
        Response::json([
            'game'   => $game,
            'period' => $this->period() ?: 'all',
            'leaderboard' => Score::leaderboardByGame($gid, 1000, $this->period()),
        ]);
    }
}
