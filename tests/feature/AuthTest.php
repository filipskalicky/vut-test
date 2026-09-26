<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AuthTest extends CIUnitTestCase
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

    public function testAdminRequiresLogin(): void
    {
        $result = $this->get('admin/news');

        $this->assertTrue($result->isRedirect());
        $result->assertRedirectTo(site_url('login'));
    }

    public function testLoginSucceedsWithSeedCredentials(): void
    {
        $result = $this->post('login', [
            'username' => 'test',
            'password' => 'test',
        ]);

        $this->assertTrue($result->isRedirect());
        $result->assertRedirectTo(site_url('admin/news'));
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $result = $this->post('login', [
            'username' => 'test',
            'password' => 'spatne',
        ]);

        $this->assertTrue($result->isRedirect());
        $result->assertRedirectTo(site_url('login'));
        $result->assertSessionHas('error');
    }

    public function testAdminIsAccessibleWhenLoggedIn(): void
    {
        $result = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->get('admin/news');

        $result->assertOK();
        $result->assertSee('Aktuality');
    }

    public function testLogoutFromPublicPageStaysThere(): void
    {
        $result = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->post('logout', [
                'back' => site_url('news/3'),
            ]);

        $this->assertTrue($result->isRedirect());
        $result->assertRedirectTo(site_url('news/3'));
    }

    public function testLogoutFromPublicSearchKeepsQuery(): void
    {
        $result = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->post('logout', [
                'back' => site_url('/') . '?q=laboratoř',
            ]);

        $this->assertTrue($result->isRedirect());
        $location = (string) $result->response()->getHeaderLine('Location');
        parse_str((string) (parse_url($location, PHP_URL_QUERY) ?? ''), $query);
        $this->assertSame('/', (string) (parse_url($location, PHP_URL_PATH) ?: '/'));
        $this->assertSame('laboratoř', $query['q'] ?? null);
    }

    public function testLogoutFromAdminGoesToHomepage(): void
    {
        $result = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->post('logout', [
                'back' => site_url('admin/news'),
            ]);

        $this->assertTrue($result->isRedirect());
        $result->assertRedirectTo(site_url('/'));
    }

    public function testLogoutRejectsExternalBackUrl(): void
    {
        $result = $this
            ->withSession(['user_id' => 1, 'username' => 'test'])
            ->post('logout', [
                'back' => 'https://evil.example/phish',
            ]);

        $this->assertTrue($result->isRedirect());
        $result->assertRedirectTo(site_url('/'));
    }
}
