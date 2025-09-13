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
    $routes->options('(:any)/(:any)', 'LandPlotController::options');

    // Urban Renewals API
    $routes->group('urban-renewals', function ($routes) {
        $routes->get('/', 'UrbanRenewalController::index');           // GET /api/urban-renewals
        $routes->get('(:num)', 'UrbanRenewalController::show/$1');    // GET /api/urban-renewals/{id}
        $routes->post('/', 'UrbanRenewalController::create');         // POST /api/urban-renewals
        $routes->put('(:num)', 'UrbanRenewalController::update/$1');  // PUT /api/urban-renewals/{id}
        $routes->delete('(:num)', 'UrbanRenewalController::delete/$1'); // DELETE /api/urban-renewals/{id}

        // Land Plots nested routes
        $routes->get('(:num)/land-plots', 'LandPlotController::index/$1');        // GET /api/urban-renewals/{id}/land-plots
        $routes->post('(:num)/land-plots', 'LandPlotController::create/$1');      // POST /api/urban-renewals/{id}/land-plots

        // Handle OPTIONS for specific routes
        $routes->options('/', 'UrbanRenewalController::options');
        $routes->options('(:num)', 'UrbanRenewalController::options');
        $routes->options('(:num)/land-plots', 'LandPlotController::options');
    });

    // Land Plots API (individual operations)
    $routes->group('land-plots', function ($routes) {
        $routes->get('(:num)', 'LandPlotController::show/$1');                    // GET /api/land-plots/{id}
        $routes->put('(:num)', 'LandPlotController::update/$1');                  // PUT /api/land-plots/{id}
        $routes->delete('(:num)', 'LandPlotController::delete/$1');               // DELETE /api/land-plots/{id}
        $routes->put('(:num)/representative', 'LandPlotController::setRepresentative/$1'); // PUT /api/land-plots/{id}/representative

        // Handle OPTIONS for specific routes
        $routes->options('(:num)', 'LandPlotController::options');
        $routes->options('(:num)/representative', 'LandPlotController::options');
    });
});
