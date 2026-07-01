<?php

namespace Tests\Support\Models;

use App\ThirdParty\Ragnos\Models\RDatasetModel;

/**
 * Modelo base reutilizable para tests CRUD con tabla en memoria.
 * Se configura con tabla, primaryKey y opcionalmente desactiva auditoría.
 */
class CrudTestModel extends RDatasetModel
{
    public $table = '';
    public $primaryKey = 'id';
    protected $returnType = 'array';

    public function __construct($db = null)
    {
        parent::__construct();
        if ($db !== null) {
            $this->db = $db;
        }
    }

    /**
     * Configura la tabla y campos para el test.
     */
    public function configure(string $table, array $tablefields, bool $enableAudit = false): static
    {
        $this->table       = $table;
        $this->tablefields = $tablefields;

        if (!$enableAudit) {
            $audit = new \ReflectionProperty($this, 'enableAudit');
            $audit->setAccessible(true);
            $audit->setValue($this, false);
        }

        return $this;
    }
}
