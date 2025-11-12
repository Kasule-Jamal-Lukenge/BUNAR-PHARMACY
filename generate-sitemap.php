<?php
    // Run this script periodically (daily) via cron job

    // Establishing the database connection
    require_once('conn.php');

    $base_url = "https://www.bunarpharmacy.com";

    // Starting XML
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Adding homepage
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$base_url}/</loc>\n";
    $xml .= "    <changefreq>daily</changefreq>\n";
    $xml .= "    <priority>1.0</priority>\n";
    $xml .= "  </url>\n";

    // Adding static pages
    $static_pages = [
        'about' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        'services' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        'contact' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        'drugs' => ['priority' => '0.9', 'changefreq' => 'daily']
    ];

    foreach ($static_pages as $page => $settings) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$base_url}/{$page}.php</loc>\n";
        $xml .= "    <changefreq>{$settings['changefreq']}</changefreq>\n";
        $xml .= "    <priority>{$settings['priority']}</priority>\n";
        $xml .= "  </url>\n";
    }

    // Adding all drug pages
    $drugs_query = "SELECT slug, updated_at FROM drugs WHERE in_stock = TRUE ORDER BY drug_name ASC";
    $drugs_result = $conn->query($drugs_query);

    while ($drug = $drugs_result->fetch_assoc()) {
        $lastmod = date('Y-m-d', strtotime($drug['updated_at']));
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$base_url}/drug/{$drug['slug']}</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.9</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>';

    // Saving sitemap
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml', $xml);

    echo "Sitemap generated successfully!\n";
    echo "Total URLs: " . ($drugs_result->num_rows + count($static_pages) + 1) . "\n";

    $conn->close();
?>
