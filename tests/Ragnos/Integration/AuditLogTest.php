<?php

namespace Tests\Ragnos\Integration;

use Tests\Ragnos\RagnosTestCase;
use App\ThirdParty\Ragnos\Controllers\RDataset;
use App\ThirdParty\Ragnos\Controllers\Ragnos;
use App\ThirdParty\Ragnos\Models\RDatasetModel;

class AuditProductoModel extends RDatasetModel
{
    public $table         = 'productos_audit';
    public $primaryKey    = 'id';
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
 * Tests de integración para auditoría (CrudOperationsTrait::logAudit).
 * Verifica que los INSERT/UPDATE/DELETE generan entradas correctas
 * en gen_audit_logs cuando enableAudit está activo.
 */
class AuditLogTest extends RagnosTestCase
{
    private RDataset $controller;
    private AuditProductoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        helper([
            'App\ThirdParty\Ragnos\Helpers\utiles_helper',
            'App\ThirdParty\Ragnos\Helpers\ragnos_helper',
            'text',
        ]);

        // logAudit() consulta session()->has('usu_id'); el DatabaseHandler
        // por defecto no soporta SQLite, así que forzamos FileHandler en tests.
        $sessionConfig = config('Session');
        if ($sessionConfig !== null && property_exists($sessionConfig, 'driver')) {
            $sessionConfig->driver  = \CodeIgniter\Session\Handlers\FileHandler::class;
            $sessionConfig->savePath = WRITEPATH . 'session';
        }

        // Tabla bajo auditoría
        $this->createTestTable('productos_audit', [
            'nombre' => ['type' => 'TEXT'],
            'precio' => ['type' => 'REAL'],
        ]);

        // Tabla de log que el modelo AuditLogModel espera
        $this->createTestTable('gen_audit_logs', [
            'user_id'    => ['type' => 'INTEGER', 'null' => true],
            'table_name' => ['type' => 'TEXT'],
            'record_id'  => ['type' => 'INTEGER', 'null' => true],
            'action'     => ['type' => 'TEXT'],
            'changes'    => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'TEXT', 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
        ]);

        Ragnos::$CI = null;

        $this->controller = new class extends RDataset {
        };
        $this->model = new AuditProductoModel($this->db);
        $this->controller->setModel($this->model);
        $this->model->tablefields = ['nombre', 'precio'];

        \CodeIgniter\Config\Services::reset(true);
        $_POST = [];
        $_GET  = [];
    }

    protected function tearDown(): void
    {
        $this->dropTestTable('productos_audit');
        $this->dropTestTable('gen_audit_logs');
        \CodeIgniter\Config\Services::reset(true);
        $_POST = [];
        $_GET  = [];
        parent::tearDown();
    }

    private function setPost(array $data): void
    {
        service('request')->setGlobal('post', $data);
        service('request')->setGlobal('request', $data);
    }

    private function getAuditLogs(): array
    {
        return $this->db->table('gen_audit_logs')->get()->getResultArray();
    }

    public function testInsertGeneraEntradaDeAuditoria(): void
    {
        $this->setPost(['nombre' => 'Laptop', 'precio' => '500']);
        $this->model->processFormInput();
        $this->assertNotNull($this->model->insertedId);
        $this->assertSame([], $this->model->errors);

        $logs = $this->getAuditLogs();
        $this->assertCount(1, $logs);

        $log = $logs[0];
        $this->assertSame('INSERT', $log['action']);
        $this->assertSame('productos_audit', $log['table_name']);
        $this->assertEquals($this->model->insertedId, $log['record_id']);

        // changes contiene los datos insertados
        $changes = json_decode($log['changes'], true);
        $this->assertIsArray($changes);
        $this->assertArrayHasKey('new', $changes);
        $this->assertSame('Laptop', $changes['new']['nombre']);
    }

    public function testUpdateGeneraEntradaConDiff(): void
    {
        // Insert directo en BD sin auditoría
        $audit = new \ReflectionProperty($this->model, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($this->model, false);

        $this->db->table('productos_audit')->insert(['nombre' => 'Antes', 'precio' => 100]);
        $insertId = $this->db->insertID();

        // Reactivar auditoría para el update
        $audit->setValue($this->model, true);

        $this->setPost([
            'id'                       => (string) $insertId,
            'nombre'                   => 'Despues',
            'precio'                   => '200',
            'Ragnos_value_ant_id'      => (string) $insertId,
            'Ragnos_value_ant_nombre'  => 'Antes',
            'Ragnos_value_ant_precio'  => '100',
        ]);
        $this->model->completeFieldList();
        $this->model->processFormInput();

        $logs = $this->getAuditLogs();
        $this->assertCount(1, $logs);
        $log = $logs[0];

        $this->assertSame('UPDATE', $log['action']);
        $this->assertEquals($insertId, $log['record_id']);

        $changes = json_decode($log['changes'], true);
        $this->assertArrayHasKey('nombre', $changes);
        $this->assertSame('Antes', $changes['nombre']['old']);
        $this->assertSame('Despues', $changes['nombre']['new']);
    }

    public function testDeleteGeneraEntradaConDatosEliminados(): void
    {
        $audit = new \ReflectionProperty($this->model, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($this->model, false);

        $this->db->table('productos_audit')->insert(['nombre' => 'Borrame', 'precio' => 50]);
        $insertId = $this->db->insertID();

        $audit->setValue($this->model, true);

        $this->model->completeFieldList();
        $this->setPost([
            'id'                      => (string) $insertId,
            'nombre'                  => 'Borrame',
            'Ragnos_value_ant_nombre' => 'Distinto', // para que createInputDataArray devuelva el campo
        ]);
        $deleted = $this->model->performDelete($insertId);
        $this->assertTrue((bool) $deleted);

        $logs = $this->getAuditLogs();
        $this->assertCount(1, $logs);
        $log = $logs[0];

        $this->assertSame('DELETE', $log['action']);
        $changes = json_decode($log['changes'], true);
        $this->assertArrayHasKey('deleted_data', $changes);
        $this->assertSame('Borrame', $changes['deleted_data']['nombre']);
    }

    public function testEnableAuditFalseNoGeneraEntradas(): void
    {
        $audit = new \ReflectionProperty($this->model, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($this->model, false);

        $this->setPost(['nombre' => 'SinAudit', 'precio' => '99']);
        $this->model->processFormInput();
        $this->assertNotNull($this->model->insertedId);

        $logs = $this->getAuditLogs();
        $this->assertCount(0, $logs, 'No se debe loggear nada cuando enableAudit=false');
    }

    public function testInsertConCaracteresUnicodePreservaAcentos(): void
    {
        $this->setPost(['nombre' => 'Cañón Año Múltiple', 'precio' => '100']);
        $this->model->processFormInput();

        $logs    = $this->getAuditLogs();
        $changes = json_decode($logs[0]['changes'], true);

        // JSON_UNESCAPED_UNICODE en logAudit preserva los caracteres
        $this->assertSame('Cañón Año Múltiple', $changes['new']['nombre']);
        // El JSON original guardado no debe contener escapes \uXXXX
        $this->assertStringNotContainsString('\\u', $logs[0]['changes']);
    }

    public function testMultiplesOperacionesGeneranMultiplesLogs(): void
    {
        // Insert 1
        $this->setPost(['nombre' => 'A', 'precio' => '10']);
        $this->model->processFormInput();
        $idA = $this->model->insertedId;

        // Insert 2
        $this->setPost(['nombre' => 'B', 'precio' => '20']);
        $this->model->insertedId = null;
        $this->model->errors     = [];
        $this->model->processFormInput();

        // Delete del primero
        $this->model->completeFieldList();
        $this->setPost([
            'id'                      => (string) $idA,
            'nombre'                  => 'A',
            'Ragnos_value_ant_nombre' => 'distinto',
        ]);
        $this->model->performDelete($idA);

        $logs    = $this->getAuditLogs();
        $actions = array_column($logs, 'action');
        $this->assertCount(3, $logs);
        $this->assertSame(['INSERT', 'INSERT', 'DELETE'], $actions);
    }
}
