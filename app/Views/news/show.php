<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <article>
        <a href="<?= site_url('/') ?>" class="nav-user">
            <span class="nav-user-icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </span>
            <span class="nav-user-label">Zpět na přehled</span>
        </a>

        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3">
                <h1 class="page-title">
                    <?= esc($item['title']) ?>
                </h1>
                <time class="text-sm font-medium text-primary-600" datetime="<?= esc(substr((string) $item['visible_from'], 0, 10)) ?>">
                    <?= esc(format_czech_date($item['visible_from'], false)) ?>
                </time>
            </div>
            <?php if (session()->get('user_id')): ?>
                <a href="<?= site_url('admin/news/edit/' . $item['id']) ?>" class="btn-secondary h-fit w-fit shrink-0 self-start sm:self-center">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L16.862 4.487" />
                    </svg>
                    Upravit
                </a>
            <?php endif; ?>
        </div>
        <div class="news-content card card-full-mobile mt-8">
            <?= $renderer->render($item['content']) ?>
        </div>
    </article>
<?= $this->endSection() ?>
