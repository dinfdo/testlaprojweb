<?php
declare(strict_types=1);

namespace LeG\Controllers;

use LeG\Core\Auth;
use LeG\Core\Response;
use LeG\Core\Security;
use LeG\Models\User;
use LeG\Models\Score;

class UserController
{
    public function profileIcons(): void
    {
        $dir = __DIR__ . '/../../assets/profiles';
        $files = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) ?: [] as $f) {
                if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $f)) $files[] = $f;
            }
        }
        sort($files);
        Response::json(['icons' => $files]);
    }

    public function updateProfile(): void
    {
        $p = Auth::require();
        $b = Security::jsonBody();
        $icon = Security::cleanString($b['profile_icon'] ?? '', 80);
        if ($icon === '' || !preg_match('/^[a-zA-Z0-9_\-\. ]+\.(jpe?g|png|gif|webp)$/i', $icon)) {
            Response::error('Nume icon invalid.', 422);
        }
        User::updateIcon((int)$p['sub'], $icon);
        Response::json(['user' => User::find((int)$p['sub'])]);
    }

    public function progress(): void
    {
        $p = Auth::require();
        Response::json(['progress' => Score::userProgress((int)$p['sub'])]);
    }
}
