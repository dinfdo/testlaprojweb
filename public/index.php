<?php
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH  . '/views');

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/View.php';

spl_autoload_register(function (string $class): void {
    $dirs = [
        APP_PATH . '/controllers/',
        APP_PATH . '/services/',
        APP_PATH . '/models/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

session_start();

$router = new Router();

$router->get('/',            'HomeController',        'index');
$router->get('/learn',       'LearnController',       'index');
$router->get('/game',        'GameController',        'index');
$router->get('/leaderboard', 'LeaderboardController', 'index');
$router->get('/dashboard',   'DashboardController',   'index');
$router->get('/admin',       'AdminController',       'index');
$router->get('/login',       'AuthController',        'loginPage');
$router->get('/register',    'AuthController',        'registerPage');
$router->post('/login',      'AuthController',        'doLogin');
$router->post('/register',   'AuthController',        'doRegister');
$router->post('/logout',           'AuthController',  'logout');
$router->post('/admin/delete-user', 'AdminController', 'deleteUser');
$router->post('/admin/set-role',    'AdminController', 'setRole');
$router->get('/profile',            'ProfileController', 'index');
$router->post('/profile',                'ProfileController', 'update');
$router->post('/profile/delete-account', 'ProfileController', 'deleteAccount');

$router->dispatch();
