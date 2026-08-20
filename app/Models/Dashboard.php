<?php

namespace App\Models;

use CodeIgniter\Model;

class Dashboard extends Model
{
    /**
     * Obtiene el total de ventas mensuales de los últimos 12 meses registrados.
     */
    public function ventasultimos12meses(): array
    {
        $sql = "SELECT
                CONCAT(MONTHNAME(o.orderDate), '/', YEAR(o.orderDate)) AS Mes,
                SUM(od.priceEach * od.quantityOrdered) AS Total
            FROM orders o
            JOIN orderdetails od ON o.orderNumber = od.orderNumber
            WHERE o.status <> 'Cancelled'
            GROUP BY YEAR(o.orderDate), MONTH(o.orderDate), MONTHNAME(o.orderDate)
            ORDER BY MAX(o.orderDate) DESC
            LIMIT 12";
        return getCachedData($sql);
    }

    /**
     * Calcula el estado de cuenta y saldos pendientes por cliente.
     */
    public function estadosDeCuenta(): array
    {
        $sql = "WITH Compras AS (
                    SELECT
                        c.customerNumber,
                        c.customerName,
                        COALESCE(ROUND(SUM(od.priceEach * od.quantityOrdered), 2), 0) AS Comprado,
                        COALESCE(c.creditLimit, 0) AS creditLimit
                    FROM customers c
                    LEFT JOIN orders o ON o.customerNumber = c.customerNumber
                        AND o.status <> 'Cancelled'
                    LEFT JOIN orderdetails od ON od.orderNumber = o.orderNumber
                    GROUP BY c.customerNumber, c.customerName, c.creditLimit
                ),
                Pagos AS (
                    SELECT
                        customerNumber,
                        COALESCE(ROUND(SUM(amount), 2), 0) AS Pagado
                    FROM payments
                    GROUP BY customerNumber
                ),
                Deudas AS (
                    SELECT
                        c.customerNumber,
                        c.customerName,
                        c.Comprado,
                        COALESCE(p.Pagado, 0) AS Pagado,
                        ROUND(c.Comprado - COALESCE(p.Pagado, 0), 2) AS Deuda,
                        c.creditLimit AS LimiteDeCredito
                    FROM Compras c
                    LEFT JOIN Pagos p ON c.customerNumber = p.customerNumber
                )
                SELECT *
                FROM Deudas
                WHERE Deuda <> 0
                ORDER BY Deuda DESC;";
        return getCachedData($sql, [], 'estadosdecuenta');
    }

    /**
     * Desglosa las ventas por línea de producto en los últimos 12 meses.
     */
    public function ventasPorLinea(): array
    {
        $sql = "WITH Last12Months AS (
                    SELECT 
                        YEAR(o.orderDate) AS Anio,
                        MONTH(o.orderDate) AS MesNum,
                        CONCAT(MONTHNAME(o.orderDate), '/', YEAR(o.orderDate)) AS Mes
                    FROM orders o
                    GROUP BY YEAR(o.orderDate), MONTH(o.orderDate), MONTHNAME(o.orderDate)
                    ORDER BY MAX(o.orderDate) DESC
                    LIMIT 12
                )
                SELECT 
                    p.productLine, 
                    l12m.Mes, 
                    SUM(od.priceEach * od.quantityOrdered) AS Total
                FROM orders o
                JOIN orderdetails od ON o.orderNumber = od.orderNumber
                JOIN products p ON p.productCode = od.productCode
                JOIN Last12Months l12m ON YEAR(o.orderDate) = l12m.Anio AND MONTH(o.orderDate) = l12m.MesNum
                WHERE o.status <> 'Cancelled'
                GROUP BY p.productLine, l12m.Mes, l12m.Anio, l12m.MesNum
                ORDER BY p.productLine ASC, l12m.Anio DESC, l12m.MesNum DESC;";
        return getCachedData($sql);
    }

    /**
     * Obtiene los 10 mejores vendedores por ventas en el último trimestre registrado.
     */
    public function empleadosConMasVentasEnElUltimoTrimestre(): array
    {
        $sql = "WITH VentasPorVendedor AS (
                    SELECT 
                        c.salesRepEmployeeNumber AS employeeNumber, 
                        SUM(od.priceEach * od.quantityOrdered) AS TotalVentasTrimestre
                    FROM customers c
                    JOIN orders ord ON c.customerNumber = ord.customerNumber
                    JOIN orderdetails od ON ord.orderNumber = od.orderNumber
                    WHERE ord.status <> 'Cancelled'
                    AND ord.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 3 MONTH)
                    GROUP BY c.salesRepEmployeeNumber
                )
                SELECT 
                    e.employeeNumber, 
                    CONCAT(e.lastName, ', ', e.firstName) AS Empleado, 
                    o.city AS Oficina, 
                    v.TotalVentasTrimestre
                FROM employees e
                JOIN offices o ON e.officeCode = o.officeCode
                JOIN VentasPorVendedor v ON e.employeeNumber = v.employeeNumber
                ORDER BY v.TotalVentasTrimestre DESC
                LIMIT 10;";
        return getCachedData($sql);
    }

    /**
     * Identifica los productos con menor volumen de venta en los últimos 6 meses.
     */
    public function productosConMenorRotacion(): array
    {
        $sql = "WITH VentasRecientes AS (
                    SELECT 
                        od.productCode, 
                        SUM(od.quantityOrdered) AS TotalVendido
                    FROM orderdetails od
                    JOIN orders o ON od.orderNumber = o.orderNumber
                    WHERE o.status <> 'Cancelled'
                    AND o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                    GROUP BY od.productCode
                )
                SELECT 
                    p.productCode, 
                    p.productName, 
                    p.quantityInStock, 
                    p.productLine,
                    COALESCE(vr.TotalVendido, 0) AS TotalVendidoUltimos6Meses
                FROM products p
                LEFT JOIN VentasRecientes vr ON p.productCode = vr.productCode
                ORDER BY TotalVendidoUltimos6Meses ASC, p.quantityInStock DESC
                LIMIT 10;";
        return getCachedData($sql);
    }

    /**
     * Calcula el margen bruto y porcentaje de beneficio por línea de producto.
     */
    public function margenDeGananciaPorLinea(): array
    {
        $sql = "SELECT
                    p.productLine,
                    SUM(od.quantityOrdered * (od.priceEach - p.buyPrice)) AS MargenTotal,
                    COALESCE(ROUND(
                        (SUM(od.quantityOrdered * (od.priceEach - p.buyPrice)) / 
                        NULLIF(SUM(od.quantityOrdered * od.priceEach), 0)) * 100,
                    2), 0) AS PorcentajeMargen
                FROM orderdetails od
                JOIN orders o ON od.orderNumber = o.orderNumber
                JOIN products p ON od.productCode = p.productCode
                WHERE o.status <> 'Cancelled'
                AND o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                GROUP BY p.productLine
                ORDER BY MargenTotal DESC;";
        return getCachedData($sql);
    }

    /**
     * Métrica atómica unificada para las tarjetas principales del Dashboard.
     */
    public function datosAtomicosDashboard(): array
    {
        $sql = "SELECT
                -- 1. Total de Ventas del Último Semestre
                (
                    SELECT COALESCE(ROUND(SUM(od.priceEach * od.quantityOrdered), 2), 0)
                    FROM orders o
                    JOIN orderdetails od ON o.orderNumber = od.orderNumber
                    WHERE o.status <> 'Cancelled'
                    AND o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                ) AS VentasUltimoSemestre,

                -- 2. Órdenes Enviadas en el Último Semestre
                (
                    SELECT COUNT(orderNumber)
                    FROM orders o
                    WHERE o.status = 'Shipped'
                    AND o.shippedDate IS NOT NULL
                    AND o.shippedDate >= DATE_SUB((SELECT MAX(shippedDate) FROM orders), INTERVAL 6 MONTH)
                ) AS OrdenesEnviadasSemestre,

                -- 3. Valor Promedio de la Orden
                (
                    SELECT COALESCE(ROUND(AVG(TotalVentas), 2), 0)
                    FROM (
                        SELECT o.orderNumber, SUM(od.priceEach * od.quantityOrdered) AS TotalVentas
                        FROM orders o
                        JOIN orderdetails od ON o.orderNumber = od.orderNumber
                        WHERE o.status <> 'Cancelled'
                        AND o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                        GROUP BY o.orderNumber
                    ) AS VentasPorOrden
                ) AS ValorPromedioOrdenSemestral,

                -- 4. Margen Promedio de Beneficio
                (
                    SELECT COALESCE(ROUND(
                        (SUM(od.quantityOrdered * (od.priceEach - p.buyPrice)) / 
                        NULLIF(SUM(od.quantityOrdered * od.priceEach), 0)) * 100,
                    2), 0)
                    FROM orderdetails od
                    JOIN orders o ON od.orderNumber = o.orderNumber
                    JOIN products p ON od.productCode = p.productCode
                    WHERE o.status <> 'Cancelled'
                    AND o.orderDate >= DATE_SUB((SELECT MAX(orderDate) FROM orders), INTERVAL 6 MONTH)
                ) AS MargenPromedioSemestral;";
        return getCachedData($sql);
    }

    /**
     * Distribución geográfica de ventas totales agrupadas por país del cliente.
     */
    public function ventasPorPais(): array
    {
        $sql = "SELECT 
                    c.country AS Pais, 
                    SUM(od.quantityOrdered * od.priceEach) AS Total
                FROM customers c
                JOIN orders o ON c.customerNumber = o.customerNumber
                JOIN orderdetails od ON o.orderNumber = od.orderNumber
                WHERE o.status <> 'Cancelled'
                GROUP BY c.country
                ORDER BY Total DESC;";
        return getCachedData($sql, [], 'ventaspais');
    }

    /**
     * Historial de órdenes de un cliente específico (no cacheado para tiempo real).
     */
    public function ventasDelCliente(int $customerNumber): array
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
                LIMIT 50;";
        return $this->db->query($sql, [$customerNumber])->getResultArray();
    }
}