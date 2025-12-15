<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Ordenesdetalles extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checklogin();

        $this->setTitle('Detalles de orden');
        $this->setTableName('orderdetails');
        $this->setIdField('idDetail');
        $this->addField('orderNumber', ['label' => 'Número de orden', 'rules' => 'required', 'default' => $this->master, 'type' => 'hidden']);

        $this->addField('productCode', ['label' => 'Producto', 'rules' => 'required']);
        $this->addSearch('productCode', 'Tienda\Productos');

        $this->addField('quantityOrdered', ['label' => 'Cantidad ordenada', 'rules' => 'required']);
        $this->addField('priceEach', ['label' => 'Precio unitario', 'rules' => 'required|money']);

        $this->setTableFields(['productCode', 'quantityOrdered', 'priceEach']);
    }

    function _filters()
    {
        $this->modelo->builder()->where('orderNumber', $this->master);
    }

    function _afterUpdate()
    {
        $cache = \Config\Services::cache();
        $cache->clean();
    }

    function _afterInsert()
    {
        $cache = \Config\Services::cache();
        $cache->clean();
    }

    function _afterDelete()
    {
        $cache = \Config\Services::cache();
        $cache->clean();
    }
}
