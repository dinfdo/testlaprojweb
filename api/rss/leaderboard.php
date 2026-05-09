<?php
require_once __DIR__ . '/../helpers.php';

only_method('GET');

$pdo  = get_db();
$stmt = $pdo->prepare('
    SELECT u.username,
           COALESCE(SUM(up.best_score), 0) AS total_score,
           COUNT(up.level_id) FILTER (WHERE up.completed = TRUE) AS levels_completed
    FROM users u
    LEFT JOIN user_progress up ON up.user_id = u.id
    WHERE u.role = \'user\'
    GROUP BY u.id, u.username
    ORDER BY total_score DESC
    LIMIT 20
');
$stmt->execute();
$rows = $stmt->fetchAll();

$baseUrl  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_XML1);
$now      = date(DATE_RSS);

header('Content-Type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
  <channel>
    <title>LeG – Leaderboard</title>
    <link><?= $baseUrl ?>/public/leaderboard.html</link>
    <description>Top players of the LeG learning platform</description>
    <language>en</language>
    <lastBuildDate><?= $now ?></lastBuildDate>
<?php foreach ($rows as $i => $row): ?>
    <item>
      <title>#<?= $i + 1 ?> – <?= htmlspecialchars($row['username'], ENT_XML1) ?></title>
      <description>Score: <?= (int)$row['total_score'] ?> pts | Levels completed: <?= (int)$row['levels_completed'] ?></description>
      <link><?= $baseUrl ?>/public/leaderboard.html</link>
      <guid isPermaLink="false">leg-leaderboard-<?= htmlspecialchars($row['username'], ENT_XML1) ?></guid>
      <pubDate><?= $now ?></pubDate>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
