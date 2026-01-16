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
