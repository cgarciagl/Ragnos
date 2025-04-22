<?php

namespace App\ThirdParty\Ragnos\Controllers;

use App\ThirdParty\Ragnos\Models\RConcreteDatasetModel;
use App\ThirdParty\Ragnos\Models\Fields\RSearchField;
use App\ThirdParty\Ragnos\Models\Fields\RSimpleTextField;


/**
 * Clase Base para los datasets de Ragnos
 */
abstract class RDataset extends RController
{

    protected $title = '';


    public $modelo = NULL;

    /**
     * Constructor de la clase
     */
    function __construct()
    {
        parent::__construct();
        $this->setModel($this->_getOrConstructModel());
    }

    public function __call($method, $args)
    {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        } else {
            return NULL;
        }
    }

    public function getModel()
    {
        return $this->modelo instanceof RConcreteDatasetModel ? $this->modelo : new RConcreteDatasetModel();
    }

    public function setModel($model)
    {
        $this->modelo             = $model;
        $this->modelo->controller = $this;
    }

    /**
     * Retorna un objeto de la clase RDatasetModel y lo asigna a la propiedad "modelo"
     *
     * @return RConcreteDatasetModel
     */
    private function _getOrConstructModel()
    {
        if (!$this->modelo) {
            $this->modelo = new RConcreteDatasetModel();
        }
        return $this->modelo;
    }

    /**
     * Asigna el título de la relación
     *
     * @param string $title
     */
    function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Retorna el título de la relación
     *
     * @return string
     */
    function getTitle()
    {
        return $this->title;
    }

    /**
     * Determina el nombre de la tabla a utilizar como base
     *
     * @param string $tableName Nombre de la tabla
     */
    function setTableName($tableName)
    {
        $this->modelo->table = $tableName;
    }

    /**
     * Determina que campo ha de usarse como llave primaria para la relación
     *
     * @param string $fieldName
     */
    function setIdField($fieldName)
    {
        $this->modelo->primaryKey = $fieldName;
    }

    function setAutoIncrement($value)
    {
        $this->modelo->autoIncrement = $value;
    }

    /**
     * Determina que campos se usaran para mostrar en la vista de tabla
     *
     * @param array $fieldNames Arreglo con los nombres de campos
     */
    function setTableFields($fieldNames = [])
    {
        $this->modelo->tablefields = $fieldNames;
    }

    /**
     * Agrega un campo a la lista de campos de la definición del modelo
     *
     * @param string $fieldName Nombre del campo a agregar
     * @param array $extraOptions Arreglo con las definiciones del campo "label" , "rules", "value"
     */
    function addField($fieldName, $extraOptions = [])
    {
        return $this->modelo->addFieldFromArray($fieldName, $extraOptions);
    }

    /**
     * Asocia una búsqueda con un campo específico
     *
     * @param string $fieldName
     * @param string $controllerclassname
     * @param string $SQLfilter
     */
    function addSearch($fieldName, $controllerclassname, $SQLfilter = '', $callback = '')
    {
        if (!array_key_exists($fieldName, $this->modelo->ofieldlist)) {
            $this->modelo->ofieldlist[$fieldName] = new RSimpleTextField($fieldName);
        }
        $field                                = $this->modelo->ofieldlist[$fieldName];
        $this->modelo->ofieldlist[$fieldName] = new RSearchField($field);
        $this->modelo->ofieldlist[$fieldName]->setController($controllerclassname);
        $this->modelo->ofieldlist[$fieldName]->setFilter($SQLfilter);
        $this->modelo->ofieldlist[$fieldName]->setCallback($callback);
        return $this->modelo->ofieldlist[$fieldName];
    }

    function removeField($fieldName)
    {
        if (array_key_exists($fieldName, $this->modelo->ofieldlist)) {
            unset($this->modelo->ofieldlist[$fieldName]);
        }
    }

    /**
     * Determina la etiqueta que ha de tener un campo los formularios y tablas
     *
     * @param string $fieldName Campo a actualizar
     * @param string $label Etiqueta que mostrar
     */
    function addLabel($fieldName, $label)
    {
        $this->modelo->ofieldlist[$fieldName]->setLabel($label);
    }

    /**
     * Asigna las reglas de validación para el campo especificado
     *
     * @param string $fieldName Campo a actualizar
     * @param string $rules Reglas a aplicar
     */
    function addRules($fieldName, $rules)
    {
        $this->modelo->ofieldlist[$fieldName]->setRules($rules);
    }

    function addDefault($fieldName, $default)
    {
        $this->modelo->ofieldlist[$fieldName]->setDefault($default);
    }

    /**
     * Setter para la propiedad canInsert del modelo
     *
     * @param bool $value
     */
    function setCanInsert($value)
    {
        $this->modelo->canInsert = (bool) $value;
    }

    /**
     * Setter para la propiedad canUpdate del modelo
     *
     * @param bool $value
     */
    function setCanUpdate($value)
    {
        $this->modelo->canUpdate = (bool) $value;
    }

    /**
     * Setter para la propiedad canDelete del modelo
     *
     * @param bool $value
     */
    function setCanDelete($value)
    {
        $this->modelo->canDelete = (bool) $value;
    }

    /**
     * Getter para la propiedad canInsert del modelo
     *
     */
    function canInsert()
    {
        return (bool) $this->modelo->canInsert;
    }

    /**
     * Getter para la propiedad canUpdate del modelo
     *
     */
    function canUpdate()
    {
        return (bool) $this->modelo->canUpdate;
    }

    /**
     * Getter para la propiedad canDelete del modelo
     *
     */
    function canDelete()
    {
        return (bool) $this->modelo->canDelete;
    }

    /**
     * Función "abstracta" para aplicar filtros al catálogo
     */
    public function _filters()
    {
    }

    /**
     * Función que aplica los filtros usando la cache de querys de Codeigniter
     */
    protected function applyFilters()
    {
        $this->_filters();
    }

    /**
     * Disparador Callback (Trigger) para Antes de Insertar
     */
    function _beforeInsert(&$dataArray)
    {
    }

    /**
     * Disparador Callback (Trigger) para Después de Insertar
     */
    function _afterInsert()
    {
    }

    /**
     * Disparador Callback (Trigger) para Antes de Actualizar
     */
    function _beforeUpdate(&$dataArray)
    {
    }

    /**
     * Disparador Callback (Trigger) para Después de Actualizar
     */
    function _afterUpdate()
    {
    }

    /**
     * Disparador Callback (Trigger) para Antes de Borrar
     */
    function _beforeDelete()
    {
    }

    /**
     * Disparador Callback (Trigger) para Después de Borrar
     */
    function _afterDelete()
    {
    }

    function setUseTimestamps($value)
    {
        $this->modelo->setUseTimestamps($value);
    }

    function setCreatedField($value)
    {
        $this->modelo->setCreatedField($value);
    }

    function setUpdatedField($value)
    {
        $this->modelo->setUpdatedField($value);
    }

    function setDeletedField($value)
    {
        $this->modelo->setDeletedField($value);
    }

    function setUseSoftDeletes($value)
    {
        $this->modelo->setUseSoftDeletes($value);
    }
}
