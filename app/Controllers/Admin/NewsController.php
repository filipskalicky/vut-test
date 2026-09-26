<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsModel;

/**
 * Authenticated CRUD for news items.
 */
class NewsController extends BaseController
{
    /**
     * All news, newest first, including future and expired items.
     */
    public function index(): string
    {
        $model    = model(NewsModel::class);
        $perPage  = NewsModel::resolvePerPage($this->request->getGet('per_page'));
        $search   = NewsModel::resolveSearch($this->request->getGet('q'));
        $sort     = NewsModel::resolveSort($this->request->getGet('sort'));
        $dir      = NewsModel::resolveDir($this->request->getGet('dir'));
        $statuses = NewsModel::resolveStatuses($this->request->getGet('status'));
        $page     = max(1, (int) $this->request->getGet('page'));

        return view('admin/news/index', [
            'title'          => 'Aktuality',
            'items'          => $model->getAllPaginated($perPage, $page, $search, $sort, $dir, $statuses),
            'pager'          => $model->pager,
            'perPage'        => $perPage,
            'perPageOptions' => NewsModel::PER_PAGE_OPTIONS,
            'search'         => $search,
            'sort'           => $sort,
            'dir'            => $dir,
            'statuses'       => $statuses,
            'adminTools'     => true,
            'seo'            => seo_private('Aktuality', current_url()),
        ]);
    }

    /**
     * Create form. visible_from is pre-filled with the current datetime.
     */
    public function new(): string
    {
        return view('admin/news/form', $this->formData(
            'Nová aktualita',
            [
                'title'        => '',
                'content'      => '',
                'visible_from' => date('Y-m-d H:i'),
                'visible_to'   => '',
            ],
            site_url('admin/news'),
        ));
    }

    /**
     * Persist a new item.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function create()
    {
        $model  = model(NewsModel::class);
        $payload = $this->payloadFromRequest();

        if (! $model->insert($payload)) {
            return view('admin/news/form', $this->formData(
                'Nová aktualita',
                $payload,
                site_url('admin/news'),
                $model->errors(),
            ));
        }

        return $this->redirectAfterSave((int) $model->getInsertID(), 'Aktualita byla vložena.');
    }

    /**
     * Edit form.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function edit(int $id)
    {
        $item = model(NewsModel::class)->find($id);

        if ($item === null) {
            return redirect()->to('/admin/news')->with('error', 'Aktualita nebyla nalezena.');
        }

        return view('admin/news/form', $this->formData(
            'Upravit aktualitu',
            $item,
            site_url('admin/news/update/' . $id),
        ));
    }

    /**
     * Update an existing item.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function update(int $id)
    {
        $model = model(NewsModel::class);
        $item  = $model->find($id);

        if ($item === null) {
            return redirect()->to('/admin/news')->with('error', 'Aktualita nebyla nalezena.');
        }

        $payload = $this->payloadFromRequest();

        if (! $model->update($id, $payload)) {
            $payload['id'] = $id;

            return view('admin/news/form', $this->formData(
                'Upravit aktualitu',
                $payload,
                site_url('admin/news/update/' . $id),
                $model->errors(),
            ));
        }

        return $this->redirectAfterSave($id, 'Aktualita byla uložena.');
    }

    /**
     * Delete an item.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete(int $id)
    {
        $model = model(NewsModel::class);

        if ($model->find($id) === null) {
            return redirect()->to('/admin/news')->with('error', 'Aktualita nebyla nalezena.');
        }

        $model->delete($id);

        return redirect()->to('/admin/news')->with('success', 'Aktualita byla smazána.');
    }

    /**
     * Shared view payload so the layout can load Editor.js assets.
     *
     * @param array<string, mixed>      $item
     * @param array<string, string>|null $errors
     *
     * @return array<string, mixed>
     */
    private function formData(string $title, array $item, string $action, ?array $errors = null): array
    {
        return [
            'title'         => $title,
            'item'          => $item,
            'action'        => $action,
            'errors'        => $errors,
            'includeEditor' => true,
            'seo'           => seo_private($title, current_url()),
        ];
    }

    /**
     * Stay on the edit form after Uložit; return to the list after Uložit a zpět.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private function redirectAfterSave(int $id, string $success)
    {
        if ($this->shouldReturnToList()) {
            return redirect()->to('/admin/news')->with('success', $success);
        }

        return redirect()->to('/admin/news/edit/' . $id)->with('success', $success);
    }

    /**
     * Both a hidden field and the clicked submit button share after_save.
     */
    private function shouldReturnToList(): bool
    {
        $after = $this->request->getPost('after_save');

        if (is_array($after)) {
            return in_array('back', $after, true);
        }

        return (string) $after === 'back';
    }

    /**
     * @return array{title: string, content: string, visible_from: string, visible_to: string|null}
     */
    private function payloadFromRequest(): array
    {
        $visibleTo = trim((string) $this->request->getPost('visible_to'));

        return [
            'title'        => trim((string) $this->request->getPost('title')),
            'content'      => (string) $this->request->getPost('content'),
            'visible_from' => trim((string) $this->request->getPost('visible_from')),
            'visible_to'   => $visibleTo === '' ? null : $visibleTo,
        ];
    }
}
