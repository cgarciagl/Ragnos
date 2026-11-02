# Reference Guide: RQueryController

`RQueryController` is a specialized variant of the Ragnos base controller designed for **read-only queries** and **advanced searches**.

Unlike [`RDatasetController`](datasets.md) (which maps to a physical table to perform CRUD), `RQueryController` maps to an **Arbitrary SQL Query**.

---

## 🎯 When to use RQueryController?

Use this controller when:

1.  **You don't need to edit:** You only want to list, search, and filter data.
2.  **The data is complex:** You need to perform `JOINs`, subqueries, or calculated fields (e.g., `CONCAT`, `CASE WHEN`) that do not exist in a single physical table.
3.  **Quick Reports:** You want to expose a database view in the administrative system quickly.

---

## 🧬 Basic Structure

A query controller extends from `RQueryController` and defines its logic in the constructor.

**Analyzed Example:** `Searchusuarios.php`

```php
namespace App\Controllers;

use Ragnos\Controllers\RQueryController;

class Searchusuarios extends RQueryController
{
    public function __construct()
    {
        parent::__construct();

        // 1. Security
        $this->checkUserInGroup('Administrador');

        // 2. Visual Configuration
        $this->setTitle('🔎 Usuarios');

        // 3. Data Source (SQL)
        $this->setQuery("SELECT usu_id, usu_nombre as 'Nombre', usu_login as 'Login', usu_activo as Activo, usu_grupo FROM gen_usuarios");

        // 4. Primary Key (Virtual or Real)
        $this->setIdField('usu_id');

        // 5. Field Configuration (Labels and Formatting)
        $this->addField('Activo', [
            'label'   => 'Activo',
            'type'    => 'dropdown',
            'options' => ['S' => 'SI', 'N' => 'NO'],
        ]);

        $this->addField('usu_grupo', ['label' => 'Grupo']);

        // 6. Relationships for Search
        $this->addSearch('usu_grupo', 'Gruposdeusuarios');

        // 7. Grid Definition
        $this->setTableFields(['Nombre', 'Login', 'Activo', 'usu_grupo']);
    }
}
```

This is the real code of the controller included with Ragnos, so its names, labels, and the `Administrador` group keep their original Spanish names.

---

## 📚 Key Methods

### `setQuery(string $sql)`

Defines the data source.

- **Important:** Do not use `ORDER BY` or `LIMIT` here; Ragnos injects them dynamically according to user interaction in the grid.
- **Alias:** It is highly recommended to use SQL aliases (`AS 'Nombre'`) so table headers are automatically readable.

### `setIdField(string $field)`

Even if it is a custom query, Ragnos needs to know which column uniquely identifies each row (for selection, details, etc.).

- Must be present in the `SELECT` of `setQuery`.

### `addField(string $name, array $config)`

Works the same as in `RDatasetController`: it gives labels and formatting to the result columns.

- `'type' => 'money'` shows the column in currency format.
- `'type' => 'dropdown'` translates values into their descriptions (for example, `S` → `SI`) and lets the grid search find records by typing that description.

### `_filters()`

Ragnos wraps your query in a CTE and builds the grid query on top of its result. That is why in `_filters()` you can filter any result column, including aggregated ones, with a regular `WHERE` (no `HAVING`):

```php
public function _filters()
{
    $this->modelo->builder()->where('Activo', 'S');
}
```

### `addSearch(string $field, string $controller)`

Lets you link a column of your query to another Ragnos controller.

- This enables the magnifying glass icon to search for related values.

### `checkUserInGroup(string|array $groups)`

Restricts access to this controller exclusively for the specified user groups.

---

## ⚠️ Differences from RDatasetController

| Feature                  | RDatasetController       | RQueryController              |
| :----------------------- | :----------------------- | :---------------------------- |
| **Data Source**          | `setTableName('table')`  | `setQuery('SELECT...')`       |
| **Editing (Save/Update)**| ✅ Supported (Automatic) | ❌ Not supported              |
| **Deletion (Delete)**    | ✅ Supported             | ❌ Not supported              |
| **New Record**           | ✅ Supported             | ❌ Not supported              |
| **Main Use**             | CRUDs (Catalogs)         | Reports, Dashboards, Searches |

---

## 💡 Implementation Tips

1.  **Calculated Fields:**
    You can create "virtual" columns in the SQL and use them in the grid:

    ```php
    $this->setQuery("SELECT id, price * quantity AS Total, ... FROM sales");
    $this->setTableFields(['id', 'Total']);
    ```

2.  **Joins (JOINs):**
    Ideal for showing names instead of IDs without visually configuring `addSearch`:
    ```php
    $this->setQuery("
        SELECT p.name, c.category_name
        FROM products p
        JOIN categories c ON p.cat_id = c.id
    ");
    ```
