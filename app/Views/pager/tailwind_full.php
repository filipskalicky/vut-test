<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * Number-only pager: 1 2 3 4 5 … 10 on the first pages. No arrow controls.
 *
 * @var PagerRenderer $pager
 */
helper('pager');

$pageCount = $pager->getPageCount();
$current   = $pager->getCurrentPageNumber();
$pager->setSurroundCount(max(0, $pageCount - 1));

$byNumber = [];
foreach ($pager->links() as $link) {
    $byNumber[(int) $link['title']] = $link;
}
?>

<nav aria-label="Stránkování aktualit">
    <ul class="flex flex-wrap items-center justify-center gap-2 sm:justify-end">
        <?php foreach (pager_compact_sequence($current, $pageCount) as $item): ?>
            <li class="h-10 w-10 shrink-0">
                <?php if ($item === '...'): ?>
                    <span class="pager-ellipsis" aria-hidden="true">...</span>
                <?php else: ?>
                    <?php $link = $byNumber[$item] ?? null; ?>
                    <?php if ($link !== null): ?>
                        <a
                            href="<?= $link['uri'] ?>"
                            class="pager-btn<?= $link['active'] ? ' pager-btn-active' : '' ?>"
                            <?= $link['active'] ? 'aria-current="page"' : '' ?>
                            aria-label="Stránka <?= (int) $item ?>"
                        >
                            <?= (int) $item ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
