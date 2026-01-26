# 📊 Simple Report Generation (RSimpleLevelReport)

The `RSimpleLevelReport` class allows generating reports in HTML table format quickly and structured directly from a data array. Ideal for displaying lists, financial summaries or any dataset requiring tabular presentation, with support for grouping levels (control breaks).

## Main Features

- Automatic HTML table generation with professional design.
- Support for multiple grouping levels (headers per group).
- Automatic record total calculation.
- **Detection and calculation of sums for numeric columns (subtotals and grand totals).**
- Easy integration with Ragnos views.

## Basic Usage Flow

1.  Get data (usually from Model).
2.  Instantiate `App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport`.
3.  Configure report using `quickSetup`.
4.  Render report and pass to view.

## Class API

### `quickSetup($title, $data, $listfields, $groups = [], $desc_filter = '')`

Configures main report parameters at once.

- **`$title`** (string): Main report title.
- **`$data`** (array): Data set (array of associative arrays).
- **`$listfields`** (array): List of fields to show.
  - If simple array `['Field1', 'Field2']`, keys are searched in `$data`.
  - Recommended for names to match data array keys.
- **`$groups`** (array): (Optional) Grouping configuration.
  - Format: `['grouping_field' => ['label' => 'Group Label']]`.
- **`$desc_filter`** (string): (Optional) Subtitle or description of applied filters.

### `setShowTotals(bool $showTotals)`

Enables or disables totals display (record count and financial sums) at the end of each group and report (default `true`).

### `setSummableFields(array $fields)`

Explicitly defines which fields should be mathematically summed in table footers.

- **`$fields`** (array): List of field names (labels) containing numeric values.
- _Note:_ If this method is not used, the class attempts to automatically detect summable fields by looking for names like 'Total', 'Price', 'Balance', 'Debt', etc.

### `render($returnRoute = 'admin/index')`

Returns rendered report HTML inside standard report view. Useful embedding in existing layouts.

### `generate()`

Returns only HTML of table and headers, without full view wrapper. Useful for AJAX injections or custom views.

---

## Implementation Examples

### 1. Simple Report (Flat List)

Example showing monthly sales list without grouping.

```php
<?php

namespace App\Controllers\Store;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

class Reports extends BaseController
{
    public function salesbymonth()
    {
        // 1. Get data
        $model = new \App\Models\Dashboard();
        $data = $model->last12monthssales();
        // Expected structure of $data:
        // [['Month' => 'January', 'Total' => 1000], ['Month' => 'February', 'Total' => 1500], ...]

        // 2. Format data (optional but recommended)
        foreach ($data as $key => $value) {
            $data[$key]['Total'] = '$ ' . number_format($value['Total'], 2);
        }

        // 3. Configure Report
        $report = new RSimpleLevelReport();
        $report->setShowTotals(false); // Hide record count as irrelevant here

        // quickSetup(Title, Data, Columns to show)
        $report->quickSetup('Sales by Month', $data, ['Month', 'Total']);

        // 4. Render
        $content = $report->render();
        return view('admin/report_view', ['content' => $content]);
    }
}
```

### 2. Grouped Report (Control Break)

Example generates product list grouped by **Category**. Every time category changes, new header is generated.

**Important Requirement**: For grouping to work correctly, **input data must be sorted** by grouping field.

```php
<?php

namespace App\Controllers\Store;

use App\ThirdParty\Ragnos\Controllers\BaseController;
use App\ThirdParty\Ragnos\Controllers\RSimpleLevelReport;

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

Possible to group by multiple levels (e.g. Country -> State -> City).

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

// Optional: Force which fields to sum (if not automatically detected)
// $report->setSummableFields(['sale', 'commission']);

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
- **Audit**: System log lists.
- **Financial Summaries**: Simple income/expense lists formatted.
