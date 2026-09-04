<?php

namespace App\ThirdParty\Ragnos\Models;

use App\ThirdParty\Ragnos\Models\Traits\CrudOperationsTrait;
use App\ThirdParty\Ragnos\Models\Traits\FieldManagementTrait;
use App\ThirdParty\Ragnos\Models\Traits\JsonResultTrait;
use App\ThirdParty\Ragnos\Models\Traits\SearchFilterTrait;

abstract class RDatasetModel extends RTableModel
{
    public $ofieldlist = [];
    public $tablefields = [];
    public $controller = NULL;
    public $errors = [];
    public $insertedId = NULL;

    /**
     * SQL base para controladores RQueryController. Si está definido, el JOIN
     * con este modelo se realiza como subquery en lugar de JOIN a tabla real.
     */
    public $baseQuerySQL = null;

    protected $enableAudit = true;

    public $defaultSortingField = '';
    public $defaultSortingDir = 'asc';

    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca un registro por ID. Si es un RQueryController (tiene baseQuerySQL),
     * ejecuta la consulta con CTE en lugar de consultar una tabla física inexistente.
     */
    public function find($id = null)
    {
        if (!empty($this->baseQuerySQL) && $id !== null) {
            $builder = $this->builder();
            $builder->where($this->table . '.' . $this->primaryKey, $id);
            $builder->limit(1);
            $sqlCompiled = $builder->getCompiledSelect();
            $sqlCompiled = " WITH {$this->table} AS ({$this->baseQuerySQL}) " . $sqlCompiled;
            $db          = db_connect();
            $query       = $db->query($sqlCompiled);
            return $query ? $query->getRowArray() : null;
        }

        return parent::find($id);
    }

    use FieldManagementTrait;
    use SearchFilterTrait;
    use CrudOperationsTrait;
    use JsonResultTrait;
}
