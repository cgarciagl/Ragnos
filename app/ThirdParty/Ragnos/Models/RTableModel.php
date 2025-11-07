<?php

namespace App\ThirdParty\Ragnos\Models;

use CodeIgniter\Model;

abstract class RTableModel extends Model
{
    public $table = '';
    public $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

    public $canInsert = TRUE;
    public $canUpdate = TRUE;
    public $canDelete = TRUE;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Devuelve los registros de la tabla ordenados por un criterio
     *
     * @param string $order_by
     * @return object
     */
    function listAll($order_by = NULL)
    {
        if ($order_by == NULL) {
            $order_by = $this->primaryKey;
        }
        return $this->builder()->orderBy($order_by, 'asc')
            ->get();
    }

    /**
     * Asigna una condición al modelo para filtrar los resultados
     *
     * @param string $field al campo a partir del cual se hará el filtro
     * @param string $value el valor que se ha de buscar
     */
    function setWhere()
    {
        if (func_num_args() == 2) {
            $field = func_get_arg(0);
            $value = func_get_arg(1);
            $this->builder()->where($field, $value);
        }
        return $this;
    }

    /**
     *  Pega una tabla adicional a la consulta
     *
     * @param $table
     * @param $cond
     * @param string $type
     * @return mixed
     */
    function join($table, $cond, $type = 'INNER')
    {
        $this->builder()->join($table, $cond, $type);
        return $this;
    }


    public function setOrderByField(string $field, string $direction = 'ASC')
    {
        $allowedDirections = ['ASC', 'DESC'];
        if (in_array($field, $this->allowedFields) && in_array(strtoupper($direction), $allowedDirections)) {
            $this->builder()->orderBy($field, strtoupper($direction));
        } else {
            throw new \InvalidArgumentException("Campo o dirección de ordenamiento no válidos.");
        }
    }

    /**
     * Agrega un campo a la selección
     *
     * @param string $select valor a agregar a la selección
     */
    function select($select = '*')
    {
        $this->builder()->select($select);
        return $this;
    }

    /**
     * Determina el número de registros a devolver
     *
     * @param int $value número de registros
     */
    function limit($value)
    {
        $this->builder()->limit($value);
        return $this;
    }

    /**
     * Devuelve los registros de la tabla
     *
     * @param string $table tabla a seleccionar
     * @return object
     */
    function get()
    {
        return $this->builder()->get();
    }

    function setUseTimestamps($value)
    {
        $this->useTimestamps = (bool) $value;
    }

    function setUseSoftDeletes($value)
    {
        $this->useSoftDeletes = (bool) $value;
    }
}
