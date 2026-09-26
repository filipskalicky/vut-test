<?php

/**
 * Copy and SEO payload for branded HTTP error pages.
 *
 * @return array{
 *     title: string,
 *     errorCode: int,
 *     errorHeading: string,
 *     seo: array<string, mixed>
 * }
 */
function error_page_data(int $status): array
{
    helper(['url', 'seo']);

    $headings = [
        400 => 'Špatný požadavek',
        403 => 'Přístup odepřen',
        404 => 'Stránka nenalezena',
        500 => 'Něco se pokazilo',
    ];

    $heading = $headings[$status] ?? $headings[500];
    $status  = isset($headings[$status]) ? $status : 500;

    return [
        'title'        => $heading,
        'errorCode'    => $status,
        'errorHeading' => $heading,
        'seo'          => seo_meta([
            'title'       => seo_document_title($heading),
            'description' => $heading,
            'robots'      => 'noindex, nofollow',
            'canonical'   => current_url(),
            'jsonLd'      => null,
        ]),
    ];
}
