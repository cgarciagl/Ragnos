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
| `tab`     | Tab name (optional)                  |

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

### Tabbed Organization

Ragnos allows you to organize form fields into tabs to improve usability in extensive forms.
Simply add the `tab` key to the field configuration array with the desired tab name.

```php
$this->addField('basic_info', [ ... , 'tab' => 'General' ]);
$this->addField('technical_details', [ ... , 'tab' => 'Details' ]);
$this->addField('pricing', [ ... , 'tab' => 'Finance' ]);
```

- Fields without a defined tab will automatically be placed in a tab labeled **"General"**.
- If no field has a tab defined, the form will be displayed without tabs (classic behavior).
- If a validation error occurs in a field hidden by a tab, Ragnos will automatically activate that tab to show the error.

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

## 3. Multiline Text Field (`textarea`)

For long texts like descriptions or comments.

```php
$this->addField('productDescription', [
    'label' => 'Description',
    'type'  => 'textarea',
    'rules' => 'required'
]);
```

- **HTML Variant:** Use `type => 'htmltextarea'` for a basic rich text editor.

---

## 4. Date and Time Field

Ragnos automatically handles date selection.

```php
$this->addField('orderDate', [
    'label' => 'Order Date',
    'type'  => 'datetime', // or 'date'
    'rules' => 'required'
]);
```

- Includes date/time picker (datepicker).

---

## 5. Numeric Field

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

## 6. Monetary Field (`money`)

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

## 7. Readonly Field

```php
$this->addField('Contact', [
    'label' => 'Contact',
    'rules' => 'readonly'
]);
```

- Visible
- Not editable

---

## 8. Hidden Field

```php
$this->addField('Contact', [
    'label' => 'Contact',
    'rules' => 'readonly',
    'type'  => 'hidden'
]);
```

- Not visible in forms
- Useful for logic or UX

---

## 9. Calculated Field (`query`)

```php
$this->addField('fullName', [
    'label' => 'Full Name',
    'rules' => 'readonly',
    'query' => "concat(contactLastName, ', ', contactFirstName)",
    'type'  => 'hidden'
]);
```

- Non-persistent (not a real column in the table)
- Dynamic SQL expression
- Read-only

---

## 10. Related Field (`addSearch`)

Links a field to another controller for selection (Foreign Key).

```php
$this->addSearch('salesRepEmployeeNumber', 'Shop\\Employees');
```

- Logical foreign key
- Dynamic selector (modal or autocomplete)
- Automatic JOIN handling

---

## 11. Primary Key Field

```php
$this->setIdField('customerNumber');
// Disable auto-increment if needed (e.g., for alphanumeric codes)
$this->setAutoIncrement(false);
```

- Dataset identity
- Mandatory
- Defaults to auto-increment

---

## 12. Dropdown Field (Enum)

- **Brief description:** A dropdown (enum) presents a closed set of options (key => label). The selected key is stored in the database; the label is used only for the interface.

- **Typical configuration:**
  - `type`: "dropdown"
  - `options`: associative array [value => label]
  - `default`: default key
  - `rules`: validate with CI4 rules (e.g. required, in_list)
  - Recommended for short and stable lists; for large volumes use `addSearch` (related selector).

- **Static Example:**

```php
$this->addField('status', [
    'label'   => 'Status',
    'type'    => 'dropdown',
    'options' => [
        'shipped'   => 'Shipped',
        'pending'   => 'Pending',
        'cancelled' => 'Cancelled'
    ],
    'default' => 'pending',
    'rules'   => 'required|in_list[shipped,pending,cancelled]'
]);
```

- **Dynamic Example (load from DB):**

```php
$rows = $this->db->table('categories')->select('id, name')->get()->getResultArray();
$options = array_column($rows, 'name', 'id');

$this->addField('categoryId', [
    'label'   => 'Category',
    'type'    => 'dropdown',
    'options' => $options,
    'rules'   => 'required|in_list[' . implode(',', array_keys($options)) . ']'
]);
```

- **Placeholder / empty option:** Add an entry with an empty key to force selection: `options => ['' => '(Select)'] + $options`.

- **Validation and Security:**
  - Use `in_list` to avoid disallowed values.
  - If options are dynamic, regenerate the list on each form load so `in_list` matches.

- **UX and Performance:**
  - Dropdown for <~20-30 options.
  - For relations with many rows, use `addSearch` or an autocomplete component.
  - If you need to select multiple values, prefer a multiselect component (or a relation type field); do not use a simple dropdown.

- **Internationalization:** Store stable keys and translate labels when generating options to facilitate language changes.

- **Persistence:** The stored value is the key; if you need to save the label, consider a calculated field or a view.

---

## 13. Switch Field (Boolean)

Renders a modern toggle switch. Ideal for boolean or status fields (active/inactive).

```php
$this->addField('isActive', [
    'label'   => 'Published',
    'type'    => 'switch',
    'default' => 1,          // Default value (checked)
    // Optional: Customize values (defaults are 1 and 0)
    'onValue' => 'Y',
    'offValue'=> 'N'
]);
```

- Handles `1/0` values (or custom).
- Modern UI (Bootstrap switch).

---

## 14. Pillbox Field (Tags)

Allows managing a list of simple tags. It is stored in the database as a JSON array.

```php
$this->addField('tags', [
    'label' => 'Tags',
    'type'  => 'pillbox'
]);
```

- **Interface:** Text input that converts entries into "pills" upon pressing Enter or Comma.
- **Storage:** JSON Array (e.g., `["Red", "Sale"]`). Requires a `JSON` or `TEXT` field in DB.
- **Usage:** Simple categorization, keywords.

---

## 15. File Upload (`fileupload`)

Allows uploading general files to the server.

- **Persistence:** Saves the **relative path** of the file in the database (e.g., `assets/uploads/document.pdf`).
- **Smart Validation:** Automatically detects insert vs. update scenarios.
  - When editing, if the user does not upload a new file, the **previous file is preserved**.
  - If the field has file-specific rules (e.g., `max_size`, `ext_in`), they are automatically ignored if no new file is uploaded during an update, preventing validation errors.
- **Automatic Folder Creation:** If the destination path does not exist, the system attempts to create it recursively.

```php
$this->addField('attached_document', [
    'label'      => 'PDF Document',
    'type'       => 'fileupload',
    'uploadPath' => 'assets/docs/contracts',  // Destination folder (relative to public/)
    'rules'      => 'uploaded[attached_document]|ext_in[attached_document,pdf,docx]|max_size[attached_document,4096]'
]);
```

### Specific Parameters

| Parameter    | Description                                                                                      |
| ------------ | ------------------------------------------------------------------------------------------------ |
| `uploadPath` | Path relative to the public folder where files will be saved. Default: `assets/uploads`.         |
| `rules`      | Standard CodeIgniter 4 file validation rules (`uploaded`, `max_size`, `ext_in`, `mime_in`, etc). |

!!! warning "Important on `uploaded`"

    Although Ragnos handles optionality during editing, if you want the file to be **mandatory upon creation**, you must include the `uploaded[field]` rule. Ragnos will automatically remove it only when it is safe to do so (editing while keeping the previous file).

---

## 16. Image Upload (`imageupload`)

A specialization of `fileupload` optimized for images.

- **Preview:** Shows an immediate preview of the selected image before saving.
- **Visualization:** Displays the currently stored image if it exists.
- **Validation:** Identical to `fileupload`, compatible with `is_image`, `max_dims`, etc.

```php
$this->addField('profile_photo', [
    'label'      => 'Avatar',
    'type'       => 'imageupload',
    'uploadPath' => 'assets/img/users',
    'rules'      => 'is_image[profile_photo]|max_size[profile_photo,2048]',
    'accept'     => 'image/*' // HTML attribute for input file
]);
```

!!! tip "Making the image optional"

    If you want the image to be optional even when creating the record, simply **omit** the `uploaded` rule.

    ```php
    'rules' => 'is_image[profile_photo]' // Optional, but if something is uploaded, it must be an image
    ```

---

## 17. Summary

| Type       | Persistent | Editable |
| ---------- | ---------- | -------- |
| Text       | Yes        | Yes      |
| Numeric    | Yes        | Yes      |
| Money      | Yes        | Yes      |
| Readonly   | Depends    | No       |
| Hidden     | Depends    | No       |
| Calculated | No         | No       |
| Relation   | Yes        | Yes      |
| Dropdown   | Yes        | Yes      |
| PK Key     | Yes        | Yes      |

---

**In Ragnos, fields are domain declarations, not simple inputs.**
