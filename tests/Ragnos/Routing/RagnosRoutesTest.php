<?php

namespace Tests\Ragnos\Routing;

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Modules;
use Config\Routing;
use Ragnos\Routing\RagnosRoutes;

/**
 * Pruebas de las rutas explícitas (Ragnos sin Auto Routing).
 */
final class RagnosRoutesTest extends CIUnitTestCase
{
    private RouteCollection $collection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collection = new RouteCollection(service('locator'), new Modules(), new Routing());
        RagnosRoutes::discover($this->collection);
    }

    private function handler(string $uri, string $verb = 'GET'): ?string
    {
        $all = $this->collection->getRoutes(strtoupper($verb));

        return isset($all[$uri]) && is_string($all[$uri]) ? $all[$uri] : null;
    }

    public function testRegistraLasAccionesDeUnDataset(): void
    {
        $this->assertSame('\App\Controllers\Tienda\Productos::index', $this->handler('tienda/productos'));
        $this->assertSame('\App\Controllers\Tienda\Productos::getAjaxGridData', $this->handler('tienda/productos/getAjaxGridData', 'POST'));
        $this->assertSame('\App\Controllers\Tienda\Productos::formProcess', $this->handler('Tienda/Productos/formProcess', 'POST'));
    }

    public function testRegistraMetodosPropiosDelControlador(): void
    {
        $this->assertSame('\App\Controllers\Tienda\Ordenes::calculatotal', $this->handler('tienda/ordenes/calculatotal'));
        $this->assertSame('\App\Controllers\Admin::login', $this->handler('admin/login', 'POST'));
        $this->assertSame('\App\Controllers\Tienda\Reportes::ventaspormes', $this->handler('tienda/reportes/ventaspormes'));
    }

    public function testRegistraLasAccionesDeUnProceso(): void
    {
        $this->assertSame('\App\Controllers\Proceso::showProgress', $this->handler('proceso/showprogress'));
        $this->assertSame('\App\Controllers\Proceso::start', $this->handler('proceso/start'));
    }

    public function testNoExponeMetodosHeredadosQueNoSonAcciones(): void
    {
        $this->assertNull($this->handler('tienda/productos/setSortingField'));
        $this->assertNull($this->handler('tienda/productos/initController'));
        $this->assertNull($this->handler('usuarios/_beforeInsert'));
    }
}
