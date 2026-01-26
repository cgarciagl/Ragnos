<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;
use App\ThirdParty\Ragnos\Controllers\RDatasetReportGenerator;

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

    /* -------------------------------------------------------------------------- */
    /* Nueva Funcionalidad de Reporte Avanzado (Genérico)                         */
    /* -------------------------------------------------------------------------- */

    public function reporte_avanzado()
    {
        helper('form');

        $generator = new RDatasetReportGenerator($this);

        // A. Procesar POST (Generación)
        if ($this->request->getMethod() === 'post') {
            $generator->processRequest($this->request);
            return $generator->renderResultView();
        }

        // B. Renderizar Configuración (GET)
        return $generator->renderConfigView(site_url('Tienda/Pagos/reporte_avanzado'));
    }
}