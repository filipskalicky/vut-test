<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="page-kicker">Administrace</span>
            <h1 class="page-title">Aktuality</h1>
        </div>
        <a href="<?= site_url('admin/news/new') ?>" class="btn-primary">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nová aktualita
        </a>
    </div>

    <?= view('pager/listing_search', [
        'search'         => $search,
        'perPage'        => $perPage,
        'perPageOptions' => $perPageOptions,
        'adminTools'     => true,
        'sort'           => $sort,
        'dir'            => $dir,
        'statuses'       => $statuses,
    ]) ?>

    <div data-listing-results>
    <?php if ($items === []): ?>
        <p class="card p-8 text-gray-600">
            <?= ($search !== '' || $statuses !== [])
                ? 'Žádné aktuality neodpovídají zadaným kritériím.'
                : 'Zatím není vložená žádná aktualita.' ?>
        </p>
    <?php else: ?>
        <?php $newsModel = model(\App\Models\NewsModel::class); ?>

        <ul class="card divide-y divide-gray-100 md:hidden">
            <?php foreach ($items as $item): ?>
                <?php
                $tone = match ($newsModel->visibilityState($item)) {
                    'visible'   => 'text-green-600',
                    'scheduled' => 'text-primary-600',
                    default     => 'text-red-600',
                };
                ?>
                <li class="flex items-center gap-3 px-4 py-4">
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-gray-900"><?= esc($item['title']) ?></div>
                        <div class="mt-0.5">
                            <?= view('admin/news/_published_inline', [
                                'from' => $item['visible_from'],
                                'to'   => $item['visible_to'],
                                'tone' => $tone,
                            ]) ?>
                        </div>
                    </div>
                    <?= view('admin/news/_news_actions', ['item' => $item, 'iconOnly' => true]) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="card hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-100 font-bold text-gray-600">
                    <tr>
                        <th class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5">
                                Název
                                <?php if ($sort === 'title'): ?>
                                    <?= view('admin/news/_sort_dir_icon', ['dir' => $dir]) ?>
                                <?php endif; ?>
                            </span>
                        </th>
                        <th class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5">
                                Publikováno
                                <?php if ($sort === 'published'): ?>
                                    <?= view('admin/news/_sort_dir_icon', ['dir' => $dir]) ?>
                                <?php endif; ?>
                            </span>
                        </th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $tone = match ($newsModel->visibilityState($item)) {
                            'visible'   => 'text-green-600',
                            'scheduled' => 'text-primary-600',
                            default     => 'text-red-600',
                        };
                        ?>
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="px-5 py-4 font-medium text-gray-900"><?= esc($item['title']) ?></td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <?= view('admin/news/_published_stamp', ['value' => $item['visible_from'], 'tone' => $tone]) ?>
                                    <?php if ($item['visible_to']): ?>
                                        <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                        <?= view('admin/news/_published_stamp', ['value' => $item['visible_to'], 'tone' => $tone]) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <?= view('admin/news/_news_actions', ['item' => $item, 'iconOnly' => false]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= view('pager/listing_controls', [
            'pager'      => $pager,
            'adminTools' => true,
        ]) ?>
    <?php endif; ?>
    </div>
<?= $this->endSection() ?>
