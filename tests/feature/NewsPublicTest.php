<?php

use App\Models\NewsModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class NewsPublicTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $refresh   = true;

    public function testHomepageListsTitleAndDateWithoutBody(): void
    {
        $id = $this->insertNews('Jen nadpis', '2024-01-01', 'Tělo článku se na přehledu nesmí objevit.');

        $result = $this->get('/');

        $result->assertOK();
        $result->assertSee('Jen nadpis');
        $result->assertSee(site_url('news/' . $id));
        $result->assertDontSee('Tělo článku se na přehledu nesmí objevit.');
    }

    public function testDetailShowsBodyForVisibleNews(): void
    {
        $id = $this->insertNews('Detailní zpráva', '2024-01-01', 'Celý obsah aktuality.');

        $result = $this->get('news/' . $id);

        $result->assertOK();
        $result->assertSee('Detailní zpráva');
        $result->assertSee('Celý obsah aktuality.');
        $result->assertDontSee(site_url('admin/news/edit/' . $id));
    }

    public function testDetailShowsEditButtonWhenLoggedIn(): void
    {
        $id = $this->insertNews('Upravitelná zpráva', '2024-01-01', 'Obsah pro úpravu.');

        $result = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('news/' . $id);

        $result->assertOK();
        $result->assertSee(site_url('admin/news/edit/' . $id));
        $result->assertSee('Upravit');
    }

    public function testDetailReturns404ForHiddenNews(): void
    {
        $id = $this->insertNews('Ještě ne', '2099-01-01', 'Tajný obsah.');

        $result = $this->get('news/' . $id);

        $result->assertStatus(404);
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('Stránka nenalezena', $body);
        $this->assertStringNotContainsString('Tajný obsah.', $body);
    }

    public function testHomepageSearchFiltersByTitleAndDate(): void
    {
        $this->insertNews('Konference studentů', '2024-09-15', 'Tělo konference');
        $this->insertNews('Nová laboratoř', '2025-01-20', 'Tělo laboratoře');
        $this->insertNews('Budoucí konference', '2099-09-15', 'Ještě ne');

        $title = $this->get('/?q=' . urlencode('laboratoř'));
        $title->assertOK();
        $title->assertSee('placeholder="Hledat"');
        $title->assertDontSee('>Hledat</button>');
        $title->assertDontSee('aria-label="Řazení"');
        $title->assertDontSee('aria-label="Filtrování"');
        $title->assertDontSee('aria-label="Na stránku"');
        $title->assertSee('Nová laboratoř');
        $title->assertDontSee('Konference studentů');
        $title->assertDontSee('Budoucí konference');

        $date = $this->get('/?q=' . urlencode('15. 9. 2024'));
        $date->assertOK();
        $date->assertSee('Konference studentů');
        $date->assertDontSee('Nová laboratoř');

        $empty = $this->get('/?q=' . urlencode('neexistuje'));
        $empty->assertOK();
        $empty->assertSee('Žádné aktuality neodpovídají hledání.');
    }

    public function testHomepageUsesFixedPageSizeAndIgnoresPerPageQuery(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->insertNews(sprintf('Položka %02d', $i), sprintf('2024-01-%02d', $i), 'Tělo ' . $i);
        }

        $first = $this->get('/');
        $first->assertOK();
        $first->assertDontSee('Na stránku');
        $first->assertSee('Stránkování aktualit');
        $first->assertSee('Položka 10');
        $first->assertDontSee('Položka 01');

        $forced = $this->get('/?per_page=10');
        $forced->assertOK();
        $forced->assertDontSee('Na stránku');
        $forced->assertSee('Stránkování aktualit');
        $forced->assertDontSee('Položka 01');

        $html = (string) $first->response()->getBody();
        $this->assertSame(['1', '2'], $this->pagerLabels($html));
        $this->assertStringNotContainsString('aria-label="Další stránka"', $html);
        $this->assertTrue(
            (bool) preg_match('#href="([^"]+)"[^>]*aria-label="Stránka 2"#s', $html, $page2),
        );
        $this->assertStringContainsString('page=2', html_entity_decode($page2[1]));
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

    private function insertNews(string $title, string $from, string $body): int
    {
        $model = model(NewsModel::class);
        $model->insert([
            'title'        => $title,
            'content'      => json_encode([
                'time'    => 1,
                'blocks'  => [
                    ['type' => 'paragraph', 'data' => ['text' => $body]],
                ],
                'version' => '2.30.7',
            ], JSON_THROW_ON_ERROR),
            'visible_from' => $from,
            'visible_to'   => null,
        ]);

        return (int) $model->getInsertID();
    }
}
