# Supported Field Types in Ragnos

In Ragnos, dataset fields are defined using the `addField()` method. This method is a fundamental part of the configuration of an [`RDatasetController`](datasets.md).
Each field is a **declarative description** of how a domain attribute:

- Is validated
- Is displayed in forms
- Is displayed in grids
- Is persisted (or not) in the database

Ragnos uses this metadata to automatically generate forms, validations, listings, and CRUD behavior.

---

## 1. General Structure of `addField()`

```php
$this->addField('fieldName', [
    'label' => 'Visible label',
    'rules' => 'validation|rules',
    'type'  => 'type',
    'query' => 'SQL expression'
]);
```

### Common Parameters

| Parameter | Description                          |
| --------- | ------------------------------------ |
| `label`   | Text visible in forms and grids      |
| `rules`   | Validation rules (CI4 + Ragnos)      |
| `type`    | Field type (optional)                |
| `query`   | SQL expression for calculated fields |

!!! tip "Validation Rules"

    Ragnos adopts the powerful CodeIgniter 4 validation engine. You can use rules such as `required`, `is_unique`, `min_length`, `valid_email`, etc.

    [Consult all available rules in the official CodeIgniter 4 documentation](https://codeigniter.com/user_guide/libraries/validation.html#available-rules)

!!! note "Simplified `is_unique` Validation"

    Unlike CodeIgniter 4, where `is_unique` usually requires parameters like `is_unique[table.field]`, Ragnos simplifies its usage: **you only need to specify the keyword `is_unique`**.

    The framework automatically detects which table and field are being operated on (using the definitions in `setTableName` and the field name in `addField`) to build the verification query. Additionally, it correctly handles exceptions when editing a record (auto-detecting the primary key defined with `setIdField` to ignore the current record).

    **Common Use Cases:**
    It is ideal for fields that must be unique across the system, such as:

    - **Email addresses** in a user registry.
    - **Usernames** or aliases.
    - **Product codes** (SKU) or barcodes.
    - **National ID** numbers.

---

## 2. Text Field (string)

```php
$this->addField('customerName', [
    'label' => 'Name',
    'rules' => 'required'
]);
```

- Persistent
- Editable
- Free text

---

## 3. Numeric Field

```php
$this->addField('postalCode', [
    'label' => 'Postal code',
    'rules' => 'required|numeric'
]);
```

- Persistent
- Editable
- Numeric

---

## 4. Monetary Field (`money`)

```php
$this->addField('creditLimit', [
    'label' => 'Credit limit',
    'rules' => 'required|money'
]);
```

- Custom validator
- Automatic normalization
- Financial use

---

## 5. Readonly Field

```php
$this->addField('Contact', [
    'label' => 'Contact',
    'rules' => 'readonly'
]);
```

- Visible
- Not editable

---
