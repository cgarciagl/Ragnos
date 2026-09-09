# PHP Helpers and Utilities

Ragnos includes a collection of global helper functions designed to speed up common tasks and improve application performance. Available throughout the framework automatically.

## Database and Cache Management

### `getCachedData()`

Executes a raw SQL query and stores the result in the CodeIgniter 4 cache system. Ideal for heavy queries or reports not needing real-time data.

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

Returns an associative `array` with query results.

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
2.  **Cache Invalidation:** If using custom `$cacheKey` (like `'dashboard_metrics'`), you can manually clear that cache when data changes using `cache()->delete('dashboard_metrics')`. Automatic keys are harder to manually invalidate.
3.  **Dev Environment:** Remember if developing and changing data, you might see old data until cache is cleared (`php spark cache:clear`).

### `queryToAssocArray()`

Executes a SQL query and transforms the result directly into an associative array `[id => value]`. Extremely useful for populating dropdowns (`<select>`).

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

## File Export (Excel & CSV)

### `arrayToXLSXFile()` / `arrayToExcelFile()`

Converts an associative array or data matrix into a native Microsoft Excel (`.xlsx`) file without needing external Composer libraries like PhpSpreadsheet. Generates compact files with modern styling (bold header with custom background fill, clean grid borders, automatic column width calculation, frozen top row pane, and auto-filters).

```php
function arrayToXLSXFile(
    array $results,
    string $fileName = 'temp.xlsx',
    bool $download = true,
    string $sheetName = 'Sheet1',
    array $options = []
): bool
```

#### Parameters

| Parameter | Type | Description |
| :--- | :--- | :--- |
| `$results` | `array` | Associative array or data matrix to export. The array keys of the first item are used as column headers. |
| `$fileName` | `string` | Target file name or path (default: `'temp.xlsx'`). If `.xlsx` extension is omitted, it is appended automatically. |
| `$download` | `bool` | If `true`, sends HTTP download headers to the client browser. If `false`, saves the file to server storage. |
| `$sheetName` | `string` | Spreadsheet tab name (default: `'Sheet1'`). |
| `$options` | `array` | (Optional) Advanced customization and styling options. |

#### Customization Options (`$options`)

| Option | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `'header_bg'` | `string` | `'1F4E79'` | Header background color in HEX format (e.g. `'107C41'` for Excel green, `'2B579A'` for corporate blue). |
| `'header_color'` | `string` | `'FFFFFF'` | Header text color in HEX format. |
| `'auto_width'` | `bool` | `true` | Calculates and adjusts each column width dynamically based on longest text. |
| `'auto_filter'` | `bool` | `true` | Adds Excel auto-filter dropdown arrows across the header row. |
| `'freeze_header'` | `bool` | `true` | Freezes the first row so headers remain visible when scrolling down. |
| `'custom_widths'` | `array` | `[]` | Explicit custom column widths by column letter or index (e.g. `['A' => 25, 'B' => 40]`). |
| `'font_name'` | `string` | `'Calibri'` | Typography font family name. |
| `'font_size'` | `int` | `11` | Typography font size in points. |

#### Usage Examples

##### 1. Direct Browser Download in a Controller
```php
namespace App\Controllers;

class Sales extends BaseController
{
    public function exportExcel()
    {
        helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

        $db = \Config\Database::connect();
        $orders = $db->table('orders')
            ->select('orderNumber as OrderID, orderDate as Date, status as Status, customerNumber as Customer')
            ->get()
            ->getResultArray();

        // Direct download to user browser as 'sales_report.xlsx'
        arrayToXLSXFile($orders, 'sales_report.xlsx');
    }
}
```

##### 2. Save on Server Storage (for Email Attachments or Batch Jobs)
```php
helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

$data = [
    ['id' => 101, 'product' => '4K Monitor', 'price' => 450.00, 'stock' => 12, 'code' => '00123'],
    ['id' => 102, 'product' => 'Mechanical Keyboard', 'price' => 95.50, 'stock' => 30, 'code' => '00456'],
];

$targetPath = WRITEPATH . 'uploads/reports/inventory_' . date('Ymd_His') . '.xlsx';

// Passing download = false saves the file directly to server path
if (arrayToXLSXFile($data, $targetPath, false, 'Inventory')) {
    log_message('info', 'File successfully generated at: ' . $targetPath);
}
```

##### 3. Advanced Styling and Filter Options
```php
helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

$clients = [
    ['id' => 1, 'name' => 'Acme Corp', 'balance' => 15200.75, 'active' => true],
    ['id' => 2, 'name' => 'Globex Ltd', 'balance' => -350.00, 'active' => false],
];

arrayToXLSXFile($clients, 'vip_clients.xlsx', true, 'VIP Clients', [
    'header_bg'     => '107C41', // Excel-like green
    'header_color'  => 'FFFFFF', // White text
    'auto_filter'   => true,     // Excel dropdown filters
    'freeze_header' => true,     // Sticky header row
    'custom_widths' => ['A' => 10, 'B' => 35, 'C' => 18, 'D' => 12]
]);
```

---

### `htmlToXLSXFile()` / `htmlToExcelFile()`

Converts HTML content (simple tables or complex Ragnos reports with titles, active filters, date/time, control-break grouping headers, subtotals, and grand summary tables) into a native Microsoft Excel (`.xlsx`) file without requiring external libraries or Composer packages like PhpSpreadsheet.

It faithfully preserves the report's visual hierarchy, automatically calculates column widths to avoid truncated text, and applies an executive styling palette with distinct colors for headers, groups, subtotals, and grand totals.

```php
function htmlToXLSXFile(
    string $html,
    string $fileName = 'reporte.xlsx',
    bool $download = true,
    string $sheetName = 'Reporte',
    array $options = []
): bool
```

#### Parameters

| Parameter | Type | Description |
| :--- | :--- | :--- |
| `$html` | `string` | HTML content to convert (can be a raw `<table>` or complete `#imprimible` report container). |
| `$fileName` | `string` | Target file name or path (default: `'reporte.xlsx'`). If `.xlsx` extension is omitted, it is appended automatically. |
| `$download` | `bool` | If `true`, sends HTTP headers for direct browser download. If `false`, saves file to local storage. |
| `$sheetName` | `string` | Spreadsheet worksheet tab name (default: `'Reporte'`). |
| `$options` | `array` | (Optional) Advanced customization and styling options. |

#### Customization Options (`$options`)

| Option | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `'header_bg'` | `string` | `'1F4E79'` | Header background color in HEX format (corporate blue by default). |
| `'header_color'` | `string` | `'FFFFFF'` | Header text color in HEX format. |
| `'title_color'` | `string` | `'1F4E79'` | Main title text color and grand total border accent color in HEX format. |
| `'group_bg'` | `string` | `'E9EEF4'` | Background color for control-break grouping headers in HEX format. |
| `'subtotal_bg'` | `string` | `'F2F4F7'` | Background color for subtotal rows in HEX format. |
| `'grand_bg'` | `string` | `'D9E1F2'` | Background color for grand summary / total rows in HEX format. |
| `'font_name'` | `string` | `'Calibri'` | Typography font family name. |
| `'font_size'` | `int` | `11` | Base typography font size in points. |
| `'auto_width'` | `bool` | `true` | Automatically calculate and adjust column widths according to content. |

#### Usage Examples

##### 1. Export an RSimpleLevelReport Directly
```php
helper('App\ThirdParty\Ragnos\Helpers\xlsxfiles_helper');

$reporte = new \App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport();
$reporte->quickSetup('Top Employees', $datos, ['employeeNumber', 'Empleado', 'TotalVentasTrimestre'], ['Oficina' => ['label' => 'Office']]);

// Generate HTML and convert directly into a real .xlsx file
$html = $reporte->generate();
htmlToXLSXFile($html, 'top_employees.xlsx');
```

##### 2. Native Export via RSimpleLevelReport Instance
The `RSimpleLevelReport` object provides the direct `exportToXLSX()` method:
```php
$reporte = new \App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport();
$reporte->quickSetup('Margin by Line', $datos, ['productLine', 'MargenTotal']);

// Direct download to user browser
$reporte->exportToXLSX('margin_by_line.xlsx');

// Or save on server storage
$reporte->exportToXLSX(WRITEPATH . 'reports/margin.xlsx', false);
```

##### 3. Direct Download via GET Parameter
Any controller invoking `$reporte->render()` automatically supports instant `.xlsx` download by appending `?export=xlsx` to the URL:
```text
http://localhost/ragnos/content/index.php/tienda/reportes/mejoresempleados?export=xlsx
```


### `buildZipPackage()`

Packages a collection of files or string contents into a standard ZIP/XLSX file. Uses the native `\ZipArchive` extension if installed and enabled; otherwise, seamlessly falls back to a pure PHP ZIP packager (leveraging `gzdeflate` from PHP's built-in `zlib` extension). This ensures that `.xlsx` generation works 100% reliably regardless of whether the compiled `php-zip` extension is installed.

```php
function buildZipPackage(string $outputPath, array $entries): bool
```

#### Parameters

| Parameter | Type | Description |
| :--- | :--- | :--- |
| `$outputPath` | `string` | Full filesystem path of the destination `.zip` or `.xlsx` file. |
| `$entries` | `array` | Associative array `['internal/path' => ['type' => 'string'\|'file', 'content' => ...]]`. |


### `arrayToCSVFile()`

Converts an associative array into a standard `.csv` file compatible with Excel and UTF-8 (with BOM included for special characters and accents).

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

#### Parameters

| Parameter | Type | Description |
| :--- | :--- | :--- |
| `$results` | `array` | Associative data array to export. |
| `$fileName` | `string` | Output file name or target path (default: `'temp.csv'`). |
| `$download` | `bool` | `true` to download in browser, `false` to save to server storage. |
| `$delimiter` | `string` | Delimiter character (default: `','`). |
| `$enclosure` | `string` | Enclosure character (default: `'"'`). |
| `$escape` | `string` | Escape character (default: `'\\'`). |

#### Usage Example

```php
helper('App\ThirdParty\Ragnos\Helpers\csvfiles_helper');

$products = [
    ['id' => 1, 'name' => 'Gaming Laptop', 'price' => 1200.00],
    ['id' => 2, 'name' => 'USB Mouse', 'price' => 25.50],
];

// Direct browser download as Excel-compatible CSV
arrayToCSVFile($products, 'products_catalog.csv');
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
