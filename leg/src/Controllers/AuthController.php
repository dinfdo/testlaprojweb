<?php
declare(strict_types=1);

namespace LeG\Controllers;

use LeG\Core\Auth;
use LeG\Core\JWT;
use LeG\Core\Response;
use LeG\Core\Security;
use LeG\Models\User;

class AuthController
{
    public function register(): void
    {
        $b = Security::jsonBody();
        $username = Security::cleanString($b['username'] ?? '', 32);
        $email    = Security::cleanString($b['email']    ?? '', 120);
        $password = (string)($b['password'] ?? '');
        $icon     = Security::cleanString($b['profile_icon'] ?? 'astronaut.jpg', 80);

        if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,32}$/', $username)) {
            Response::error('Username invalid (3-32 caractere, alfanumeric).', 422);
        }
        if (strlen($password) < 6) {
            Response::error('Parola trebuie sa aiba minim 6 caractere.', 422);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email invalid.', 422);
        }
        if (User::findByUsername($username)) {
            Response::error('Username deja folosit.', 409);
        }

        $id = User::create($username, $email, password_hash($password, PASSWORD_BCRYPT), $icon);
        $user = User::find($id);
        $token = JWT::encode(['sub' => $id, 'usr' => $username, 'admin' => false, 'type' => 'access']);
        Response::json([
            'token'         => $token,
            'refresh_token' => JWT::issueRefresh($id),
            'user'          => $user,
        ], 201);
    }

    public function login(): void
    {
        $b = Security::jsonBody();
        $username = Security::cleanString($b['username'] ?? '', 32);
        $password = (string)($b['password'] ?? '');
        $u = User::findByUsername($username);
        if (!$u || !password_verify($password, $u['password_hash'])) {
            Response::error('Credentiale invalide.', 401);
        }
        unset($u['password_hash']);
        $id = (int)$u['id'];
        $token = JWT::encode([
            'sub' => $id,
            'usr' => $u['username'],
            'admin' => (bool)$u['is_admin'],
            'type' => 'access',
        ]);
        Response::json([
            'token'         => $token,
            'refresh_token' => JWT::issueRefresh($id),
            'user'          => $u,
        ]);
    }

    public function refresh(): void
    {
        $b = Security::jsonBody();
        $rawRefresh = (string)($b['refresh_token'] ?? '');
        try {
            $p = JWT::verifyRefresh($rawRefresh);
        } catch (\RuntimeException $e) {
            Response::error($e->getMessage(), 401);
        }
        $u = User::find((int)$p['sub']);
        if (!$u) Response::error('User not found.', 404);
        $token = JWT::encode([
            'sub'   => (int)$u['id'],
            'usr'   => $u['username'],
            'admin' => (bool)$u['is_admin'],
            'type'  => 'access',
        ]);
        Response::json(['token' => $token]);
    }

    public function me(): void
    {
        $p = Auth::require();
        $u = User::find((int)$p['sub']);
        if (!$u) Response::error('User not found', 404);
        Response::json(['user' => $u]);
    }
}
