<span class="inline-flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs <?= esc($tone ?? 'text-gray-600', 'attr') ?>">
    <span><?= esc(format_czech_date($from)) ?></span>
    <?php if (! empty($to)): ?>
        <svg class="h-3.5 w-3.5 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
        </svg>
        <span><?= esc(format_czech_date($to)) ?></span>
    <?php endif; ?>
</span>
