<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Lineas extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checkLogin();
        $this->setTitle('Lineas de productos');
        $this->setTableName('productlines');
        $this->setIdField('productLine');
        $this->setAutoIncrement(false);
        $this->addField('productLine', ['label' => 'Línea de producto', 'rules' => 'required|is_unique']);
        $this->addField('textDescription', ['label' => 'Descripción', 'rules' => 'required', 'type' => 'textarea']);
        $this->addField('htmlDescription', ['label' => 'Descripción HTML', 'rules' => '', 'type' => 'htmltextarea']);

        $this->setTableFields(['productLine', 'textDescription']);
    }

    function _beforeDelete()
    {
        raise('No se pueden eliminar líneas de productos');
    }

}
