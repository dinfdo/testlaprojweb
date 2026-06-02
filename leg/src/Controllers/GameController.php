<?php
declare(strict_types=1);

namespace LeG\Controllers;

use LeG\Core\Auth;
use LeG\Core\Response;
use LeG\Core\Security;
use LeG\Models\Game;
use LeG\Models\Component;
use LeG\Models\Score;
use LeG\Services\GameDataService;

class GameController
{
    public function list(): void
    {
        Response::json(['games' => Game::all()]);
    }

    public function show(string $id): void
    {
        $g = Game::find(Security::int($id));
        if (!$g) Response::error('Game not found', 404);
        Response::json(['game' => $g]);
    }

    public function data(string $id): void
    {
        $game = Game::find(Security::int($id));
        if (!$game) Response::error('Game not found', 404);
        $difficulty = max(1, min(3, Security::int($_GET['difficulty'] ?? 1, 1)));
        $payload = GameDataService::build($game, $difficulty, Component::all());
        Response::json(['game' => $game, 'difficulty' => $difficulty, 'data' => $payload]);
    }

    public function submit(string $id): void
    {
        $p = Auth::require();
        $game = Game::find(Security::int($id));
        if (!$game) Response::error('Game not found', 404);
        $b = Security::jsonBody();
        $score = max(0, Security::int($b['score'] ?? 0));
        $time  = max(0, Security::int($b['time_seconds'] ?? 0));
        $diff  = max(1, min(3, Security::int($b['difficulty'] ?? 1, 1)));

        $score = min($score, 10000);

        $sid = Score::add((int)$p['sub'], (int)$game['id'], $score, $time, $diff);
        Response::json([
            'ok' => true,
            'score_id' => $sid,
            'score' => $score,
            'time_seconds' => $time,
            'difficulty' => $diff,
        ], 201);
    }
}
