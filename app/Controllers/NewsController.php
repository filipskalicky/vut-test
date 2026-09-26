<?php

namespace App\Controllers;

use App\Libraries\EditorJsRenderer;
use App\Models\NewsModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Public news listing with a fixed page size, plus a detail page.
 */
class NewsController extends BaseController
{
    /**
     * Newest-first public overview: title and publication date only.
     * Pagination links appear when the result spans more than one page.
     */
    public function index(): string
    {
        $model  = model(NewsModel::class);
        $search = NewsModel::resolveSearch($this->request->getGet('q'));
        $page   = max(1, (int) $this->request->getGet('page'));
        $items  = $model->getPublicPaginated(NewsModel::DEFAULT_PER_PAGE, $page, $search);

        return view('news/index', [
            'title'      => 'Novinky',
            'items'      => $items,
            'pager'      => $model->pager,
            'perPage'    => NewsModel::DEFAULT_PER_PAGE,
            'search'     => $search,
            'adminTools' => false,
            'seo'        => seo_public_listing($search, $page),
        ]);
    }

    /**
     * Full article. Hidden, future or expired items return 404 so the URL
     * cannot bypass the public visibility window.
     *
     * @return ResponseInterface|string
     */
    public function show(int $id)
    {
        $model = model(NewsModel::class);
        $item  = $model->findPublicById($id);

        if ($item === null) {
            throw PageNotFoundException::forPageNotFound('Aktualita nebyla nalezena.');
        }

        $renderer = new EditorJsRenderer();

        return view('news/show', [
            'title'    => $item['title'],
            'item'     => $item,
            'renderer' => $renderer,
            'seo'      => seo_article($item, $renderer->plainText($item['content'] ?? null)),
        ]);
    }
}
