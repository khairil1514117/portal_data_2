<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Map::index');
$routes->get('map', 'Map::index');
$routes->get('get-kabupaten', 'Map::getKabupaten');
$routes->get('map/total-sumut', 'Map::totalPtsSumut');
$routes->get('map/wilayah/(:any)', 'Map::wilayah/$1');
