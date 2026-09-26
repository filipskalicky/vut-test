<?php
helper('seo');

/**
 * Document title, robots, canonical, favicons, Open Graph and JSON-LD.
 *
 * @var array<string, mixed>|null $seo
 * @var string|null               $title
 */
$seo = is_array($seo ?? null) ? seo_meta($seo) : seo_meta([
    'title' => seo_document_title((string) ($title ?? seo_site_name())),
]);
?>
    <title><?= esc($seo['title']) ?></title>
    <meta name="description" content="<?= esc($seo['description']) ?>">
    <meta name="robots" content="<?= esc($seo['robots']) ?>">
    <link rel="canonical" href="<?= esc($seo['canonical']) ?>">
    <link rel="alternate" hreflang="cs" href="<?= esc($seo['canonical']) ?>">
    <link rel="icon" href="<?= esc(base_url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= esc(base_url('favicon.ico')) ?>" sizes="32x32">
    <link rel="apple-touch-icon" href="<?= esc(base_url('apple-touch-icon.png')) ?>">
    <link rel="manifest" href="<?= esc(base_url('site.webmanifest')) ?>">
    <meta name="theme-color" content="#7c3aed">
    <meta name="application-name" content="<?= esc($seo['siteName']) ?>">
    <meta name="color-scheme" content="light">
    <meta property="og:locale" content="cs_CZ">
    <meta property="og:type" content="<?= esc($seo['ogType']) ?>">
    <meta property="og:site_name" content="<?= esc($seo['siteName']) ?>">
    <meta property="og:title" content="<?= esc($seo['title']) ?>">
    <meta property="og:description" content="<?= esc($seo['description']) ?>">
    <meta property="og:url" content="<?= esc($seo['canonical']) ?>">
    <meta property="og:image" content="<?= esc($seo['ogImage']) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= esc($seo['ogImageAlt']) ?>">
<?php if ($seo['ogType'] === 'article' && $seo['publishedAt'] !== null): ?>
    <meta property="article:published_time" content="<?= esc($seo['publishedAt']) ?>">
<?php endif; ?>
<?php if ($seo['ogType'] === 'article' && $seo['modifiedAt'] !== null): ?>
    <meta property="article:modified_time" content="<?= esc($seo['modifiedAt']) ?>">
<?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($seo['title']) ?>">
    <meta name="twitter:description" content="<?= esc($seo['description']) ?>">
    <meta name="twitter:image" content="<?= esc($seo['ogImage']) ?>">
<?php if (is_array($seo['jsonLd'])): ?>
    <script type="application/ld+json"><?= json_encode($seo['jsonLd'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>
<?php endif; ?>
