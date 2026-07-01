<?php

namespace Tests\Ragnos\Integration;

use Tests\Ragnos\RagnosTestCase;
use Tests\Support\RequestSimulator;
use App\ThirdParty\Ragnos\Models\RDatasetModel;

class ArticuloSearchModel extends RDatasetModel
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
 * Test de integración: búsqueda + filtros + paginación + ordenamiento
 * en un dataset poblado, ejercitando SearchFilterTrait + JsonResultTrait
 * juntos a través de getTableAjax/getCountForSearch.
 */
class SearchAndFilterTest extends RagnosTestCase
{
    use RequestSimulator;

    private ArticuloSearchModel $model;

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

        // 10 registros con suficiente variedad para distintos filtros
        $rows = [
            ['nombre' => 'Widget Alpha', 'categoria' => 'Tools', 'precio' => 50, 'stock' => 10],
            ['nombre' => 'Widget Beta', 'categoria' => 'Tools', 'precio' => 150, 'stock' => 5],
            ['nombre' => 'Widget Gamma', 'categoria' => 'Tools', 'precio' => 250, 'stock' => 0],
            ['nombre' => 'Gadget Lambda', 'categoria' => 'Gadgets', 'precio' => 350, 'stock' => 8],
            ['nombre' => 'Gadget Sigma', 'categoria' => 'Gadgets', 'precio' => 450, 'stock' => 2],
            ['nombre' => 'Gizmo Delta', 'categoria' => 'Gizmos', 'precio' => 100, 'stock' => 12],
            ['nombre' => 'Gizmo Epsilon', 'categoria' => 'Gizmos', 'precio' => 200, 'stock' => 4],
            ['nombre' => 'Other Mu', 'categoria' => 'Other', 'precio' => 75, 'stock' => 20],
            ['nombre' => 'Other Nu', 'categoria' => 'Other', 'precio' => 125, 'stock' => 15],
            ['nombre' => 'Other Xi', 'categoria' => 'Other', 'precio' => 300, 'stock' => 0],
        ];
        $this->insertMultiple('articulos_search', $rows);

        $this->model              = new ArticuloSearchModel($this->db);
        $this->model->tablefields = ['nombre', 'categoria', 'precio', 'stock'];
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('articulos_search');
        $this->resetRequest();
        parent::tearDown();
    }

    public function testBusquedaSinFiltrosCuentaTodo(): void
    {
        $this->assertSame(10, $this->model->getCountForSearch());
    }

    public function testBusquedaGlobalEnNombreEncuentraCoincidencias(): void
    {
        $this->setGet(['search' => ['value' => 'Widget']]);
        $this->assertSame(3, $this->model->getCountForSearch());
    }

    public function testBusquedaGlobalEnCategoriaEncuentraCoincidencias(): void
    {
        $this->setGet(['search' => ['value' => 'Gizmos']]);
        // Gizmo Delta y Gizmo Epsilon
        $this->assertSame(2, $this->model->getCountForSearch());
    }

    public function testFiltroEstructuradoPrecioRangoSimple(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'precio', 'op' => '>', 'value' => 200],
            ['field' => 'precio', 'op' => '<=', 'value' => 400],
        ]));
        $this->setGet(['sFilter' => $filter]);

        // Widget Gamma(250), Gadget Lambda(350), Other Xi(300) => 3
        $this->assertSame(3, $this->model->getCountForSearch());
    }

    public function testFiltroEstructuradoStockCero(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'stock', 'op' => '=', 'value' => 0],
        ]));
        $this->setGet(['sFilter' => $filter]);

        // Widget Gamma y Other Xi
        $this->assertSame(2, $this->model->getCountForSearch());
    }

    public function testFiltroEstructuradoLanzaConOperadorPeligroso(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'precio', 'op' => 'DELETE FROM x', 'value' => 1],
        ]));
        $this->setGet(['sFilter' => $filter]);

        $this->expectException(\InvalidArgumentException::class);
        $this->model->getCountForSearch();
    }

    public function testFiltroEstructuradoLanzaConCampoNoEnTablefields(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'campo_inyectado', 'op' => '=', 'value' => 'x'],
        ]));
        $this->setGet(['sFilter' => $filter]);

        $this->expectException(\InvalidArgumentException::class);
        $this->model->getCountForSearch();
    }

    public function testGetTableAjaxRespectaLimitYStart(): void
    {
        $this->setGet(['length' => '3', 'start' => '0']);
        $decoded = json_decode($this->model->getTableAjax(), true);

        $this->assertCount(3, $decoded['data']);
        $this->assertSame(10, $decoded['recordsTotal']);
    }

    public function testGetTableAjaxSegundaPaginaDevuelveRestantes(): void
    {
        $this->setGet(['length' => '4', 'start' => '6']);
        $decoded = json_decode($this->model->getTableAjax(), true);

        $this->assertCount(4, $decoded['data']);
    }

    public function testOrdenamientoDescendentePorPrecio(): void
    {
        $this->setGet([
            'order'  => [['column' => '2', 'dir' => 'desc']], // col 2 = 'precio'
            'length' => '10',
        ]);
        $decoded = json_decode($this->model->getTableAjax(), true);

        // El primero en data debe ser el de precio más alto (Gadget Sigma=450)
        $this->assertStringContainsString('Gadget Sigma', $decoded['data'][0][0]);
        // Y el último, el más bajo (Widget Alpha=50)
        $this->assertStringContainsString('Widget Alpha', $decoded['data'][9][0]);
    }

    public function testOrdenamientoAscendentePorNombre(): void
    {
        $this->setGet([
            'order'  => [['column' => '0', 'dir' => 'asc']], // col 0 = 'nombre'
            'length' => '10',
        ]);
        $decoded = json_decode($this->model->getTableAjax(), true);

        // Primer registro: Gadget Lambda (orden alfabético)
        $this->assertStringContainsString('Gadget Lambda', $decoded['data'][0][0]);
    }

    public function testFiltroCombinadoConBusquedaGlobalSeAcumula(): void
    {
        $filter = base64_encode(json_encode([
            ['field' => 'stock', 'op' => '>', 'value' => 0],
        ]));
        $this->setGet([
            'search'  => ['value' => 'Widget'],
            'sFilter' => $filter,
        ]);

        // Widget Alpha(stock=10) y Widget Beta(stock=5) - Widget Gamma tiene stock 0
        $this->assertSame(2, $this->model->getCountForSearch());
    }

    public function testFiltroSoloCampo(): void
    {
        $this->setGet([
            'search'     => ['value' => 'Alpha'],
            'sOnlyField' => 'nombre',
        ]);
        $this->assertSame(1, $this->model->getCountForSearch());
    }
}
