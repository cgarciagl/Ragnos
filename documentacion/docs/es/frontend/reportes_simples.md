# 📊 Generación de Reportes Simples (RSimpleLevelReport)

La clase `RSimpleLevelReport` permite generar reportes en formato de tabla HTML de manera rápida y estructurada a partir de un array de datos. Es ideal para mostrar listados, resúmenes financieros o cualquier conjunto de datos que requiera una presentación tabular, con soporte para agrupación por niveles (cortes de control).

## Características Principales

- Generación automática de tablas HTML.
- Soporte para múltiples niveles de agrupación (encabezados por grupo).
- Cálculo automático de totales de registros.
- Fácil integración con vistas de Ragnos.

## Flujo Básico de Uso

1.  Obtener los datos (generalmente desde un Modelo).
2.  Instanciar `App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport`.
3.  Configurar el reporte usando `quickSetup`.
4.  Renderizar el reporte y pasarlo a la vista.

## API de la Clase

### `quickSetup($title, $data, $listfields, $groups = [], $desc_filter = '')`

Configura los parámetros principales del reporte de una sola vez.

- **`$title`** (string): El título principal del reporte.
- **`$data`** (array): El conjunto de datos (array de arrays asociativos).
- **`$listfields`** (array): Lista de campos a mostrar.
  - Si es un array simple `['Campo1', 'Campo2']`, se buscarán esas claves en `$data`.
  - Se recomienda que los nombres coincidan con las claves del array de datos.
- **`$groups`** (array): (Opcional) Configuración de agrupamiento.
  - Formato: `['campo_agrupador' => ['label' => 'Etiqueta del Grupo']]`.
- **`$desc_filter`** (string): (Opcional) Subtítulo o descripción de filtros aplicados.

### `setShowTotals(bool $showTotals)`

Habilita o deshabilita la visualización del conteo total de registros al final del reporte (por defecto `true`).

### `render($rutadevuelta = 'admin/index')`

Retorna el HTML del reporte renderizado dentro de la vista estándar de reportes. Útil para incrustar en layouts existentes.

### `generate()`

Retorna solo el HTML de la tabla y los encabezados, sin envoltorio de vista completa. Útil para inyecciones AJAX o vistas personalizadas.

---

## Ejemplos de Implementación

### 1. Reporte Simple (Listado Plano)

Este ejemplo muestra un listado de ventas mensuales sin agrupación.

```php
<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

class Reportes extends BaseController
{
    public function ventaspormes()
    {
        // 1. Obtener datos
        $model = new \App\Models\Dashboard();
        $datos = $model->ventasultimos12meses();
        // Estructura esperada de $datos:
        // [['Mes' => 'Enero', 'Total' => 1000], ['Mes' => 'Febrero', 'Total' => 1500], ...]

        // 2. Formatear datos (opcional pero recomendado)
        foreach ($datos as $key => $value) {
            $datos[$key]['Total'] = '$ ' . number_format($value['Total'], 2);
        }

        // 3. Configurar Reporte
        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(false); // Ocultar conteo de registros xq es irrelevante aquí

        // quickSetup(Título, Datos, Columnas a mostrar)
        $reporte->quickSetup('Ventas por Mes', $datos, ['Mes', 'Total']);

        // 4. Renderizar
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }
}
```

### 2. Reporte Agrupado (Corte de Control)

Este ejemplo genera un listado de productos agrupados por **Categoría**. Cada vez que cambia la categoría, se genera un nuevo encabezado.

**Requisito Importante**: Para que la agrupación funcione correctamente, **los datos de entrada deben estar ordenados** por el campo de agrupación.

```php
<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

class Productos extends BaseController
{
    public function listadoPorCategoria()
    {
        // 1. Obtener datos (¡Ordenados por categoría!)
        $data = [
            ['categoria' => 'Electrónica', 'producto' => 'Televisor', 'precio' => 500],
            ['categoria' => 'Electrónica', 'producto' => 'Radio', 'precio' => 50],
            ['categoria' => 'Hogar', 'producto' => 'Silla', 'precio' => 25],
            ['categoria' => 'Hogar', 'producto' => 'Mesa', 'precio' => 100],
        ];

        $reporte = new RSimpleLevelReport();

        // 2. Definir Agrupación
        // La clave 'categoria' debe existir en $data.
        $grupos = [
            'categoria' => ['label' => 'Categoría de Producto']
        ];

        // 3. Configurar
        $reporte->quickSetup(
            'Listado de Productos',
            $data,
            ['producto', 'precio'], // Campos a mostrar en las columnas
            $grupos,                // Configuración de grupos
            'Filtro: Todos los productos activos' // Subtítulo opcional
        );

        return view('admin/reporte_view', ['contenido' => $reporte->render()]);
    }
}
```

### 3. Reporte Multi-Nivel

Es posible agrupar por múltiples niveles (ej: País -> Estado -> Ciudad).

```php
$grupos = [
    'pais'   => ['label' => 'País'],
    'estado' => ['label' => 'Estado/Provincia']
];

// Los datos deben venir ordenados por pais y luego por estado
$reporte->quickSetup('Reporte Geográfico', $data, ['ciudad', 'poblacion'], $grupos);
```

## Aplicaciones Comunes

- **Listados de Inventario**: Agrupados por almacén o categoría.
- **Reportes de Ventas**: Diarios, mensuales o agrupados por vendedor.
- **Auditoría**: Listados de logs de sistema.
- **Resúmenes Financieros**: Listados simples de ingresos/egresos formateados.
