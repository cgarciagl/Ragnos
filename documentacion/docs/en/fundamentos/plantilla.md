# Ragnos CLI Generator

The **Ragnos CLI Generator** is a command-line tool integrated into Spark (CodeIgniter 4) that allows creating [`RDatasetController`](../datasets/datasets.md) skeletons in seconds.

You can see this generator in action in the [Getting Started](primeros_pasos.md) guide.

---

## 📋 Table of Contents

1. [Installation](#installation)
2. [Command Syntax](#command-syntax)
3. [Usage Examples](#usage-examples)
4. [Intelligent Type Mapping](#intelligent-type-mapping)
5. [Benefits](#benefits)

---

## Installation {#installation}

The Ragnos CLI Generator comes pre-installed with the Ragnos package. Just make sure you have Ragnos correctly installed in your CodeIgniter 4 project.

1. Verify command availability by running:

```bash
php spark list
```

You should see the `Ragnos` group and the commands `ragnos:make` and `ragnos:make:query`.

---

## Command Syntax {#command-syntax}

### 1. Generate from Table (RDatasetController)

```bash
php spark ragnos:make [ControllerName] [Options]
```

#### Arguments (ragnos:make)

| Argument         | Description                                                  |
| :--------------- | :----------------------------------------------------------- |
| `ControllerName` | The path and name of the class (e.g., `Inventory/Products`). |

#### Options (ragnos:make)

| Option   | Description                    |
| :------- | :----------------------------- |
| `-table` | Exact name of the table in DB. |

---

### 2. Generate from Query (RQueryController)

```bash
php spark ragnos:make:query [ControllerName] [Options]
```

#### Arguments (ragnos:make:query)

| Argument         | Description                                               |
| :--------------- | :-------------------------------------------------------- |
| `ControllerName` | The path and name of the class (e.g., `Dashboard/Sales`). |

#### Options (ragnos:make:query)

| Option   | Description              |
| :------- | :----------------------- |
| `-query` | The SQL query in quotes. |

---

## Usage Examples {#usage-examples}

### 1. Basic Usage (Auto-detection)

If your controller is `Products` and table `products`:

```bash
php spark ragnos:make Store/Products
```

### 2. Usage with Specific Table

```bash
php spark ragnos:make Admin/Users -table app_users_tbl
```

---

## Intelligent Type Mapping {#intelligent-type-mapping}

Ragnos chooses the component according to your DB:

| DB Type      | Ragnos Type | Auto-generated Rules        |
| :----------- | :---------- | :-------------------------- |
| `INT`        | `number`    | `required \| integer`       |
| `DECIMAL`    | `money`     | `required \| decimal`       |
| `DATE`       | `date`      | `required`                  |
| `TEXT`       | `textarea`  | `required`                  |
| `TINYINT(1)` | `checkbox`  | `permit_empty`              |
| `VARCHAR`    | `text`      | `required \| max_length[n]` |

---

## Benefits {#benefits}

1. **Speed:** Create a CRUD in 10 seconds.
2. **Standardization:** Avoids copy-paste errors and namespace issues.
3. **Cleanliness:** Generates readable labels ("Start Date" instead of "start_date").

## Official New Dataset Template

If you want to create a new Dataset from scratch, without using the command line, use this template as a guide to define the recommended structure and conventions.

```php
<?php

namespace App\Controllers\Store;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class ExampleDataset extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        $this->checkLogin();
        $this->setTitle('Example');
        $this->setTableName('example_table');
        $this->setIdField('id');
        $this->setAutoIncrement(true);

        $this->addField('name', [
            'label' => 'Name',
            'rules' => 'required'
        ]);

        $this->setTableFields(['name']);
    }
}
```

## 📄 Dataset Template Documentation

### 🎯 Purpose

This template serves as a starting point for creating a new Dataset to be integrated into the project store. It provides the structure, conventions, and minimum recommended fields for the Dataset to function correctly in the existing flow.

### 📝 Basic Instructions

1. **Duplicate:** Copy this file and place it in the appropriate path of the repository for the new Dataset.
2. **Adjust:** Modify class names, tables, and fields according to your needs (see "Conventions").
3. **Register:** Define the Dataset in the store module/manifest so it becomes available in the interface and API.

### 🏷 Naming Conventions

- **Class/Model:** `PascalCase` (e.g., `MyDataset`).
- **DB Table:** `snake_case` plural (e.g., `my_datasets`).
- **Fields:** `snake_case` (e.g., `creation_date`, `user_id`).
- **Slug/Public Identifier:** `kebab-case` (e.g., `my-dataset`).

### 🏗 Recommended Structure

- **Metadata:** Name, slug, short description, version, author/contact.
- **Data Schema:** List of fields with name, type (`string`, `integer`, `boolean`, `date`, `json`, etc.), nullability, and default values.
- **Relationships:** Define `belongsTo`/`hasMany`/`hasOne` relationships with other tables (indicating FK).
- **Indexes:** Declare indexes for search fields and foreign keys.

### 🗄 Migrations and Schema

- **Migration:** Create a migration that generates the table with all fields and constraints.
- **Foreign Keys:** Include `ON DELETE`/`ON UPDATE` according to business logic.
- **Optimization:** Add indexes for frequent queries.

### ✅ Validations and Types

- **Server:** Specify validations (required, format, range) and expected types.
- **UI:** Map validations to interface forms to offer consistent feedback.

### 🛒 Store Registration

- **Manifest:** Add the Dataset to the Dataset list so the store recognizes it.
- **API:** Provide endpoints or adapters for CRUD according to project architecture.
- **Security:** Include necessary permissions and roles to access or modify the Dataset.

### 🧩 Placeholder Examples (Replace with real ones)

- **Class name:** `MyDataset`
- **Table:** `my_datasets`
- **Fields:**
  - `id` (PK)
  - `title` (string)
  - `description` (text)
  - `published` (boolean)
  - `created_at` (datetime)
  - `user_id` (FK)

### 🧪 Testing and Verification

1. Create **unit tests** for validations and models.
2. Test **migrations** on a DB copy and verify referential integrity.
3. Verify **registration and visibility** in the store interface.

### 💡 Best Practices

- **Self-documentation:** Maintain documentation within the Dataset itself (metadata) to facilitate discovery.
- **Versioning:** Control schema changes and document incompatible migrations.
- **Compatibility:** Avoid _breaking_ changes in public fields without prior migrations and communication to consumers.

> **Final Notes:** Ensure you adapt names and types to your domain logic. This template is a guide; review your project's database policies, security, and permissions before publishing the Dataset.

With this template and guide, you should be ready to create a new Dataset that integrates perfectly into your project!
