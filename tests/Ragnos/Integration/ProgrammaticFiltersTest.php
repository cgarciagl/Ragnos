<?php

namespace Tests\Ragnos\Integration;

use Tests\Ragnos\RagnosTestCase;
use Tests\Support\RequestSimulator;
use Ragnos\Models\RDatasetModel;

class ArticuloFiltradoModel extends RDatasetModel
{
    public $table = 'articulos_search';
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
 * Regresión: los filtros que se agregan al Query Builder antes de pedir la grilla
 * (los de _filters(), el filtro maestro-detalle o el de borrado lógico) deben
 * limitar las filas devueltas, no solo el conteo.
 */
class ProgrammaticFiltersTest extends RagnosTestCase
{
    use RequestSimulator;

    private ArticuloFiltradoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadRagnosHelpers();
        $this->resetRequest();

        $this->createTestTable('articulos_search', [
            'nombre'    => ['type' => 'TEXT'],
            'categoria' => ['type' => 'TEXT'],
            'precio'    => ['type' => 'REAL'],
            'stock'     => ['type' => 'INTEGER'],
        ]);
        $this->insertMultiple('articulos_search', [
            ['nombre' => 'Widget Alpha', 'categoria' => 'Tools', 'precio' => 50, 'stock' => 10],
            ['nombre' => 'Widget Beta', 'categoria' => 'Tools', 'precio' => 150, 'stock' => 5],
            ['nombre' => 'Gadget Lambda', 'categoria' => 'Gadgets', 'precio' => 350, 'stock' => 8],
            ['nombre' => 'Gizmo Delta', 'categoria' => 'Gizmos', 'precio' => 100, 'stock' => 12],
        ]);

        $this->model              = new ArticuloFiltradoModel($this->db);
        $this->model->tablefields = ['nombre', 'categoria', 'precio', 'stock'];
        $this->setPost(['start' => 0, 'length' => 50]);
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('articulos_search');
        $this->resetRequest();
        parent::tearDown();
    }

    private function categorias(array $respuesta): array
    {
        return array_values(array_unique(array_column($respuesta['data'], 1)));
    }

    public function testElFiltroLimitaLasFilasDeLaGrilla(): void
    {
        // Lo que hace _filters() en un controlador
        $this->model->builder()->where('categoria', 'Tools');

        $respuesta = json_decode($this->model->getTableAjax(), true);

        $this->assertSame(2, $respuesta['recordsTotal']);
        $this->assertCount(2, $respuesta['data']);
        $this->assertSame(['Tools'], $this->categorias($respuesta));
    }

    public function testElFiltroSeCombinaConLaBusqueda(): void
    {
        $this->model->builder()->where('categoria', 'Tools');
        $this->setPost(['start' => 0, 'length' => 50, 'search' => ['value' => 'Beta']]);

        $respuesta = json_decode($this->model->getTableAjax(), true);

        $this->assertSame(1, $respuesta['recordsTotal']);
        $this->assertCount(1, $respuesta['data']);
        $this->assertSame('Widget Beta', $respuesta['data'][0][0]);
    }

    public function testElFiltroLimitaLasFilasDeUnaConsultaSQL(): void
    {
        // Equivalente a un RQueryController: la consulta base se envuelve en una CTE
        $this->model->table = 'rqcte_prueba';
        $this->model->builder('rqcte_prueba')->where('precio >', 120);

        $sql       = 'SELECT id, nombre, categoria, precio, stock FROM articulos_search';
        $respuesta = json_decode($this->model->getTableAjaxBySQL($sql), true);

        $this->assertSame(2, $respuesta['recordsTotal']);
        $this->assertCount(2, $respuesta['data']);
        $this->assertSame(['Widget Beta', 'Gadget Lambda'], array_column($respuesta['data'], 0));
    }

    public function testElFiltroLimitaLosDatosDeLaAPI(): void
    {
        $this->model->builder()->where('categoria', 'Gizmos');

        $respuesta = $this->model->getTableForAPI();

        $this->assertSame(1, $respuesta['countAll']);
        $this->assertCount(1, $respuesta['data']);
        $this->assertSame('Gizmo Delta', $respuesta['data'][0]['nombre']);
    }
}
