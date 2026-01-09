<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Clientes extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checklogin();
        $this->setTitle('Clientes');
        $this->setTableName('customers');
        $this->setIdField('customerNumber');
        $this->setAutoIncrement(false);
        $this->addField('customerName', ['label' => 'Nombre', 'rules' => 'required']);
        $this->addField('contactLastName', ['label' => 'Apellido de contacto', 'rules' => 'required']);
        $this->addField('contactFirstName', ['label' => 'Nombre de contacto', 'rules' => 'required']);

        $this->addField('Contacto', [
            'label' => 'Contacto',
            'rules' => 'readonly',
            'query' => "concat(contactLastName, ', ', contactFirstName)",
            'type'  => 'hidden'
        ]);

        $this->addField('phone', ['label' => 'Teléfono', 'rules' => 'required']);
        $this->addField('addressLine1', ['label' => 'Dirección 1', 'rules' => 'required']);
        $this->addField('addressLine2', ['label' => 'Dirección 2', 'rules' => '']);
        $this->addField('city', ['label' => 'Ciudad', 'rules' => 'required']);
        $this->addField('state', ['label' => 'Estado', 'rules' => '']);
        $this->addField('postalCode', ['label' => 'Código postal', 'rules' => 'required']);
        $this->addField('country', ['label' => 'País', 'rules' => 'required']);

        $this->addField('salesRepEmployeeNumber', ['label' => 'Empleado a cargo', 'rules' => '']);
        $this->addSearch('salesRepEmployeeNumber', 'Tienda\Empleados');

        $this->addField('creditLimit', ['label' => 'Límite de crédito', 'rules' => 'required|money']);

        $this->setTableFields(['customerName', 'Contacto', 'salesRepEmployeeNumber']);
    }

    function _afterUpdate()
    {
        if (fieldHasChanged('creditLimit')) {
            $cache = \Config\Services::cache();
            $cache->delete('estadosdecuenta');
        }

        if (fieldHasChanged('country')) {
            $cache = \Config\Services::cache();
            $cache->delete('ventaspais');
        }
    }

    function _customFormDataFooter($idCliente)
    {
        //cargamos el modelo Dashboard para usar en la vista
        $dashboardModel = new \App\Models\Dashboard();
        $data           = ['ventas' => $dashboardModel->ventasDelCliente($idCliente)];
        return view('tienda/clientescustomfooter', $data);
    }
}
