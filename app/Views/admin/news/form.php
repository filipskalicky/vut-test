<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <a href="<?= site_url('admin/news') ?>" class="nav-user">
        <span class="nav-user-icon" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </span>
        <span class="nav-user-label">Zpět na seznam</span>
    </a>

    <?php $itemId = isset($item['id']) ? (int) $item['id'] : 0; ?>
    <div class="mb-8 mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <h1 class="page-title-sm"><?= esc($title) ?></h1>
        <div class="flex flex-wrap items-center gap-2">
            <?php if ($itemId > 0): ?>
                <form method="post" action="<?= site_url('admin/news/delete/' . $itemId) ?>" data-confirm-delete>
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn-danger" aria-label="Smazat">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </form>
            <?php endif; ?>
            <button type="submit" form="news-form" name="after_save" value="stay" class="btn-secondary">
                Uložit
            </button>
            <button type="submit" form="news-form" name="after_save" value="back" class="btn-primary">
                Uložit a zpět
            </button>
        </div>
    </div>

    <form id="news-form" method="post" action="<?= esc($action) ?>" class="js-news-form" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="after_save" id="after_save" value="stay">

        <?php if (! empty($errors)): ?>
            <script type="application/json" data-app-toasts><?= json_encode(
                [['type' => 'error', 'text' => implode("\n", array_map('strval', $errors))]],
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS,
            ) ?></script>
        <?php endif; ?>

        <div class="card card-full-mobile space-y-6">
            <div>
                <label for="title" class="label">Nadpis</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    required
                    maxlength="255"
                    value="<?= esc(old('title', $item['title'] ?? '')) ?>"
                    class="input"
                >
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="visible_from" class="label">
                        Publikováno od
                    </label>
                    <input
                        type="text"
                        id="visible_from"
                        name="visible_from"
                        required
                        readonly
                        inputmode="none"
                        autocomplete="off"
                        placeholder="Vyberte datum a čas"
                        value="<?= esc(old('visible_from', format_datetime_input($item['visible_from'] ?? date('Y-m-d H:i')))) ?>"
                        class="js-datepicker input"
                    >
                </div>
                <div>
                    <label for="visible_to" class="label">
                        Publikováno do
                    </label>
                    <div class="relative">
                        <?php $visibleToValue = old('visible_to', format_datetime_input($item['visible_to'] ?? '')); ?>
                        <input
                            type="text"
                            id="visible_to"
                            name="visible_to"
                            readonly
                            inputmode="none"
                            autocomplete="off"
                            placeholder="Vyberte datum a čas"
                            value="<?= esc($visibleToValue) ?>"
                            class="js-datepicker js-datepicker-optional input<?= $visibleToValue !== '' ? ' pr-10' : '' ?>"
                        >
                        <?= view('partials/input_clear', ['hidden' => $visibleToValue === '']) ?>
                    </div>
                </div>
            </div>

            <div>
                <label class="label">Obsah</label>
                <div
                    id="editorjs"
                    data-initial="<?= esc(old('content', $item['content'] ?? ''), 'attr') ?>"
                ></div>
                <input type="hidden" name="content" id="content" value="<?= esc(old('content', $item['content'] ?? ''), 'attr') ?>">
            </div>
        </div>
    </form>
<?= $this->endSection() ?>
