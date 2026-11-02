<?php

namespace Tests\App\Libraries;

use App\Libraries\DemoData;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas para la detección de la demo Classicmodels.
 */
final class DemoDataTest extends CIUnitTestCase
{
    private array $entries = [
        ['id' => 'clientes', 'class' => '\App\Controllers\Tienda\Clientes', 'path' => '/tienda/clientes'],
        ['id' => 'usuarios', 'class' => 'App\Controllers\Usuarios', 'path' => '/usuarios'],
    ];

    protected function tearDown(): void
    {
        DemoData::override(null);
        parent::tearDown();
    }

    public function testSinTablasDeLaDemoNoSeConsideraInstalada(): void
    {
        DemoData::override(null);
        // La base de pruebas no contiene las tablas de demo_classicmodels.sql.
        $this->assertFalse(DemoData::isInstalled());
    }

    public function testSinDemoSeOmitenLosControladoresDeLaTiendaEnOpenApi(): void
    {
        DemoData::override(false);
        $this->assertSame(['usuarios'], array_column(DemoData::filterOpenApiEntries($this->entries), 'id'));
    }

    public function testConDemoSeConservanTodosLosControladores(): void
    {
        DemoData::override(true);
        $this->assertCount(2, DemoData::filterOpenApiEntries($this->entries));
    }
}
