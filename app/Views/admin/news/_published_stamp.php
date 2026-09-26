<div class="<?= esc($tone ?? 'text-gray-900', 'attr') ?>">
    <div class="font-medium"><?= esc(format_czech_date($value, false)) ?></div>
    <div class="mt-1 flex items-center gap-1.5 text-xs opacity-80">
        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <?= esc(format_czech_time($value)) ?>
    </div>
</div>
