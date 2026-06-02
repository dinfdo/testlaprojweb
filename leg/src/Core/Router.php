<?php
declare(strict_types=1);

namespace LeG\Core;

class Router
{
    private array $routes = [];

    public function get(string $p, $h):    void { $this->add('GET',    $p, $h); }
    public function post(string $p, $h):   void { $this->add('POST',   $p, $h); }
    public function put(string $p, $h):    void { $this->add('PUT',    $p, $h); }
    public function delete(string $p, $h): void { $this->add('DELETE', $p, $h); }

    private function add(string $method, string $pattern, $handler): void
    {
        $this->routes[] = compact('method', 'pattern', 'handler');
    }

    public function dispatch(string $method, string $path): void
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName !== '') {
            $appRoot = dirname(dirname($scriptName));
            if (strlen($appRoot) > 1) {
                $stripped = substr($path, strlen($appRoot));
                $path = ($stripped !== false && $stripped !== '') ? $stripped : '/';
            }
        }

        foreach ($this->routes as $r) {
            if ($r['method'] !== $method) continue;
            $regex = '#^' . preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $r['pattern']) . '/?$#';
            if (preg_match($regex, $path, $m)) {
                array_shift($m);
                $handler = $r['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    (new $class())->{$action}(...$m);
                } else {
                    $handler(...$m);
                }
                return;
            }
        }

        Response::error('Route not found: ' . $method . ' ' . $path, 404);
    }
}
