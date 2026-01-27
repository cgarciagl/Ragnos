<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

class Reportes extends BaseController
{

    public function reporte_avanzado()
    {
        // Instanciamos el controlador de Pagos para obtener su configuración
        $pagosController = new Pagos();

        return $pagosController->genericAdvancedReport();
    }

    function ventaspormes()
    {
        $this->checkLogin();
        //cargar el helper utiles
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->ventasultimos12meses();
        //recorremos los datos y formateamos como moneda el Total
        foreach ($datos as $key => $value) {
            $datos[$key]['Total'] = moneyFormat($value['Total']);
        }
        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Ventas por mes', $datos, ['Mes', 'Total']);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }

    function estadosdecuenta()
    {
        $this->checkLogin();
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->estadosDeCuenta();

        foreach ($datos as $key => $value) {
            $datos[$key]['Comprado']        = moneyFormat($value['Comprado']);
            $datos[$key]['Pagado']          = moneyFormat($value['Pagado']);
            $datos[$key]['Deuda']           = moneyFormat($value['Deuda']);
            $datos[$key]['LimiteDeCredito'] = moneyFormat($value['LimiteDeCredito']);
        }

        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Estados de Cuenta', $datos, ['customerNumber', 'customerName', 'Comprado', 'Pagado', 'Deuda', 'LimiteDeCredito']);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }

    function ventasporlinea()
    {
        $this->checkLogin();
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->ventasPorLinea();

        // Ordenar por productLine para el agrupamiento
        usort($datos, function ($a, $b) {
            return strcmp($a['productLine'], $b['productLine']);
        });

        foreach ($datos as $key => $value) {
            $datos[$key]['Total'] = moneyFormat($value['Total']);
        }

        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Ventas por Línea', $datos, ['Mes', 'Total'], ['productLine' => ['label' => 'Línea']]);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }

    function mejoresempleados()
    {
        $this->checkLogin();
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->empleadosConMasVentasEnElUltimoTrimestre();

        // Ordenar por Oficina para el agrupamiento
        usort($datos, function ($a, $b) {
            return strcmp($a['Oficina'], $b['Oficina']);
        });

        foreach ($datos as $key => $value) {
            $datos[$key]['TotalVentasTrimestre'] = moneyFormat($value['TotalVentasTrimestre']);
        }

        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Mejores Empleados (Último Trimestre)', $datos, ['employeeNumber', 'Empleado', 'TotalVentasTrimestre'], ['Oficina' => ['label' => 'Oficina']]);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }

    function menorrotacion()
    {
        $this->checkLogin();
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->productosConMenorRotacion();

        // Ordenar por productLine para el agrupamiento
        usort($datos, function ($a, $b) {
            return strcmp($a['productLine'], $b['productLine']);
        });

        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Productos con Menor Rotación', $datos, ['productCode', 'productName', 'quantityInStock', 'TotalVendidoUltimos6Meses'], ['productLine' => ['label' => 'Línea']]);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }

    function margenporlinea()
    {
        $this->checkLogin();
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->margenDeGananciaPorLinea();

        foreach ($datos as $key => $value) {
            $datos[$key]['MargenTotal']      = moneyFormat($value['MargenTotal']);
            $datos[$key]['PorcentajeMargen'] = $value['PorcentajeMargen'] . '%';
        }

        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Margen por Línea', $datos, ['productLine', 'MargenTotal', 'PorcentajeMargen']);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }

    function ventasporpais()
    {
        $this->checkLogin();
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $model = new \App\Models\Dashboard();
        $datos = $model->ventasPorPais();

        foreach ($datos as $key => $value) {
            $datos[$key]['Total'] = moneyFormat($value['Total']);
        }

        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(true);
        $reporte->quickSetup('Ventas por País', $datos, ['Pais', 'Total']);
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }
}