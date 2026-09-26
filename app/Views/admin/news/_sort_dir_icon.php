<?php
/**
 * Bars-and-arrow icon for the active sort direction.
 *
 * @var string $dir
 * @var string $class
 */
$class ??= 'h-4 w-4 shrink-0 pt-0.5';
?>
<svg class="<?= esc($class, 'attr') ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
    <?php if (($dir ?? 'desc') === 'asc'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12" />
    <?php else: ?>
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0-3.75-3.75M17.25 21 21 17.25" />
    <?php endif; ?>
</svg>
