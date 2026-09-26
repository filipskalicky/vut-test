<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= esc($home) ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
<?php foreach ($items as $item): ?>
    <url>
        <loc><?= esc($item['loc']) ?></loc>
<?php if (! empty($item['lastmod'])): ?>
        <lastmod><?= esc($item['lastmod']) ?></lastmod>
<?php endif; ?>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>
</urlset>
