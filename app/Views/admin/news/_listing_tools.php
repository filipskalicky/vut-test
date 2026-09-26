<?php
/**
 * Sort and status-filter menus for the admin listing.
 *
 * @var string     $sort
 * @var string     $dir
 * @var list<string> $statuses
 */

use App\Models\NewsModel;

$sortActive   = $sort !== NewsModel::DEFAULT_SORT || $dir !== NewsModel::DEFAULT_DIR;
$filterActive = $statuses !== [];

$statusItems = [
    'scheduled' => ['label' => 'Budoucí', 'dot' => 'bg-primary-600', 'text' => 'text-primary-600'],
    'visible'   => ['label' => 'Současné', 'dot' => 'bg-green-600', 'text' => 'text-green-600'],
    'expired'   => ['label' => 'Uplynulé', 'dot' => 'bg-red-600', 'text' => 'text-red-600'],
];
?>
<div class="flex shrink-0 items-center gap-2" data-listing-tools>
    <div class="relative" data-listing-menu="sort">
        <button
            type="button"
            class="listing-tool<?= $sortActive ? ' listing-tool-active' : '' ?>"
            data-listing-menu-toggle
            aria-expanded="false"
            aria-haspopup="true"
            aria-label="Řazení"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v18m13.5-4.5L16.5 21m0 0L12 16.5m4.5 4.5V3" />
            </svg>
        </button>
        <div class="absolute right-0 top-full z-50 hidden pt-2" data-listing-menu-panel>
            <div class="min-w-60 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                <?php foreach ([
                    'title'     => 'Název',
                    'published' => 'Datum publikace',
                    'created'   => 'Datum vytvoření',
                    'updated'   => 'Datum poslední úpravy',
                ] as $field => $label): ?>
                    <?php $isCurrent = $sort === $field; ?>
                    <button
                        type="button"
                        class="menu-item flex w-full items-center justify-between gap-3<?= $isCurrent ? ' font-semibold text-gray-900' : '' ?>"
                        data-sort="<?= $field ?>"
                    >
                        <span><?= $label ?></span>
                        <?php if ($isCurrent): ?>
                            <?= view('admin/news/_sort_dir_icon', ['dir' => $dir]) ?>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="relative" data-listing-menu="filter">
        <button
            type="button"
            class="listing-tool<?= $filterActive ? ' listing-tool-active' : '' ?>"
            data-listing-menu-toggle
            aria-expanded="false"
            aria-haspopup="true"
            aria-label="Filtrování"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v6.668a2.25 2.25 0 01-1.166 1.97l-1.5.75a2.25 2.25 0 01-3.182-1.97V13.43a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
            </svg>
        </button>
        <div class="absolute right-0 top-full z-50 hidden pt-2" data-listing-menu-panel>
            <div class="min-w-52 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                <?php foreach ($statusItems as $value => $item): ?>
                    <?php $isSelected = in_array($value, $statuses, true); ?>
                    <button
                        type="button"
                        class="menu-item flex w-full items-center gap-2.5<?= $isSelected ? ' bg-gray-100' : '' ?>"
                        data-status="<?= $value ?>"
                        aria-pressed="<?= $isSelected ? 'true' : 'false' ?>"
                    >
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full <?= $item['dot'] ?>" aria-hidden="true"></span>
                        <span class="flex-1 text-left font-medium <?= $item['text'] ?>"><?= $item['label'] ?></span>
                        <?php if ($isSelected): ?>
                            <svg class="h-4 w-4 shrink-0 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
