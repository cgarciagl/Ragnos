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

    use FieldManagementTrait;
    use SearchFilterTrait;
    use CrudOperationsTrait;
    use JsonResultTrait;
}
