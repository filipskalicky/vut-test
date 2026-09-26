<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="mb-10">
        <h1 class="page-title">Novinky</h1>
    </div>

    <?= view('pager/listing_search', [
        'search'     => $search,
        'adminTools' => false,
    ]) ?>

    <div data-listing-results>
    <?php if ($items === []): ?>
        <p class="card p-8 text-gray-600">
            <?= $search !== ''
                ? 'Žádné aktuality neodpovídají hledání.'
                : 'Momentálně nejsou k zobrazení žádné aktuality.' ?>
        </p>
    <?php else: ?>
        <ul class="space-y-4">
            <?php foreach ($items as $item): ?>
                <li>
                    <a
                        href="<?= site_url('news/' . $item['id']) ?>"
                        class="card group flex items-center gap-4 px-6 py-5 transition-all hover:shadow-soft-lg"
                    >
                        <div class="flex min-w-0 flex-1 flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
                            <h2 class="min-w-0 flex-1 text-lg font-semibold text-gray-900 transition-colors group-hover:text-primary-700">
                                <?= esc($item['title']) ?>
                            </h2>
                            <time
                                class="shrink-0 text-sm font-medium text-gray-500"
                                datetime="<?= esc(substr((string) $item['visible_from'], 0, 10)) ?>"
                            >
                                <?= esc(format_czech_date($item['visible_from'], false)) ?>
                            </time>
                        </div>
                        <span class="nav-user-icon shrink-0" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?= view('pager/listing_controls', [
            'pager'      => $pager,
            'adminTools' => false,
        ]) ?>
    <?php endif; ?>
    </div>
<?= $this->endSection() ?>
