<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Login / logout for the admin area.
 *
 * Session login against the users table.
 */
class AuthController extends BaseController
{
    /**
     * Show the login form, or bounce already authenticated users into admin.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/admin/news');
        }

        return view('auth/login', [
            'title' => 'Přihlášení',
            'seo'   => seo_login(),
        ]);
    }

    /**
     * Validate credentials and open a session.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function attemptLogin()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()
                ->to('/login')
                ->withInput()
                ->with('error', 'Vyplňte login i heslo.');
        }

        $user = model(UserModel::class)->findByUsername($username);

        if ($user === null || ! password_verify($password, (string) $user['password_hash'])) {
            return redirect()
                ->to('/login')
                ->withInput()
                ->with('error', 'Neplatný login nebo heslo.');
        }

        session()->regenerate(true);
        session()->set('user_id', (int) $user['id']);
        session()->set('username', $user['username']);

        return redirect()->to('/admin/news');
    }

    /**
     * Destroy the session. Stay on the current public page; leave admin for `/`.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        $target = $this->publicLogoutTarget($this->request->getPost('back'));

        session()->destroy();

        return redirect()->to($target);
    }

    /**
     * Same-origin public path only. Admin, login and foreign hosts fall back to `/`.
     */
    private function publicLogoutTarget(mixed $raw): string
    {
        $raw = trim((string) $raw);

        if ($raw === '' || str_starts_with($raw, '//') || str_contains($raw, '\\')) {
            return '/';
        }

        if (! str_starts_with($raw, '/') && preg_match('#^https?://#i', $raw) !== 1) {
            return '/';
        }

        $parts = parse_url($raw);

        if ($parts === false) {
            return '/';
        }

        if (isset($parts['host'])) {
            $expected = strtolower((string) (parse_url((string) site_url('/'), PHP_URL_HOST) ?? ''));

            if ($expected === '' || strtolower((string) $parts['host']) !== $expected) {
                return '/';
            }
        }

        $path = $parts['path'] ?? '/';

        if ($path === '') {
            $path = '/';
        }

        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/';
        }

        $normalized = strtolower($path);

        if ($normalized === '/login' || $normalized === '/logout' || str_starts_with($normalized, '/admin')) {
            return '/';
        }

        if (! empty($parts['query'])) {
            return $path . '?' . $parts['query'];
        }

        return $path;
    }
}
