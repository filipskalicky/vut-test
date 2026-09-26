<?php

namespace App\Controllers;

/**
 * Branded HTTP error pages that reuse the public layout.
 */
class ErrorsController extends BaseController
{
    public function notFound(?string $message = null): string
    {
        return view('errors/page', error_page_data(404));
    }
}
