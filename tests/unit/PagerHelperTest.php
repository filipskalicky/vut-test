<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PagerHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('pager');
    }

    public function testFewPagesHaveNoDots(): void
    {
        $this->assertSame([1, 2, 3], pager_compact_sequence(1, 3));
        $this->assertSame([1, 2, 3], pager_compact_sequence(2, 3));
        $this->assertSame([1, 2], pager_compact_sequence(1, 2));
    }

    public function testDotsOnlyWhenAPageIsSkipped(): void
    {
        $start = [1, 2, 3, 4, 5, '...', 10];
        $this->assertSame($start, pager_compact_sequence(1, 10));
        $this->assertSame($start, pager_compact_sequence(2, 10));
        $this->assertSame($start, pager_compact_sequence(3, 10));
        $this->assertSame($start, pager_compact_sequence(4, 10));
        $this->assertSame([1, '...', 4, 5, 6, '...', 10], pager_compact_sequence(5, 10));
        $this->assertSame([1, '...', 6, 7, 8, 9, 10], pager_compact_sequence(7, 10));

        $end = [1, '...', 6, 7, 8, 9, 10];
        $this->assertSame($end, pager_compact_sequence(8, 10));
        $this->assertSame($end, pager_compact_sequence(9, 10));
        $this->assertSame($end, pager_compact_sequence(10, 10));
    }
}
