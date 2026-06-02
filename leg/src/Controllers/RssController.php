<?php
declare(strict_types=1);

namespace LeG\Controllers;

use LeG\Core\Response;
use LeG\Core\Security;
use LeG\Models\Score;

class RssController
{
    public function feed(): void
    {
        $period = strtolower($_GET['period'] ?? '');
        $period = in_array($period, ['week', 'month'], true) ? $period : null;
        $rows = Score::leaderboardGlobal(20, $period);
        $now = date(DATE_RSS);
        $base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>LeG - Leaderboard</title>' . "\n";
        $xml .= '<link>' . Security::cleanString($base . $basePath . '/app', 200) . '</link>' . "\n";
        $xml .= '<description>Clasamentul global al jucatorilor LeG (Learning by Playing Web Games).</description>' . "\n";
        $xml .= '<language>ro-RO</language>' . "\n";
        $xml .= '<lastBuildDate>' . $now . '</lastBuildDate>' . "\n";

        $i = 1;
        foreach ($rows as $r) {
            $u  = htmlspecialchars($r['username'], ENT_QUOTES | ENT_XML1, 'UTF-8');
            $sc = (int)$r['total_score'];
            $pl = (int)$r['plays'];
            $lp = $r['last_played'] ?: $now;
            try { $lp = date(DATE_RSS, strtotime($lp)); } catch (\Throwable) {}
            $xml .= "  <item>\n";
            $xml .= "    <title>#$i - $u (scor: $sc)</title>\n";
            $xml .= "    <link>{$base}{$basePath}/app</link>\n";
            $xml .= "    <description>Jucatorul $u are un scor cumulat de $sc puncte din $pl jocuri.</description>\n";
            $xml .= "    <guid isPermaLink=\"false\">leg-leaderboard-$i-$u</guid>\n";
            $xml .= "    <pubDate>$lp</pubDate>\n";
            $xml .= "  </item>\n";
            $i++;
        }

        $xml .= "</channel>\n</rss>\n";
        Response::xml($xml);
    }
}
