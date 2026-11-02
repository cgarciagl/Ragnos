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
$routes->post('ragnos/export-xlsx', '\Ragnos\Controllers\RSimpleLevelReport::exportHtmlToXlsx');


/*
 * Rutas explícitas de Ragnos.
 *
 * Con el Auto Routing (Legacy) activo ($autoRoute = true en Config\Routing,
 * valor por defecto) no hace falta nada más. Si lo desactivas, Ragnos
 * registra aquí las rutas de cada controlador de app/Controllers, exponiendo
 * solo sus acciones y los métodos públicos que declara cada uno.
 */
if (! config(\Config\Routing::class)->autoRoute) {
    \Ragnos\Routing\RagnosRoutes::discover($routes);
}
