<?php

namespace App\Controllers;

use App\Models\NewsModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * robots.txt and sitemap.xml for public news.
 */
class SeoController extends BaseController
{
    public function robots(): ResponseInterface
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /logout',
            '',
            'Sitemap: ' . site_url('sitemap.xml'),
            '',
        ];

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setBody(implode("\n", $lines));
    }

    public function sitemap(): ResponseInterface
    {
        $items = [];

        foreach (model(NewsModel::class)->getPublicSitemapEntries() as $item) {
            $timestamp = strtotime((string) ($item['updated_at'] ?? $item['visible_from'] ?? ''));

            $items[] = [
                'loc'     => site_url('news/' . (int) $item['id']),
                'lastmod' => $timestamp !== false ? date('Y-m-d', $timestamp) : null,
            ];
        }

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setBody(view('seo/sitemap', [
                'home'  => site_url('/'),
                'items' => $items,
            ], ['debug' => false]));
    }
}
