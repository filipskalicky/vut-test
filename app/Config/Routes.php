<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Application routes.
 *
 * Public pages are open. Everything under /admin is protected by the auth filter.
 *
 * @var RouteCollection $routes
 */
$routes->get('/', 'NewsController::index');
$routes->get('news/(:num)', 'NewsController::show/$1');
$routes->get('robots.txt', 'SeoController::robots');
$routes->get('sitemap.xml', 'SeoController::sitemap');

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->post('logout', 'AuthController::logout');

$routes->group('admin', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('news', 'Admin\NewsController::index');
    $routes->get('news/new', 'Admin\NewsController::new');
    $routes->post('news', 'Admin\NewsController::create');
    $routes->get('news/edit/(:num)', 'Admin\NewsController::edit/$1');
    $routes->post('news/update/(:num)', 'Admin\NewsController::update/$1');
    $routes->post('news/delete/(:num)', 'Admin\NewsController::delete/$1');
});
