<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Productos extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checkLogin();
        $this->setTitle('Productos');
        $this->setTableName('products');
        $this->setIdField('productCode');
        $this->setAutoIncrement(false);
        $this->addField('productName', ['label' => 'Nombre', 'rules' => 'required']);
        $this->addField('productCode', ['label' => 'Código', 'rules' => 'required|is_unique']);
        $this->addField('productLine', ['label' => 'Línea', 'rules' => 'required']);
        $this->addSearch('productLine', 'Tienda\Lineas');
        $this->addField('productScale', ['label' => 'Escala', 'rules' => 'required']);
        $this->addField('productVendor', ['label' => 'Proveedor', 'rules' => 'required']);
        $this->addField('productDescription', ['label' => 'Descripción', 'rules' => 'required', 'type' => 'textarea', 'tab' => 'Detalles']);
        $this->addField('quantityInStock', ['label' => 'Cantidad en stock', 'rules' => 'required|numeric']);
        $this->addField('buyPrice', ['label' => 'Precio de compra', 'rules' => 'required|numeric|money', 'tab' => 'Detalles']);
        $this->addField('MSRP', ['label' => 'Precio de Venta Sugerido', 'rules' => 'required|numeric|money', 'tab' => 'Detalles']);

        $this->setTableFields(['productName', 'productCode', 'productLine', 'productVendor', 'quantityInStock', 'MSRP']);
    }
}
