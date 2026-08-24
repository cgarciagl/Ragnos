<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Admin::index');
$routes->group('api', static function ($routes): void {
    $routes->get('openapi.json', 'OpenApi::json');
    $routes->get('openapi.yaml', 'OpenApi::yaml');
    $routes->get('docs', 'OpenApi::docs');
});
