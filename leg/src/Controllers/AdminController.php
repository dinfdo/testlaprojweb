<?php
declare(strict_types=1);

namespace LeG\Controllers;

use LeG\Core\Auth;
use LeG\Core\Database;
use LeG\Core\Response;
use LeG\Core\Security;
use LeG\Models\Component;
use LeG\Models\User;
use LeG\Models\Score;
use LeG\Services\ExportService;
use LeG\Services\ImportService;

class AdminController
{
    public function users(): void
    {
        Auth::requireAdmin();
        Response::json(['users' => User::all()]);
    }

    public function deleteUser(string $id): void
    {
        Auth::requireAdmin();
        $uid = Security::int($id);
        if ($uid <= 0) Response::error('Invalid id', 422);
        User::delete($uid);
        Response::json(['ok' => true]);
    }

    public function stats(): void
    {
        Auth::requireAdmin();
        $db = Database::pdo();
        Response::json([
            'users'      => (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'components' => (int)$db->query('SELECT COUNT(*) FROM components')->fetchColumn(),
            'games'      => (int)$db->query('SELECT COUNT(*) FROM games')->fetchColumn(),
            'scores'     => (int)$db->query('SELECT COUNT(*) FROM scores')->fetchColumn(),
        ]);
    }

    public function createComponent(): void
    {
        Auth::requireAdmin();
        $b = Security::jsonBody();
        $slug = Security::cleanString($b['slug'] ?? '', 60);
        $name = Security::cleanString($b['name'] ?? '', 80);
        $cat  = Security::cleanString($b['category'] ?? 'core', 30);
        $short= Security::cleanString($b['short_desc'] ?? '', 160);
        $desc = Security::cleanString($b['description'] ?? '', 2000);
        $icon = Security::cleanString($b['icon'] ?? 'this_computer.png', 80);
        $specs= is_array($b['specs'] ?? null) ? $b['specs'] : [];

        if ($slug === '' || $name === '') Response::error('Slug si name sunt obligatorii.', 422);

        try {
            $id = Component::create([
                'slug' => $slug, 'name' => $name, 'category' => $cat,
                'short_desc' => $short, 'description' => $desc,
                'icon' => $icon, 'specs' => $specs,
            ]);
        } catch (\Throwable $e) {
            Response::error('Eroare la creare (slug duplicat?)', 409);
        }
        Response::json(['ok' => true, 'id' => $id], 201);
    }

    public function deleteComponent(string $id): void
    {
        Auth::requireAdmin();
        Component::delete(Security::int($id));
        Response::json(['ok' => true]);
    }

    public function icons(): void
    {
        Auth::requireAdmin();
        $dir    = __DIR__ . '/../../assets/icons/';
        $exts   = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
        $result = [];
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (!in_array($ext, $exts, true)) continue;
            $result[] = $f;
        }
        sort($result);
        Response::json(['icons' => $result]);
    }

    public function exportUsersCsv(): void
    {
        Auth::requireAdmin();
        $rows = User::all();
        Response::csv(ExportService::toCsv($rows), 'leg_users.csv');
    }

    public function exportScoresJson(): void
    {
        Auth::requireAdmin();
        Response::json(['scores' => Score::allScoresWithUser()]);
    }

    public function importComponents(): void
    {
        Auth::requireAdmin();
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $rows = [];

        if (str_contains($contentType, 'multipart/form-data')) {
            $file = $_FILES['file'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                Response::error('Fisier lipsa sau eroare la incarcare.', 422);
            }
            $content = file_get_contents($file['tmp_name']);
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext === 'csv') {
                $rows = ImportService::fromCsv($content);
            } elseif ($ext === 'json') {
                $rows = ImportService::fromJson($content);
            } else {
                Response::error('Tip de fisier nesuportat. Foloseste .csv sau .json.', 422);
            }
        } else {
            $b    = Security::jsonBody();
            $rows = $b['components'] ?? [];
        }

        if (empty($rows)) Response::error('Nicio componenta gasita in fisier.', 422);

        $db  = Database::pdo();
        $ins = $db->prepare(
            'INSERT OR IGNORE INTO components (slug, name, category, short_desc, description, icon, specs)
             VALUES (?,?,?,?,?,?,?)'
        );

        $inserted = 0;
        $skipped  = 0;
        foreach ($rows as $row) {
            $slug = Security::cleanString($row['slug'] ?? '', 60);
            $name = Security::cleanString($row['name'] ?? '', 80);
            if ($slug === '' || $name === '') { $skipped++; continue; }

            $specs = $row['specs'] ?? [];
            if (is_string($specs)) {
                $decoded = json_decode($specs, true);
                $specs   = is_array($decoded) ? $decoded : [];
            }

            $ins->execute([
                $slug,
                $name,
                Security::cleanString($row['category']    ?? 'core',              30),
                Security::cleanString($row['short_desc']  ?? '',                 160),
                Security::cleanString($row['description'] ?? '',                2000),
                Security::cleanString($row['icon']        ?? 'this_computer.png', 80),
                json_encode($specs, JSON_UNESCAPED_UNICODE),
            ]);
            $ins->rowCount() > 0 ? $inserted++ : $skipped++;
        }

        Response::json(['ok' => true, 'inserted' => $inserted, 'skipped' => $skipped]);
    }
}
