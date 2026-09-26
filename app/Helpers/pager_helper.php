<?php

/**
 * Compact pager: first … current±1 … last.
 * Pages 1–3 show 1–5; the last 3 pages show the last 5.
 * A single skipped page is shown as a number instead of dots.
 *
 * @return list<int|string>
 */
function pager_compact_sequence(int $current, int $total): array
{
    if ($total < 1) {
        return [];
    }

    $current = max(1, min($current, $total));

    if ($current <= 3) {
        $from = 1;
        $to   = min($total, 5);
    } elseif ($current >= $total - 2) {
        $from = max(1, $total - 4);
        $to   = $total;
    } else {
        $from = max(1, $current - 1);
        $to   = min($total, $current + 1);
    }

    $items = [1];

    if ($from > 2) {
        $items[] = $from === 3 ? 2 : '...';
    }

    for ($page = $from; $page <= $to; $page++) {
        if ($page !== 1 && $page !== $total) {
            $items[] = $page;
        }
    }

    if ($to < $total - 1) {
        $items[] = $to === $total - 2 ? $total - 1 : '...';
    }

    if ($total > 1) {
        $items[] = $total;
    }

    return $items;
}
