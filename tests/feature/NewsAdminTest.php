<?php

use App\Models\NewsModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class NewsAdminTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $refresh   = true;

    protected function setUp(): void
    {
        parent::setUp();

        model(UserModel::class)->insert([
            'username'      => 'test',
            'password_hash' => password_hash('test', PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function testAdminListPaginatesAndShowsPerPageSelector(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->insertNews('Admin položka ' . $i, '2024-01-0' . $i);
        }

        $default = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news');

        $default->assertOK();
        $default->assertSee('Admin položka 6');
        $default->assertSee('Admin položka 1');
        $default->assertDontSee('Stránkování aktualit');
        $this->assertStringContainsString(
            'data-listing-per-page-value>10</span>',
            (string) $default->response()->getBody(),
        );

        $five = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?per_page=5');

        $five->assertOK();
        $five->assertSee('Na stránku');
        $five->assertSee('Stránkování aktualit');
        $five->assertSee('Admin položka 6');
        $five->assertDontSee('Admin položka 1');

        $ten = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?per_page=10');

        $ten->assertOK();
        $ten->assertSee('Admin položka 6');
        $ten->assertSee('Admin položka 1');
        $ten->assertDontSee('Stránkování aktualit');
    }

    public function testAdminPagerUsesCompactNumbers(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $this->insertNews('Stránka položka ' . $i, sprintf('2024-01-%02d', min($i, 28)));
        }

        $first = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?per_page=5');

        $first->assertOK();
        $firstHtml = (string) $first->response()->getBody();
        $this->assertSame(['1', '2', '3', '4', '5', '...', '10'], $this->pagerLabels($firstHtml));
        $this->assertStringNotContainsString('aria-label="Další stránka"', $firstHtml);

        $middle = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?per_page=5&page=5');

        $this->assertSame(['1', '...', '4', '5', '6', '...', '10'], $this->pagerLabels((string) $middle->response()->getBody()));

        $last = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?per_page=5&page=10');

        $this->assertSame(['1', '...', '6', '7', '8', '9', '10'], $this->pagerLabels((string) $last->response()->getBody()));
    }

    /**
     * @return list<string>
     */
    private function pagerLabels(string $html): array
    {
        if (preg_match('#<nav aria-label="Stránkování aktualit">(.*?)</nav>#s', $html, $nav) !== 1) {
            return [];
        }

        preg_match_all('#class="pager-(?:btn|ellipsis)[^"]*"[^>]*>([^<]+)#', $nav[1], $matches);

        return array_map(static fn (string $label): string => trim($label), $matches[1]);
    }

    public function testAdminListSearchFiltersByTitleAndDate(): void
    {
        $this->insertNews('Veřejná laboratoř', '2024-01-01');
        $this->insertNews('Skrytá konference', '2099-03-10');

        $title = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?q=' . urlencode('konference'));

        $title->assertOK();
        $title->assertSee('placeholder="Hledat"');
        $title->assertDontSee('>Hledat</button>');
        $title->assertSee('Skrytá konference');
        $title->assertDontSee('Veřejná laboratoř');

        $date = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?q=' . urlencode('10. 3. 2099'));

        $date->assertOK();
        $date->assertSee('Skrytá konference');
        $date->assertDontSee('Veřejná laboratoř');
    }

    public function testAdminListSortAndStatusFilter(): void
    {
        $this->insertNews('Beta', '2025-01-01');
        $this->insertNews('Alfa', '2024-01-01');
        $this->insertNews('Budoucí', '2099-01-01');
        $this->insertNews('Uplynulá', '2020-01-01', '2020-12-31');

        $sorted = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?sort=title&dir=asc&per_page=10');

        $sorted->assertOK();
        $sorted->assertSee('aria-label="Řazení"');
        $sorted->assertSee('Datum vytvoření');
        $sorted->assertSee('Datum poslední úpravy');
        $sorted->assertSee('aria-label="Filtrování"');
        $sorted->assertSee('Budoucí');
        $sorted->assertSee('Současné');
        $sorted->assertSee('Uplynulé');
        $html = (string) $sorted->response()->getBody();
        $this->assertLessThan(
            strpos($html, 'Beta') ?: PHP_INT_MAX,
            strpos($html, 'Alfa') ?: PHP_INT_MAX,
        );

        $filtered = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news?status=scheduled');

        $filtered->assertOK();
        $filtered->assertSee('Budoucí');
        $filtered->assertDontSee('Alfa');
        $filtered->assertDontSee('Uplynulá');
    }

    public function testSaveStaysOnEditFormAndSaveAndBackReturnsToList(): void
    {
        $payload = [
            'title'        => 'Uložená aktualita',
            'content'      => json_encode([
                'time'    => 1,
                'blocks'  => [
                    ['type' => 'paragraph', 'data' => ['text' => 'Text aktuality']],
                ],
                'version' => '2.30.7',
            ], JSON_THROW_ON_ERROR),
            'visible_from' => '2026-01-01 10:00',
            'visible_to'   => '',
        ];

        $stay = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->post('admin/news', $payload + ['after_save' => 'stay']);

        $this->assertTrue($stay->isRedirect());
        $stay->assertRedirectTo(site_url('admin/news/edit/1'));

        $back = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->post('admin/news/update/1', $payload + [
                'title'      => 'Upravená aktualita',
                'after_save' => 'back',
            ]);

        $this->assertTrue($back->isRedirect());
        $back->assertRedirectTo(site_url('admin/news'));
    }

    public function testEditFormShowsDeleteAndCreateFormDoesNot(): void
    {
        $id = $this->insertNews('Ke smazání', '2024-01-01');

        $edit = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news/edit/' . $id);

        $edit->assertOK();
        $edit->assertSee(site_url('admin/news/delete/' . $id));
        $edit->assertSee('Smazat');

        $create = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news/new');

        $create->assertOK();
        $create->assertDontSee(site_url('admin/news/delete/'));
    }

    private function insertNews(string $title, string $from, ?string $to = null): int
    {
        $model = model(NewsModel::class);
        $model->insert([
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

        return (int) $model->getInsertID();
    }
}
