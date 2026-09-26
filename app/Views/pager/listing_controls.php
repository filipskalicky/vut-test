<?php
/**
 * Pagination links for news listings.
 *
 * @var \CodeIgniter\Pager\Pager|null $pager
 * @var bool                          $adminTools
 */
$adminTools = $adminTools === true;
?>
<?php if ($pager !== null && $pager->getPageCount() > 1): ?>
    <div class="mt-10 flex justify-center sm:justify-end">
        <?= $pager->only($adminTools ? ['per_page', 'q', 'sort', 'dir', 'status'] : ['q'])->links() ?>
    </div>
<?php endif; ?>
