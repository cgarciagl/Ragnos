<?php

namespace App\Models;

use CodeIgniter\Model;

class Dashboard extends Model
{

    private function getCachedData($cacheKey, $sql)
    {
        $cache      = \Config\Services::cache();
        $cachedData = $cache->get($cacheKey);

        if ($cachedData) {
            return $cachedData;
        }

        $db     = db_connect();
        $query  = $db->query($sql);
        $result = $query->getResultArray();

        $cache->save($cacheKey, $result, 86400); // Cache for 24 hours (86400 seconds)
        return $result;
    }

    function ventasultimos12meses()
    {
        $sql = "SELECT
                concat(MONTHNAME(o.orderDate), '/', YEAR(o.orderDate)) AS Mes,
                SUM(od.priceEach * od.quantityOrdered) AS Total
            FROM orders o
                JOIN orderdetails od ON o.orderNumber = od.orderNumber
            GROUP BY 1
            ORDER BY o.orderDate DESC
            LIMIT 12";
        return $this->getCachedData('ventasultimos12meses', $sql);
    }

    function estadosDeCuenta()
    {
        $sql = "WITH Compras AS (
                    SELECT
                        c.customerNumber,
                        c.customerName,
                        ROUND(SUM(od.priceEach * od.quantityOrdered), 2) AS Comprado,
                        c.creditLimit
                    FROM customers c
                    LEFT JOIN orders o ON o.customerNumber = c.customerNumber
                    LEFT JOIN orderdetails od ON od.orderNumber = o.orderNumber
                    GROUP BY c.customerNumber, c.customerName, c.creditLimit
                ),
                Pagos AS (
                    SELECT
                        c.customerNumber,
                        c.customerName,
                        ROUND(SUM(p.amount), 2) AS Pagado
                    FROM customers c
                    LEFT JOIN payments p ON c.customerNumber = p.customerNumber
                    GROUP BY c.customerNumber, c.customerName
                ),
                Deudas AS (
                    SELECT
                        Compras.customerNumber,
                        Compras.customerName,
                        Compras.Comprado,
                        Pagos.Pagado,
                        ROUND(Compras.Comprado - COALESCE(Pagos.Pagado, 0), 2) AS Deuda,
                        Compras.creditLimit AS LimiteDeCredito
                    FROM Compras
                    LEFT JOIN Pagos ON Compras.customerNumber = Pagos.customerNumber
                )
                SELECT *
                FROM Deudas
                WHERE Deuda <> 0
                ORDER BY Deuda DESC;";
        return $this->getCachedData('estadosdecuenta', $sql);
    }

    function ventasPorLinea()
    {
        $sql = "WITH Last12Months AS (
                SELECT CONCAT(MONTHNAME(o.orderDate), '/', YEAR(o.orderDate)) AS Mes
                FROM  orders o
                GROUP BY  1
                ORDER BY  o.orderDate DESC
                LIMIT 12
            )
            SELECT 
                p.productLine, 
                concat(MONTHNAME(o.orderDate),'/', YEAR(o.orderDate)) as Mes, 
                SUM(od.priceEach * od.quantityOrdered) AS Total
            FROM  orders o
            INNER JOIN orderdetails od ON o.orderNumber = od.orderNumber
            INNER JOIN products p ON p.productCode = od.productCode
            INNER JOIN  Last12Months l12m ON concat(MONTHNAME(o.orderDate),'/', YEAR(o.orderDate)) = l12m.Mes
            GROUP BY  1, 2;";
        return $this->getCachedData('ventasporlinea', $sql);
    }

}