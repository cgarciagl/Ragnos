<?php

namespace Tests\Support;

use Tests\Support\Controllers\TestRDataset;
use Tests\Support\Models\CrudTestModel;
use App\ThirdParty\Ragnos\Controllers\Ragnos;

/**
 * Trait que centraliza la configuración de tests CRUD e Integración.
 *
 * Proporciona:
 * - Creación de tablas temporales con y sin auditoría
 * - Controller + Model listos para usar (TestRDataset + CrudTestModel)
 * - Helper para desactivar/activar auditoría vía reflection
 * - Limpieza automática en tearDown
 */
trait CrudTestSetup
{
    use RequestSimulator;

    protected TestRDataset $controller;
    protected CrudTestModel $model;
    protected string $testTable = '';

    /**
     * Inicializa el test CRUD: crea tabla, controller y modelo.
     * Llamar desde setUp() del test case.
     *
     * @param string $tableName Nombre de la tabla temporal
     * @param array  $columns   Columnas para createTestTable (ej: ['nombre' => ['type' => 'TEXT']])
     * @param array  $tablefields Campos del modelo (ej: ['nombre', 'precio'])
     * @param bool   $enableAudit Si true, no desactiva auditoría (requiere tabla gen_audit_logs)
     */
    protected function initCrudTest(
        string $tableName,
        array $columns,
        array $tablefields,
        bool $enableAudit = false
    ): void {
        $this->testTable = $tableName;

        $this->loadRagnosHelpers();
        $this->resetRequest();

        // Crear tabla
        $this->createTestTable($tableName, $columns);

        // Si se requiere auditoría, crear también la tabla de logs
        if ($enableAudit) {
            $this->createAuditLogTable();
        }

        // Reset singleton Ragnos
        Ragnos::$CI = null;

        // Controller + Model
        $this->controller = new TestRDataset();
        $this->model      = new CrudTestModel($this->db);
        $this->controller->setModel($this->model);
        $this->model->configure($tableName, $tablefields, $enableAudit);
    }

    /**
     * Crea la tabla gen_audit_logs necesaria para tests de auditoría.
     */
    protected function createAuditLogTable(): void
    {
        if (!$this->tableExists('gen_audit_logs')) {
            $this->createTestTable('gen_audit_logs', [
                'user_id'    => ['type' => 'INTEGER', 'null' => true],
                'table_name' => ['type' => 'TEXT'],
                'record_id'  => ['type' => 'INTEGER', 'null' => true],
                'action'     => ['type' => 'TEXT'],
                'changes'    => ['type' => 'TEXT', 'null' => true],
                'ip_address' => ['type' => 'TEXT', 'null' => true],
                'user_agent' => ['type' => 'TEXT', 'null' => true],
            ]);
        }
    }

    /**
     * Obtiene los logs de auditoría de la tabla gen_audit_logs.
     */
    protected function getAuditLogs(): array
    {
        return $this->db->table('gen_audit_logs')->get()->getResultArray();
    }

    /**
     * Desactiva la auditoría en el modelo vía reflection.
     */
    protected function disableAudit(): void
    {
        $audit = new \ReflectionProperty($this->model, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($this->model, false);
    }

    /**
     * Activa la auditoría en el modelo vía reflection.
     */
    protected function enableAudit(): void
    {
        $audit = new \ReflectionProperty($this->model, 'enableAudit');
        $audit->setAccessible(true);
        $audit->setValue($this->model, true);
    }

    /**
     * Limpia el test: dropea tablas, resetea request, llama a parent::tearDown().
     * Debe llamarse al final del tearDown() del test case.
     */
    protected function cleanupCrudTest(): void
    {
        if ($this->testTable !== '') {
            $this->dropTestTable($this->testTable);
        }
        if ($this->tableExists('gen_audit_logs')) {
            $this->dropTestTable('gen_audit_logs');
        }
        $this->resetRequest();
    }
}
