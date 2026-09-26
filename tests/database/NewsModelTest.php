<?php

use App\Models\NewsModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class NewsModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $refresh   = true;

    private NewsModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new NewsModel();
    }

    public function testPublicFilterHidesFutureAndExpired(): void
    {
        $now    = $this->model->now();
        $future = '2099-01-01 08:00:00';
        $past   = '2020-01-01 08:00:00';

        $this->insertNews('Viditelná bez konce', $now, null);
        $this->insertNews('Viditelná s koncem', $now, '2099-12-31 23:59:00');
        $this->insertNews('Budoucí', $future, null);
        $this->insertNews('Vypršená', $past, '2020-12-31 23:59:00');

        $public = $this->model->getPublicPaginated(10);

        $titles = array_column($public, 'title');
        $this->assertContains('Viditelná bez konce', $titles);
        $this->assertContains('Viditelná s koncem', $titles);
        $this->assertNotContains('Budoucí', $titles);
        $this->assertNotContains('Vypršená', $titles);
    }

    public function testBoundaryDatesAreVisibleToday(): void
    {
        $now     = new DateTimeImmutable($this->model->now());
        $starts  = $now->format('Y-m-d H:i:s');
        $ends    = $now->modify('+1 hour')->format('Y-m-d H:i:s');
        $ended   = $now->modify('-2 hours')->format('Y-m-d H:i:s');

        $this->insertNews('Začíná teď', $starts, null);
        $this->insertNews('Končí za hodinu', '2020-01-01 00:00:00', $ends);
        $this->insertNews('Už skončila', '2020-01-01 00:00:00', $ended);

        $titles = array_column($this->model->getPublicPaginated(10), 'title');

        $this->assertContains('Začíná teď', $titles);
        $this->assertContains('Končí za hodinu', $titles);
        $this->assertNotContains('Už skončila', $titles);
    }

    public function testPublicListIsSortedNewestFirst(): void
    {
        $this->insertNews('Starší', '2024-01-01 08:00:00', null);
        $this->insertNews('Novější', '2025-06-01 08:00:00', null);

        $titles = array_column($this->model->getPublicPaginated(10), 'title');

        $this->assertSame(['Novější', 'Starší'], $titles);
    }

    public function testPaginationUsesFiveItemsPerPage(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->insertNews('Položka ' . $i, '2024-01-0' . $i . ' 08:00:00', null);
        }

        $page1 = $this->model->getPublicPaginated(5, 1);
        $page2 = $this->model->getPublicPaginated(5, 2);

        $this->assertCount(5, $page1);
        $this->assertCount(1, $page2);
        $this->assertSame(2, $this->model->pager->getPageCount());
    }

    public function testFindPublicByIdRespectsVisibilityWindow(): void
    {
        $visibleId = $this->insertNews('Veřejná', '2024-01-01 08:00:00', null);
        $hiddenId  = $this->insertNews('Skrytá', '2099-01-01 08:00:00', null);

        $this->assertSame('Veřejná', $this->model->findPublicById($visibleId)['title']);
        $this->assertNull($this->model->findPublicById($hiddenId));
        $this->assertNull($this->model->findPublicById(99999));
    }

    public function testAdminListContainsHiddenItems(): void
    {
        $this->insertNews('Veřejná', '2024-01-01 08:00:00', null);
        $this->insertNews('Skrytá', '2099-01-01 08:00:00', null);

        $titles = array_column($this->model->getAllSorted(), 'title');

        $this->assertContains('Veřejná', $titles);
        $this->assertContains('Skrytá', $titles);
    }

    public function testAdminPaginationUsesRequestedPageSize(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->insertNews('Admin ' . $i, '2024-01-0' . $i . ' 08:00:00', null);
        }

        $page1 = $this->model->getAllPaginated(5, 1);
        $page2 = $this->model->getAllPaginated(5, 2);

        $this->assertCount(5, $page1);
        $this->assertCount(1, $page2);
        $this->assertSame(2, $this->model->pager->getPageCount());
    }

    public function testResolvePerPageAcceptsOnlyAllowedSizes(): void
    {
        $this->assertSame(10, NewsModel::resolvePerPage(null));
        $this->assertSame(10, NewsModel::resolvePerPage('3'));
        $this->assertSame(10, NewsModel::resolvePerPage('999'));
        $this->assertSame(10, NewsModel::resolvePerPage('1'));
        $this->assertSame(5, NewsModel::resolvePerPage('5'));
        $this->assertSame(10, NewsModel::resolvePerPage('10'));
        $this->assertSame(20, NewsModel::resolvePerPage(20));
        $this->assertSame(50, NewsModel::resolvePerPage('50'));
    }

    public function testSearchDateTermsConvertCzechDates(): void
    {
        $this->assertSame(['2026'], NewsModel::searchDateTerms('2026'));
        $this->assertSame(['15. 9. 2024', '2024-09-15'], NewsModel::searchDateTerms('15. 9. 2024'));
        $this->assertSame(['15.09.2024', '2024-09-15'], NewsModel::searchDateTerms('15.09.2024'));
        $this->assertSame(['15. 9. 2024 08:00', '2024-09-15 08:00'], NewsModel::searchDateTerms('15. 9. 2024 08:00'));
        $this->assertSame(['15. 9', '-09-15'], NewsModel::searchDateTerms('15. 9'));
        $this->assertSame(['9. 2026', '2026-09'], NewsModel::searchDateTerms('9. 2026'));
        $this->assertSame([], NewsModel::searchDateTerms('   '));
    }

    public function testPublicSearchMatchesTitleAndDateButKeepsVisibility(): void
    {
        $this->insertNews('Konference studentů', '2024-09-15 08:00:00', null);
        $this->insertNews('Nová laboratoř', '2025-01-20 10:00:00', null);
        $this->insertNews('Konference budoucí', '2099-09-15 08:00:00', null);

        $byTitle = array_column($this->model->getPublicPaginated(10, 1, 'laboratoř'), 'title');
        $this->assertSame(['Nová laboratoř'], $byTitle);

        $byDate = array_column($this->model->getPublicPaginated(10, 1, '15. 9. 2024'), 'title');
        $this->assertSame(['Konference studentů'], $byDate);

        $byYear = array_column($this->model->getPublicPaginated(10, 1, '2024'), 'title');
        $this->assertSame(['Konference studentů'], $byYear);

        $hidden = array_column($this->model->getPublicPaginated(10, 1, 'Konference'), 'title');
        $this->assertSame(['Konference studentů'], $hidden);
    }

    public function testAdminSearchMatchesTitleAndBothDates(): void
    {
        $this->insertNews('Veřejná laboratoř', '2024-01-01 08:00:00', null);
        $this->insertNews('Skrytá konference', '2099-03-10 08:00:00', '2099-06-01 23:59:00');

        $byTitle = array_column($this->model->getAllPaginated(10, 1, 'konference'), 'title');
        $this->assertSame(['Skrytá konference'], $byTitle);

        $byFrom = array_column($this->model->getAllPaginated(10, 1, '10. 3. 2099'), 'title');
        $this->assertSame(['Skrytá konference'], $byFrom);

        $byTo = array_column($this->model->getAllPaginated(10, 1, '1. 6. 2099'), 'title');
        $this->assertSame(['Skrytá konference'], $byTo);
    }

    public function testResolveSortDirAndStatuses(): void
    {
        $this->assertSame('published', NewsModel::resolveSort(null));
        $this->assertSame('published', NewsModel::resolveSort('unknown'));
        $this->assertSame('title', NewsModel::resolveSort('title'));
        $this->assertSame('created', NewsModel::resolveSort('created'));
        $this->assertSame('updated', NewsModel::resolveSort('updated'));
        $this->assertSame('desc', NewsModel::resolveDir(null));
        $this->assertSame('asc', NewsModel::resolveDir('asc'));
        $this->assertSame(['scheduled', 'expired'], NewsModel::resolveStatuses('scheduled,expired'));
        $this->assertSame(['visible'], NewsModel::resolveStatuses(['visible', 'nope']));
        $this->assertSame([], NewsModel::resolveStatuses(''));
        $this->assertSame(
            ['sort' => 'title', 'dir' => 'asc', 'status' => 'visible'],
            NewsModel::listingQueryParams('', 'title', 'asc', ['visible']),
        );
    }

    public function testAdminListSortsByTitleAndPublicationDate(): void
    {
        $this->insertNews('Beta', '2025-01-01 08:00:00', null);
        $this->insertNews('Alfa', '2024-01-01 08:00:00', null);

        $byDateDesc = array_column($this->model->getAllPaginated(10, 1, '', 'published', 'desc'), 'title');
        $this->assertSame(['Beta', 'Alfa'], $byDateDesc);

        $byDateAsc = array_column($this->model->getAllPaginated(10, 1, '', 'published', 'asc'), 'title');
        $this->assertSame(['Alfa', 'Beta'], $byDateAsc);

        $byTitleAsc = array_column($this->model->getAllPaginated(10, 1, '', 'title', 'asc'), 'title');
        $this->assertSame(['Alfa', 'Beta'], $byTitleAsc);

        $byTitleDesc = array_column($this->model->getAllPaginated(10, 1, '', 'title', 'desc'), 'title');
        $this->assertSame(['Beta', 'Alfa'], $byTitleDesc);
    }

    public function testAdminListSortsByCreatedAndUpdatedAt(): void
    {
        $olderId = $this->insertNews('Starší záznam', '2025-01-01 08:00:00', null);
        $newerId = $this->insertNews('Novější záznam', '2024-01-01 08:00:00', null);

        $this->db->table('news')->where('id', $olderId)->update([
            'created_at' => '2024-01-01 08:00:00',
            'updated_at' => '2025-06-01 08:00:00',
        ]);
        $this->db->table('news')->where('id', $newerId)->update([
            'created_at' => '2025-01-01 08:00:00',
            'updated_at' => '2024-02-01 08:00:00',
        ]);

        $byCreatedDesc = array_column($this->model->getAllPaginated(10, 1, '', 'created', 'desc'), 'title');
        $this->assertSame(['Novější záznam', 'Starší záznam'], $byCreatedDesc);

        $byUpdatedDesc = array_column($this->model->getAllPaginated(10, 1, '', 'updated', 'desc'), 'title');
        $this->assertSame(['Starší záznam', 'Novější záznam'], $byUpdatedDesc);
    }

    public function testAdminListFiltersByVisibilityStatus(): void
    {
        $this->insertNews('Současná', '2024-01-01 08:00:00', null);
        $this->insertNews('Budoucí', '2099-01-01 08:00:00', null);
        $this->insertNews('Uplynulá', '2020-01-01 08:00:00', '2020-12-31 23:59:00');

        $scheduled = array_column($this->model->getAllPaginated(10, 1, '', 'published', 'desc', ['scheduled']), 'title');
        $this->assertSame(['Budoucí'], $scheduled);

        $visible = array_column($this->model->getAllPaginated(10, 1, '', 'published', 'desc', ['visible']), 'title');
        $this->assertSame(['Současná'], $visible);

        $expired = array_column($this->model->getAllPaginated(10, 1, '', 'published', 'desc', ['expired']), 'title');
        $this->assertSame(['Uplynulá'], $expired);

        $mixed = array_column(
            $this->model->getAllPaginated(10, 1, '', 'title', 'asc', ['scheduled', 'expired']),
            'title',
        );
        $this->assertSame(['Budoucí', 'Uplynulá'], $mixed);
    }

    public function testSitemapEntriesIncludeOnlyCurrentlyVisibleNews(): void
    {
        $visible = $this->insertNews('Ve sitemapu', '2024-01-01 08:00:00', null);
        $this->insertNews('Budoucí mimo sitemap', '2099-01-01 08:00:00', null);
        $this->insertNews('Vypršená mimo sitemap', '2020-01-01 08:00:00', '2020-12-31 23:59:00');

        $ids = array_map('intval', array_column($this->model->getPublicSitemapEntries(), 'id'));

        $this->assertSame([$visible], $ids);
    }

    private function insertNews(string $title, string $from, ?string $to): int
    {
        $this->model->insert([
            'title'        => $title,
            'content'      => json_encode([
                'time'    => 1,
                'blocks'  => [
                    ['type' => 'paragraph', 'data' => ['text' => $title]],
                ],
                'version' => '2.30.7',
            ], JSON_THROW_ON_ERROR),
            'visible_from' => $from,
            'visible_to'   => $to,
        ]);

        return (int) $this->model->getInsertID();
    }
}
