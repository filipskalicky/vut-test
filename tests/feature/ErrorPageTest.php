<?php

use App\Models\NewsModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ErrorPageTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    protected $refresh   = true;

    public function testUnknownRouteShowsBranded404(): void
    {
        $result = $this->get('neexistujici-stranka');

        $result->assertStatus(404);
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('<title>Stránka nenalezena | Aktuality</title>', $body);
        $this->assertStringContainsString('Stránka nenalezena', $body);
        $this->assertStringNotContainsString('Tuto stránku se nepodařilo najít.', $body);
        $this->assertStringContainsString('Zpět na přehled', $body);
        $this->assertStringContainsString('content="noindex, nofollow"', $body);
        $this->assertStringContainsString('Aktuality', $body);
        $this->assertStringNotContainsString("Can't find a route", $body);
    }

    public function testHiddenNewsShowsBranded404WithoutBody(): void
    {
        $model = model(NewsModel::class);
        $model->insert([
            'title'        => 'Ještě ne',
            'content'      => json_encode([
                'time'    => 1,
                'blocks'  => [
                    ['type' => 'paragraph', 'data' => ['text' => 'Tajný obsah.']],
                ],
                'version' => '2.30.7',
            ], JSON_THROW_ON_ERROR),
            'visible_from' => '2099-01-01',
            'visible_to'   => null,
        ]);

        $result = $this->get('news/' . $model->getInsertID());

        $result->assertStatus(404);
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('Stránka nenalezena', $body);
        $this->assertStringContainsString('page-kicker', $body);
        $this->assertStringNotContainsString('Tajný obsah.', $body);
    }

    public function testErrorPageViewCoversClientAndServerErrors(): void
    {
        helper('error_page');

        foreach ([400, 403, 500] as $code) {
            $html = view('errors/page', error_page_data($code));

            $this->assertStringContainsString('>' . $code . '<', $html);
            $this->assertStringContainsString('Zpět na přehled', $html);
            $this->assertStringContainsString('content="noindex, nofollow"', $html);
        }

        $this->assertStringContainsString('Špatný požadavek', view('errors/page', error_page_data(400)));
        $this->assertStringContainsString('Přístup odepřen', view('errors/page', error_page_data(403)));
        $this->assertStringContainsString('Něco se pokazilo', view('errors/page', error_page_data(500)));
    }
}
