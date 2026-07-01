<?php

namespace Tests\Ragnos\Models\Traits;

use Tests\Ragnos\RagnosTestCase;
use Tests\Support\RequestSimulator;
use App\ThirdParty\Ragnos\Models\RDatasetModel;

/**
 * Modelo concreto para SearchFilterTrait sobre tabla 'productos'.
 */
class SearchProductoModel extends RDatasetModel
{
    public $table = 'productos';
    public $primaryKey = 'id';
    protected $returnType = 'array';

    public function __construct($db = null)
    {
        parent::__construct();
        if ($db !== null) {
            $this->db = $db;
        }
    }
}

/**
 * Pruebas reales para SearchFilterTrait.
 * Cubre isPostgres, parseStructuredFilters (incluyendo validación de
 * campos/operadores) y getCountForSearch contra datos reales.
 */
class SearchFilterTraitTest extends RagnosTestCase
{
    use RequestSimulator;

    private SearchProductoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadRagnosHelpers();
        $this->resetRequest();

        $this->createTestTable('productos', [
            'nombre'    => ['type' => 'TEXT'],
            'categoria' => ['type' => 'TEXT'],
            'precio'    => ['type' => 'REAL'],
            'stock'     => ['type' => 'INTEGER'],
        ]);

        $this->insertTestData('productos', ['nombre' => 'Widget A', 'categoria' => 'Tools', 'precio' => 50, 'stock' => 10]);
        $this->insertTestData('productos', ['nombre' => 'Widget B', 'categoria' => 'Tools', 'precio' => 150, 'stock' => 5]);
        $this->insertTestData('productos', ['nombre' => 'Gadget X', 'categoria' => 'Gadgets', 'precio' => 250, 'stock' => 0]);
        $this->insertTestData('productos', ['nombre' => 'Gadget Y', 'categoria' => 'Gadgets', 'precio' => 350, 'stock' => 8]);
        $this->insertTestData('productos', ['nombre' => 'Other Z', 'categoria' => 'Other', 'precio' => 100, 'stock' => 3]);

        $this->model              = new SearchProductoModel($this->db);
        $this->model->tablefields = ['nombre', 'categoria', 'precio', 'stock'];
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('productos');
        $this->resetRequest();
        parent::tearDown();
    }

    public function testIsPostgresEnSQLiteRetornaFalse(): void
    {
        $this->assertFalse($this->model->isPostgres());
    }

    public function testGetCountForSearchSinFiltrosCuentaTodo(): void
    {
        $this->assertSame(5, $this->model->getCountForSearch());
    }

    public function testParseStructuredFiltersAplicaFiltroPrecioMayor(): void
    {
        // Codificar filtro estructurado y simular el GET correspondiente
        $filter = base64_encode(json_encode([
            ['field' => 'precio', 'op' => '>', 'value' => 100],
        ]));
        $this->setGet(['sFilter' => $filter]);

        // 3 productos con precio > 100: Widget B (150), Gadget X (250), Gadget Y (350)
        $this->assertSame(3, $this->model->getCountForSearch());
    }

    public function testParseStructuredFiltersConIgualdad(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'categoria', 'op' => '=', 'value' => 'Gadgets'],
        ]));
        $this->setGet(['sFilter' => $filter]);

        $this->assertSame(2, $this->model->getCountForSearch());
    }

    public function testParseStructuredFiltersConMultiplesCondicionesAND(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'categoria', 'op' => '=', 'value' => 'Tools'],
            ['field' => 'precio', 'op' => '>=', 'value' => 100],
        ]));
        $this->setGet(['sFilter' => $filter]);

        // Solo Widget B cumple ambos
        $this->assertSame(1, $this->model->getCountForSearch());
    }

    public function testParseStructuredFiltersLanzaConCampoNoPermitido(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'campo_inyectado', 'op' => '=', 'value' => 'x'],
        ]));
        $this->setGet(['sFilter' => $filter]);

        $this->expectException(\InvalidArgumentException::class);
        $this->model->getCountForSearch();
    }

    public function testParseStructuredFiltersLanzaConOperadorNoPermitido(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'precio', 'op' => 'DROP TABLE', 'value' => 1],
        ]));
        $this->setGet(['sFilter' => $filter]);

        $this->expectException(\InvalidArgumentException::class);
        $this->model->getCountForSearch();
    }

    public function testParseStructuredFiltersLanzaConFiltroSinKeysRequeridas(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'precio'], // falta op y value
        ]));
        $this->setGet(['sFilter' => $filter]);

        $this->expectException(\InvalidArgumentException::class);
        $this->model->getCountForSearch();
    }

    public function testParseStructuredFiltersDirectoViaReflection(): void
    {
        $ref = new \ReflectionMethod($this->model, 'parseStructuredFilters');
        $ref->setAccessible(true);

        // Caso válido: no lanza
        $ref->invoke($this->model, [['field' => 'precio', 'op' => '<', 'value' => 200]]);
        $this->addToAssertionCount(1);

        // Caso inválido: lanza
        $this->expectException(\InvalidArgumentException::class);
        $ref->invoke($this->model, [['field' => 'precio', 'op' => 'XX', 'value' => 1]]);
    }

    public function testGetCountForSearchConBusquedaGlobal(): void
    {
        // Búsqueda global: buscar 'Widget' en cualquier campo
        $this->setGet(['search' => ['value' => 'Widget']]);

        // 2 productos contienen 'Widget' en nombre
        $count = $this->model->getCountForSearch();
        $this->assertSame(2, $count);
    }

    public function testGetCountForSearchConBusquedaSoloUnCampo(): void
    {
        $this->setGet([
            'search'     => ['value' => 'Gadget'],
            'sOnlyField' => 'nombre',
        ]);
        $count = $this->model->getCountForSearch();
        $this->assertSame(2, $count);
    }

    public function testCheckRelationsAgregaSELECTParaCampoConQuery(): void
    {
        $this->model->addFieldFromArray('nombre_upper', [
            'query' => 'UPPER(productos.nombre)',
        ]);
        $this->model->tablefields[] = 'nombre_upper';
        $this->model->completeFieldList();
        $this->model->checkRelations();

        $sql = $this->model->builder()->getCompiledSelect();
        $this->assertStringContainsString('UPPER(productos.nombre)', $sql);
    }

    public function testGetCountForSearchSeRecalculaCadaLlamada(): void
    {
        // Sin filtros: 5
        $this->assertSame(5, $this->model->getCountForSearch());

        // Con filtros: subset
        $filter = base64_encode(json_encode([
            ['field' => 'stock', 'op' => '>', 'value' => 0],
        ]));
        $this->setGet(['sFilter' => $filter]);

        // 4 productos con stock > 0
        $this->assertSame(4, $this->model->getCountForSearch());
    }
}
