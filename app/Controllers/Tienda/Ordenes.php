<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Ordenes extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checklogin();
        $this->setTitle('Ordenes de compra');
        $this->setTableName('orders');
        $this->setIdField('orderNumber');
        $this->setAutoIncrement(false);

        $this->addField('orderNumber', ['label' => 'Número de orden', 'rules' => 'required|is_unique']);
        $this->addField('orderDate', ['label' => 'Fecha de orden', 'rules' => 'required', 'type' => 'date']);
        $this->addField('requiredDate', ['label' => 'Fecha requerida', 'rules' => '', 'type' => 'date']);
        $this->addField('shippedDate', ['label' => 'Fecha de envío', 'rules' => '', 'type' => 'date']);

        $statuses = [
            'Shipped'    => 'Enviado',
            'Resolved'   => 'Resuelto',
            'Cancelled'  => 'Cancelado',
            'On Hold'    => 'En espera',
            'Disputed'   => 'Disputado',
            'In Process' => 'En proceso'
        ];
        $this->addField('status', [
            'label'   => 'Estado',
            'rules'   => 'required',
            'type'    => 'dropdown',
            'default' => 'In Process',
            'options' => $statuses
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
        $this->setHasDetails(true);
    }

    function _customFormDataFooter()
    {
        return view('tienda/ordenescustomfooter', []);
    }

    /**
     * Calculate the total for a specific order
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    function calculatotal()
    {
        $orderNumber = getRagnosInputValue('orden');

        // Validate input
        if (empty($orderNumber)) {
            return $this->respondWithError(400, 'Order number is required');
        }

        try {
            $builder = $this->db->table('orderdetails');
            $result  = $builder->select('SUM(quantityOrdered * priceEach) AS total')
                ->where('orderNumber', $orderNumber)
                ->get()
                ->getRow();

            if (!$result || $result->total === null) {
                return $this->respondWithError(404, 'Order not found or has no details');
            }

            return $this->response->setJSON([
                'success'  => true,
                'total'    => moneyFormat($result->total),
                'rawTotal' => (float) $result->total
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error calculating order total: ' . $e->getMessage());
            return $this->respondWithError(500, 'Failed to calculate total');
        }
    }
}
