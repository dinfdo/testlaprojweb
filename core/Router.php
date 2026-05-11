<?php
class Router {
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void {
        $this->routes['GET'][$path] = [$controller, $action];
    }

    public function post(string $path, string $controller, string $action): void {
        $this->routes['POST'][$path] = [$controller, $action];
    }

    public function dispatch(): void {
        $method    = $_SERVER['REQUEST_METHOD'];
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path      = '/' . ltrim(substr($uri, strlen($scriptDir)), '/');
        $path      = ($path === '' || $path === '//') ? '/' : $path;

        if (isset($this->routes[$method][$path])) {
            [$controllerName, $action] = $this->routes[$method][$path];
            $controller = new $controllerName();
            $controller->$action();
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['title' => '404 - Pagina negasita', 'base' => rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')]);
    }
}
