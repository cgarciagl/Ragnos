# Audit Log

Ragnos framework includes a native automatic system to log changes made to entity data. This system allows tracking who performed an action, when, from which IP address, and what specific data was modified, providing complete operation traceability.

## Configuration

### Usage in Datasets (RDatasetController)

!!! success "Enabled by default"

    All classes extending `RDatasetController` have audit logging **automatically enabled**.

If you wish to **disable** audit for a specific dataset (e.g. temporary tables or massive movements not needing traceability), use `setEnableAudit(false)` method inside constructor.

**Example:**

```php
class TemporaryLogs extends RDatasetController
{
    public function __construct()
    {
        parent::__construct();

        $this->setTableName('temp_logs');
        $this->setIdField('id');

        // ❌ Disable audit for this controller
        $this->setEnableAudit(false);

        // ... rest of config
    }
}
```

### Usage in Manual Models

To activate audit log in custom model (not managed by Dataset), property `$enableAudit` must be set to `true`. Model must use `CrudOperationsTrait` (or inherit from base class using it, like `RdatasetModel`).

```php
class CustomersModel extends RdatasetModel
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';

    // Enable automatic audit for this entity
    protected $enableAudit = true;

    // ... field definition
}
```

By enabling this property, all insert, update, delete operations via controller/model will be intercepted and logged automatically without extra code in controllers.

## Internal Functioning

System works via `App\ThirdParty\Ragnos\Models\Traits\CrudOperationsTrait`. When write action executes, system performs steps transparently:

### 1. Insertion (INSERT)

- Executed after record saved successfully.
- Saves all new record data in log under key `new`.

### 2. Update (UPDATE)

- Before saving, system compares current database values with sent new values.
- Only if real differences exist, log save proceeds.
- **Optimization**: Logs JSON containing only changed fields, with old value (`old`) and new (`new`).

  _Example saved JSON:_

  ```json
  {
    "status": {
      "old": "pending",
      "new": "active"
    },
    "credit_limit": {
      "old": "1000.00",
      "new": "2500.00"
    }
  }
  ```

### 3. Deletion (DELETE)

- Before deleting physical record, system queries for current data.
- Saves full copy of deleted record data under key `deleted_data` to allow historical query or manual restore.

## Logged Information

For each event, `AuditLogModel` (table `gen_audit_logs`) stores:

| Field        | Description                                                                                                         |
| ------------ | ------------------------------------------------------------------------------------------------------------------- |
| `user_id`    | ID of user performing action. System resolves identity automatically by active session (Web) or Bearer Token (API). |
| `table_name` | Name of database table where change occurred.                                                                       |
| `record_id`  | ID (Primary Key) of affected record.                                                                                |
| `action`     | Operation type: `INSERT`, `UPDATE` or `DELETE`.                                                                     |
| `changes`    | Payload in JSON format with change details (see above). Stored with Unicode support for special characters.         |
| `ip_address` | IP address originating request.                                                                                     |
| `user_agent` | Browser or HTTP client info used.                                                                                   |

## User Detection (Web and API)

Audit mechanism is agnostic to request origin. Method `getCurrentUserId()` resolves actor identity as follows:

1. **Web Session:** Checks if CodeIgniter session active (`session()->get('usu_id')`).
2. **API Token:** If API call, inspects `Authorization` header. Extracts Bearer token and looks up user in `gen_usuarios`.
3. **Fallback:** If user not identified, logs ID `0` (System/Anonymous).
