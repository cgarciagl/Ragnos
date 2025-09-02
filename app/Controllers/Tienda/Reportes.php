<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;


class Reportes extends BaseController
{

    function ventaspormes()
    {
        $this->checklogin();
        //cargar el helper utiles
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->ventasultimos12meses();
        //recorremos los datos y formateamos como moneda el Total
        foreach ($datos as $key => $value) {
            $datos[$key]['Total'] = moneyFormat($value['Total']);
        }
        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(false);
        $reporte->quickSetup('Ventas por mes', $datos, ['Mes', 'Total']);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }
}