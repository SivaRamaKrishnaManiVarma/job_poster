<?php
require_once 'includes/config.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/job_poster';

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
             xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

// Homepage
echo '<url>';
echo '<loc>' . $baseUrl . '/index.php</loc>';
echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
echo '<changefreq>daily</changefreq>';
echo '<priority>1.0</priority>';
echo '</url>';

// Static pages
$pages = [
    'about.php' => ['changefreq' => 'monthly', 'priority' => '0.8'],
    'contact.php' => ['changefreq' => 'monthly', 'priority' => '0.7'],
    'archive.php' => ['changefreq' => 'weekly', 'priority' => '0.8']
];

foreach ($pages as $page => $config) {
    echo '<url>';
    echo '<loc>' . $baseUrl . '/' . $page . '</loc>';
    echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
    echo '<changefreq>' . $config['changefreq'] . '</changefreq>';
    echo '<priority>' . $config['priority'] . '</priority>';
    echo '</url>';
}

// Job detail pages
$stmt = $pdo->query("
    SELECT slug, updated_at, title, company 
    FROM jobs 
    WHERE is_active = 1 
    AND slug IS NOT NULL 
    ORDER BY updated_at DESC 
    LIMIT 1000
");

while ($job = $stmt->fetch()) {
    echo '<url>';
    echo '<loc>' . $baseUrl . '/job-details.php?slug=' . urlencode($job['slug']) . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($job['updated_at'])) . '</lastmod>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.9</priority>';
    echo '</url>';
}

echo '</urlset>';
?>
