<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API Routes with CORS handling
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Handle preflight OPTIONS requests for all API routes
    $routes->options('(:any)', 'UrbanRenewalController::options');

    // Urban Renewals API
    $routes->group('urban-renewals', function ($routes) {
        $routes->get('/', 'UrbanRenewalController::index');           // GET /api/urban-renewals
        $routes->get('(:num)', 'UrbanRenewalController::show/$1');    // GET /api/urban-renewals/{id}
        $routes->post('/', 'UrbanRenewalController::create');         // POST /api/urban-renewals
        $routes->put('(:num)', 'UrbanRenewalController::update/$1');  // PUT /api/urban-renewals/{id}
        $routes->delete('(:num)', 'UrbanRenewalController::delete/$1'); // DELETE /api/urban-renewals/{id}

        // Handle OPTIONS for specific routes
        $routes->options('/', 'UrbanRenewalController::options');
        $routes->options('(:num)', 'UrbanRenewalController::options');
    });
});
