<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Pagos extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checkLogin();
        $this->setTitle('Pagos');
        $this->setTableName('payments');
        $this->setIdField('idPayment');
        $this->addField('customerNumber', ['label' => 'Cliente', 'rules' => 'required']);
        $this->addSearch('customerNumber', 'Tienda\Clientes');
        $this->addField('checkNumber', ['label' => 'Número de cheque', 'rules' => 'required']);
        $this->addField('paymentDate', ['label' => 'Fecha de pago', 'rules' => 'required', 'type' => 'date']);
        $this->addField('amount', ['label' => 'Monto', 'rules' => 'required|numeric|money']);

        $this->setTableFields(['customerNumber', 'checkNumber', 'paymentDate', 'amount']);

        $this->setSortingField('paymentDate', 'desc');
    }
}