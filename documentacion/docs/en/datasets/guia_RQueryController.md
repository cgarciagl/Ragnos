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

use App\ThirdParty\Ragnos\Controllers\RQueryController;

class SearchUsers extends RQueryController
{
    public function __construct()
    {
        parent::__construct();

        // 1. Security
        $this->checkUserInGroup('Administrator');

        // 2. Visual Configuration
        $this->setTitle('🔎 Users');

        // 3. Data Source (SQL)
        $this->setQuery("SELECT usu_id, usu_nombre as 'Name', usu_login as 'Login', usu_activo as Active, usu_grupo FROM gen_usuarios");

        // 4. Primary Key (Virtual or Real)
        $this->setIdField('usu_id');

        // 5. Field Configuration (Filters and Visualization)
        $this->addField('Active', [
            'label'   => 'Active',
            'type'    => 'dropdown',
            'options' => ['S' => 'YES', 'N' => 'NO'],
        ]);

        $this->addField('usu_grupo', ['label' => 'Group']);

        // 6. Relationships for Search
        $this->addSearch('usu_grupo', 'UserGroups');

        // 7. Grid Definition
        $this->setTableFields(['Name', 'Login', 'Active', 'usu_grupo']);
    }
}
```

---

## 📚 Key Methods

### `setQuery(string $sql)`

Defines the data source.

- **Important:** Do not use `ORDER BY` or `LIMIT` here; Ragnos injects them dynamically according to user interaction in the grid.
- **Alias:** It is highly recommended to use SQL aliases (`AS 'Name'`) so table headers are automatically readable.

### `setIdField(string $field)`

Even if it is a custom query, Ragnos needs to know which column uniquely identifies each row (for selection, details, etc.).

- Must be present in the `SELECT` of `setQuery`.

### `addField(string $name, array $config)`

Works the same as in `RDatasetController`, but here it is mainly used to **generate search filters**.

- If you define a field as `'type' => 'dropdown'`, Ragnos will create a `<select>` in the advanced search bar.

### `addSearch(string $field, string $controller)`

Allows linking a column of your query to another Ragnos controller.

- This enables the magnifying glass icon to search for related values.

### `checkUserInGroup(string|array $groups)`

Restricts access to this controller exclusively for the specified user groups.

---
