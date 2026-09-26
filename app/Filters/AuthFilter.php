<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Blocks unauthenticated access to the admin area.
 */
class AuthFilter implements FilterInterface
{
    /**
     * Redirect guests to the login form.
     *
     * @param list<string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('user_id')) {
            return redirect()->to('/login');
        }

        return null;
    }

    /**
     * @param list<string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
