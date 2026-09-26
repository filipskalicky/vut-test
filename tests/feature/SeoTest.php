<?php

use App\Models\NewsModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class SeoTest extends CIUnitTestCase
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

    public function testHomepageHasTitleDescriptionCanonicalAndFavicons(): void
    {
        $body = $this->body($this->get('/'));

        $this->assertStringContainsString('<title>Novinky | Aktuality</title>', $body);
        $this->assertStringContainsString('name="description"', $body);
        $this->assertStringContainsString('Aktuální novinky a oznámení.', $body);
        $this->assertStringContainsString('rel="canonical"', $body);
        $this->assertStringContainsString(site_url('/'), $body);
        $this->assertStringContainsString('content="index, follow"', $body);
        $this->assertStringContainsString('favicon.svg', $body);
        $this->assertStringContainsString('apple-touch-icon.png', $body);
        $this->assertStringContainsString('og-image.png', $body);
        $this->assertStringContainsString('"@type":"WebSite"', $body);
        $this->assertStringContainsString('property="og:locale" content="cs_CZ"', $body);
    }

    public function testSearchResultsAreNoindex(): void
    {
        $body = $this->body($this->get('/?q=' . urlencode('laboratoř')));

        $this->assertStringContainsString('<title>Hledání: laboratoř | Aktuality</title>', $body);
        $this->assertStringContainsString('content="noindex, follow"', $body);
        $this->assertStringContainsString('q=laborato%C5%99', $body);
    }

    public function testSecondPageUpdatesTitleAndCanonical(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            $this->insertNews('Položka ' . $i, '2024-01-0' . $i, 'Tělo ' . $i);
        }

        $body = $this->body($this->get('/?page=2'));

        $this->assertStringContainsString('<title>Novinky, strana 2 | Aktuality</title>', $body);
        $this->assertStringContainsString('page=2', $body);
    }

    public function testDetailUsesArticleMetaAndExcerpt(): void
    {
        $id   = $this->insertNews('Detailní zpráva', '2024-01-01', 'Celý obsah aktuality pro vyhledávače.');
        $body = $this->body($this->get('news/' . $id));

        $this->assertStringContainsString('<title>Detailní zpráva | Aktuality</title>', $body);
        $this->assertStringContainsString('Celý obsah aktuality pro vyhledávače.', $body);
        $this->assertStringContainsString('property="og:type" content="article"', $body);
        $this->assertStringContainsString('"@type":"NewsArticle"', $body);
        $this->assertStringContainsString(site_url('news/' . $id), $body);
        $this->assertStringContainsString('article:published_time', $body);
    }

    public function testLoginAndAdminAreNoindex(): void
    {
        $login = $this->body($this->get('login'));
        $this->assertStringContainsString('<title>Přihlášení | Aktuality</title>', $login);
        $this->assertStringContainsString('content="noindex, nofollow"', $login);

        $admin = $this->body(
            $this
                ->withSession(['user_id' => 1, 'username' => 'test'])
                ->get('admin/news'),
        );
        $this->assertStringContainsString('<title>Aktuality | Administrace</title>', $admin);
        $this->assertStringContainsString('content="noindex, nofollow"', $admin);
    }

    public function testRobotsTxtDisallowsAdminAndPointsToSitemap(): void
    {
        $result = $this->get('robots.txt');
        $result->assertOK();

        $body = $result->response()->getBody();
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /login', $body);
        $this->assertStringContainsString('Sitemap: ' . site_url('sitemap.xml'), $body);
    }

    public function testSitemapListsHomepageAndVisibleNewsOnly(): void
    {
        $visible = $this->insertNews('Veřejná pro sitemap', '2024-01-01', 'Tělo');
        $hidden  = $this->insertNews('Skrytá pro sitemap', '2099-01-01', 'Tajné');

        $result = $this->get('sitemap.xml');
        $result->assertOK();

        $body = $result->response()->getBody();
        $this->assertStringContainsString(site_url('/'), $body);
        $this->assertStringContainsString(site_url('news/' . $visible), $body);
        $this->assertStringNotContainsString(site_url('news/' . $hidden), $body);
    }

    private function body(object $result): string
    {
        $result->assertOK();

        return (string) $result->response()->getBody();
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
