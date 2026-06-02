<?php
declare(strict_types=1);

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$root = __DIR__;

if (strpos($uri, '/api') === 0 || strpos($uri, '/leg/api') === 0) {
    require __DIR__ . '/api/index.php';
    return true;
}

if ($uri === '/rss' || $uri === '/rss.xml' || $uri === '/leg/rss.xml') {
    $_GET['__rss_direct'] = '1';
    require __DIR__ . '/api/index.php';
    return true;
}

$path = preg_replace('#^/leg#', '', $uri);
if ($path === '' || $path === '/') {
    require __DIR__ . '/index.html';
    return true;
}

$pretty = [
    '/app'   => '/app.html',
    '/admin' => '/admin.html',
    '/encyclopedia' => '/encyclopedia.html',
];
if (isset($pretty[$path])) {
    require __DIR__ . $pretty[$path];
    return true;
}

$file = $root . $path;
if (is_file($file)) {
    return false;
}

require __DIR__ . '/index.html';
return true;
