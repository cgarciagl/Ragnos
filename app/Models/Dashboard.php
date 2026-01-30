<?php

namespace App\Models;

use CodeIgniter\Model;

class Dashboard extends Model
{

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
        return getCachedData($sql);
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
        return getCachedData($sql, [], 'estadosdecuenta');
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
        return getCachedData($sql);
    }

    function empleadosConMasVentasEnElUltimoTrimestre()
    {
        $sql = "WITH 
                VentasPorVendedor AS (
                    SELECT c.salesRepEmployeeNumber AS employeeNumber, SUM(od.priceEach * od.quantityOrdered) AS TotalVentasTrimestre
                    FROM customers c
                    JOIN orders ord ON c.customerNumber = ord.customerNumber
                    JOIN orderdetails od ON ord.orderNumber = od.orderNumber
                    WHERE ord.orderDate >=  DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 3 MONTH)
                    GROUP BY c.salesRepEmployeeNumber
                )
                SELECT e.employeeNumber, CONCAT( e.lastName, ', ', e.firstName) AS Empleado, o.city AS Oficina, v.TotalVentasTrimestre
                FROM employees e
                JOIN offices o ON e.officeCode = o.officeCode
                JOIN  VentasPorVendedor v ON e.employeeNumber = v.employeeNumber
                ORDER BY v.TotalVentasTrimestre DESC
                LIMIT 10;";
        return getCachedData($sql);
    }

    function productosConMenorRotacion()
    {
        $sql = "SELECT p.productCode, 
                    p.productName, 
                    p.quantityInStock, 
                    p.productLine,
                    IFNULL(SUM(od.quantityOrdered), 0) AS TotalVendidoUltimos6Meses
                FROM products p
                LEFT JOIN orderdetails od ON p.productCode = od.productCode
                LEFT JOIN orders o ON od.orderNumber = o.orderNumber 
                    AND o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                GROUP BY p.productCode, 
                    p.productName, 
                    p.quantityInStock
                ORDER BY TotalVendidoUltimos6Meses ASC
                LIMIT 10;";
        return getCachedData($sql);
    }

    function margenDeGananciaPorLinea()
    {
        $sql = "SELECT
                p.productLine,
                SUM(od.quantityOrdered * (od.priceEach - p.buyPrice)) AS MargenTotal,
                ROUND(
                    (SUM(od.quantityOrdered * (od.priceEach - p.buyPrice)) / SUM(od.quantityOrdered * od.priceEach)) * 100,
                2) AS PorcentajeMargen
                FROM orderdetails od
                JOIN orders o ON od.orderNumber = o.orderNumber
                JOIN products p ON od.productCode = p.productCode
                WHERE
                o.orderDate >= DATE_SUB(
                    (SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH
                )
                GROUP BY 1
                ORDER BY MargenTotal DESC;";
        return getCachedData($sql);
    }

    function datosAtomicosDashboard()
    {
        $sql = "SELECT
                -- 1. Total de Ventas del Último Semestre (Métrica de Rendimiento)
                (
                    SELECT 
                    FORMAT(SUM(od.priceEach * od.quantityOrdered), 2)
                    FROM orders o
                    JOIN orderdetails od ON o.orderNumber = od.orderNumber
                    WHERE o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                ) AS VentasUltimoSemestre,

                -- 2. Órdenes Enviadas en el Último Semestre (Métrica de Volumen/Operación)
                (
                    SELECT
                    COUNT(orderNumber)
                    FROM orders o
                    WHERE o.status = 'Shipped'
                    AND o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                ) AS OrdenesEnviadasSemestre,

                -- 3. Valor Promedio de la Orden (Métrica de Valor de Cliente)
                (
                    SELECT 
                    FORMAT(AVG(TotalVentas), 2)
                    FROM (
                    SELECT
                        o.orderNumber,
                        SUM(od.priceEach * od.quantityOrdered) AS TotalVentas
                    FROM orders o
                    JOIN orderdetails od ON o.orderNumber = od.orderNumber
                    WHERE o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                    GROUP BY 1
                    ) AS VentasPorOrden
                ) AS ValorPromedioOrdenSemestral,

                -- 4. Margen Promedio de Beneficio (Métrica de Rentabilidad)
                (
                    SELECT 
                    ROUND(
                        (SUM(od.quantityOrdered * (od.priceEach - p.buyPrice)) / SUM(od.quantityOrdered * od.priceEach)) * 100,
                    2)
                    FROM orderdetails od
                    JOIN orders o ON od.orderNumber = o.orderNumber
                    JOIN products p ON od.productCode = p.productCode
                    WHERE o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                ) AS MargenPromedioSemestral;";
        return getCachedData($sql);
    }

    function ventasPorPais()
    {
        $sql = "SELECT 
                c.country AS Pais, 
                SUM(od.quantityOrdered * od.priceEach) AS Total
            FROM customers c
            JOIN orders o ON c.customerNumber = o.customerNumber
            JOIN orderdetails od ON o.orderNumber = od.orderNumber
            GROUP BY c.country
            ORDER BY Total DESC";
        return getCachedData($sql, [], 'ventaspais');
    }

    function ventasDelCliente($customerNumber)
    {
        $sql = "SELECT 
                o.orderNumber,
                o.orderDate,
                o.status,
                SUM(od.quantityOrdered * od.priceEach) AS TotalVenta
            FROM orders o
            JOIN orderdetails od ON o.orderNumber = od.orderNumber
            WHERE o.customerNumber = ?
            GROUP BY o.orderNumber, o.orderDate, o.status
            ORDER BY o.orderDate DESC
            Limit 50;";
        return $this->db->query($sql, [$customerNumber])->getResultArray();
    }
}