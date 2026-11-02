# Audit Log

The Ragnos framework includes a native, automatic system that logs changes made to entity data. It lets you track who performed an action, when, from which IP address, and exactly which data was modified, providing complete traceability of every operation.

## Configuration

### Usage in Datasets (RDatasetController)

!!! success "Enabled by default"

    All classes extending `RDatasetController` have audit logging **automatically enabled**.

If you want to **disable** auditing for a specific dataset (e.g. temporary tables or bulk operations that do not need traceability), call the `setEnableAudit(false)` method inside the constructor.

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

To enable the audit log in a custom model (one not managed by a dataset), set its `$enableAudit` property to `true`. The model must use `CrudOperationsTrait` (or inherit from a base class that uses it, such as `RDatasetModel`).

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

With this property enabled, every insert, update, and delete operation performed through the controller or model is intercepted and logged automatically, with no extra code in your controllers.

## Internal Functioning

The system works through `Ragnos\Models\Traits\CrudOperationsTrait`. When a write operation runs, the system transparently performs these steps:

### 1. Insertion (INSERT)

- It runs after the record has been saved successfully.
- It saves all of the new record's data in the log under the `new` key.

### 2. Update (UPDATE)

- Before saving, the system compares the current database values with the new values that were sent.
- The log entry is saved only if there are real differences.
- **Optimization**: It logs a JSON object containing only the changed fields, with their old (`old`) and new (`new`) values.

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

- Before deleting the physical record, the system reads its current data.
- It saves a full copy of the deleted record under the `deleted_data` key, so it can be consulted later or restored manually.

## Logged Information

For each event, `AuditLogModel` (the `gen_audit_logs` table) stores:

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

The audit mechanism does not depend on where the request comes from. The `getCurrentUserId()` method resolves the user's identity as follows:

1. **Web Session:** It checks whether there is an active CodeIgniter session (`session()->get('usu_id')`).
2. **API Token:** For API calls, it inspects the `Authorization` header, extracts the Bearer token, and looks up the user in `gen_usuarios`.
3. **Fallback:** If the user cannot be identified, it logs ID `0` (System/Anonymous).

## Querying a Record's History (API)

Every dataset based on `RDatasetController` automatically exposes the `history/{id}` endpoint, which returns a record's audit entries from newest to oldest. It is the ideal foundation for a "Change history" tab in your UI or in an external app.

```bash
curl -X GET "https://your-domain.com/tienda/productos/history/S10_1678" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer YOUR_API_TOKEN"
```

Sample response:

```json
{
  "data": [
    {
      "id": "152",
      "user_id": "3",
      "table_name": "products",
      "record_id": "S10_1678",
      "action": "UPDATE",
      "changes": "{\"buyPrice\":{\"old\":\"48.81\",\"new\":\"52.00\"}}",
      "ip_address": "192.168.1.20",
      "user_agent": "Mozilla/5.0 ...",
      "created_at": "2026-10-28 11:42:07"
    }
  ]
}
```

- It is an **API-mode-only** endpoint: if `Accept: application/json` is not sent, it responds `400` with `{"error": "API call required"}`.
- It requires the same credentials as the rest of the API.
- It is automatically documented in the OpenAPI specification (`GET {route}/history/{id}`).
- The `changes` field is JSON stored as text; decode it on the client (`JSON.parse`) to show the before and after.
