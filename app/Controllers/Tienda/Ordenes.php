<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Ordenes extends RDatasetController
{
    public const STATUSES = [
        'Shipped'    => 'Enviado',
        'Resolved'   => 'Resuelto',
        'Cancelled'  => 'Cancelado',
        'On Hold'    => 'En espera',
        'Disputed'   => 'Disputado',
        'In Process' => 'En proceso'
    ];

    function __construct()
    {
        parent::__construct();
        $this->checkLogin();
        $this->setTitle('Ordenes de compra');
        $this->setTableName('orders');
        $this->setIdField('orderNumber');
        $this->setAutoIncrement(false);

        $this->addField('orderNumber', ['label' => 'Número de orden', 'rules' => 'required|is_unique']);
        $this->addField('orderDate', ['label' => 'Fecha de orden', 'rules' => 'required', 'type' => 'date']);
        $this->addField('requiredDate', ['label' => 'Fecha requerida', 'rules' => '', 'type' => 'date']);
        $this->addField('shippedDate', ['label' => 'Fecha de envío', 'rules' => '', 'type' => 'date']);

        $this->addField('status', [
            'label'   => 'Estado',
            'rules'   => 'required',
            'type'    => 'dropdown',
            'default' => 'In Process',
            'options' => self::STATUSES
        ]);

        $this->addField('customerNumber', ['label' => 'Cliente', 'rules' => 'required']);
        $this->addSearch('customerNumber', 'Tienda\Clientes');

        $this->addField('comments', ['label' => 'Comentarios', 'type' => 'textarea']);

        $this->addField(
            'total',
            [
                'label' => 'Total',
                'rules' => 'disabled|money',
                'query' => "select sum(od.quantityOrdered * od.priceEach) from orderdetails od where od.orderNumber = orders.orderNumber",
            ]
        );

        $this->setTableFields(['orderNumber', 'orderDate', 'status', 'customerNumber', 'total']);

        $this->setSortingField('orderDate', 'desc');
        $this->setDetailsController('Tienda\Ordenesdetalles');
    }

    /**
     * Calcula el total de una orden específica
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function calculatotal()
    {
        $orderNumber = getInputValue('orden');

        // Validate input
        if (empty($orderNumber)) {
            return $this->respondWithError(400, 'El número de orden es requerido');
        }

        try {
            $builder = $this->db->table('orderdetails');
            $result  = $builder->select('SUM(quantityOrdered * priceEach) AS total')
                ->where('orderNumber', $orderNumber)
                ->get()
                ->getRow();

            if (!$result || $result->total === null) {
                return $this->respondWithError(404, 'Orden no encontrada o sin detalles');
            }

            return $this->response->setJSON([
                'success'  => true,
                'total'    => moneyFormat($result->total),
                'rawTotal' => (float) $result->total
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error al calcular el total de la orden: ' . $e->getMessage());
            return $this->respondWithError(500, 'Error al calcular el total');
        }
    }

    /**
     * Hook: Antes de actualizar, revisamos cambios de estado
     */
    function _beforeUpdate(&$data)
    {
        $id = oldValue('orderNumber');
        // 1. Automatización de Fecha de Envío
        // Si el usuario cambia el estado a 'Shipped' y no puso fecha, la ponemos hoy.
        if (isset($data['status']) && $data['status'] === 'Shipped') {
            // Verificamos si no enviaron ya una fecha manual
            if (empty($data['shippedDate'])) {
                $data['shippedDate'] = date('Y-m-d');
            }
        }

        // 2. Bloqueo de Cancelación
        // Si la orden ya fue enviada, impedimos cancelarla sin permiso especial
        if (isset($data['status']) && $data['status'] === 'Cancelled') {
            $ordenActual = $this->modelo->find($id);
            if ($ordenActual['status'] === 'Shipped') {
                // Ragnos capturará esto y mostrará un error bonito
                raise("No puedes cancelar una orden que ya fue enviada. Haz una devolución.");
            }
        }

        return $data;
    }

    /**
     * Hook: Integridad antes de borrar
     */
    function _beforeDelete()
    {
        $id = oldValue('orderNumber');
        // No permitir borrar órdenes enviadas, solo cancelarlas
        $orden = $this->modelo->find($id);
        if ($orden && $orden['status'] === 'Shipped') {
            raise("Por seguridad y auditoría, las órdenes enviadas no se pueden eliminar.");
        }
    }
}
