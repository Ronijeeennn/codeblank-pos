<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Auto-routing is off, so every reachable URL is listed here.
 * Everything except /login sits behind the "auth" filter.
 */

// Signed-out visitors only
$routes->group('', ['filter' => 'guest'], static function (RouteCollection $routes) {
    $routes->get('login', 'Auth::index');
    $routes->post('login', 'Auth::login');
});

// Staff only
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('logout', 'Auth::logout');

    // Same seven routes for each managed resource: list, new, create, edit, update, delete.
    foreach (['products' => 'Products', 'customers' => 'Customers', 'staff' => 'Staff'] as $path => $controller) {
        $routes->get($path, "$controller::index");
        $routes->get("$path/new", "$controller::new");
        $routes->post($path, "$controller::create");
        $routes->get("$path/(:num)/edit", "$controller::edit/$1");
        $routes->post("$path/(:num)/update", "$controller::update/$1");
        $routes->post("$path/(:num)/delete", "$controller::delete/$1");
    }

    $routes->get('sales', 'Sales::index');       // Sales History
    $routes->get('sales/new', 'Sales::new');     // Record Sale
    $routes->post('sales', 'Sales::create');
});
