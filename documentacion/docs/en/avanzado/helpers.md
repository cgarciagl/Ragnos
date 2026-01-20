# PHP Helpers and Utilities

Ragnos includes collection of global helper functions designed to speed up common tasks and improve application performance. Available throughout framework automatically.

## Database and Cache Management

### `getCachedData()`

Executes raw SQL query and stores result in CodeIgniter 4 cache system. Ideal for heavy queries or reports not needing real-time data.

```php
function getCachedData(string $sql, array $params = [], ?string $cacheKey = null, int $ttl = 86400): array
```

#### Parameters

| Parameter   | Type     | Description                                                                                                |
| :---------- | :------- | :--------------------------------------------------------------------------------------------------------- |
| `$sql`      | `string` | SQL query to execute.                                                                                      |
| `$params`   | `array`  | (Optional) Array of values for bound parameters (`?`) in query.                                            |
| `$cacheKey` | `string` | (Optional) Unique cache identifier. If `null`, hash generated automatically based on query and parameters. |
| `$ttl`      | `int`    | (Optional) Time To Live in seconds. Default `86400` (24 hours).                                            |

#### Return

Returns associative `array` with query results.

#### Usage Example

```php
// In Model or Controller

public function getMetrics()
{
    $sql = "SELECT
                p.productLine,
                SUM(od.quantityOrdered * od.priceEach) as Total
            FROM products p
            JOIN orderdetails od ON p.productCode = od.productCode
            GROUP BY p.productLine";

    // Cache result for 1 hour (3600 seconds) with specific key
    return getCachedData($sql, [], 'dashboard_metrics', 3600);
}
```

#### ⚠️ Warnings and Considerations

1.  **Real Time Data:** Do not use for critical data changing constantly (e.g. real time inventory with high concurrency, bank balances). Data served can be up to `$ttl` seconds old.
2.  **Cache Invalidation:** If using custom `$cacheKey` (like `'dashboard_metrics'`), can manually clear that cache when data changes using `cache()->delete('dashboard_metrics')`. Automatic keys harder to manually invalidate.
3.  **Dev Environment:** Remember if developing and changing data, might see old data until cache cleared (`php spark cache:clear`).

### `queryToAssocArray()`

Executes SQL query and transforms result directly into associative array `[id => value]`. Extremely useful for populating dropdowns (`<select>`).

```php
function queryToAssocArray(string $sql, string $index_key, string $column_key): array
```

#### Example

```php
// Get category list for Dropdown
$sql = "SELECT id, name FROM categories ORDER BY name ASC";
$options = queryToAssocArray($sql, 'id', 'name');

// Result: [1 => 'Electronics', 2 => 'Home', ...]
```

---

## Debugging and Diagnostics

### `dbgConsola()`

Sends PHP data directly to developer browser console. Uses `console.log` via script injection. **Only works in `development` environment.**

```php
function dbgConsola($data, string $label = 'dbgConsola')
```

#### Example

```php
$users = $model->findAll();
dbgConsola($users, 'User List');
// Check Chrome/Firefox console (F12)
```

### `dbgDie()`

Kills script execution and returns provided data in JSON format. Perfect for debugging API/AJAX calls.

```php
function dbgDie($data, int $statusCode = 200): never
```

---

## Format and UI

### `currency()`

Formats numbers as currency respecting regional config (`locale`) without complex extensions.

```php
function currency(float|int $number, bool $includeSymbol = true): string
```

#### Example

```php
echo currency(1500.50); // Shows "$1,500.50" (depending on config)
```

### `returnAsJSON()`

Standardizes JSON responses for API or AJAX controllers. Automatically handles HTTP headers and UTF-8 encoding.

```php
function returnAsJSON($data, $statusCode = 200)
```

#### Example

```php
if (!$user) {
    returnAsJSON(['error' => 'User not found'], 404);
}
returnAsJSON($user);
```
