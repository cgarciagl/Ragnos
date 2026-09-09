# Helpers y Utilidades PHP

Ragnos incluye una colección de funciones auxiliares (helpers) globales diseñadas para agilizar tareas comunes y mejorar el rendimiento de la aplicación. Estas funciones están disponibles en todo el framework automáticamente.

## Gestión de Base de Datos y Caché

### `getCachedData()`

Esta función ejecuta una consulta SQL pura y almacena el resultado en el sistema de caché de CodeIgniter 4. Es ideal para consultas pesadas o reportes que no requieren datos en tiempo real.

```php
function getCachedData(string $sql, array $params = [], ?string $cacheKey = null, int $ttl = 86400): array
```

#### Parámetros

| Parámetro   | Tipo     | Descripción                                                                                                                        |
| :---------- | :------- | :--------------------------------------------------------------------------------------------------------------------------------- |
| `$sql`      | `string` | La consulta SQL a ejecutar.                                                                                                        |
| `$params`   | `array`  | (Opcional) Array de valores para los parámetros vinculados (`?`) en la consulta.                                                   |
| `$cacheKey` | `string` | (Opcional) Identificador único para el caché. Si es `null`, se generará un hash automático basado en la consulta y los parámetros. |
| `$ttl`      | `int`    | (Opcional) Tiempo de vida (Time To Live) en segundos. Por defecto es `86400` (24 horas).                                           |

#### Retorno

Retorna un `array` asociativo con los resultados de la consulta.

#### Ejemplo de Uso

```php
// En un Modelo o Controlador

public function obtenerMetricas()
{
    $sql = "SELECT
                p.productLine,
                SUM(od.quantityOrdered * od.priceEach) as Total
            FROM products p
            JOIN orderdetails od ON p.productCode = od.productCode
            GROUP BY p.productLine";

    // Cachear el resultado por 1 hora (3600 segundos) con una clave específica
    return getCachedData($sql, [], 'metricas_dashboard', 3600);
}
```

#### ⚠️ Advertencias y Consideraciones

1.  **Datos en Tiempo Real:** No utilice esta función para datos críticos que cambian constantemente (ej. inventario en tiempo real si hay alta concurrencia, saldos bancarios). La información servida puede tener hasta `$ttl` segundos de antigüedad.
2.  **Invalidación de Caché:** Si utiliza una `$cacheKey` personalizada (como `'metricas_dashboard'`), puede limpiar manualmente ese caché cuando los datos cambien usando `cache()->delete('metricas_dashboard')`. Si deja que el sistema genere la clave automáticamente, la invalidación manual es más difícil.
3.  **Entorno de Desarrollo:** Recuerde que si está desarrollando y cambiando datos, es posible que siga viendo los datos antiguos hasta que limpie el caché (`php spark cache:clear`).

### `queryToAssocArray()`

Ejecuta una consulta SQL y transforma el resultado directamente en un array asociativo del tipo `[id => valor]`. Es extremadamente útil para poblar listas desplegables (`<select>`).

```php
function queryToAssocArray(string $sql, string $index_key, string $column_key): array
```

#### Ejemplo

```php
// Obtener lista de categorías para un Dropdown
$sql = "SELECT id, nombre FROM categorias ORDER BY nombre ASC";
$opciones = queryToAssocArray($sql, 'id', 'nombre');

// Resultado: [1 => 'Electrónica', 2 => 'Hogar', ...]
```

---

## Exportación de Archivos (Excel y CSV)

### `arrayToXLSXFile()` / `arrayToExcelFile()`

Convierte un array asociativo o matriz de datos en un archivo nativo de Microsoft Excel (`.xlsx`) sin necesidad de librerías externas o dependencias de Composer como PhpSpreadsheet. Genera archivos compactos con estilos modernos (encabezados en negrita y color de fondo, cuadrícula con bordes, ajuste automático del ancho de columna, panel superior inmovilizado y filtros automáticos).

```php
function arrayToXLSXFile(
    array $results,
    string $fileName = 'temp.xlsx',
    bool $download = true,
    string $sheetName = 'Sheet1',
    array $options = []
): bool
```

#### Parámetros

| Parámetro | Tipo | Descripción |
| :--- | :--- | :--- |
| `$results` | `array` | Array asociativo o matriz con los datos a exportar. Los nombres de las claves del primer elemento se utilizarán como encabezados de columna. |
| `$fileName` | `string` | Nombre del archivo o ruta de destino (por defecto: `'temp.xlsx'`). Si no incluye `.xlsx`, se añadirá automáticamente. |
| `$download` | `bool` | Si es `true`, envía los encabezados HTTP para descargar el archivo directamente en el navegador. Si es `false`, lo guarda en el disco del servidor. |
| `$sheetName` | `string` | Nombre de la pestaña de la hoja de cálculo (por defecto: `'Sheet1'`). |
| `$options` | `array` | (Opcional) Opciones avanzadas de personalización y estilo. |

#### Opciones de Personalización (`$options`)

| Opción | Tipo | Defecto | Descripción |
| :--- | :--- | :--- | :--- |
| `'header_bg'` | `string` | `'1F4E79'` | Color de fondo del encabezado en formato HEX (ej. `'107C41'` para verde Excel, `'2B579A'` para azul corporativo). |
| `'header_color'` | `string` | `'FFFFFF'` | Color del texto del encabezado en formato HEX. |
| `'auto_width'` | `bool` | `true` | Calcula y ajusta el ancho de cada columna de acuerdo al contenido más largo. |
| `'auto_filter'` | `bool` | `true` | Añade los desplegables de filtro automático de Excel en la fila de encabezados. |
| `'freeze_header'` | `bool` | `true` | Inmoviliza la primera fila para que los encabezados permanezcan visibles al hacer scroll. |
| `'custom_widths'` | `array` | `[]` | Anchos personalizados específicos por letra o índice de columna (ej. `['A' => 25, 'B' => 40]`). |
| `'font_name'` | `string` | `'Calibri'` | Nombre de la fuente tipográfica. |
| `'font_size'` | `int` | `11` | Tamaño de la fuente tipográfica. |

#### Ejemplos de Uso

##### 1. Descarga Directa al Navegador desde un Controlador
```php
namespace App\Controllers;

class Ventas extends BaseController
{
    public function exportarExcel()
    {
        helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

        $db = \Config\Database::connect();
        $ventas = $db->table('orders')
            ->select('orderNumber as Pedido, orderDate as Fecha, status as Estado, customerNumber as Cliente')
            ->get()
            ->getResultArray();

        // Descarga directa al usuario como 'reporte_pedidos.xlsx'
        arrayToXLSXFile($ventas, 'reporte_pedidos.xlsx');
    }
}
```

##### 2. Guardar en Servidor Local (para adjuntar a un Email o proceso Batch)
```php
helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

$datos = [
    ['id' => 101, 'producto' => 'Monitor 4K', 'precio' => 450.00, 'stock' => 12, 'codigo' => '00123'],
    ['id' => 102, 'producto' => 'Teclado Mecánico', 'precio' => 95.50, 'stock' => 30, 'codigo' => '00456'],
];

$rutaDestino = WRITEPATH . 'uploads/reportes/inventario_' . date('Ymd_His') . '.xlsx';

// Pasar download = false guarda el archivo en la ruta indicada
if (arrayToXLSXFile($datos, $rutaDestino, false, 'Inventario')) {
    log_message('info', 'Archivo generado exitosamente en: ' . $rutaDestino);
}
```

##### 3. Opciones Avanzadas de Estilos y Filtros
```php
helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

$clientes = [
    ['id' => 1, 'nombre' => 'Acme Corp', 'saldo' => 15200.75, 'activo' => true],
    ['id' => 2, 'nombre' => 'Globex Ltd', 'saldo' => -350.00, 'activo' => false],
];

arrayToXLSXFile($clientes, 'clientes_vip.xlsx', true, 'Clientes VIP', [
    'header_bg'     => '107C41', // Verde estilo Excel
    'header_color'  => 'FFFFFF', // Texto blanco
    'auto_filter'   => true,     // Filtros desplegables automáticos
    'freeze_header' => true,     // Fila de encabezado fija
    'custom_widths' => ['A' => 10, 'B' => 35, 'C' => 18, 'D' => 12]
]);
```

---

### `htmlToXLSXFile()` / `htmlToExcelFile()`

Convierte contenido HTML (tablas simples o reportes complejos de Ragnos con títulos, filtros activos, fecha/hora, grupos de corte de control, subtotales y resumen general) en un archivo nativo de Microsoft Excel (`.xlsx`) sin necesidad de librerías externas o dependencias como PhpSpreadsheet.

Preserva fielmente la jerarquía visual del reporte, ajusta los anchos de columna automáticamente para evitar textos truncados y aplica una paleta de estilos ejecutiva con colores diferenciados para cabeceras, grupos, subtotales y totales generales.

```php
function htmlToXLSXFile(
    string $html,
    string $fileName = 'reporte.xlsx',
    bool $download = true,
    string $sheetName = 'Reporte',
    array $options = []
): bool
```

#### Parámetros

| Parámetro | Tipo | Descripción |
| :--- | :--- | :--- |
| `$html` | `string` | Contenido HTML a convertir (puede ser una tabla `<table>` o el HTML completo de un reporte `#imprimible`). |
| `$fileName` | `string` | Nombre del archivo o ruta de destino (por defecto: `'reporte.xlsx'`). Si no incluye `.xlsx`, se añadirá automáticamente. |
| `$download` | `bool` | Si es `true`, envía los encabezados HTTP para descarga directa en el navegador. Si es `false`, guarda el archivo en disco. |
| `$sheetName` | `string` | Nombre de la pestaña de la hoja de cálculo (por defecto: `'Reporte'`). |
| `$options` | `array` | (Opcional) Opciones avanzadas de personalización de estilos. |

#### Opciones de Personalización (`$options`)

| Opción | Tipo | Defecto | Descripción |
| :--- | :--- | :--- | :--- |
| `'header_bg'` | `string` | `'1F4E79'` | Color HEX de fondo para las cabeceras de columnas en tablas (azul corporativo por defecto). |
| `'header_color'` | `string` | `'FFFFFF'` | Color HEX del texto de cabeceras de columnas. |
| `'title_color'` | `string` | `'1F4E79'` | Color HEX utilizado para el título principal del reporte y los bordes del resumen general. |
| `'group_bg'` | `string` | `'E9EEF4'` | Color HEX de fondo para las filas de encabezados de grupo (control break). |
| `'subtotal_bg'` | `string` | `'F2F4F7'` | Color HEX de fondo para las filas de subtotales. |
| `'grand_bg'` | `string` | `'D9E1F2'` | Color HEX de fondo para la fila de resumen general (gran total). |
| `'font_name'` | `string` | `'Calibri'` | Nombre de la fuente tipográfica. |
| `'font_size'` | `int` | `11` | Tamaño base de la fuente. |
| `'auto_width'` | `bool` | `true` | Ajustar automáticamente el ancho de las columnas según su contenido. |

#### Ejemplos de Uso

##### 1. Exportar un Reporte RSimpleLevelReport Directamente
```php
helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

$reporte = new \App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport();
$reporte->quickSetup('Mejores Empleados', $datos, ['employeeNumber', 'Empleado', 'TotalVentasTrimestre'], ['Oficina' => ['label' => 'Oficina']]);

// Generar el HTML del reporte y convertirlo a un archivo .xlsx real
$html = $reporte->generate();
htmlToXLSXFile($html, 'mejores_empleados.xlsx');
```

##### 2. Exportación Nativa desde la Instancia de RSimpleLevelReport
El objeto `RSimpleLevelReport` incluye el método directo `exportToXLSX()`:
```php
$reporte = new \App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport();
$reporte->quickSetup('Margen por Línea', $datos, ['productLine', 'MargenTotal']);

// Descarga directa en el navegador
$reporte->exportToXLSX('margen_por_linea.xlsx');

// O guardar en el servidor
$reporte->exportToXLSX(WRITEPATH . 'reportes/margen.xlsx', false);
```

##### 3. Descarga Directa Vía Parámetro GET
Cualquier controlador que invoque `$reporte->render()` soporta exportación inmediata a `.xlsx` simplemente añadiendo `?export=xlsx` a la URL:
```text
http://localhost/ragnos/content/index.php/tienda/reportes/mejoresempleados?export=xlsx
```


### `buildZipPackage()`

Empaqueta una colección de archivos o contenidos en un archivo ZIP/XLSX estándar. Utiliza la extensión nativa `\ZipArchive` si está disponible en PHP; de lo contrario, conmuta de forma transparente a un empaquetador ZIP nativo escrito en PHP puro (aprovechando `gzdeflate` de la extensión `zlib` incorporada en PHP). Esto garantiza que la generación de hojas de cálculo `.xlsx` funcione al 100% sin importar si la extensión compilada `php-zip` está presente o no.

```php
function buildZipPackage(string $outputPath, array $entries): bool
```

#### Parámetros

| Parámetro | Tipo | Descripción |
| :--- | :--- | :--- |
| `$outputPath` | `string` | Ruta completa del archivo `.zip` o `.xlsx` a crear. |
| `$entries` | `array` | Array asociativo `['ruta/interna' => ['type' => 'string'\|'file', 'content' => ...]]`. |


### `arrayToCSVFile()`

Convierte un array asociativo en un archivo `.csv` estándar compatible con Excel y UTF-8 (con BOM incluido para compatibilidad con caracteres especiales y tildes).

```php
function arrayToCSVFile(
    array $results,
    $fileName = 'temp.csv',
    $download = true,
    $delimiter = ',',
    $enclosure = '"',
    $escape = '\\'
): bool
```

#### Parámetros

| Parámetro | Tipo | Descripción |
| :--- | :--- | :--- |
| `$results` | `array` | Array de datos asociativos a exportar. |
| `$fileName` | `string` | Nombre del archivo o ruta de salida (por defecto: `'temp.csv'`). |
| `$download` | `bool` | `true` para descargar en el navegador, `false` para guardar en disco local. |
| `$delimiter` | `string` | Carácter delimitador de campos (por defecto: `','`). |
| `$enclosure` | `string` | Carácter delimitador de texto (por defecto: `'"'`). |
| `$escape` | `string` | Carácter de escape (por defecto: `'\\'`). |

#### Ejemplo de Uso

```php
helper('App\ThirdParty\Ragnos\Helpers\csvfiles_helper');

$productos = [
    ['id' => 1, 'nombre' => 'Laptop Gamer', 'precio' => 1200.00],
    ['id' => 2, 'nombre' => 'Mouse USB', 'precio' => 25.50],
];

// Descarga directa en formato CSV compatible con Excel
arrayToCSVFile($productos, 'catalogo_productos.csv');
```

---

## Depuración y Diagnóstico

### `dbgConsola()`

Envía datos de PHP directamente a la consola del navegador del desarrollador. Utiliza `console.log` vía inyección de script. **Solo funciona en entorno de desarrollo (`development`).**

```php
function dbgConsola($data, string $label = 'dbgConsola')
```

#### Ejemplo

```php
$usuarios = $model->findAll();
dbgConsola($usuarios, 'Lista de Usuarios');
// Verifica la consola de Chrome/Firefox (F12)
```

### `dbgDie()`

Mata la ejecución del script y devuelve los datos proporcionados en formato JSON. Es perfecto para depurar llamadas API o AJAX.

```php
function dbgDie($data, int $statusCode = 200): never
```

---

## Formato y UI

### `currency()`

Formatea números como moneda respetando la configuración regional (`locale`) sin necesidad de extensiones complejas.

```php
function currency(float|int $number, bool $includeSymbol = true): string
```

#### Ejemplo

```php
echo currency(1500.50); // Muestra "$1,500.50" (dependiendo de config)
```

### `returnAsJSON()`

Estandariza las respuestas JSON de tu API o controladores AJAX. Maneja automáticamente los encabezados HTTP y la codificación UTF-8.

```php
function returnAsJSON($data, $statusCode = 200)
```

#### Ejemplo

```php
if (!$usuario) {
    returnAsJSON(['error' => 'Usuario no encontrado'], 404);
}
returnAsJSON($usuario);
```
