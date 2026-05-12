<?php
require_once __DIR__ . '/helpers.php';

only_method('GET');

json_response([
    'name'    => 'LeG API',
    'version' => '1.0',
    'routes'  => [
        'GET  api/components/list.php'      => 'List components (?device_id, ?difficulty)',
        'GET  api/components/details.php'   => 'Component detail (?id)',
        'GET  api/levels/list.php'          => 'List levels (?device_id)',
        'GET  api/game/questions.php'       => 'Questions for a level (?level_id)',
        'POST api/game/submit.php'          => 'Submit quiz answers',
        'GET  api/progress/user.php'        => 'Current user progress (auth)',
        'GET  api/leaderboard/list.php'     => 'Top players (?limit)',
        'GET  api/rss/leaderboard.php'      => 'Leaderboard RSS feed',
        'POST api/auth/login.php'           => 'Login',
        'POST api/auth/register.php'        => 'Register',
        'GET  api/auth/whoami.php'          => 'Current user (auth)',
        'POST api/auth/logout.php'          => 'Logout',
        'GET  api/admin/stats.php'          => 'Admin stats (admin)',
        'ANY  api/admin/components.php'     => 'Admin CRUD components (admin)',
        'ANY  api/admin/content.php'        => 'Admin CRUD content (admin)',
        'ANY  api/admin/users.php'          => 'Admin user management (admin)',
        'GET  api/admin/export.php'         => 'Export data (admin)',
        'POST api/admin/import.php'         => 'Import components (admin)',
    ],
]);
