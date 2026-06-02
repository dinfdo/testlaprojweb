<?php
declare(strict_types=1);

namespace LeG\Controllers;

use LeG\Core\Response;
use LeG\Core\Security;
use LeG\Models\Component;

class ComponentController
{
    public function list(): void
    {
        Response::json(['components' => Component::all()]);
    }

    public function show(string $id): void
    {
        $c = Component::find(Security::int($id));
        if (!$c) Response::error('Component not found', 404);
        Response::json(['component' => $c]);
    }

    public function byCategory(string $cat): void
    {
        $cat = Security::cleanString($cat, 30);
        Response::json(['components' => Component::byCategory($cat)]);
    }
}
