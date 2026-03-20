<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Dashboard::index');

$routes->get('dashboard', 'Dashboard::index');

$routes->get('competitions', 'Competitions::index');
$routes->get('competitions/select/(:num)', 'Competitions::select/$1');

$routes->get('import', 'Import::index');
$routes->post('import/run', 'Import::run');

$routes->get('jugement', 'Jugement::index');

$routes->get('juges', 'Juges::index');

$routes->get('photos', 'Photos::index');

$routes->get('export', 'Export::index');

$routes->get('competitions', 'Competitions::index');

$routes->get(
    'competitions/select/(:num)',
    'Competitions::select/$1'
);

$routes->get(
    'competitions/(:num)',
    'Competitions::show/$1'
);

$routes->get(
    'competitions/(:num)/photos',
    'Competitions::photos/$1'
);

$routes->get(
    'photos',
    'Competitions::photos'
);

$routes->get(
    'notation',
    'Competitions::notation'
);

$routes->get('import', 'Import::index');

$routes->get(
    'import/run/(:any)',
    'Import::run/$1'
);

$routes->setAutoRoute(true);
