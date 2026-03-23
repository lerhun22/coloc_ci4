<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Dashboard::index');

$routes->get('dashboard', 'Dashboard::index');


/*
|--------------------------------------------------------------------------
COMPETITIONS
|--------------------------------------------------------------------------
*/

$routes->get('competitions', 'Competitions::index');


$routes->get(
    'competitions/photos',
    'Competitions::photos'

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
    'notation',
    'Competitions::notation'
);


/*
|--------------------------------------------------------------------------
JUGEMENT
|--------------------------------------------------------------------------
*/

$routes->get('jugement', 'Jugement::index');

$routes->get('juges', 'Juges::index');

$routes->get('export', 'Export::index');


/*
|--------------------------------------------------------------------------
IMPORT
|--------------------------------------------------------------------------
*/

// ZIP LOCAL

$routes->get('import', 'Import::index');

$routes->get(
    'import/run/(:any)',
    'Import::run/$1'
);


// COPAINS

$routes->get(
    'import/copain',
    'ImportFromCopain::index'
);

$routes->post(
    'import/copain/run',
    'ImportFromCopain::run'
);

$routes->post(
    'competitions/import/run',
    'ImportFromCopain::run'
);

$routes->get(
    'competitions/import',
    'ImportFromCopain::index'
);

$routes->post(
    'competitions/import/run',
    'ImportFromCopain::run'
);


$routes->get('test/run', function () {
    echo "TEST OK";
});

/*
|--------------------------------------------------------------------------
SUIVI
|--------------------------------------------------------------------------
*/

$routes->get('suivi', 'Suivi::index');
$routes->get('suivi/create', 'Suivi::create');
$routes->get('suivi/edit/(:num)', 'Suivi::edit/$1');
$routes->post('suivi/save', 'Suivi::save');


/*
|--------------------------------------------------------------------------
AUTOROUTE
|--------------------------------------------------------------------------
*/

$routes->setAutoRoute(false);
