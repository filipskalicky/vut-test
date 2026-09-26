<?php

/**
 * Shared SEO metadata for public pages, admin screens and robots/sitemap.
 */

function seo_site_name(): string
{
    return 'Aktuality';
}

function seo_default_description(): string
{
    return 'Aktuální novinky a oznámení. Přehled zveřejněných aktualit.';
}

/**
 * @param array<string, mixed> $overrides
 *
 * @return array<string, mixed>
 */
function seo_meta(array $overrides = []): array
{
    $site = seo_site_name();

    $defaults = [
        'siteName'    => $site,
        'title'       => $site,
        'description' => seo_default_description(),
        'canonical'   => current_url(),
        'robots'      => 'index, follow',
        'ogType'      => 'website',
        'ogImage'     => base_url('og-image.png'),
        'ogImageAlt'  => $site,
        'publishedAt' => null,
        'modifiedAt'  => null,
        'jsonLd'      => null,
    ];

    $meta = array_replace($defaults, $overrides);

    if ($meta['jsonLd'] === null && str_starts_with((string) $meta['robots'], 'index')) {
        $meta['jsonLd'] = seo_website_json_ld();
    }

    return $meta;
}

function seo_document_title(string $page, string $suffix = ''): string
{
    $tail = $suffix !== '' ? $suffix : seo_site_name();

    if ($page === '' || $page === $tail) {
        return $tail;
    }

    return $page . ' | ' . $tail;
}

/**
 * @return array<string, mixed>
 */
function seo_public_listing(string $search, int $page): array
{
    $query = [];

    if ($search !== '') {
        $query['q'] = $search;
    }

    if ($page > 1) {
        $query['page'] = $page;
    }

    $canonical = site_url('/');

    if ($query !== []) {
        $canonical .= '?' . http_build_query($query);
    }

    if ($search !== '') {
        return seo_meta([
            'title'       => seo_document_title('Hledání: ' . $search),
            'description' => 'Výsledky hledání „' . $search . '“ na webu Aktuality.',
            'canonical'   => $canonical,
            'robots'      => 'noindex, follow',
            'jsonLd'      => null,
        ]);
    }

    $title = $page > 1
        ? seo_document_title('Novinky, strana ' . $page)
        : seo_document_title('Novinky');

    return seo_meta([
        'title'       => $title,
        'description' => seo_default_description(),
        'canonical'   => $canonical,
        'robots'      => 'index, follow',
    ]);
}

/**
 * @param array<string, mixed> $item
 *
 * @return array<string, mixed>
 */
function seo_article(array $item, string $excerpt): array
{
    $title       = (string) ($item['title'] ?? '');
    $canonical   = site_url('news/' . (int) ($item['id'] ?? 0));
    $description = $excerpt !== '' ? $excerpt : $title;
    $published   = seo_iso_datetime($item['visible_from'] ?? null);
    $modified    = seo_iso_datetime($item['updated_at'] ?? $item['visible_from'] ?? null);

    return seo_meta([
        'title'       => seo_document_title($title),
        'description' => $description,
        'canonical'   => $canonical,
        'robots'      => 'index, follow',
        'ogType'      => 'article',
        'publishedAt' => $published,
        'modifiedAt'  => $modified,
        'jsonLd'      => [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => $title,
            'description'      => $description,
            'inLanguage'       => 'cs',
            'mainEntityOfPage' => $canonical,
            'url'              => $canonical,
            'datePublished'    => $published,
            'dateModified'     => $modified,
            'image'            => base_url('og-image.png'),
            'publisher'        => [
                '@type' => 'Organization',
                'name'  => seo_site_name(),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => base_url('apple-touch-icon.png'),
                ],
            ],
        ],
    ]);
}

/**
 * @return array<string, mixed>
 */
function seo_private(string $pageTitle, ?string $canonical = null, ?string $description = null): array
{
    return seo_meta([
        'title'       => seo_document_title($pageTitle, 'Administrace'),
        'description' => $description ?? 'Administrace webu Aktuality.',
        'canonical'   => $canonical ?? current_url(),
        'robots'      => 'noindex, nofollow',
        'jsonLd'      => null,
    ]);
}

/**
 * @return array<string, mixed>
 */
function seo_login(): array
{
    return seo_meta([
        'title'       => seo_document_title('Přihlášení'),
        'description' => 'Přihlášení do administrace webu Aktuality.',
        'canonical'   => site_url('login'),
        'robots'      => 'noindex, nofollow',
        'jsonLd'      => null,
    ]);
}

function seo_iso_datetime(mixed $value): ?string
{
    if ($value === null || $value === '') {
        return null;
    }

    $timestamp = strtotime((string) $value);

    if ($timestamp === false) {
        return null;
    }

    return date('c', $timestamp);
}

/**
 * @return array<string, mixed>
 */
function seo_website_json_ld(): array
{
    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => seo_site_name(),
        'url'             => site_url('/'),
        'description'     => seo_default_description(),
        'inLanguage'      => 'cs',
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => rtrim(site_url('/'), '/') . '/?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
}
