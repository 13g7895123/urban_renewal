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

        // Property Owners nested routes
        $routes->get('(:num)/property-owners', 'PropertyOwnerController::getByUrbanRenewal/$1'); // GET /api/urban-renewals/{id}/property-owners

        // Handle OPTIONS for specific routes
        $routes->options('/', 'UrbanRenewalController::options');
        $routes->options('(:num)', 'UrbanRenewalController::options');
        $routes->options('(:num)/land-plots', 'LandPlotController::options');
        $routes->options('(:num)/property-owners', 'PropertyOwnerController::options');
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

    // Property Owners API (individual operations)
    $routes->group('property-owners', function ($routes) {
        $routes->get('/', 'PropertyOwnerController::index');                     // GET /api/property-owners
        $routes->get('(:num)', 'PropertyOwnerController::show/$1');              // GET /api/property-owners/{id}
        $routes->post('/', 'PropertyOwnerController::create');                   // POST /api/property-owners
        $routes->put('(:num)', 'PropertyOwnerController::update/$1');            // PUT /api/property-owners/{id}
        $routes->delete('(:num)', 'PropertyOwnerController::delete/$1');         // DELETE /api/property-owners/{id}

        // Handle OPTIONS for specific routes
        $routes->options('/', 'PropertyOwnerController::options');
        $routes->options('(:num)', 'PropertyOwnerController::options');
    });

    // Location API for cascading dropdowns
    $routes->group('locations', function ($routes) {
        $routes->get('counties', 'LocationController::counties');                          // GET /api/locations/counties
        $routes->get('districts/(:segment)', 'LocationController::districts/$1');          // GET /api/locations/districts/{countyCode}
        $routes->get('sections/(:segment)/(:segment)', 'LocationController::sections/$1/$2'); // GET /api/locations/sections/{countyCode}/{districtCode}
        $routes->get('hierarchy', 'LocationController::hierarchy');                        // GET /api/locations/hierarchy

        // Handle OPTIONS for specific routes
        $routes->options('(:any)', 'LocationController::options');
    });
});
