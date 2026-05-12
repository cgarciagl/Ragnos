<?php

namespace Tests\Ragnos\Models;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Models\RTableModel;

/**
 * Modelo concreto para probar RTableModel.
 */
class TestRecordModel extends RTableModel
{
    public $table         = 'test_records';
    public $primaryKey    = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'email', 'category'];

    public function __construct($db = null)
    {
        parent::__construct();
        if ($db !== null) {
            $this->db = $db;
        }
    }
}

/**
 * Pruebas de comportamiento real de RTableModel contra SQLite en memoria.
 * Cubre listAll, setWhere, join, select, limit, get, setOrderByField y CRUD.
 */
class RTableModelTest extends RagnosTestCase
{
    private TestRecordModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestTable('test_records', [
            'name'     => ['type' => 'TEXT'],
            'email'    => ['type' => 'TEXT', 'null' => true],
            'category' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->model = new TestRecordModel($this->db);
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('test_records');
        parent::tearDown();
    }

    public function testInsertarYRecuperarRegistro(): void
    {
        $id = $this->model->insert(['name' => 'Ana', 'email' => 'ana@x.com', 'category' => 'A']);
        $this->assertNotFalse($id);

        $record = $this->model->find($id);
        $this->assertSame('Ana', $record['name']);
        $this->assertSame('ana@x.com', $record['email']);
    }

    public function testListAllOrdenaPorPrimaryKeyAsc(): void
    {
        $this->model->insert(['name' => 'C', 'category' => 'X']);
        $this->model->insert(['name' => 'A', 'category' => 'X']);
        $this->model->insert(['name' => 'B', 'category' => 'X']);

        $rows = $this->model->listAll()->getResultArray();
        $this->assertCount(3, $rows);
        // Orden por id (insertion order): C, A, B
        $this->assertSame('C', $rows[0]['name']);
        $this->assertSame('A', $rows[1]['name']);
        $this->assertSame('B', $rows[2]['name']);
    }

    public function testListAllConOrderByPersonalizado(): void
    {
        $this->model->insert(['name' => 'C']);
        $this->model->insert(['name' => 'A']);
        $this->model->insert(['name' => 'B']);

        $rows = $this->model->listAll('name')->getResultArray();
        $this->assertSame(['A', 'B', 'C'], array_column($rows, 'name'));
    }

    public function testSetWhereFiltraRegistros(): void
    {
        $this->model->insert(['name' => 'Ana', 'category' => 'A']);
        $this->model->insert(['name' => 'Bob', 'category' => 'B']);
        $this->model->insert(['name' => 'Ana2', 'category' => 'A']);

        $rows = $this->model->setWhere('category', 'A')->findAll();
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertSame('A', $row['category']);
        }
    }

    public function testSetWhereSinArgumentosCorrectosRetornaThis(): void
    {
        // setWhere espera 2 args; con 0 retorna $this sin error
        $this->assertSame($this->model, $this->model->setWhere());
    }

    public function testSelectLimitaColumnas(): void
    {
        $this->model->insert(['name' => 'Ana', 'email' => 'a@x.com', 'category' => 'A']);

        $row = $this->model->select('name')->get()->getRowArray();
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayNotHasKey('email', $row, 'select() debe restringir columnas devueltas');
        $this->assertArrayNotHasKey('category', $row);
    }

    public function testLimitRestringeNumeroDeFilas(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->model->insert(['name' => "R{$i}"]);
        }
        $rows = $this->model->limit(2)->get()->getResultArray();
        $this->assertCount(2, $rows);
    }

    public function testJoinAgregaTablaRelacionada(): void
    {
        $this->createTestTable('test_categories', [
            'code' => ['type' => 'TEXT'],
            'desc' => ['type' => 'TEXT'],
        ]);
        $this->insertTestData('test_categories', ['code' => 'A', 'desc' => 'Alta']);
        $this->insertTestData('test_categories', ['code' => 'B', 'desc' => 'Baja']);
        $this->model->insert(['name' => 'Ana', 'category' => 'A']);
        $this->model->insert(['name' => 'Bob', 'category' => 'B']);

        $rows = $this->model
            ->select('test_records.name, test_categories.desc as cat_desc')
            ->join('test_categories', 'test_categories.code = test_records.category', 'INNER')
            ->get()->getResultArray();

        $this->assertCount(2, $rows);
        $byName = array_column($rows, 'cat_desc', 'name');
        $this->assertSame('Alta', $byName['Ana']);
        $this->assertSame('Baja', $byName['Bob']);

        $this->dropTestTable('test_categories');
    }

    public function testSetOrderByFieldNoLanzaConCampoYDireccionValidos(): void
    {
        $this->model->insert(['name' => 'B']);
        $this->model->insert(['name' => 'A']);

        // 'name' está en allowedFields, 'DESC' es válido
        $this->model->setOrderByField('name', 'DESC');
        $rows = $this->model->get()->getResultArray();
        $this->assertSame('B', $rows[0]['name']);
        $this->assertSame('A', $rows[1]['name']);
    }

    public function testSetOrderByFieldLanzaParaCampoNoPermitido(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->model->setOrderByField('campo_no_existe', 'ASC');
    }

    public function testSetOrderByFieldLanzaParaDireccionInvalida(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->model->setOrderByField('name', 'INVALID_DIR');
    }

    public function testCanInsertCanUpdateCanDeleteTrueDefault(): void
    {
        $this->assertTrue($this->model->canInsert);
        $this->assertTrue($this->model->canUpdate);
        $this->assertTrue($this->model->canDelete);
    }

    public function testSetUseTimeStampsActualizaPropiedad(): void
    {
        $ref = new \ReflectionProperty($this->model, 'useTimestamps');
        $ref->setAccessible(true);
        $this->assertFalse($ref->getValue($this->model));

        $this->model->setUseTimeStamps(true);
        $this->assertTrue($ref->getValue($this->model));
    }

    public function testSetUseSoftDeletesActualizaPropiedad(): void
    {
        $ref = new \ReflectionProperty($this->model, 'useSoftDeletes');
        $ref->setAccessible(true);
        $this->assertFalse($ref->getValue($this->model));

        $this->model->setUseSoftDeletes(true);
        $this->assertTrue($ref->getValue($this->model));
    }

    public function testUpdateModificaRegistro(): void
    {
        $id = $this->model->insert(['name' => 'Ana']);
        $this->model->update($id, ['name' => 'Ana María']);

        $row = $this->model->find($id);
        $this->assertSame('Ana María', $row['name']);
    }

    public function testDeleteEliminaRegistro(): void
    {
        $id = $this->model->insert(['name' => 'Temp']);
        $this->assertTableCount('test_records', 1);

        $this->model->delete($id);
        $this->assertTableCount('test_records', 0);
    }
}
