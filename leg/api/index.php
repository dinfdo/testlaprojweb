<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use LeG\Core\Router;
use LeG\Controllers\AuthController;
use LeG\Controllers\UserController;
use LeG\Controllers\ComponentController;
use LeG\Controllers\GameController;
use LeG\Controllers\LeaderboardController;
use LeG\Controllers\AdminController;
use LeG\Controllers\RssController;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!empty($_GET['__rss_direct'])) {
    (new RssController())->feed();
    exit;
}

$router = new Router();

$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login',    [AuthController::class, 'login']);
$router->post('/api/auth/refresh',  [AuthController::class, 'refresh']);
$router->get ('/api/auth/me',       [AuthController::class, 'me']);

$router->get ('/api/user/profile-icons', [UserController::class, 'profileIcons']);
$router->put ('/api/user/profile',       [UserController::class, 'updateProfile']);
$router->get ('/api/user/progress',      [UserController::class, 'progress']);

$router->get ('/api/components',           [ComponentController::class, 'list']);
$router->get ('/api/components/{id}',      [ComponentController::class, 'show']);
$router->get ('/api/components/category/{cat}', [ComponentController::class, 'byCategory']);

$router->get ('/api/games',              [GameController::class, 'list']);
$router->get ('/api/games/{id}',         [GameController::class, 'show']);
$router->get ('/api/games/{id}/data',    [GameController::class, 'data']);
$router->post('/api/games/{id}/submit',  [GameController::class, 'submit']);

$router->get ('/api/leaderboard',                       [LeaderboardController::class, 'global']);
$router->get ('/api/leaderboard/export.csv',            [LeaderboardController::class, 'exportGlobalCsv']);
$router->get ('/api/leaderboard/export.json',           [LeaderboardController::class, 'exportGlobalJson']);
$router->get ('/api/leaderboard/game/{id}',             [LeaderboardController::class, 'byGame']);
$router->get ('/api/leaderboard/game/{id}/export.csv',  [LeaderboardController::class, 'exportGameCsv']);
$router->get ('/api/leaderboard/game/{id}/export.json', [LeaderboardController::class, 'exportGameJson']);
$router->get ('/api/leaderboard/rss',                   [RssController::class, 'feed']);

$router->get ('/api/admin/users',          [AdminController::class, 'users']);
$router->delete('/api/admin/users/{id}',   [AdminController::class, 'deleteUser']);
$router->get ('/api/admin/stats',          [AdminController::class, 'stats']);
$router->post('/api/admin/components',     [AdminController::class, 'createComponent']);
$router->delete('/api/admin/components/{id}', [AdminController::class, 'deleteComponent']);
$router->get ('/api/admin/icons',                  [AdminController::class, 'icons']);
$router->get ('/api/admin/export/users.csv',       [AdminController::class, 'exportUsersCsv']);
$router->get ('/api/admin/export/scores.json',     [AdminController::class, 'exportScoresJson']);
$router->post('/api/admin/import/components',      [AdminController::class, 'importComponents']);

$router->get ('/api/health', function () {
    \LeG\Core\Response::json(['status' => 'ok', 'app' => 'LeG', 'php' => PHP_VERSION]);
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
);
