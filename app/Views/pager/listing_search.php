<?php
/**
 * Live listing search. Admin can also sort and filter from icons beside the input.
 *
 * @var string       $search
 * @var int          $perPage
 * @var list<int>    $perPageOptions
 * @var bool         $adminTools
 * @var string       $sort
 * @var string       $dir
 * @var list<string> $statuses
 */

use App\Models\NewsModel;

$adminTools      = $adminTools === true;
$sort            = is_string($sort ?? null) ? $sort : NewsModel::DEFAULT_SORT;
$dir             = is_string($dir ?? null) ? $dir : NewsModel::DEFAULT_DIR;
$statuses        = is_array($statuses ?? null) ? $statuses : [];
$perPageOptions  = is_array($perPageOptions ?? null) ? $perPageOptions : NewsModel::PER_PAGE_OPTIONS;
?>
<form method="get" action="<?= current_url() ?>" class="mb-6" data-listing-search data-default-per-page="<?= NewsModel::ADMIN_DEFAULT_PER_PAGE ?>">
    <?php if ($adminTools): ?>
        <input type="hidden" name="sort" value="<?= esc($sort) ?>">
        <input type="hidden" name="dir" value="<?= esc($dir) ?>">
        <input type="hidden" name="status" value="<?= esc(implode(',', $statuses)) ?>">
    <?php endif; ?>

    <div class="flex items-center gap-2">
        <div class="relative min-w-0 flex-1">
            <label for="q" class="sr-only">Hledat</label>
            <input
                type="text"
                id="q"
                name="q"
                value="<?= esc($search) ?>"
                placeholder="Hledat"
                autocomplete="off"
                class="input<?= $search !== '' ? ' pr-10' : '' ?>"
            >
            <?= view('partials/input_clear', ['hidden' => $search === '']) ?>
        </div>

        <?php if ($adminTools): ?>
            <?= view('admin/news/_listing_tools', [
                'sort'     => $sort,
                'dir'      => $dir,
                'statuses' => $statuses,
            ]) ?>

        <div class="relative shrink-0" data-listing-menu="per-page" data-listing-per-page>
            <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">
            <button
                type="button"
                class="listing-tool listing-tool-count"
                data-listing-menu-toggle
                aria-expanded="false"
                aria-haspopup="true"
                aria-label="Na stránku"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                </svg>
                <span class="hidden min-w-[1.25rem] text-sm font-medium tabular-nums sm:inline" data-listing-per-page-value><?= (int) $perPage ?></span>
            </button>
            <div class="absolute right-0 top-full z-50 hidden pt-2" data-listing-menu-panel>
                <div class="min-w-24 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                    <?php foreach ($perPageOptions as $option): ?>
                        <?php $isCurrent = (int) $option === (int) $perPage; ?>
                        <button
                            type="button"
                            class="menu-item flex w-full items-center justify-between gap-3<?= $isCurrent ? ' bg-gray-100 font-semibold text-gray-900' : '' ?>"
                            data-per-page="<?= $option ?>"
                        >
                            <span><?= $option ?></span>
                            <?php if ($isCurrent): ?>
                                <svg class="h-4 w-4 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</form>
