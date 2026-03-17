<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Dashboard::index');

$routes->group('preparation', function ($routes) {
    $routes->get('/', 'Preparation\Preparation::index');
    $routes->get('create', 'Preparation\Preparation::create');
    $routes->post('store', 'Preparation\Preparation::store');
});

$routes->get('competitions', 'Competitions::index');
$routes->get('competitions/(:num)', 'Competitions::show/$1');
$routes->get('competitions/(:num)/photos', 'Competitions::photos/$1');
$routes->get('competitions/(:num)/classement', 'Competitions::classement/$1');
$routes->get('competitions/(:num)/export', 'Competitions::export/$1');
$routes->get('juges', 'Juges\Juges::index');
$routes->get('juges/create', 'Juges\Juges::create');
$routes->post('juges/store', 'Juges\Juges::store');
$routes->post('juges/(:num)/delete', 'Juges\Juges::delete/$1');
$routes->get('competitions/(:num)/jugement', 'Jugement::index/$1');
$routes->get('competitions/(:num)/jugement/photo/(:num)', 'Jugement::photo/$1/$2');

$routes->post(
    'competitions/(:num)/jugement/saveNote',
    'Jugement::saveNote/$1'
);
$routes->get(
    'competitions/(:num)/jugement/photos',
    'Jugement::photos/$1'
);
$routes->get(
    'competitions/(:num)/jugement/disqualify/(:num)',
    'Jugement::disqualify/$1/$2'
);

$routes->get('tools/generer-vignettes/(:num)', 'Tools\GenererVignettes::index/$1');

$routes->post('import/getConcours', 'ImportController::getConcours');
$routes->post('import/import', 'ImportController::import');
$routes->post('import/generateZip', 'ImportController::generateZip');


$routes->setAutoRoute(true);
