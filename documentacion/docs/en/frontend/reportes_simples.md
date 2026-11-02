# 📊 Simple Report Generation (RSimpleLevelReport)

The `RSimpleLevelReport` class lets you generate well-structured HTML table reports quickly, directly from a data array. It is ideal for displaying lists, financial summaries, or any data set that needs a tabular presentation, with support for grouping levels (control breaks).

## Main Features

- Automatic generation of professionally styled HTML tables.
- Support for multiple grouping levels (headers per group).
- Automatic calculation of record totals.
- **Detection and calculation of sums for numeric columns (subtotals and grand totals).**
- Easy integration with Ragnos views.

## Basic Usage Flow

1.  Get the data (usually from a model).
2.  Instantiate `Ragnos\Controllers\RSimpleLevelReport`.
3.  Configure the report with `quickSetup`.
4.  Render the report and pass it to a view.

## Class API

### `quickSetup($title, $data, $listfields, $groups = [], $desc_filter = '')`

Configures the main report parameters in a single call.

- **`$title`** (string): The main report title.
- **`$data`** (array): The data set (an array of associative arrays).
- **`$listfields`** (array): The list of fields to show.
  - If it is a simple array such as `['Field1', 'Field2']`, those keys are looked up in `$data`.
  - The names should match the keys of the data array.
- **`$groups`** (array): (Optional) The grouping configuration.
  - Format: `['grouping_field' => ['label' => 'Group Label']]`.
- **`$desc_filter`** (string): (Optional) A subtitle or description of the applied filters.

### `setShowTotals(bool $showTotals)`

Enables or disables the totals (record count and sums) at the end of each group and of the report (default `true`).

### `setSummableFields(array $fields)`

Explicitly defines which fields are summed in the table footers.

- **`$fields`** (array): The names (labels) of the fields that contain numeric values.
- _Note:_ If you do not call this method, the class tries to detect summable fields by comparing the exact name of each column (case-sensitive) against a fixed list, for example `Total`, `Monto`, `Precio`, `Saldo`, `Deuda`, `amount`, or `MSRP`. If your columns have other names, list them with this method.

### `render($returnRoute = 'admin/index')`

Returns the rendered report HTML inside the standard report view. Useful for embedding it in existing layouts.

### `generate()`

Returns only the HTML of the table and its headers, without the full view wrapper. Useful for AJAX injection or custom views.

---

## Implementation Examples

### 1. Simple Report (Flat List)

An example that shows a list of monthly sales without grouping.

```php
<?php

namespace App\Controllers\Tienda;

use Ragnos\Controllers\BaseController;
use Ragnos\Controllers\RSimpleLevelReport;

class Reportes extends BaseController
{
    public function ventaspormes()
    {
        // 1. Get data
        $model = new \App\Models\Dashboard();
        $datos = $model->ventasultimos12meses();
        // Expected structure of $datos:
        // [['Mes' => 'Enero', 'Total' => 1000], ['Mes' => 'Febrero', 'Total' => 1500], ...]

        // 2. Format data (optional but recommended)
        foreach ($datos as $key => $value) {
            $datos[$key]['Total'] = '$ ' . number_format($value['Total'], 2);
        }

        // 3. Configure Report
        $reporte = new RSimpleLevelReport();
        $reporte->setShowTotals(false); // Hide the record count, since it is irrelevant here

        // quickSetup(Title, Data, Columns to show)
        $reporte->quickSetup('Ventas por Mes', $datos, ['Mes', 'Total']);

        // 4. Render
        $contenido = $reporte->render();
        return view('admin/reporte_view', ['contenido' => $contenido]);
    }
}
```

This example follows the real `Tienda\Reportes` controller of the demo store, so its methods, columns, and view keep their original Spanish names.

### 2. Grouped Report (Control Break)

This example generates a list of products grouped by **Category**. Every time the category changes, a new header is generated.

**Important Requirement**: For grouping to work correctly, **the input data must be sorted** by the grouping field.

```php
<?php

namespace App\Controllers\Tienda;

use Ragnos\Controllers\BaseController;
use Ragnos\Controllers\RSimpleLevelReport;

class Products extends BaseController
{
    public function listByCategory()
    {
        // 1. Get data (Sorted by category!)
        $data = [
            ['category' => 'Electronics', 'product' => 'TV', 'price' => 500],
            ['category' => 'Electronics', 'product' => 'Radio', 'price' => 50],
            ['category' => 'Home', 'product' => 'Chair', 'price' => 25],
            ['category' => 'Home', 'product' => 'Table', 'price' => 100],
        ];

        $report = new RSimpleLevelReport();

        // 2. Define Grouping
        // Key 'category' must exist in $data.
        $groups = [
            'category' => ['label' => 'Product Category']
        ];

        // 3. Configure
        $report->quickSetup(
            'Product List',
            $data,
            ['product', 'price'], // Fields to show in columns
            $groups,              // Groups config
            'Filter: All active products' // Optional subtitle
        );

        return view('admin/report_view', ['content' => $report->render()]);
    }
}
```

### 3. Multi-Level Report

You can group by multiple levels (e.g. Country -> State -> City).

```php
$groups = [
    'country' => ['label' => 'Country'],
    'state'   => ['label' => 'State/Province']
];

// Data must come sorted by country then state
$report->quickSetup('Geographic Report', $data, ['city', 'population'], $groups);
```

### 4. Financial Report with Totals

This example shows how the class automatically handles sums and grand totals.

```php
// Sales data
$data = [
    ['salesperson' => 'John', 'sale' => 100.50, 'commission' => 10],
    ['salesperson' => 'Mary', 'sale' => 200.00, 'commission' => 20],
    ['salesperson' => 'Pete', 'sale' => 150.00, 'commission' => 15],
];

$report = new RSimpleLevelReport();
$report->quickSetup('Commission Report', $data, ['salesperson', 'sale', 'commission']);

// 'sale' and 'commission' are not among the names detected automatically,
// so they are listed explicitly
$report->setSummableFields(['sale', 'commission']);

// Rendering will generate:
// - A table with the data.
// - A "GRAND TOTAL" footer aligned correctly.
// - The 'sale' column will sum to 450.50
// - The 'commission' column will sum to 45
return $report->render();
```

## Common Applications

- **Inventory Lists**: Grouped by warehouse or category.
- **Sales Reports**: Daily, monthly or grouped by salesperson.
- **Audit**: Lists of system logs.
- **Financial Summaries**: Simple, formatted income and expense lists.
