<?php
/**
 * Circle-X clear control at the end of a text input.
 *
 * @var bool $hidden
 */
$hidden = $hidden === true;
?>
<button
    type="button"
    class="input-clear absolute inset-y-0 right-0 flex w-10 items-center justify-center text-gray-400 hover:text-gray-600"
    aria-label="Vymazat"
    <?= $hidden ? 'hidden' : '' ?>
>
    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
        <path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13zM5.47 5.47a.75.75 0 011.06 0L8 6.94l1.47-1.47a.75.75 0 111.06 1.06L9.06 8l1.47 1.47a.75.75 0 11-1.06 1.06L8 9.06l-1.47 1.47a.75.75 0 11-1.06-1.06L6.94 8 5.47 6.53a.75.75 0 010-1.06z" />
    </svg>
</button>
