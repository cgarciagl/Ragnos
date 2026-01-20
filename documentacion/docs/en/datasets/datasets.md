# 💾 Datasets in Ragnos

## What is a Dataset?

A dataset (`RDatasetController`) is the core concept of Ragnos for **declarative** development. Instead of writing controllers, models, and views for every CRUD module, a dataset allows defining the **structure and behavior** of an entity through metadata in the constructor.

The framework uses this definition to automatically generate:

- User Interfaces (Forms and Grids/Lists).
- Input Validations (Backend and Frontend).
- SQL Queries and Database Persistence.
- API Responses.

## Advantages

- **Centralization**: Everything (validation, display, persistence) is defined in one place.
- **Productivity**: Eliminates the need to write repetitive HTML or basic CRUD queries.
- **Consistency**: All modules behave and look the same.
- **Flexibility**: Extensions through Hooks and virtual fields.

## Create a Dataset

A dataset is a controller that extends `RDatasetController`. All configuration is done in the constructor.

### Minimal Example

```php
namespace App\Controllers\Store;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Customers extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        // Security and context
        $this->checkLogin();
        $this->setTitle('Customers');

        // Persistence
        $this->setTableName('customers');
        $this->setIdField('customerNumber');
        $this->setAutoIncrement(false);

        // Fields
        $this->addField('customerName', [
            'label' => 'Name',
            'rules' => 'required'
        ]);

        // Calculated field
        $this->addField('Contact', [
            'label' => 'Contact',
            'rules' => 'readonly',
            'query' => "concat(contactLastName, ', ', contactFirstName)",
            'type'  => 'hidden'
        ]);

        // Relationships
        $this->addSearch('salesRepEmployeeNumber', 'Store\\Employees');

        // Grid
        $this->setTableFields([
            'customerName',
            'Contact',
            'salesRepEmployeeNumber'
        ]);
    }
}
```

## Security and Context

- `checkLogin()` to require active session.
- `setTitle()` for module title and breadcrumbs.

!!! tip "Security by Default"

    Always call `checkLogin()` at the beginning of the constructor if your module should be private. If you forget this line, the module will be publicly accessible.

## Table and Primary Key

- `setTableName('table')` defines the main table.
  !!! warning "Consistency with DB"

      The value of `setTableName` must exactly match the name of the physical table in your database. Ragnos does not create the table for you; it assumes it already exists.

- `setIdField('id')` establishes the primary key.
- `setAutoIncrement(true|false)` controls if the PK is auto-incrementing.

## Field Definition

Use `addField(name, options)` to describe validation, presentation, and persistence.
For a complete reference of all options and field types, consult the [Field Guide](campos.md).

Common options:

- `label`: visible text.
